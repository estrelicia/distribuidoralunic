<?php

namespace Distribuidora_Lunic\Modules\Discounts;

defined('ABSPATH') || exit;

class Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name(): string {
        return 'wcd_category_discount';
    }

    public function get_title(): string {
        return __('Descuento por Categoría', 'distribuidora-lunic');
    }

    public function get_icon(): string {
        return 'eicon-product-price';
    }

    public function get_categories(): array {
        return ['woocommerce-elements'];
    }

    protected function register_controls(): void {
        $this->start_controls_section('section', [
            'label' => __('Configuración', 'distribuidora-lunic'),
        ]);
        $this->add_control('custom_text', [
            'label' => __('Texto personalizado', 'distribuidora-lunic'),
            'type' => \Elementor\Controls_Manager::TEXT,
        ]);
        $this->end_controls_section();
    }

    protected function render(): void {
        global $product;
        if (!$product && is_product()) {
            $product = wc_get_product(get_the_ID());
        }
        if (!$product) {
            return;
        }
        $settings = $this->get_settings_for_display();
        echo '<div class="wcd-discount-text-wrapper">';
        if ($product->is_type('variable')) {
            $product_id = $product->get_id();
            echo '<div class="wcd-discount-text" data-product-id="' . esc_attr((string) $product_id) . '"></div>';
            $nonce = wp_create_nonce('wcd_ajax_nonce');
            $ajax = admin_url('admin-ajax.php');
            $custom = esc_js($settings['custom_text'] ?? '');
            add_action('wp_footer', static function () use ($product_id, $nonce, $ajax, $custom) {
                ?>
                <script>
                jQuery(function ($) {
                    function update(id) {
                        if (!id) { $('.wcd-discount-text').html(''); return; }
                        $.post('<?php echo esc_url($ajax); ?>', {
                            action: 'lunic_get_variation_discount',
                            product_id: <?php echo (int) $product_id; ?>,
                            variation_id: id,
                            custom_text: '<?php echo $custom; ?>',
                            nonce: '<?php echo esc_js($nonce); ?>'
                        }).done(function (response) {
                            $('.wcd-discount-text').html(response.success ? response.data.discount_text : '');
                        });
                    }
                    $(document).on('found_variation show_variation', 'form.variations_form', function (e, variation) {
                        update(variation.variation_id);
                    });
                    $(document).on('reset_data', 'form.variations_form', function () {
                        $('.wcd-discount-text').html('');
                    });
                });
                </script>
                <?php
            });
        } else {
            $text = !empty($settings['custom_text']) ? $settings['custom_text'] : Calculator::discount_label($product);
            if ($text !== '') {
                echo '<div class="wcd-discount-text">' . esc_html($text) . '</div>';
            }
        }
        echo '</div>';
    }
}
