<?php

namespace Distribuidora_Lunic\Modules\Checkout;

defined('ABSPATH') || exit;

class Module {

    public const META_CUIT = 'CUIT/CUIL o DNI';
    public const META_IVA = 'Condición frente al IVA';

    public function register(): void {
        if (has_action('woocommerce_after_checkout_billing_form', 'add_campos_personalizados')) {
            return;
        }
        require_once __DIR__ . '/class-fields.php';
        (new Fields())->register();
    }
}
