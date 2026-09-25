<?php
/**
 * HPOS Compatibility for Custom Shipping Plugin
 * 
 * @package CustomShipping
 */

defined('ABSPATH') || exit;

class CustomShipping_HPOS_Compatibility {
    
    public function __construct() {
        // Declarar compatibilidad con HPOS
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));
        
        // Asegurar que los meta datos sean compatibles con HPOS
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', array($this, 'handle_custom_query_var'), 10, 2);
    }
    
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', CUSTOM_SHIPPING_PLUGIN_BASENAME, true);
        }
    }
    
    public function handle_custom_query_var($query, $query_vars) {
        if (!empty($query_vars['_carry_to_carrier'])) {
            $query['meta_query'][] = array(
                'key' => '_carry_to_carrier',
                'value' => esc_attr($query_vars['_carry_to_carrier']),
                'compare' => '='
            );
        }
        
        return $query;
    }
    
    public static function install() {
        // Asegurar que las tablas custom sean compatibles con HPOS
        if (class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')) {
            // Registrar meta datos para HPOS
            add_action('woocommerce_after_register_post_type', function() {
                if (function_exists('wc_register_order_type')) {
                    wc_register_order_type(
                        'shop_order',
                        array(
                            'meta_keys' => array(
                                '_carry_to_carrier',
                            )
                        )
                    );
                }
            });
        }
    }
}

new CustomShipping_HPOS_Compatibility();

// Hook de activación para HPOS
register_activation_hook(CUSTOM_SHIPPING_PLUGIN_BASENAME, array('CustomShipping_HPOS_Compatibility', 'install'));