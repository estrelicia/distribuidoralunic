<?php

namespace Distribuidora_Lunic\Modules\Discounts;

defined('ABSPATH') || exit;

class Admin {

    public const PAGE = 'wcd-category-discounts';

    private const OPTION = 'wcd_discount_rules';

    private string $hook = '';

    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 20);
        add_action('admin_init', [$this, 'register_setting']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('update_option_' . self::OPTION, [$this, 'clear_cache']);
    }

    public function menu(): void {
        $hook = add_submenu_page(
            'woocommerce',
            __('Descuentos por Categoría', 'distribuidora-lunic'),
            __('Descuentos por Categoría', 'distribuidora-lunic'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'render']
        );
        $this->hook = is_string($hook) ? $hook : '';
    }

    public function register_setting(): void {
        register_setting('wcd_settings_group', self::OPTION, [
            'sanitize_callback' => [$this, 'sanitize'],
        ]);
    }

    public function assets(string $hook): void {
        if ($this->hook === '' || $hook !== $this->hook) {
            return;
        }
        wp_enqueue_style(
            'distribuidora-lunic-admin',
            DISTRIBUIDORA_LUNIC_URL . 'assets/css/admin.css',
            [],
            DISTRIBUIDORA_LUNIC_VERSION
        );
    }

    public function clear_cache(): void {
        delete_transient('wcd_discount_rules_cache');
    }

    /**
     * @param mixed $rules
     * @return array<int, array<string, mixed>>
     */
    public function sanitize($rules): array {
        if (!is_array($rules)) {
            return [];
        }
        $clean = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $category = isset($rule['category']) ? absint($rule['category']) : 0;
            if ($category < 1 || !term_exists($category, 'product_cat')) {
                continue;
            }
            $amount = isset($rule['amount']) ? (float) $rule['amount'] : 0;
            if ($amount < 0) {
                $amount = 0;
            }
            $amount_type = ($rule['amount_type'] ?? '') === 'fixed' ? 'fixed' : 'percentage';
            if ($amount_type === 'percentage' && $amount > 100) {
                $amount = 100;
            }
            $clean[] = [
                'category' => $category,
                'type' => ($rule['type'] ?? '') === 'surcharge' ? 'surcharge' : 'discount',
                'amount_type' => $amount_type,
                'amount' => $amount == (int) $amount ? (int) $amount : $amount,
                'priority' => max(1, isset($rule['priority']) ? absint($rule['priority']) : 1),
                'exclude_individual_discounts' => empty($rule['exclude_individual_discounts']) ? 0 : 1,
            ];
        }
        return $clean;
    }

    public function render(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso para ver esta página.', 'distribuidora-lunic'));
        }
        $rules = get_option(self::OPTION, []);
        if (!is_array($rules)) {
            $rules = [];
        }
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'orderby' => 'name',
        ]);
        if (is_wp_error($terms)) {
            $terms = [];
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Descuentos por Categoría', 'distribuidora-lunic'); ?></h1>
            <p class="description">
                <?php esc_html_e('Agregá una categoría y definí si es un descuento o un recargo, en porcentaje o en pesos. Se aplica la primera regla que coincida, según la prioridad. Si marcás “Excluir descuentos individuales”, un producto que ya está en oferta no recibe esta regla.', 'distribuidora-lunic'); ?>
            </p>
            <form method="post" action="options.php">
                <?php settings_fields('wcd_settings_group'); ?>
                <table class="widefat wcd-rules-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Categoría', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Tipo', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Tipo de monto', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Monto', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Prioridad', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Excluir descuentos individuales', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Acciones', 'distribuidora-lunic'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wcd-rules-body">
                        <?php if (!$rules) : ?>
                            <tr class="no-rules"><td colspan="7"><?php esc_html_e('No hay reglas configuradas.', 'distribuidora-lunic'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($rules as $index => $rule) : ?>
                                <?php $this->row((int) $index, $rule, $terms); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <button type="button" class="button button-primary" id="wcd-add-rule"><?php esc_html_e('Agregar regla', 'distribuidora-lunic'); ?></button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <?php submit_button(__('Guardar cambios', 'distribuidora-lunic')); ?>
            </form>
            <template id="wcd-rule-template">
                <?php $this->row('{index}', [
                    'category' => 0,
                    'type' => 'discount',
                    'amount_type' => 'percentage',
                    'amount' => '',
                    'priority' => 1,
                    'exclude_individual_discounts' => 0,
                ], $terms); ?>
            </template>
        </div>
        <script>
        (function () {
            var body = document.getElementById('wcd-rules-body');
            var add = document.getElementById('wcd-add-rule');
            var template = document.getElementById('wcd-rule-template');
            if (!body || !add || !template) return;
            var index = body.querySelectorAll('.wcd-rule-row').length;
            add.addEventListener('click', function () {
                var empty = body.querySelector('.no-rules');
                if (empty) empty.remove();
                body.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('{index}', String(index)));
                index += 1;
            });
            body.addEventListener('click', function (event) {
                var button = event.target.closest('.wcd-remove-rule');
                if (!button) return;
                var row = button.closest('.wcd-rule-row');
                if (row) row.remove();
                if (!body.querySelector('.wcd-rule-row')) {
                    body.innerHTML = '<tr class="no-rules"><td colspan="7">No hay reglas configuradas.</td></tr>';
                    index = 0;
                }
            });
        })();
        </script>
        <?php
    }

    /**
     * @param array<string, mixed> $rule
     * @param \WP_Term[] $terms
     */
    private function row($index, array $rule, array $terms): void {
        $category = (int) ($rule['category'] ?? 0);
        $type = ($rule['type'] ?? '') === 'surcharge' ? 'surcharge' : 'discount';
        $amount_type = ($rule['amount_type'] ?? '') === 'fixed' ? 'fixed' : 'percentage';
        $name = self::OPTION . '[' . $index . ']';
        ?>
        <tr class="wcd-rule-row">
            <td>
                <select name="<?php echo esc_attr($name); ?>[category]" required>
                    <option value=""><?php esc_html_e('Seleccionar categoría', 'distribuidora-lunic'); ?></option>
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_attr((string) $term->term_id); ?>" <?php selected($category, (int) $term->term_id); ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="<?php echo esc_attr($name); ?>[type]">
                    <option value="discount" <?php selected($type, 'discount'); ?>><?php esc_html_e('Descuento', 'distribuidora-lunic'); ?></option>
                    <option value="surcharge" <?php selected($type, 'surcharge'); ?>><?php esc_html_e('Recargo', 'distribuidora-lunic'); ?></option>
                </select>
            </td>
            <td>
                <select name="<?php echo esc_attr($name); ?>[amount_type]">
                    <option value="percentage" <?php selected($amount_type, 'percentage'); ?>><?php esc_html_e('Porcentaje', 'distribuidora-lunic'); ?></option>
                    <option value="fixed" <?php selected($amount_type, 'fixed'); ?>><?php esc_html_e('Monto fijo', 'distribuidora-lunic'); ?></option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="<?php echo esc_attr($name); ?>[amount]" value="<?php echo esc_attr((string) ($rule['amount'] ?? '')); ?>" required>
            </td>
            <td>
                <input type="number" min="1" step="1" name="<?php echo esc_attr($name); ?>[priority]" value="<?php echo esc_attr((string) ($rule['priority'] ?? 1)); ?>" required>
            </td>
            <td>
                <input type="checkbox" name="<?php echo esc_attr($name); ?>[exclude_individual_discounts]" value="1" <?php checked(!empty($rule['exclude_individual_discounts'])); ?>>
            </td>
            <td>
                <button type="button" class="button button-secondary wcd-remove-rule"><?php esc_html_e('Eliminar', 'distribuidora-lunic'); ?></button>
            </td>
        </tr>
        <?php
    }
}
