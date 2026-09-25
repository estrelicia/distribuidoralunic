<?php
get_header();
echo '<article class="lunic-page">';
while (have_posts()) {
    the_post();
    if ((int) get_the_ID() === 12340 && shortcode_exists('lunic_categorias')) {
        echo do_shortcode('[lunic_categorias]');
        continue;
    }
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
echo '</article>';
get_footer();
