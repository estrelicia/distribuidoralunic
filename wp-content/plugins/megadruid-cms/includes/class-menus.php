<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Inventario de menús y recorte para el rol marcado.
 * No reconstruye los submenús de WooCommerce ni de Lunic.
 */
final class Menus {

    public function register(): void {
        add_filter('show_admin_bar', [$this, 'show_frontend_admin_bar'], 20);
        add_action('admin_bar_menu', [$this, 'capture_bar'], 100000);
        add_action('admin_menu', [$this, 'hide_side'], 999999);
        add_action('admin_bar_menu', [$this, 'hide_bar'], 999998);
        add_action('admin_init', [$this, 'block_screen']);
    }

    public function show_frontend_admin_bar(bool $show): bool {
        if (is_admin() || !is_user_logged_in()) {
            return $show;
        }
        if (Access::manages_brand()) {
            return $show;
        }
        $roles = Settings::get('hide_frontend_admin_bar_roles', []);
        if (!is_array($roles) || $roles === []) {
            return $show;
        }

        return !in_array(Access::primary_role(), $roles, true);
    }

    public function capture_bar(\WP_Admin_Bar $bar): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $ids = [];
        foreach ((array) $bar->get_nodes() as $node) {
            if (!is_object($node) || !isset($node->id)) {
                continue;
            }
            $id = self::clean_slug((string) $node->id);
            if ($id === '' || in_array($id, self::bar_excluded(), true)) {
                continue;
            }
            $ids[] = $id;
        }
        if ($ids !== []) {
            set_transient('mdcms_bar_inventory', array_values(array_unique($ids)), DAY_IN_SECONDS);
        }
    }

    public function hide_side(): void {
        if (!Access::receives_trimmed()) {
            return;
        }
        foreach ($this->hidden_for_current('hidden_side_menus') as $slug) {
            if (str_contains($slug, '::')) {
                [$parent, $child] = explode('::', $slug, 2);
                remove_submenu_page($parent, $child);
                continue;
            }
            remove_menu_page($slug);
        }
    }

    public function hide_bar(\WP_Admin_Bar $bar): void {
        if (!Access::receives_trimmed()) {
            return;
        }
        foreach ($this->hidden_for_current('hidden_admin_bar') as $id) {
            $bar->remove_node($id);
        }
    }

    public function block_screen(): void {
        if (!Access::receives_trimmed() || wp_doing_ajax()) {
            return;
        }
        $pagenow = isset($GLOBALS['pagenow']) ? (string) $GLOBALS['pagenow'] : '';
        if (in_array($pagenow, ['admin-ajax.php', 'admin-post.php', 'async-upload.php'], true)) {
            return;
        }
        $page = isset($_GET['page']) ? self::clean_slug((string) wp_unslash($_GET['page'])) : '';
        if ($page === 'megadruid-cms' || str_contains($page, 'megadruid-cms')) {
            return;
        }
        $hidden = $this->hidden_for_current('hidden_side_menus');
        if ($hidden === []) {
            return;
        }
        $post_type = isset($_GET['post_type']) ? sanitize_key((string) wp_unslash($_GET['post_type'])) : '';
        foreach ($hidden as $slug) {
            if (self::request_matches_slug($slug, $pagenow, $page, $post_type)) {
                wp_die(
                    esc_html__('No tenés permiso para abrir esta pantalla.', 'megadruid-cms'),
                    esc_html__('Permiso denegado', 'megadruid-cms'),
                    ['response' => 403]
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $posted
     * @return array<string, mixed>
     */
    public static function fill_missing(array $posted): array {
        $roles = isset($posted['restricted_roles']) && is_array($posted['restricted_roles'])
            ? $posted['restricted_roles']
            : Settings::get('restricted_roles', []);
        if (!is_array($roles)) {
            $roles = [];
        }
        $side = isset($posted['hidden_side_menus']) && is_array($posted['hidden_side_menus'])
            ? $posted['hidden_side_menus']
            : [];
        $bar = isset($posted['hidden_admin_bar']) && is_array($posted['hidden_admin_bar'])
            ? $posted['hidden_admin_bar']
            : [];
        foreach ($roles as $role) {
            $role = sanitize_key((string) $role);
            if ($role === '') {
                continue;
            }
            if (!array_key_exists($role, $side)) {
                $side[$role] = [];
            }
            if (!array_key_exists($role, $bar)) {
                $bar[$role] = [];
            }
        }
        $posted['hidden_side_menus'] = $side;
        $posted['hidden_admin_bar'] = $bar;

        return $posted;
    }

    public static function render_inventory(): void {
        $roles = Settings::get('restricted_roles', []);
        if (!is_array($roles) || $roles === []) {
            echo '<p class="description">' . esc_html__('Marcá al menos un rol para elegir qué menús se ocultan.', 'megadruid-cms') . '</p>';
            return;
        }
        $side = self::side_tree();
        $bar = self::known_bar_ids();
        $hidden_side = Settings::get('hidden_side_menus', []);
        $hidden_bar = Settings::get('hidden_admin_bar', []);
        if (!is_array($hidden_side)) {
            $hidden_side = [];
        }
        if (!is_array($hidden_bar)) {
            $hidden_bar = [];
        }
        echo '<h2 class="mdcms-section-title">' . esc_html__('Menús ocultos por rol', 'megadruid-cms') . '</h2>';
        echo '<p class="description">' . esc_html__('El recorte aplica solo a los roles marcados arriba. Quien administra la marca sigue viendo el escritorio completo. Megadruid CMS no se puede ocultar.', 'megadruid-cms') . '</p>';
        foreach ($roles as $role) {
            $role = sanitize_key((string) $role);
            if ($role === '' || !isset(wp_roles()->roles[$role])) {
                continue;
            }
            $label = translate_user_role((string) wp_roles()->roles[$role]['name']);
            $side_marked = isset($hidden_side[$role]) && is_array($hidden_side[$role]) ? $hidden_side[$role] : [];
            $bar_marked = isset($hidden_bar[$role]) && is_array($hidden_bar[$role]) ? $hidden_bar[$role] : [];
            echo '<details class="mdcms-menu-role" open>';
            echo '<summary>' . esc_html($label) . '</summary>';
            echo '<div class="mdcms-menu-columns">';
            echo '<fieldset><legend>' . esc_html__('Menú lateral', 'megadruid-cms') . '</legend>';
            foreach ($side as $item) {
                self::render_side_choice($role, $item, $side_marked, 0);
            }
            echo '</fieldset>';
            echo '<fieldset><legend>' . esc_html__('Barra de admin', 'megadruid-cms') . '</legend>';
            if ($bar === []) {
                echo '<p class="description">' . esc_html__('Abrí cualquier pantalla del escritorio y volvé a esta pestaña para listar la barra.', 'megadruid-cms') . '</p>';
            }
            foreach ($bar as $id) {
                $input = 'mdcms_bar_' . $role . '_' . md5($id);
                echo '<label class="mdcms-role" for="' . esc_attr($input) . '">';
                echo '<input type="checkbox" id="' . esc_attr($input) . '" name="mdcms[hidden_admin_bar][' . esc_attr($role) . '][]" value="' . esc_attr($id) . '" ' . checked(in_array($id, $bar_marked, true), true, false) . ' />';
                echo esc_html($id) . '</label>';
            }
            echo '</fieldset></div></details>';
        }
    }

    /**
     * @param array<string, mixed> $item
     * @param string[] $marked
     */
    private static function render_side_choice(string $role, array $item, array $marked, int $depth): void {
        $slug = (string) ($item['slug'] ?? '');
        $label = (string) ($item['label'] ?? $slug);
        if ($slug !== '') {
            $input = 'mdcms_side_' . $role . '_' . md5($slug);
            echo '<label class="mdcms-role" for="' . esc_attr($input) . '" style="margin-left:' . (int) ($depth * 16) . 'px">';
            echo '<input type="checkbox" id="' . esc_attr($input) . '" name="mdcms[hidden_side_menus][' . esc_attr($role) . '][]" value="' . esc_attr($slug) . '" ' . checked(in_array($slug, $marked, true), true, false) . ' />';
            echo esc_html($label) . '</label>';
        }
        foreach ($item['children'] ?? [] as $child) {
            if (is_array($child)) {
                self::render_side_choice($role, $child, $marked, $depth + 1);
            }
        }
    }

    /**
     * @return array<int, array{slug: string, label: string, children: array<int, array{slug: string, label: string}>}>
     */
    public static function side_tree(): array {
        global $menu, $submenu;
        $tree = [];
        foreach ((array) $menu as $item) {
            if (!is_array($item)) {
                continue;
            }
            $slug = self::clean_slug((string) ($item[2] ?? ''));
            $label = wp_strip_all_tags((string) ($item[0] ?? $slug));
            if ($slug === '' || str_contains($slug, 'megadruid-cms') || str_contains($slug, 'separator')) {
                continue;
            }
            $children = [];
            foreach ((array) ($submenu[$slug] ?? []) as $child) {
                if (!is_array($child)) {
                    continue;
                }
                $child_slug = self::clean_slug((string) ($child[2] ?? ''));
                if ($child_slug === '' || str_contains($child_slug, 'megadruid-cms')) {
                    continue;
                }
                $children[] = [
                    'slug' => $slug . '::' . $child_slug,
                    'label' => wp_strip_all_tags((string) ($child[0] ?? $child_slug)),
                    'children' => [],
                ];
            }
            $tree[] = [
                'slug' => $slug,
                'label' => $label,
                'children' => $children,
            ];
        }

        return $tree;
    }

    /**
     * @return string[]
     */
    public static function known_side_slugs(): array {
        $slugs = [];
        foreach (self::side_tree() as $item) {
            self::collect_slugs($item, $slugs);
        }

        return $slugs;
    }

    /**
     * @param array<string, mixed> $item
     * @param string[] $slugs
     */
    private static function collect_slugs(array $item, array &$slugs): void {
        if (!empty($item['slug'])) {
            $slugs[] = (string) $item['slug'];
        }
        foreach ($item['children'] ?? [] as $child) {
            if (is_array($child)) {
                self::collect_slugs($child, $slugs);
            }
        }
    }

    /**
     * @return string[]
     */
    public static function known_bar_ids(): array {
        $stored = get_transient('mdcms_bar_inventory');
        if (!is_array($stored)) {
            return [];
        }
        $ids = [];
        foreach ($stored as $id) {
            $id = self::clean_slug((string) $id);
            if ($id !== '' && !in_array($id, self::bar_excluded(), true)) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    public static function clean_slug(string $slug): string {
        $slug = trim(wp_strip_all_tags($slug));
        if ($slug === '' || !preg_match('/^[a-zA-Z0-9_.:?=&%\/%+-]+$/', $slug)) {
            return '';
        }

        return $slug;
    }

    /**
     * @return string[]
     */
    private function hidden_for_current(string $key): array {
        $map = Settings::get($key, []);
        if (!is_array($map)) {
            return [];
        }
        $role = Access::primary_role();
        $hidden = $map[$role] ?? [];

        return is_array($hidden) ? array_values(array_filter($hidden, 'is_string')) : [];
    }

    public static function request_matches_slug(string $slug, string $pagenow, string $page, string $post_type): bool {
        if (str_contains($slug, 'megadruid-cms')) {
            return false;
        }
        if (str_contains($slug, '::')) {
            [$parent, $child] = explode('::', $slug, 2);
            if ($page !== '' && ($page === $child || $parent . '::' . $page === $slug)) {
                return true;
            }
            if ($pagenow !== '' && $pagenow === $child) {
                return true;
            }
            if ($post_type !== '' && ($child === 'edit.php?post_type=' . $post_type || str_contains($child, 'post_type=' . $post_type))) {
                return $pagenow === 'edit.php' || $pagenow === 'post.php' || $pagenow === 'post-new.php';
            }

            return false;
        }
        if ($slug === $pagenow || ($page !== '' && $slug === $page)) {
            return true;
        }
        if ($post_type !== '' && $slug === $pagenow . '?post_type=' . $post_type) {
            return true;
        }

        return $page !== '' && $slug === 'admin.php?page=' . $page;
    }

    /**
     * @return string[]
     */
    private static function bar_excluded(): array {
        return [
            'menu-toggle',
            'wp-logo',
            'my-account',
            'user-actions',
            'logout',
            'top-secondary',
            'user-info',
            'mdcms-logo',
        ];
    }
}
