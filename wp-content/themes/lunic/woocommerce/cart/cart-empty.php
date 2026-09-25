<?php
defined('ABSPATH') || exit;
?>
<p class="cart-empty"><?php esc_html_e('Tu carrito está vacío.', 'lunic'); ?></p>
<p><a class="button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Volver a la tienda', 'lunic'); ?></a></p>
