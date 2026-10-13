<?php
/**
 * Carrito vacío
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;
?>
<div class="lunic-empty">
	<?php
	/**
	 * @hooked wc_empty_cart_message - 10
	 */
	do_action('woocommerce_cart_is_empty');
	?>
	<p class="return-to-shop"><a class="button" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>"><?php echo esc_html(apply_filters('woocommerce_return_to_shop_text', __('Volver a la tienda', 'lunic'))); ?></a></p>
</div>
<?php if (function_exists('lunic_maybe_envios_accordion')) { lunic_maybe_envios_accordion(); } ?>
