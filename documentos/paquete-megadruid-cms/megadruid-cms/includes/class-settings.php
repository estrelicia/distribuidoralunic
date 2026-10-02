<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

final class Settings {

    public const OPTION = 'mdcms_settings';

    public const PAGE_SLUG = 'megadruid-cms';

    public const SAVE_ACTION = 'mdcms_save';

    /**
     * @return array<string, array{type: string, default: mixed, min?: int, max?: int}>
     */
    public static function schema(): array {
        return [
            'test_text' => [
                'type' => 'text',
                'default' => '',
            ],
            'restricted_roles' => [
                'type' => 'roles',
                'default' => ['editor'],
            ],
            'admin_bar_logo' => [
                'type' => 'url',
                'default' => '',
            ],
            'admin_bar_logo_width' => [
                'type' => 'int',
                'default' => 20,
                'min' => 8,
                'max' => 240,
            ],
            'admin_bar_alt_text' => [
                'type' => 'text',
                'default' => '',
            ],
            'admin_bar_url' => [
                'type' => 'url',
                'default' => '',
            ],
            'admin_bar_howdy_text' => [
                'type' => 'text',
                'default' => '',
            ],
            'hide_wp_logo' => [
                'type' => 'bool',
                'default' => false,
            ],
            'side_menu_image' => [
                'type' => 'url',
                'default' => '',
            ],
            'collapsed_side_menu_image' => [
                'type' => 'url',
                'default' => '',
            ],
            'side_menu_link_url' => [
                'type' => 'url',
                'default' => '',
            ],
            'side_menu_alt_text' => [
                'type' => 'text',
                'default' => '',
            ],
            'footer_image' => [
                'type' => 'url',
                'default' => '',
            ],
            'footer_url' => [
                'type' => 'url',
                'default' => '',
            ],
            'footer_text' => [
                'type' => 'text',
                'default' => '',
            ],
            'footer_html' => [
                'type' => 'html',
                'default' => '',
            ],
            'hide_wp_version' => [
                'type' => 'bool',
                'default' => false,
            ],
            'custom_page_title' => [
                'type' => 'text',
                'default' => '',
            ],
            'gutenberg_exit_icon' => [
                'type' => 'enum',
                'default' => 'wordpress',
                'allowed' => ['wordpress', 'exit', 'admin_bar', 'custom'],
            ],
            'gutenberg_exit_custom_icon' => [
                'type' => 'url',
                'default' => '',
            ],
            'login_logo' => [
                'type' => 'url',
                'default' => '',
            ],
            'retina_login_logo' => [
                'type' => 'url',
                'default' => '',
            ],
            'login_logo_width' => [
                'type' => 'int',
                'default' => 0,
                'min' => 0,
                'max' => 320,
            ],
            'login_logo_height' => [
                'type' => 'int',
                'default' => 0,
                'min' => 0,
                'max' => 400,
            ],
            'login_logo_bottom_margin' => [
                'type' => 'int',
                'default' => 0,
                'min' => 0,
                'max' => 200,
            ],
            'login_background_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_background_image' => [
                'type' => 'url',
                'default' => '',
            ],
            'login_background_fullscreen' => [
                'type' => 'bool',
                'default' => false,
            ],
            'login_background_position' => [
                'type' => 'enum',
                'default' => 'center center',
                'allowed' => [
                    'center center',
                    'center top',
                    'center bottom',
                    'left top',
                    'left center',
                    'left bottom',
                    'right top',
                    'right center',
                    'right bottom',
                ],
            ],
            'login_background_repeat' => [
                'type' => 'enum',
                'default' => 'no-repeat',
                'allowed' => ['repeat', 'repeat-y', 'no-repeat'],
            ],
            'login_form_label_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_form_background_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_form_button_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_form_button_text_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_form_button_hover_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_form_button_text_hover_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_link_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_link_hover_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_privacy_link_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_privacy_link_hover_color' => [
                'type' => 'color',
                'default' => '',
            ],
            'login_hide_register' => [
                'type' => 'bool',
                'default' => false,
            ],
            'login_hide_lost_password' => [
                'type' => 'bool',
                'default' => false,
            ],
            'login_hide_back_to' => [
                'type' => 'bool',
                'default' => false,
            ],
            'login_custom_css' => [
                'type' => 'css',
                'default' => '',
            ],
            'login_custom_js' => [
                'type' => 'js',
                'default' => '',
            ],
            'dashboard_title' => [
                'type' => 'text',
                'default' => '',
            ],
            'dashboard_panel_roles' => [
                'type' => 'panel_roles',
                'default' => [],
            ],
            'welcome_panels' => [
                'type' => 'welcome',
                'default' => [
                    [
                        'enabled' => false,
                        'roles' => [],
                        'show_title' => false,
                        'title' => '',
                        'type' => 'html',
                        'html' => '',
                        'page_id' => 0,
                        'elementor_id' => 0,
                        'beaver_id' => 0,
                    ],
                    [
                        'enabled' => false,
                        'roles' => [],
                        'show_title' => false,
                        'title' => '',
                        'type' => 'html',
                        'html' => '',
                        'page_id' => 0,
                        'elementor_id' => 0,
                        'beaver_id' => 0,
                    ],
                ],
            ],
            'rss_enabled' => [
                'type' => 'bool',
                'default' => false,
            ],
            'rss_roles' => [
                'type' => 'roles',
                'default' => [],
            ],
            'rss_title' => [
                'type' => 'text',
                'default' => '',
            ],
            'rss_logo' => [
                'type' => 'url',
                'default' => '',
            ],
            'rss_url' => [
                'type' => 'url',
                'default' => '',
            ],
            'rss_count' => [
                'type' => 'int',
                'default' => 5,
                'min' => 1,
                'max' => 10,
            ],
            'rss_content' => [
                'type' => 'enum',
                'default' => 'excerpt',
                'allowed' => ['excerpt', 'content'],
            ],
            'rss_intro' => [
                'type' => 'html',
                'default' => '',
            ],
            'hidden_side_menus' => [
                'type' => 'menu_map',
                'source' => 'side',
                'default' => [],
            ],
            'hidden_admin_bar' => [
                'type' => 'menu_map',
                'source' => 'bar',
                'default' => [],
            ],
            'hide_frontend_admin_bar_roles' => [
                'type' => 'roles',
                'default' => [],
            ],
            'hide_help_box' => [
                'type' => 'bool',
                'default' => false,
            ],
            'hide_screen_options' => [
                'type' => 'bool',
                'default' => false,
            ],
            'hide_nag_messages' => [
                'type' => 'bool',
                'default' => false,
            ],
            'admin_custom_css' => [
                'type' => 'css',
                'default' => '',
            ],
            'editor_stylesheet' => [
                'type' => 'stylesheet',
                'default' => '',
            ],
            'post_metabox_roles' => [
                'type' => 'metabox_map',
                'source' => 'post',
                'default' => [],
            ],
            'page_metabox_roles' => [
                'type' => 'metabox_map',
                'source' => 'page',
                'default' => [],
            ],
            'wizard_completed' => [
                'type' => 'bool',
                'default' => false,
            ],
            'trust_cloudflare' => [
                'type' => 'bool',
                'default' => true,
            ],
            'login_limit' => [
                'type' => 'bool',
                'default' => true,
            ],
            'login_max' => [
                'type' => 'int',
                'default' => 5,
                'min' => 3,
                'max' => 20,
            ],
            'login_window' => [
                'type' => 'int',
                'default' => 15,
                'min' => 5,
                'max' => 120,
            ],
            'generic_login_errors' => [
                'type' => 'bool',
                'default' => true,
            ],
            'disable_xmlrpc' => [
                'type' => 'bool',
                'default' => true,
            ],
            'block_author_enum' => [
                'type' => 'bool',
                'default' => true,
            ],
            'block_rest_users' => [
                'type' => 'bool',
                'default' => true,
            ],
            'rest_auth_required' => [
                'type' => 'bool',
                'default' => false,
            ],
            'block_readme' => [
                'type' => 'bool',
                'default' => true,
            ],
            'disable_feeds' => [
                'type' => 'bool',
                'default' => true,
            ],
            'disable_file_edit' => [
                'type' => 'bool',
                'default' => true,
            ],
            'disable_app_passwords' => [
                'type' => 'bool',
                'default' => true,
            ],
            'security_headers' => [
                'type' => 'bool',
                'default' => true,
            ],
            'send_hsts' => [
                'type' => 'bool',
                'default' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array {
        $defaults = [];
        foreach (self::schema() as $key => $field) {
            $defaults[$key] = $field['default'];
        }

        return $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array {
        $stored = get_option(self::OPTION, []);
        if (!is_array($stored)) {
            $stored = [];
        }

        return array_merge(self::defaults(), $stored);
    }

    public static function get(string $key, mixed $default = null): mixed {
        $all = self::all();
        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return $default;
    }

    /**
     * Fusiona claves conocidas, sanitiza y guarda.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function update(array $input): array {
        $clean = self::all();
        foreach (self::schema() as $key => $field) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            if ($key === 'login_custom_js' && !current_user_can('manage_options')) {
                continue;
            }
            $clean[$key] = self::sanitize_value($input[$key], $field);
        }
        update_option(self::OPTION, $clean);

        return $clean;
    }

    public static function ensure_option_exists(): void {
        if (false === get_option(self::OPTION, false)) {
            add_option(self::OPTION, self::defaults());
        }
    }

    /**
     * @param array{type: string, default: mixed, min?: int, max?: int} $field
     */
    public static function sanitize_value(mixed $value, array $field): mixed {
        return match ($field['type']) {
            'url' => self::sanitize_url($value),
            'color' => self::sanitize_color($value),
            'int' => self::sanitize_int($value, $field),
            'bool' => self::sanitize_bool($value),
            'roles' => self::sanitize_roles($value),
            'html' => self::sanitize_html($value),
            'css' => self::sanitize_css($value),
            'js' => self::sanitize_js($value),
            'enum' => self::sanitize_enum($value, $field),
            'panel_roles' => self::sanitize_panel_roles($value),
            'welcome' => self::sanitize_welcome($value),
            'menu_map' => self::sanitize_menu_map($value, $field),
            'metabox_map' => self::sanitize_metabox_map($value, $field),
            'stylesheet' => self::sanitize_stylesheet($value),
            default => self::sanitize_text($value),
        };
    }

    /**
     * @param array{source?: string} $field
     * @return array<string, string[]>
     */
    private static function sanitize_metabox_map(mixed $value, array $field): array {
        if (!is_array($value)) {
            return [];
        }
        if (!class_exists(Metaboxes::class)) {
            require_once MDCMS_PATH . 'includes/class-metaboxes.php';
        }
        $keys = ($field['source'] ?? '') === 'page'
            ? array_keys(Metaboxes::PAGE_BOXES)
            : array_keys(Metaboxes::POST_BOXES);
        $clean = [];
        foreach ($keys as $box) {
            $clean[$box] = self::sanitize_roles($value[$box] ?? []);
        }

        return $clean;
    }

    private static function sanitize_stylesheet(mixed $value): string {
        $path = trim(str_replace('\\', '/', (string) $value));
        if ($path === '' || str_contains($path, '..')) {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return self::sanitize_url($path);
        }
        $path = ltrim($path, '/');
        if ($path === '' || !preg_match('#^[a-zA-Z0-9._/-]+$#', $path)) {
            return '';
        }

        return $path;
    }

    private static function sanitize_css(mixed $value): string {
        $css = wp_strip_all_tags((string) $value);
        $css = str_ireplace(['<?php', '<?=', '<?', '?>', '<style', '</style', '<script', '</script'], '', $css);
        $css = preg_replace('/expression\s*\(/i', '', $css) ?? $css;
        $css = preg_replace('/javascript\s*:/i', '', $css) ?? $css;
        $css = preg_replace('/@import\b/i', '', $css) ?? $css;

        return trim($css);
    }

    private static function sanitize_js(mixed $value): string {
        $js = (string) $value;
        $js = str_ireplace(['<?php', '<?=', '<?', '?>', '</script'], '', $js);

        return trim($js);
    }

    /**
     * @return array<string, string[]>
     */
    private static function sanitize_panel_roles(mixed $value): array {
        if (!is_array($value)) {
            return [];
        }
        $clean = [];
        foreach (array_merge(array_keys(Dashboard::PANELS), ['all']) as $panel) {
            $clean[$panel] = self::sanitize_roles($value[$panel] ?? []);
        }

        return $clean;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function sanitize_welcome(mixed $value): array {
        $blank = [
            'enabled' => false,
            'roles' => [],
            'show_title' => false,
            'title' => '',
            'type' => 'html',
            'html' => '',
            'page_id' => 0,
            'elementor_id' => 0,
            'beaver_id' => 0,
        ];
        $allowed = ['html', 'page'];
        if (class_exists('\Elementor\Plugin')) {
            $allowed[] = 'elementor';
        }
        if (class_exists('FLBuilder')) {
            $allowed[] = 'beaver';
        }
        $panels = [];
        for ($index = 0; $index < 2; $index++) {
            $panel = (is_array($value) && isset($value[$index]) && is_array($value[$index])) ? $value[$index] : [];
            $type = sanitize_key((string) ($panel['type'] ?? 'html'));
            if (!in_array($type, $allowed, true)) {
                $type = 'html';
            }
            $panels[] = [
                'enabled' => self::sanitize_bool($panel['enabled'] ?? false),
                'roles' => self::sanitize_roles($panel['roles'] ?? []),
                'show_title' => self::sanitize_bool($panel['show_title'] ?? false),
                'title' => self::sanitize_text($panel['title'] ?? ''),
                'type' => $type,
                'html' => self::sanitize_html($panel['html'] ?? ''),
                'page_id' => max(0, (int) ($panel['page_id'] ?? 0)),
                'elementor_id' => max(0, (int) ($panel['elementor_id'] ?? 0)),
                'beaver_id' => max(0, (int) ($panel['beaver_id'] ?? 0)),
            ];
        }

        return $panels === [] ? [$blank, $blank] : $panels;
    }

    /**
     * @param array{source?: string} $field
     * @return array<string, string[]>
     */
    private static function sanitize_menu_map(mixed $value, array $field): array {
        if (!is_array($value)) {
            return [];
        }
        if (!class_exists(Menus::class)) {
            require_once MDCMS_PATH . 'includes/class-menus.php';
        }
        $source = ($field['source'] ?? '') === 'bar' ? 'bar' : 'side';
        $known = $source === 'bar' ? Menus::known_bar_ids() : Menus::known_side_slugs();
        $filter = $source === 'bar' ? 'mdcms_known_admin_bar' : 'mdcms_known_side_slugs';
        $filtered = apply_filters($filter, $known);
        if (is_array($filtered)) {
            $known = $filtered;
        }
        $roles = array_keys(wp_roles()->roles);
        $clean = [];
        foreach ($value as $role => $slugs) {
            $role = sanitize_key((string) $role);
            if (!in_array($role, $roles, true) || !is_array($slugs)) {
                continue;
            }
            $items = [];
            foreach ($slugs as $slug) {
                $slug = Menus::clean_slug((string) $slug);
                if ($slug === '' || str_contains($slug, 'megadruid-cms') || !in_array($slug, $known, true)) {
                    continue;
                }
                $items[] = $slug;
            }
            $clean[$role] = array_values(array_unique($items));
        }

        return $clean;
    }

    /**
     * @param array{allowed?: string[], default?: string} $field
     */
    private static function sanitize_enum(mixed $value, array $field): string {
        $allowed = $field['allowed'] ?? [];
        $choice = trim((string) $value);
        if ($choice !== '' && in_array($choice, $allowed, true)) {
            return $choice;
        }

        return (string) ($field['default'] ?? '');
    }

    private static function sanitize_text(mixed $value): string {
        return sanitize_text_field((string) $value);
    }

    private static function sanitize_url(mixed $value): string {
        $url = esc_url_raw(trim((string) $value));

        return is_string($url) ? $url : '';
    }

    private static function sanitize_color(mixed $value): string {
        $color = sanitize_hex_color(trim((string) $value));

        return is_string($color) ? $color : '';
    }

    /**
     * @param array{type: string, default: mixed, min?: int, max?: int} $field
     */
    private static function sanitize_int(mixed $value, array $field): int {
        $number = (int) $value;
        if (isset($field['min'])) {
            $number = max((int) $field['min'], $number);
        }
        if (isset($field['max'])) {
            $number = min((int) $field['max'], $number);
        }

        return $number;
    }

    private static function sanitize_bool(mixed $value): bool {
        return !empty($value) && $value !== '0' && $value !== 0;
    }

    /**
     * @return string[]
     */
    private static function sanitize_roles(mixed $value): array {
        if (!is_array($value)) {
            return [];
        }
        $known = array_keys(wp_roles()->roles);
        $clean = [];
        foreach ($value as $role) {
            $role = sanitize_key((string) $role);
            if ($role !== '' && in_array($role, $known, true)) {
                $clean[] = $role;
            }
        }

        return array_values(array_unique($clean));
    }

    private static function sanitize_html(mixed $value): string {
        return wp_kses_post((string) $value);
    }
}
