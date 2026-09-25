<?php
defined('ABSPATH') || exit;
global $product;
?>
<div class="product_meta">
	<?php do_action('woocommerce_product_meta_start'); ?>
	<?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable'))) : ?>
		<span class="sku_wrapper">Código <span class="sku"><?php echo ($sku = $product->get_sku()) ? esc_html($sku) : 'No disponible'; ?></span></span>
	<?php endif; ?>
	<?php echo wc_get_product_category_list($product->get_id(), ', ', '<span class="posted_in">' . _n('Categoría:', 'Categorías:', count($product->get_category_ids()), 'woocommerce') . ' ', '</span>'); ?>
	<?php do_action('woocommerce_product_meta_end'); ?>
</div>
