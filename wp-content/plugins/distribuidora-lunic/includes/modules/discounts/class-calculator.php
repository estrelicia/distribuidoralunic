<?php

namespace Distribuidora_Lunic\Modules\Discounts;

defined('ABSPATH') || exit;

class Calculator {

    private const OPTION = 'wcd_discount_rules';

    /** @var array<int, true> */
    private static array $processing = [];

    /** @var array<string, float|string> */
    private static array $price_cache = [];

    public static function init(): void {
        add_filter('woocommerce_product_get_price', [self::class, 'apply_category_discount'], 10, 2);
        add_filter('woocommerce_product_variation_get_price', [self::class, 'apply_category_discount'], 10, 2);
        add_filter('woocommerce_get_price_html', [self::class, 'display_discount_price'], 999, 2);
        add_filter('woocommerce_variation_prices_price', [self::class, 'apply_variation_discount'], 10, 3);        add_filter('woocommerce_get_variation_prices_hash', [self::class, 'variation_prices_hash']);
        add_filter('woocommerce_show_variation_price', '__return_true');
        add_action('wp_ajax_wcd_get_variation_discount', [self::class, 'ajax_variation_discount']);
        add_action('wp_ajax_nopriv_wcd_get_variation_discount', [self::class, 'ajax_variation_discount']);
        add_action('wp_ajax_lunic_get_variation_discount', [self::class, 'ajax_variation_discount']);
        add_action('wp_ajax_nopriv_lunic_get_variation_discount', [self::class, 'ajax_variation_discount']);
    }

    public static function apply_category_discount($price, $product) {
        if (!is_a($product, 'WC_Product') || $price === '' || $price === null || (float) $price <= 0) {
            return $price;
        }
        $product_id = $product->get_id();
        if (isset(self::$processing[$product_id])) {
            return $price;
        }
        self::$processing[$product_id] = true;
        $result = self::category_price($product, $price);
        unset(self::$processing[$product_id]);
        return $result;
    }

    public static function apply_variation_discount($price, $variation, $product) {
        if (!is_a($variation, 'WC_Product_Variation')) {
            return $price;
        }
        return self::apply_category_discount($price, $variation);
    }

    /**
     * WooCommerce guarda los precios de variaciones en un transient por producto.
     * Sin la regla en el hash, al cambiar un descuento sigue mostrando el rango viejo.
     */
    public static function variation_prices_hash($hash) {
        $hash = (array) $hash;
        $hash['lunic_discounts'] = md5((string) wp_json_encode(self::rules()));
        return $hash;
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

    /**
     * Un solo cálculo: el filtro de precio. La base es el precio normal.
     * Si la regla excluye ofertas y el producto está en oferta, se deja el precio de oferta.
     * Si no excluye, la regla reemplaza la oferta: no se suma sobre el precio de oferta.
     *
     * @param float|string $price
     * @return float|string
     */
    private static function category_price($product, $price) {
        $regular = (float) $product->get_regular_price();
        $cache_key = $product->get_id() . '|' . $regular . '|' . $price;
        if (isset(self::$price_cache[$cache_key])) {
            return self::$price_cache[$cache_key];
        }
        $result = $price;
        $rules = self::rules();
        if ($rules && $regular > 0 && self::categories($product)) {
            $rule = self::matching_rule($product, $rules);
            if ($rule) {
                $result = self::calculate($regular, $rule);
            }
        }
        self::$price_cache[$cache_key] = $result;
        return $result;
    }

    public static function ajax_variation_discount(): void {
        check_ajax_referer('wcd_ajax_nonce', 'nonce');
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
        $variation = $variation_id ? wc_get_product($variation_id) : null;
        if (!$variation) {
            wp_send_json_error('Variación no encontrada');
        }
        wp_send_json_success(['discount_text' => self::discount_label($variation)]);
    }

    public static function discount_label($product): string {
        $info = self::discount_info($product);
        if ($info && !empty($info['has_category_discount'])) {
            $amount = $info['amount'];
            if ($info['type'] === 'discount' && $info['amount_type'] === 'percentage') {
                return sprintf(__('-%s%% de descuento', 'distribuidora-lunic'), wc_format_localized_decimal((float) $amount));
            }
            if ($info['type'] === 'discount') {
                return sprintf(__('-$%s de descuento', 'distribuidora-lunic'), number_format((float) $amount, 2));
            }
            if ($info['amount_type'] === 'percentage') {
                return sprintf(__('Recargo %s%%', 'distribuidora-lunic'), wc_format_localized_decimal((float) $amount));
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
        return $product->is_on_sale();
    }
}
