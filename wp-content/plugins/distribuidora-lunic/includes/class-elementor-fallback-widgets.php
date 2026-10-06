<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

abstract class Elementor_Woo_Widget extends \Elementor\Widget_Base {

    abstract protected function panel_title(): string;

    abstract protected function panel_help(): string;

    abstract protected function frontend_html(): string;

    public function get_categories(): array {
        return ['woocommerce-elements', 'general'];
    }

    public function show_in_panel(): bool {
        return true;
    }

    protected function register_controls(): void {
        $this->start_controls_section('section_lunic', [
            'label' => $this->panel_title(),
        ]);
        $this->add_control('lunic_help', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'raw' => '<p>' . esc_html($this->panel_help()) . '</p>',
        ]);
        $this->end_controls_section();
    }

    protected function render(): void {
        if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo '<div class="lunic-el-woo-ph"><strong>' . esc_html($this->panel_title()) . '</strong><span>' . esc_html($this->panel_help()) . '</span></div>';
            return;
        }
        echo $this->frontend_html();
    }
}

final class Elementor_Filter_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'woofilters';
    }

    public function get_title(): string {
        return 'Filtro';
    }

    public function get_icon(): string {
        return 'eicon-filter';
    }

    public function get_categories(): array {
        return ['general'];
    }

    protected function register_controls(): void {
    }

    protected function render(): void {
        if (shortcode_exists('lunic_filter')) {
            echo do_shortcode('[lunic_filter]');
        }
    }
}

final class Elementor_Archive_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'wc-archive-products';
    }

    public function get_title(): string {
        return 'Productos';
    }

    public function get_icon(): string {
        return 'eicon-products';
    }

    public function get_categories(): array {
        return ['general'];
    }

    protected function register_controls(): void {
    }

    protected function render(): void {
        if (!function_exists('woocommerce_product_loop')) {
            return;
        }
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
    }
}

final class Elementor_Woo_Cart_Widget extends Elementor_Woo_Widget {

    public function get_name(): string {
        return 'woocommerce-cart';
    }

    public function get_title(): string {
        return 'Carrito';
    }

    public function get_icon(): string {
        return 'eicon-cart';
    }

    protected function panel_title(): string {
        return 'Carrito';
    }

    protected function panel_help(): string {
        return 'Acá movés o borramos el bloque. La tabla, precios y botones salen de WooCommerce; se ven en la tienda, no se pintan widget por widget.';
    }

    protected function frontend_html(): string {
        return shortcode_exists('woocommerce_cart') ? do_shortcode('[woocommerce_cart]') : '';
    }
}

final class Elementor_Woo_Checkout_Page_Widget extends Elementor_Woo_Widget {

    public function get_name(): string {
        return 'woocommerce-checkout-page';
    }

    public function get_title(): string {
        return 'Checkout';
    }

    public function get_icon(): string {
        return 'eicon-checkout';
    }

    protected function panel_title(): string {
        return 'Checkout';
    }

    protected function panel_help(): string {
        return 'Acá movés o borramos el bloque. El formulario de pago es de WooCommerce y se ve completo en la tienda.';
    }

    protected function frontend_html(): string {
        return shortcode_exists('woocommerce_checkout') ? do_shortcode('[woocommerce_checkout]') : '';
    }
}

final class Elementor_Woo_Checkout_Widget extends Elementor_Woo_Widget {

    public function get_name(): string {
        return 'woocommerce-checkout';
    }

    public function get_title(): string {
        return 'Checkout';
    }

    public function get_icon(): string {
        return 'eicon-checkout';
    }

    protected function panel_title(): string {
        return 'Checkout';
    }

    protected function panel_help(): string {
        return 'Acá movés o borramos el bloque. El formulario de pago es de WooCommerce y se ve completo en la tienda.';
    }

    protected function frontend_html(): string {
        return shortcode_exists('woocommerce_checkout') ? do_shortcode('[woocommerce_checkout]') : '';
    }
}

final class Elementor_Woo_Account_Widget extends Elementor_Woo_Widget {

    public function get_name(): string {
        return 'woocommerce-my-account';
    }

    public function get_title(): string {
        return 'Mi cuenta';
    }

    public function get_icon(): string {
        return 'eicon-person';
    }

    protected function panel_title(): string {
        return 'Mi cuenta';
    }

    protected function panel_help(): string {
        return 'Acá movés o borramos el bloque. Pedidos y datos de la cuenta los arma WooCommerce en la tienda.';
    }

    protected function frontend_html(): string {
        return shortcode_exists('woocommerce_my_account') ? do_shortcode('[woocommerce_my_account]') : '';
    }
}

final class Elementor_Woo_Notices_Widget extends Elementor_Woo_Widget {

    public function get_name(): string {
        return 'woocommerce-notices';
    }

    public function get_title(): string {
        return 'Avisos WooCommerce';
    }

    public function get_icon(): string {
        return 'eicon-alert';
    }

    protected function panel_title(): string {
        return 'Avisos WooCommerce';
    }

    protected function panel_help(): string {
        return 'Muestra cupones, errores y “producto agregado”. En el editor se ve este recuadro; los avisos aparecen en la tienda cuando hay un mensaje.';
    }

    protected function frontend_html(): string {
        return do_shortcode('[lunic_notices]');
    }
}
