<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Pantalla y sincronización de ajustes de seguridad (etapa 7.4).
 */
final class Security {

    /**
     * @return array<string, string>
     */
    public static function checkbox_fields(): array {
        return [
            'trust_cloudflare' => __('Confiar IP de Cloudflare (CF-Connecting-IP solo si REMOTE_ADDR está en rangos CF). Nunca X-Forwarded-For.', 'megadruid-cms'),
            'login_limit' => __('Limitar intentos de login (bloquea antes de validar la contraseña).', 'megadruid-cms'),
            'generic_login_errors' => __('Mensaje de login genérico (no revelar si el usuario existe).', 'megadruid-cms'),
            'disable_xmlrpc' => __('Desactivar XML-RPC y pingbacks.', 'megadruid-cms'),
            'block_author_enum' => __('Redirigir /author/ y ?author= a la home; quitar autores del sitemap y oEmbed.', 'megadruid-cms'),
            'block_rest_users' => __('Ocultar /wp-json/wp/v2/users salvo a quien pueda listar usuarios (list_users).', 'megadruid-cms'),
            'rest_auth_required' => __('REST anónima cerrada por completo. No activar en WooCommerce, Elementor o headless.', 'megadruid-cms'),
            'block_readme' => __('404 a readme.html y license.txt.', 'megadruid-cms'),
            'hide_wp_version' => __('Ocultar versión de WordPress en HTML y ?ver= del núcleo.', 'megadruid-cms'),
            'disable_feeds' => __('Desactivar feeds RSS/Atom.', 'megadruid-cms'),
            'disable_file_edit' => __('Desactivar el editor de archivos en el admin (DISALLOW_FILE_EDIT).', 'megadruid-cms'),
            'disable_app_passwords' => __('Desactivar contraseñas de aplicación. Desmarcar si usás Jetpack o apps oficiales.', 'megadruid-cms'),
            'security_headers' => __('Cabeceras X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy.', 'megadruid-cms'),
            'send_hsts' => __('Enviar HSTS desde PHP. Dejar apagado si Cloudflare ya envía HSTS.', 'megadruid-cms'),
        ];
    }

    /**
     * @return string[]
     */
    public static function bool_keys(): array {
        return array_keys(self::checkbox_fields());
    }

    /**
     * @param array<string, mixed> $posted
     * @return array<string, mixed>
     */
    public static function fill_missing(array $posted): array {
        foreach (self::bool_keys() as $key) {
            if (!array_key_exists($key, $posted)) {
                $posted[$key] = 0;
            }
        }

        return $posted;
    }

    /**
     * Mientras Megadruid Seguridad siga activo, refleja los mismos valores en wbs_settings.
     */
    public static function sync_legacy_plugin(): void {
        if (!function_exists('wbs_settings')) {
            return;
        }
        $keys = array_merge(self::bool_keys(), ['login_max', 'login_window']);
        $patch = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, Settings::schema())) {
                $patch[$key] = Settings::get($key);
            }
        }
        if ($patch === []) {
            return;
        }
        update_option('wbs_settings', array_merge(wbs_settings(), $patch));
    }

    public static function render_settings(): void {
        $ip = Client_Ip::client_ip();
        $remote = Client_Ip::sanitize_ip((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        $country = Client_Ip::country();
        $from_cf = $remote !== '' && Client_Ip::is_cloudflare_addr($remote);
        $legacy = Login_Limit::delegated_to_legacy();
        Admin_Layout::open_card(
            __('Estado de esta visita', 'megadruid-cms'),
            __('Capa base para WordPress detrás de Cloudflare. No sustituye el WAF ni el 2FA.', 'megadruid-cms'),
            'dashicons-info'
        );
        ?>
        <table class="widefat striped mdcms-security-status">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('IP detectada', 'megadruid-cms'); ?></th>
                    <td><code><?php echo esc_html($ip); ?></code></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('REMOTE_ADDR es Cloudflare', 'megadruid-cms'); ?></th>
                    <td>
                        <?php
                        echo $from_cf
                            ? esc_html__('Sí (se usa CF-Connecting-IP)', 'megadruid-cms')
                            : esc_html__('No (se usa REMOTE_ADDR)', 'megadruid-cms');
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('CF-IPCountry', 'megadruid-cms'); ?></th>
                    <td>
                        <?php
                        echo $country !== ''
                            ? esc_html($country)
                            : esc_html__('(vacío: activá IP Geolocation en Cloudflare)', 'megadruid-cms');
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php if ($legacy) : ?>
            <p class="description"><?php esc_html_e('Megadruid Seguridad sigue aplicando el límite de login; los números de abajo se guardan aquí y se sincronizan allí.', 'megadruid-cms'); ?></p>
        <?php endif; ?>
        <?php
        Admin_Layout::close_card();
        Admin_Layout::open_card(
            __('Endurecimiento', 'megadruid-cms'),
            __('Si Megadruid Seguridad sigue activo, estas casillas también se copian a ese plugin.', 'megadruid-cms'),
            'dashicons-shield-alt'
        );
        ?>
        <table class="form-table" role="presentation">
            <?php
            foreach (self::checkbox_fields() as $key => $label) {
                self::render_checkbox_row($key, $label);
            }
            ?>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_max"><?php esc_html_e('Máximo de fallos', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        class="small-text"
                        id="mdcms_login_max"
                        name="mdcms[login_max]"
                        min="3"
                        max="20"
                        value="<?php echo esc_attr((string) (int) Settings::get('login_max', 5)); ?>"
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_window"><?php esc_html_e('Ventana (minutos)', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        class="small-text"
                        id="mdcms_login_window"
                        name="mdcms[login_window]"
                        min="5"
                        max="120"
                        value="<?php echo esc_attr((string) (int) Settings::get('login_window', 15)); ?>"
                    />
                </td>
            </tr>
        </table>
        <?php
        Admin_Layout::close_card();
    }

    private static function render_checkbox_row(string $key, string $label): void {
        $checked = (bool) Settings::get($key, false);
        $id = 'mdcms_' . $key;
        $titles = self::checkbox_titles();
        $title = $titles[$key] ?? $key;
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($title); ?></label></th>
            <td>
                <label for="<?php echo esc_attr($id); ?>">
                    <input type="checkbox" id="<?php echo esc_attr($id); ?>" name="mdcms[<?php echo esc_attr($key); ?>]" value="1" <?php checked($checked); ?> />
                    <?php echo esc_html($label); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    /**
     * @return array<string, string>
     */
    private static function checkbox_titles(): array {
        return [
            'trust_cloudflare' => __('Cloudflare', 'megadruid-cms'),
            'login_limit' => __('Límite de login', 'megadruid-cms'),
            'generic_login_errors' => __('Mensaje genérico', 'megadruid-cms'),
            'disable_xmlrpc' => __('XML-RPC', 'megadruid-cms'),
            'block_author_enum' => __('Autores', 'megadruid-cms'),
            'block_rest_users' => __('Usuarios REST', 'megadruid-cms'),
            'rest_auth_required' => __('REST anónima', 'megadruid-cms'),
            'block_readme' => __('readme.html', 'megadruid-cms'),
            'hide_wp_version' => __('Versión', 'megadruid-cms'),
            'disable_feeds' => __('Feeds', 'megadruid-cms'),
            'disable_file_edit' => __('Editor de archivos', 'megadruid-cms'),
            'disable_app_passwords' => __('Contraseñas de app', 'megadruid-cms'),
            'security_headers' => __('Cabeceras', 'megadruid-cms'),
            'send_hsts' => __('HSTS', 'megadruid-cms'),
        ];
    }
}
