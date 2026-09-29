<?php

namespace Distribuidora_Lunic\Modules\CatalogFilter;

defined('ABSPATH') || exit;

class Category_Tree {

    private const CACHE_KEY = 'lunic_cat_filter_v1';

    private const CACHE_TTL = 900;

    public static function register(): void {
        $flush = [self::class, 'flush'];
        add_action('save_post_product', $flush);
        add_action('deleted_post', $flush);
        add_action('created_product_cat', $flush);
        add_action('edited_product_cat', $flush);
        add_action('delete_product_cat', $flush);
        add_action('woocommerce_product_set_stock_status', $flush);
        add_action('woocommerce_update_product', $flush);
    }

    public static function flush(): void {
        delete_transient(self::CACHE_KEY);
    }

    /**
     * @return object[]
     */
    public static function terms(): array {
        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return array_map(static fn(array $row): object => (object) $row, $cached);
        }

        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'exclude' => [Module::EXCLUDE_TERM],
        ]);
        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }

        $payload = array_map(
            static function (\WP_Term $term): array {
                return [
                    'term_id' => (int) $term->term_id,
                    'name' => $term->name,
                    'parent' => (int) $term->parent,
                    'count' => (int) $term->count,
                ];
            },
            $terms
        );
        set_transient(self::CACHE_KEY, $payload, self::CACHE_TTL);

        return $terms;
    }
}
