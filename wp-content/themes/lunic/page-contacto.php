<?php
get_header();
echo '<h1>Contacto</h1>';
echo do_shortcode('[lunic_contact]');
echo '<iframe title="Mapa" style="width:100%;height:320px;border:0" src="' . esc_url('https://www.openstreetmap.org/export/embed.html?bbox=-58.45,-34.64,-58.42,-34.62&layer=mapnik&marker=-34.6285,-58.4355') . '"></iframe>';
echo '<p>Av. José María Moreno 1280 CABA</p>';
get_footer();
