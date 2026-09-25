<?php

namespace Distribuidora_Lunic\Modules\Discounts;

defined('ABSPATH') || exit;

class Calculator {

    private const OPTION = 'wcd_discount_rules';

    /** @var array<int, true> */
    private static array $processing = [];

    /** @var array<string, float|string> */
    private static array $price_cache = [];

    private static bool $cart_processed = false;

    public static function init(): void {
        add_filter('woocommerce_product_get_price', [self::class, 'apply_category_discount'], 10, 2);
        add_filter('woocommerce_product_variation_get_price', [self::class, 'apply_category_discount'], 10, 2);
        add_filter('woocommerce_get_price_html', [self::class, 'display_discount_price'], 999, 2);
        add_action('woocommerce_before_calculate_totals', [self::class, 'apply_cart_discounts'], 5);
        add_filter('woocommerce_variation_prices_price', [self::class, 'apply_variation_discount'], 10, 3);
        add_filter('woocommerce_show_variation_price', '__return_true');
        add_action('wp_ajax_wcd_get_variation_discount', [self::class, 'ajax_variation_discount']);
        add_action('wp_ajax_nopriv_wcd_get_variation_discount', [self::class, 'ajax_variation_discount']);
        add_action('wp_ajax_lunic_get_variation_discount', [self::class, 'ajax_variation_discount']);
        add_action('wp_ajax_nopriv_lunic_get_variation_discount', [self::class, 'ajax_variation_discount']);
    }

    public static function apply_category_discount($price, $product) {
        if ($price === '' || $price === null || (float) $price <= 0) {
            return $price;
        }
        if (self::$cart_processed && doing_action('woocommerce_before_calculate_totals')) {
            return $price;
        }
        $product_id = $product->get_id();
        if (isset(self::$processing[$product_id])) {
            return $price;
        }
        $cache_key = $product_id . '_' . $price;
        if (isset(self::$price_cache[$cache_key])) {
            return self::$price_cache[$cache_key];
        }

        self::$processing[$product_id] = true;
        $rules = self::rules();
        $result = $price;
        if ($rules && self::categories($product)) {
            $rule = self::matching_rule($product, $rules);
            if ($rule) {
                $result = self::calculate((float) $price, $rule);
            }
        }
        unset(self::$processing[$product_id]);
        self::$price_cache[$cache_key] = $result;
        return $result;
    }

    public static function apply_variation_discount($price, $variation, $product) {
        if (!is_a($variation, 'WC_Product_Variation')) {
            return $price;
        }
        return self::apply_category_discount($price, $variation);
    }

    public static function display_discount_price($price_html, $product) {
        if ($product->is_type('variable')) {
            return $price_html;
        }
        $regular = (float) $product->get_regular_price();
        $current = (float) $product->get_price();
        if ($current <= 0 || $current >= $regular) {
            return $price_html;
        }
        return '<del>' . wc_price($regular) . '</del> <ins>' . wc_price($current) . '</ins>';
    }

    public static function apply_cart_discounts($cart): void {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (self::$cart_processed) {
            return;
        }
        self::$cart_processed = true;
        $rules = self::rules();
        if (!$rules) {
            self::$cart_processed = false;
            return;
        }
        foreach ($cart->get_cart() as $item) {
            $product = $item['data'];
            if (self::has_individual_discount($product)) {
                continue;
            }
            $regular = (float) $product->get_regular_price();
            $discounted = (float) self::apply_category_discount($regular, $product);
            if ($regular !== $discounted && $discounted > 0) {
                $product->set_price($discounted);
            }
        }
        self::$cart_processed = false;
    }

    public static function ajax_variation_discount(): void {
        check_ajax_referer('wcd_ajax_nonce', 'nonce');
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
        $custom_text = isset($_POST['custom_text']) ? sanitize_text_field(wp_unslash($_POST['custom_text'])) : '';
        $variation = $variation_id ? wc_get_product($variation_id) : null;
        if (!$variation) {
            wp_send_json_error('Variación no encontrada');
        }
        if ($custom_text !== '') {
            wp_send_json_success(['discount_text' => $custom_text]);
        }
        wp_send_json_success(['discount_text' => self::discount_label($variation)]);
    }

    public static function discount_label($product): string {
        $info = self::discount_info($product);
        if ($info && !empty($info['has_category_discount'])) {
            $amount = $info['amount'];
            if ($info['type'] === 'discount' && $info['amount_type'] === 'percentage') {
                return sprintf(__('-%d%% de descuento', 'distribuidora-lunic'), $amount);
            }
            if ($info['type'] === 'discount') {
                return sprintf(__('-$%s de descuento', 'distribuidora-lunic'), number_format((float) $amount, 2));
            }
            if ($info['amount_type'] === 'percentage') {
                return sprintf(__('Recargo %d%%', 'distribuidora-lunic'), $amount);
            }
            return sprintf(__('Recargo +$%s', 'distribuidora-lunic'), number_format((float) $amount, 2));
        }
        if (self::has_individual_discount($product)) {
            return __('Producto en Oferta', 'distribuidora-lunic');
        }
        return '';
    }

    public static function discount_info($product) {
        $rule = self::matching_rule($product, self::rules());
        if (!$rule) {
            return false;
        }
        return [
            'amount' => $rule['amount'],
            'amount_type' => $rule['amount_type'],
            'type' => $rule['type'],
            'has_category_discount' => true,
        ];
    }

    public static function has_individual_discount($product): bool {
        if ($product->is_type('variable')) {
            foreach ($product->get_children() as $child_id) {
                $child = wc_get_product($child_id);
                if ($child && self::sale_differs($child)) {
                    return true;
                }
            }
            return false;
        }
        return self::sale_differs($product);
    }

    /** @return array<int, array<string, mixed>> */
    public static function rules(): array {
        $rules = get_transient('wcd_discount_rules_cache');
        if ($rules === false) {
            $rules = get_option(self::OPTION, []);
            set_transient('wcd_discount_rules_cache', $rules, HOUR_IN_SECONDS);
        }
        if (!is_array($rules)) {
            return [];
        }
        usort($rules, static function ($a, $b) {
            return (int) ($a['priority'] ?? 0) <=> (int) ($b['priority'] ?? 0);
        });
        return $rules;
    }

    private static function matching_rule($product, array $rules): ?array {
        $categories = self::categories($product);
        if (!$categories) {
            return null;
        }
        $individual = self::has_individual_discount($product);
        foreach ($rules as $rule) {
            if (!in_array((int) $rule['category'], $categories, true) && !in_array((string) $rule['category'], array_map('strval', $categories), true)) {
                continue;
            }
            if (!empty($rule['exclude_individual_discounts']) && $individual) {
                continue;
            }
            return $rule;
        }
        return null;
    }

    private static function calculate(float $original, array $rule): float {
        $amount = (float) $rule['amount'];
        $percent = ($rule['amount_type'] ?? '') === 'percentage';
        if (($rule['type'] ?? '') === 'discount') {
            return $percent ? $original * (1 - ($amount / 100)) : max(0, $original - $amount);
        }
        return $percent ? $original * (1 + ($amount / 100)) : $original + $amount;
    }

    /** @return int[] */
    private static function categories($product): array {
        $id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();
        $terms = wc_get_product_term_ids($id, 'product_cat');
        return array_map('intval', $terms);
    }

    private static function sale_differs($product): bool {
        $sale = $product->get_sale_price();
        $regular = $product->get_regular_price();
        return $sale !== '' && $sale !== null && (string) $sale !== (string) $regular;
    }
}
