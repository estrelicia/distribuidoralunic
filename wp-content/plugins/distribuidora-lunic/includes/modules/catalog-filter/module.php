<?php

namespace Distribuidora_Lunic\Modules\CatalogFilter;

defined('ABSPATH') || exit;

class Module {

    public const QUERY_ARG = 'lunic_cat';

    public static function excluded_term(): int {
        return (int) get_option('default_product_cat', 0);
    }

    public function register(): void {
        require_once __DIR__ . '/class-category-tree.php';
        require_once __DIR__ . '/class-filter.php';
        Category_Tree::register();
        (new Filter())->register();
    }
}
