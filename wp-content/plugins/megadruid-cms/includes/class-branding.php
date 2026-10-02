<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Marca del escritorio: barra, menú lateral, pie y salida del editor.
 */
final class Branding {

    public const LOGO_NODE = 'mdcms-logo';

    public function register(): void {
        add_action('admin_bar_menu', [$this, 'replace_wp_logo'], 11);
        add_action('admin_bar_menu', [$this, 'add_logo'], 11);
        add_action('admin_bar_menu', [$this, 'replace_howdy'], 10000);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_bar_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_bar_styles']);
        add_filter('admin_title', [$this, 'admin_title'], 10, 1);
        add_filter('admin_footer_text', [$this, 'admin_footer_text'], 2000);
        add_filter('update_footer', [$this, 'update_footer'], 11);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_branding']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
    }

    public function replace_wp_logo(\WP_Admin_Bar $bar): void {
        if (!Settings::get('hide_wp_logo')) {
            return;
        }
        $bar->remove_node('wp-logo');
    }

    public function add_logo(\WP_Admin_Bar $bar): void {
        $src = (string) Settings::get('admin_bar_logo', '');
        if ($src === '') {
            return;
        }
        $alt = (string) Settings::get('admin_bar_alt_text', '');
        $url = (string) Settings::get('admin_bar_url', '');
        $bar->add_node([
            'id' => self::LOGO_NODE,
            'title' => sprintf(
                '<img src="%s" alt="%s" />',
                esc_url($src),
                esc_attr($alt)
            ),
            'href' => $url !== '' ? $url : false,
            'meta' => [
                'class' => 'mdcms-admin-bar-logo',
                'title' => $alt,
            ],
        ]);
    }

    public function replace_howdy(\WP_Admin_Bar $bar): void {
        $custom = (string) Settings::get('admin_bar_howdy_text', '');
        if ($custom === '') {
            return;
        }
        $node = $bar->get_node('my-account');
        if (!$node || !isset($node->title) || !is_string($node->title)) {
            return;
        }
        $needle = str_replace(', %s', ',', __('Howdy, %s'));
        $bar->add_node([
            'id' => 'my-account',
            'title' => str_replace($needle, $custom, $node->title),
        ]);
    }

    public function enqueue_bar_styles(): void {
        if (!is_admin_bar_showing()) {
            return;
        }
        $src = (string) Settings::get('admin_bar_logo', '');
        if ($src === '') {
            return;
        }
        $width = (int) Settings::get('admin_bar_logo_width', 20);
        wp_register_style('megadruid-cms-admin-bar', false, [], MDCMS_VERSION);
        wp_enqueue_style('megadruid-cms-admin-bar');
        wp_add_inline_style(
            'megadruid-cms-admin-bar',
            sprintf(
                '#wp-admin-bar-%1$s .ab-item{display:flex;align-items:center;height:32px;}'
                . '#wp-admin-bar-%1$s img{display:block;width:%2$dpx;max-height:20px;height:auto;vertical-align:middle;}',
                self::LOGO_NODE,
                $width
            )
        );
    }

    public function admin_title(string $admin_title): string {
        $custom = (string) Settings::get('custom_page_title', '');
        if ($custom === '') {
            return $admin_title;
        }

        return str_replace(
            '&#8212; WordPress',
            '&#8212; ' . esc_attr($custom),
            $admin_title
        );
    }

