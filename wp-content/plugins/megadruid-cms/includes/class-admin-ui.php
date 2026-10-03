<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Ayuda, avisos de actualización, CSS extra del admin y hoja del editor.
 */
final class Admin_Ui {

    public function register(): void {
        add_action('admin_head', [$this, 'hide_help']);
        add_filter('screen_options_show_screen', [$this, 'show_screen_options']);
        add_action('admin_init', [$this, 'remove_nags'], 9999);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_css']);
        add_filter('mce_css', [$this, 'classic_editor_css']);
        add_action('enqueue_block_editor_assets', [$this, 'block_editor_css']);
    }

    public function hide_help(): void {
        if (!Settings::get('hide_help_box')) {
            return;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && $screen->id === 'settings_page_' . Settings::PAGE_SLUG) {
            return;
        }
        if ($screen) {
            $screen->remove_help_tabs();
        }
        echo '<style id="mdcms-hide-help">#contextual-help-link-wrap,#contextual-help-link,#screen-meta-links #contextual-help-link-wrap{display:none!important;}</style>';
    }

    public function show_screen_options(bool $show): bool {
        if (Settings::get('hide_screen_options')) {
            return false;
        }

        return $show;
    }

    public function remove_nags(): void {
        if (!Settings::get('hide_nag_messages')) {
            return;
        }
        remove_action('admin_notices', 'update_nag', 3);
        remove_action('admin_notices', 'maintenance_nag', 10);
        remove_action('network_admin_notices', 'update_nag', 3);
        remove_action('network_admin_notices', 'maintenance_nag', 10);
    }

    public function enqueue_admin_css(): void {
        $css = (string) Settings::get('admin_custom_css', '');
        if ($css === '') {
            return;
        }
        wp_register_style('mdcms-admin-extra', false, [], MDCMS_VERSION);
        wp_enqueue_style('mdcms-admin-extra');
        wp_add_inline_style('mdcms-admin-extra', $css);
    }

    public function classic_editor_css(string $mce_css): string {
        $url = $this->editor_stylesheet_url();
        if ($url === '') {
            return $mce_css;
        }
        $parts = array_filter(array_map('trim', explode(',', $mce_css)));
        $parts[] = $url;

        return implode(',', $parts);
    }

    public function block_editor_css(): void {
        $url = $this->editor_stylesheet_url();
        if ($url === '') {
            return;
        }
        wp_enqueue_style('mdcms-editor-stylesheet', $url, [], MDCMS_VERSION);
    }

    public function editor_stylesheet_url(): string {
        $value = (string) Settings::get('editor_stylesheet', '');
        if ($value === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $value)) {
            return esc_url_raw($value);
        }
        $relative = ltrim(str_replace('\\', '/', $value), '/');
        $file = get_stylesheet_directory() . '/' . $relative;
        $theme = wp_normalize_path(get_stylesheet_directory());
        $real = realpath($file);
        if ($real === false || !str_starts_with(wp_normalize_path($real), $theme)) {
            return '';
        }

        return esc_url_raw(get_stylesheet_directory_uri() . '/' . $relative);
    }
}
