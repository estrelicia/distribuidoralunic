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
    wp_enqueue_style('lunic-font', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap', [], null);
    wp_enqueue_style('lunic', get_stylesheet_uri(), ['lunic-font'], '0.2.8');
    wp_enqueue_script('lunic', get_template_directory_uri() . '/assets/theme.js', [], '0.2.2', true);
});

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
    $url = wp_get_attachment_image_url(64, 'medium');
    if ($url) {
        echo '<a class="lunic-header__logo" href="' . esc_url(home_url('/')) . '"><img src="' . esc_url($url) . '" alt="Distribuidora Lunic" /></a>';
    }
}

function lunic_cart_link(): void {
    $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
    $total = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_total() : '';
    $url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carro/');
    echo '<a class="lunic-cart" href="' . esc_url($url) . '"><span class="lunic-cart__total">' . wp_kses_post($total) . '</span><span class="lunic-cart__icon" aria-hidden="true"></span><span class="lunic-cart__count">' . (int) $count . '</span><span class="screen-reader-text">Carrito</span></a>';
}

function lunic_envios_accordion(): void {
    $option = get_option('configuraciones');
    $html = is_array($option) && !empty($option['envios']) ? $option['envios'] : '';
    echo '<details class="lunic-envios"><summary>Envíos</summary><div>' . wp_kses_post($html) . '</div></details>';
}

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
    echo '<div class="lunic-buybar"><span>' . wp_kses_post($product->get_price_html()) . '</span>';
    echo '<button type="button" class="lunic-buybar__btn">Agregar</button></div>';
});
