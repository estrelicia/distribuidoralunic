<?php

namespace Distribuidora_Lunic\Modules\Categories;

defined('ABSPATH') || exit;

class Grid {

    public function register(): void {
        add_action('init', [$this, 'register_meta']);
        add_shortcode('lunic_categorias', [$this, 'render']);
        add_filter('elementor/widget/render_content', [$this, 'replace_jet_listing'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_meta(): void {
        register_term_meta('product_cat', 'imagen-web', [
            'type' => 'object',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'url' => ['type' => 'string'],
                    ],
                ],
            ],
        ]);
    }

    public function register_assets(): void {
        wp_register_style(
            'lunic-categories',
            DISTRIBUIDORA_LUNIC_URL . 'assets/css/categories.css',
            [],
            DISTRIBUIDORA_LUNIC_VERSION
        );
    }

    public function replace_jet_listing($content, $widget) {
        if (!is_object($widget) || !method_exists($widget, 'get_name')) {
            return $content;
        }
        if ($widget->get_name() !== 'jet-listing-grid') {
            return $content;
        }
        if ((string) $widget->get_settings('lisitng_id') !== Module::JET_LISTING) {
            return $content;
        }
        return $this->render();
    }

    public function render(): string {
        wp_enqueue_style('lunic-categories');
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'exclude' => [15],
        ]);
        if (is_wp_error($terms) || !$terms) {
            return '';
        }
        $html = '<div class="lunic-cats">';
        foreach ($terms as $term) {
            $url = get_term_link($term);
            if (is_wp_error($url)) {
                continue;
            }
            $image = $this->image_url($term->term_id);
            $missing = $image === '' || strpos($image, 'placeholder') !== false;
            $html .= '<a class="lunic-cats__card" href="' . esc_url($url) . '">';
            if ($missing) {
                $html .= '<span class="lunic-cats__empty">Sin imagen. <span>Ver en la tienda</span></span>';
            } else {
                $html .= '<img src="' . esc_url($image) . '" alt="' . esc_attr($term->name) . '" />';
            }
            $html .= '<span>' . esc_html($term->name) . '</span>';
            $html .= '</a>';
        }
        $html .= '</div>';
        return $html;
    }

    private function image_url(int $term_id): string {
        $meta = get_term_meta($term_id, 'imagen-web', true);
        if (is_array($meta)) {
            if (!empty($meta['id'])) {
                $from_id = wp_get_attachment_image_url((int) $meta['id'], 'medium');
                if ($from_id) {
                    return $from_id;
                }
            }
            if (!empty($meta['url'])) {
                return (string) $meta['url'];
            }
        }
        $thumb = (int) get_term_meta($term_id, 'thumbnail_id', true);
        if ($thumb) {
            $url = wp_get_attachment_image_url($thumb, 'medium');
            if ($url) {
                return $url;
            }
        }
        return function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('medium') : '';
    }
}
