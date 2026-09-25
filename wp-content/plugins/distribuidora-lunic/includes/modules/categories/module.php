<?php

namespace Distribuidora_Lunic\Modules\Categories;

defined('ABSPATH') || exit;

class Module {

    public const LISTING_PAGE = 12340;
    public const JET_LISTING = '12348';

    public function register(): void {
        require_once __DIR__ . '/class-grid.php';
        (new Grid())->register();
    }
}
