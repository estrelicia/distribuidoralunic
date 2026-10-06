<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Exportar, importar y restablecer mdcms_settings.
 */
final class Transfer {

    public const EXPORT_ACTION = 'mdcms_export';

    public const IMPORT_ACTION = 'mdcms_import';

    public const RESET_ACTION = 'mdcms_reset';

    public const MAX_BYTES = 262144;

    public function register(): void {
        add_action('admin_post_' . self::EXPORT_ACTION, [$this, 'export']);
        add_action('admin_post_' . self::IMPORT_ACTION, [$this, 'import']);
        add_action('admin_post_' . self::RESET_ACTION, [$this, 'reset']);
    }

    public function export(): void {
        if (!Access::can_see_plugin()) {
            wp_die(esc_html__('No tenés permiso.', 'megadruid-cms'), '', ['response' => 403]);
        }
        check_admin_referer(self::EXPORT_ACTION);
        $json = wp_json_encode(Settings::all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            wp_die(esc_html__('No se pudo armar el JSON.', 'megadruid-cms'));
        }
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="mdcms-settings.json"');
        echo $json;
        exit;
    }

    public function import(): void {
        if (!Access::can_see_plugin()) {
            wp_die(esc_html__('No tenés permiso.', 'megadruid-cms'), '', ['response' => 403]);
        }
        check_admin_referer(self::IMPORT_ACTION);
        $redirect = admin_url('options-general.php?page=' . Settings::PAGE_SLUG . '&tab=dashboard');
        $file = $_FILES['mdcms_import_file'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            self::redirect_error($redirect, 'upload');
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > self::MAX_BYTES) {
            self::redirect_error($redirect, 'size');
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        $raw = $tmp !== '' ? (string) file_get_contents($tmp) : '';
        $result = self::apply_json($raw);
        if ($result !== true) {
            self::redirect_error($redirect, $result);
        }
        wp_safe_redirect(add_query_arg('mdcms_imported', '1', $redirect));
        exit;
    }

    public function reset(): void {
        if (!Access::can_see_plugin()) {
            wp_die(esc_html__('No tenés permiso.', 'megadruid-cms'), '', ['response' => 403]);
        }
        check_admin_referer(self::RESET_ACTION);
        $redirect = admin_url('options-general.php?page=' . Settings::PAGE_SLUG . '&tab=dashboard');
        $confirm = isset($_POST['mdcms_reset_confirm']) && (string) $_POST['mdcms_reset_confirm'] === '1';
        if (!$confirm) {
            self::redirect_error($redirect, 'confirm');
        }
        update_option(Settings::OPTION, Settings::defaults());
        wp_safe_redirect(add_query_arg('mdcms_reset', '1', $redirect));
        exit;
    }

    /**
     * @return true|'json'|'php'|'empty'
     */
    public static function apply_json(string $raw): bool|string {
        if ($raw === '' || strlen($raw) > self::MAX_BYTES) {
            return 'size';
        }
        if (preg_match('/<\?(?:php|=)?/i', $raw)) {
            return 'php';
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return 'json';
        }
        $known = [];
        foreach (Settings::schema() as $key => $field) {
            unset($field);
            if (array_key_exists($key, $decoded)) {
                $known[$key] = $decoded[$key];
            }
        }
        if ($known === []) {
            return 'empty';
        }
        Settings::update($known);

        return true;
    }

    private static function redirect_error(string $redirect, string $code): void {
        wp_safe_redirect(add_query_arg('mdcms_transfer_error', $code, $redirect));
        exit;
    }

    public static function render_settings(): void {
        $export = wp_nonce_url(admin_url('admin-post.php?action=' . self::EXPORT_ACTION), self::EXPORT_ACTION);
        Admin_Layout::open_card(
            __('Copia de ajustes', 'megadruid-cms'),
            __('El archivo es JSON de mdcms_settings. Al importar solo se guardan claves conocidas. Un archivo que no es JSON, que trae PHP o que supera 256 KB se rechaza.', 'megadruid-cms'),
            'dashicons-migrate',
            true
        );
        ?>
        <p>
            <a class="button" href="<?php echo esc_url($export); ?>"><?php esc_html_e('Exportar JSON', 'megadruid-cms'); ?></a>
        </p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field(self::IMPORT_ACTION); ?>
            <input type="hidden" name="action" value="<?php echo esc_attr(self::IMPORT_ACTION); ?>" />
            <p class="mdcms-file">
                <input
                    id="mdcms_import_file"
                    class="mdcms-file__input"
                    type="file"
                    name="mdcms_import_file"
                    accept="application/json,.json"
                    required
                    data-mdcms-file
                />
                <label class="mdcms-file__btn" for="mdcms_import_file"><?php esc_html_e('Elegir archivo', 'megadruid-cms'); ?></label>
                <span
                    class="mdcms-file__name"
                    data-mdcms-file-name
                    data-empty="<?php echo esc_attr__('Ningún archivo elegido', 'megadruid-cms'); ?>"
                ><?php esc_html_e('Ningún archivo elegido', 'megadruid-cms'); ?></span>
                <?php submit_button(__('Importar JSON', 'megadruid-cms'), 'secondary', 'submit', false); ?>
            </p>
        </form>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Se vuelven los valores de fábrica. ¿Seguir?', 'megadruid-cms')); ?>');">
            <?php wp_nonce_field(self::RESET_ACTION); ?>
            <input type="hidden" name="action" value="<?php echo esc_attr(self::RESET_ACTION); ?>" />
            <p>
                <label>
                    <input type="checkbox" name="mdcms_reset_confirm" value="1" required />
                    <?php esc_html_e('Confirmo restablecer todos los ajustes de Megadruid CMS.', 'megadruid-cms'); ?>
                </label>
            </p>
            <?php submit_button(__('Restablecer', 'megadruid-cms'), 'delete', 'submit', false); ?>
        </form>
        <?php
        Admin_Layout::close_card();
    }
}
