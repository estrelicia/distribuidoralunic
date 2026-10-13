</main>
<?php
$lunic_footer = function_exists('lunic_elementor_slot') ? lunic_elementor_slot('footer') : '';
if ($lunic_footer !== '') {
    echo $lunic_footer;
} else {
?>
<footer class="lunic-footer">
	<div class="lunic-footer__bar">
		<div class="lunic-footer__logo"><?php lunic_logo(); ?></div>
		<button type="button" class="lunic-footer__toggle" aria-expanded="false" aria-controls="lunic-footer-menu" aria-label="Menú del pie"><span></span><span></span><span></span></button>
		<nav id="lunic-footer-menu" class="lunic-footer__menu" aria-label="Footer"><?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => 'lunic-footer__list', 'fallback_cb' => false]); ?></nav>
		<div>
			<h2>Horario comercial</h2>
			<p><?php echo nl2br(esc_html(function_exists('lunic_site') ? lunic_site('hours') : '')); ?></p>
		</div>
		<div>
			<h2>Contacto</h2>
			<?php
			$whatsapp = function_exists('lunic_whatsapp_url') ? lunic_whatsapp_url() : '';
			$label = function_exists('lunic_site') ? lunic_site('whatsapp_label') : '';
			$email = function_exists('lunic_site') ? lunic_site('email') : '';
			$address = function_exists('lunic_site') ? lunic_site('address') : '';
			if ($whatsapp !== '' && $label !== '') :
			?>
			<p class="lunic-footer__phone"><a href="<?php echo esc_url($whatsapp); ?>"><?php echo esc_html($label); ?></a></p>
			<?php endif; ?>
			<?php if ($email !== '') : ?>
			<p><a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a></p>
			<?php endif; ?>
			<?php if ($address !== '') : ?>
			<p><strong>Dirección:</strong><br><?php echo nl2br(esc_html($address)); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="lunic-footer__end"></div>
</footer>
<?php } ?>
<?php wp_footer(); ?>
</body>
</html>
