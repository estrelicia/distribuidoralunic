<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

/**
 * Elementor free replaces Pro widgets with an empty box. These templates
 * still store the menu, logo and cart, so the store content is rendered here.
 */
final class Elementor_Fallback {

    public static function register(): void {
        add_filter('elementor/widget/render_content', [self::class, 'render_content'], 10, 2);
        add_filter('elementor/frontend/the_content', [self::class, 'fix_whatsapp']);
        add_action('elementor/elements/categories_registered', [self::class, 'register_category']);
        add_action('elementor/widgets/register', [self::class, 'register_missing_widgets'], 100);
        add_action('elementor/preview/enqueue_styles', [self::class, 'preview_styles']);
        add_action('elementor/editor/after_enqueue_styles', [self::class, 'preview_styles']);
        add_action('admin_init', [self::class, 'migrate_pro_woo_widgets']);
        add_shortcode('lunic_notices', [self::class, 'notices_shortcode']);
    }

    public static function register_category($elements_manager): void {
        if (!is_object($elements_manager) || !method_exists($elements_manager, 'add_category')) {
            return;
        }
        $elements_manager->add_category('woocommerce-elements', [
            'title' => 'WooCommerce',
            'icon' => 'eicon-woocommerce',
        ]);
    }

    public static function preview_styles(): void {
        wp_enqueue_style(
            'lunic-elementor-preview',
            DISTRIBUIDORA_LUNIC_URL . 'assets/css/elementor-preview.css',
            [],
            DISTRIBUIDORA_LUNIC_VERSION
        );
    }

    public static function notices_shortcode(): string {
        if (!function_exists('woocommerce_output_all_notices')) {
            return '';
        }
        ob_start();
        woocommerce_output_all_notices();
        return (string) ob_get_clean();
    }

    public static function register_missing_widgets($widgets_manager): void {
        if (!is_object($widgets_manager) || !method_exists($widgets_manager, 'get_widget_types') || !class_exists('\Elementor\Widget_Base')) {
            return;
        }
        require_once __DIR__ . '/class-elementor-fallback-widgets.php';
        $widgets = [
            'woofilters' => Elementor_Filter_Widget::class,
            'wc-archive-products' => Elementor_Archive_Widget::class,
            'woocommerce-cart' => Elementor_Woo_Cart_Widget::class,
            'woocommerce-checkout' => Elementor_Woo_Checkout_Widget::class,
            'woocommerce-checkout-page' => Elementor_Woo_Checkout_Page_Widget::class,
            'woocommerce-my-account' => Elementor_Woo_Account_Widget::class,
            'woocommerce-notices' => Elementor_Woo_Notices_Widget::class,
        ];
        foreach ($widgets as $name => $class) {
            if (method_exists($widgets_manager, 'unregister')) {
                $widgets_manager->unregister($name);
            }
            $widgets_manager->register(new $class());
        }
    }

    public static function migrate_pro_woo_widgets(): void {
        if (get_option('lunic_elementor_pro_woo_migrated') === '3') {
            return;
        }
        if (php_sapi_name() !== 'cli' && !current_user_can('edit_pages')) {
            return;
        }
        global $wpdb;
        $ids = $wpdb->get_col(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND ("
            . "meta_value LIKE '%woocommerce-cart%' OR meta_value LIKE '%woocommerce-notices%' OR "
            . "meta_value LIKE '%woocommerce-checkout%' OR meta_value LIKE '%woocommerce-my-account%' OR "
            . "meta_value LIKE '%woocommerce_cart%')"
        );
        foreach ($ids as $id) {
            self::convert_pro_woo_widgets((int) $id);
        }
        update_option('lunic_elementor_pro_woo_migrated', '3', false);
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::instance()->files_manager->clear_cache();
        }
    }

