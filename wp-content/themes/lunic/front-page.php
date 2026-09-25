<?php
get_header();
$slides = [
    'WhatsApp-Image-2026-08-20-at-3.36.24-PM.jpeg',
    'WhatsApp-Image-2026-08-20-at-3.36.24-PM-1.jpeg',
    'WhatsApp-Image-2026-07-29-at-4.14.01-PM.jpeg',
    'Capullos-de-te.jpeg',
    'Naranja-y-mix-de-citricos-en-rodajas.jpeg',
    'Retiros-de-mercaderia.jpeg',
    'Retiro-de-mercaderia.jpeg',
    'Acarreo-Via-cargo.png',
];
echo '<section class="lunic-slider" aria-label="Destacados"><div class="lunic-slider__track">';
foreach ($slides as $file) {
    echo '<img src="' . esc_url(content_url('uploads/' . $file)) . '" alt="">';
}
echo '</div></section>';
$catalog = 'https://onedrive.live.com/:x:/g/personal/6c9e34c688d4059c/IQCcBdSIxjSeIIBsDAIAAAAAARHT_yfCI4UwYDsBRQP6MAA?rtime=r7B14PcB30g&redeem=aHR0cHM6Ly8xZHJ2Lm1zL3gvYy82YzllMzRjNjg4ZDQwNTljL0lRQ2NCZFNJeGpTZUlJQnNEQUlBQUFBQUFSSFRfeWZDSTRVd1lEc0JSUVA2TUFBP2U9NjhpUFow';
echo '<p class="lunic-home-cta"><a href="' . esc_url($catalog) . '">Descargá la Lista de Precios</a></p>';
lunic_envios_accordion();
echo '<section class="lunic-home-intro"><h2>Productos de calidad</h2><h3>Siempre al mejor precio</h3>';
the_widget('WC_Widget_Products', [
    'title' => 'En oferta',
    'number' => 5,
    'show' => 'onsale',
    'orderby' => 'rand',
    'order' => 'desc',
    'hide_free' => 1,
]);
echo '</section>';
echo '<section class="lunic-searchband"><h2>Buscador de productos</h2>';
if (shortcode_exists('lunic_search')) {
    echo do_shortcode('[lunic_search]');
}
echo '</section>';
get_footer();
