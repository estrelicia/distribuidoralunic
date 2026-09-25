<?php
defined('ABSPATH') || exit;

class CustomShipping_Label_Fix {
    
    public function __construct() {
        $this->init();
    }
    
    public function init() {
        // Filtro para corregir las etiquetas en el carrito y checkout
        add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'fix_shipping_label'), 10, 2);
        
        // Filtro para corregir las etiquetas en los detalles del pedido
        add_filter('woocommerce_order_shipping_to_display', array($this, 'fix_order_shipping_display'), 10, 2);
        
        // Filtro para corregir las etiquetas en el admin
        add_filter('woocommerce_get_order_item_totals', array($this, 'fix_order_item_totals'), 10, 3);
    }
    
    /**
     * Corregir etiqueta en carrito y checkout
     */
    public function fix_shipping_label($label, $method) {
        if ($method->method_id === 'custom_shipping') {
            // Devolver solo la etiqueta personalizada sin el precio
            return $method->get_label();
        }
        return $label;
    }
    
    /**
     * Corregir visualización en detalles del pedido
     */
    public function fix_order_shipping_display($shipping_to_display, $order) {
        $shipping_methods = $order->get_shipping_methods();
        
        if (!empty($shipping_methods)) {
            foreach ($shipping_methods as $shipping_method) {
                if (strpos($shipping_method->get_method_id(), 'custom_shipping') !== false) {
                    // Devolver solo el nombre del método sin el precio
                    return $shipping_method->get_name();
                }
            }
        }
        
        return $shipping_to_display;
    }
    
    /**
     * Corregir totales del pedido
     */
    public function fix_order_item_totals($total_rows, $order, $tax_display) {
        $shipping_methods = $order->get_shipping_methods();
        
        if (!empty($shipping_methods)) {
            $shipping_method = reset($shipping_methods);
            
            if (strpos($shipping_method->get_method_id(), 'custom_shipping') !== false) {
                if (isset($total_rows['shipping'])) {
                    // Usar solo el nombre del método sin el precio
                    $total_rows['shipping']['value'] = $shipping_method->get_name();
                }
            }
        }
        
        return $total_rows;
    }
}

new CustomShipping_Label_Fix();