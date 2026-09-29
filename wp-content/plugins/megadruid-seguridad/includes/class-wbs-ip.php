<?php
/**
 * IP del cliente detrás de Cloudflare.
 * Confía en CF-Connecting-IP solo si REMOTE_ADDR está en rangos de Cloudflare.
 * Nunca usa X-Forwarded-For ni Client-IP (el cliente las inventa).
 *
 * @package MegadruidBaseSecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WBS_IP {

	public static function init(): void {
		add_action( 'init', array( self::class, 'maybe_refresh_ranges' ), 1 );
	}

	public static function client_ip(): string {
		$remote = self::sanitize_ip( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );

		if ( ! wbs_setting( 'trust_cloudflare' ) ) {
			return $remote !== '' ? $remote : '0.0.0.0';
		}

		$cf = self::sanitize_ip( (string) ( $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '' ) );
		if ( $cf !== '' && self::is_cloudflare_addr( $remote ) ) {
			return $cf;
		}

		return $remote !== '' ? $remote : '0.0.0.0';
	}

	public static function country(): string {
		$code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '' ) ) );
		if ( preg_match( '/^[A-Z]{2}$/', $code ) ) {
			return $code;
		}

		return '';
	}

	public static function is_cloudflare_addr( string $ip ): bool {
		$ip = self::sanitize_ip( $ip );
		if ( $ip === '' ) {
			return false;
		}

		$ranges = self::ranges();
		$family = str_contains( $ip, ':' ) ? 'v6' : 'v4';
		$list   = $ranges[ $family ] ?? array();
		if ( ! is_array( $list ) ) {
			return false;
		}

		foreach ( $list as $cidr ) {
			if ( is_string( $cidr ) && $cidr !== '' && self::ip_in_cidr( $ip, $cidr ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array{v4: string[], v6: string[]}
	 */
	public static function ranges(): array {
		$cached = get_option( 'wbs_cf_ranges', null );
		if ( is_array( $cached ) && self::valid_range_list( $cached['v4'] ?? null ) && self::valid_range_list( $cached['v6'] ?? null ) ) {
			return array(
				'v4' => array_values( $cached['v4'] ),
				'v6' => array_values( $cached['v6'] ),
			);
		}

		/** @var array{v4: string[], v6: string[]} $bundled */
		$bundled = require WBS_DIR . 'includes/cloudflare-ranges.php';

		return $bundled;
	}

	private static function valid_range_list( mixed $list ): bool {
		return is_array( $list ) && $list !== [] && is_string( $list[ array_key_first( $list ) ] ?? null );
	}

	public static function maybe_refresh_ranges(): void {
		if ( wp_installing() ) {
			return;
		}

		$stamp = (int) get_option( 'wbs_cf_ranges_ts', 0 );
		if ( $stamp > time() - WEEK_IN_SECONDS ) {
			return;
		}

		if ( get_transient( 'wbs_cf_ranges_lock' ) ) {
			return;
		}
		set_transient( 'wbs_cf_ranges_lock', 1, 5 * MINUTE_IN_SECONDS );

		$v4 = self::fetch_list( 'https://www.cloudflare.com/ips-v4' );
		$v6 = self::fetch_list( 'https://www.cloudflare.com/ips-v6' );

		if ( $v4 && $v6 ) {
			update_option(
				'wbs_cf_ranges',
				array(
					'v4' => $v4,
					'v6' => $v6,
				),
				false
			);
			update_option( 'wbs_cf_ranges_ts', time(), false );
			return;
		}

		update_option( 'wbs_cf_ranges_ts', time() - WEEK_IN_SECONDS + HOUR_IN_SECONDS, false );
	}

	/**
	 * @return string[]|null
	 */
	private static function fetch_list( string $url ): ?array {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 8,
				'redirection' => 2,
				'user-agent'  => 'MegadruidSeguridad/' . WBS_VERSION,
			)
		);

		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return null;
		}

		$body  = (string) wp_remote_retrieve_body( $response );
		$lines = preg_split( '/\R/', $body ) ?: array();
		$out   = array();

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( $line !== '' && preg_match( '/^[0-9a-fA-F:\.]+\/\d{1,3}$/', $line ) ) {
				$out[] = $line;
			}
		}

		return count( $out ) >= 3 ? $out : null;
	}

	public static function ip_in_cidr( string $ip, string $cidr ): bool {
		if ( ! str_contains( $cidr, '/' ) ) {
			return $ip === $cidr;
		}

		[ $subnet, $bits ] = explode( '/', $cidr, 2 );
		$bits              = (int) $bits;
		$ip_bin            = @inet_pton( $ip );
		$sub_bin           = @inet_pton( $subnet );

		if ( false === $ip_bin || false === $sub_bin || strlen( $ip_bin ) !== strlen( $sub_bin ) ) {
			return false;
		}

		$max = 8 * strlen( $ip_bin );
		if ( $bits < 1 || $bits > $max ) {
			return false;
		}
		$bytes = intdiv( $bits, 8 );
		$rest  = $bits % 8;

		if ( $bytes && substr( $ip_bin, 0, $bytes ) !== substr( $sub_bin, 0, $bytes ) ) {
			return false;
		}

		if ( $rest === 0 ) {
			return true;
		}

		$mask = chr( ( 0xFF << ( 8 - $rest ) ) & 0xFF );

		return ( $ip_bin[ $bytes ] & $mask ) === ( $sub_bin[ $bytes ] & $mask );
	}

	private static function sanitize_ip( string $raw ): string {
		$raw = trim( $raw );
		if ( $raw === '' ) {
			return '';
		}

		if ( str_contains( $raw, ',' ) ) {
			$raw = trim( explode( ',', $raw )[0] );
		}

		return filter_var( $raw, FILTER_VALIDATE_IP ) ? $raw : '';
	}
}