    /**
     * @param string $text Texto original del pie izquierdo.
     */
    public function admin_footer_text(string $text): string {
        $html = (string) Settings::get('footer_html', '');
        if ($html !== '') {
            return $html;
        }

        $image = (string) Settings::get('footer_image', '');
        $footer_text = (string) Settings::get('footer_text', '');
        $url = (string) Settings::get('footer_url', '');
        $content = '';

        if ($image !== '') {
            $content .= sprintf(
                '<img src="%s" alt="" style="vertical-align:middle;max-height:50px;margin-right:5px;" /> ',
                esc_url($image)
            );
        }
        if ($footer_text !== '') {
            $content .= esc_html($footer_text);
        }
        if ($content === '') {
            return $text;
        }
        if ($url === '') {
            return $content;
        }

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" style="text-decoration:none;">%s</a>',
            esc_url($url),
            $content
        );
    }

    /**
     * @param string $text Versión de WordPress en el pie derecho.
     */
    public function update_footer(string $text): string {
        if (!Settings::get('hide_wp_version')) {
            return $text;
        }

        return '';
    }

    public function enqueue_admin_branding(): void {
        $sidebar_image = (string) Settings::get('side_menu_image', '');
        if ($sidebar_image !== '') {
            wp_enqueue_style(
                'mdcms-branding-admin',
                MDCMS_URL . 'assets/css/branding-admin.css',
                [],
                MDCMS_VERSION
            );
            $markup = $this->sidebar_logo_markup();
            wp_enqueue_script('jquery');
            wp_add_inline_script(
                'jquery',
                'jQuery(function($){$("#adminmenuwrap").prepend(' . wp_json_encode($markup) . ');});'
            );
        }
    }

    public function enqueue_block_editor_assets(): void {
        $mode = (string) Settings::get('gutenberg_exit_icon', 'wordpress');
        if ($mode === 'wordpress') {
            return;
        }
        $markup = $this->gutenberg_exit_markup($mode);
        if ($markup === '') {
            return;
        }
        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            'mdcms-branding-admin',
            MDCMS_URL . 'assets/css/branding-admin.css',
            [],
            MDCMS_VERSION
        );
        wp_enqueue_script(
            'mdcms-block-editor',
            MDCMS_URL . 'assets/js/block-editor.js',
            [],
            MDCMS_VERSION,
            true
        );
        wp_localize_script(
            'mdcms-block-editor',
            'mdcmsBlockEditor',
            [
                'mode' => $mode,
                'markup' => $markup,
            ]
        );
    }

    public function gutenberg_exit_markup(string $mode): string {
        return match ($mode) {
            'exit' => '<span class="mdcms-exit-icon"><span class="dashicons dashicons-exit" aria-hidden="true"></span></span>',
            'admin_bar' => $this->gutenberg_image_markup((string) Settings::get('admin_bar_logo', '')),
            'custom' => $this->gutenberg_image_markup((string) Settings::get('gutenberg_exit_custom_icon', '')),
            default => '',
        };
    }

    private function gutenberg_image_markup(string $src): string {
        if ($src === '') {
            return '';
        }

        return sprintf(
            '<span class="mdcms-exit-icon"><img src="%s" alt="" /></span>',
            esc_url($src)
        );
    }

    private function sidebar_logo_markup(): string {
        $sidebar_image = esc_url((string) Settings::get('side_menu_image', ''));
        $collapsed = (string) Settings::get('collapsed_side_menu_image', '');
        $collapsed_image = esc_url($collapsed !== '' ? $collapsed : (string) Settings::get('side_menu_image', ''));
        $alt = esc_attr((string) Settings::get('side_menu_alt_text', ''));
        $url = (string) Settings::get('side_menu_link_url', '');
        $imgs = sprintf(
            '<img src="%1$s" alt="%3$s" class="mdcms-side-logo__large" />'
            . '<img src="%2$s" alt="%3$s" class="mdcms-side-logo__small" />',
            $sidebar_image,
            $collapsed_image,
            $alt
        );
        $inner = $imgs;
        if ($url !== '') {
            $target = $this->external_link_target($url);
            $inner = sprintf(
                '<a href="%s" title="%s" target="%s"%s>%s</a>',
                esc_url($url),
                $alt,
                esc_attr($target),
                $target === '_blank' ? ' rel="noopener noreferrer"' : '',
                $imgs
            );
        }

        return '<span class="mdcms-side-logo">' . $inner . '</span>';
    }

    private function external_link_target(string $url): string {
        $parts = wp_parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return '_self';
        }
        $host = isset($_SERVER['HTTP_HOST']) ? (string) wp_unslash($_SERVER['HTTP_HOST']) : '';

        return str_contains($host, (string) $parts['host']) ? '_self' : '_blank';
    }
}
