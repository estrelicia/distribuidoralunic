<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Copia una sola vez wlcms_options y wbs_settings hacia mdcms_settings.
 * No borra las opciones de origen.
 */
final class Legacy_Import {

    public const FLAG = 'mdcms_legacy_imported';

    public const NOTES = 'mdcms_legacy_import_notes';

    public function register(): void {
        add_action('admin_init', [$this, 'maybe_import'], 20);
    }

    public function maybe_import(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (get_option(self::FLAG, false)) {
            return;
        }
        $wlcms = get_option('wlcms_options', false);
        $wbs = get_option('wbs_settings', false);
        if (!is_array($wlcms) && !is_array($wbs)) {
            return;
        }
        $ignored = [];
        $patch = [];
        if (is_array($wlcms)) {
            [$mapped, $skip] = self::map_wlcms($wlcms);
            $patch = array_merge($patch, $mapped);
            $ignored = array_merge($ignored, $skip);
        }
        if (is_array($wbs)) {
            [$mapped, $skip] = self::map_wbs($wbs);
            $patch = array_merge($patch, $mapped);
            $ignored = array_merge($ignored, $skip);
        }
        if ($patch !== []) {
            Settings::update($patch);
        }
        update_option(self::NOTES, $ignored, false);
        update_option(self::FLAG, time(), false);
    }

    /**
     * @param array<string, mixed> $source
     * @return array{0: array<string, mixed>, 1: string[]}
     */
    public static function map_wlcms(array $source): array {
        $direct = [
            'admin_bar_logo' => 'admin_bar_logo',
            'admin_bar_alt_text' => 'admin_bar_alt_text',
            'admin_bar_url' => 'admin_bar_url',
            'admin_bar_howdy_text' => 'admin_bar_howdy_text',
            'side_menu_image' => 'side_menu_image',
            'collapsed_side_menu_image' => 'collapsed_side_menu_image',
            'side_menu_link_url' => 'side_menu_link_url',
            'side_menu_alt_text' => 'side_menu_alt_text',
            'footer_image' => 'footer_image',
            'footer_url' => 'footer_url',
            'footer_html' => 'footer_html',
            'hide_wordpress_logo_and_links' => 'hide_wp_logo',
            'hide_wp_version' => 'hide_wp_version',
            'dashboard_title' => 'dashboard_title',
            'login_logo' => 'login_logo',
            'retina_login_logo' => 'retina_login_logo',
            'logo_bottom_margin' => 'login_logo_bottom_margin',
            'background_color' => 'login_background_color',
            'background_image' => 'login_background_image',
            'full_screen_background_image' => 'login_background_fullscreen',
            'background_positions' => 'login_background_position',
            'background_repeat' => 'login_background_repeat',
            'form_label_color' => 'login_form_label_color',
            'form_background_color' => 'login_form_background_color',
            'form_button_color' => 'login_form_button_color',
            'form_button_text_color' => 'login_form_button_text_color',
            'form_button_hover_color' => 'login_form_button_hover_color',
            'form_button_text_hover_color' => 'login_form_button_text_hover_color',
            'back_to_register_link_color' => 'login_link_color',
            'back_to_register_link_hover_color' => 'login_link_hover_color',
            'privacy_policy_link_color' => 'login_privacy_link_color',
            'privacy_policy_link_hover_color' => 'login_privacy_link_hover_color',
            'hide_back_to_link' => 'login_hide_back_to',
            'login_custom_css' => 'login_custom_css',
            'login_custom_js' => 'login_custom_js',
            'hide_help_box' => 'hide_help_box',
            'hide_screen_options' => 'hide_screen_options',
            'hide_nag_messages' => 'hide_nag_messages',
            'settings_custom_css_admin' => 'admin_custom_css',
            'settings_custom_css_url' => 'editor_stylesheet',
            'rss_logo' => 'rss_logo',
            'rss_title' => 'rss_title',
            'rss_feed_address' => 'rss_url',
            'rss_introduction' => 'rss_intro',
            'add_own_rss_panel' => 'rss_enabled',
        ];
        $patch = [];
        $used = [];
        foreach ($direct as $from => $to) {
            if (!array_key_exists($from, $source)) {
                continue;
            }
            $value = $source[$from];
            if (is_string($value) && trim($value) === '') {
                continue;
            }
            $patch[$to] = $value;
            $used[] = $from;
        }
        if (array_key_exists('admin_bar_logo_width', $source) && (int) $source['admin_bar_logo_width'] > 0) {
            $patch['admin_bar_logo_width'] = $source['admin_bar_logo_width'];
            $used[] = 'admin_bar_logo_width';
        }
        if (array_key_exists('logo_width', $source) && (int) $source['logo_width'] > 0) {
            $patch['login_logo_width'] = $source['logo_width'];
            $used[] = 'logo_width';
        }
        if (array_key_exists('logo_height', $source) && (int) $source['logo_height'] > 0) {
            $patch['login_logo_height'] = $source['logo_height'];
            $used[] = 'logo_height';
        }
        if (!empty($source['hide_register_lost_password'])) {
            $patch['login_hide_register'] = true;
            $patch['login_hide_lost_password'] = true;
            $used[] = 'hide_register_lost_password';
        }
        if (array_key_exists('rss_feed_number_of_item', $source)) {
            $patch['rss_count'] = $source['rss_feed_number_of_item'];
            $used[] = 'rss_feed_number_of_item';
        }
        if (array_key_exists('show_post_content', $source)) {
            $patch['rss_content'] = !empty($source['show_post_content']) ? 'content' : 'excerpt';
            $used[] = 'show_post_content';
        }
        if (isset($source['gutenberg_exit_icon']) && is_string($source['gutenberg_exit_icon']) && $source['gutenberg_exit_icon'] !== '') {
            $icon = strtolower($source['gutenberg_exit_icon']);
            $patch['gutenberg_exit_icon'] = in_array($icon, ['wordpress', 'exit', 'admin_bar', 'custom'], true) ? $icon : 'wordpress';
            $used[] = 'gutenberg_exit_icon';
        }
        if (!empty($source['gutenberg_exit_custom_icon']) && is_string($source['gutenberg_exit_custom_icon'])) {
            $patch['gutenberg_exit_custom_icon'] = $source['gutenberg_exit_custom_icon'];
            $used[] = 'gutenberg_exit_custom_icon';
        }
        if (isset($source['custom_page_title']) && is_string($source['custom_page_title']) && $source['custom_page_title'] !== '') {
            $patch['custom_page_title'] = $source['custom_page_title'];
            $used[] = 'custom_page_title';
        }
        if (isset($source['welcome_panel']) && is_array($source['welcome_panel'])) {
            $panels = self::map_welcome($source['welcome_panel']);
            if ($panels !== []) {
                $patch['welcome_panels'] = $panels;
            }
            $used[] = 'welcome_panel';
        }
        $ignored = [];
        foreach (array_keys($source) as $key) {
            if (in_array($key, $used, true) || $key === 'version') {
                continue;
            }
            $ignored[] = 'wlcms_options.' . $key;
        }

        return [$patch, $ignored];
    }

