<?php
/**
 * Plugin Name: Distribuidora Lunic
 * Description: Lógica de tienda Lunic: envíos, descuentos, checkout y módulos de catálogo.
 * Version: 0.1.0
 * Author: Distribuidora Lunic
 * Text Domain: distribuidora-lunic
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 11.1
 */

defined('ABSPATH') || exit;

define('DISTRIBUIDORA_LUNIC_VERSION', '0.1.0');
define('DISTRIBUIDORA_LUNIC_FILE', __FILE__);
define('DISTRIBUIDORA_LUNIC_PATH', plugin_dir_path(__FILE__));
define('DISTRIBUIDORA_LUNIC_URL', plugin_dir_url(__FILE__));
define('DISTRIBUIDORA_LUNIC_BASENAME', plugin_basename(__FILE__));

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            DISTRIBUIDORA_LUNIC_FILE,
            true
        );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            DISTRIBUIDORA_LUNIC_FILE,
            true
        );
    }
});

require_once DISTRIBUIDORA_LUNIC_PATH . 'includes/class-plugin.php';

add_action('plugins_loaded', function () {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            if (!current_user_can('activate_plugins')) {
                return;
            }
            echo '<div class="notice notice-error"><p>';
            esc_html_e(
                'Distribuidora Lunic requiere WooCommerce activo.',
                'distribuidora-lunic'
            );
            echo '</p></div>';
        });
        return;
    }

    \Distribuidora_Lunic\Plugin::instance()->init();
}, 20);
