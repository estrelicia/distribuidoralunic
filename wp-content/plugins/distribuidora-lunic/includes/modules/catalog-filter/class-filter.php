<?php

namespace Distribuidora_Lunic\Modules\CatalogFilter;

defined('ABSPATH') || exit;

class Filter {

    private static bool $printed = false;

    public function register(): void {
        add_action('template_redirect', [$this, 'redirect_legacy_param']);
        add_action('pre_get_posts', [$this, 'apply_query']);
        add_action('woocommerce_before_shop_loop', [$this, 'print_on_shop'], 4);
        add_action('woocommerce_no_products_found', [$this, 'empty_notice'], 9);
        add_shortcode('lunic_filter', [$this, 'render']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_assets(): void {
        wp_register_style('lunic-filter', DISTRIBUIDORA_LUNIC_URL . 'assets/css/filter.css', [], DISTRIBUIDORA_LUNIC_VERSION);
        wp_register_script('lunic-filter', DISTRIBUIDORA_LUNIC_URL . 'assets/js/filter.js', [], DISTRIBUIDORA_LUNIC_VERSION, true);
    }

    public function redirect_legacy_param(): void {
        if (!isset($_GET['wpf_filter_cat_list_0'])) {
            return;
        }
        $term = absint($_GET['wpf_filter_cat_list_0']);
        $url = remove_query_arg(['wpf_filter_cat_list_0', 'wpf_fbv']);
        if ($term && $term !== Module::EXCLUDE_TERM) {
            $url = add_query_arg(Module::QUERY_ARG, $term, $url);
        }
        wp_safe_redirect($url);
        exit;
    }

    public function apply_query(\WP_Query $query): void {
        if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive('product')) {
            return;
        }
        $term = $this->active_term();
        if (!$term) {
            return;
        }
        $tax = (array) $query->get('tax_query');
        $tax[] = [
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => [$term],
            'include_children' => true,
        ];
        $query->set('tax_query', $tax);
    }

    public function print_on_shop(): void {
        if (is_shop()) {
            echo $this->markup();
        }
    }

    public function render(): string {
        return $this->markup();
    }

    public function empty_notice(): void {
        if (!$this->active_term()) {
            return;
        }
        remove_action('woocommerce_no_products_found', 'wc_no_products_found', 10);
        echo '<p class="woocommerce-info">' . esc_html__('No se encontraron productos', 'distribuidora-lunic') . ' <a href="' . esc_url(wc_get_page_permalink('shop')) . '">' . esc_html__('Volver a la tienda', 'distribuidora-lunic') . '</a></p>';
    }

    private function markup(): string {
        if (self::$printed || !function_exists('is_shop')) {
            return '';
        }
        self::$printed = true;
        wp_enqueue_style('lunic-filter');
        wp_enqueue_script('lunic-filter');
        $active = $this->active_term();
        wp_localize_script('lunic-filter', 'lunicFilter', [
            'shop' => esc_url_raw(wc_get_page_permalink('shop')),
            'arg' => Module::QUERY_ARG,
            'active' => $active,
        ]);
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'exclude' => [Module::EXCLUDE_TERM],
        ]);
        if (is_wp_error($terms)) {
            return '';
        }
        $tree = $this->tree($terms);
        ob_start();
        ?>
        <aside class="lunic-filter" data-active="<?php echo esc_attr((string) $active); ?>">
            <button type="button" class="lunic-filter__toggle" aria-expanded="false">
                <?php esc_html_e('Categorías', 'distribuidora-lunic'); ?>
                <?php if ($active) : ?><span class="lunic-filter__badge">1</span><?php endif; ?>
            </button>
            <div class="lunic-filter__panel">
                <ul class="lunic-filter__list">
                    <li>
                        <a class="<?php echo $active ? '' : 'is-active'; ?>" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" data-term="0"><?php esc_html_e('Todas', 'distribuidora-lunic'); ?></a>
                    </li>
                    <?php $this->render_branch($tree, 0, $active); ?>
                </ul>
            </div>
        </aside>
        <?php
        return (string) ob_get_clean();
    }

    private function render_branch(array $tree, int $parent, int $active): void {
        if (empty($tree[$parent])) {
            return;
        }
        foreach ($tree[$parent] as $term) {
            $class = ((int) $term->term_id === $active) ? 'is-active' : '';
            echo '<li>';
            echo '<a class="' . esc_attr($class) . '" data-term="' . esc_attr((string) $term->term_id) . '" href="' . esc_url(add_query_arg(Module::QUERY_ARG, $term->term_id, wc_get_page_permalink('shop'))) . '">';
            echo esc_html($term->name) . ' (' . (int) $term->count . ')';
            echo '</a>';
            echo '<ul>';
            $this->render_branch($tree, (int) $term->term_id, $active);
            echo '</ul>';
            echo '</li>';
        }
    }

    /** @param \WP_Term[] $terms */
    private function tree(array $terms): array {
        $tree = [];
        foreach ($terms as $term) {
            if ((int) $term->parent === Module::EXCLUDE_TERM) {
                continue;
            }
            $tree[(int) $term->parent][] = $term;
        }
        return $tree;
    }

    private function active_term(): int {
        $term = isset($_GET[Module::QUERY_ARG]) ? absint($_GET[Module::QUERY_ARG]) : 0;
        return $term === Module::EXCLUDE_TERM ? 0 : $term;
    }
}
