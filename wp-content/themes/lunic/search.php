<?php
defined('ABSPATH') || exit;
get_header();
$query = get_search_query();
?>
<div class="lunic-shop-page">
	<h1><?php echo $query !== '' ? 'Búsqueda: ' . esc_html($query) : 'Búsqueda'; ?></h1>
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
            if (have_posts()) {
                woocommerce_product_loop_start();
                while (have_posts()) {
                    the_post();
                    wc_get_template_part('content', 'product');
                }
                woocommerce_product_loop_end();
                woocommerce_pagination();
            } else {
                echo '<div class="lunic-empty"><p class="cart-empty">No se encontraron productos.</p><p class="return-to-shop"><a class="button" href="' . esc_url(wc_get_page_permalink('shop')) . '">Volver a la tienda</a></p></div>';
            }
            ?>
		</div>
	</div>
</div>
<?php
get_footer();
