<?php

namespace Distribuidora_Lunic\Modules\Security;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        add_filter('woocommerce_sale_flash', [$this, 'sale_flash_oferta'], 10, 1);
        add_filter('woocommerce_defer_transactional_emails', '__return_true');
        if (defined('MDCMS_VERSION') || has_action('send_headers', 'cabeceras_seguridad')) {
            return;
        }
        require_once __DIR__ . '/class-hardening.php';
        (new Hardening())->register();
    }

    public function sale_flash_oferta($text) {
        return str_replace(__('Sale!', 'woocommerce'), 'Oferta', (string) $text);
    }
}
