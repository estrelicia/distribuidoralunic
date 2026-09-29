<?php

namespace Distribuidora_Lunic\Modules\Shipping;

defined('ABSPATH') || exit;

/**
 * El costo de custom_shipping se muestra después del Total y no entra en ese total.
 */
class Totals {

    public function register(): void {
        add_action('woocommerce_cart_totals_after_order_total', [$this, 'render_after_total']);
        add_action('woocommerce_review_order_after_order_total', [$this, 'render_after_total']);
        add_filter('woocommerce_calculated_total', [$this, 'adjust_calculated_total'], 1000, 2);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_real_shipping']);
        add_filter('woocommerce_get_order_item_totals', [$this, 'adjust_order_totals'], 10, 3);
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'admin_carry_note']);
        add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'keep_rate_label'], 10, 2);
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

    public function adjust_calculated_total($total, $cart) {
        $rate = $this->chosen_custom_rate();
        if (!$rate || !$cart) {
            return $total;
        }
        $cost = (float) $rate->get_cost();
        $tax = (float) $cart->get_shipping_tax();
        if (\WC()->session) {
            \WC()->session->set('custom_shipping_total', $cost);
        }
        if ($cost <= 0 && $tax <= 0) {
            return $total;
        }
        return max(0, (float) $total - $cost - $tax);
    }

    public function save_real_shipping($order_id): void {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $rate = $this->chosen_custom_rate();
        $shipping_total = $rate ? (float) $rate->get_cost() : 0;
        if ($shipping_total <= 0 && \WC()->session) {
            $shipping_total = (float) \WC()->session->get('custom_shipping_total', 0);
        }
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
        if (!isset($method->method_id) || $method->method_id !== 'custom_shipping') {
            return $label;
        }
        $own = $method->get_label();
        if (!$this->package_has_other_methods()) {
            return $own;
        }
        $meta = $method->get_meta_data();
        $title = isset($meta['zone_title']) ? (string) $meta['zone_title'] : '';
        if ($title === '') {
            return $own;
        }
        return $title . ' — ' . $own;
    }

    private function chosen_custom_rate() {
        if (!\WC()->session || !\WC()->shipping()) {
            return null;
        }
        $chosen = (array) \WC()->session->get('chosen_shipping_methods', []);
        foreach (\WC()->shipping()->get_packages() as $i => $package) {
            $id = $chosen[$i] ?? '';
            if (strpos((string) $id, 'custom_shipping') === false) {
                continue;
            }
            if (isset($package['rates'][$id]) && $package['rates'][$id]->get_method_id() === 'custom_shipping') {
                return $package['rates'][$id];
            }
        }
        return null;
    }

    private function package_has_other_methods(): bool {
        if (!\WC()->shipping()) {
            return false;
        }
        foreach (\WC()->shipping()->get_packages() as $package) {
            $has_custom = false;
            $has_other = false;
            foreach ($package['rates'] ?? [] as $rate) {
                if ($rate->get_method_id() === 'custom_shipping') {
                    $has_custom = true;
                } else {
                    $has_other = true;
                }
            }
            if ($has_custom && $has_other) {
                return true;
            }
        }
        return false;
    }

    private function chosen_label(): string {
        $rate = $this->chosen_custom_rate();
        return $rate ? $rate->get_label() : '';
    }
}
