<?php

namespace Distribuidora_Lunic\Modules\MegaMenu;

defined('ABSPATH') || exit;

class Admin {

    public const PAGE = 'lunic-mega-menu';

    private string $hook = '';

    public function register(): void {
        add_action('admin_menu', [$this, 'menu'], 20);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('admin_post_lunic_save_mega_menu', [$this, 'save']);
    }

    public function menu(): void {
        $hook = add_submenu_page(
            \Distribuidora_Lunic\Settings::MENU_SLUG,
            __('Menú de categorías', 'distribuidora-lunic'),
            __('Menú de categorías', 'distribuidora-lunic'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'render']
        );
        $this->hook = is_string($hook) ? $hook : '';
    }

    public function assets(string $hook): void {
        if ($this->hook === '' || $hook !== $this->hook) {
            return;
        }
        wp_enqueue_style(
            'distribuidora-lunic-admin',
            DISTRIBUIDORA_LUNIC_URL . 'assets/css/admin.css',
            [],
            DISTRIBUIDORA_LUNIC_VERSION
        );
        wp_enqueue_script(
            'distribuidora-lunic-mega-menu',
            DISTRIBUIDORA_LUNIC_URL . 'assets/js/mega-menu-admin.js',
            [],
            DISTRIBUIDORA_LUNIC_VERSION,
            true
        );
    }

    public function save(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso.', 'distribuidora-lunic'));
        }
        check_admin_referer('lunic_save_mega_menu');
        $raw = isset($_POST['lunic_mega_menu']) ? wp_unslash($_POST['lunic_mega_menu']) : '';
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $columns = is_array($decoded) && isset($decoded['columns']) && is_array($decoded['columns'])
            ? $decoded['columns']
            : [];
        update_option(Menu::OPTION, ['columns' => Menu::normalize($columns)]);
        \Distribuidora_Lunic\Page_Cache::flush();
        wp_safe_redirect(add_query_arg([
            'page' => self::PAGE,
            'updated' => '1',
        ], admin_url('admin.php')));
        exit;
    }

    public function render(): void {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        $columns = Menu::columns();
        $used = [];
        foreach ($columns as $ids) {
            foreach ($ids as $id) {
                $used[$id] = true;
            }
        }
        $pool = [];
        foreach (Menu::available_terms() as $term) {
            if (!isset($used[$term->term_id])) {
                $pool[] = $term;
            }
        }
        $labels = [
            __('Columna 1', 'distribuidora-lunic'),
            __('Columna 2', 'distribuidora-lunic'),
            __('Columna 3', 'distribuidora-lunic'),
        ];
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Menú de categorías', 'distribuidora-lunic') . '</h1>';
        if (isset($_GET['updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Menú guardado.', 'distribuidora-lunic') . '</p></div>';
        }
        echo '<p>' . esc_html__('Arrastrá cada categoría a una columna. Lo que queda en «Sin mostrar» no aparece en el desplegable. El orden de cada columna es el orden del menú.', 'distribuidora-lunic') . '</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" id="lunic-mega-form">';
        echo '<input type="hidden" name="action" value="lunic_save_mega_menu">';
        wp_nonce_field('lunic_save_mega_menu');
        echo '<input type="hidden" name="lunic_mega_menu" id="lunic-mega-data" value="">';
        echo '<div class="lunic-mega-board">';
        echo '<section class="lunic-mega-pool">';
        echo '<h2>' . esc_html__('Sin mostrar', 'distribuidora-lunic') . '</h2>';
        echo '<ul class="lunic-mega-list" data-list="pool">';
        foreach ($pool as $term) {
            echo self::item($term);
        }
        echo '</ul></section>';
        echo '<div class="lunic-mega-cols">';
        foreach ($columns as $index => $ids) {
            echo '<section>';
            echo '<h2>' . esc_html($labels[$index]) . '</h2>';
            echo '<ul class="lunic-mega-list" data-list="' . esc_attr((string) $index) . '">';
            foreach ($ids as $id) {
                $term = get_term($id, 'product_cat');
                if ($term && !is_wp_error($term)) {
                    echo self::item($term);
                }
            }
            echo '</ul></section>';
        }
        echo '</div></div>';
        echo '<p><button type="submit" class="button button-primary">' . esc_html__('Guardar menú', 'distribuidora-lunic') . '</button></p>';
        echo '</form></div>';
    }

    private static function item(\WP_Term $term): string {
        $count = sprintf(
            /* translators: %d: product count */
            _n('%d producto', '%d productos', (int) $term->count, 'distribuidora-lunic'),
            (int) $term->count
        );
        return '<li draggable="true" data-id="' . esc_attr((string) $term->term_id) . '"><span>' . esc_html($term->name) . '</span><small>' . esc_html($count) . '</small></li>';
    }
}
