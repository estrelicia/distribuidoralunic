<?php
defined('ABSPATH') || exit;

class CustomShipping_Elementor_Pro_Compatibility {
    
    public function __construct() {
        $this->init();
    }
    
    public function init() {
        // Verificar si Elementor Pro está activo
        if (!did_action('elementor_pro/init')) {
            return;
        }
        
        add_action('elementor_pro/init', array($this, 'register_elementor_widgets'));
        add_action('elementor/frontend/after_register_scripts', array($this, 'register_elementor_scripts'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_elementor_styles'));
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'enqueue_editor_scripts'));
        
        // Filtros para modificar los widgets de Elementor
        add_filter('elementor_pro/woocommerce/cart/totals', array($this, 'modify_cart_totals_display'), 10, 2);
        add_filter('elementor_pro/woocommerce/checkout/totals', array($this, 'modify_checkout_totals_display'), 10, 2);
        
        // Hooks para los widgets
        add_action('elementor/widget/woocommerce-checkout/skins_init', array($this, 'modify_checkout_widget'));
        add_action('elementor/widget/woocommerce-cart/skins_init', array($this, 'modify_cart_widget'));
    }
    
    public function register_elementor_widgets() {
        // Registrar widgets personalizados si es necesario
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
    }
    
    public function register_widgets($widgets_manager) {
        // Podemos agregar widgets personalizados aquí si es necesario
    }
    
    public function register_elementor_scripts() {
        wp_register_script(
            'custom-shipping-elementor',
            CUSTOM_SHIPPING_PLUGIN_URL . 'assets/js/elementor-pro-compatibility.js',
            array('jquery', 'elementor-frontend'),
            CUSTOM_SHIPPING_VERSION,
            true
        );
    }
    
    public function enqueue_elementor_styles() {
        wp_enqueue_style(
            'custom-shipping-elementor',
            CUSTOM_SHIPPING_PLUGIN_URL . 'assets/css/elementor-pro-compatibility.css',
            array(),
            CUSTOM_SHIPPING_VERSION
        );
    }
    
    public function enqueue_editor_scripts() {
        wp_enqueue_script(
            'custom-shipping-elementor-editor',
            CUSTOM_SHIPPING_PLUGIN_URL . 'assets/js/elementor-editor.js',
            array('jquery', 'elementor-editor'),
            CUSTOM_SHIPPING_VERSION,
            true
        );
    }
    
    /**
     * Modificar la visualización de totales en el carrito de Elementor
     */
    public function modify_cart_totals_display($totals_html, $widget) {
        if (!WC()->cart->needs_shipping() || !WC()->cart->show_shipping()) {
            return $totals_html;
        }
        
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        $shipping_label = '';
        
        if (!empty($chosen_shipping_methods)) {
            $shipping_packages = WC()->shipping()->get_packages();
            
            foreach ($shipping_packages as $i => $package) {
                $chosen_method = isset($chosen_shipping_methods[$i]) ? $chosen_shipping_methods[$i] : '';
                
                if (strpos($chosen_method, 'custom_shipping') !== false) {
                    $method = isset($package['rates'][$chosen_method]) ? $package['rates'][$chosen_method] : null;
                    
                    if ($method) {
                        $shipping_label = $method->get_label();
                        break;
                    }
                }
            }
        }
        
        if ($shipping_label) {
            // Encontrar la posición del total y insertar el envío después
            $total_pattern = '/<tr[^>]*class="[^"]*order-total[^"]*"[^>]*>.*?<\/tr>/s';
            
            if (preg_match($total_pattern, $totals_html, $matches)) {
                $total_html = $matches[0];
                $shipping_html = '<tr class="shipping-after-total elementor-shipping">';
                $shipping_html .= '<th>' . __('Envío', 'custom-shipping') . '</th>';
                $shipping_html .= '<td><strong>' . $shipping_label . '</strong></td>';
                $shipping_html .= '</tr>';
                
                $totals_html = str_replace($total_html, $total_html . $shipping_html, $totals_html);
            }
        }
        
        return $totals_html;
    }
    
