<?php

defined('ABSPATH') || exit;

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-slider');
    register_nav_menus([
        'inicio' => 'Inicio',
        'celular' => 'Celular',
        'footer' => 'Footer',
        'categorias-01' => 'Categorías 01',
        'categorias-02' => 'Categorías 02',
        'categorias-03' => 'Categorías 03',
    ]);
});

add_action('after_setup_theme', function () {
    if (get_stylesheet() !== 'lunic') {
        return;
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    $defaults = [
        'inicio' => 16,
        'celular' => 102,
        'footer' => 103,
        'categorias-01' => 113,
        'categorias-02' => 114,
        'categorias-03' => 115,
    ];
    $changed = false;
    foreach ($defaults as $location => $menu_id) {
        if (empty($locations[$location])) {
            $locations[$location] = $menu_id;
            $changed = true;
        }
    }
    if ($changed) {
        set_theme_mod('nav_menu_locations', $locations);
    }
}, 20);

add_action('elementor/theme/register_locations', function () {
    if (get_stylesheet() !== 'lunic') {
        return;
    }
    remove_all_actions('get_header');
    remove_all_actions('get_footer');
}, 100);

add_filter('template_include', function ($template) {
    if (get_stylesheet() !== 'lunic') {
        return $template;
    }
    $dir = get_template_directory();
    if (is_front_page()) {
        return $dir . '/front-page.php';
    }
    if (function_exists('is_shop') && (is_shop() || is_product_taxonomy())) {
        return $dir . '/woocommerce/archive-product.php';
    }
    if (function_exists('is_product') && is_product()) {
        return $dir . '/woocommerce/single-product.php';
    }
    if (is_page()) {
        $post = get_queried_object();
        $named = $dir . '/page-' . $post->post_name . '.php';
        return file_exists($named) ? $named : $dir . '/page.php';
    }
    return $template;
}, 99999);

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('lunic', get_stylesheet_uri(), [], '0.3.57');
    wp_enqueue_script('lunic', get_template_directory_uri() . '/assets/theme.js', [], '0.2.4', true);
});

add_action('wp_head', function () {
    if (get_stylesheet() !== 'lunic') {
        return;
    }
    $font = get_template_directory_uri() . '/assets/fonts/montserrat-400.woff2';
    echo '<link rel="preload" href="' . esc_url($font) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    if (is_front_page()) {
        $hero = get_template_directory_uri() . '/assets/hero-1.webp';
        echo '<link rel="preload" href="' . esc_url($hero) . '" as="image" fetchpriority="high">' . "\n";
    }
}, 1);

function lunic_theme_template_id(string $slot): int {
    $saved = get_option('lunic_theme_templates');
    if (!is_array($saved)) {
        return 0;
    }
    return absint($saved[$slot] ?? 0);
}

function lunic_elementor_slot(string $slot): string {
    static $cache = [];
    if (array_key_exists($slot, $cache)) {
        return $cache[$slot];
    }
    $id = lunic_theme_template_id($slot);
    if ($id < 1 || !class_exists('\Elementor\Plugin')) {
        $cache[$slot] = '';
        return '';
    }
    $post = get_post($id);
    if (!$post instanceof WP_Post || $post->post_type !== 'elementor_library' || $post->post_status !== 'publish') {
        $cache[$slot] = '';
        return '';
    }
    $html = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($id);
    $cache[$slot] = is_string($html) ? $html : '';
    return $cache[$slot];
}

function lunic_uses_assigned_elementor(): bool {
    if (lunic_theme_template_id('header') || lunic_theme_template_id('footer') || lunic_theme_template_id('categories')) {
        return true;
    }
    if (get_option('lunic_maintenance') && lunic_theme_template_id('maintenance') && !current_user_can('manage_options')) {
        return true;
    }
    if (function_exists('is_shop') && (is_shop() || is_product_taxonomy()) && lunic_theme_template_id('shop')) {
        return true;
    }
    return function_exists('is_product') && is_product() && (bool) lunic_theme_template_id('product');
}

