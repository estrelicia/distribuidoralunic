<?php
// Asegúrate de que Elementor esté cargado y la clase Widget_Base exista
if (class_exists('\Elementor\Widget_Base')) {
    class Elementor_WCD_Category_Discount_Widget extends \Elementor\Widget_Base {
        public function get_name() {
            return 'wcd_category_discount';
        }
        
        public function get_title() {
            return __('Descuento por Categoría', 'woocommerce-category-discounts');
        }
        
        public function get_icon() {
            return 'eicon-tags';
        }
        
        public function get_categories() {
            return ['woocommerce-elements'];
        }
        
        protected function register_controls() {
            $this->start_controls_section(
                'content_section',
                [
                    'label' => __('Configuración', 'woocommerce-category-discounts'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );
            
            $this->add_control(
                'show_on_product_page',
                [
                    'label' => __('Mostrar solo en páginas de producto', 'woocommerce-category-discounts'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Sí', 'woocommerce-category-discounts'),
                    'label_off' => __('No', 'woocommerce-category-discounts'),
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );
            
            $this->add_control(
                'custom_text',
                [
                    'label' => __('Texto personalizado', 'woocommerce-category-discounts'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => __('Ej: ¡Oferta especial!', 'woocommerce-category-discounts'),
                    'description' => __('Dejar vacío para usar el texto automático del descuento', 'woocommerce-category-discounts'),
                ]
            );
            
            $this->end_controls_section();
            
            $this->start_controls_section(
                'style_section',
                [
                    'label' => __('Estilo', 'woocommerce-category-discounts'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );
            
            $this->add_control(
                'text_color',
                [
                    'label' => __('Color del texto', 'woocommerce-category-discounts'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wcd-discount-text' => 'color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'text_typography',
                    'selector' => '{{WRAPPER}} .wcd-discount-text',
                ]
            );
            
            $this->add_control(
                'background_color',
                [
                    'label' => __('Color de fondo', 'woocommerce-category-discounts'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wcd-discount-text' => 'background-color: {{VALUE}};',
                    ],
                ]
            );
            
            $this->add_control(
                'padding',
                [
                    'label' => __('Relleno', 'woocommerce-category-discounts'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%', 'em'],
                    'selectors' => [
                        '{{WRAPPER}} .wcd-discount-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            $this->add_control(
                'border_radius',
                [
                    'label' => __('Radio del borde', 'woocommerce-category-discounts'),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .wcd-discount-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            
            $this->end_controls_section();
        }
        
        protected function render() {
            $settings = $this->get_settings_for_display();
            
            // Si está configurado para mostrar solo en páginas de producto y no estamos en una
            if ($settings['show_on_product_page'] === 'yes' && !is_product()) {
                return;
            }
            
            global $product;
            
            if (!$product && is_product()) {
                $product = wc_get_product(get_the_ID());
            }
            
            if (!$product) {
                return;
            }

            // Contenedor principal para el texto de descuento
            echo '<div class="wcd-discount-text-wrapper" id="wcd-discount-widget-' . $this->get_id() . '">';

            // Para productos variables, inicializar vacío y preparar para AJAX
            if ($product->is_type('variable')) {
                echo '<div class="wcd-discount-text" data-product-id="' . $product->get_id() . '"></div>';
                
                // Encolar script personalizado solo para productos variables
                add_action('wp_footer', function() use ($product, $settings) {
                    ?>
                    <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        var wcdProductId = <?php echo $product->get_id(); ?>;
                        var customText = '<?php echo esc_js($settings['custom_text']); ?>';
                        
                        // Función para actualizar el texto del descuento
                        function wcdUpdateDiscountText(variationId) {
                            if (!variationId) {
                                $('.wcd-discount-text').html('');
                                return;
                            }
                            
                            $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                type: 'POST',
                                data: {
                                    action: 'wcd_get_variation_discount',
                                    product_id: wcdProductId,
                                    variation_id: variationId,
                                    custom_text: customText,
                                    nonce: '<?php echo wp_create_nonce('wcd_ajax_nonce'); ?>'
                                },
                                success: function(response) {
                                    if (response.success && response.data.discount_text) {
                                        $('.wcd-discount-text').html(response.data.discount_text);
                                    } else {
                                        $('.wcd-discount-text').html('');
                                    }
                                },
                                error: function() {
                                    $('.wcd-discount-text').html('');
                                }
                            });
                        }
                        
                        // Manejar cambios en las variaciones
                        $(document).on('found_variation', 'form.variations_form', function(event, variation) {
                            wcdUpdateDiscountText(variation.variation_id);
                        });
                        
                        // Limpiar cuando se restablecen las variaciones
                        $(document).on('reset_data', 'form.variations_form', function() {
                            $('.wcd-discount-text').html('');
                        });
                        
                        // También manejar el evento show_variation por si acaso
                        $(document).on('show_variation', 'form.variations_form', function(event, variation) {
                            wcdUpdateDiscountText(variation.variation_id);
                        });
                    });
                    </script>
                    <?php
                });
                
            } else {
                // Lógica para productos simples
                $discount_info = WCD_Price_Calculator::get_discount_info($product);
                $discount_text = $this->get_discount_text_from_info($discount_info, $settings, $product);
                
                if (!empty($discount_text)) {
                    echo '<div class="wcd-discount-text">' . esc_html($discount_text) . '</div>';
                }
            }
            
            echo '</div>'; // Cierre del wrapper
        }

        // Nueva función auxiliar para generar texto de descuento
        private function get_discount_text_from_info($discount_info, $settings, $product) {
            if (!empty($settings['custom_text'])) {
                return $settings['custom_text'];
            }
            
            if ($discount_info && isset($discount_info['has_category_discount']) && $discount_info['has_category_discount']) {
                if ($discount_info['type'] === 'discount') {
                    if ($discount_info['amount_type'] === 'percentage') {
                        return sprintf(__('-%d%% de descuento', 'woocommerce-category-discounts'), $discount_info['amount']);
                    } else {
                        return sprintf(__('-$%s de descuento', 'woocommerce-category-discounts'), number_format($discount_info['amount'], 2));
                    }
                } else {
                    // Para recargos
                    if ($discount_info['amount_type'] === 'percentage') {
                        return sprintf(__('Recargo %d%%', 'woocommerce-category-discounts'), $discount_info['amount']);
                    } else {
                        return sprintf(__('Recargo +$%s', 'woocommerce-category-discounts'), number_format($discount_info['amount'], 2));
                    }
                }
            } elseif (WCD_Price_Calculator::has_individual_discount($product)) {
                return __('Producto en Oferta', 'woocommerce-category-discounts');
            }
            
            return '';
        }
    }
}

class WCD_Elementor_Widget {
    public static function init() {
        // Solo inicializar si Elementor está disponible
        if (!did_action('elementor/loaded') || !class_exists('\Elementor\Widget_Base')) {
            // Registrar aviso si Elementor no está activo
            add_action('admin_notices', function() {
                if (current_user_can('activate_plugins')) {
                    echo '<div class="notice notice-warning"><p>';
                    echo esc_html__('WooCommerce Category Discounts: El widget de Elementor no está disponible porque Elementor Pro no está activado.', 'woocommerce-category-discounts');
                    echo '</p></div>';
                }
            });
            return;
        }
        
        add_action('elementor/widgets/register', array(__CLASS__, 'register_widgets'));
        add_action('elementor/frontend/after_enqueue_styles', array(__CLASS__, 'enqueue_styles'));
        
        // Encolar scripts para AJAX
        add_action('elementor/frontend/after_register_scripts', array(__CLASS__, 'enqueue_scripts'));
    }
    
    public static function register_widgets($widgets_manager) {
        // Verificar nuevamente que la clase existe antes de registrar
        if (class_exists('\Elementor_WCD_Category_Discount_Widget')) {
            $widgets_manager->register(new \Elementor_WCD_Category_Discount_Widget());
        }
    }
    
    public static function enqueue_styles() {
        wp_enqueue_style('wcd-elementor-style', WCD_PLUGIN_URL . 'assets/css/frontend.css', array(), WCD_VERSION);
    }
    
    public static function enqueue_scripts() {
        wp_enqueue_script('jquery');
    }
}