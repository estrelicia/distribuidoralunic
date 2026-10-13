<?php

namespace Distribuidora_Lunic\Modules\Checkout;

defined('ABSPATH') || exit;

/**
 * Las zonas de envío de WooCommerce comparan el código de provincia del país.
 * Si el checkout envía el nombre traducido del estado, se normaliza con la lista de WC.
 */
class Shipping_Address {

    public function register(): void {
        add_filter('woocommerce_checkout_posted_data', [$this, 'normalize_posted_data']);
        add_action('woocommerce_checkout_update_customer', [$this, 'normalize_customer'], 5, 2);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function normalize_posted_data(array $data): array {
        foreach (['billing', 'shipping'] as $prefix) {
            $country_key = $prefix . '_country';
            $state_key = $prefix . '_state';
            if (($data[$country_key] ?? '') !== 'AR' || !isset($data[$state_key])) {
                continue;
            }
            $data[$state_key] = self::normalize_ar_state((string) $data[$state_key]);
        }
        return $data;
    }

    public function normalize_customer($customer, $data): void {
        if (!is_a($customer, 'WC_Customer')) {
            return;
        }
        if ($customer->get_billing_country() === 'AR') {
            $customer->set_billing_state(self::normalize_ar_state($customer->get_billing_state()));
        }
        if ($customer->get_shipping_country() === 'AR') {
            $customer->set_shipping_state(self::normalize_ar_state($customer->get_shipping_state()));
        }
    }

    public static function normalize_ar_state(string $state): string {
        $state = trim($state);
        if ($state === '' || !function_exists('WC') || !WC()->countries) {
            return $state;
        }
        $states = WC()->countries->get_states('AR');
        if (!is_array($states) || $states === []) {
            return $state;
        }
        if (isset($states[$state])) {
            return $state;
        }
        if (preg_match('/^AR-([A-Za-z0-9]+)$/i', $state, $matches)) {
            $iso = $matches[1];
            foreach ($states as $code => $label) {
                if (strcasecmp((string) $code, $iso) === 0) {
                    return (string) $code;
                }
            }
        }
        foreach ($states as $code => $label) {
            if (is_string($label) && strcasecmp($label, $state) === 0) {
                return (string) $code;
            }
        }
        return $state;
    }
}