add_action('template_redirect', function () {
    if (get_stylesheet() !== 'lunic' || lunic_renders_elementor() || lunic_uses_assigned_elementor() || !class_exists('\Elementor\Plugin')) {
        return;
    }
    $frontend = \Elementor\Plugin::instance()->frontend;
    remove_action('wp_enqueue_scripts', [$frontend, 'enqueue_styles'], \Elementor\Frontend::ENQUEUED_STYLES_PRIORITY);
    remove_action('wp_head', [$frontend, 'print_fonts_links'], 7);
    $images = \Elementor\Plugin::instance()->modules_manager->get_modules('image-loading-optimization');
    if (is_object($images)) {
        remove_action('get_header', [$images, 'set_buffer']);
    }
}, 11);

add_action('wp_enqueue_scripts', function () {
    if (get_stylesheet() !== 'lunic') {
        return;
    }
    $catalog = is_front_page() || (function_exists('is_woocommerce') && (is_shop() || is_product() || is_product_taxonomy()));
    if (!$catalog) {
        return;
    }
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('wc-blocks-style');
    if (is_front_page()) {
        foreach ([
            'wc-cart-fragments',
            'wc-add-to-cart',
            'woocommerce',
            'wc-jquery-blockui',
            'wc-js-cookie',
            'sourcebuster-js',
            'wc-order-attribution',
            'jquery',
            'jquery-core',
            'jquery-migrate',
        ] as $handle) {
            wp_dequeue_script($handle);
        }
    }
}, 100);

add_action('wp_enqueue_scripts', function () {
    if (get_stylesheet() !== 'lunic') {
        return;
    }
    $move = is_front_page() || (function_exists('is_cart') && (is_cart() || is_checkout() || is_shop() || is_product() || is_product_taxonomy()));
    if (!$move) {
        return;
    }
    $scripts = wp_scripts();
    $seen = [];
    $set_footer = function (string $handle) use (&$set_footer, &$seen, $scripts): void {
        if (isset($seen[$handle])) {
            return;
        }
        $seen[$handle] = true;
        $scripts->add_data($handle, 'group', 1);
        $reg = $scripts->registered[$handle] ?? null;
        if (!$reg) {
            return;
        }
        foreach ($reg->deps as $dep) {
            $set_footer($dep);
        }
    };
    foreach ($scripts->queue as $handle) {
        $set_footer($handle);
    }
}, 999);

add_filter('style_loader_tag', function (string $html, string $handle): string {
    if (get_stylesheet() !== 'lunic' || !is_front_page()) {
        return $html;
    }
    if (!in_array($handle, ['woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen'], true)) {
        return $html;
    }
    $async = str_replace(" media='all'", " media='print' onload=\"this.media='all'\"", $html);
    return $async . '<noscript>' . $html . '</noscript>';
}, 10, 2);

add_filter('wp_get_loading_optimization_attributes', function (array $attrs, string $tag, array $attr): array {
    if ($tag !== 'img' || get_stylesheet() !== 'lunic') {
        return $attrs;
    }
    $class = $attr['class'] ?? '';
    if (str_contains($class, 'custom-logo')) {
        unset($attrs['fetchpriority']);
    }
    return $attrs;
}, 10, 3);

add_filter('wp_get_attachment_image_attributes', function (array $attr): array {
    if (get_stylesheet() !== 'lunic') {
        return $attr;
    }
    $class = $attr['class'] ?? '';
    if (function_exists('is_product') && is_product() && str_contains($class, 'wp-post-image')) {
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager';
        $attr['decoding'] = 'async';
    }
    return $attr;
});

function lunic_renders_elementor(): bool {
    if (!is_singular('page') || is_front_page()) {
        return false;
    }
    if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) {
        return false;
    }
    $post = get_queried_object();
    if (!$post instanceof WP_Post) {
        return false;
    }
    if ($post->post_name === 'contacto' || (int) $post->ID === 12340) {
        return false;
    }
    return get_post_meta($post->ID, '_elementor_edit_mode', true) === 'builder';
}

