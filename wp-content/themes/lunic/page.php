<?php
get_header();
echo '<article class="lunic-page">';
while (have_posts()) {
    the_post();
    if ((int) get_the_ID() === 12340 && shortcode_exists('lunic_categorias')) {
        echo do_shortcode('[lunic_categorias]');
        continue;
    }
    $elementor = get_post_meta(get_the_ID(), '_elementor_edit_mode', true) === 'builder';
    $woo_screen = function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page());
    $hide_title = false;
    if ($elementor && class_exists('\Elementor\Plugin')) {
        $document = \Elementor\Plugin::instance()->documents->get(get_the_ID());
        $hide_title = $document && $document->get_settings('hide_title') === 'yes';
    }
    if ((!$elementor || !$hide_title) && !$woo_screen) {
        echo '<h1 class="entry-title">' . esc_html(get_the_title()) . '</h1>';
    }
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
