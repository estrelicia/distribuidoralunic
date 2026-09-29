<?php
/**
 * Ajustes del plugin.
 *
 * @package MegadruidBaseSecurity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WBS_Admin {

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ) );
		add_action( 'admin_init', array( self::class, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WBS_FILE ), array( self::class, 'links' ) );
	}

	/**
	 * @param string[] $links
	 * @return string[]
	 */
	public static function links( array $links ): array {
		$url = admin_url( 'options-general.php?page=megadruid-seguridad' );
		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Ajustes', 'megadruid-seguridad' ) . '</a>'
		);

		return $links;
	}

	public static function menu(): void {
		add_options_page(
			__( 'Megadruid Seguridad', 'megadruid-seguridad' ),
			__( 'Seguridad Megadruid', 'megadruid-seguridad' ),
			'manage_options',
			'megadruid-seguridad',
			array( self::class, 'render' )
		);
	}

	public static function register(): void {
		register_setting(
			'wbs_settings_group',
			'wbs_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'default'           => wbs_default_settings(),
				'show_in_rest'      => false,
				'capability'        => 'manage_options',
			)
		);
	}

	/**
	 * @param mixed $input
	 * @return array<string,mixed>
	 */
	public static function sanitize( $input ): array {
		$defaults = wbs_default_settings();
		$input    = is_array( $input ) ? $input : array();
		$out      = $defaults;

		$checks = array(
			'trust_cloudflare',
			'login_limit',
			'generic_login_errors',
			'disable_xmlrpc',
			'block_author_enum',
			'block_rest_users',
			'rest_auth_required',
			'block_readme',
			'hide_wp_version',
			'disable_feeds',
			'disable_file_edit',
			'disable_app_passwords',
			'security_headers',
			'send_hsts',
		);

		foreach ( $checks as $key ) {
			$out[ $key ] = ! empty( $input[ $key ] );
		}

		$out['login_max']    = max( 3, min( 20, (int) ( $input['login_max'] ?? $defaults['login_max'] ) ) );
		$out['login_window'] = max( 5, min( 120, (int) ( $input['login_window'] ?? $defaults['login_window'] ) ) );

		return $out;
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$s  = wbs_settings();
		$ip = WBS_IP::client_ip();
		$cc = WBS_IP::country();
		$cf = WBS_IP::is_cloudflare_addr( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Megadruid Seguridad', 'megadruid-seguridad' ); ?></h1>
			<p><?php esc_html_e( 'Capa base para cualquier WordPress detrás de Cloudflare. No sustituye el WAF ni el 2FA. Puede convivir con otros plugins de seguridad.', 'megadruid-seguridad' ); ?></p>

			<table class="widefat striped" style="max-width:720px;margin:16px 0;">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'IP detectada', 'megadruid-seguridad' ); ?></th>
						<td><code><?php echo esc_html( $ip ); ?></code></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'REMOTE_ADDR es Cloudflare', 'megadruid-seguridad' ); ?></th>
						<td><?php echo $cf ? esc_html__( 'Sí (se usa CF-Connecting-IP)', 'megadruid-seguridad' ) : esc_html__( 'No (se usa REMOTE_ADDR)', 'megadruid-seguridad' ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'CF-IPCountry', 'megadruid-seguridad' ); ?></th>
						<td><?php echo $cc !== '' ? esc_html( $cc ) : esc_html__( '(vacío: activá IP Geolocation en Cloudflare)', 'megadruid-seguridad' ); ?></td>
					</tr>
				</tbody>
			</table>

			<form method="post" action="options.php">
				<?php settings_fields( 'wbs_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<?php
					self::checkbox( 'trust_cloudflare', __( 'Confiar IP de Cloudflare (CF-Connecting-IP solo si REMOTE_ADDR está en rangos CF). Nunca X-Forwarded-For.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'login_limit', __( 'Limitar intentos de login (bloquea antes de validar la contraseña).', 'megadruid-seguridad' ), $s );
					?>
					<tr>
						<th><?php esc_html_e( 'Máximo de fallos', 'megadruid-seguridad' ); ?></th>
						<td>
							<input name="wbs_settings[login_max]" type="number" min="3" max="20" value="<?php echo esc_attr( (string) $s['login_max'] ); ?>">
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Ventana (minutos)', 'megadruid-seguridad' ); ?></th>
						<td>
							<input name="wbs_settings[login_window]" type="number" min="5" max="120" value="<?php echo esc_attr( (string) $s['login_window'] ); ?>">
						</td>
					</tr>
					<?php
					self::checkbox( 'generic_login_errors', __( 'Mensaje de login genérico (no revelar si el usuario existe).', 'megadruid-seguridad' ), $s );
					self::checkbox( 'disable_xmlrpc', __( 'Desactivar XML-RPC y pingbacks.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'block_author_enum', __( 'Redirigir /author/ y ?author= a la home; quitar autores del sitemap y oEmbed.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'block_rest_users', __( 'Ocultar /wp-json/wp/v2/users salvo a quien pueda listar usuarios (list_users).', 'megadruid-seguridad' ), $s );
					self::checkbox( 'rest_auth_required', __( 'REST anónima cerrada por completo. No activar en WooCommerce, Elementor o headless.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'block_readme', __( '404 a readme.html y license.txt.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'hide_wp_version', __( 'Ocultar versión de WordPress en HTML y ?ver= de core.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'disable_feeds', __( 'Desactivar feeds RSS/Atom.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'disable_file_edit', __( 'Desactivar el editor de archivos en el admin (DISALLOW_FILE_EDIT).', 'megadruid-seguridad' ), $s );
					self::checkbox( 'disable_app_passwords', __( 'Desactivar contraseñas de aplicación. Desmarcar si usás Jetpack o apps oficiales.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'security_headers', __( 'Cabeceras X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy.', 'megadruid-seguridad' ), $s );
					self::checkbox( 'send_hsts', __( 'Enviar HSTS desde PHP. Dejar apagado si Cloudflare ya envía HSTS.', 'megadruid-seguridad' ), $s );
					?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param array<string,mixed> $s
	 */
	private static function checkbox( string $key, string $label, array $s ): void {
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $key ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="wbs_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $s[ $key ] ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			</td>
		</tr>
		<?php
	}
}
