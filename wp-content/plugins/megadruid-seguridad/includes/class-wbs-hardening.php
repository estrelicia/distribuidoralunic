<?php
/**
 * Hardening para cualquier WordPress (Woo, Elementor, brochure).
 * Las opciones se aplican aunque otro plugin (p. ej. agencia-core) ya cubra lo mismo.
 *
 * @package MegadruidBaseSecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WBS_Hardening {

	public static function init(): void {
		add_action( 'init', array( self::class, 'clean_head' ), 5 );

		if ( wbs_setting( 'disable_xmlrpc' ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false', 99 );
			add_filter( 'xmlrpc_methods', '__return_empty_array', 99 );
			add_filter( 'wp_headers', array( self::class, 'remove_pingback_header' ) );
			add_action( 'init', array( self::class, 'kill_xmlrpc_request' ), 0 );
		}

		if ( wbs_setting( 'block_author_enum' ) ) {
			add_action( 'template_redirect', array( self::class, 'block_author_enum' ) );
			add_filter( 'wp_sitemaps_add_provider', array( self::class, 'remove_users_sitemap' ), 10, 2 );
			add_filter( 'oembed_response_data', array( self::class, 'strip_oembed_author' ), 10, 1 );
		}

		if ( wbs_setting( 'block_rest_users' ) ) {
			add_filter( 'rest_endpoints', array( self::class, 'filter_rest_users' ) );
		}

		if ( wbs_setting( 'rest_auth_required' ) ) {
			add_filter( 'rest_authentication_errors', array( self::class, 'rest_auth_required' ) );
		}

		if ( wbs_setting( 'block_readme' ) ) {
			add_action( 'init', array( self::class, 'block_fingerprint_files' ), 0 );
		}

		if ( wbs_setting( 'hide_wp_version' ) ) {
			add_filter( 'the_generator', '__return_empty_string' );
			add_filter( 'style_loader_src', array( self::class, 'strip_ver' ), 9999 );
			add_filter( 'script_loader_src', array( self::class, 'strip_ver' ), 9999 );
			remove_action( 'wp_head', 'wp_generator' );
		}

		if ( wbs_setting( 'disable_feeds' ) ) {
			foreach ( array( 'do_feed', 'do_feed_rdf', 'do_feed_rss', 'do_feed_rss2', 'do_feed_atom', 'do_feed_rss2_comments', 'do_feed_atom_comments' ) as $hook ) {
				add_action( $hook, array( self::class, 'disable_feeds' ), 1 );
			}
		}

		if ( wbs_setting( 'disable_file_edit' ) && ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}

		if ( wbs_setting( 'disable_app_passwords' ) ) {
			add_filter( 'wp_is_application_passwords_available', '__return_false' );
		}

		if ( wbs_setting( 'security_headers' ) ) {
			add_action( 'send_headers', array( self::class, 'headers' ) );
			add_action( 'login_init', array( self::class, 'login_robots' ) );
		}

		if ( function_exists( 'header_remove' ) ) {
			header_remove( 'X-Powered-By' );
		}
	}

	public static function clean_head(): void {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
		add_filter( 'pings_open', '__return_false', 20 );
	}

	/**
	 * @param array<string,string> $headers
	 * @return array<string,string>
	 */
	public static function remove_pingback_header( array $headers ): array {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	public static function kill_xmlrpc_request(): void {
		$blocked = ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST );
		if ( ! $blocked ) {
			$path = strtolower( '/' . ltrim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ) );
			$blocked = str_ends_with( $path, '/xmlrpc.php' );
		}

		if ( ! $blocked ) {
			return;
		}

		status_header( 403 );
		nocache_headers();
		exit;
	}

	public static function block_author_enum(): void {
		if ( is_admin() ) {
			return;
		}

		if ( isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}

		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}

	/**
	 * @param WP_Sitemaps_Provider $provider
	 * @param string               $name
	 * @return WP_Sitemaps_Provider|false
	 */
	public static function remove_users_sitemap( $provider, $name ) {
		if ( 'users' === $name ) {
			return false;
		}

		return $provider;
	}

	/**
	 * @param array<string,mixed> $data
	 * @return array<string,mixed>
	 */
	public static function strip_oembed_author( array $data ): array {
		unset( $data['author_name'], $data['author_url'] );

		return $data;
	}

	/**
	 * @param array<string,mixed> $endpoints
	 * @return array<string,mixed>
	 */
	public static function filter_rest_users( array $endpoints ): array {
		if ( current_user_can( 'list_users' ) ) {
			return $endpoints;
		}

		foreach ( array_keys( $endpoints ) as $route ) {
			if ( preg_match( '#^/wp/v2/users#', (string) $route ) ) {
				unset( $endpoints[ $route ] );
			}
		}

		return $endpoints;
	}

	/**
	 * Cierra toda la REST anónima (estilo megadruid). Rompe tiendas y builders públicos.
	 *
	 * @param WP_Error|null|true $result
	 * @return WP_Error|null|true
	 */
	public static function rest_auth_required( $result ) {
		if ( true === $result || is_wp_error( $result ) ) {
			return $result;
		}

		if ( is_user_logged_in() ) {
			return $result;
		}

		return new WP_Error(
			'wbs_rest_forbidden',
			__( 'La API REST requiere autenticación.', 'megadruid-seguridad' ),
			array( 'status' => 401 )
		);
	}

	public static function block_fingerprint_files(): void {
		if ( is_admin() ) {
			return;
		}

		$uri  = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		if ( $path === '' ) {
			return;
		}

		$path = '/' . ltrim( $path, '/' );

		if ( preg_match( '#^(.*/)?(readme\.html|license\.txt)/?$#i', $path ) ) {
			status_header( 404 );
			nocache_headers();
			exit;
		}
	}

	public static function disable_feeds(): void {
		wp_die(
			esc_html__( 'Los feeds están desactivados.', 'megadruid-seguridad' ),
			'',
			array( 'response' => 404 )
		);
	}

	public static function strip_ver( string $src ): string {
		if ( $src === '' || ! str_contains( $src, 'ver=' ) ) {
			return $src;
		}

		$path = (string) wp_parse_url( $src, PHP_URL_PATH );
		$core = ( defined( 'WPINC' ) ? '/' . WPINC . '/' : '/wp-includes/' );
		if ( ! str_contains( $path, $core ) && ! str_contains( $path, '/wp-admin/' ) ) {
			return $src;
		}

		return remove_query_arg( 'ver', $src );
	}

	public static function login_robots(): void {
		header( 'X-Robots-Tag: noindex, nofollow', false );
	}

	public static function headers(): void {
		if ( headers_sent() ) {
			return;
		}

		header( 'X-Frame-Options: SAMEORIGIN', false );
		header( 'X-Content-Type-Options: nosniff', false );
		header( 'Referrer-Policy: strict-origin-when-cross-origin', false );
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()', false );

		if ( wbs_setting( 'send_hsts' ) && is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains', false );
		}
	}
}
