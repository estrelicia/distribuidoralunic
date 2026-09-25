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
	<div class="lunic-wrap lunic-header__bar">
		<?php lunic_logo(); ?>
		<button type="button" class="lunic-menu-btn" aria-expanded="false" aria-controls="lunic-panel">Menú</button>
		<nav class="lunic-nav lunic-nav--desktop" aria-label="Inicio">
			<?php wp_nav_menu(['theme_location' => 'inicio', 'container' => false, 'menu_class' => 'lunic-nav', 'fallback_cb' => false]); ?>
		</nav>
		<?php if (shortcode_exists('lunic_search')) { echo do_shortcode('[lunic_search]'); } ?>
		<?php lunic_cart_link(); ?>
	</div>
	<div id="lunic-panel" class="lunic-panel">
		<div class="lunic-wrap">
			<nav aria-label="Celular"><?php wp_nav_menu(['theme_location' => 'celular', 'container' => false, 'fallback_cb' => false]); ?></nav>
			<details><summary>Categorías 01</summary><?php wp_nav_menu(['theme_location' => 'categorias-01', 'container' => false, 'menu_class' => 'lunic-cats-menu', 'fallback_cb' => false]); ?></details>
			<details><summary>Categorías 02</summary><?php wp_nav_menu(['theme_location' => 'categorias-02', 'container' => false, 'menu_class' => 'lunic-cats-menu', 'fallback_cb' => false]); ?></details>
			<details><summary>Categorías 03</summary><?php wp_nav_menu(['theme_location' => 'categorias-03', 'container' => false, 'menu_class' => 'lunic-cats-menu', 'fallback_cb' => false]); ?></details>
		</div>
	</div>
</header>
<main class="lunic-wrap">
