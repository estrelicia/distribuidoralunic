<?php
defined('ABSPATH') || exit;
get_header();
while (have_posts()) {
    the_post();
    $lunic_product = function_exists('lunic_elementor_slot') ? lunic_elementor_slot('product') : '';
    if ($lunic_product !== '') {
        global $product;
        $classes = function_exists('wc_get_product_class') ? implode(' ', wc_get_product_class('', $product)) : 'product';
        echo '<div id="product-' . get_the_ID() . '" class="' . esc_attr($classes) . ' lunic-product-view">';
        echo $lunic_product;
        echo '</div>';
        continue;
    }
    wc_get_template_part('content', 'single-product');
}
get_footer();
