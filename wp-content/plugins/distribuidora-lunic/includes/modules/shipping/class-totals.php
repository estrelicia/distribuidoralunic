<?php

namespace Distribuidora_Lunic\Modules\Shipping;

defined('ABSPATH') || exit;

/**
 * El costo de custom_shipping se muestra después del Total y no entra en ese total.
 */
class Totals {

    public function register(): void {
        add_action('wp_loaded', [$this, 'hide_default_shipping_row']);
        add_action('woocommerce_cart_totals_after_order_total', [$this, 'render_after_total']);
        add_action('woocommerce_review_order_after_order_total', [$this, 'render_after_total']);
        add_action('woocommerce_before_calculate_totals', [$this, 'exclude_from_cart_total'], 1000);
        add_filter('woocommerce_calculated_total', [$this, 'adjust_calculated_total'], 1000, 2);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_real_shipping']);
        add_filter('woocommerce_get_order_item_totals', [$this, 'adjust_order_totals'], 10, 3);
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'admin_carry_note']);
        add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'keep_rate_label'], 10, 2);
    }

    public function hide_default_shipping_row(): void {
        remove_action('woocommerce_cart_totals_before_order_total', 'woocommerce_cart_totals_shipping_html');
        remove_action('woocommerce_review_order_before_order_total', 'woocommerce_review_order_shipping');
    }

    public function render_after_total(): void {
        if (!\WC()->cart || !\WC()->cart->needs_shipping()) {
            return;
        }
        $label = $this->chosen_label();
        if ($label === '') {
            return;
        }
        echo '<tr class="shipping-after-total">';
        echo '<th>' . esc_html__('Envío', 'distribuidora-lunic') . '</th>';
        echo '<td><strong>' . wp_kses_post($label) . '</strong></td>';
        echo '</tr>';
    }

    public function exclude_from_cart_total($cart): void {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (!$this->uses_custom_shipping() || !$cart) {
            return;
        }
        $shipping_total = (float) $cart->get_shipping_total();
        if ($shipping_total > 0 && \WC()->session) {
            \WC()->session->set('custom_shipping_total', $shipping_total);
            $cart->set_shipping_total(0);
            $cart->set_shipping_tax(0);
        }
    }

    public function adjust_calculated_total($total, $cart) {
        if (!$this->uses_custom_shipping() || !$cart) {
            return $total;
        }
        $calculated = (float) $cart->get_subtotal() + (float) $cart->get_total_tax();
        $calculated -= (float) $cart->get_discount_total();
        return $calculated;
    }

    public function save_real_shipping($order_id): void {
        $order = wc_get_order($order_id);
        if (!$order || !\WC()->session) {
            return;
        }
        $shipping_total = (float) \WC()->session->get('custom_shipping_total', 0);
        if ($shipping_total > 0) {
            $order->update_meta_data('_real_shipping_total', $shipping_total);
            $order->save();
        }
    }

    public function adjust_order_totals($total_rows, $order, $tax_display) {
        $methods = $order->get_shipping_methods();
        if (empty($methods) || !isset($total_rows['shipping'])) {
            return $total_rows;
        }
        $method = reset($methods);
        if (strpos((string) $method->get_method_id(), 'custom_shipping') === false) {
            return $total_rows;
        }
        $total_rows['shipping']['value'] = '<strong>' . esc_html($method->get_name()) . '</strong>';
        return $total_rows;
    }

    public function admin_carry_note($order): void {
        foreach ($order->get_shipping_methods() as $method) {
            if (strpos((string) $method->get_name(), 'Acarreo') === false) {
                continue;
            }
            echo '<p><strong>' . esc_html__('Acarreo', 'distribuidora-lunic') . '</strong>';
            $real = $order->get_meta('_real_shipping_total');
            if ($real) {
                echo ' — ' . wp_kses_post(wc_price($real));
            }
            echo '</p>';
        }
    }

    public function keep_rate_label($label, $method) {
        if (isset($method->method_id) && $method->method_id === 'custom_shipping') {
            return $method->get_label();
        }
        return $label;
    }

    private function uses_custom_shipping(): bool {
        if (!\WC()->session) {
            return false;
        }
        $chosen = (array) \WC()->session->get('chosen_shipping_methods', []);
        foreach ($chosen as $method) {
            if (strpos((string) $method, 'custom_shipping') !== false) {
                return true;
            }
        }
        return false;
    }

    private function chosen_label(): string {
        if (!\WC()->session) {
            return '';
        }
        $chosen = (array) \WC()->session->get('chosen_shipping_methods', []);
        $packages = \WC()->shipping()->get_packages();
        foreach ($packages as $i => $package) {
            $id = $chosen[$i] ?? '';
            if (strpos((string) $id, 'custom_shipping') === false) {
                continue;
            }
            if (isset($package['rates'][$id])) {
                return $package['rates'][$id]->get_label();
            }
        }
        return '';
    }
}
