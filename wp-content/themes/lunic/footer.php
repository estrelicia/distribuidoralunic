</main>
<footer class="lunic-footer">
	<div class="lunic-footer__bar">
		<div class="lunic-footer__logo"><?php lunic_logo(); ?></div>
		<button type="button" class="lunic-footer__toggle" aria-expanded="false" aria-controls="lunic-footer-menu" aria-label="Menú del pie"><span></span><span></span><span></span></button>
		<nav id="lunic-footer-menu" class="lunic-footer__menu" aria-label="Footer"><?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => 'lunic-footer__list', 'fallback_cb' => false]); ?></nav>
		<div>
			<h2>Horario comercial</h2>
			<p>Lunes a Viernes 9:00 a 13:00 Hs y 14:00 a 18:00 Hs.<br>Sabados 9:00 a 14:00 Hs</p>
		</div>
		<div>
			<h2>Contacto</h2>
			<p class="lunic-footer__phone"><a href="https://wa.me/5491123545375"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#368D00" d="M12.04 2C6.58 2 2.15 6.4 2.15 11.83c0 1.74.46 3.44 1.34 4.94L2 22l5.39-1.41a10.1 10.1 0 0 0 4.65 1.12h.01c5.46 0 9.89-4.4 9.89-9.83C21.94 6.4 17.5 2 12.04 2zm5.76 14.15c-.24.68-1.4 1.3-1.94 1.38-.5.08-1.12.11-1.81-.11-.41-.14-.95-.31-1.63-.61-2.87-1.24-4.74-4.13-4.88-4.32-.14-.19-1.16-1.54-1.16-2.94s.73-2.08 1-2.37c.24-.28.64-.41 1.02-.41.12 0 .23 0 .33.01.3.01.44-.03.68.52.24.58.83 2 .9 2.15.07.14.12.32.02.51-.09.19-.14.31-.28.48-.14.16-.29.37-.42.49-.14.14-.28.28-.12.55.16.27.72 1.19 1.55 1.93 1.07.95 1.96 1.25 2.24 1.39.28.14.44.12.6-.07.16-.19.7-.81.88-1.09.19-.28.37-.23.62-.14.26.09 1.62.76 1.9.9.28.14.46.21.53.32.07.12.07.68-.17 1.36z"/></svg> (011) 15 2354-5375</a></p>
			<p>info@distribuidoralunic.com.ar</p>
			<p><strong>Dirección:</strong><br>Av. José María Moreno<br>1280 CABA (Argentina)</p>
		</div>
	</div>
	<div class="lunic-footer__end"></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
