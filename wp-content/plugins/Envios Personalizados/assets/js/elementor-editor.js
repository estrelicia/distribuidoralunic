(function($) {
    'use strict';

    // Scripts para el editor de Elementor
    const ElementorCustomShippingEditor = {
        init: function() {
            this.addEditorControls();
            this.previewShippingInEditor();
        },

        addEditorControls: function() {
            // Agregar controles personalizados al editor
            elementor.hooks.addAction('panel/open_editor/widget', (panel, model, view) => {
                const widgetType = model.get('widgetType');
                
                if (widgetType === 'woocommerce-cart' || widgetType === 'woocommerce-checkout') {
                    this.addShippingControls(panel, model, view);
                }
            });
        },

        addShippingControls: function(panel, model, view) {
            // Agregar sección de controles para el envío personalizado
            setTimeout(() => {
                this.createShippingSection(panel, model);
            }, 100);
        },

        createShippingSection: function(panel, model) {
            // Crear sección personalizada en el panel de control
            const section = $('<div>').addClass('elementor-control-section custom-shipping-section');
            
            section.html(`
                <div class="elementor-control-field">
                    <label class="elementor-control-title">Custom Shipping</label>
                    <div class="elementor-control-input-wrapper">
                        <div class="elementor-control-raw-html">
                            <p>El envío personalizado se mostrará automáticamente después del total en el frontend.</p>
                            <p><strong>Nota:</strong> Para ver los cambios en tiempo real, actualice la vista previa.</p>
                        </div>
                    </div>
                </div>
            `);

            // Insertar después de la sección de contenido
            $('.elementor-control-section_cart:last, .elementor-control-section_checkout:last').after(section);
        },

        previewShippingInEditor: function() {
            // Simular el envío personalizado en el editor
            elementor.hooks.addAction('frontend/element_ready/global', ($element) => {
                if ($element.hasClass('elementor-widget-woocommerce-cart') || 
                    $element.hasClass('elementor-widget-woocommerce-checkout')) {
                    
                    this.simulateShippingInEditor($element);
                }
            });
        },

        simulateShippingInEditor: function($element) {
            // Simular la presencia del envío personalizado en el editor
            const $orderTotal = $element.find('.order-total');
            
            if ($orderTotal.length && !$element.find('.elementor-shipping').length) {
                const shippingHTML = `
                    <tr class="shipping-after-total elementor-shipping">
                        <th>Envío</th>
                        <td><strong>Gratuito</strong></td>
                    </tr>
                `;
                
                $orderTotal.after(shippingHTML);
            }
        }
    };

    // Inicializar cuando Elementor Editor esté listo
    if (window.elementor) {
        elementor.on('preview:loaded', () => {
            ElementorCustomShippingEditor.init();
        });
    }

})(jQuery);