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
            $ids = array_values(array_unique($ids));
            update_option('mdcms_bar_inventory', $ids, false);
            set_transient('mdcms_bar_inventory', $ids, DAY_IN_SECONDS);
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
        if ($post_type === '' && $pagenow === 'post.php') {
            $post_type = self::post_type_being_edited();
        }
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
        if (!array_key_exists('full_access_admin_ids', $posted) || !is_array($posted['full_access_admin_ids'])) {
            $posted['full_access_admin_ids'] = [];
        }
        if (!array_key_exists('hide_menu_roles', $posted) || !is_array($posted['hide_menu_roles'])) {
            $posted['hide_menu_roles'] = [];
        }
        $posted['hidden_side_menus'] = self::posted_slug_list('hidden_side_menus', 'mdcms_side_listed', $posted);
        $posted['hidden_admin_bar'] = self::posted_slug_list('hidden_admin_bar', 'mdcms_bar_listed', $posted);
        unset($posted['hidden_side_on'], $posted['hidden_bar_on']);

        return $posted;
    }

    /**
     * @param mixed $values
     * @return string[]
     */
    private static function decode_slug_list(mixed $values): array {
        if (!is_array($values)) {
            return [];
        }
        $out = [];
        foreach ($values as $value) {
            if (is_array($value)) {
                continue;
            }
            $slug = self::slug_from_posted((string) $value);
            if ($slug !== '') {
                $out[] = $slug;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Clave de formulario sin puntos ni signos de consulta (PHP los altera en POST).
     */
    public static function form_key(string $slug): string {
        return rtrim(strtr(base64_encode($slug), '+/', '-_'), '=');
    }

    public static function form_slug(string $key): string {
        $pad = strlen($key) % 4;
        if ($pad > 0) {
            $key .= str_repeat('=', 4 - $pad);
        }
        $raw = base64_decode(strtr($key, '-_', '+/'), true);

        return is_string($raw) ? $raw : '';
    }

    /**
     * El formulario manda la clave codificada. Un id real (por ejemplo «comments»)
     * también puede parecer base64: si al decodificar no queda un slug válido, se usa el texto tal cual.
     */
    public static function slug_from_posted(string $raw): string {
        $decoded = self::form_slug($raw);
        if ($decoded !== '') {
            $decoded_slug = self::clean_slug($decoded);
            if ($decoded_slug !== '' && self::form_key($decoded_slug) === $raw) {
                return $decoded_slug;
            }
        }

        return self::clean_slug($raw);
    }

    /**
     * @return string[]
     */
    public static function hidden_slug_list(string $key): array {
        $raw = Settings::get($key, []);
        if (!is_array($raw) || $raw === []) {
            return [];
        }
        $out = [];
        foreach ($raw as $index => $item) {
            if (is_array($item)) {
                $slug = self::clean_slug((string) $index);
            } else {
                $slug = self::clean_slug((string) $item);
            }
            if ($slug !== '') {
                $out[] = $slug;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Perfiles únicos para todo lo marcado en el mapa.
     *
     * @return string[]
     */
    public static function hide_menu_roles(): array {
        $roles = Settings::get('hide_menu_roles', []);
        if (is_array($roles) && $roles !== []) {
            return array_values(array_filter($roles, 'is_string'));
        }
        $union = [];
        foreach (['hidden_side_menus', 'hidden_admin_bar'] as $key) {
            $map = Settings::get($key, []);
            if (!is_array($map)) {
                continue;
            }
            foreach ($map as $role_list) {
                if (!is_array($role_list)) {
                    continue;
                }
                foreach ($role_list as $role) {
                    if (is_string($role) && $role !== '') {
                        $union[] = $role;
                    }
                }
            }
        }

        return array_values(array_unique($union));
    }

    /**
     * @param string[] $hide_menu_roles
     * @param array<string, string> $role_labels
     */
    public static function render_inventory(array $hide_menu_roles, array $role_labels): void {
        $side = self::side_tree();
        $bar = self::known_bar_ids();
        $hidden_side = self::hidden_slug_list('hidden_side_menus');
        $hidden_bar = self::hidden_slug_list('hidden_admin_bar');
        Admin_Layout::open_card(
            __('Menús del escritorio (wp-admin)', 'megadruid-cms'),
            __('Esto es el panel de WordPress: menú de la izquierda y atajos de la barra negra mientras estás en el escritorio. No es la tienda.', 'megadruid-cms'),
            'dashicons-networking',
            true
        );
        echo '<div class="mdcms-hide-who">';
        echo '<h3>' . esc_html__('A quién se le ocultan los ítems marcados', 'megadruid-cms') . '</h3>';
        echo '<p class="description">' . esc_html__('Agregá perfiles (Editor, Administrador, etc.). Todo lo que tildes en el mapa de abajo se les esconde a esos perfiles. Quien está en «Administradores con escritorio completo» lo sigue viendo.', 'megadruid-cms') . '</p>';
        self::render_role_adder('mdcms[hide_menu_roles][]', $hide_menu_roles, $role_labels, false);
        echo '</div>';
        echo '<h3>' . esc_html__('Qué ocultar: menú de la izquierda', 'megadruid-cms') . '</h3>';
        if ($side !== []) {
            echo '<input type="hidden" name="mdcms_side_listed" value="1" />';
        }
        echo '<ul class="mdcms-sitemap">';
        foreach ($side as $item) {
            self::render_sitemap_item('hidden_side_menus', $item, $hidden_side);
        }
        echo '</ul>';
        echo '<h3>' . esc_html__('Qué ocultar: atajos de la barra negra (solo dentro del escritorio)', 'megadruid-cms') . '</h3>';
        echo '<p class="description">' . esc_html__('Son los botones de la barra negra cuando ya estás en wp-admin (sitio, comentarios, nuevo…). No es la barra de la tienda: esa se configura en la tarjeta de arriba.', 'megadruid-cms') . '</p>';
        if ($bar === []) {
            echo '<p class="description">' . esc_html__('Abrí cualquier pantalla del escritorio y volvé a esta pestaña para listar esos atajos. Lo que ya estaba oculto se mantiene.', 'megadruid-cms') . '</p>';
        } else {
            echo '<input type="hidden" name="mdcms_bar_listed" value="1" />';
            echo '<ul class="mdcms-sitemap">';
            foreach ($bar as $id) {
                self::render_sitemap_item(
                    'hidden_admin_bar',
                    ['slug' => $id, 'label' => $id, 'children' => []],
                    $hidden_bar
                );
            }
            echo '</ul>';
        }
        Admin_Layout::close_card();
    }

    /**
     * @param array<string, mixed> $item
     * @param string[] $hidden
     */
    private static function render_sitemap_item(string $field, array $item, array $hidden): void {
        $slug = (string) ($item['slug'] ?? '');
        $label = (string) ($item['label'] ?? $slug);
        $children = isset($item['children']) && is_array($item['children']) ? $item['children'] : [];
        echo '<li>';
        if ($slug !== '') {
            $box_id = 'mdcms_hide_' . md5($field . '|' . $slug);
            echo '<label class="mdcms-sitemap__item" for="' . esc_attr($box_id) . '">';
            echo '<input type="checkbox" id="' . esc_attr($box_id) . '" name="mdcms[' . esc_attr($field) . '][]" value="'
                . esc_attr(self::form_key($slug)) . '" ' . checked(in_array($slug, $hidden, true), true, false) . ' />';
            echo '<span>' . esc_html($label) . '</span>';
            echo '</label>';
        }
        if ($children !== []) {
            echo '<ul>';
            foreach ($children as $child) {
                if (is_array($child)) {
                    self::render_sitemap_item($field, $child, $hidden);
                }
            }
            echo '</ul>';
        }
        echo '</li>';
    }

    /**
     * Agregar perfiles como fichas, sin casillas de roles.
     *
     * @param array<string, string> $role_labels
     * @param string[] $picked
     */
    public static function render_role_adder(string $input_name, array $picked, array $role_labels, bool $inactive): void {
        $catalog = wp_json_encode($role_labels, JSON_UNESCAPED_UNICODE);
        if (!is_string($catalog)) {
            $catalog = '{}';
        }
        echo '<div class="mdcms-role-add" data-mdcms-role-add data-input-name="' . esc_attr($input_name) . '" data-roles="' . esc_attr($catalog) . '"' . ($inactive ? ' hidden' : '') . '>';
        echo '<div class="mdcms-role-add__chips" data-mdcms-chips>';
        foreach ($picked as $role_slug) {
            $role_slug = (string) $role_slug;
            if ($role_slug === '' || !isset($role_labels[$role_slug])) {
                continue;
            }
            echo '<span class="mdcms-chip" data-mdcms-chip data-role="' . esc_attr($role_slug) . '">';
            echo '<span class="mdcms-chip__label">' . esc_html($role_labels[$role_slug]) . '</span>';
            echo '<button type="button" class="mdcms-chip__remove" data-mdcms-chip-remove aria-label="'
                . esc_attr(sprintf(/* translators: %s: role name */ __('Quitar %s', 'megadruid-cms'), $role_labels[$role_slug])) . '">×</button>';
            echo '<input type="hidden" name="' . esc_attr($input_name) . '" value="' . esc_attr($role_slug) . '" />';
            echo '</span>';
        }
        echo '</div>';
        echo '<div class="mdcms-role-add__bar">';
        echo '<label class="screen-reader-text">' . esc_html__('Agregar perfil', 'megadruid-cms') . '</label>';
        echo '<select class="mdcms-role-add__pick" data-mdcms-role-pick></select>';
        echo '<button type="button" class="button mdcms-role-add__btn" data-mdcms-role-add-btn>' . esc_html__('Agregar', 'megadruid-cms') . '</button>';
        echo '</div>';
        echo '</div>';
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
        $stored = get_option('mdcms_bar_inventory', null);
        if (!is_array($stored) || $stored === []) {
            $stored = get_transient('mdcms_bar_inventory');
        }
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
        $role = Access::primary_role();
        if ($role === '' || !in_array($role, self::hide_menu_roles(), true)) {
            return [];
        }

        return self::hidden_slug_list($key);
    }

    public static function request_matches_slug(string $slug, string $pagenow, string $page, string $post_type): bool {
        if (str_contains($slug, 'megadruid-cms')) {
            return false;
        }
        if ($post_type === '' && $pagenow === 'post.php') {
            $post_type = self::post_type_being_edited();
        }
        $targets = [$slug];
        if (!str_contains($slug, '::')) {
            foreach (self::submenu_child_slugs($slug) as $child) {
                $targets[] = $child;
            }
        }
        $match = false;
        foreach ($targets as $target) {
            if (self::slug_covers_screen($target, $pagenow, $page, $post_type)) {
                $match = true;
                break;
            }
        }

        return $match;
    }

    /**
     * Si el mapa no se dibujó, no se pisa lo ya guardado (el inventario de la barra puede no estar).
     *
     * @param array<string, mixed> $posted
     * @return string[]
     */
    private static function posted_slug_list(string $field, string $flag, array $posted): array {
        $listed = isset($_POST[$flag]) && (string) wp_unslash($_POST[$flag]) === '1';
        if (!$listed) {
            return self::hidden_slug_list($field);
        }
        $values = $posted[$field] ?? [];

        return self::decode_slug_list(is_array($values) ? $values : []);
    }

    /**
     * @return string[]
     */
    private static function submenu_child_slugs(string $parent): array {
        global $submenu;
        if (!is_array($submenu) || !isset($submenu[$parent]) || !is_array($submenu[$parent])) {
            return [];
        }
        $out = [];
        foreach ($submenu[$parent] as $child) {
            if (!is_array($child)) {
                continue;
            }
            $slug = self::clean_slug((string) ($child[2] ?? ''));
            if ($slug === '' || str_contains($slug, 'megadruid-cms')) {
                continue;
            }
            $out[] = $slug;
        }

        return array_values(array_unique($out));
    }

    private static function post_type_being_edited(): string {
        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
        if ($post_id <= 0) {
            return '';
        }
        $type = get_post_type($post_id);

        return is_string($type) ? $type : '';
    }

    private static function slug_covers_screen(string $slug, string $pagenow, string $page, string $post_type): bool {
        if (str_contains($slug, 'megadruid-cms')) {
            return false;
        }
        $item = $slug;
        if (str_contains($slug, '::')) {
            [, $item] = explode('::', $slug, 2);
        }
        $item = self::clean_slug($item);
        if ($item === '') {
            return false;
        }
        $file = $item;
        $args = [];
        if (str_contains($item, '?')) {
            [$file, $query] = explode('?', $item, 2);
            $parsed = [];
            parse_str($query, $parsed);
            if (is_array($parsed)) {
                $args = $parsed;
            }
        }
        if ($page !== '') {
            if ($item === $page || $file === $page) {
                return true;
            }
            if (isset($args['page']) && (string) $args['page'] === $page) {
                return true;
            }
        }
        if ($pagenow !== '' && $file === $pagenow && isset($args['taxonomy'])) {
            $tax = isset($_GET['taxonomy']) ? sanitize_key((string) wp_unslash($_GET['taxonomy'])) : '';

            return $tax !== '' && $tax === sanitize_key((string) $args['taxonomy']);
        }
        $menu_type = isset($args['post_type']) ? sanitize_key((string) $args['post_type']) : '';
        if ($menu_type === '' && in_array($file, ['edit.php', 'post-new.php'], true) && !str_contains($item, '?')) {
            $menu_type = 'post';
        }
        if ($menu_type !== '' && in_array($file, ['edit.php', 'post.php', 'post-new.php'], true) && in_array($pagenow, ['edit.php', 'post.php', 'post-new.php'], true)) {
            $current = $post_type !== '' ? $post_type : 'post';

            return $current === $menu_type;
        }
        if ($pagenow === '' || $file !== $pagenow) {
            return false;
        }
        if (isset($args['page'])) {
            return $page !== '' && $page === (string) $args['page'];
        }

        return !in_array($file, ['edit.php', 'post-new.php'], true);
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
