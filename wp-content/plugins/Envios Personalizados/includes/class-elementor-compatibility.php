<?php
/**
 * Clase para compatibilidad con Elementor Pro
 * 
 * @package CustomShipping
 */

defined('ABSPATH') || exit;

class CustomShipping_Elementor_Compatibility {
    
    public function __construct() {
        add_action('elementor_pro/init', array($this, 'init_elementor_support'));
        add_action('elementor/theme/register_locations', array($this, 'register_elementor_locations'));
        
        // Añadir soporte para HPOS en widgets de Elementor
        add_filter('elementor/woocommerce/orders_query_args', array($this, 'modify_elementor_orders_query'));
    }
    
    public function init_elementor_support() {
        // Añadir soporte para widgets de Elementor
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        
        // Asegurar que los envíos se muestren correctamente en widgets de Elementor
        add_filter('elementor/woocommerce/checkout/review_order_shipping', array($this, 'elementor_shipping_display'));
        
        // Modificar la visualización en el widget de carrito de Elementor
        add_filter('elementor/woocommerce/cart/totals', array($this, 'elementor_cart_totals'));
    }
    
    public function register_elementor_locations($elementor_theme_manager) {
        // Registrar ubicaciones para templates personalizados
        $elementor_theme_manager->register_location('cart');
        $elementor_theme_manager->register_location('checkout');
    }
    
    public function register_widgets($widgets_manager) {
        // Registrar widgets personalizados si es necesario
        // require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/elementor/class-custom-shipping-widget.php';
        // $widgets_manager->register(new \Elementor\Custom_Shipping_Widget());
    }
    
    public function elementor_shipping_display($shipping_html) {
        // Modificar la visualización de envíos en widgets de Elementor
        ob_start();
        ?>
        <tr class="shipping-after-total">
            <th><?php esc_html_e('Envío', 'custom-shipping'); ?></th>
            <td>
                <?php wc_cart_totals_shipping_html(); ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }
    
    public function elementor_cart_totals($totals_html) {
        // Modificar los totales del carrito en widgets de Elementor
        ob_start();
        ?>
        <div class="elementor-cart-totals">
            <?php 
            // Mostrar subtotal
            echo '<div class="cart-subtotal">';
            echo '<strong>' . esc_html__('Subtotal', 'woocommerce') . ':</strong> ';
            echo '<span>' . WC()->cart->get_cart_subtotal() . '</span>';
            echo '</div>';
            
            // Mostrar total (sin envío)
            echo '<div class="order-total">';
            echo '<strong>' . esc_html__('Total', 'woocommerce') . ':</strong> ';
            echo '<span>' . wc_price(WC()->cart->get_total('edit')) . '</span>';
            echo '</div>';
            
            // Mostrar envío después del total
            if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {
                echo '<div class="shipping-after-total">';
                echo '<strong>' . esc_html__('Envío', 'custom-shipping') . ':</strong> ';
                wc_cart_totals_shipping_html();
                echo '</div>';
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function modify_elementor_orders_query($query_args) {
        // Asegurar compatibilidad con HPOS en consultas de pedidos de Elementor
        if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil') && 
            Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled('custom_order_tables')) {
            
            // Usar el sistema de consultas de pedidos de WooCommerce para HPOS
            $query_args['type'] = 'shop_order';
        }
        
        return $query_args;
    }
}

new CustomShipping_Elementor_Compatibility();