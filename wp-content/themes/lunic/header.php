<?php defined('ABSPATH') || exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="lunic-header">
	<div class="lunic-header__bar">
		<?php lunic_logo(); ?>
		<nav class="lunic-nav lunic-nav--desktop" aria-label="Inicio">
			<?php wp_nav_menu(['theme_location' => 'inicio', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?>
		</nav>
		<div class="lunic-header__tools">
			<button type="button" class="lunic-menu-btn" aria-expanded="false" aria-controls="lunic-panel" aria-label="Menú"><span></span><span></span><span></span></button>
			<?php lunic_cart_link(); ?>
			<a class="lunic-whatsapp" href="https://wa.me/5491123545375" target="_blank" rel="noopener" aria-label="WhatsApp">
				<svg viewBox="0 0 24 24" width="42" height="42" aria-hidden="true"><path fill="#368D00" d="M12.04 2C6.58 2 2.15 6.4 2.15 11.83c0 1.74.46 3.44 1.34 4.94L2 22l5.39-1.41a10.1 10.1 0 0 0 4.65 1.12h.01c5.46 0 9.89-4.4 9.89-9.83C21.94 6.4 17.5 2 12.04 2zm5.76 14.15c-.24.68-1.4 1.3-1.94 1.38-.5.08-1.12.11-1.81-.11-.41-.14-.95-.31-1.63-.61-2.87-1.24-4.74-4.13-4.88-4.32-.14-.19-1.16-1.54-1.16-2.94s.73-2.08 1-2.37c.24-.28.64-.41 1.02-.41.12 0 .23 0 .33.01.3.01.44-.03.68.52.24.58.83 2 .9 2.15.07.14.12.32.02.51-.09.19-.14.31-.28.48-.14.16-.29.37-.42.49-.14.14-.28.28-.12.55.16.27.72 1.19 1.55 1.93 1.07.95 1.96 1.25 2.24 1.39.28.14.44.12.6-.07.16-.19.7-.81.88-1.09.19-.28.37-.23.62-.14.26.09 1.62.76 1.9.9.28.14.46.21.53.32.07.12.07.68-.17 1.36z"/></svg>
			</a>
		</div>
	</div>
	<div id="lunic-mega" class="lunic-mega" hidden>
		<div class="lunic-mega__cols">
			<nav aria-label="Categorías 01"><?php wp_nav_menu(['theme_location' => 'categorias-01', 'container' => false, 'menu_class' => 'lunic-mega__list', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 02"><?php wp_nav_menu(['theme_location' => 'categorias-02', 'container' => false, 'menu_class' => 'lunic-mega__list', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 03"><?php wp_nav_menu(['theme_location' => 'categorias-03', 'container' => false, 'menu_class' => 'lunic-mega__list', 'fallback_cb' => false]); ?></nav>
		</div>
	</div>
	<div id="lunic-panel" class="lunic-panel">
		<nav aria-label="Menú"><?php wp_nav_menu(['theme_location' => 'inicio', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
		<div id="lunic-panel-cats" class="lunic-panel__cats" hidden>
			<nav aria-label="Categorías"><?php wp_nav_menu(['theme_location' => 'categorias-01', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 02"><?php wp_nav_menu(['theme_location' => 'categorias-02', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 03"><?php wp_nav_menu(['theme_location' => 'categorias-03', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
		</div>
	</div>
</header>
<main class="lunic-main">
