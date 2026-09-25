<?php
get_header();
echo '<h1>Búsqueda</h1>';
if (have_posts()) {
    woocommerce_product_loop_start();
    while (have_posts()) {
        the_post();
        wc_get_template_part('content', 'product');
    }
    woocommerce_product_loop_end();
} else {
    echo '<p>No se encontraron productos. <a href="' . esc_url(wc_get_page_permalink('shop')) . '">Volver a la tienda</a></p>';
}
get_footer();
