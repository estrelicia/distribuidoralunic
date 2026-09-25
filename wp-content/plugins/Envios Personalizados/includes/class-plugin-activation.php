<?php
/**
 * Clase de activación y desactivación del plugin
 * 
 * @package CustomShipping
 */

defined('ABSPATH') || exit;

class CustomShipping_Activation {
    
    public static function activate() {
        // Verificar dependencias
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('Este plugin requiere WooCommerce. Por favor, instala y activa WooCommerce primero.', 'custom-shipping'));
        }
        
        // Crear tablas personalizadas si es necesario
        self::create_tables();
        
        // Programar eventos cron si es necesario
        self::schedule_events();
        
        // Guardar versión y fecha de activación
        update_option('custom_shipping_version', CUSTOM_SHIPPING_VERSION);
        update_option('custom_shipping_activation_date', current_time('mysql'));
        
        // Verificar compatibilidad con HPOS
        self::check_hpos_compatibility();
    }
    
    public static function deactivate() {
        // Limpiar eventos cron
        wp_clear_scheduled_hook('custom_shipping_daily_event');
        
        // Limpiar cualquier otra configuración temporal si es necesario
        delete_option('custom_shipping_activation_date');
    }
    
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$wpdb->prefix}custom_shipping_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            message text NOT NULL,
            type varchar(20) DEFAULT 'info' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private static function schedule_events() {
        if (!wp_next_scheduled('custom_shipping_daily_event')) {
            wp_schedule_event(time(), 'daily', 'custom_shipping_daily_event');
        }
    }
    
    private static function check_hpos_compatibility() {
        // Verificar si HPOS está activo y mostrar aviso si es necesario
        if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            $hpos_enabled = Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled('custom_order_tables');
            
            if ($hpos_enabled) {
                // Registrar en log que HPOS está activo
                error_log('Custom Shipping Plugin: HPOS está activado en este sitio.');
            }
        }
    }
}