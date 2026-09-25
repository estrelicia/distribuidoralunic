<?php
class WCD_Admin_Interface {
    private static $option_name = 'wcd_discount_rules';
    
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
        
        // Limpiar cache cuando se guarden nuevas reglas
        add_action('update_option_' . self::$option_name, array(__CLASS__, 'clear_rules_cache'), 10, 2);
    }
    
    public static function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Descuentos por Categoría', 'woocommerce-category-discounts'),
            __('Descuentos por Categoría', 'woocommerce-category-discounts'),
            'manage_woocommerce',
            'wcd-category-discounts',
            array(__CLASS__, 'render_admin_page')
        );
    }
    
    public static function register_settings() {
        register_setting('wcd_settings_group', self::$option_name, array(
            'sanitize_callback' => array(__CLASS__, 'sanitize_rules')
        ));
    }
    
    public static function sanitize_rules($rules) {
        if (!is_array($rules)) {
            return array();
        }
        
        $sanitized_rules = array();
        foreach ($rules as $index => $rule) {
            // Asegurarse de que cada regla tenga todos los campos necesarios
            if (!isset($rule['category']) || empty($rule['category'])) {
                continue; // Saltar reglas inválidas
            }
            
            $sanitized_rule = array(
                'category' => absint($rule['category']),
                'type' => in_array($rule['type'], array('discount', 'surcharge')) ? $rule['type'] : 'discount',
                'amount_type' => in_array($rule['amount_type'], array('fixed', 'percentage')) ? $rule['amount_type'] : 'percentage',
                'amount' => floatval($rule['amount']),
                'priority' => absint($rule['priority']),
                'exclude_individual_discounts' => isset($rule['exclude_individual_discounts']) ? 1 : 0
            );
            
            $sanitized_rules[] = $sanitized_rule;
        }
        
        return $sanitized_rules;
    }
    
    public static function enqueue_admin_scripts($hook) {
        if ($hook !== 'woocommerce_page_wcd-category-discounts') {
            return;
        }
        
        wp_enqueue_style('wcd-admin-style', WCD_PLUGIN_URL . 'assets/css/admin.css', array(), WCD_VERSION);
        wp_enqueue_script('wcd-admin-script', WCD_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WCD_VERSION, true);
    }
    
    public static function render_admin_page() {
        $rules = get_option(self::$option_name, array());
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'cache_results' => true // Habilitar cache para términos
        ));
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Descuentos por Categoría', 'woocommerce-category-discounts'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('wcd_settings_group'); ?>
                
                <table class="widefat wcd-rules-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Categoría', 'woocommerce-category-discounts'); ?></th>
                            <th><?php esc_html_e('Tipo', 'woocommerce-category-discounts'); ?></th>
                            <th><?php esc_html_e('Tipo de Monto', 'woocommerce-category-discounts'); ?></th>
                            <th><?php esc_html_e('Monto', 'woocommerce-category-discounts'); ?></th>
                            <th><?php esc_html_e('Prioridad', 'woocommerce-category-discounts'); ?></th>
                            <th><?php esc_html_e('Excluir descuentos individuales', 'woocommerce-category-discounts'); ?></th>
                            <th><?php esc_html_e('Acciones', 'woocommerce-category-discounts'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wcd-rules-body">
                        <?php if (empty($rules)): ?>
                            <tr class="no-rules">
                                <td colspan="7"><?php esc_html_e('No hay reglas configuradas.', 'woocommerce-category-discounts'); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rules as $index => $rule): ?>
                                <tr class="wcd-rule-row">
                                    <td>
                                        <select name="wcd_discount_rules[<?php echo $index; ?>][category]" required>
                                            <option value=""><?php esc_html_e('Seleccionar categoría', 'woocommerce-category-discounts'); ?></option>
                                            <?php foreach ($product_categories as $category): ?>
                                                <option value="<?php echo $category->term_id; ?>" <?php selected($rule['category'], $category->term_id); ?>>
                                                    <?php echo esc_html($category->name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="wcd_discount_rules[<?php echo $index; ?>][type]">
                                            <option value="discount" <?php selected($rule['type'], 'discount'); ?>>
                                                <?php esc_html_e('Descuento', 'woocommerce-category-discounts'); ?>
                                            </option>
                                            <option value="surcharge" <?php selected($rule['type'], 'surcharge'); ?>>
                                                <?php esc_html_e('Recargo', 'woocommerce-category-discounts'); ?>
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="wcd_discount_rules[<?php echo $index; ?>][amount_type]">
                                            <option value="percentage" <?php selected($rule['amount_type'], 'percentage'); ?>>
                                                <?php esc_html_e('Porcentaje', 'woocommerce-category-discounts'); ?>
                                            </option>
                                            <option value="fixed" <?php selected($rule['amount_type'], 'fixed'); ?>>
                                                <?php esc_html_e('Monto Fijo', 'woocommerce-category-discounts'); ?>
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" 
                                               name="wcd_discount_rules[<?php echo $index; ?>][amount]" 
                                               value="<?php echo esc_attr($rule['amount']); ?>" required>
                                    </td>
                                    <td>
                                        <input type="number" min="1" 
                                               name="wcd_discount_rules[<?php echo $index; ?>][priority]" 
                                               value="<?php echo esc_attr($rule['priority']); ?>" required>
                                    </td>
                                    <td>
                                        <input type="checkbox" 
                                               name="wcd_discount_rules[<?php echo $index; ?>][exclude_individual_discounts]" 
                                               value="1" <?php checked($rule['exclude_individual_discounts'], 1); ?>>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-secondary wcd-remove-rule">
                                            <?php esc_html_e('Eliminar', 'woocommerce-category-discounts'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <button type="button" id="wcd-add-rule" class="button button-primary">
                                    <?php esc_html_e('Agregar Regla', 'woocommerce-category-discounts'); ?>
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <!-- Template para nuevas filas -->
            <script type="text/template" id="wcd-rule-template">
                <tr class="wcd-rule-row">
                    <td>
                        <select name="wcd_discount_rules[{index}][category]" required>
                            <option value=""><?php esc_html_e('Seleccionar categoría', 'woocommerce-category-discounts'); ?></option>
                            <?php foreach ($product_categories as $category): ?>
                                <option value="<?php echo $category->term_id; ?>">
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="wcd_discount_rules[{index}][type]">
                            <option value="discount"><?php esc_html_e('Descuento', 'woocommerce-category-discounts'); ?></option>
                            <option value="surcharge"><?php esc_html_e('Recargo', 'woocommerce-category-discounts'); ?></option>
                        </select>
                    </td>
                    <td>
                        <select name="wcd_discount_rules[{index}][amount_type]">
                            <option value="percentage"><?php esc_html_e('Porcentaje', 'woocommerce-category-discounts'); ?></option>
                            <option value="fixed"><?php esc_html_e('Monto Fijo', 'woocommerce-category-discounts'); ?></option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="wcd_discount_rules[{index}][amount]" value="" required>
                    </td>
                    <td>
                        <input type="number" min="1" name="wcd_discount_rules[{index}][priority]" value="1" required>
                    </td>
                    <td>
                        <input type="checkbox" name="wcd_discount_rules[{index}][exclude_individual_discounts]" value="1">
                    </td>
                    <td>
                        <button type="button" class="button button-secondary wcd-remove-rule">
                            <?php esc_html_e('Eliminar', 'woocommerce-category-discounts'); ?>
                        </button>
                    </td>
                </tr>
            </script>
        </div>
        <?php
    }
    
    public static function clear_rules_cache($old_value, $new_value) {
        WCD_Price_Calculator::clear_cache();
    }
}