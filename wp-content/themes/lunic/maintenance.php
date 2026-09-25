<?php
defined('ABSPATH') || exit;
status_header(503);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>En mantenimiento</title>
	<?php wp_head(); ?>
</head>
<body>
	<main class="lunic-wrap">
		<h1>En mantenimiento</h1>
		<p>La tienda vuelve enseguida.</p>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
