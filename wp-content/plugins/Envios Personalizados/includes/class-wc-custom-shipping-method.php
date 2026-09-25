<?php
defined('ABSPATH') || exit;

if (class_exists('WC_Shipping_Method')) {
    class WC_Custom_Shipping_Method extends WC_Shipping_Method {
        
        public function __construct($instance_id = 0) {
            parent::__construct($instance_id);
            $this->id = 'custom_shipping';
            $this->instance_id = absint($instance_id);
            $this->title = __('Envío Personalizado', 'custom-shipping');
            $this->method_title = __('Envío Personalizado', 'custom-shipping');
            $this->method_description = __('Método de envío personalizado con opciones de envío gratuito, precio fijo y acarreo. El envío se muestra después del total y NO se incluye en el cálculo del total.', 'custom-shipping');
            $this->supports = array('shipping-zones', 'instance-settings', 'instance-settings-modal');
            $this->init();
        }
        
        public function init() {
            $this->init_form_fields();
            $this->init_settings();
            
            // Configuración básica
            $this->enabled = $this->get_option('enabled', 'yes');
            $this->title = $this->get_option('title', __('Envío Personalizado', 'custom-shipping'));
            
            // Configuración de envío normal
            $this->min_amount = floatval($this->get_option('min_amount', 10000));
            $this->cost = floatval($this->get_option('cost', 1000));
            
            // Configuración de acarreo (NUEVO: agregar monto mínimo para acarreo)
            $this->carry_cost = floatval($this->get_option('carry_cost', 0));
            $this->carry_min_amount = floatval($this->get_option('carry_min_amount', 10000)); // NUEVO CAMPO
            $this->carry_enabled = $this->get_option('carry_enabled', 'no');
            
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }
        
        public function init_form_fields() {
            $this->instance_form_fields = array(
                'enabled' => array(
                    'title' => __('Habilitar/Deshabilitar', 'custom-shipping'), 
                    'type' => 'checkbox', 
                    'label' => __('Habilitar este método de envío', 'custom-shipping'), 
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Título', 'custom-shipping'), 
                    'type' => 'text', 
                    'description' => __('Título que el usuario verá durante el checkout.', 'custom-shipping'), 
                    'default' => __('Envío Personalizado', 'custom-shipping'), 
                    'desc_tip' => true
                ),
                
                // Sección de Envío Normal
                'shipping_section' => array(
                    'title' => __('Configuración de Envío Normal', 'custom-shipping'),
                    'type' => 'title',
                    'description' => __('Configura las opciones para el envío estándar. NOTA: El costo de envío NO se incluirá en el total del pedido.', 'custom-shipping')
                ),
                'min_amount' => array(
                    'title' => __('Monto mínimo para envío gratis', 'custom-shipping'), 
                    'type' => 'number', 
                    'description' => __('Monto mínimo del pedido para que el envío sea gratuito.', 'custom-shipping'), 
                    'default' => 10000, 
                    'desc_tip' => true, 
                    'custom_attributes' => array('step' => '0.01', 'min' => '0')
                ),
                'cost' => array(
                    'title' => __('Precio de envío', 'custom-shipping'), 
                    'type' => 'number', 
                    'description' => __('Costo de envío para pedidos que no alcanzan el monto mínimo. Este costo NO se incluirá en el total.', 'custom-shipping'), 
                    'default' => 1000, 
                    'desc_tip' => true, 
                    'custom_attributes' => array('step' => '0.01', 'min' => '0')
                ),
                
                // Sección de Acarreo (MODIFICADA: agregar monto mínimo para acarreo)
                'carry_section' => array(
                    'title' => __('Configuración de Acarreo', 'custom-shipping'),
                    'type' => 'title',
                    'description' => __('Cuando el acarreo está activado, se usa la lógica de acarreo en lugar del envío normal. El costo de acarreo NO se incluirá en el total.', 'custom-shipping')
                ),
                'carry_enabled' => array(
                    'title' => __('Habilitar Acarreo', 'custom-shipping'), 
                    'type' => 'checkbox', 
                    'label' => __('Activar modo acarreo (reemplaza el envío normal)', 'custom-shipping'), 
                    'default' => 'no',
                    'desc_tip' => true
                ),
                'carry_min_amount' => array( // NUEVO CAMPO
                    'title' => __('Monto mínimo para acarreo gratis', 'custom-shipping'), 
                    'type' => 'number', 
                    'description' => __('Monto mínimo del pedido para que el acarreo sea gratuito.', 'custom-shipping'), 
                    'default' => 10000, 
                    'desc_tip' => true, 
                    'custom_attributes' => array('step' => '0.01', 'min' => '0')
                ),
                'carry_cost' => array(
                    'title' => __('Precio de acarreo', 'custom-shipping'), 
                    'type' => 'number', 
                    'description' => __('Costo de acarreo hasta la empresa de transporte para pedidos que no alcanzan el monto mínimo. Use 0 para ofrecerlo sin cargo. Este costo NO se incluirá en el total.', 'custom-shipping'), 
                    'default' => 0, 
                    'desc_tip' => true, 
                    'custom_attributes' => array('step' => '0.01', 'min' => '0')
                )
            );
        }
        
        public function calculate_shipping($package = array()) {
            $cart_total = WC()->cart->get_displayed_subtotal();
            
            // VERIFICAR: ¿El acarreo está habilitado en la configuración?
            if ($this->carry_enabled === 'yes') {
                // MODO ACARREO (automático cuando el admin lo activa) - NUEVA LÓGICA
                $this->add_carry_rate($cart_total, $package);
            } else {
                // MODO ENVÍO NORMAL (cuando acarreo está desactivado)
                $this->add_normal_shipping_rate($cart_total, $package);
            }
        }
        
        // MODIFICADA: Ahora recibe el cart_total para la nueva lógica
        private function add_carry_rate($cart_total, $package) {
            // NUEVA LÓGICA: Acarreo gratuito basado en monto mínimo, no en precio
            if ($cart_total >= $this->carry_min_amount) {
                // Acarreo gratuito (por monto mínimo alcanzado)
                $label = __('Acarreo hasta la empresa de transporte', 'custom-shipping');
                $cost = 0;
            } else {
                // Acarreo con costo (no alcanzó el monto mínimo)
                $label = sprintf(__('Acarreo hasta la empresa de transporte %s', 'custom-shipping'), wc_price($this->carry_cost));
                $cost = $this->carry_cost;
            }
            
            $rate = array(
                'id' => $this->get_rate_id(),
                'label' => $label,
                'cost' => $cost,
                'package' => $package,
                'meta_data' => array(
                    'carry_mode' => true,
                    'carry_cost' => $cost,
                    'carry_free' => ($cart_total >= $this->carry_min_amount)
                )
            );
            
            $this->add_rate($rate);
        }
        
        private function add_normal_shipping_rate($cart_total, $package) {
            // Lógica de envío normal según especificaciones (sin cambios)
            if ($cart_total >= $this->min_amount) {
                // Envío gratuito
                $label = __('Gratuito', 'custom-shipping');
                $cost = 0;
            } else {
                // Envío con costo
                $label = wc_price($this->cost);
                $cost = $this->cost;
            }
            
            $rate = array(
                'id' => $this->get_rate_id(),
                'label' => $label,
                'cost' => $cost,
                'package' => $package,
                'meta_data' => array(
                    'free_shipping' => ($cart_total >= $this->min_amount),
                    'normal_cost' => $this->cost
                )
            );
            
            $this->add_rate($rate);
        }
        
        public function is_available($package) {
            $is_available = parent::is_available($package);
            
            if (WC()->cart->is_empty()) {
                $is_available = false;
            }
            
            return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this);
        }
        
        public function admin_options() {
            echo '<div class="notice notice-info">';
            echo '<p><strong>' . __('Importante:', 'custom-shipping') . '</strong> ';
            echo __('El costo de envío NO se incluirá en el total del pedido. Se mostrará como un cargo separado después del total.', 'custom-shipping');
            echo '</p></div>';
            
            parent::admin_options();
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Mostrar/ocultar campos de acarreo según la configuración
                    function toggleCarryFields() {
                        var carryEnabled = $('#woocommerce_custom_shipping_carry_enabled').is(':checked');
                        if (carryEnabled) {
                            // Mostrar campos de acarreo
                            $('#woocommerce_custom_shipping_carry_min_amount').closest('tr').show();
                            $('#woocommerce_custom_shipping_carry_cost').closest('tr').show();
                            // Ocultar campos de envío normal
                            $('#woocommerce_custom_shipping_min_amount').closest('tr').hide();
                            $('#woocommerce_custom_shipping_cost').closest('tr').hide();
                        } else {
                            // Ocultar campos de acarreo
                            $('#woocommerce_custom_shipping_carry_min_amount').closest('tr').hide();
                            $('#woocommerce_custom_shipping_carry_cost').closest('tr').hide();
                            // Mostrar campos de envío normal
                            $('#woocommerce_custom_shipping_min_amount').closest('tr').show();
                            $('#woocommerce_custom_shipping_cost').closest('tr').show();
                        }
                    }
                    
                    // Inicializar
                    toggleCarryFields();
                    $('#woocommerce_custom_shipping_carry_enabled').change(toggleCarryFields);
                });
            </script>
            <?php
        }
    }
} else {
    error_log('WC_Shipping_Method no está disponible al intentar cargar WC_Custom_Shipping_Method.');
}