# Etapas 7 y 8 — corte y móvil

Fecha: 25/09/2026. El corte visual se revirtió el mismo día: el tema activo volvió a ser **Hello Elementor (hijo)** y Elementor, Elementor Pro, JetEngine, Ivory Search y Product Filter quedaron activos otra vez, porque sin esas plantillas el sitio no se veía igual.

## 7. Corte

- Se activó `wp-content/themes/lunic/`.
- Quedaron desactivados Elementor, Elementor Pro, JetEngine, Ivory Search y Product Filter (WBW).
- Se borró el CSS generado en `wp-content/uploads/elementor/css`.
- Inicio, tienda y ficha cargan sin pantalla en blanco y sin el texto de un shortcode suelto.
- Tienda con `lunic_cat=135`: 52 productos de Blends de té, orden y paginación.
- Ficha Cardamomo Fruto (Guatemala): **$129.800**, botón Agregar al carrito y barra móvil Agregar.
- Las zonas de envío tenían una sola instancia `custom_shipping` cada una. La copia está en `documentos/export-envios-zonas.json`.
- Se eliminaron del disco los plugins «Envíos Personalizados» y «Descuentos por Categoría». La lógica vive en `distribuidora-lunic`.

## 8. Móvil y estados vacíos

- En un ancho de 390 px la ficha no genera scroll horizontal (`scrollWidth` igual al ancho de la ventana). El precio y el botón de compra quedan en pantalla.
- El foco visible usa un borde verde.
- Búsqueda sin resultados, filtro sin productos y carrito vacío dicen que no hay productos o que el carrito está vacío, y enlazan a la tienda.
- Una categoría sin foto muestra «Sin imagen» y sigue enlazando a su archivo.

El carrito de prueba de la etapa 0 sigue en la sesión del navegador (1 ítem). Los importes de envío y el 5 % no se cambiaron.
