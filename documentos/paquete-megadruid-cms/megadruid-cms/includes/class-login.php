<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Personalización de wp-login.php. El CSS no se carga en la tienda.
 */
final class Login {

    public function register(): void {
        add_action('login_enqueue_scripts', [$this, 'enqueue']);
        add_action('login_footer', [$this, 'print_script'], 1000);
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
        $data = [];
        foreach (Settings::schema() as $key => $field) {
        if (!str_starts_with($key, 'login_') && $key !== 'retina_login_logo') {
            continue;
        }
        if (!array_key_exists($key, $input)) {
            continue;
        }
            $data[$key] = Settings::sanitize_value($input[$key], $field);
        }
        set_transient('mdcms_login_preview_' . get_current_user_id(), $data, 15 * MINUTE_IN_SECONDS);
    }

    public function preview_message(string $message): string {
        if ($this->preview() === null) {
            return $message;
        }

        return $message . '<p class="message">' . esc_html__('Vista previa de Megadruid CMS. Los ajustes definitivos no cambiaron.', 'megadruid-cms') . '</p>';
    }

    public function print_script(): void {
        $js = (string) $this->setting('login_custom_js', '');
        if ($js === '') {
            return;
        }
        echo '<script>' . $js . '</script>';
    }

    public function enqueue(): void {
        $css = $this->css();
        $needs_nav_cleanup = $this->needs_nav_cleanup();
        if ($css === '' && !$needs_nav_cleanup) {
            return;
        }
        wp_register_style('mdcms-login', false, [], MDCMS_VERSION);
        wp_enqueue_style('mdcms-login');
        if ($css !== '') {
            wp_add_inline_style('mdcms-login', $css);
        }
        if ($needs_nav_cleanup) {
            wp_enqueue_script('mdcms-login-nav', false, [], MDCMS_VERSION, true);
            wp_add_inline_script('mdcms-login-nav', $this->nav_cleanup_script());
        }
    }

    public function header_url(string $url): string {
        if ((string) $this->setting('login_logo', '') === '') {
            return $url;
        }

        return home_url('/');
    }

    public function header_text(string $text): string {
        if ((string) $this->setting('login_logo', '') === '') {
            return $text;
        }

        return get_bloginfo('name', 'display');
    }

    public function css(): string {
        $parts = array_filter([
            $this->background_css(),
            $this->logo_css(),
            $this->form_css(),
            $this->links_css(),
            (string) $this->setting('login_custom_css', ''),
        ]);

        return implode('', $parts);
    }

    private function needs_nav_cleanup(): bool {
        $hide_register = (bool) $this->setting('login_hide_register', false);
        $hide_lost = (bool) $this->setting('login_hide_lost_password', false);

        return ($hide_register xor $hide_lost) && get_option('users_can_register');
    }

    private function nav_cleanup_script(): string {
        return <<<'JS'
(function () {
	var nav = document.getElementById('nav');
	if (!nav) {
		return;
	}
	nav.querySelectorAll('a').forEach(function (link) {
		if (link.offsetParent === null || window.getComputedStyle(link).display === 'none') {
			link.remove();
		}
	});
	Array.from(nav.childNodes).forEach(function (node) {
		if (node.nodeType === Node.TEXT_NODE && !node.textContent.trim().replace(/\|/g, '').length) {
			node.remove();
		}
	});
	if (!nav.querySelector('a')) {
		nav.style.display = 'none';
	}
})();
JS;
    }

    private function background_css(): string {
        $rules = [];
        $color = (string) $this->setting('login_background_color', '');
        $image = (string) $this->setting('login_background_image', '');
        $position = (string) $this->setting('login_background_position', 'center center');
        $repeat = (string) $this->setting('login_background_repeat', 'no-repeat');

        if ($color !== '') {
            $rules[] = 'background-color:' . $color . '!important';
        }
        if ($image !== '') {
            $rules[] = 'background-image:url(' . esc_url($image, ['http', 'https']) . ')!important';
            $rules[] = 'background-position:' . $position . '!important';
            $rules[] = 'background-repeat:' . $repeat . '!important';
            if ($this->setting('login_background_fullscreen')) {
                $rules[] = 'background-size:cover!important';
            }
        }
        if ($rules === []) {
            return '';
        }

        return 'body.login{' . implode(';', $rules) . ';}';
    }

