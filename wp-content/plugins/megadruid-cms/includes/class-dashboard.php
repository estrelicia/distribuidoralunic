<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Escritorio: paneles nativos, bienvenida y RSS.
 * Elementor y Beaver solo se renderizan dentro del escritorio.
 */
final class Dashboard {

    public const PANELS = [
        'dashboard_right_now' => 'De un vistazo',
        'dashboard_activity' => 'Actividad',
        'dashboard_recent_comments' => 'Comentarios recientes',
        'dashboard_quick_press' => 'Borrador rápido',
        'dashboard_primary' => 'Noticias y eventos',
    ];

    public function register(): void {
        add_action('wp_dashboard_setup', [$this, 'setup'], 999);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('admin_footer', [$this, 'dashboard_title_script']);
        add_action('wp_ajax_mdcms_search_pages', [$this, 'search_pages']);
        add_action('wp', [$this, 'hide_welcome_admin_bar']);
    }

    public function setup(): void {
        $this->hide_native_panels();
        $this->add_welcome_widgets();
        $this->add_rss_widget();
    }

    public function assets(string $hook): void {
        if ($hook !== 'index.php') {
            return;
        }
        $css = '.mdcms-welcome{margin:16px 0;padding:16px 20px;background:#fff;border:1px solid #c3c4c7;box-shadow:0 1px 1px rgba(0,0,0,.04);}'
            . '.mdcms-welcome iframe{width:100%;min-height:360px;border:0;}'
            . '#dashboard-widgets .empty-container{border:0;min-height:0;height:0;background:transparent;}';
        wp_register_style('mdcms-dashboard', false, [], MDCMS_VERSION);
        wp_enqueue_style('mdcms-dashboard');
        wp_add_inline_style('mdcms-dashboard', $css);
    }

