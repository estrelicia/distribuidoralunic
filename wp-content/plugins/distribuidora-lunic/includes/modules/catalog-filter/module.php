<?php

namespace Distribuidora_Lunic\Modules\CatalogFilter;

defined('ABSPATH') || exit;

class Module {

    public const EXCLUDE_TERM = 15;
    public const QUERY_ARG = 'lunic_cat';

    public function register(): void {
        require_once __DIR__ . '/class-filter.php';
        (new Filter())->register();
    }
}
