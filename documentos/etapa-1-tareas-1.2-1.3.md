# Etapa 1 — tareas 1.2 y 1.3

Fecha: 25/09/2026. Copia local `https://distribuidoralunic.com.ar.dev/`.

## 1.2 Envíos

El método de WooCommerce sigue siendo `custom_shipping`, así las instancias ya guardadas en las zonas no cambian de ID.

| Zona | Instancia | Modo | Costo | Gratis desde |
| --- | --- | --- | --- | --- |
| CABA | 39 | normal | $4.700 | $100.000 |
| Primer cordón | 38 | normal | $8.000 | $5.000.000 |
| Segundo cordón | 40 | normal | $10.000 | $5.000.000 |
| Tercer cordón | 41 | normal | $12.000 | $5.000.000 |
| Provincia de Buenos Aires | 37 | acarreo | $4.700 | $100.000 |
| Nacional | 36 | acarreo | $4.700 | $100.000 |

El texto de acarreo con cargo es «Acarreo hasta la empresa de transporte» más el precio. El total del carrito de prueba (variación 15498) quedó en **$10.897,26** (subtotal $9.006 + IVA). El envío se muestra aparte: CABA $4.700, primer cordón (CP 1602) $8.000, nacional (Córdoba) acarreo $4.700.

Código: `wp-content/plugins/distribuidora-lunic/includes/modules/shipping/`.

El plugin «Envíos Personalizados» está desactivado en esta base. Si se vuelve a activar, el módulo nuevo no registra el método para no duplicarlo.

## 1.3 Descuentos

Se siguen leyendo las **11 reglas** de la opción `wcd_discount_rules` (no se copiaron a otra opción).

- Blend Nº 1, variación 15498: regular $9.480, precio $9.006, HTML con precio tachado y vigente, texto «-5% de descuento».
- Una variación con precio de oferta en memoria ($8.000, sin guardar) no recibe el 5 % adicional: el precio calculado sigue en $8.000.
- El HTML de productos simples usa `<del>` y `<ins>`. En variables el precio lo sigue actualizando WooCommerce; el texto «-5% de descuento» sale por AJAX (`lunic_get_variation_discount` y, por compatibilidad, `wcd_get_variation_discount`) cuando cambia la variación. El widget de Elementor conserva el nombre `wcd_category_discount` para que las fichas ya armadas no queden vacías.

Código: `wp-content/plugins/distribuidora-lunic/includes/modules/discounts/`.

El plugin «Descuentos por Categoría» está desactivado en esta base por el mismo motivo que el de envíos.

## Qué no cambió

Elementor, JetEngine, Ivory Search y Product Filter siguen activos. El cliente sigue viendo las mismas tarifas y el mismo −5 % en las categorías con regla.
