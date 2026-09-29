<?php

namespace Distribuidora_Lunic\Modules\MegaMenu;

defined('ABSPATH') || exit;

class Menu {

    public const OPTION = 'lunic_mega_menu';

    private const EXCLUDED_TERM = 15;

    /**
     * @return array<int, array<int, int>>
     */
    public static function columns(): array {
        $saved = get_option(self::OPTION);
        if (is_array($saved) && isset($saved['columns']) && is_array($saved['columns'])) {
            return self::normalize($saved['columns']);
        }
        return self::from_theme_menus();
    }

    /**
     * @param array<int, mixed> $columns
     * @return array<int, array<int, int>>
     */
    public static function normalize(array $columns): array {
        $clean = [[], [], []];
        $seen = [];
        foreach ([0, 1, 2] as $index) {
            $ids = isset($columns[$index]) && is_array($columns[$index]) ? $columns[$index] : [];
            foreach ($ids as $id) {
                $id = absint($id);
                if ($id < 1 || $id === self::EXCLUDED_TERM || isset($seen[$id])) {
                    continue;
                }
                $term = get_term($id, 'product_cat');
                if (!$term || is_wp_error($term) || $term->slug === 'uncategorized') {
                    continue;
                }
                $seen[$id] = true;
                $clean[$index][] = $id;
            }
        }
        return $clean;
    }

    /**
     * @return array<int, \WP_Term>
     */
    public static function available_terms(): array {
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'exclude' => [self::EXCLUDED_TERM],
        ]);
        if (!is_array($terms)) {
            return [];
        }
        $terms = array_values(array_filter($terms, static function ($term) {
            return $term instanceof \WP_Term && $term->slug !== 'uncategorized';
        }));
        usort($terms, static function ($a, $b) {
            return strcasecmp($a->name, $b->name);
        });
        return $terms;
    }

    public static function desktop(): string {
        $html = '<div class="lunic-mega__cols">';
        foreach (self::columns() as $index => $ids) {
            $html .= '<nav aria-label="' . esc_attr(sprintf('Categorías %02d', $index + 1)) . '">';
            $html .= '<ul class="lunic-mega__list">';
            $html .= self::links($ids);
            $html .= '</ul></nav>';
        }
        $html .= '</div>';
        return $html;
    }

    public static function mobile(): string {
        $html = '';
        foreach (self::columns() as $index => $ids) {
            $html .= '<nav aria-label="' . esc_attr(sprintf('Categorías %02d', $index + 1)) . '">';
            $html .= '<ul class="lunic-menu">';
            $html .= self::links($ids);
            $html .= '</ul></nav>';
        }
        return $html;
    }

    /**
     * @param array<int, int> $ids
     */
    private static function links(array $ids): string {
        $html = '';
        foreach ($ids as $id) {
            $term = get_term($id, 'product_cat');
            if (!$term || is_wp_error($term)) {
                continue;
            }
            $link = get_term_link($term);
            if (is_wp_error($link)) {
                continue;
            }
            $html .= '<li><a href="' . esc_url($link) . '">' . esc_html($term->name) . '</a></li>';
        }
        return $html;
    }

    /**
     * @return array<int, array<int, int>>
     */
    private static function from_theme_menus(): array {
        $locations = get_nav_menu_locations();
        $keys = ['categorias-01', 'categorias-02', 'categorias-03'];
        $fallback = [113, 114, 115];
        $columns = [[], [], []];
        foreach ($keys as $index => $key) {
            $menu_id = isset($locations[$key]) ? (int) $locations[$key] : $fallback[$index];
            $items = $menu_id > 0 ? wp_get_nav_menu_items($menu_id) : false;
            if (!is_array($items)) {
                continue;
            }
            foreach ($items as $item) {
                if (($item->object ?? '') !== 'product_cat') {
                    continue;
                }
                $columns[$index][] = (int) $item->object_id;
            }
        }
        return self::normalize($columns);
    }
}
