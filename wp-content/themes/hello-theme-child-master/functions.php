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
/* SEGURIDAD */
/* A- Cabeceras de Seguridad */
function cabeceras_seguridad() {
	header("X-Frame-Options: SAMEORIGIN");
	header("X-Content-Type-Options: nosniff");
	header("X-XSS-Protection: 1;mode=block");
	header("Referrer-Policy: no-referrer-when-downgrade");
	header("Content-Security-Policy: upgrade-insecure-requests;");
	header("Strict-Transport-Security: max-age=31536000;");
	header("Permissions-Policy: geolocation=(); midi=();notifications=();push=();sync-xhr=();accelerometer=(); gyroscope=(); magnetometer=(); payment=(); camera=(); microphone=();usb=(); xr=();speaker=(self);vibrate=();fullscreen=(self);");
}
add_action("send_headers", "cabeceras_seguridad");
/* B- Funciones deshabilitadas */
/** Deshabilita XMLRPC **/
add_filter('xmlrpc_enabled', function() {
    return false;
});
/** Deshabilita información innecesaria del <head> **/
add_action('init', function() {
	// Post and comment feed link
    remove_action( 'wp_head', 'feed_links', 2 );
    // Post category links
    remove_action('wp_head', 'feed_links_extra', 3);
    // Link to the Really Simple Discovery service endpoint
    remove_action('wp_head', 'rsd_link');
    // Link to the Windows Live Writer manifest file
    remove_action('wp_head', 'wlwmanifest_link');
    // XHTML generator that is generated on the wp_head hook, WP version
    remove_action('wp_head', 'wp_generator');
    // Start link
    remove_action('wp_head', 'start_post_rel_link');
    // Index link
    remove_action('wp_head', 'index_rel_link');

    // Remove previous link
    remove_action('wp_head', 'parent_post_rel_link', 10, 0);
    // Remove relational links for the posts adjacent to the current post
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    // Remove relational links for the posts adjacent to the current post
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    // Remove REST API links
    remove_action('wp_head', 'rest_output_link_wp_head');
    // Remove Link header for REST API
    remove_action('template_redirect', 'rest_output_link_header', 11, 0 );
    // Remove Link header for shortlink
    remove_action('template_redirect', 'wp_shortlink_header', 11, 0 );
});

/** Deshabilita FEEDS **/
$feeds = [
    'do_feed',
    'do_feed_rdf',
    'do_feed_rss',
    'do_feed_rss2',
    'do_feed_atom',
    'do_feed_rss2_comments',
    'do_feed_atom_comments',
];
foreach($feeds as $feed) {
    add_action($feed, function() {
        wp_die('Feed has been disabled.');
    }, 1);
}
/** No ejecuta el wp-embed.js */
add_action( 'wp_footer', function() {
    wp_deregister_script('wp-embed');
});
/** Deshabilita la REST API para usuarios no logueados **/
add_filter('rest_authentication_errors', function($result) {
   return (is_user_logged_in()) ? $result : new WP_Error('rest_not_logged_in', 'You are not currently logged in.', array   ('status' => 401));
});
/** Deshabilita los REST API endpoints por defecto **/
add_filter('rest_endpoints', function($endpoints) {
    // If user is logged in, allow all endpoints
    if(is_user_logged_in()) {
        return $endpoints;
    }
    foreach($endpoints as $route => $endpoint) {
        if(stripos($route, '/wp/') === 0) {
            unset($endpoints[ $route ]);
        }
    }
    return $endpoints;
});

/** Deshabilita Notificaciones de AutoUpdate en Temas y Plugins **/
add_filter( 'auto_plugin_update_send_email', function() {
    return false;
});
add_filter( 'auto_theme_update_send_email', function() {
    return false;
});
/** Quita x-powered-by header **/
header_remove('x-powered-by');

/** Quita X-Pingback header **/
add_filter('pings_open', function() {
    return false;
});

/* FUNCIONES PERSONALIZADAS */
////* Cambiar nombre al Cartel de Oferta *//
add_filter( 'woocommerce_sale_flash', function( $texto ) {
	return str_replace( __( 'Sale!', 'woocommerce' ), 'Oferta', $texto );
}, 10, 1 );

// Posponer el envio de Mails Transaccionales durante Checkout
add_filter( 'woocommerce_defer_transactional_emails', '__return_true' );

// Filtrar The Generator
add_filter ('the_generator', function () {
  return "";
});

// SE AGREGAN CAMPOS ADICIONALES
add_action( 'woocommerce_after_checkout_billing_form', 'add_campos_personalizados');
 
function add_campos_personalizados ( $checkout ) {
 
     woocommerce_form_field( 'cuitcuil', array(
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('CUIT/CUIL o DNI'),
        'placeholder'   => __('Ingresa aqui CUIT/CUIL o DNI'),
        ), $checkout->get_value( 'cuitcuil' ));
     woocommerce_form_field( 'condicioniva', array(
        'type'          => 'select',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Condición frente al IVA'),
        'options'       => array(
				'Consumidor Final' => __('Consumidor Final'),
				'Responsable Monotributo' => __('Responsable Monotributo'),
				'IVA Responsable Inscripto' => __('IVA Responsable Inscripto'),
				'IVA Responsable no Inscripto' => __('IVA Responsable no Inscripto'),
				'IVA no Responsable' => __('IVA no Responsable'),
				'IVA Sujeto Exento' => __('IVA Sujeto Exento')
                           ),
        ), $checkout->get_value( 'condicioniva' ));
}
/**
 * Actualiza la información del pedido con el nuevo campo
 */
add_action( 'woocommerce_checkout_update_order_meta', 'actualizar_info_pedido_con_nuevo_campo' );
 
function actualizar_info_pedido_con_nuevo_campo( $order_id ) {
    if ( ! empty( $_POST['cuitcuil'] ) ) {
        update_post_meta( $order_id, 'CUIT/CUIL o DNI', sanitize_text_field( $_POST['cuitcuil'] ) );
    }
	    if ( ! empty( $_POST['condicioniva'] ) ) {
        update_post_meta( $order_id, 'Condición frente al IVA', sanitize_text_field( $_POST['condicioniva'] ) );
    }
}

/**
 * Muestra el valor del nuevo campo NIF en la página de edición del pedido
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'mostrar_campo_personalizado_en_admin_pedido', 10, 1 );
 
function mostrar_campo_personalizado_en_admin_pedido($order){
    echo '<p><strong>'.__('CUIT/CUIL o DNI').':</strong> ' . get_post_meta( $order->id, 'CUIT/CUIL o DNI', true ) . '</p>';
	echo '<p><strong>'.__('Condición frente al IVA').':</strong> ' . get_post_meta( $order->id, 'Condición frente al IVA', true ) . '</p>';
}

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