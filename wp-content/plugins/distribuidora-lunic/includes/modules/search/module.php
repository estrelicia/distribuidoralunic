<?php

namespace Distribuidora_Lunic\Modules\Search;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        require_once __DIR__ . '/class-suggestions.php';
        require_once __DIR__ . '/class-shortcode.php';
        (new Suggestions())->register();
        (new Shortcode())->register();
    }
}
