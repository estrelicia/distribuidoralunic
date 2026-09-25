<?php

namespace Distribuidora_Lunic\Modules\Search;

defined('ABSPATH') || exit;

class Suggestions {

    public function register(): void {
        add_action('rest_api_init', [$this, 'register_route']);
        add_action('wp_ajax_lunic_search', [$this, 'ajax']);
        add_action('wp_ajax_nopriv_lunic_search', [$this, 'ajax']);
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
        return new \WP_REST_Response($this->query((string) $request->get_param('q')));
    }

    public function ajax(): void {
        $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        wp_send_json($this->query($q));
    }

    /** @return array{items: array<int, array<string, string>>} */
    public function query(string $term): array {
        $term = trim($term);
        if (mb_strlen($term) < 1) {
            return ['items' => []];
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
                'title' => $product->get_name(),
                'url' => $product->get_permalink(),
                'image' => $image ? $image : '',
                'price' => wp_strip_all_tags(wc_price(wc_get_price_to_display($product))),
                'category' => $cats[0] ?? '',
            ];
        }
        wp_reset_postdata();
        return ['items' => $items];
    }
}
