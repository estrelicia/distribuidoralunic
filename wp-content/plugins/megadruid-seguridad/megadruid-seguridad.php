<?php
/**
 * Plugin Name: Megadruid Seguridad
 * Plugin URI: https://megadruid.com
 * Description: Capa base de seguridad para WordPress detrás de Cloudflare: IP real, límite de login, enumeración, XML-RPC, REST de usuarios y cabeceras. No reemplaza el WAF de Cloudflare.
 * Version: 1.0.2
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Megadruid agencia digital
 * License: GPL-2.0-or-later
 * Text Domain: megadruid-seguridad
 *
 * @package MegadruidBaseSecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WBS_VERSION' ) ) {
	return;
}

define( 'WBS_VERSION', '1.0.2' );
if ( ! defined( 'WBS_FILE' ) ) {
	define( 'WBS_FILE', __FILE__ );
}
define( 'WBS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WBS_URL', plugin_dir_url( __FILE__ ) );

require_once WBS_DIR . 'includes/class-wbs-ip.php';
require_once WBS_DIR . 'includes/class-wbs-login.php';
require_once WBS_DIR . 'includes/class-wbs-hardening.php';
require_once WBS_DIR . 'includes/class-wbs-admin.php';

/**
 * Ajustes por defecto (seguros para WooCommerce / Elementor).
 *
 * @return array<string,mixed>
 */
function wbs_default_settings(): array {
	return array(
		'trust_cloudflare'      => true,
		'login_limit'           => true,
		'login_max'             => 5,
		'login_window'          => 15,
		'generic_login_errors'  => true,
		'disable_xmlrpc'        => true,
		'block_author_enum'     => true,
		'block_rest_users'      => true,
		'rest_auth_required'    => false,
		'block_readme'          => true,
		'hide_wp_version'       => true,
		'disable_feeds'         => true,
		'disable_file_edit'     => true,
		'disable_app_passwords' => true,
		'security_headers'      => true,
		'send_hsts'             => false,
	);
}

/**
 * @return array<string,mixed>
 */
function wbs_settings(): array {
	$stored = get_option( 'wbs_settings', array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return array_merge( wbs_default_settings(), $stored );
}

/**
 * @param string $key
 * @return mixed
 */
function wbs_setting( string $key ) {
	$all = wbs_settings();

	return $all[ $key ] ?? null;
}

function wbs_migrate_active_basename(): void {
	$old = 'megadruid-seguridad/watsabi-base-security.php';
	$new = 'megadruid-seguridad/megadruid-seguridad.php';
	$active = get_option( 'active_plugins', array() );
	if ( ! is_array( $active ) || ! in_array( $old, $active, true ) ) {
		return;
	}

	$active = array_values( array_unique( array_merge( array_diff( $active, array( $old ) ), array( $new ) ) ) );
	update_option( 'active_plugins', $active );
}

function wbs_boot(): void {
	WBS_IP::init();
	WBS_Login::init();
	WBS_Hardening::init();

	if ( is_admin() ) {
		WBS_Admin::init();
	}
}

add_action( 'plugins_loaded', 'wbs_migrate_active_basename', 0 );
add_action( 'plugins_loaded', 'wbs_boot' );

register_activation_hook(
	WBS_FILE,
	static function (): void {
		if ( false === get_option( 'wbs_settings', false ) ) {
			add_option( 'wbs_settings', wbs_default_settings() );
		}
	}
);
