<?php
get_header();
$icon = static function (string $file): string {
    return esc_url(content_url('uploads/2022/12/' . $file));
};
?>
<article class="lunic-contact">
	<h1>Contacto</h1>
	<div class="lunic-contact__grid">
		<?php echo do_shortcode('[lunic_contact]'); ?>
		<div class="lunic-contact__info">
			<p><img src="<?php echo $icon('6210156.png'); ?>" alt="" width="28" height="28"> <a href="tel:+541135600573">(011) 15 3560-0573</a></p>
			<p><img src="<?php echo $icon('3936425.png'); ?>" alt="" width="28" height="28"> <a href="mailto:info@distribuidoralunic.com.ar">info@distribuidoralunic.com.ar</a></p>
			<p><img src="<?php echo $icon('6209724.png'); ?>" alt="" width="28" height="28"> <span><strong>Av. José María Moreno</strong><br>1280 CABA (Argentina)</span></p>
			<p><img src="<?php echo $icon('6209559.png'); ?>" alt="" width="28" height="28"> <span><strong>Horarios Comerciales:</strong><br>Lunes a Viernes 9:00 a 13:00 Hs. y 14:00 a 18:00 Hs.<br>Sabados 9:00 a 14:00 Hs.</span></p>
		</div>
	</div>
	<iframe class="lunic-contact__map" title="Mapa" src="<?php echo esc_url('https://maps.google.com/maps?q=Av.%20Jose%20Mar%C3%ADa%20Moreno%201280%20CABA&z=15&output=embed'); ?>"></iframe>
</article>
<?php
get_footer();
