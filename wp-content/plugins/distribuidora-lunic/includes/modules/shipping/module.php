<?php

namespace Distribuidora_Lunic\Modules\Shipping;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        if (class_exists('WC_Custom_Shipping_Method')) {
            return;
        }

        add_action('woocommerce_shipping_init', [$this, 'load_method']);
        add_filter('woocommerce_shipping_methods', [$this, 'register_method']);

        require_once __DIR__ . '/class-totals.php';
        (new Totals())->register();
    }

    public function load_method(): void {
        if (!class_exists(Method::class)) {
            require_once __DIR__ . '/class-method.php';
        }
    }

    public function register_method(array $methods): array {
        $this->load_method();
        $methods['custom_shipping'] = Method::class;
        return $methods;
    }
}
