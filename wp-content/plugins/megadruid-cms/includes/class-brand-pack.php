<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Logos empaquetados y valores de fábrica de la marca Megadruid.
 */
final class Brand_Pack {

    public const VERSION = 3;

    public static function mark_url(): string {
        return MDCMS_URL . 'assets/img/megadruid-mark.png';
    }

    public static function logo_url(): string {
        return MDCMS_URL . 'assets/img/megadruid-logo.png';
    }

    /**
     * @return array<string, mixed>
     */
    public static function factory(): array {
        return [
            'login_logo' => '',
            'retina_login_logo' => '',
            'login_logo_width' => 84,
            'login_logo_height' => 84,
            'login_background_color' => '',
            'login_background_image' => '',
            'login_form_label_color' => '',
            'login_form_background_color' => '',
            'login_form_button_color' => '',
            'login_form_button_text_color' => '',
            'login_form_button_hover_color' => '',
            'login_form_button_text_hover_color' => '',
            'login_link_color' => '',
            'login_link_hover_color' => '',
            'login_privacy_link_color' => '',
            'login_privacy_link_hover_color' => '',
        ];
    }

    /**
     * Rellena claves vacías o ausentes. No pisa un valor que ya hayas escrito.
     */
    public static function seed(): void {
        $stored = get_option(Settings::OPTION, []);
        if (!is_array($stored)) {
            $stored = [];
        }
        if ((int) ($stored['brand_pack_version'] ?? 0) >= self::VERSION) {
            return;
        }
        $stored = array_merge($stored, self::factory());
        $stored['brand_pack_version'] = self::VERSION;
        update_option(Settings::OPTION, array_merge(Settings::defaults(), $stored));
    }
}
