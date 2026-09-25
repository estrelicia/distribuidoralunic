<?php
class WCD_Price_Calculator {
    private static $option_name = 'wcd_discount_rules';
    private static $processing_products = array();
    private static $price_cache = array();
    private static $cart_processed = false;
    
    public static function init() {
        // Aplicar descuentos/recargos solo al precio final
        add_filter('woocommerce_product_get_price', array(__CLASS__, 'apply_category_discount'), 10, 2);
        add_filter('woocommerce_product_variation_get_price', array(__CLASS__, 'apply_category_discount'), 10, 2);
        
        // Mostrar precios en el frontend - CON PRIORIDAD BAJA para evitar conflictos
        add_filter('woocommerce_get_price_html', array(__CLASS__, 'display_discount_price'), 999, 2);
        
        // Aplicar descuentos al carrito
        add_action('woocommerce_before_calculate_totals', array(__CLASS__, 'apply_cart_discounts'), 5, 1);
        
        // Aplicar descuentos a productos variables
        add_filter('woocommerce_variation_prices_price', array(__CLASS__, 'apply_variation_discount'), 10, 3);
        
        // Asegurar que los precios variables se muestren correctamente
        add_filter('woocommerce_show_variation_price', '__return_true');
        
        // Agregar AJAX para el widget de Elementor
        add_action('wp_ajax_wcd_get_variation_discount', array(__CLASS__, 'get_variation_discount_ajax'));
        add_action('wp_ajax_nopriv_wcd_get_variation_discount', array(__CLASS__, 'get_variation_discount_ajax'));
    }
    
    public static function apply_category_discount($price, $product) {
        if (empty($price) || $price <= 0) {
            return $price;
        }
        
        // Si estamos procesando el carrito, no aplicar descuentos duplicados
        if (self::$cart_processed && doing_action('woocommerce_before_calculate_totals')) {
            return $price;
        }
        
        // Evitar bucles infinitos
        $product_id = $product->get_id();
        if (isset(self::$processing_products[$product_id])) {
            return $price;
        }
        
        // Verificar si ya calculamos este precio
        $cache_key = $product_id . '_' . $price;
        if (isset(self::$price_cache[$cache_key])) {
            return self::$price_cache[$cache_key];
        }
        
        self::$processing_products[$product_id] = true;
        
        // Obtener reglas de descuento
        $rules = self::get_discount_rules();
        if (empty($rules)) {
            unset(self::$processing_products[$product_id]);
            return $price;
        }
        
        // Verificar si el producto tiene descuento individual
        $has_individual_discount = self::has_individual_discount($product);
        
        // Obtener categorías del producto
        $product_categories = self::get_product_categories($product);
        if (empty($product_categories)) {
            unset(self::$processing_products[$product_id]);
            return $price;
        }
        
        // Ordenar reglas por prioridad
        usort($rules, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        $original_price = $price;
        $applied_rule = false;
        
        // Aplicar la primera regla que coincida
        foreach ($rules as $rule) {
            if (!in_array($rule['category'], $product_categories)) {
                continue;
            }
            
            if ($rule['exclude_individual_discounts'] && $has_individual_discount) {
                continue;
            }
            
            // Aplicar el descuento o recargo al precio ORIGINAL
            $price = self::calculate_discount($original_price, $rule);
            $applied_rule = $rule;
            break;
        }
        
        unset(self::$processing_products[$product_id]);
        
        // Cachear el resultado
        self::$price_cache[$cache_key] = $price;
        return $price;
    }
    
    /**
     * Aplicar descuentos a variaciones de productos variables
     */
    public static function apply_variation_discount($price, $variation, $product) {
        if (!is_a($variation, 'WC_Product_Variation')) {
            return $price;
        }
        
        // Evitar bucles infinitos para variaciones
        $variation_id = $variation->get_id();
        if (isset(self::$processing_products[$variation_id])) {
            return $price;
        }
        
        return self::apply_category_discount($price, $variation);
    }
    
    /**
     * Verificación de descuentos individuales
     */
    public static function has_individual_discount($product) {
        if ($product->is_type('variable')) {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_product = wc_get_product($variation['variation_id']);
                if ($variation_product->get_sale_price() && 
                    $variation_product->get_sale_price() !== $variation_product->get_regular_price()) {
                    return true;
                }
            }
            return false;
        }
        
        return ($product->get_sale_price() && $product->get_sale_price() !== $product->get_regular_price());
    }
    
