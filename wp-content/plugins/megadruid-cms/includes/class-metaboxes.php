<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Oculta cajas del editor de entradas y páginas para roles marcados.
 */
final class Metaboxes {

    /**
     * @var array<string, array{id: string, screen: string, context: string}>
     */
    public const POST_BOXES = [
        'excerpt' => ['id' => 'postexcerpt', 'screen' => 'post', 'context' => 'normal'],
        'slug' => ['id' => 'slugdiv', 'screen' => 'post', 'context' => 'normal'],
        'tags' => ['id' => 'tagsdiv-post_tag', 'screen' => 'post', 'context' => 'side'],
        'author' => ['id' => 'authordiv', 'screen' => 'post', 'context' => 'normal'],
        'comments' => ['id' => 'commentsdiv', 'screen' => 'post', 'context' => 'normal'],
        'revisions' => ['id' => 'revisionsdiv', 'screen' => 'post', 'context' => 'normal'],
        'discussion' => ['id' => 'commentstatusdiv', 'screen' => 'post', 'context' => 'normal'],
        'categories' => ['id' => 'categorydiv', 'screen' => 'post', 'context' => 'side'],
        'custom_fields' => ['id' => 'postcustom', 'screen' => 'post', 'context' => 'normal'],
        'trackbacks' => ['id' => 'trackbacksdiv', 'screen' => 'post', 'context' => 'normal'],
    ];

    /**
     * @var array<string, array{id: string, screen: string, context: string}>
     */
    public const PAGE_BOXES = [
        'custom_fields' => ['id' => 'postcustom', 'screen' => 'page', 'context' => 'normal'],
        'author' => ['id' => 'authordiv', 'screen' => 'page', 'context' => 'normal'],
        'discussion' => ['id' => 'commentstatusdiv', 'screen' => 'page', 'context' => 'normal'],
        'revisions' => ['id' => 'revisionsdiv', 'screen' => 'page', 'context' => 'normal'],
        'page_attributes' => ['id' => 'pageparentdiv', 'screen' => 'page', 'context' => 'side'],
        'slug' => ['id' => 'slugdiv', 'screen' => 'page', 'context' => 'normal'],
    ];

    public function register(): void {
        add_action('add_meta_boxes', [$this, 'remove_boxes'], 9999);
    }

    public function remove_boxes(): void {
        if (Access::manages_brand()) {
            return;
        }
        $role = Access::primary_role();
        if ($role === '') {
            return;
        }
        $post_map = Settings::get('post_metabox_roles', []);
        $page_map = Settings::get('page_metabox_roles', []);
        if (!is_array($post_map)) {
            $post_map = [];
        }
        if (!is_array($page_map)) {
            $page_map = [];
        }
        foreach (self::POST_BOXES as $key => $box) {
            $roles = $post_map[$key] ?? [];
            if (is_array($roles) && in_array($role, $roles, true)) {
                remove_meta_box($box['id'], $box['screen'], $box['context']);
            }
        }
        foreach (self::PAGE_BOXES as $key => $box) {
            $roles = $page_map[$key] ?? [];
            if (is_array($roles) && in_array($role, $roles, true)) {
                remove_meta_box($box['id'], $box['screen'], $box['context']);
            }
        }
    }

    public static function render_settings(): void {
        $roles = wp_roles()->roles;
        $post_map = Settings::get('post_metabox_roles', []);
        $page_map = Settings::get('page_metabox_roles', []);
        if (!is_array($post_map)) {
            $post_map = [];
        }
        if (!is_array($page_map)) {
            $page_map = [];
        }
        $post_labels = [
            'excerpt' => __('Extracto', 'megadruid-cms'),
            'slug' => __('Slug', 'megadruid-cms'),
            'tags' => __('Etiquetas', 'megadruid-cms'),
            'author' => __('Autor', 'megadruid-cms'),
            'comments' => __('Comentarios', 'megadruid-cms'),
            'revisions' => __('Revisiones', 'megadruid-cms'),
            'discussion' => __('Debate', 'megadruid-cms'),
            'categories' => __('Categorías', 'megadruid-cms'),
            'custom_fields' => __('Campos personalizados', 'megadruid-cms'),
            'trackbacks' => __('Trackbacks', 'megadruid-cms'),
        ];
        $page_labels = [
            'custom_fields' => __('Campos personalizados', 'megadruid-cms'),
            'author' => __('Autor', 'megadruid-cms'),
            'discussion' => __('Debate', 'megadruid-cms'),
            'revisions' => __('Revisiones', 'megadruid-cms'),
            'page_attributes' => __('Atributos de página', 'megadruid-cms'),
            'slug' => __('Slug', 'megadruid-cms'),
        ];
        Admin_Layout::open_card(
            __('Cajas del editor', 'megadruid-cms'),
            __('Solo aplica a los roles marcados en cada fila. Quien administra la marca sigue viendo todas las cajas.', 'megadruid-cms'),
            'dashicons-welcome-write-blog'
        );
        echo '<table class="widefat striped mdcms-metabox-table"><thead><tr><th>' . esc_html__('Caja', 'megadruid-cms') . '</th><th>' . esc_html__('Ocultar para', 'megadruid-cms') . '</th></tr></thead><tbody>';
        foreach ($post_labels as $key => $label) {
            self::render_row('post_metabox_roles', $key, sprintf(__('Entrada: %s', 'megadruid-cms'), $label), $roles, $post_map[$key] ?? []);
        }
        foreach ($page_labels as $key => $label) {
            self::render_row('page_metabox_roles', $key, sprintf(__('Página: %s', 'megadruid-cms'), $label), $roles, $page_map[$key] ?? []);
        }
        echo '</tbody></table>';
        Admin_Layout::close_card();
    }

    /**
     * @param array<string, mixed> $roles
     * @param string[] $marked
     */
    private static function render_row(string $group, string $key, string $label, array $roles, array $marked): void {
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td>';
        foreach ($roles as $slug => $role) {
            $slug = (string) $slug;
            $id = 'mdcms_' . $group . '_' . $key . '_' . $slug;
            echo '<label class="mdcms-role" for="' . esc_attr($id) . '"><input type="checkbox" id="' . esc_attr($id) . '" name="mdcms[' . esc_attr($group) . '][' . esc_attr($key) . '][]" value="' . esc_attr($slug) . '" '
                . checked(in_array($slug, $marked, true), true, false) . ' /> ' . esc_html(translate_user_role((string) ($role['name'] ?? $slug))) . '</label>';
        }
        echo '</td></tr>';
    }

    /**
     * @param array<string, mixed> $posted
     * @return array<string, mixed>
     */
    public static function fill_missing(array $posted): array {
        $map_post = isset($posted['post_metabox_roles']) && is_array($posted['post_metabox_roles'])
            ? $posted['post_metabox_roles']
            : [];
        $map_page = isset($posted['page_metabox_roles']) && is_array($posted['page_metabox_roles'])
            ? $posted['page_metabox_roles']
            : [];
        foreach (array_keys(self::POST_BOXES) as $key) {
            if (!array_key_exists($key, $map_post)) {
                $map_post[$key] = [];
            }
        }
        foreach (array_keys(self::PAGE_BOXES) as $key) {
            if (!array_key_exists($key, $map_page)) {
                $map_page[$key] = [];
            }
        }
        $posted['post_metabox_roles'] = $map_post;
        $posted['page_metabox_roles'] = $map_page;

        return $posted;
    }
}
