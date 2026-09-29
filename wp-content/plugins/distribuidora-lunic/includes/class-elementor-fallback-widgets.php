<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

final class Elementor_Filter_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'woofilters';
    }

    public function get_title(): string {
        return 'Filtro';
    }

    public function get_categories(): array {
        return ['general'];
    }

    protected function register_controls(): void {
    }

    protected function render(): void {
        if (shortcode_exists('lunic_filter')) {
            echo do_shortcode('[lunic_filter]');
        }
    }
}

final class Elementor_Archive_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'wc-archive-products';
    }

    public function get_title(): string {
        return 'Productos';
    }

    public function get_categories(): array {
        return ['general'];
    }

    protected function register_controls(): void {
    }

    protected function render(): void {
        if (!function_exists('woocommerce_product_loop')) {
            return;
        }
        if (woocommerce_product_loop()) {
            do_action('woocommerce_before_shop_loop');
            woocommerce_product_loop_start();
            if (wc_get_loop_prop('total')) {
                while (have_posts()) {
                    the_post();
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                }
            }
            woocommerce_product_loop_end();
            do_action('woocommerce_after_shop_loop');
            return;
        }
        do_action('woocommerce_no_products_found');
    }
}