    /**
     * Calcular descuento/recargo
     */
    private static function calculate_discount($original_price, $rule) {
        if ($rule['type'] === 'discount') {
            if ($rule['amount_type'] === 'percentage') {
                return $original_price * (1 - ($rule['amount'] / 100));
            } else {
                return max(0, $original_price - $rule['amount']);
            }
        } else {
            if ($rule['amount_type'] === 'percentage') {
                return $original_price * (1 + ($rule['amount'] / 100));
            } else {
                return $original_price + $rule['amount'];
            }
        }
    }
    
    public static function display_discount_price($price_html, $product) {
        // PARA PRODUCTOS VARIABLES: No modificar el HTML del precio bajo ninguna circunstancia
        // Esto permite que WooCommerce y Elementor manejen la visualización dinámica
        if ($product->is_type('variable')) {
            return $price_html;
        }
        
        // Solo procesar productos simples
        $original_price = $product->get_regular_price();
        $discounted_price = $product->get_price();
        
        // Si no hay cambios o el precio es inválido, retornar el HTML original
        if ($original_price == $discounted_price || $discounted_price <= 0 || empty($discounted_price)) {
            return $price_html;
        }
        
        // Solo mostrar formato de descuento si el precio con descuento es menor
        if ($discounted_price < $original_price) {
            $price_html = '<del>' . wc_price($original_price) . '</del> <ins>' . wc_price($discounted_price) . '</ins>';
        }
        
        return $price_html;
    }
    
    public static function apply_cart_discounts($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        // Evitar procesamiento duplicado
        if (self::$cart_processed) {
            return;
        }
        
        self::$cart_processed = true;
        
        $rules = self::get_discount_rules();
        if (empty($rules)) {
            self::$cart_processed = false;
            return;
        }
        
        usort($rules, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            
            // Solo aplicar si el producto no tiene ya un descuento individual
            if (self::has_individual_discount($product)) {
                continue;
            }
            
            $original_price = $product->get_regular_price();
            
            // Aplicar descuento/recargo
            $discounted_price = self::apply_category_discount($original_price, $product);
            
            // Solo actualizar si el precio cambió y es válido
            if ($original_price != $discounted_price && $discounted_price > 0) {
                $cart_item['data']->set_price($discounted_price);
            }
        }
        
        self::$cart_processed = false;
    }
    
