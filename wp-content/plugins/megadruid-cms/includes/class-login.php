<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Login: logo del cliente e imagen de fondo. El CSS no se carga en la tienda.
 */
final class Login {

    public const LOGO_WIDTH = 320;

    public const LOGO_HEIGHT = 84;

    public function register(): void {
        add_action('login_enqueue_scripts', [$this, 'enqueue']);
        add_filter('login_headerurl', [$this, 'header_url']);
        add_filter('login_headertext', [$this, 'header_text']);
        add_filter('login_message', [$this, 'preview_message']);
    }

    /**
     * Guarda una vista previa corta. No escribe mdcms_settings.
     *
     * @param array<string, mixed> $input
     */
    public static function store_preview(array $input): void {
        $keys = ['login_logo', 'login_background_image'];
        $data = [];
        $schema = Settings::schema();
        foreach ($keys as $key) {
            if (!isset($schema[$key]) || !array_key_exists($key, $input)) {
                continue;
            }
            $data[$key] = Settings::sanitize_value($input[$key], $schema[$key]);
        }
        set_transient('mdcms_login_preview_' . get_current_user_id(), $data, 15 * MINUTE_IN_SECONDS);
    }

    public function preview_message(string $message): string {
        if ($this->preview() === null) {
            return $message;
        }

        return $message . '<p class="message">' . esc_html__('Vista previa de Megadruid CMS. Los ajustes definitivos no cambiaron.', 'megadruid-cms') . '</p>';
    }

    public function enqueue(): void {
        $css = $this->css();
        if ($css === '') {
            return;
        }
        wp_register_style('mdcms-login', false, [], MDCMS_VERSION);
        wp_enqueue_style('mdcms-login');
        wp_add_inline_style('mdcms-login', $css);
    }

    public function header_url(string $url): string {
        if ($this->logo_url() === '') {
            return $url;
        }

        return home_url('/');
    }

    public function header_text(string $text): string {
        if ($this->logo_url() === '') {
            return $text;
        }

        return get_bloginfo('name', 'display');
    }

    public function css(): string {
        return implode('', array_filter([
            $this->background_css(),
            $this->logo_css(),
        ]));
    }

    private function background_css(): string {
        $image = (string) $this->setting('login_background_image', '');
        if ($image === '') {
            return '';
        }

        return 'body.login{background-image:url(' . esc_url($image, ['http', 'https']) . ')!important;'
            . 'background-position:center center!important;background-repeat:no-repeat!important;'
            . 'background-size:cover!important;}';
    }

    private function logo_css(): string {
        $logo = $this->logo_url();
        if ($logo === '') {
            return '';
        }
        $width = self::LOGO_WIDTH;
        $height = self::LOGO_HEIGHT;

        return '#login h1 a,.login h1 a{background-image:url(' . esc_url($logo, ['http', 'https'])
            . ')!important;width:' . $width . 'px!important;height:' . $height
            . 'px;max-width:100%;background-size:contain;background-position:center center;'
            . 'background-repeat:no-repeat;}';
    }

    private function logo_url(): string {
        $logo = (string) $this->setting('login_logo', '');
        if ($logo === '' || str_contains($logo, '/megadruid-cms/assets/img/')) {
            return '';
        }

        return $logo;
    }

    private function setting(string $key, mixed $default = null): mixed {
        $preview = $this->preview();
        if (is_array($preview) && array_key_exists($key, $preview)) {
            return $preview[$key];
        }

        return Settings::get($key, $default);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function preview(): ?array {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return null;
        }
        if (!isset($_GET['mdcms_preview']) || (string) $_GET['mdcms_preview'] !== '1') {
            return null;
        }
        $data = get_transient('mdcms_login_preview_' . get_current_user_id());

        return is_array($data) ? $data : null;
    }
}
