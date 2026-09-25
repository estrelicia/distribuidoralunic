<?php
/**
 * Plugin Name: Envíos Personalizados WooCommerce
 * Description: Nuevo método de envío con opciones avanzadas y visualización modificada
 * Version: 1.8
 * Author: WSB agencia digital
 * Author URI: https://watsabi.com
 * Text Domain: custom-shipping
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

defined('ABSPATH') || exit;

// Definir constantes
define('CUSTOM_SHIPPING_VERSION', '1.0.6');
define('CUSTOM_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CUSTOM_SHIPPING_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CUSTOM_SHIPPING_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Incluir clase de activación primero
require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-plugin-activation.php';

// Declarar compatibilidad con HPOS
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

// Verificar si WooCommerce está activo
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Hook para inicializar el plugin
    add_action('woocommerce_init', 'custom_shipping_init');

    function custom_shipping_init() {
        require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-custom-shipping-plugin.php';
        require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-wc-custom-shipping-method.php';
        require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-admin-settings.php';
        require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-elementor-compatibility.php';
        require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-hpos-compatibility.php';
        require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-shipping-label-fix.php';
        require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-elementor-pro-compatibility.php'; // NUEVA COMPATIBILIDAD

        new CustomShippingPlugin();
    }

    // Hook para cargar el método de envío
    add_action('woocommerce_shipping_init', 'custom_shipping_method_init');

    function custom_shipping_method_init() {
        if (!class_exists('WC_Custom_Shipping_Method')) {
            require_once CUSTOM_SHIPPING_PLUGIN_PATH . 'includes/class-wc-custom-shipping-method.php';
        }
    }

    // Añadir método a la lista de métodos de envío
    add_filter('woocommerce_shipping_methods', 'add_custom_shipping_method');

    function add_custom_shipping_method($methods) {
        $methods['custom_shipping'] = 'WC_Custom_Shipping_Method';
        return $methods;
    }

} else {
    // Mostrar aviso si WooCommerce no está activo
    add_action('admin_notices', 'custom_shipping_woocommerce_missing_notice');
    function custom_shipping_woocommerce_missing_notice() {
        echo '<div class="error"><p>' . sprintf(
            __('Envíos Personalizados requiere WooCommerce. %s', 'custom-shipping'), 
            '<a href="' . admin_url('plugin-install.php?tab=search&s=woocommerce&plugin-search-input=Search+Plugins') . '">' . __('Instalar WooCommerce', 'custom-shipping') . '</a>'
        ) . '</p></div>';
    }
}

// Registrar hooks de activación
register_activation_hook(__FILE__, array('CustomShipping_Activation', 'activate'));
register_deactivation_hook(__FILE__, array('CustomShipping_Activation', 'deactivate'));