    /**
     * Modificar la visualización de totales en el checkout de Elementor
     */
    public function modify_checkout_totals_display($totals_html, $widget) {
        if (!WC()->cart->needs_shipping()) {
            return $totals_html;
        }
        
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        $shipping_label = '';
        
        if (!empty($chosen_shipping_methods)) {
            $shipping_packages = WC()->shipping()->get_packages();
            
            foreach ($shipping_packages as $i => $package) {
                $chosen_method = isset($chosen_shipping_methods[$i]) ? $chosen_shipping_methods[$i] : '';
                
                if (strpos($chosen_method, 'custom_shipping') !== false) {
                    $method = isset($package['rates'][$chosen_method]) ? $package['rates'][$chosen_method] : null;
                    
                    if ($method) {
                        $shipping_label = $method->get_label();
                        break;
                    }
                }
            }
        }
        
        if ($shipping_label) {
            // Encontrar la posición del total y insertar el envío después
            $total_pattern = '/<tr[^>]*class="[^"]*order-total[^"]*"[^>]*>.*?<\/tr>/s';
            
            if (preg_match($total_pattern, $totals_html, $matches)) {
                $total_html = $matches[0];
                $shipping_html = '<tr class="shipping-after-total elementor-shipping">';
                $shipping_html .= '<th>' . __('Envío', 'custom-shipping') . '</th>';
                $shipping_html .= '<td><strong>' . $shipping_label . '</strong></td>';
                $shipping_html .= '</tr>';
                
                $totals_html = str_replace($total_html, $total_html . $shipping_html, $totals_html);
            }
        }
        
        return $totals_html;
    }
    
    public function modify_checkout_widget($widget) {
        // Agregar controles personalizados al widget de checkout si es necesario
        add_action('elementor/element/woocommerce-checkout/section_checkout/after_section_end', 
            array($this, 'add_checkout_controls'), 10, 2);
    }
    
    public function modify_cart_widget($widget) {
        // Agregar controles personalizados al widget de cart si es necesario
        add_action('elementor/element/woocommerce-cart/section_cart/after_section_end', 
            array($this, 'add_cart_controls'), 10, 2);
    }
    
    public function add_checkout_controls($element, $args) {
        // Agregar controles personalizados para el widget de checkout
        $element->start_controls_section(
            'custom_shipping_section',
            [
                'label' => __('Custom Shipping', 'custom-shipping'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $element->add_control(
            'custom_shipping_note',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('El envío se mostrará automáticamente después del total según la configuración del plugin.', 'custom-shipping'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );
        
        $element->end_controls_section();
        
        // Agregar sección de estilo para el envío
        $element->start_controls_section(
            'custom_shipping_style_section',
            [
                'label' => __('Custom Shipping Style', 'custom-shipping'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $element->add_control(
            'shipping_label_color',
            [
                'label' => __('Shipping Label Color', 'custom-shipping'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-shipping th' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $element->add_control(
            'shipping_value_color',
            [
                'label' => __('Shipping Value Color', 'custom-shipping'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-shipping td' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $element->end_controls_section();
    }
    
    public function add_cart_controls($element, $args) {
        // Agregar controles personalizados para el widget de cart
        $element->start_controls_section(
            'custom_shipping_section',
            [
                'label' => __('Custom Shipping', 'custom-shipping'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $element->add_control(
            'custom_shipping_note',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('El envío se mostrará automáticamente después del total según la configuración del plugin.', 'custom-shipping'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );
        
        $element->end_controls_section();
        
        // Agregar sección de estilo para el envío
        $element->start_controls_section(
            'custom_shipping_style_section',
            [
                'label' => __('Custom Shipping Style', 'custom-shipping'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $element->add_control(
            'shipping_label_color',
            [
                'label' => __('Shipping Label Color', 'custom-shipping'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-shipping th' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $element->add_control(
            'shipping_value_color',
            [
                'label' => __('Shipping Value Color', 'custom-shipping'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-shipping td' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $element->end_controls_section();
    }
}

// Inicializar solo si Elementor Pro está activo
if (defined('ELEMENTOR_PRO_VERSION')) {
    new CustomShipping_Elementor_Pro_Compatibility();
}