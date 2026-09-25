(function($) {
    'use strict';

    const ElementorCustomShipping = {
        init: function() {
            this.bindEvents();
            this.processExistingWidgets();
        },

        bindEvents: function() {
            // Esperar a que Elementor esté listo
            if (window.elementorFrontend) {
                this.processExistingWidgets();
            }

            // Manejar actualizaciones de WooCommerce
            $(document.body).on('updated_cart_totals updated_checkout', () => {
                setTimeout(() => {
                    this.processExistingWidgets();
                }, 100);
            });

            // Manejar cambios en métodos de envío
            $(document.body).on('change', 'input[name^="shipping_method"]', () => {
                setTimeout(() => {
                    this.processExistingWidgets();
                }, 500);
            });

            // Manejar la inicialización de widgets de Elementor
            $(document).on('elementor/frontend/init', () => {
                this.processExistingWidgets();
            });
        },

        processExistingWidgets: function() {
            this.processCartWidgets();
            this.processCheckoutWidgets();
        },

        processCartWidgets: function() {
            $('.elementor-widget-woocommerce-cart').each((index, widget) => {
                this.processCartWidget($(widget));
            });
        },

        processCheckoutWidgets: function() {
            $('.elementor-widget-woocommerce-checkout').each((index, widget) => {
                this.processCheckoutWidget($(widget));
            });
        },

        processCartWidget: function($widget) {
            const $shippingRow = $widget.find('.shipping');
            const $orderTotalRow = $widget.find('.order-total');
            
            if ($shippingRow.length && $orderTotalRow.length) {
                // Mover el envío después del total
                $shippingRow.detach().insertAfter($orderTotalRow);
                
                // Aplicar estilos específicos de Elementor
                $shippingRow.addClass('elementor-shipping shipping-after-total');
                
                // Asegurar que se mantenga el estilo después de actualizaciones AJAX
                this.applyElementorStyles($widget);
            }
        },

        processCheckoutWidget: function($widget) {
            const $shippingRow = $widget.find('.woocommerce-shipping-totals');
            const $orderTotalRow = $widget.find('.order-total');
            
            if ($shippingRow.length && $orderTotalRow.length) {
                // Mover el envío después del total
                $shippingRow.detach().insertAfter($orderTotalRow);
                
                // Aplicar estilos específicos de Elementor
                $shippingRow.addClass('elementor-shipping shipping-after-total');
                
                // Asegurar que se mantenga el estilo después de actualizaciones AJAX
                this.applyElementorStyles($widget);
            }
        },

        applyElementorStyles: function($widget) {
            // Aplicar estilos CSS específicos para Elementor
            $widget.find('.elementor-shipping').css({
                'border-top': '2px solid #e0e0e0',
                'background-color': '#fafafa',
                'font-weight': '600'
            });

            $widget.find('.elementor-shipping th').css({
                'padding': '15px 0',
                'text-align': 'left',
                'color': '#333'
            });

            $widget.find('.elementor-shipping td').css({
                'padding': '15px 0',
                'text-align': 'right',
                'color': '#007cba'
            });
        },

        // Función para verificar si hay envío personalizado activo
        hasCustomShipping: function() {
            const chosenMethods = window.wc_cart_fragments_params || {};
            if (chosenMethods.chosen_shipping_methods) {
                for (let method of chosenMethods.chosen_shipping_methods) {
                    if (method && method.indexOf('custom_shipping') !== -1) {
                        return true;
                    }
                }
            }
            return false;
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(() => {
        ElementorCustomShipping.init();
    });

    // También inicializar cuando Elementor cargue widgets dinámicamente
    if (window.elementorFrontend) {
        window.elementorFrontend.hooks.addAction('frontend/element_ready/global', () => {
            ElementorCustomShipping.init();
        });
    }

})(jQuery);