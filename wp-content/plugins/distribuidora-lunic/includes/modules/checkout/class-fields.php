<?php

namespace Distribuidora_Lunic\Modules\Checkout;

defined('ABSPATH') || exit;

class Fields {

    public function register(): void {
        add_action('woocommerce_after_checkout_billing_form', [$this, 'render_fields']);
        add_action('woocommerce_checkout_process', [$this, 'validate']);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_order_meta'], 10, 1);
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'render_admin'], 10, 1);
    }

    public function render_fields($checkout): void {
        woocommerce_form_field('cuitcuil', [
            'type' => 'text',
            'class' => ['my-field-class', 'form-row-wide'],
            'label' => __('CUIT/CUIL o DNI', 'distribuidora-lunic'),
            'placeholder' => __('CUIT/CUIL o DNI', 'distribuidora-lunic'),
            'description' => __('Opcional. Si lo completás, tiene que ser un CUIT/CUIL válido o un DNI de 7 u 8 números.', 'distribuidora-lunic'),
            'required' => false,
        ], $checkout->get_value('cuitcuil'));

        woocommerce_form_field('condicioniva', [
            'type' => 'select',
            'class' => ['my-field-class', 'form-row-wide'],
            'label' => __('Condición frente al IVA', 'distribuidora-lunic'),
            'options' => $this->iva_options(),
            'required' => false,
        ], $checkout->get_value('condicioniva'));
    }

    public function validate(): void {
        $tax_id = isset($_POST['cuitcuil']) ? sanitize_text_field(wp_unslash($_POST['cuitcuil'])) : '';
        $iva = isset($_POST['condicioniva']) ? sanitize_text_field(wp_unslash($_POST['condicioniva'])) : '';
        if ($tax_id !== '' && !self::is_valid_tax_id($tax_id)) {
            wc_add_notice(
                __('El CUIT/CUIL o DNI no es válido. Revisá el número o dejá el campo vacío.', 'distribuidora-lunic'),
                'error'
            );
        }
        if ($iva !== '' && !array_key_exists($iva, $this->iva_options())) {
            wc_add_notice(
                __('La condición frente al IVA no es válida.', 'distribuidora-lunic'),
                'error'
            );
        }
    }

    public function save_order_meta($order_id): void {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $tax_id = isset($_POST['cuitcuil']) ? sanitize_text_field(wp_unslash($_POST['cuitcuil'])) : '';
        $iva = isset($_POST['condicioniva']) ? sanitize_text_field(wp_unslash($_POST['condicioniva'])) : '';
        if ($tax_id !== '' && self::is_valid_tax_id($tax_id)) {
            $order->update_meta_data(Module::META_CUIT, self::digits($tax_id));
        }
        if ($iva !== '' && array_key_exists($iva, $this->iva_options())) {
            $order->update_meta_data(Module::META_IVA, $iva);
        }
        $order->save();
    }

    public function render_admin($order): void {
        if (!is_a($order, 'WC_Order')) {
            return;
        }
        $cuit = $order->get_meta(Module::META_CUIT);
        $iva = $order->get_meta(Module::META_IVA);
        if ($cuit !== '') {
            echo '<p><strong>' . esc_html__('CUIT/CUIL o DNI', 'distribuidora-lunic') . ':</strong> ' . esc_html($cuit) . '</p>';
        }
        if ($iva !== '') {
            echo '<p><strong>' . esc_html__('Condición frente al IVA', 'distribuidora-lunic') . ':</strong> ' . esc_html($iva) . '</p>';
        }
    }

    public static function is_valid_tax_id(string $value): bool {
        $digits = self::digits($value);
        $length = strlen($digits);
        if ($digits === '' || (int) $digits === 0) {
            return false;
        }
        if ($length === 7 || $length === 8) {
            return true;
        }
        if ($length !== 11) {
            return false;
        }
        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $digits[$i] * $weights[$i];
        }
        $check = 11 - ($sum % 11);
        if ($check === 11) {
            $check = 0;
        } elseif ($check === 10) {
            $check = 9;
        }
        return (int) $digits[10] === $check;
    }

    private static function digits(string $value): string {
        return (string) preg_replace('/\D+/', '', $value);
    }

    /** @return array<string, string> */
    private function iva_options(): array {
        return [
            'Consumidor Final' => __('Consumidor Final', 'distribuidora-lunic'),
            'Responsable Monotributo' => __('Responsable Monotributo', 'distribuidora-lunic'),
            'IVA Responsable Inscripto' => __('IVA Responsable Inscripto', 'distribuidora-lunic'),
            'IVA Responsable no Inscripto' => __('IVA Responsable no Inscripto', 'distribuidora-lunic'),
            'IVA no Responsable' => __('IVA no Responsable', 'distribuidora-lunic'),
            'IVA Sujeto Exento' => __('IVA Sujeto Exento', 'distribuidora-lunic'),
        ];
    }
}
