<?php
get_header();
while (have_posts()) {
    the_post();
    echo '<h1>' . esc_html(get_the_title()) . '</h1>';
    if (function_exists('is_cart') && is_cart()) {
        echo do_shortcode('[woocommerce_cart]');
    } elseif (function_exists('is_checkout') && is_checkout()) {
        echo do_shortcode('[woocommerce_checkout]');
    } elseif (function_exists('is_account_page') && is_account_page()) {
        echo do_shortcode('[woocommerce_my_account]');
    } else {
        the_content();
    }
}
get_footer();