    public static function get_discount_text($product) {
        $rules = self::get_discount_rules();
        if (empty($rules)) {
            return '';
        }
        
        $product_categories = self::get_product_categories($product);
        if (empty($product_categories)) {
            return '';
        }
        
        usort($rules, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        foreach ($rules as $rule) {
            if (in_array($rule['category'], $product_categories)) {
                $has_individual_discount = self::has_individual_discount($product);
                if ($rule['exclude_individual_discounts'] && $has_individual_discount) {
                    continue;
                }
                
                if ($rule['type'] === 'discount') {
                    if ($rule['amount_type'] === 'percentage') {
                        return sprintf(__('Descuento %d%%', 'woocommerce-category-discounts'), $rule['amount']);
                    } else {
                        return sprintf(__('Descuento -$%s', 'woocommerce-category-discounts'), number_format($rule['amount'], 2));
                    }
                } else {
                    if ($rule['amount_type'] === 'percentage') {
                        return sprintf(__('Recargo %d%%', 'woocommerce-category-discounts'), $rule['amount']);
                    } else {
                        return sprintf(__('Recargo +$%s', 'woocommerce-category-discounts'), number_format($rule['amount'], 2));
                    }
                }
            }
        }
        
        return '';
    }
    
    // Obtener reglas con cache
    public static function get_discount_rules() {
        $rules = get_transient('wcd_discount_rules_cache');
        
        if (false === $rules) {
            $rules = get_option(self::$option_name, array());
            set_transient('wcd_discount_rules_cache', $rules, HOUR_IN_SECONDS);
        }
        
        return $rules;
    }
    
    // Obtener categorías del producto con cache
    public static function get_product_categories($product) {
        $product_id = $product->get_id();
        $categories = wp_cache_get('wcd_product_categories_' . $product_id, 'wcd');
        
        if (false === $categories) {
            if ($product->is_type('variation')) {
                $parent_id = $product->get_parent_id();
                $categories = wc_get_product_term_ids($parent_id, 'product_cat');
            } else {
                $categories = wc_get_product_term_ids($product_id, 'product_cat');
            }
            wp_cache_set('wcd_product_categories_' . $product_id, $categories, 'wcd', HOUR_IN_SECONDS);
        }
        
        return $categories;
    }
    
    /**
     * FUNCIÓN CRÍTICA CORREGIDA: Obtener información de descuento para el widget de Elementor
     */
    public static function get_discount_info($product) {
        $rules = self::get_discount_rules();
        if (empty($rules)) {
            return false;
        }
        
        $product_categories = self::get_product_categories($product);
        if (empty($product_categories)) {
            return false;
        }
        
        // Ordenar reglas por prioridad
        usort($rules, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        foreach ($rules as $rule) {
            if (in_array($rule['category'], $product_categories)) {
                // Verificar si el producto tiene descuento individual
                $has_individual_discount = self::has_individual_discount($product);
                
                // Solo excluir este producto específico si tiene descuento individual Y la regla lo indica
                if ($rule['exclude_individual_discounts'] && $has_individual_discount) {
                    // Saltar esta regla pero continuar con otras reglas
                    continue;
                }
                
                // Si llegamos aquí, aplicamos el descuento por categoría
                return array(
                    'amount' => $rule['amount'],
                    'amount_type' => $rule['amount_type'],
                    'type' => $rule['type'],
                    'has_category_discount' => true
                );
            }
        }
        
        return false;
    }
    
    /**
     * NUEVA FUNCIÓN: Manejar petición AJAX para obtener descuento de variación
     */
    public static function get_variation_discount_ajax() {
        check_ajax_referer('wcd_ajax_nonce', 'nonce');
        
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $custom_text = isset($_POST['custom_text']) ? sanitize_text_field($_POST['custom_text']) : '';
        
        if ($variation_id === 0 || $product_id === 0) {
            wp_send_json_error('Datos inválidos');
        }
        
        $variation = wc_get_product($variation_id);
        
        if (!$variation) {
            wp_send_json_error('Variación no encontrada');
        }
        
        // Si hay texto personalizado, usarlo directamente
        if (!empty($custom_text)) {
            wp_send_json_success(array('discount_text' => $custom_text));
        }
        
        // Obtener información del descuento para la variación
        $discount_info = self::get_discount_info($variation);
        $discount_text = '';
        
        // Usar la misma lógica de texto que para productos simples
        if ($discount_info && isset($discount_info['has_category_discount']) && $discount_info['has_category_discount']) {
            if ($discount_info['type'] === 'discount') {
                if ($discount_info['amount_type'] === 'percentage') {
                    $discount_text = sprintf(__('-%d%% de descuento', 'woocommerce-category-discounts'), $discount_info['amount']);
                } else {
                    $discount_text = sprintf(__('-$%s de descuento', 'woocommerce-category-discounts'), number_format($discount_info['amount'], 2));
                }
            } else {
                // Para recargos
                if ($discount_info['amount_type'] === 'percentage') {
                    $discount_text = sprintf(__('Recargo %d%%', 'woocommerce-category-discounts'), $discount_info['amount']);
                } else {
                    $discount_text = sprintf(__('Recargo +$%s', 'woocommerce-category-discounts'), number_format($discount_info['amount'], 2));
                }
            }
        } elseif (self::has_individual_discount($variation)) {
            $discount_text = __('Producto en Oferta', 'woocommerce-category-discounts');
        }
        
        wp_send_json_success(array('discount_text' => $discount_text));
    }
    
    // Limpiar cache
    public static function clear_cache() {
        delete_transient('wcd_discount_rules_cache');
        self::$price_cache = array();
    }
    
    // Nueva función para limpiar todos los caches - CORREGIDA
    public static function clear_all_caches() {
        delete_transient('wcd_discount_rules_cache');
        self::$price_cache = array();
        wp_cache_flush();
        if (function_exists('wc_delete_product_transients')) {
            wc_delete_product_transients();
        }
    }
}