<?php

namespace Distribuidora_Lunic\Modules\Search;

defined('ABSPATH') || exit;

class Shortcode {

    private static bool $printed = false;

    public function register(): void {
        add_shortcode('lunic_search', [$this, 'render']);
        add_filter('the_content', [$this, 'prepend_on_front'], 8);
        add_action('woocommerce_before_shop_loop', [$this, 'print_on_shop'], 5);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
    }

    public function register_assets(): void {
        wp_register_style('lunic-search', DISTRIBUIDORA_LUNIC_URL . 'assets/css/search.css', [], DISTRIBUIDORA_LUNIC_VERSION);
        wp_register_script('lunic-search', DISTRIBUIDORA_LUNIC_URL . 'assets/js/search.js', [], DISTRIBUIDORA_LUNIC_VERSION, true);
    }

    public function prepend_on_front(string $content): string {
        if (!is_front_page() || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        return $this->markup() . $content;
    }

    public function print_on_shop(): void {
        if (!is_shop()) {
            return;
        }
        echo $this->markup();
    }

    public function render(): string {
        return $this->markup();
    }

    private function markup(): string {
        if (self::$printed) {
            return '';
        }
        self::$printed = true;
        wp_enqueue_style('lunic-search');
        wp_enqueue_script('lunic-search');
        wp_localize_script('lunic-search', 'lunicSearch', [
            'endpoint' => esc_url_raw(rest_url('lunic/v1/search')),
            'results' => esc_url_raw(home_url('/?s=%s&post_type=product')),
        ]);
        ob_start();
        ?>
        <form class="lunic-search" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
            <label class="screen-reader-text" for="lunic-search-input"><?php esc_html_e('Buscar productos', 'distribuidora-lunic'); ?></label>
            <input id="lunic-search-input" class="lunic-search__input" type="search" name="s" autocomplete="off" placeholder="<?php esc_attr_e('Buscar productos', 'distribuidora-lunic'); ?>" />
            <input type="hidden" name="post_type" value="product" />
            <div class="lunic-search__panel" hidden></div>
        </form>
        <?php
        return (string) ob_get_clean();
    }
}