    private function logo_css(): string {
        $logo = (string) $this->setting('login_logo', '');
        $width = (int) $this->setting('login_logo_width', 0);
        $height = (int) $this->setting('login_logo_height', 0);
        $margin = (int) $this->setting('login_logo_bottom_margin', 0);
        $retina = (string) $this->setting('retina_login_logo', '');
        if ($logo === '' && $width === 0 && $height === 0 && $margin === 0 && $retina === '') {
            return '';
        }

        $rules = [];
        if ($logo !== '') {
            $rules[] = 'background-image:url(' . esc_url($logo, ['http', 'https']) . ')!important';
        }
        $rules[] = $width > 0 ? 'width:' . $width . 'px!important' : 'width:auto!important';
        $rules[] = 'max-width:100%';
        if ($height > 0) {
            $rules[] = 'height:' . $height . 'px';
        }
        if ($width > 0 && $height > 0) {
            $rules[] = 'background-size:' . $width . 'px ' . $height . 'px';
        } else {
            $rules[] = 'background-size:contain';
            $rules[] = 'background-position-y:center';
        }
        if ($margin > 0) {
            $rules[] = 'margin-bottom:' . $margin . 'px!important';
        }
        $css = '#login h1 a,.login h1 a{' . implode(';', $rules) . ';}';
        if ($retina !== '') {
            $css .= '@media (-webkit-min-device-pixel-ratio:2),(min-resolution:192dpi){'
                . '#login h1 a,.login h1 a{background-image:url(' . esc_url($retina, ['http', 'https']) . ')!important;}'
                . '}';
        }

        return $css;
    }

    private function form_css(): string {
        $css = '';
        $label = (string) $this->setting('login_form_label_color', '');
        $background = (string) $this->setting('login_form_background_color', '');
        $button = (string) $this->setting('login_form_button_color', '');
        $button_text = (string) $this->setting('login_form_button_text_color', '');
        $button_hover = (string) $this->setting('login_form_button_hover_color', '');
        $button_text_hover = (string) $this->setting('login_form_button_text_hover_color', '');

        if ($label !== '') {
            $css .= '#loginform label{color:' . $label . '!important;}';
        }
        if ($background !== '') {
            $css .= '#loginform{background-color:' . $background . '!important;}';
        }
        if ($button !== '' || $button_text !== '') {
            $rules = [];
            if ($button_text !== '') {
                $rules[] = 'color:' . $button_text . '!important';
                $rules[] = 'text-shadow:none';
            }
            if ($button !== '') {
                $rules[] = 'background-color:' . $button . '!important';
                $rules[] = 'border-color:' . $button . '!important';
                $rules[] = 'box-shadow:none';
            }
            $css .= '#loginform .button-primary,#loginform input[type=submit]{' . implode(';', $rules) . ';}';
        }
        if ($button_hover !== '' || $button_text_hover !== '') {
            $rules = [];
            if ($button_text_hover !== '') {
                $rules[] = 'color:' . $button_text_hover . '!important';
            }
            if ($button_hover !== '') {
                $rules[] = 'background-color:' . $button_hover . '!important';
                $rules[] = 'border-color:' . $button_hover . '!important';
            }
            $css .= '#loginform .button-primary:hover,#loginform input[type=submit]:hover{'
                . implode(';', $rules) . ';}';
        }

        return $css;
    }

    private function links_css(): string {
        $css = '';
        $hide_register = (bool) $this->setting('login_hide_register', false);
        $hide_lost = (bool) $this->setting('login_hide_lost_password', false);

        if ($hide_register && $hide_lost) {
            $css .= '#login #nav{display:none!important;}';
        } else {
            if ($hide_register) {
                $css .= '#login #nav a[href*="action=register"]{display:none!important;}';
            }
            if ($hide_lost) {
                $css .= '#login #nav a[href*="action=lostpassword"]{display:none!important;}';
            }
        }
        if ($this->setting('login_hide_back_to')) {
            $css .= '#backtoblog{display:none!important;}';
        }

        $link = (string) $this->setting('login_link_color', '');
        $link_hover = (string) $this->setting('login_link_hover_color', '');
        $privacy = (string) $this->setting('login_privacy_link_color', '');
        $privacy_hover = (string) $this->setting('login_privacy_link_hover_color', '');

        if ($link !== '') {
            $css .= '#backtoblog a,#login #nav a{color:' . $link . '!important;}';
        }
        if ($link_hover !== '') {
            $css .= '#backtoblog a:hover,#login #nav a:hover{color:' . $link_hover . '!important;}';
        }
        if ($privacy !== '') {
            $css .= 'a.privacy-policy-link{color:' . $privacy . '!important;text-decoration:none;}';
        }
        if ($privacy_hover !== '') {
            $css .= 'a.privacy-policy-link:hover{color:' . $privacy_hover . '!important;}';
        }

        return $css;
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
