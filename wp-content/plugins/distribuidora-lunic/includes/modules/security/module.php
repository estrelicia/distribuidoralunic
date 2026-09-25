<?php

namespace Distribuidora_Lunic\Modules\Security;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        if (has_action('send_headers', 'cabeceras_seguridad')) {
            return;
        }
        require_once __DIR__ . '/class-hardening.php';
        (new Hardening())->register();
    }
}
