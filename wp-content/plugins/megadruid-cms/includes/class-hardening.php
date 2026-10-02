<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Endurecimiento (etapa 7.3), controlado desde mdcms_settings.
 */
final class Hardening {

    public static function delegated_to_legacy(): bool {
        return defined('WBS_VERSION');
    }

    public function register(): void {
        if (self::delegated_to_legacy()) {
            return;
        }
        add_action('init', [$this, 'clean_head'], 5);
        if (Settings::get('disable_xmlrpc', true)) {
            add_filter('xmlrpc_enabled', '__return_false', 99);
            add_filter('xmlrpc_methods', '__return_empty_array', 99);
            add_filter('wp_headers', [$this, 'remove_pingback_header']);
            add_action('init', [$this, 'kill_xmlrpc_request'], 0);
        }
        if (Settings::get('block_author_enum', true)) {
            add_action('template_redirect', [$this, 'block_author_enum']);
            add_filter('wp_sitemaps_add_provider', [$this, 'remove_users_sitemap'], 10, 2);
            add_filter('oembed_response_data', [$this, 'strip_oembed_author'], 10, 1);
        }
        if (Settings::get('block_rest_users', true)) {
            add_filter('rest_endpoints', [$this, 'filter_rest_users']);
        }
        if (Settings::get('rest_auth_required', false)) {
            add_filter('rest_authentication_errors', [$this, 'rest_auth_required']);
        }
        if (Settings::get('block_readme', true)) {
            add_action('init', [$this, 'block_fingerprint_files'], 0);
        }
        if (Settings::get('hide_wp_version', false)) {
            add_filter('the_generator', '__return_empty_string');
            add_filter('style_loader_src', [$this, 'strip_ver'], 9999);
            add_filter('script_loader_src', [$this, 'strip_ver'], 9999);
            remove_action('wp_head', 'wp_generator');
        }
        if (Settings::get('disable_feeds', true)) {
            foreach (['do_feed', 'do_feed_rdf', 'do_feed_rss', 'do_feed_rss2', 'do_feed_atom', 'do_feed_rss2_comments', 'do_feed_atom_comments'] as $hook) {
                add_action($hook, [$this, 'disable_feeds'], 1);
            }
        }
        if (Settings::get('disable_file_edit', true) && !defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
        if (Settings::get('disable_app_passwords', true)) {
            add_filter('wp_is_application_passwords_available', '__return_false');
        }
        if (Settings::get('security_headers', true)) {
            add_action('send_headers', [$this, 'headers']);
            add_action('login_init', [$this, 'login_robots']);
        }
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }
    }

    public function clean_head(): void {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
        add_filter('pings_open', '__return_false', 20);
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    public function remove_pingback_header(array $headers): array {
        unset($headers['X-Pingback']);

        return $headers;
    }

    public function kill_xmlrpc_request(): void {
        $blocked = (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST);
        if (!$blocked) {
            $path = strtolower('/' . ltrim((string) wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/'));
            $blocked = str_ends_with($path, '/xmlrpc.php');
        }
        if (!$blocked) {
            return;
        }
        status_header(403);
        nocache_headers();
        exit;
    }

    public function block_author_enum(): void {
        if (is_admin()) {
            return;
        }
        if (isset($_GET['author'])) {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
        if (is_author()) {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
    }

    /**
     * @param mixed $provider
     * @return mixed
     */
    public function remove_users_sitemap(mixed $provider, mixed $name): mixed {
        if ($name === 'users') {
            return false;
        }

        return $provider;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function strip_oembed_author(array $data): array {
        unset($data['author_name'], $data['author_url']);

        return $data;
    }

    /**
     * @param array<string, mixed> $endpoints
     * @return array<string, mixed>
     */
    public function filter_rest_users(array $endpoints): array {
        if (current_user_can('list_users')) {
            return $endpoints;
        }
        foreach (array_keys($endpoints) as $route) {
            if (preg_match('#^/wp/v2/users#', (string) $route)) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }

    /**
     * @param mixed $result
     * @return mixed
     */
    public function rest_auth_required(mixed $result): mixed {
        if ($result === true || is_wp_error($result)) {
            return $result;
        }
        if (is_user_logged_in()) {
            return $result;
        }

        return new \WP_Error(
            'mdcms_rest_forbidden',
            __('La API REST requiere autenticación.', 'megadruid-cms'),
            ['status' => 401]
        );
    }

    public function block_fingerprint_files(): void {
        if (is_admin()) {
            return;
        }
        $path = (string) wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        if ($path === '') {
            return;
        }
        $path = '/' . ltrim($path, '/');
        if (preg_match('#^(.*/)?(readme\.html|license\.txt)/?$#i', $path)) {
            status_header(404);
            nocache_headers();
            exit;
        }
    }

    public function disable_feeds(): void {
        wp_die(
            esc_html__('Los feeds están desactivados.', 'megadruid-cms'),
            '',
            ['response' => 404]
        );
    }

    public function strip_ver(string $src): string {
        if ($src === '' || !str_contains($src, 'ver=')) {
            return $src;
        }
        $path = (string) wp_parse_url($src, PHP_URL_PATH);
        $core = (defined('WPINC') ? '/' . WPINC . '/' : '/wp-includes/');
        if (!str_contains($path, $core) && !str_contains($path, '/wp-admin/')) {
            return $src;
        }

        return remove_query_arg('ver', $src);
    }

    public function login_robots(): void {
        header('X-Robots-Tag: noindex, nofollow', false);
    }

    public function headers(): void {
        if (headers_sent()) {
            return;
        }
        header('X-Frame-Options: SAMEORIGIN', false);
        header('X-Content-Type-Options: nosniff', false);
        header('Referrer-Policy: strict-origin-when-cross-origin', false);
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()', false);
        if (Settings::get('send_hsts', false) && is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains', false);
        }
    }
}
