<?php

namespace Distribuidora_Lunic\Modules\Shipping;

defined('ABSPATH') || exit;

class Method extends \WC_Shipping_Method {

    /** @var float */
    public $min_amount;

    /** @var float */
    public $cost;

    /** @var float */
    public $carry_cost;

    /** @var float */
    public $carry_min_amount;

    /** @var string */
    public $carry_enabled;

    public function __construct($instance_id = 0) {
        parent::__construct($instance_id);
        $this->id = 'custom_shipping';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Envío Personalizado', 'distribuidora-lunic');
        $this->method_description = __('Envío con precio fijo o acarreo. El importe se muestra después del total y el cliente lo abona aparte: no entra en el total del pedido ni en una pasarela. El texto informativo del acordeón se edita en Lunic → Texto de envíos.', 'distribuidora-lunic');
        $this->title = $this->method_title;
        $this->supports = ['shipping-zones', 'instance-settings', 'instance-settings-modal'];
        $this->init();
    }

    public function init(): void {
        $this->init_form_fields();
        $this->init_settings();
        $this->enabled = $this->get_option('enabled', 'yes');
        $this->title = $this->get_option('title', $this->method_title);
        $this->min_amount = (float) $this->get_option('min_amount', 10000);
        $this->cost = (float) $this->get_option('cost', 1000);
        $this->carry_cost = (float) $this->get_option('carry_cost', 0);
        $this->carry_min_amount = (float) $this->get_option('carry_min_amount', 10000);
        $this->carry_enabled = $this->get_option('carry_enabled', 'no');
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void {
        $this->instance_form_fields = [
            'enabled' => [
                'title' => __('Habilitar', 'distribuidora-lunic'),
                'type' => 'checkbox',
                'label' => __('Habilitar este método', 'distribuidora-lunic'),
                'default' => 'yes',
            ],
            'title' => [
                'title' => __('Título', 'distribuidora-lunic'),
                'type' => 'text',
                'default' => __('Envío Personalizado', 'distribuidora-lunic'),
            ],
            'min_amount' => [
                'title' => __('Monto mínimo para envío gratis', 'distribuidora-lunic'),
                'type' => 'number',
                'default' => 10000,
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
            ],
            'cost' => [
                'title' => __('Precio de envío', 'distribuidora-lunic'),
                'type' => 'number',
                'default' => 1000,
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
            ],
            'carry_enabled' => [
                'title' => __('Habilitar acarreo', 'distribuidora-lunic'),
                'type' => 'checkbox',
                'label' => __('Activar modo acarreo (reemplaza el envío normal)', 'distribuidora-lunic'),
                'default' => 'no',
            ],
            'carry_min_amount' => [
                'title' => __('Monto mínimo para acarreo gratis', 'distribuidora-lunic'),
                'type' => 'number',
                'default' => 10000,
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
            ],
            'carry_cost' => [
                'title' => __('Precio de acarreo', 'distribuidora-lunic'),
                'type' => 'number',
                'default' => 0,
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
            ],
        ];
    }

    public function calculate_shipping($package = []): void {
        // Umbral de envío gratis: subtotal de productos sin IVA (ver estado.md / envios-donde-se-edita.md).
        $cart_total = \WC()->cart ? (float) \WC()->cart->get_subtotal() : 0;
        if ($this->carry_enabled === 'yes') {
            $this->add_carry_rate($cart_total, $package);
            return;
        }
        $this->add_normal_rate($cart_total, $package);
    }

    private function add_carry_rate(float $cart_total, array $package): void {
        $free = $this->carry_min_amount > 0 && $cart_total >= $this->carry_min_amount;
        $cost = $free ? 0 : $this->carry_cost;
        $label = $free
            ? __('Acarreo hasta la empresa de transporte', 'distribuidora-lunic')
            : sprintf(
                /* translators: %s: formatted carry cost */
                __('Acarreo hasta la empresa de transporte %s', 'distribuidora-lunic'),
                wc_price($this->carry_cost)
            );
        $this->add_rate([
            'id' => $this->get_rate_id(),
            'label' => $label,
            'cost' => $cost,
            'package' => $package,
            'meta_data' => [
                'zone_title' => $this->title,
                'carry_mode' => true,
                'carry_cost' => $cost,
                'carry_free' => $free,
            ],
        ]);
    }

    private function add_normal_rate(float $cart_total, array $package): void {
        $free = $this->min_amount > 0 && $cart_total >= $this->min_amount;
        $this->add_rate([
            'id' => $this->get_rate_id(),
            'label' => $free ? __('Gratuito', 'distribuidora-lunic') : wc_price($this->cost),
            'cost' => $free ? 0 : $this->cost,
            'package' => $package,
            'meta_data' => [
                'zone_title' => $this->title,
                'free_shipping' => $free,
                'normal_cost' => $this->cost,
            ],
        ]);
    }

    public function is_available($package): bool {
        $available = parent::is_available($package);
        if (\WC()->cart && \WC()->cart->is_empty()) {
            $available = false;
        }
        return (bool) apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $available, $package, $this);
    }
}
