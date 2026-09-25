<?php
defined('ABSPATH') || exit;
get_header('shop');
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
do_action('woocommerce_before_main_content');
?>
<div class="lunic-shop-page">
<header class="woocommerce-products-header">
	<?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
		<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
	<?php endif; ?>
	<?php woocommerce_breadcrumb(); ?>
</header>
<?php
if (shortcode_exists('lunic_search')) {
    echo do_shortcode('[lunic_search]');
}
?>
<div class="lunic-shop">
<aside class="lunic-shop__filter">
<?php
if (shortcode_exists('lunic_filter')) {
    echo do_shortcode('[lunic_filter]');
}
?>
</aside>
<div class="lunic-shop__main">
<?php
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
} else {
    do_action('woocommerce_no_products_found');
}
do_action('woocommerce_after_main_content');
echo '</div></div></div>';
get_footer('shop');
