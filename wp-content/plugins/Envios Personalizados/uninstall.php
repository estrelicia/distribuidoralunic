<?php
/**
 * Uninstall Custom Shipping Plugin
 * 
 * @package CustomShipping
 */

// Verificar que se llama desde WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Eliminar opciones de WordPress
delete_option('wc_custom_shipping_settings');
delete_option('custom_shipping_version');
delete_option('custom_shipping_activation_date');

// Eliminar metadatos de pedidos
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_carry_to_carrier'");
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_custom_shipping_%'");

// Eliminar tablas personalizadas si existen
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}custom_shipping_logs");