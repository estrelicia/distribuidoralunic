<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Shell de escritorio propio (no usa postbox ni nav-tab de WordPress).
 */
final class Admin_Layout {

    /**
     * @return array<string, array{label: string, icon: string, intro: string}>
     */
    public static function tabs(): array {
        return [
            'login' => [
                'label' => __('Login', 'megadruid-cms'),
                'icon' => 'dashicons-lock',
                'intro' => __('Logo del cliente e imagen de fondo. El resto del login queda como WordPress.', 'megadruid-cms'),
            ],
            'dashboard' => [
                'label' => __('Escritorio', 'megadruid-cms'),
                'icon' => 'dashicons-dashboard',
                'intro' => __('Qué paneles nativos ve cada rol en el Escritorio.', 'megadruid-cms'),
            ],
            'menus' => [
                'label' => __('Menús', 'megadruid-cms'),
                'icon' => 'dashicons-menu',
                'intro' => __('Dos cosas distintas: menús del escritorio (wp-admin) y la barra negra cuando alguien mira la tienda.', 'megadruid-cms'),
            ],
            'general' => [
                'label' => __('Ajustes', 'megadruid-cms'),
                'icon' => 'dashicons-admin-generic',
                'intro' => __('Cajas del editor, CSS extra, exportar e importar.', 'megadruid-cms'),
            ],
            'security' => [
                'label' => __('Seguridad', 'megadruid-cms'),
                'icon' => 'dashicons-shield',
                'intro' => __('Capa base detrás de Cloudflare. No reemplaza un WAF ni el doble factor.', 'megadruid-cms'),
            ],
            'manual' => [
                'label' => __('Manual', 'megadruid-cms'),
                'icon' => 'dashicons-book-alt',
                'intro' => __('Cómo usar cada pestaña, en orden de trabajo habitual.', 'megadruid-cms'),
            ],
        ];
    }

    public static function open_card(string $title, string $description = '', string $icon = '', bool $full_width = false): void {
        $class = 'mdcms-card' . ($full_width ? ' mdcms-card--full' : '');
        echo '<section class="' . esc_attr($class) . '">';
        echo '<header class="mdcms-card__head">';
        if ($icon !== '') {
            echo '<span class="mdcms-card__icon dashicons ' . esc_attr($icon) . '" aria-hidden="true"></span>';
        }
        echo '<div class="mdcms-card__titles"><h2 class="mdcms-card__title">' . esc_html($title) . '</h2>';
        if ($description !== '') {
            echo '<p class="mdcms-card__intro">' . esc_html($description) . '</p>';
        }
        echo '</div></header>';
        echo '<div class="mdcms-card__body">';
    }

    public static function close_card(): void {
        echo '</div></section>';
    }
}
