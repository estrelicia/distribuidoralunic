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
<?php
$lunic_header = function_exists('lunic_elementor_slot') ? lunic_elementor_slot('header') : '';
$lunic_categories = function_exists('lunic_elementor_slot') ? lunic_elementor_slot('categories') : '';
echo '<header class="lunic-header">';
if ($lunic_header !== '') {
    echo $lunic_header;
    echo '<button type="button" class="lunic-menu-btn" aria-expanded="false" aria-controls="lunic-panel" aria-label="Menú"><span></span><span></span><span></span></button>';
} else {
?>
	<div class="lunic-header__bar">
		<?php lunic_logo(); ?>
		<nav class="lunic-nav lunic-nav--desktop" aria-label="Inicio">
			<?php wp_nav_menu(['theme_location' => 'inicio', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?>
		</nav>
		<div class="lunic-header__tools">
			<button type="button" class="lunic-menu-btn" aria-expanded="false" aria-controls="lunic-panel" aria-label="Menú"><span></span><span></span><span></span></button>
			<?php lunic_cart_link(); ?>
			<a class="lunic-whatsapp" href="https://wa.me/5491123545375" target="_blank" rel="noopener" aria-label="WhatsApp">
				<svg viewBox="0 0 448 512" width="55" height="55" aria-hidden="true"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
			</a>
		</div>
	</div>
<?php } ?>
	<div id="lunic-mega" class="lunic-mega" hidden>
		<?php if (class_exists(\Distribuidora_Lunic\Modules\MegaMenu\Menu::class)) : ?>
			<?php echo \Distribuidora_Lunic\Modules\MegaMenu\Menu::desktop(); ?>
		<?php elseif ($lunic_categories !== '') : ?>
			<?php echo $lunic_categories; ?>
		<?php else : ?>
		<div class="lunic-mega__cols">
			<nav aria-label="Categorías 01"><?php wp_nav_menu(['theme_location' => 'categorias-01', 'container' => false, 'menu_class' => 'lunic-mega__list', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 02"><?php wp_nav_menu(['theme_location' => 'categorias-02', 'container' => false, 'menu_class' => 'lunic-mega__list', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 03"><?php wp_nav_menu(['theme_location' => 'categorias-03', 'container' => false, 'menu_class' => 'lunic-mega__list', 'fallback_cb' => false]); ?></nav>
		</div>
		<?php endif; ?>
	</div>
	<div id="lunic-panel" class="lunic-panel">
		<nav aria-label="Menú"><?php wp_nav_menu(['theme_location' => 'inicio', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
		<div id="lunic-panel-cats" class="lunic-panel__cats" hidden>
			<?php if (class_exists(\Distribuidora_Lunic\Modules\MegaMenu\Menu::class)) : ?>
				<?php echo \Distribuidora_Lunic\Modules\MegaMenu\Menu::mobile(); ?>
			<?php else : ?>
			<nav aria-label="Categorías"><?php wp_nav_menu(['theme_location' => 'categorias-01', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 02"><?php wp_nav_menu(['theme_location' => 'categorias-02', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
			<nav aria-label="Categorías 03"><?php wp_nav_menu(['theme_location' => 'categorias-03', 'container' => false, 'menu_class' => 'lunic-menu', 'fallback_cb' => false]); ?></nav>
			<?php endif; ?>
		</div>
	</div>
</header>
<main class="lunic-main">
