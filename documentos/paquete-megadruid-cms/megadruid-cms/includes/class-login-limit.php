<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Límite de intentos de login por IP, antes de comprobar la contraseña.
 */
final class Login_Limit {

    public function register(): void {
        if (self::delegated_to_legacy()) {
            return;
        }
        if (Settings::get('login_limit', true)) {
            add_filter('authenticate', [$this, 'block_locked'], 5, 3);
            add_action('wp_login_failed', [$this, 'on_fail'], 10, 1);
            add_action('wp_login', [$this, 'on_success'], 10, 2);
        }
        if (Settings::get('generic_login_errors', true)) {
            add_filter('login_errors', [$this, 'generic_error']);
        }
    }

    /**
     * Megadruid Seguridad ya cuenta y bloquea. No se engancha dos veces.
     */
    public static function delegated_to_legacy(): bool {
        return function_exists('wbs_setting') && (bool) wbs_setting('login_limit');
    }

    /**
     * @param \WP_User|\WP_Error|null $user
     * @return \WP_User|\WP_Error|null
     */
    public function block_locked(mixed $user, mixed $username, mixed $password): mixed {
        $username = is_string($username) ? $username : '';
        $password = is_string($password) ? $password : '';
        if ($username === '' && $password === '') {
            return $user;
        }
        if (self::is_locked()) {
            return new \WP_Error('mdcms_locked', self::locked_message());
        }

        return $user;
    }

    public function on_fail(mixed $username): void {
        unset($username);
        if (self::is_locked()) {
            return;
        }
        $ip = Client_Ip::client_ip();
        $max = max(3, min(20, (int) Settings::get('login_max', 5)));
        $window = max(1, (int) Settings::get('login_window', 15)) * MINUTE_IN_SECONDS;
        $key = self::key_ip($ip);
        $count = (int) get_transient($key) + 1;
        set_transient($key, $count, $window);
        if ($count >= $max) {
            set_transient(self::lock_key($ip), 1, $window);
        }
    }

    /**
     * @param mixed $user
     */
    public function on_success(mixed $user_login, mixed $user): void {
        unset($user_login, $user);
        $ip = Client_Ip::client_ip();
        delete_transient(self::key_ip($ip));
        delete_transient(self::lock_key($ip));
    }

    public function generic_error(mixed $error): string {
        $ip = Client_Ip::client_ip();
        if (get_transient(self::lock_key($ip)) || (is_string($error) && str_contains($error, 'mdcms_locked'))) {
            return self::locked_message();
        }

        return self::generic_message();
    }

    public static function locked_message(): string {
        return __('Demasiados intentos. Esperá unos minutos e intentá de nuevo.', 'megadruid-cms');
    }

    public static function generic_message(): string {
        return __('Credenciales incorrectas.', 'megadruid-cms');
    }

    public static function is_locked(): bool {
        $ip = Client_Ip::client_ip();
        if (get_transient(self::lock_key($ip))) {
            return true;
        }

        return (int) get_transient(self::key_ip($ip)) >= max(3, min(20, (int) Settings::get('login_max', 5)));
    }

    public static function key_ip(string $ip): string {
        return 'mdcms_lf_' . md5($ip);
    }

    public static function lock_key(string $ip): string {
        return 'mdcms_lk_' . md5($ip);
    }
}
