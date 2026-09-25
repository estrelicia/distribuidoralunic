jQuery(document).ready(function($) {
    // Obtener el índice actual basado en el número de filas existentes
    var ruleIndex = $('.wcd-rule-row').length;
    
    // Agregar nueva regla
    $('#wcd-add-rule').on('click', function() {
        var template = $('#wcd-rule-template').html();
        var newRow = template.replace(/{index}/g, ruleIndex);
        
        // Eliminar la fila "no-rules" si existe
        if ($('.no-rules').length) {
            $('.no-rules').remove();
        }
        
        $('#wcd-rules-body').append(newRow);
        ruleIndex++;
    });
    
    // Eliminar regla
    $(document).on('click', '.wcd-remove-rule', function() {
        $(this).closest('.wcd-rule-row').remove();
        
        // Si no quedan reglas, mostrar mensaje
        if ($('.wcd-rule-row').length === 0) {
            $('#wcd-rules-body').html('<tr class="no-rules"><td colspan="7"><?php echo esc_js(__("No hay reglas configuradas.", "woocommerce-category-discounts")); ?></td></tr>');
            ruleIndex = 0;
        }
    });
});