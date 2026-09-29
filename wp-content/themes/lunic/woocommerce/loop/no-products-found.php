<?php
defined('ABSPATH') || exit;
?>
<div class="lunic-empty">
	<p class="cart-empty"><?php esc_html_e('No se encontraron productos.', 'lunic'); ?></p>
	<p class="return-to-shop"><a class="button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Volver a la tienda', 'lunic'); ?></a></p>
</div>