    /**
     * @param array<string, mixed> $source
     * @return array{0: array<string, mixed>, 1: string[]}
     */
    public static function map_wbs(array $source): array {
        $direct = [
            'trust_cloudflare' => 'trust_cloudflare',
            'login_limit' => 'login_limit',
            'login_max' => 'login_max',
            'login_window' => 'login_window',
            'generic_login_errors' => 'generic_login_errors',
            'disable_xmlrpc' => 'disable_xmlrpc',
            'block_author_enum' => 'block_author_enum',
            'block_rest_users' => 'block_rest_users',
            'rest_auth_required' => 'rest_auth_required',
            'block_readme' => 'block_readme',
            'hide_wp_version' => 'hide_wp_version',
            'disable_feeds' => 'disable_feeds',
            'disable_file_edit' => 'disable_file_edit',
            'disable_app_passwords' => 'disable_app_passwords',
            'security_headers' => 'security_headers',
            'send_hsts' => 'send_hsts',
        ];
        $patch = [];
        $used = [];
        foreach ($direct as $from => $to) {
            if (!array_key_exists($from, $source)) {
                continue;
            }
            $patch[$to] = $source[$from];
            $used[] = $from;
        }
        $ignored = [];
        foreach (array_keys($source) as $key) {
            if (in_array($key, $used, true)) {
                continue;
            }
            $ignored[] = 'wbs_settings.' . $key;
        }

        return [$patch, $ignored];
    }

    /**
     * @param array<int, mixed> $source
     * @return array<int, array<string, mixed>>
     */
    private static function map_welcome(array $source): array {
        $panels = Settings::defaults()['welcome_panels'];
        if (!is_array($panels)) {
            return [];
        }
        foreach ([0, 1] as $index) {
            $row = $source[$index] ?? null;
            if (!is_array($row)) {
                continue;
            }
            $type = strtolower((string) ($row['template_type'] ?? 'html'));
            if ($type === 'elementor') {
                $type = 'elementor';
            } elseif (str_contains($type, 'beaver')) {
                $type = 'beaver';
            } elseif ($type !== 'page') {
                $type = 'html';
            }
            $panels[$index] = [
                'enabled' => !empty($row['is_active']),
                'roles' => isset($row['visible_to']) && is_array($row['visible_to']) ? $row['visible_to'] : [],
                'show_title' => !empty($row['show_title']),
                'title' => (string) ($row['title'] ?? ''),
                'type' => $type,
                'html' => (string) ($row['description'] ?? ''),
                'page_id' => (int) ($row['page_id_page'] ?? 0),
                'elementor_id' => (int) ($row['page_id_elementor'] ?? 0),
                'beaver_id' => (int) ($row['page_id_beaver'] ?? 0),
            ];
        }

        return $panels;
    }

    public static function render_notice(): void {
        $notes = get_option(self::NOTES, []);
        if (!is_array($notes) || $notes === [] || !get_option(self::FLAG, false)) {
            return;
        }
        echo '<div class="notice notice-info"><p>';
        esc_html_e('Se copiaron ajustes de White Label CMS y Megadruid Seguridad. Las opciones viejas siguen en la base. Estas claves no tienen equivalente y no se guardaron:', 'megadruid-cms');
        echo '</p><p><code>' . esc_html(implode(', ', array_map('strval', $notes))) . '</code></p></div>';
    }
}
