<?php

namespace Distribuidora_Lunic\Modules\Checkout;

defined('ABSPATH') || exit;

class Fields {

    public function register(): void {
        add_action('woocommerce_after_checkout_billing_form', [$this, 'render_fields']);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_order_meta'], 10, 1);
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'render_admin'], 10, 1);
    }

    public function render_fields($checkout): void {
        woocommerce_form_field('cuitcuil', [
            'type' => 'text',
            'class' => ['my-field-class', 'form-row-wide'],
            'label' => __('CUIT/CUIL o DNI', 'distribuidora-lunic'),
            'placeholder' => __('Ingresa aqui CUIT/CUIL o DNI', 'distribuidora-lunic'),
        ], $checkout->get_value('cuitcuil'));

        woocommerce_form_field('condicioniva', [
            'type' => 'select',
            'class' => ['my-field-class', 'form-row-wide'],
            'label' => __('Condición frente al IVA', 'distribuidora-lunic'),
            'options' => $this->iva_options(),
        ], $checkout->get_value('condicioniva'));
    }

    public function save_order_meta($order_id): void {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        if (!empty($_POST['cuitcuil'])) {
            $order->update_meta_data(
                Module::META_CUIT,
                sanitize_text_field(wp_unslash($_POST['cuitcuil']))
            );
        }
        if (!empty($_POST['condicioniva'])) {
            $order->update_meta_data(
                Module::META_IVA,
                sanitize_text_field(wp_unslash($_POST['condicioniva']))
            );
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
