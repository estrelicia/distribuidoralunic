<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Escritorio: caja Megadruid, título y paneles (nativos y de otros plugins).
 */
final class Dashboard {

    public const CATALOG_OPTION = 'mdcms_dashboard_panels';

    public const PANELS = [
        'dashboard_right_now' => 'De un vistazo',
        'dashboard_activity' => 'Actividad',
        'dashboard_recent_comments' => 'Comentarios recientes',
        'dashboard_quick_press' => 'Borrador rápido',
        'dashboard_primary' => 'Noticias y eventos',
    ];

    public function register(): void {
        add_action('wp_dashboard_setup', [$this, 'setup'], 999);
        add_action('do_meta_boxes', [$this, 'late_catalog'], 9999, 3);
        add_filter('get_user_option_meta-box-order_dashboard', [$this, 'pin_shortcuts_first']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('admin_footer', [$this, 'dashboard_title_script']);
    }

    public function setup(): void {
        $this->add_shortcuts_widget();
        $this->remember_panels();
        $this->hide_registered_panels();
    }

    /**
     * @param mixed $screen
     * @param mixed $context
     * @param mixed $data
     */
    public function late_catalog($screen, $context, $data): void {
        unset($context, $data);
        $id = is_object($screen) ? (string) ($screen->id ?? '') : (string) $screen;
        if ($id !== 'dashboard') {
            return;
        }
        $this->remember_panels();
        $this->hide_registered_panels();
    }

    private function add_shortcuts_widget(): void {
        wp_add_dashboard_widget(
            'mdcms_shortcuts',
            __('Megadruid accesos directos', 'megadruid-cms'),
            [$this, 'render_shortcuts_panel']
        );
        global $wp_meta_boxes;
        if (!isset($wp_meta_boxes['dashboard']['normal']['core']['mdcms_shortcuts'])) {
            return;
        }
        $widget = $wp_meta_boxes['dashboard']['normal']['core']['mdcms_shortcuts'];
        unset($wp_meta_boxes['dashboard']['normal']['core']['mdcms_shortcuts']);
        $wp_meta_boxes['dashboard']['normal']['core'] = array_merge(
            ['mdcms_shortcuts' => $widget],
            $wp_meta_boxes['dashboard']['normal']['core']
        );
    }

    /**
     * @param mixed $order
     * @return mixed
     */
    public function pin_shortcuts_first($order) {
        if (!is_array($order)) {
            return $order;
        }
        $id = 'mdcms_shortcuts';
        foreach (['normal', 'side', 'column3', 'column4'] as $column) {
            if (empty($order[$column]) || !is_string($order[$column])) {
                continue;
            }
            $ids = array_values(array_filter(explode(',', $order[$column])));
            $order[$column] = implode(',', array_values(array_diff($ids, [$id])));
        }
        $normal = empty($order['normal']) ? [] : array_values(array_filter(explode(',', (string) $order['normal'])));
        array_unshift($normal, $id);
        $order['normal'] = implode(',', $normal);

        return $order;
    }

    public function render_shortcuts_panel(): void {
        $portal = Branding::PORTAL_URL;
        $mail = Branding::MAIL;
        $wa = Branding::WHATSAPP_DISPLAY;
        $wa_url = Branding::WHATSAPP_URL;
        $web = 'megadruid.com';
        ?>
        <div class="mdcms-shortcuts">
            <p class="mdcms-shortcuts__brand">
                <a href="<?php echo esc_url(Branding::HOME_URL); ?>" target="_blank" rel="noopener noreferrer">
                    <img
                        class="mdcms-shortcuts__logo"
                        src="<?php echo esc_url(Brand_Pack::logo_url()); ?>"
                        alt="<?php echo esc_attr(Branding::ALT); ?>"
                    />
                </a>
            </p>
            <p class="mdcms-shortcuts__portal">
                <span><?php esc_html_e('Portal del cliente:', 'megadruid-cms'); ?></span>
                <a href="<?php echo esc_url($portal); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($portal); ?></a>
            </p>
            <p class="mdcms-shortcuts__contacts">
                <a href="<?php echo esc_url('mailto:' . $mail); ?>">
                    <span class="dashicons dashicons-email" aria-hidden="true"></span>
                    <?php echo esc_html($mail); ?>
                </a>
                <a href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer">
                    <span class="dashicons dashicons-whatsapp" aria-hidden="true"></span>
                    <?php echo esc_html($wa); ?>
                </a>
                <a href="<?php echo esc_url(Branding::HOME_URL); ?>" target="_blank" rel="noopener noreferrer">
                    <span class="dashicons dashicons-admin-site-alt3" aria-hidden="true"></span>
                    <?php echo esc_html($web); ?>
                </a>
            </p>
        </div>
        <?php
    }

    public function assets(string $hook): void {
        if ($hook !== 'index.php') {
            return;
        }
        $css = '#dashboard-widgets .empty-container{border:0;min-height:0;height:0;background:transparent;}'
            . '#mdcms_shortcuts .mdcms-shortcuts__brand{margin:0 0 12px;}'
            . '#mdcms_shortcuts .mdcms-shortcuts__logo{display:block;height:56px;width:auto;max-width:100%;image-rendering:pixelated;image-rendering:crisp-edges;}'
            . '#mdcms_shortcuts .mdcms-shortcuts__portal{margin:0 0 12px;font-size:14px;}'
            . '#mdcms_shortcuts .mdcms-shortcuts__portal span{margin-right:6px;}'
            . '#mdcms_shortcuts .mdcms-shortcuts__contacts{display:flex;flex-wrap:wrap;gap:8px 18px;margin:0;}'
            . '#mdcms_shortcuts .mdcms-shortcuts__contacts a{display:inline-flex;align-items:center;gap:6px;text-decoration:none;}'
            . '#mdcms_shortcuts .mdcms-shortcuts__contacts .dashicons{font-size:18px;width:18px;height:18px;line-height:1;}';
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

    /**
     * @param array<string, mixed> $posted
     * @return array<string, mixed>
     */
    public static function fill_missing(array $posted): array {
        $map = isset($posted['dashboard_panel_roles']) && is_array($posted['dashboard_panel_roles'])
            ? $posted['dashboard_panel_roles']
            : [];
        $ids = array_merge(array_keys(self::panels_for_settings(false)), ['all']);
        foreach ($ids as $panel) {
            if (!array_key_exists($panel, $map)) {
                $map[$panel] = [];
            }
        }
        $posted['dashboard_panel_roles'] = $map;

        return $posted;
    }

    /**
     * @return array<string, string> id => título
     */
    public static function panels_for_settings(bool $bootstrap = true): array {
        $labels = self::PANELS;
        $live = $bootstrap ? self::bootstrap_catalog() : [];
        foreach (array_merge(self::stored_catalog(), $live) as $id => $title) {
            $id = sanitize_key((string) $id);
            if ($id === '') {
                continue;
            }
            $title = wp_strip_all_tags((string) $title);
            $labels[$id] = $title !== '' ? $title : $id;
        }
        $saved = Settings::get('dashboard_panel_roles', []);
        if (is_array($saved)) {
            foreach (array_keys($saved) as $id) {
                $id = sanitize_key((string) $id);
                if ($id === '' || $id === 'all' || isset($labels[$id])) {
                    continue;
                }
                $labels[$id] = $id;
            }
        }
        $first = [];
        if (isset($labels['mdcms_shortcuts'])) {
            $first['mdcms_shortcuts'] = $labels['mdcms_shortcuts'];
            unset($labels['mdcms_shortcuts']);
        }
        uasort($labels, static function (string $a, string $b): int {
            return strnatcasecmp($a, $b);
        });

        return $first + $labels;
    }

    public static function render_settings(): void {
        $map = Settings::get('dashboard_panel_roles', []);
        if (!is_array($map)) {
            $map = [];
        }
        $roles = wp_roles()->roles;
        $rows = self::panels_for_settings();
        Admin_Layout::open_card(
            __('Paneles del Escritorio', 'megadruid-cms'),
            __('Cada fila es un panel que existe ahora (nativos y de otros plugins). El panel se oculta para los roles marcados. Quien administra la marca lo sigue viendo.', 'megadruid-cms'),
            'dashicons-screenoptions',
            true
        );
        $known = self::PANELS;
        $known['mdcms_shortcuts'] = true;
        $extra = array_diff_key($rows, $known);
        if ($extra === []) {
            echo '<p class="description">' . esc_html__('Si falta un panel de otro plugin, abrí el Escritorio de WordPress una vez y volvé a esta pantalla.', 'megadruid-cms') . '</p>';
        }
        echo '<table class="form-table" role="presentation">';
        $rows['all'] = __('Ocultar todos', 'megadruid-cms');
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
        Admin_Layout::close_card();
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

    /**
     * @return array<string, string>
     */
    private static function collect_panels(): array {
        global $wp_meta_boxes;
        $out = [];
        if (empty($wp_meta_boxes['dashboard']) || !is_array($wp_meta_boxes['dashboard'])) {
            return $out;
        }
        foreach ($wp_meta_boxes['dashboard'] as $priorities) {
            if (!is_array($priorities)) {
                continue;
            }
            foreach ($priorities as $boxes) {
                if (!is_array($boxes)) {
                    continue;
                }
                foreach ($boxes as $id => $box) {
                    if (!is_array($box)) {
                        continue;
                    }
                    $id = sanitize_key((string) $id);
                    if ($id === '') {
                        continue;
                    }
                    $title = isset($box['title']) ? wp_strip_all_tags((string) $box['title']) : $id;
                    $out[$id] = $title !== '' ? $title : $id;
                }
            }
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private static function stored_catalog(): array {
        $stored = get_option(self::CATALOG_OPTION, []);
        if (!is_array($stored)) {
            return [];
        }
        $clean = [];
        foreach ($stored as $id => $title) {
            $id = sanitize_key((string) $id);
            if ($id === '') {
                continue;
            }
            $title = wp_strip_all_tags((string) $title);
            $clean[$id] = $title !== '' ? $title : $id;
        }

        return $clean;
    }

    /**
     * @param array<string, string> $panels
     */
    private static function store_catalog(array $panels): void {
        if ($panels === []) {
            return;
        }
        $merged = array_merge(self::stored_catalog(), $panels);
        update_option(self::CATALOG_OPTION, $merged, false);
    }

    /**
     * @return array<string, string>
     */
    private static function bootstrap_catalog(): array {
        $existing = self::collect_panels();
        if ($existing !== []) {
            self::store_catalog($existing);
        }

        return $existing;
    }

    private function remember_panels(): void {
        self::store_catalog(self::collect_panels());
    }

    private function hide_registered_panels(): void {
        $ids = array_unique(array_merge(
            array_keys(self::collect_panels()),
            array_keys(self::stored_catalog()),
            array_keys(self::PANELS)
        ));
        foreach ($ids as $id) {
            if (!$this->hides_panel($id)) {
                continue;
            }
            foreach (['normal', 'side', 'column3', 'column4'] as $context) {
                remove_meta_box($id, 'dashboard', $context);
            }
        }
    }
}
