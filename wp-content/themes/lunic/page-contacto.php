<?php
get_header();
?>
<article class="lunic-contact">
	<h1>Contacto</h1>
	<div class="lunic-contact__grid">
		<?php echo do_shortcode('[lunic_contact]'); ?>
		<div class="lunic-contact__info">
			<p><span aria-hidden="true">☎</span> <a href="tel:+541135600573">(011) 15 3560-0573</a></p>
			<p><span aria-hidden="true">✉</span> <a href="mailto:info@distribuidoralunic.com.ar">info@distribuidoralunic.com.ar</a></p>
			<p><span aria-hidden="true">⚑</span> <strong>Av. José María Moreno</strong><br>1280 CABA (Argentina)</p>
			<p><strong>Horarios Comerciales:</strong><br>Lunes a Viernes 9:00 a 13:00 Hs. y 14:00 a 18:00 Hs.<br>Sabados 9:00 a 14:00 Hs.</p>
		</div>
	</div>
	<iframe class="lunic-contact__map" title="Mapa" src="<?php echo esc_url('https://maps.google.com/maps?q=Av.%20Jose%20Mar%C3%ADa%20Moreno%201280%20CABA&z=15&output=embed'); ?>"></iframe>
</article>
<?php
get_footer();
