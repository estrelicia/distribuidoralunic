<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

class Page_Cache {

    private const TTL = 900;

    public static function serve(): void {
        if (!self::request_can_cache()) {
            return;
        }
        $file = self::file();
        if (!is_file($file) || filemtime($file) < time() - self::TTL) {
            return;
        }
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Lunic-Cache: HIT');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
        if ($https) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        readfile($file);
        exit;
    }

    public static function capture(): void {
        add_action('template_redirect', [self::class, 'start'], 0);
        add_action('init', [self::class, 'register_flush_hooks']);
        add_action('init', [self::class, 'maybe_rekey'], 20);
    }

    public static function register_flush_hooks(): void {
        add_action('save_post_product', [self::class, 'flush']);
        add_action('woocommerce_update_product', [self::class, 'flush']);
        add_action('woocommerce_product_set_stock', [self::class, 'flush']);
        add_action('woocommerce_variation_set_stock', [self::class, 'flush']);
        foreach (['created_product_cat', 'edited_product_cat', 'delete_product_cat'] as $hook) {
            add_action($hook, [self::class, 'flush']);
        }
        add_action('update_option_wcd_discount_rules', [self::class, 'flush']);
    }

    public static function maybe_rekey(): void {
        if (get_option('lunic_page_cache_key') === '2') {
            return;
        }
        self::flush();
        update_option('lunic_page_cache_key', '2', false);
    }

    public static function start(): void {
        if (!self::view_can_cache()) {
            return;
        }
        ob_start([self::class, 'store']);
    }

    public static function store(string $html): string {
        if (!self::host_is_canonical() || http_response_code() !== 200 || strlen($html) < 1000 || !str_contains($html, 'lunic-header')) {
            return $html;
        }
        $dir = self::dir();
        if (!is_dir($dir) && !wp_mkdir_p($dir)) {
            return $html;
        }
        file_put_contents(self::file(), $html, LOCK_EX);
        if (!headers_sent()) {
            header('X-Lunic-Cache: MISS');
        }
        return $html;
    }

    private static function request_can_cache(): bool {
        if (PHP_SAPI === 'cli' || wp_doing_ajax() || wp_doing_cron()) {
            return false;
        }
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method !== 'GET' || !empty($_GET)) {
            return false;
        }
        $path = self::path();
        if ($path === '' || self::is_private_path($path) || self::has_private_cookie() || !self::host_is_canonical()) {
            return false;
        }
        return true;
    }

    public static function flush(): void {
        $dir = self::dir();
        if (!is_dir($dir)) {
            return;
        }
        $files = glob($dir . '/*.html');
        if (!$files) {
            return;
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private static function view_can_cache(): bool {
        if (!self::request_can_cache() || is_user_logged_in() || is_feed() || is_preview()) {
            return false;
        }
        if (is_front_page()) {
            return true;
        }
        if (!function_exists('is_shop')) {
            return false;
        }
        if (is_shop() || is_product() || is_product_taxonomy()) {
            return true;
        }
        return is_page(12340);
    }

    private static function has_private_cookie(): bool {
        foreach ($_COOKIE as $name => $value) {
            if (strpos($name, 'wordpress_logged_in_') === 0 || strpos($name, 'wp_woocommerce_session_') === 0 || $name === 'woocommerce_items_in_cart' || $name === 'woocommerce_cart_hash') {
                return true;
            }
        }
        return false;
    }

    private static function is_private_path(string $path): bool {
        foreach (['/wp-admin', '/wp-login.php', '/carro', '/carrito', '/finalizar-comprar', '/mi-cuenta', '/checkout', '/cart'] as $prefix) {
            if ($path === $prefix || strpos($path, $prefix . '/') === 0) {
                return true;
            }
        }
        return false;
    }

    private static function path(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return is_string($path) ? $path : '/';
    }

    private static function dir(): string {
        return WP_CONTENT_DIR . '/cache/lunic';
    }

    /**
     * Dominio guardado en la base. No usa WP_HOME: ese valor sigue al Host del pedido.
     *
     * @return array{scheme: string, host: string}|null
     */
    private static function canonical_parts(): ?array {
        static $parts = false;
        if ($parts !== false) {
            return $parts;
        }
        global $wpdb;
        if (!isset($wpdb)) {
            $parts = null;
            return null;
        }
        $home = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'home' LIMIT 1");
        $parsed = is_string($home) ? wp_parse_url($home) : null;
        if (!is_array($parsed) || empty($parsed['host'])) {
            $parts = null;
            return null;
        }
        $parts = [
            'scheme' => !empty($parsed['scheme']) ? strtolower((string) $parsed['scheme']) : 'https',
            'host' => strtolower((string) $parsed['host']),
        ];
        return $parts;
    }

    private static function host_is_canonical(): bool {
        $parts = self::canonical_parts();
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $host = (string) preg_replace('/:\d+$/', '', $host);
        if ($parts === null || $host === '') {
            return false;
        }
        return $host === $parts['host'];
    }

    private static function file(): string {
        $parts = self::canonical_parts();
        $prefix = $parts === null ? 'invalid' : $parts['scheme'] . '://' . $parts['host'];
        return self::dir() . '/' . md5($prefix . self::path()) . '.html';
    }
}
