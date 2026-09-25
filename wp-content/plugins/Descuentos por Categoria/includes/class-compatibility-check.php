<?php
class WCD_Compatibility_Check {
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'check_woocommerce_status'));
    }
    
    public static function check_woocommerce_status() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            add_action('admin_notices', array(__CLASS__, 'woocommerce_deactivated_notice'));
            
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    }
    
    public static function woocommerce_deactivated_notice() {
        echo '<div class="error"><p>';
        echo esc_html__('WooCommerce Category Discounts ha sido desactivado porque requiere WooCommerce.', 'woocommerce-category-discounts');
        echo '</p></div>';
    }
    
    public static function check_elementor_pro_status() {
        if (!did_action('elementor/loaded')) {
            return false;
        }
        
        return true;
    }
}