add_filter('template_include', function ($template) {
    if (!get_option('lunic_maintenance')) {
        return $template;
    }
    if (current_user_can('manage_options')) {
        return $template;
    }
    if (is_admin() || wp_doing_ajax()) {
        return $template;
    }
    $maintenance = get_template_directory() . '/maintenance.php';
    return file_exists($maintenance) ? $maintenance : $template;
});

function lunic_logo(): void {
    if (has_custom_logo()) {
        the_custom_logo();
        return;
    }
    $image = wp_get_attachment_image(64, 'medium', false, [
        'alt' => 'Distribuidora Lunic',
        'decoding' => 'async',
    ]);
    if ($image) {
        echo '<a class="lunic-header__logo" href="' . esc_url(home_url('/')) . '">' . $image . '</a>';
    }
}

function lunic_cart_link(): void {
    $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
    $total = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_total() : '';
    $url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carro/');
    echo '<a class="lunic-cart" href="' . esc_url($url) . '"><span class="lunic-cart__total">' . wp_kses_post($total) . '</span><span class="lunic-cart__icon"><span class="lunic-cart__count">' . (int) $count . '</span><svg viewBox="0 0 1000 1000" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M104 365C104 365 105 365 105 365H208L279 168C288 137 320 115 355 115H646C681 115 713 137 723 170L793 365H896C896 365 897 365 897 365H958C975 365 990 379 990 396S975 427 958 427H923L862 801C848 851 803 885 752 885H249C198 885 152 851 138 798L78 427H42C25 427 10 413 10 396S25 365 42 365H104ZM141 427L199 785C205 807 225 823 249 823H752C775 823 796 807 801 788L860 427H141ZM726 365L663 189C660 182 654 177 645 177H355C346 177 340 182 338 187L274 365H726ZM469 521C469 504 483 490 500 490S531 504 531 521V729C531 746 517 760 500 760S469 746 469 729V521ZM677 734C674 751 658 762 641 760 624 758 613 742 615 725L644 519C647 502 663 490 680 492S708 510 706 527L677 734ZM385 725C388 742 375 757 358 760 341 762 325 750 323 733L293 527C291 510 303 494 320 492 337 489 353 501 355 518L385 725Z"/></svg></span><span class="screen-reader-text">Carrito</span></a>';
}

function lunic_envios_accordion(): void {
    $option = get_option('configuraciones');
    $html = is_array($option) && !empty($option['envios']) ? $option['envios'] : '';
    $open = (function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout() && !is_order_received_page());
    echo '<details class="lunic-envios"' . ($open ? ' open' : '') . '><summary>Envíos</summary><div>' . wp_kses_post($html) . '</div></details>';
}

add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain !== 'woocommerce' || get_stylesheet() !== 'lunic') {
        return $translated;
    }
    $map = [
        'Update cart' => 'Actualizar el carrito de compras',
        'Cart totals' => 'Total de la compra',
        'Proceed to checkout' => 'Finalizar Compra',
        'Billing details' => 'Datos de facturación',
        'Last name' => 'Apellido',
        'Ship to a different address?' => 'Dirección de envío (si es diferente a la dirección de facturación)',
    ];
    return $map[$text] ?? $translated;
}, 10, 3);

add_filter('wp_video_shortcode', function (string $output): string {
    if (!function_exists('is_product') || !is_product() || str_contains($output, 'poster=')) {
        return $output;
    }
    $thumb = get_post_thumbnail_id();
    $poster = $thumb ? wp_get_attachment_image_url($thumb, 'large') : '';
    if (!$poster) {
        return $output;
    }
    return str_replace('<video ', '<video poster="' . esc_url($poster) . '" ', $output);
});

add_action('woocommerce_after_cart', 'lunic_envios_accordion');
add_action('woocommerce_after_checkout_form', 'lunic_envios_accordion');

add_action('wp_footer', function () {
    if (!function_exists('is_product') || !is_product()) {
        return;
    }
    global $product;
    if (!$product instanceof WC_Product || !$product->is_purchasable() || !$product->is_in_stock()) {
        return;
    }
    echo '<div class="lunic-buybar"><span class="lunic-buybar__price">' . wp_kses_post($product->get_price_html()) . '</span>';
    echo '<button type="button" class="lunic-buybar__btn">Agregar</button></div>';
});
