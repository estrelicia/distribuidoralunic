<?php

namespace Distribuidora_Lunic\Modules\Search;

use Distribuidora_Lunic\Visitor;

defined('ABSPATH') || exit;

class Suggestions {

    public function register(): void {
        add_action('rest_api_init', [$this, 'register_route']);
    }

    public function register_route(): void {
        register_rest_route('lunic/v1', '/search', [
            'methods' => 'GET',
            'callback' => [$this, 'rest'],
            'permission_callback' => '__return_true',
            'args' => [
                'q' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    public function rest(\WP_REST_Request $request): \WP_REST_Response {
        if (Visitor::limited('search', 30, MINUTE_IN_SECONDS)) {
            return new \WP_REST_Response(['items' => []], 429);
        }
        return new \WP_REST_Response($this->query((string) $request->get_param('q')));
    }

    /** @return array{items: array<int, array<string, string>>} */
    public function query(string $term): array {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return ['items' => []];
        }
        $cache_key = 'lunic_search_' . md5(mb_strtolower($term));
        $cached = get_transient($cache_key);
        if (is_array($cached) && isset($cached['items']) && is_array($cached['items'])) {
            return $cached;
        }

        $found = new \WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            's' => $term,
            'posts_per_page' => 8,
            'no_found_rows' => true,
        ]);

        $items = [];
        foreach ($found->posts as $post) {
            $product = wc_get_product($post);
            if (!$product) {
                continue;
            }
            $cats = wc_get_product_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
            $image = wp_get_attachment_image_url((int) $product->get_image_id(), 'woocommerce_thumbnail');
            $items[] = [
                'id' => (string) $product->get_id(),
                'title' => self::plain($product->get_name()),
                'url' => $product->get_permalink(),
                'image' => $image ? $image : '',
                'price' => self::plain(wc_price(wc_get_price_to_display($product))),
                'category' => self::plain($cats[0] ?? ''),
            ];
        }
        wp_reset_postdata();
        $result = ['items' => $items];
        set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
        return $result;
    }

    private static function plain(string $html): string {
        return html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
