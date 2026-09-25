<?php

namespace Distribuidora_Lunic\Modules\Security;

defined('ABSPATH') || exit;

class Hardening {

    public function register(): void {
        add_action('send_headers', [$this, 'security_headers']);
        add_action('init', [$this, 'init_cleanup'], 1);
        add_action('wp_footer', [$this, 'deregister_embed']);
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('rest_authentication_errors', [$this, 'rest_require_login']);
        add_filter('rest_endpoints', [$this, 'trim_wp_core_endpoints']);
        add_filter('auto_plugin_update_send_email', '__return_false');
        add_filter('auto_theme_update_send_email', '__return_false');
        add_filter('pings_open', '__return_false');
        add_filter('woocommerce_sale_flash', [$this, 'sale_flash_oferta'], 10, 1);
        add_filter('woocommerce_defer_transactional_emails', '__return_true');
        add_filter('the_generator', [$this, 'empty_generator']);
        $this->disable_feeds();
    }

    public function security_headers(): void {
        if (headers_sent()) {
            return;
        }
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1;mode=block');
        header('Referrer-Policy: no-referrer-when-downgrade');
        header('Content-Security-Policy: upgrade-insecure-requests;');
        header('Strict-Transport-Security: max-age=31536000;');
        header('Permissions-Policy: geolocation=(); midi=();notifications=();push=();sync-xhr=();accelerometer=(); gyroscope=(); magnetometer=(); payment=(); camera=(); microphone=();usb=(); xr=();speaker=(self);vibrate=();fullscreen=(self);');
        header_remove('x-powered-by');
    }

    public function init_cleanup(): void {
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'start_post_rel_link');
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('template_redirect', 'rest_output_link_header', 11, 0);
        remove_action('template_redirect', 'wp_shortlink_header', 11, 0);
    }

    public function deregister_embed(): void {
        wp_deregister_script('wp-embed');
    }

    public function rest_require_login($result) {
        if (is_user_logged_in() || $this->is_lunic_public_route()) {
            return $result;
        }
        return new \WP_Error(
            'rest_not_logged_in',
            __('You are not currently logged in.', 'distribuidora-lunic'),
            ['status' => 401]
        );
    }

    public function trim_wp_core_endpoints($endpoints) {
        if (is_user_logged_in()) {
            return $endpoints;
        }
        foreach ($endpoints as $route => $endpoint) {
            if (stripos($route, '/wp/') === 0) {
                unset($endpoints[$route]);
            }
        }
        return $endpoints;
    }

    private function is_lunic_public_route(): bool {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
        return strpos($uri, '/wp-json/lunic/v1/') !== false;
    }

    public function sale_flash_oferta($text) {
        return str_replace(__('Sale!', 'woocommerce'), 'Oferta', $text);
    }

    public function empty_generator() {
        return '';
    }

    private function disable_feeds(): void {
        $feeds = [
            'do_feed',
            'do_feed_rdf',
            'do_feed_rss',
            'do_feed_rss2',
            'do_feed_atom',
            'do_feed_rss2_comments',
            'do_feed_atom_comments',
        ];
        foreach ($feeds as $feed) {
            add_action($feed, static function () {
                wp_die(esc_html__('Feed has been disabled.', 'distribuidora-lunic'));
            }, 1);
        }
    }
}
