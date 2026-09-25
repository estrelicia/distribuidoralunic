<?php
/**
 * Clase para configuraciones administrativas
 * 
 * @package CustomShipping
 */

defined('ABSPATH') || exit;

class CustomShipping_Admin_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_notices', array($this, 'show_hpos_notice'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Configuración Envíos Personalizados', 'custom-shipping'),
            __('Envíos Personalizados', 'custom-shipping'),
            'manage_options',
            'custom-shipping',
            array($this, 'options_page')
        );
    }
    
    public function settings_init() {
        register_setting('custom_shipping', 'custom_shipping_settings');
        
        add_settings_section(
            'custom_shipping_section',
            __('Configuración General', 'custom-shipping'),
            array($this, 'settings_section_callback'),
            'custom_shipping'
        );
        
        add_settings_field(
            'debug_mode',
            __('Modo Depuración', 'custom-shipping'),
            array($this, 'debug_mode_render'),
            'custom_shipping',
            'custom_shipping_section'
        );
        
        add_settings_field(
            'enable_logging',
            __('Habilitar Logging', 'custom-shipping'),
            array($this, 'enable_logging_render'),
            'custom_shipping',
            'custom_shipping_section'
        );
        
        add_settings_field(
            'hpos_compatibility',
            __('Compatibilidad HPOS', 'custom-shipping'),
            array($this, 'hpos_compatibility_render'),
            'custom_shipping',
            'custom_shipping_section'
        );
    }
    
    public function debug_mode_render() {
        $options = get_option('custom_shipping_settings');
        ?>
        <input type="checkbox" name="custom_shipping_settings[debug_mode]" <?php checked(isset($options['debug_mode']), true); ?> value="1">
        <p class="description"><?php _e('Habilita el modo depuración para mostrar información adicional.', 'custom-shipping'); ?></p>
        <?php
    }
    
    public function enable_logging_render() {
        $options = get_option('custom_shipping_settings');
        ?>
        <input type="checkbox" name="custom_shipping_settings[enable_logging]" <?php checked(isset($options['enable_logging']), true); ?> value="1">
        <p class="description"><?php _e('Registra eventos del plugin para depuración.', 'custom-shipping'); ?></p>
        <?php
    }
    
    public function hpos_compatibility_render() {
        // SOLUCIÓN: No llamar a declare_compatibility aquí, solo verificar el estado
        $compatibility_status = $this->check_hpos_compatibility();
        ?>
        <div class="hpos-compatibility-status">
            <?php if ($compatibility_status) : ?>
                <span style="color: green;">✓ <?php _e('Compatible con HPOS', 'custom-shipping'); ?></span>
            <?php else : ?>
                <span style="color: red;">✗ <?php _e('No compatible con HPOS', 'custom-shipping'); ?></span>
            <?php endif; ?>
        </div>
        <p class="description"><?php _e('Estado de compatibilidad con High-Performance Order Storage (HPOS) de WooCommerce.', 'custom-shipping'); ?></p>
        <?php
    }
    
    // NUEVO MÉTODO: Verificar compatibilidad sin declararla
    private function check_hpos_compatibility() {
        if (!class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            return false;
        }
        
        // Verificar si ya está declarada la compatibilidad
        $compatible_plugins = get_option('woocommerce_feature_custom_order_tables_compatible_plugins', array());
        return in_array(CUSTOM_SHIPPING_PLUGIN_BASENAME, $compatible_plugins);
    }
    
    public function show_hpos_notice() {
        // SOLUCIÓN: No llamar a declare_compatibility en admin_notices
        if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            $hpos_enabled = Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled('custom_order_tables');
            $compatibility_status = $this->check_hpos_compatibility();
            
            if ($hpos_enabled && !$compatibility_status) {
                echo '<div class="notice notice-warning"><p>';
                _e('El plugin Envíos Personalizados no es compatible con la característica activa de WooCommerce «Almacenamiento de pedidos de alto rendimiento».', 'custom-shipping');
                echo ' <a href="' . admin_url('admin.php?page=custom-shipping') . '">' . __('Ver detalles', 'custom-shipping') . '</a>';
                echo '</p></div>';
            }
        }
    }
    
    public function settings_section_callback() {
        echo __('Configura las opciones generales del plugin de envíos personalizados.', 'custom-shipping');
    }
    
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Configuración Envíos Personalizados', 'custom-shipping'); ?></h1>
            
            <?php if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) : ?>
                <?php $hpos_enabled = Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled('custom_order_tables'); ?>
                <?php if ($hpos_enabled) : ?>
                    <div class="notice notice-info">
                        <p><?php _e('HPOS (High-Performance Order Storage) está activado en este sitio. El plugin es compatible con esta característica.', 'custom-shipping'); ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('custom_shipping');
                do_settings_sections('custom_shipping');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

new CustomShipping_Admin_Settings();