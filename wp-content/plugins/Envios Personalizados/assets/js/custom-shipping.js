jQuery(document).ready(function($) {
    'use strict';

    // Asegurar que el shipping se muestre después del total
    $(document.body).on('updated_checkout updated_cart_totals', function() {
        reorganizeShippingDisplay();
    });

    // Reorganizar la visualización del shipping
    function reorganizeShippingDisplay() {
        var $shippingRow = $('.shipping-after-total');
        var $orderTotalRow = $('.order-total');
        
        if ($shippingRow.length && $orderTotalRow.length) {
            // Mover el shipping después del total
            $shippingRow.detach().insertAfter($orderTotalRow);
        }
    }

    // Inicializar en carga de página
    $(window).on('load', function() {
        setTimeout(function() {
            reorganizeShippingDisplay();
        }, 1000);
    });

    // Manejar cambios en el método de envío para asegurar la actualización
    $('body').on('change', 'input[name^="shipping_method"]', function() {
        setTimeout(function() {
            reorganizeShippingDisplay();
        }, 500);
    });
});