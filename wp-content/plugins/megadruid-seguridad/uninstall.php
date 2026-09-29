<?php
/**
 * Limpieza al desinstalar (no al desactivar).
 *
 * @package MegadruidBaseSecurity
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wbs_settings' );
delete_option( 'wbs_cf_ranges' );
delete_option( 'wbs_cf_ranges_ts' );
delete_transient( 'wbs_cf_ranges_lock' );
