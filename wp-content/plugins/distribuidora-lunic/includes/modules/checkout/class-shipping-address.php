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
        add_action('woocommerce_customer_save_address', [$this, 'refresh_session_address'], 20, 4);
        add_filter('woocommerce_cart_shipping_packages', [$this, 'packages_from_billing']);
        add_action('woocommerce_before_calculate_totals', [$this, 'fill_session_shipping'], 5);
    }

    /**
     * El carrito solo cotiza si la dirección de envío de la sesión tiene país,
     * provincia y código postal. Un cliente que solo cargó facturación ve
     * “Ingresá tu dirección” aunque la tienda envíe a facturación.
     * Se completa solo la sesión; la dirección guardada en la cuenta no cambia.
     */
    public function fill_session_shipping(): void {
        if (!$this->ships_to_billing_by_default() || $this->posted_ships_elsewhere()) {
            return;
        }
        if (!function_exists('WC') || !WC()->customer) {
            return;
        }
        $customer = WC()->customer;
        if ($customer->get_shipping_country() !== '' && $customer->get_shipping_postcode() !== '' && $customer->get_shipping_state() !== '') {
            return;
        }
        if ($customer->get_billing_country() === '' || $customer->get_billing_postcode() === '') {
            return;
        }
        $customer->set_shipping_country($customer->get_billing_country());
        $customer->set_shipping_state(self::normalize_ar_state((string) $customer->get_billing_state()));
        $customer->set_shipping_postcode($customer->get_billing_postcode());
        $customer->set_shipping_city($customer->get_billing_city());
        $customer->set_shipping_address_1($customer->get_billing_address_1());
        $customer->set_shipping_address_2($customer->get_billing_address_2());
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

    /**
     * Mi cuenta guarda la dirección en otro objeto cliente. El de la sesión, que es
     * el que usa el carrito para cotizar, queda con la dirección vieja.
     */
    public function refresh_session_address($user_id, $type, $address = [], $saved = null): void {
        $type = (string) $type;
        if (($type !== 'billing' && $type !== 'shipping') || !function_exists('WC') || !WC()->customer) {
            return;
        }
        $session = WC()->customer;
        if ((int) $session->get_id() !== (int) $user_id) {
            return;
        }
        $source = $saved instanceof \WC_Customer ? $saved : new \WC_Customer((int) $user_id);
        foreach (['country', 'state', 'postcode', 'city', 'address_1', 'address_2'] as $field) {
            $getter = 'get_' . $type . '_' . $field;
            $setter = 'set_' . $type . '_' . $field;
            if (is_callable([$source, $getter]) && is_callable([$session, $setter])) {
                $session->{$setter}($source->{$getter}());
            }
        }
        $session->save();
        $this->flush_shipping_session();
    }

    /**
     * @param array<int, array<string, mixed>> $packages
     * @return array<int, array<string, mixed>>
     */
    public function packages_from_billing(array $packages): array {
        if (!$this->ships_to_billing_by_default() || !function_exists('WC') || !WC()->customer) {
            return $packages;
        }
        if ($this->posted_ships_elsewhere()) {
            return $packages;
        }
        $customer = WC()->customer;
        $country = (string) $customer->get_billing_country();
        $postcode = (string) $customer->get_billing_postcode();
        $state = (string) $customer->get_billing_state();
        if ($country === '' || ($postcode === '' && $state === '')) {
            return $packages;
        }
        $dest = [
            'country' => (string) $customer->get_billing_country(),
            'state' => self::normalize_ar_state((string) $customer->get_billing_state()),
            'postcode' => (string) $customer->get_billing_postcode(),
            'city' => (string) $customer->get_billing_city(),
            'address' => (string) $customer->get_billing_address(),
            'address_1' => (string) $customer->get_billing_address_1(),
            'address_2' => (string) $customer->get_billing_address_2(),
        ];
        foreach ($packages as $i => $package) {
            if (!isset($package['destination']) || !is_array($package['destination'])) {
                $packages[$i]['destination'] = $dest;
                continue;
            }
            $packages[$i]['destination'] = array_merge($package['destination'], $dest);
        }
        return $packages;
    }

    private function ships_to_billing_by_default(): bool {
        $mode = get_option('woocommerce_ship_to_destination');
        return $mode === 'billing' || $mode === 'billing_only';
    }

    /**
     * En el recálculo del checkout el checkbox viaja dentro de post_data, no en $_POST.
     */
    private function posted_ships_elsewhere(): bool {
        if (!empty($_POST['ship_to_different_address'])) {
            return true;
        }
        $raw = $_POST['post_data'] ?? '';
        if (!is_string($raw) || $raw === '') {
            return false;
        }
        $parsed = [];
        parse_str(wp_unslash($raw), $parsed);
        return !empty($parsed['ship_to_different_address']);
    }

    private function flush_shipping_session(): void {
        if (!function_exists('WC') || !WC()->session) {
            return;
        }
        WC()->session->set('shipping_for_package_0', null);
        WC()->session->set('chosen_shipping_methods', []);
        if (class_exists('\WC_Cache_Helper')) {
            \WC_Cache_Helper::get_transient_version('shipping', true);
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
