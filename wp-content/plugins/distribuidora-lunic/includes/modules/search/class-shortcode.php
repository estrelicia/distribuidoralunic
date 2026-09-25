<?php

namespace Distribuidora_Lunic\Modules\Search;

defined('ABSPATH') || exit;

class Shortcode {

    private static bool $printed = false;

    public function register(): void {
        add_shortcode('lunic_search', [$this, 'render']);
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
            <input id="lunic-search-input" class="lunic-search__input" type="search" name="s" autocomplete="off" placeholder="Search here..." />
            <button class="lunic-search__btn" type="submit" aria-label="<?php esc_attr_e('Buscar', 'distribuidora-lunic'); ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#666" d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16a6.47 6.47 0 0 0 4.23-1.57l.27.28v.79L20 21.5 21.5 20l-6-6zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            </button>
            <input type="hidden" name="post_type" value="product" />
            <div class="lunic-search__panel" hidden></div>
        </form>
        <?php
        return (string) ob_get_clean();
    }
}