    public function dashboard_title_script(): void {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->base !== 'dashboard') {
            return;
        }
        $title = (string) Settings::get('dashboard_title', '');
        if ($title === '') {
            return;
        }
        echo '<script>document.addEventListener("DOMContentLoaded",function(){var node=document.querySelector("#wpbody-content .wrap h1");if(node){node.textContent='
            . wp_json_encode($title) . ';}});</script>';
    }

    public function search_pages(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'forbidden'], 403);
        }
        check_ajax_referer('mdcms_search_pages', 'nonce');
        $term = isset($_GET['term']) ? sanitize_text_field(wp_unslash((string) $_GET['term'])) : '';
        $kind = isset($_GET['kind']) ? sanitize_key(wp_unslash((string) $_GET['kind'])) : 'page';
        $post_type = 'page';
        if ($kind === 'elementor' && class_exists('\Elementor\Plugin')) {
            $post_type = 'elementor_library';
        } elseif ($kind === 'beaver' && class_exists('FLBuilder')) {
            $post_type = 'fl-builder-template';
        } elseif ($kind !== 'page') {
            wp_send_json_success([]);
        }
        $query = new \WP_Query([
            'post_type' => $post_type,
            'post_status' => 'publish',
            's' => $term,
            'posts_per_page' => 10,
            'no_found_rows' => true,
        ]);
        $results = [];
        foreach ($query->posts as $post) {
            if (!$post instanceof \WP_Post) {
                continue;
            }
            $results[] = [
                'id' => $post->ID,
                'title' => $post->post_title !== '' ? $post->post_title : ('#' . $post->ID),
            ];
        }
        wp_send_json_success($results);
    }

    public function hide_welcome_admin_bar(): void {
        if (!isset($_GET['mdcms_welcome']) || (string) $_GET['mdcms_welcome'] !== '1' || !is_user_logged_in()) {
            return;
        }
        $id = (int) get_queried_object_id();
        if (!in_array($id, $this->welcome_page_ids(), true)) {
            return;
        }
        add_filter('show_admin_bar', '__return_false');
    }

    /**
     * @param array<string, mixed> $posted
     * @return array<string, mixed>
     */
    public static function fill_missing(array $posted): array {
        $panels = isset($posted['welcome_panels']) && is_array($posted['welcome_panels'])
            ? $posted['welcome_panels']
            : [];
        for ($index = 0; $index < 2; $index++) {
            $panel = isset($panels[$index]) && is_array($panels[$index]) ? $panels[$index] : [];
            $panel['enabled'] = $panel['enabled'] ?? 0;
            $panel['show_title'] = $panel['show_title'] ?? 0;
            $panel['roles'] = $panel['roles'] ?? [];
            $panels[$index] = $panel;
        }
        $posted['welcome_panels'] = $panels;
        $map = isset($posted['dashboard_panel_roles']) && is_array($posted['dashboard_panel_roles'])
            ? $posted['dashboard_panel_roles']
            : [];
        foreach (array_merge(array_keys(self::PANELS), ['all']) as $panel) {
            if (!array_key_exists($panel, $map)) {
                $map[$panel] = [];
            }
        }
        $posted['dashboard_panel_roles'] = $map;
        $posted['rss_enabled'] = $posted['rss_enabled'] ?? 0;
        $posted['rss_roles'] = $posted['rss_roles'] ?? [];

        return $posted;
    }

    public static function render_settings(): void {
        $title = (string) Settings::get('dashboard_title', '');
        $map = Settings::get('dashboard_panel_roles', []);
        if (!is_array($map)) {
            $map = [];
        }
        $roles = wp_roles()->roles;
        echo '<h2 class="mdcms-section-title">' . esc_html__('Título', 'megadruid-cms') . '</h2>';
        echo '<table class="form-table" role="presentation"><tr><th scope="row"><label for="mdcms_dashboard_title">'
            . esc_html__('Título del escritorio', 'megadruid-cms') . '</label></th><td>';
        echo '<input type="text" class="regular-text" id="mdcms_dashboard_title" name="mdcms[dashboard_title]" value="'
            . esc_attr($title) . '" /></td></tr></table>';

        echo '<h2 class="mdcms-section-title">' . esc_html__('Paneles nativos', 'megadruid-cms') . '</h2>';
        echo '<p class="description">' . esc_html__('El panel se oculta para los roles marcados. Quien administra la marca lo sigue viendo.', 'megadruid-cms') . '</p>';
        echo '<table class="form-table" role="presentation">';
        $rows = self::PANELS + ['all' => __('Ocultar todos', 'megadruid-cms')];
        foreach ($rows as $id => $label) {
            $marked = isset($map[$id]) && is_array($map[$id]) ? $map[$id] : [];
            echo '<tr><th scope="row">' . esc_html((string) $label) . '</th><td>';
            foreach ($roles as $slug => $role) {
                $slug = (string) $slug;
                $input = 'mdcms_panel_' . $id . '_' . $slug;
                echo '<label class="mdcms-role" for="' . esc_attr($input) . '"><input type="checkbox" id="'
                    . esc_attr($input) . '" name="mdcms[dashboard_panel_roles][' . esc_attr($id) . '][]" value="'
                    . esc_attr($slug) . '" ' . checked(in_array($slug, $marked, true), true, false) . ' /> '
                    . esc_html(translate_user_role((string) ($role['name'] ?? $slug))) . '</label>';
            }
            echo '</td></tr>';
        }
        echo '</table>';

        $panels = Settings::get('welcome_panels', []);
        if (!is_array($panels)) {
            $panels = [];
        }
        echo '<h2 class="mdcms-section-title">' . esc_html__('Paneles de bienvenida', 'megadruid-cms') . '</h2>';
        for ($index = 0; $index < 2; $index++) {
            $panel = isset($panels[$index]) && is_array($panels[$index]) ? $panels[$index] : [];
            self::render_welcome_fields($index, $panel, $roles);
        }
        self::render_rss_fields($roles);
    }

    /**
     * @param array<string, mixed> $panel
     * @param array<string, mixed> $roles
     */
    private static function render_welcome_fields(int $index, array $panel, array $roles): void {
        $base = 'mdcms[welcome_panels][' . $index . ']';
        $type = (string) ($panel['type'] ?? 'html');
        echo '<h3>' . esc_html(sprintf(__('Panel %d', 'megadruid-cms'), $index + 1)) . '</h3>';
        echo '<table class="form-table" role="presentation">';
        echo '<tr><th scope="row">' . esc_html__('Activo', 'megadruid-cms') . '</th><td><label><input type="checkbox" name="'
            . esc_attr($base) . '[enabled]" value="1" ' . checked(!empty($panel['enabled']), true, false) . ' /> '
            . esc_html__('Mostrar este panel', 'megadruid-cms') . '</label></td></tr>';
        echo '<tr><th scope="row">' . esc_html__('Visible para', 'megadruid-cms') . '</th><td>';
        $marked = isset($panel['roles']) && is_array($panel['roles']) ? $panel['roles'] : [];
        foreach ($roles as $slug => $role) {
            $slug = (string) $slug;
            echo '<label class="mdcms-role"><input type="checkbox" name="' . esc_attr($base) . '[roles][]" value="'
                . esc_attr($slug) . '" ' . checked(in_array($slug, $marked, true), true, false) . ' /> '
                . esc_html(translate_user_role((string) ($role['name'] ?? $slug))) . '</label>';
        }
        echo '</td></tr>';
        echo '<tr><th scope="row"><label>' . esc_html__('Título', 'megadruid-cms') . '</label></th><td>';
        echo '<input type="text" class="regular-text" name="' . esc_attr($base) . '[title]" value="'
            . esc_attr((string) ($panel['title'] ?? '')) . '" />';
        echo '<label><input type="checkbox" name="' . esc_attr($base) . '[show_title]" value="1" '
            . checked(!empty($panel['show_title']), true, false) . ' /> ' . esc_html__('Mostrar título', 'megadruid-cms') . '</label>';
        echo '</td></tr>';
        echo '<tr><th scope="row">' . esc_html__('Tipo', 'megadruid-cms') . '</th><td><select name="' . esc_attr($base) . '[type]">';
        $types = [
            'html' => __('HTML', 'megadruid-cms'),
            'page' => __('Página de WordPress', 'megadruid-cms'),
        ];
        if (class_exists('\Elementor\Plugin')) {
            $types['elementor'] = __('Plantilla de Elementor', 'megadruid-cms');
        }
        if (class_exists('FLBuilder')) {
            $types['beaver'] = __('Plantilla de Beaver Builder', 'megadruid-cms');
        }
        foreach ($types as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($type, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></td></tr>';
        echo '<tr><th scope="row">' . esc_html__('HTML', 'megadruid-cms') . '</th><td><textarea class="large-text code" rows="4" name="'
            . esc_attr($base) . '[html]">' . esc_textarea((string) ($panel['html'] ?? '')) . '</textarea></td></tr>';
        self::render_id_search($base, 'page_id', 'page', __('Página', 'megadruid-cms'), (int) ($panel['page_id'] ?? 0), $index);
        if (isset($types['elementor'])) {
            self::render_id_search($base, 'elementor_id', 'elementor', __('Plantilla Elementor', 'megadruid-cms'), (int) ($panel['elementor_id'] ?? 0), $index);
        }
        if (isset($types['beaver'])) {
            self::render_id_search($base, 'beaver_id', 'beaver', __('Plantilla Beaver', 'megadruid-cms'), (int) ($panel['beaver_id'] ?? 0), $index);
        }
        echo '</table>';
    }

    private static function render_id_search(string $base, string $field, string $kind, string $label, int $value, int $index): void {
        $input = 'mdcms_welcome_' . $index . '_' . $field;
        echo '<tr><th scope="row"><label for="' . esc_attr($input) . '">' . esc_html($label) . '</label></th><td>';
        echo '<input type="search" class="regular-text" data-mdcms-search data-kind="' . esc_attr($kind) . '" data-target="'
            . esc_attr($input) . '" placeholder="' . esc_attr__('Buscar…', 'megadruid-cms') . '" />';
        echo '<ul class="mdcms-search-results" hidden></ul>';
        echo '<input type="number" class="small-text" min="0" id="' . esc_attr($input) . '" name="' . esc_attr($base . '[' . $field . ']')
            . '" value="' . esc_attr((string) $value) . '" />';
        echo '</td></tr>';
    }

    /**
     * @param array<string, mixed> $roles
     */
    private static function render_rss_fields(array $roles): void {
        echo '<h2 class="mdcms-section-title">' . esc_html__('RSS', 'megadruid-cms') . '</h2>';
        echo '<table class="form-table" role="presentation">';
        echo '<tr><th scope="row">' . esc_html__('Activo', 'megadruid-cms') . '</th><td><label><input type="checkbox" name="mdcms[rss_enabled]" value="1" '
            . checked((bool) Settings::get('rss_enabled', false), true, false) . ' /> ' . esc_html__('Mostrar panel RSS', 'megadruid-cms') . '</label></td></tr>';
        echo '<tr><th scope="row">' . esc_html__('Visible para', 'megadruid-cms') . '</th><td>';
        $marked = Settings::get('rss_roles', []);
        if (!is_array($marked)) {
            $marked = [];
        }
        foreach ($roles as $slug => $role) {
            $slug = (string) $slug;
            echo '<label class="mdcms-role"><input type="checkbox" name="mdcms[rss_roles][]" value="' . esc_attr($slug) . '" '
                . checked(in_array($slug, $marked, true), true, false) . ' /> '
                . esc_html(translate_user_role((string) ($role['name'] ?? $slug))) . '</label>';
        }
        echo '</td></tr>';
        echo '<tr><th scope="row"><label for="mdcms_rss_title">' . esc_html__('Título', 'megadruid-cms') . '</label></th><td><input type="text" class="regular-text" id="mdcms_rss_title" name="mdcms[rss_title]" value="'
            . esc_attr((string) Settings::get('rss_title', '')) . '" /></td></tr>';
        echo '<tr><th scope="row"><label for="mdcms_rss_logo">' . esc_html__('Logo (URL)', 'megadruid-cms') . '</label></th><td><input type="url" class="regular-text" id="mdcms_rss_logo" name="mdcms[rss_logo]" value="'
            . esc_url((string) Settings::get('rss_logo', '')) . '" /></td></tr>';
        echo '<tr><th scope="row"><label for="mdcms_rss_url">' . esc_html__('Feed', 'megadruid-cms') . '</label></th><td><input type="url" class="regular-text" id="mdcms_rss_url" name="mdcms[rss_url]" value="'
            . esc_url((string) Settings::get('rss_url', '')) . '" /></td></tr>';
        echo '<tr><th scope="row"><label for="mdcms_rss_count">' . esc_html__('Cantidad', 'megadruid-cms') . '</label></th><td><input type="number" class="small-text" min="1" max="10" id="mdcms_rss_count" name="mdcms[rss_count]" value="'
            . esc_attr((string) (int) Settings::get('rss_count', 5)) . '" /></td></tr>';
        $content = (string) Settings::get('rss_content', 'excerpt');
        echo '<tr><th scope="row"><label for="mdcms_rss_content">' . esc_html__('Contenido', 'megadruid-cms') . '</label></th><td><select id="mdcms_rss_content" name="mdcms[rss_content]">';
        echo '<option value="excerpt" ' . selected($content, 'excerpt', false) . '>' . esc_html__('Extracto', 'megadruid-cms') . '</option>';
        echo '<option value="content" ' . selected($content, 'content', false) . '>' . esc_html__('Contenido', 'megadruid-cms') . '</option>';
        echo '</select></td></tr>';
        echo '<tr><th scope="row"><label for="mdcms_rss_intro">' . esc_html__('Introducción', 'megadruid-cms') . '</label></th><td><textarea class="large-text" rows="3" id="mdcms_rss_intro" name="mdcms[rss_intro]">'
            . esc_textarea((string) Settings::get('rss_intro', '')) . '</textarea></td></tr>';
        echo '</table>';
    }

    public function hides_panel(string $panel, ?\WP_User $user = null): bool {
        if (Access::manages_brand($user)) {
            return false;
        }
        $map = Settings::get('dashboard_panel_roles', []);
        if (!is_array($map)) {
            return false;
        }
        $role = Access::primary_role($user);
        $all = isset($map['all']) && is_array($map['all']) ? $map['all'] : [];
        $own = isset($map[$panel]) && is_array($map[$panel]) ? $map[$panel] : [];

        return in_array($role, $all, true) || in_array($role, $own, true);
    }

    private function hide_native_panels(): void {
        foreach (array_keys(self::PANELS) as $id) {
            if (!$this->hides_panel($id)) {
                continue;
            }
            remove_meta_box($id, 'dashboard', 'normal');
            remove_meta_box($id, 'dashboard', 'side');
        }
    }

    private function add_welcome_widgets(): void {
        $panels = Settings::get('welcome_panels', []);
        if (!is_array($panels)) {
            return;
        }
        foreach ($panels as $index => $panel) {
            if (!is_array($panel) || empty($panel['enabled']) || !$this->role_allowed($panel['roles'] ?? [])) {
                continue;
            }
            $title = !empty($panel['show_title']) ? (string) ($panel['title'] ?? '') : '';
            wp_add_dashboard_widget(
                'mdcms_welcome_' . (int) $index,
                $title !== '' ? $title : ' ',
                function () use ($panel): void {
                    $this->render_welcome($panel);
                }
            );
        }
    }

    /**
     * @param array<string, mixed> $panel
     */
    private function render_welcome(array $panel): void {
        echo '<div class="mdcms-welcome-body">';
        $type = (string) ($panel['type'] ?? 'html');
        if ($type === 'html') {
            echo wp_kses_post((string) ($panel['html'] ?? ''));
        } elseif ($type === 'page') {
            $this->render_page_frame((int) ($panel['page_id'] ?? 0));
        } elseif ($type === 'elementor') {
            $this->render_elementor((int) ($panel['elementor_id'] ?? 0));
        } elseif ($type === 'beaver') {
            $this->render_beaver((int) ($panel['beaver_id'] ?? 0));
        }
        echo '</div>';
    }

    private function render_page_frame(int $page_id): void {
        if ($page_id <= 0) {
            return;
        }
        $url = get_permalink($page_id);
        if (!is_string($url) || $url === '') {
            return;
        }
        $url = add_query_arg('mdcms_welcome', '1', $url);
        echo '<iframe title="' . esc_attr__('Contenido de bienvenida', 'megadruid-cms') . '" src="' . esc_url($url) . '"></iframe>';
    }

    private function render_elementor(int $id): void {
        if ($id <= 0 || !is_admin() || !class_exists('\Elementor\Plugin')) {
            return;
        }
        $elementor = \Elementor\Plugin::$instance;
        if (!is_object($elementor) || !isset($elementor->frontend)) {
            return;
        }
        $elementor->frontend->enqueue_styles();
        if (method_exists($elementor->frontend, 'get_builder_content_for_display')) {
            echo $elementor->frontend->get_builder_content_for_display($id);
            return;
        }
        if (method_exists($elementor->frontend, 'get_builder_content')) {
            echo $elementor->frontend->get_builder_content($id, true);
        }
    }

    private function render_beaver(int $id): void {
        if ($id <= 0 || !is_admin() || !class_exists('FLBuilder') || !method_exists('FLBuilder', 'render_content_by_id')) {
            return;
        }
        \FLBuilder::render_content_by_id($id);
    }

    private function add_rss_widget(): void {
        if (!Settings::get('rss_enabled') || !$this->role_allowed(Settings::get('rss_roles', []))) {
            return;
        }
        $title = (string) Settings::get('rss_title', '');
        wp_add_dashboard_widget(
            'mdcms_rss',
            $title !== '' ? $title : __('Noticias', 'megadruid-cms'),
            [$this, 'render_rss']
        );
    }

    public function render_rss(): void {
        $intro = (string) Settings::get('rss_intro', '');
        $logo = (string) Settings::get('rss_logo', '');
        if ($logo !== '') {
            echo '<p><img src="' . esc_url($logo) . '" alt="" style="max-height:40px;width:auto;" /></p>';
        }
        if ($intro !== '') {
            echo '<div class="mdcms-rss-intro">' . wp_kses_post($intro) . '</div>';
        }
        $url = (string) Settings::get('rss_url', '');
        $count = (int) Settings::get('rss_count', 5);
        $mode = (string) Settings::get('rss_content', 'excerpt');
        if ($url === '') {
            echo '<div class="notice notice-warning inline"><p>' . esc_html__('Falta la dirección del feed.', 'megadruid-cms') . '</p></div>';
            return;
        }
        $items = $this->rss_items($url, $count);
        echo $this->format_rss($items, $mode);
    }

    /**
     * @return array<int, array{title: string, link: string, excerpt: string, content: string}>|\WP_Error
     */
    public function rss_items(string $url, int $count) {
        $count = max(1, min(10, $count));
        $key = 'mdcms_rss_' . md5($url . '|' . $count);
        $cached = get_transient($key);
        if (is_array($cached)) {
            return $cached;
        }
        if (!function_exists('fetch_feed')) {
            require_once ABSPATH . WPINC . '/feed.php';
        }
        $feed = fetch_feed($url);
        if (is_wp_error($feed)) {
            return $feed;
        }
        $max = (int) $feed->get_item_quantity($count);
        $parsed = [];
        foreach ($feed->get_items(0, $max) as $item) {
            $parsed[] = [
                'title' => (string) $item->get_title(),
                'link' => (string) $item->get_permalink(),
                'excerpt' => wp_trim_words(wp_strip_all_tags((string) $item->get_description()), 40, '…'),
                'content' => (string) $item->get_content(),
            ];
            if (count($parsed) >= $count) {
                break;
            }
        }
        set_transient($key, $parsed, HOUR_IN_SECONDS);

        return $parsed;
    }

    /**
     * @param array<int, array{title: string, link: string, excerpt: string, content: string}>|\WP_Error $items
     */
    public function format_rss($items, string $mode): string {
        if (is_wp_error($items)) {
            return '<div class="notice notice-warning inline"><p>' . esc_html($items->get_error_message()) . '</p></div>';
        }
        if ($items === []) {
            return '<p>' . esc_html__('El feed no tiene entradas.', 'megadruid-cms') . '</p>';
        }
        $html = '<ul class="mdcms-rss">';
        foreach ($items as $item) {
            $html .= '<li><a href="' . esc_url($item['link']) . '" target="_blank" rel="noopener noreferrer"><strong>'
                . esc_html($item['title']) . '</strong></a>';
            $body = $mode === 'content' ? wp_kses_post($item['content']) : esc_html($item['excerpt']);
            if ($body !== '') {
                $html .= '<div>' . $body . '</div>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * @return int[]
     */
    private function welcome_page_ids(): array {
        $ids = [];
        $panels = Settings::get('welcome_panels', []);
        if (!is_array($panels)) {
            return [];
        }
        foreach ($panels as $panel) {
            if (is_array($panel) && !empty($panel['enabled']) && (int) ($panel['page_id'] ?? 0) > 0) {
                $ids[] = (int) $panel['page_id'];
            }
        }

        return $ids;
    }

    private function role_allowed(mixed $roles): bool {
        if (!is_array($roles) || $roles === []) {
            return false;
        }

        return in_array(Access::primary_role(), $roles, true);
    }
}
