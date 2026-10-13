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
			<?php
			$phone = function_exists('lunic_site') ? lunic_site('phone') : '';
			$email = function_exists('lunic_site') ? lunic_site('email') : '';
			$address = function_exists('lunic_site') ? lunic_site('address') : '';
			$hours = function_exists('lunic_site') ? lunic_site('hours') : '';
			$tel = preg_replace('/\D+/', '', $phone);
			?>
			<?php if ($phone !== '') : ?>
			<p><img src="<?php echo $icon('6210156.png'); ?>" alt="" width="28" height="28"> <a href="<?php echo esc_url('tel:+' . $tel); ?>"><?php echo esc_html($phone); ?></a></p>
			<?php endif; ?>
			<?php if ($email !== '') : ?>
			<p><img src="<?php echo $icon('3936425.png'); ?>" alt="" width="28" height="28"> <a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a></p>
			<?php endif; ?>
			<?php if ($address !== '') : ?>
			<p><img src="<?php echo $icon('6209724.png'); ?>" alt="" width="28" height="28"> <span><?php echo nl2br(esc_html($address)); ?></span></p>
			<?php endif; ?>
			<?php if ($hours !== '') : ?>
			<p><img src="<?php echo $icon('6209559.png'); ?>" alt="" width="28" height="28"> <span><strong>Horarios Comerciales:</strong><br><?php echo nl2br(esc_html($hours)); ?></span></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
	$map = trim(preg_replace('/\s+/', ' ', $address) ?? '');
	if ($map !== '') :
	?>
	<iframe class="lunic-contact__map" title="Mapa" src="<?php echo esc_url('https://maps.google.com/maps?q=' . rawurlencode($map) . '&z=15&output=embed'); ?>"></iframe>
	<?php endif; ?>
</article>
<?php
get_footer();
