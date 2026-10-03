<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Marca fija de Megadruid: barra y título del admin.
 */
final class Branding {

    public const LOGO_NODE = 'mdcms-logo';

    public const HOME_URL = 'https://megadruid.com';

    public const ALT = 'Megadruid agencia digital';

    public const TAB_TITLE = 'Megadruid AD';

    public const MAIL = 'clientes@megadruid.com';

    public const WHATSAPP_DISPLAY = '+54 911 7529-3223';

    public const WHATSAPP_URL = 'https://wa.me/5491175293223';

    public const PORTAL_URL = 'https://merlin.megadruid.com/clientes';

    public function register(): void {
        add_action('admin_bar_menu', [$this, 'replace_wp_logo'], 11);
        add_action('admin_bar_menu', [$this, 'add_logo'], 11);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_bar_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_bar_styles']);
        add_filter('admin_title', [$this, 'admin_title'], 10, 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_branding']);
    }

    public function replace_wp_logo(\WP_Admin_Bar $bar): void {
        $bar->remove_node('wp-logo');
    }

    public function add_logo(\WP_Admin_Bar $bar): void {
        $alt = self::ALT;
        $bar->add_node([
            'id' => self::LOGO_NODE,
            'title' => sprintf(
                '<img src="%s" alt="%s" />',
                esc_url(Brand_Pack::mark_url()),
                esc_attr($alt)
            ),
            'href' => self::HOME_URL,
            'meta' => [
                'class' => 'mdcms-admin-bar-logo',
                'title' => $alt,
                'target' => '_blank',
            ],
        ]);
    }

    public function enqueue_bar_styles(): void {
        if (!is_admin_bar_showing()) {
            return;
        }
        wp_register_style('megadruid-cms-admin-bar', false, [], MDCMS_VERSION);
        wp_enqueue_style('megadruid-cms-admin-bar');
        wp_add_inline_style(
            'megadruid-cms-admin-bar',
            sprintf(
                '#wpadminbar #wp-admin-bar-%1$s{width:32px;}'
                . '#wpadminbar #wp-admin-bar-%1$s>.ab-item{display:flex!important;align-items:center;justify-content:center;box-sizing:border-box;height:32px;width:32px;min-width:32px;padding:0!important;line-height:0!important;}'
                . '#wpadminbar #wp-admin-bar-%1$s img{display:block;width:20px;height:20px;margin:0;padding:0;border:0;vertical-align:middle;object-fit:contain;image-rendering:pixelated;image-rendering:crisp-edges;}',
                self::LOGO_NODE
            )
        );
    }

    public function admin_title(string $admin_title): string {
        $replaced = preg_replace(
            '/(\s*(?:&#8212;|—|–|-)\s*)WordPress\s*$/u',
            ' &#8212; ' . self::TAB_TITLE,
            $admin_title
        );

        return is_string($replaced) ? $replaced : $admin_title;
    }

    public function enqueue_admin_branding(): void {
        wp_enqueue_style(
            'mdcms-branding-admin',
            MDCMS_URL . 'assets/css/branding-admin.css',
            ['dashicons'],
            file_exists(MDCMS_PATH . 'assets/css/branding-admin.css')
                ? (string) filemtime(MDCMS_PATH . 'assets/css/branding-admin.css')
                : MDCMS_VERSION
        );
    }
}
