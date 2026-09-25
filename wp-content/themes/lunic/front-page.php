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
    '2022/12/pexels-kafeel-ahmed-3997459-1.jpg',
];
echo '<section class="lunic-slider" aria-label="Destacados"><div class="lunic-slider__track">';
foreach ($slides as $file) {
    $url = content_url('uploads/' . $file);
    echo '<img src="' . esc_url($url) . '" alt="">';
}
echo '</div></section>';
$catalog = 'https://onedrive.live.com/:x:/g/personal/6c9e34c688d4059c/IQCcBdSIxjSeIIBsDAIAAAAAARHT_yfCI4UwYDsBRQP6MAA?rtime=r7B14PcB30g&redeem=aHR0cHM6Ly8xZHJ2Lm1zL3gvYy82YzllMzRjNjg4ZDQwNTljL0lRQ2NCZFNJeGpTZUlJQnNEQUlBQUFBQUFSSFRfeWZDSTRVd1lEc0JSUVA2TUFBP2U9NjhpUFow';
echo '<p><a class="button" href="' . esc_url($catalog) . '">Lista de precios</a></p>';
echo '<section class="lunic-home-products"><h2>Productos</h2>';
echo do_shortcode('[products limit="8" columns="4" orderby="date"]');
echo '</section>';
lunic_envios_accordion();
if (shortcode_exists('lunic_search')) {
    echo '<h2>Buscar</h2>' . do_shortcode('[lunic_search]');
}
get_footer();
