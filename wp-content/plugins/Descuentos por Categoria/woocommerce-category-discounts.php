<?php
/**
 * Plugin Name: Descuentos por Categoría para Woocommerce
 * Description: Plugin para aplicar descuentos o recargos por categoría de producto.
 * Version: 1.1
 * Author: WSB agencia digital
 * Author URI: https://watsabi.com
 * Text Domain: woocommerce-category-discounts
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

defined('ABSPATH') || exit;

// Definir constantes del plugin
define('WCD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCD_VERSION', '1.0.4');

// Declarar compatibilidad con HPOS (High-Performance Order Storage)
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Aumentar límite de memoria para PHP
function wcd_increase_memory_limit() {
    if (defined('WCD_MEMORY_LIMIT')) {
        @ini_set('memory_limit', WCD_MEMORY_LIMIT);
    }
}
add_action('init', 'wcd_increase_memory_limit');

// Manejar errores de shutdown
register_shutdown_function('wcd_shutdown_handler');
function wcd_shutdown_handler() {
    $error = error_get_last();
    if ($error && $error['type'] === E_ERROR) {
        error_log('WCD Plugin Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
    }
}

// Verificar dependencias
add_action('plugins_loaded', 'wcd_init_plugin');
function wcd_init_plugin() {
    // Verificar si WooCommerce está activo
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wcd_woocommerce_missing_notice');
        return;
    }
    
    // Cargar archivos del plugin
    require_once WCD_PLUGIN_PATH . 'includes/class-admin-interface.php';
    require_once WCD_PLUGIN_PATH . 'includes/class-price-calculator.php';
    require_once WCD_PLUGIN_PATH . 'includes/class-compatibility-check.php';
    
    // Inicializar componentes
    WCD_Admin_Interface::init();
    WCD_Price_Calculator::init();
    
    // Inicializar widget de Elementor solo si está disponible
    add_action('elementor/init', function() {
        if (did_action('elementor/loaded') && class_exists('\Elementor\Widget_Base')) {
            require_once WCD_PLUGIN_PATH . 'includes/class-elementor-widget.php';
            WCD_Elementor_Widget::init();
        }
    });
    
    // Cargar traducciones
    load_plugin_textdomain('woocommerce-category-discounts', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Mostrar aviso si WooCommerce no está activo
function wcd_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>' . 
         esc_html__('WooCommerce Category Discounts requiere que WooCommerce esté instalado y activado.', 'woocommerce-category-discounts') . 
         '</strong></p></div>';
}

// Mostrar aviso si Elementor Pro no está activo
function wcd_elementor_missing_notice() {
    echo '<div class="notice notice-warning"><p><strong>' . 
         esc_html__('WooCommerce Category Discounts: El widget de Elementor no estará disponible porque Elementor Pro no está activado.', 'woocommerce-category-discounts') . 
         '</strong></p></div>';
}

// Activación del plugin
register_activation_hook(__FILE__, 'wcd_activate_plugin');
function wcd_activate_plugin() {
    // Crear tabla en la base de datos si es necesario
    // o establecer opciones por defecto
}

// Desactivación del plugin
register_deactivation_hook(__FILE__, 'wcd_deactivate_plugin');
function wcd_deactivate_plugin() {
    // Limpiar opciones o cron jobs si es necesario
    WCD_Price_Calculator::clear_all_caches();
}