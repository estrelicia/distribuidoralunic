<?php
/**
 * Límite de login sobre authenticate (antes de validar la contraseña).
 *
 * @package MegadruidBaseSecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WBS_Login {

	public static function init(): void {
		if ( wbs_setting( 'login_limit' ) ) {
			add_filter( 'authenticate', array( self::class, 'block_locked' ), 5, 3 );
			add_action( 'wp_login_failed', array( self::class, 'on_fail' ), 10, 1 );
			add_action( 'wp_login', array( self::class, 'on_success' ), 10, 2 );
		}

		if ( wbs_setting( 'generic_login_errors' ) ) {
			add_filter( 'login_errors', array( self::class, 'generic_error' ) );
		}
	}

	/**
	 * @param WP_User|WP_Error|null $user
	 * @return WP_User|WP_Error|null
	 */
	public static function block_locked( $user, $username, $password ) {
		$username = is_string( $username ) ? $username : '';
		$password = is_string( $password ) ? $password : '';
		if ( $username === '' && $password === '' ) {
			return $user;
		}

		if ( self::is_locked( $username ) ) {
			return new WP_Error(
				'wbs_locked',
				__( 'Demasiados intentos. Esperá unos minutos e intentá de nuevo.', 'megadruid-seguridad' )
			);
		}

		return $user;
	}

	public static function on_fail( $username ): void {
		$username = sanitize_user( (string) $username, true );
		if ( $username === '' ) {
			return;
		}
		$ip       = WBS_IP::client_ip();
		$max      = max( 1, (int) wbs_setting( 'login_max' ) );
		$window   = max( 1, (int) wbs_setting( 'login_window' ) ) * MINUTE_IN_SECONDS;

		foreach ( array( self::key_ip( $ip ), self::key_pair( $ip, $username ) ) as $key ) {
			$count = (int) get_transient( $key );
			set_transient( $key, $count + 1, $window );
		}

		$ip_count = (int) get_transient( self::key_ip( $ip ) );
		if ( $ip_count >= $max ) {
			set_transient( self::lock_key( $ip ), 1, $window );
		}
	}

	/**
	 * @param string  $user_login
	 * @param WP_User $user
	 */
	public static function on_success( $user_login, $user ): void {
		unset( $user );
		$ip       = WBS_IP::client_ip();
		$username = sanitize_user( (string) $user_login, true );
		delete_transient( self::key_ip( $ip ) );
		delete_transient( self::lock_key( $ip ) );
		if ( $username !== '' ) {
			delete_transient( self::key_pair( $ip, $username ) );
		}
	}

	public static function generic_error( $error ): string {
		$ip = WBS_IP::client_ip();
		if ( get_transient( self::lock_key( $ip ) ) || ( is_string( $error ) && ( str_contains( $error, 'too_many' ) || str_contains( $error, 'Demasiados' ) || str_contains( $error, 'wbs_locked' ) ) ) ) {
			return __( 'Demasiados intentos. Esperá unos minutos e intentá de nuevo.', 'megadruid-seguridad' );
		}

		return __( 'Credenciales incorrectas.', 'megadruid-seguridad' );
	}

	private static function is_locked( string $username ): bool {
		$ip = WBS_IP::client_ip();
		if ( get_transient( self::lock_key( $ip ) ) ) {
			return true;
		}

		$max = max( 1, (int) wbs_setting( 'login_max' ) );
		if ( (int) get_transient( self::key_ip( $ip ) ) >= $max ) {
			return true;
		}

		$username = sanitize_user( $username, true );
		if ( $username !== '' && (int) get_transient( self::key_pair( $ip, $username ) ) >= $max ) {
			return true;
		}

		return false;
	}

	private static function key_ip( string $ip ): string {
		return 'wbs_lf_' . md5( $ip );
	}

	private static function lock_key( string $ip ): string {
		return 'wbs_lk_' . md5( $ip );
	}

	private static function key_pair( string $ip, string $username ): string {
		return 'wbs_lu_' . md5( $ip . '|' . strtolower( $username ) );
	}
}
