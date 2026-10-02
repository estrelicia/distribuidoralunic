<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Asistente de puesta en marcha. Escribe en las mismas claves que las pestañas.
 */
final class Wizard {

    public const ACTION = 'mdcms_wizard_save';

    public function register(): void {
        add_action('admin_post_' . self::ACTION, [$this, 'save']);
    }

    public static function is_complete(): bool {
        return (bool) Settings::get('wizard_completed', false);
    }

    public static function render(): void {
        $step = isset($_GET['mdcms_step']) ? (int) $_GET['mdcms_step'] : 1;
        if ($step < 1 || $step > 4) {
            $step = 1;
        }
        $base = admin_url('options-general.php?page=' . Settings::PAGE_SLUG . '&tab=general');
        ?>
        <div class="mdcms-wizard">
            <h2><?php esc_html_e('Asistente de puesta en marcha', 'megadruid-cms'); ?></h2>
            <p class="description">
                <?php esc_html_e('Cuatro pasos opcionales. Podés saltar cualquiera. Los datos se guardan en las mismas opciones que las pestañas Marca, Login y Escritorio.', 'megadruid-cms'); ?>
            </p>
            <p><strong><?php echo esc_html(sprintf(__('Paso %1$d de 4', 'megadruid-cms'), $step)); ?></strong></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field(self::ACTION, 'mdcms_wizard_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION); ?>" />
                <input type="hidden" name="mdcms_step" value="<?php echo esc_attr((string) $step); ?>" />
                <?php self::render_step($step); ?>
                <p class="submit">
                    <?php if ($step > 1) : ?>
                        <a class="button" href="<?php echo esc_url(add_query_arg('mdcms_step', $step - 1, $base)); ?>"><?php esc_html_e('Anterior', 'megadruid-cms'); ?></a>
                    <?php endif; ?>
                    <?php submit_button($step === 4 ? __('Finalizar', 'megadruid-cms') : __('Siguiente', 'megadruid-cms'), 'primary', 'mdcms_wizard_next', false); ?>
                    <?php if ($step < 4) : ?>
                        <a class="button" href="<?php echo esc_url(add_query_arg('mdcms_step', $step + 1, $base)); ?>"><?php esc_html_e('Saltar paso', 'megadruid-cms'); ?></a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        <hr />
        <?php
    }

    private static function render_step(int $step): void {
        if ($step === 1) {
            $logo = (string) Settings::get('login_logo', '');
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Logo de login', 'megadruid-cms'); ?></th>
                    <td>
                        <input type="url" class="regular-text" name="mdcms_wizard[login_logo]" value="<?php echo esc_url($logo); ?>" />
                        <p class="description"><?php esc_html_e('URL de la imagen. Podés ajustarla después en la pestaña Login.', 'megadruid-cms'); ?></p>
                    </td>
                </tr>
            </table>
            <?php
            return;
        }
        if ($step === 2) {
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="mdcms_wizard_business"><?php esc_html_e('Nombre del sitio', 'megadruid-cms'); ?></label></th>
                    <td><input type="text" class="regular-text" id="mdcms_wizard_business" name="mdcms_wizard[business_name]" value="<?php echo esc_attr((string) Settings::get('custom_page_title', '')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mdcms_wizard_footer_text"><?php esc_html_e('Texto del pie', 'megadruid-cms'); ?></label></th>
                    <td><input type="text" class="regular-text" id="mdcms_wizard_footer_text" name="mdcms_wizard[footer_text]" value="<?php echo esc_attr((string) Settings::get('footer_text', '')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mdcms_wizard_footer_url"><?php esc_html_e('URL del pie', 'megadruid-cms'); ?></label></th>
                    <td><input type="url" class="regular-text" id="mdcms_wizard_footer_url" name="mdcms_wizard[footer_url]" value="<?php echo esc_url((string) Settings::get('footer_url', '')); ?>" /></td>
                </tr>
            </table>
            <?php
            return;
        }
        if ($step === 3) {
            $panels = Settings::get('welcome_panels', []);
            $panel = is_array($panels) && isset($panels[0]) && is_array($panels[0]) ? $panels[0] : [];
            ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Panel de bienvenida', 'megadruid-cms'); ?></th>
                    <td>
                        <label><input type="checkbox" name="mdcms_wizard[welcome_enabled]" value="1" <?php checked(!empty($panel['enabled'])); ?> /> <?php esc_html_e('Activar el primer panel del escritorio', 'megadruid-cms'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mdcms_wizard_welcome_title"><?php esc_html_e('Título', 'megadruid-cms'); ?></label></th>
                    <td><input type="text" class="regular-text" id="mdcms_wizard_welcome_title" name="mdcms_wizard[welcome_title]" value="<?php echo esc_attr((string) ($panel['title'] ?? '')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mdcms_wizard_welcome_html"><?php esc_html_e('Contenido HTML', 'megadruid-cms'); ?></label></th>
                    <td><textarea class="large-text" rows="5" id="mdcms_wizard_welcome_html" name="mdcms_wizard[welcome_html]"><?php echo esc_textarea((string) ($panel['html'] ?? '')); ?></textarea></td>
                </tr>
            </table>
            <?php
            return;
        }
        ?>
        <p><?php esc_html_e('Revisá los datos y pulsá Finalizar. Podés cambiar todo después en las pestañas del plugin.', 'megadruid-cms'); ?></p>
        <ul class="mdcms-wizard-summary">
            <li><?php echo esc_html(sprintf(__('Logo de login: %s', 'megadruid-cms'), (string) Settings::get('login_logo', '') !== '' ? __('configurado', 'megadruid-cms') : __('vacío', 'megadruid-cms'))); ?></li>
            <li><?php echo esc_html(sprintf(__('Nombre: %s', 'megadruid-cms'), (string) Settings::get('custom_page_title', ''))); ?></li>
            <li><?php echo esc_html(sprintf(__('Pie: %s', 'megadruid-cms'), (string) Settings::get('footer_text', ''))); ?></li>
        </ul>
        <?php
    }

    public function save(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tenés permiso.', 'megadruid-cms'), '', ['response' => 403]);
        }
        check_admin_referer(self::ACTION, 'mdcms_wizard_nonce');
        $step = isset($_POST['mdcms_step']) ? (int) $_POST['mdcms_step'] : 1;
        $raw = isset($_POST['mdcms_wizard']) && is_array($_POST['mdcms_wizard'])
            ? wp_unslash($_POST['mdcms_wizard'])
            : [];
        $patch = [];
        if ($step === 1 && isset($raw['login_logo'])) {
            $patch['login_logo'] = $raw['login_logo'];
        }
        if ($step === 2) {
            if (isset($raw['business_name'])) {
                $name = sanitize_text_field((string) $raw['business_name']);
                $patch['custom_page_title'] = $name;
                $patch['dashboard_title'] = $name;
                $patch['admin_bar_alt_text'] = $name;
                $patch['side_menu_alt_text'] = $name;
            }
            if (isset($raw['footer_text'])) {
                $patch['footer_text'] = $raw['footer_text'];
            }
            if (isset($raw['footer_url'])) {
                $patch['footer_url'] = $raw['footer_url'];
            }
        }
        if ($step === 3) {
            $panels = Settings::get('welcome_panels', []);
            if (!is_array($panels)) {
                $panels = Settings::defaults()['welcome_panels'];
            }
            $panel = isset($panels[0]) && is_array($panels[0]) ? $panels[0] : [];
            $panel['enabled'] = !empty($raw['welcome_enabled']);
            $panel['show_title'] = !empty($raw['welcome_title']);
            $panel['title'] = isset($raw['welcome_title']) ? sanitize_text_field((string) $raw['welcome_title']) : '';
            $panel['type'] = 'html';
            $panel['html'] = isset($raw['welcome_html']) ? $raw['welcome_html'] : '';
            $panel['roles'] = ['administrator', 'editor'];
            $panels[0] = $panel;
            $patch['welcome_panels'] = $panels;
        }
        if ($patch !== []) {
            Settings::update($patch);
        }
        $base = admin_url('options-general.php?page=' . Settings::PAGE_SLUG . '&tab=general');
        if ($step >= 4) {
            Settings::update(['wizard_completed' => 1]);
            wp_safe_redirect(add_query_arg('mdcms_wizard_done', '1', $base));
            exit;
        }
        wp_safe_redirect(add_query_arg('mdcms_step', $step + 1, $base));
        exit;
    }
}