    private static function convert_pro_woo_widgets(int $post_id): void {
        $raw = get_post_meta($post_id, '_elementor_data', true);
        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            return;
        }
        $from_shortcode = [
            'woocommerce_cart' => 'woocommerce-cart',
            'woocommerce_checkout' => 'woocommerce-checkout-page',
            'woocommerce_my_account' => 'woocommerce-my-account',
            'lunic_notices' => 'woocommerce-notices',
        ];
        $changed = false;
        $walk = static function (array &$els) use (&$walk, $from_shortcode, &$changed): void {
            $keep = [];
            $has_cart = false;
            $has_notices = false;
            foreach ($els as $el) {
                if (!is_array($el)) {
                    continue;
                }
                $type = (string) ($el['widgetType'] ?? '');
                $code = (string) ($el['settings']['shortcode'] ?? '');
                if ($type === 'shortcode') {
                    foreach ($from_shortcode as $tag => $widget) {
                        if (!str_contains($code, $tag)) {
                            continue;
                        }
                        $el['widgetType'] = $widget;
                        $el['elType'] = 'widget';
                        $el['settings'] = [];
                        $el['elements'] = [];
                        $type = $widget;
                        $changed = true;
                        break;
                    }
                }
                if ($type === 'woocommerce-notices') {
                    $has_notices = true;
                }
                if ($type === 'woocommerce-cart') {
                    $has_cart = true;
                }
                if (!empty($el['elements']) && is_array($el['elements'])) {
                    $walk($el['elements']);
                }
                $keep[] = $el;
            }
            if ($has_cart && !$has_notices) {
                array_unshift($keep, [
                    'id' => substr(bin2hex(random_bytes(4)), 0, 7),
                    'elType' => 'widget',
                    'widgetType' => 'woocommerce-notices',
                    'settings' => [],
                    'elements' => [],
                ]);
                $changed = true;
            }
            $els = $keep;
        };
        $walk($data);
        if (!$changed) {
            return;
        }
        $json = wp_json_encode($data);
        if (!is_string($json) || $json === '') {
            return;
        }
        update_post_meta($post_id, '_elementor_data', wp_slash($json));
        delete_post_meta($post_id, '_elementor_css');
        delete_post_meta($post_id, '_elementor_controls_usage');
    }

    /**
     * @param mixed $widget
     */
    public static function render_content($content, $widget) {
        if (!is_object($widget) || !method_exists($widget, 'get_name')) {
            return $content;
        }
        $name = $widget->get_name();
        $settings = $widget->get_data('settings');
        if (!is_array($settings)) {
            $settings = [];
        }
        if ($name === 'shortcode') {
            $code = (string) ($settings['shortcode'] ?? '');
            if (str_contains($code, 'ivory-search') || str_contains((string) $content, '[ivory-search')) {
                return shortcode_exists('lunic_search') ? do_shortcode('[lunic_search]') : $content;
            }
            $editing = class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode();
            if ($editing && (str_contains($code, 'woocommerce_cart') || str_contains($code, 'woocommerce_checkout') || str_contains($code, 'woocommerce_my_account') || str_contains($code, 'lunic_notices'))) {
                return '<div class="lunic-el-woo-ph"><strong>WooCommerce</strong><span>Seleccioná este bloque para moverlo. El contenido se ve en la tienda.</span></div>';
            }
        }
        $filled = self::markup($name, $settings);
        if ($filled === '') {
            return $content;
        }
        if ($name === 'woocommerce-product-data-tabs' || trim((string) $content) === '') {
            return $filled;
        }
        return $content;
    }

    public static function fix_whatsapp($html) {
        if (!is_string($html) || !str_contains($html, 'fa-whatsapp')) {
            return $html;
        }
        $icon = '<svg viewBox="0 0 448 512" width="1em" height="1em" aria-hidden="true"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>';
        $linked = '<a class="lunic-whatsapp" href="https://wa.me/5491123545375" target="_blank" rel="noopener" aria-label="WhatsApp">' . $icon . '</a>';
        $html = preg_replace(
            '#<div class="elementor-icon-wrapper">\s*<div class="elementor-icon[^"]*">\s*<i[^>]*fa-whatsapp[^>]*>\s*</i>\s*</div>\s*</div>#',
            $linked,
            $html
        );
        return str_replace('<i aria-hidden="true" class="fab fa-whatsapp"></i>', $icon, (string) $html);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function markup(string $name, array $settings): string {
        switch ($name) {
            case 'theme-site-logo':
                return self::capture(static function (): void {
                    if (function_exists('lunic_logo')) {
                        lunic_logo();
                    } elseif (function_exists('the_custom_logo')) {
                        the_custom_logo();
                    }
                });
            case 'nav-menu':
                return self::menu($settings);
            case 'woocommerce-menu-cart':
                return self::capture(static function (): void {
                    if (function_exists('lunic_cart_link')) {
                        lunic_cart_link();
                    }
                });
            case 'theme-archive-title':
                if (!function_exists('woocommerce_page_title')) {
                    return '';
                }
                return '<h1 class="woocommerce-products-header__title page-title">' . esc_html(woocommerce_page_title(false)) . '</h1>';
            case 'woocommerce-breadcrumb':
                return self::capture(static function (): void {
                    if (function_exists('woocommerce_breadcrumb')) {
                        woocommerce_breadcrumb();
                    }
                });
            case 'woocommerce-product-images':
                return self::product_part('woocommerce_show_product_images');
            case 'woocommerce-product-title':
                return self::product_part('woocommerce_template_single_title');
            case 'woocommerce-product-meta':
                return self::product_part('woocommerce_template_single_meta');
            case 'woocommerce-product-short-description':
                return self::product_part('woocommerce_template_single_excerpt');
            case 'woocommerce-product-price':
                return self::product_part('woocommerce_template_single_price');
            case 'woocommerce-product-add-to-cart':
                return self::product_part('woocommerce_template_single_add_to_cart');
            case 'woocommerce-product-data-tabs':
                return self::product_part('woocommerce_output_product_data_tabs');
            case 'woocommerce-cart':
                return shortcode_exists('woocommerce_cart') ? do_shortcode('[woocommerce_cart]') : '';
            case 'woocommerce-checkout':
            case 'woocommerce-checkout-page':
                return shortcode_exists('woocommerce_checkout') ? do_shortcode('[woocommerce_checkout]') : '';
            case 'woocommerce-my-account':
                return shortcode_exists('woocommerce_my_account') ? do_shortcode('[woocommerce_my_account]') : '';
            case 'woocommerce-notices':
                return do_shortcode('[lunic_notices]');
            case 'wcd_category_discount':
                return self::discount_label();
            case 'wc-archive-products':
                return self::archive_products();
            case 'woofilters':
                return shortcode_exists('lunic_filter') ? do_shortcode('[lunic_filter]') : '';
            default:
                return '';
        }
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function menu(array $settings): string {
        $slug = isset($settings['menu']) ? (string) $settings['menu'] : '';
        if ($slug === '') {
            return '';
        }
        $vertical = ($settings['layout'] ?? '') === 'vertical';
        if (str_starts_with($slug, 'categorias')) {
            $class = 'lunic-mega__list';
        } elseif ($vertical) {
            $class = 'lunic-footer__list';
        } else {
            $class = 'lunic-menu';
        }
        $args = [
            'container' => false,
            'menu_class' => $class,
            'fallback_cb' => false,
            'echo' => false,
        ];
        $locations = get_nav_menu_locations();
        if (isset($locations[$slug])) {
            $args['theme_location'] = $slug;
        } else {
            $args['menu'] = $slug;
        }
        $html = wp_nav_menu($args);
        if (!is_string($html) || $html === '') {
            return '';
        }
        return '<nav aria-label="' . esc_attr($slug) . '">' . $html . '</nav>';
    }

    private static function archive_products(): string {
        if (!function_exists('woocommerce_product_loop')) {
            return '';
        }
        return self::capture(static function (): void {
            if (woocommerce_product_loop()) {
                do_action('woocommerce_before_shop_loop');
                woocommerce_product_loop_start();
                if (wc_get_loop_prop('total')) {
                    while (have_posts()) {
                        the_post();
                        do_action('woocommerce_shop_loop');
                        wc_get_template_part('content', 'product');
                    }
                }
                woocommerce_product_loop_end();
                do_action('woocommerce_after_shop_loop');
                return;
            }
            do_action('woocommerce_no_products_found');
        });
    }

    private static function product_part(string $callback): string {
        if (!function_exists($callback)) {
            return '';
        }
        return self::capture(static function () use ($callback): void {
            self::ensure_product();
            $callback();
        });
    }

    private static function discount_label(): string {
        if (!class_exists(Modules\Discounts\Calculator::class)) {
            return '';
        }
        self::ensure_product();
        $product = $GLOBALS['product'] ?? null;
        if (!$product instanceof \WC_Product) {
            return '';
        }
        $label = Modules\Discounts\Calculator::discount_label($product);
        if ($label === '') {
            return '';
        }
        return '<p class="wcd-discount-text">' . esc_html($label) . '</p>';
    }

    private static function ensure_product(): void {
        if (isset($GLOBALS['product']) && $GLOBALS['product'] instanceof \WC_Product) {
            return;
        }
        if (!function_exists('wc_get_product')) {
            return;
        }
        $product = wc_get_product(get_the_ID());
        if ($product instanceof \WC_Product) {
            $GLOBALS['product'] = $product;
        }
    }

    private static function capture(callable $callback): string {
        ob_start();
        $callback();
        return (string) ob_get_clean();
    }
}
