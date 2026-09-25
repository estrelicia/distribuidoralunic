<?php
defined('ABSPATH') || exit;
global $product;
do_action('woocommerce_before_single_product');
if (post_password_required()) {
    echo get_the_password_form();
    return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
	<?php woocommerce_breadcrumb(); ?>
	<?php do_action('woocommerce_before_single_product_summary'); ?>
	<div class="summary entry-summary">
		<?php
        woocommerce_template_single_title();
        woocommerce_template_single_rating();
        woocommerce_template_single_price();
        woocommerce_template_single_excerpt();
        if (class_exists(\Distribuidora_Lunic\Modules\Discounts\Calculator::class)) {
            $label = \Distribuidora_Lunic\Modules\Discounts\Calculator::discount_label($product);
            if ($label !== '') {
                echo '<p class="wcd-discount-text">' . esc_html($label) . '</p>';
            }
        }
        woocommerce_template_single_add_to_cart();
        woocommerce_template_single_meta();
        ?>
	</div>
	<?php
    woocommerce_output_product_data_tabs();
    woocommerce_output_related_products();
    ?>
</div>
<?php do_action('woocommerce_after_single_product'); ?>
