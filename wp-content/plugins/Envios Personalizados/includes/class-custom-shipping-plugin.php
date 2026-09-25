<?php
defined('ABSPATH') || exit;

class CustomShippingPlugin {
    public function __construct() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        $this->init();
    }
    
    public function init() {
        load_plugin_textdomain('custom-shipping', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // CORRECCIÓN: Agregar el método faltante
        add_action('wp_loaded', array($this, 'remove_default_shipping_display'));
        add_action('woocommerce_cart_totals_after_order_total', array($this, 'display_shipping_after_total'));
        add_action('woocommerce_review_order_after_order_total', array($this, 'display_shipping_after_total_checkout'));
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('woocommerce_locate_template', array($this, 'custom_locate_template'), 10, 3);
        
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // SOLUCIÓN CORREGIDA: Excluir envío del total
        add_action('woocommerce_before_calculate_totals', array($this, 'remove_shipping_from_total'), 1000);
        add_filter('woocommerce_calculated_total', array($this, 'adjust_calculated_total'), 1000, 2);
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_order_shipping_separate'));
        
        // Mantener para mostrar info en admin
        add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'display_carry_info_in_admin'));
        add_filter('woocommerce_get_order_item_totals', array($this, 'adjust_order_item_totals'), 10, 3);
    }
    
    // CORRECCIÓN: Agregar el método que faltaba
    public function remove_default_shipping_display() {
        remove_action('woocommerce_cart_totals_before_order_total', 'woocommerce_cart_totals_shipping_html');
        remove_action('woocommerce_review_order_before_order_total', 'woocommerce_review_order_shipping');
    }
    
    public function display_shipping_after_total() {
        if (!WC()->cart->needs_shipping() || !WC()->cart->show_shipping()) {
            return;
        }
        
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        $shipping_label = '';
        $shipping_cost = 0;
        
        if (!empty($chosen_shipping_methods)) {
            $shipping_packages = WC()->shipping()->get_packages();
            
            foreach ($shipping_packages as $i => $package) {
                $chosen_method = isset($chosen_shipping_methods[$i]) ? $chosen_shipping_methods[$i] : '';
                
                if (strpos($chosen_method, 'custom_shipping') !== false) {
                    $method = isset($package['rates'][$chosen_method]) ? $package['rates'][$chosen_method] : null;
                    
                    if ($method) {
                        $shipping_label = $method->get_label();
                        $shipping_cost = $method->get_cost();
                        break;
                    }
                }
            }
        }
        
        if ($shipping_label) {
            echo '<tr class="shipping-after-total">';
            echo '<th>' . __('Envío', 'custom-shipping') . '</th>';
            echo '<td data-title="' . esc_attr__('Envío', 'custom-shipping') . '">';
            echo '<strong>' . $shipping_label . '</strong>';
            echo '</td>';
            echo '</tr>';
        }
    }
    
    public function display_shipping_after_total_checkout() {
        if (!WC()->cart->needs_shipping()) {
            return;
        }
        
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        $shipping_label = '';
        $shipping_cost = 0;
        
        if (!empty($chosen_shipping_methods)) {
            $shipping_packages = WC()->shipping()->get_packages();
            
            foreach ($shipping_packages as $i => $package) {
                $chosen_method = isset($chosen_shipping_methods[$i]) ? $chosen_shipping_methods[$i] : '';
                
                if (strpos($chosen_method, 'custom_shipping') !== false) {
                    $method = isset($package['rates'][$chosen_method]) ? $package['rates'][$chosen_method] : null;
                    
                    if ($method) {
                        $shipping_label = $method->get_label();
                        $shipping_cost = $method->get_cost();
                        break;
                    }
                }
            }
        }
        
        if ($shipping_label) {
            echo '<tr class="shipping-after-total">';
            echo '<th>' . __('Envío', 'custom-shipping') . '</th>';
            echo '<td>';
            echo '<strong>' . $shipping_label . '</strong>';
            echo '</td>';
            echo '</tr>';
        }
    }
    
    /**
     * SOLUCIÓN PRINCIPAL: Remover el shipping del cálculo del total
     */
    public function remove_shipping_from_total($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        // Solo procesar si nuestro método de envío está activo
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        $has_custom_shipping = false;
        
        if (!empty($chosen_shipping_methods)) {
            foreach ($chosen_shipping_methods as $method) {
                if (strpos($method, 'custom_shipping') !== false) {
                    $has_custom_shipping = true;
                    break;
                }
            }
        }
        
        if (!$has_custom_shipping) {
            return;
        }
        
        // Guardar el costo de envío original
        $shipping_total = $cart->get_shipping_total();
        
        if ($shipping_total > 0) {
            // Guardar el shipping total en session para usarlo después
            WC()->session->set('custom_shipping_total', $shipping_total);
            
            // Establecer el shipping total a 0 para que no afecte el total
            $cart->set_shipping_total(0);
            
            // También establecer los impuestos de envío a 0
            $cart->set_shipping_tax(0);
        }
    }
    
    /**
     * Ajustar el total calculado para mantenerlo sin envío
     */
    public function adjust_calculated_total($total, $cart) {
        // Solo ajustar si tenemos nuestro método de envío
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        $has_custom_shipping = false;
        
        if (!empty($chosen_shipping_methods)) {
            foreach ($chosen_shipping_methods as $method) {
                if (strpos($method, 'custom_shipping') !== false) {
                    $has_custom_shipping = true;
                    break;
                }
            }
        }
        
        if (!$has_custom_shipping) {
            return $total;
        }
        
        // El total ya no incluye el shipping porque lo establecimos a 0
        // Pero necesitamos asegurarnos de que no se agregue de otra forma
        $shipping_total = WC()->session->get('custom_shipping_total', 0);
        
        // Si por alguna razón el total incluye el shipping, lo removemos
        $calculated_total = $cart->get_subtotal() + $cart->get_total_tax();
        
        // Aplicar descuentos
        $calculated_total -= $cart->get_discount_total();
        
        return $calculated_total;
    }
    
    /**
     * Guardar información del envío por separado en la orden
     */
    public function save_order_shipping_separate($order_id) {
        $order = wc_get_order($order_id);
        $shipping_total = WC()->session->get('custom_shipping_total', 0);
        
        if ($shipping_total > 0) {
            // Guardar el shipping total real como meta
            $order->update_meta_data('_real_shipping_total', $shipping_total);
            $order->save();
        }
    }
    
    public function adjust_order_item_totals($total_rows, $order, $tax_display) {
        $shipping_methods = $order->get_shipping_methods();
        
        if (!empty($shipping_methods)) {
            $shipping_method = reset($shipping_methods);
            $shipping_label = $shipping_method->get_name();
            
            if (isset($total_rows['shipping'])) {
                $total_rows['shipping']['value'] = '<strong>' . $shipping_label . '</strong>';
            }
        }
        
        return $total_rows;
    }
    
    public function display_carry_info_in_admin($order) {
        $shipping_methods = $order->get_shipping_methods();
        if (!empty($shipping_methods)) {
            $shipping_method = reset($shipping_methods);
            $method_name = $shipping_method->get_method_title();
            
            if (strpos($method_name, 'Acarreo') !== false) {
                echo '<div class="order_data_column">';
                echo '<h3>' . __('Información de Envío', 'custom-shipping') . '</h3>';
                echo '<p><strong>' . __('Tipo:', 'custom-shipping') . '</strong> ' . __('Acarreo', 'custom-shipping') . '</p>';
                
                // Mostrar el costo real del envío si existe
                $real_shipping = $order->get_meta('_real_shipping_total');
                if ($real_shipping) {
                    echo '<p><strong>' . __('Costo real de envío:', 'custom-shipping') . '</strong> ' . wc_price($real_shipping) . '</p>';
                }
                echo '</div>';
            }
        }
    }
    
    public function enqueue_scripts() {
        if (is_checkout() || is_cart()) {
            wp_enqueue_script('custom-shipping-js', CUSTOM_SHIPPING_PLUGIN_URL . 'assets/js/custom-shipping.js', array('jquery', 'wc-checkout'), CUSTOM_SHIPPING_VERSION, true);
            wp_enqueue_style('custom-shipping-css', CUSTOM_SHIPPING_PLUGIN_URL . 'assets/css/custom-shipping.css', array(), CUSTOM_SHIPPING_VERSION);
            
            wp_localize_script('custom-shipping-js', 'custom_shipping_params', array(
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
    }
    
    public function enqueue_admin_scripts() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'woocommerce_page_wc-settings') {
            wp_enqueue_style('custom-shipping-admin-css', CUSTOM_SHIPPING_PLUGIN_URL . 'assets/css/admin.css', array(), CUSTOM_SHIPPING_VERSION);
        }
    }
    
    public function custom_locate_template($template, $template_name, $template_path) {
        $plugin_path = CUSTOM_SHIPPING_PLUGIN_PATH . 'templates/';
        if (file_exists($plugin_path . $template_name)) {
            return $plugin_path . $template_name;
        }
        return $template;
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p>' . sprintf(
            __('Envíos Personalizados requiere WooCommerce. %s', 'custom-shipping'), 
            '<a href="' . admin_url('plugin-install.php?tab=search&s=woocommerce&plugin-search-input=Search+Plugins') . '">' . __('Instalar WooCommerce', 'custom-shipping') . '</a>'
        ) . '</p></div>';
    }
}