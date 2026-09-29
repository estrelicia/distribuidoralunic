# Línea base de compra — 25/09/2026

Recorrido hecho sobre `https://distribuidoralunic.com.ar.dev/` con Elementor Pro, JetEngine, Ivory Search, WBW, Envíos Personalizados y Descuentos por Categoría **activos**. No se colocó un pedido real: los totales de envío se leyeron en el checkout al cambiar provincia y código postal.

Capturas: `documentos/linea-base/capturas/`.

## Producto de referencia (carrito y checkout)

| Campo | Valor |
| --- | --- |
| Producto | Blend Nº 1 – Té negro, Té verde e Hibiscus |
| Variación | 250 gr (ID 15498, padre 9434) |
| Precio de lista de la variación | $9.480 |
| Precio cobrado (regla −5 % categoría “Blends de té”) | **$9.006** |
| IVA 21 % en checkout | **$1.891,26** |
| Total WooCommerce (sin envío) | **$10.897,26** |

El envío **no entra en esa fila Total**. Aparece debajo, como línea “Envío”. El cliente paga el total más el envío (salvo que el negocio lo cobre aparte en efectivo; en pantalla queda explícito).

## Totales de los tres envíos (mismo carrito)

Subtotal e IVA no cambian. Solo cambia la línea Envío.

| Zona | Cómo se forzó | Línea Envío | Total Woo | Envío fuera del total |
| --- | --- | --- | --- | --- |
| **CABA** | Provincia `C`, CP `1424`, ciudad CABA | `$4.700,00` | $10.897,26 | Sí. Pedido &lt; $100.000 |
| **Nacional (acarreo)** | Provincia `X` (Córdoba), CP `5000` | `Acarreo hasta la empresa de transporte $4.700,00` | $10.897,26 | Sí. Pedido &lt; $100.000 |
| **Primer cordón** | Provincia `B`, CP `1602`, Vicente López | `$8.000,00` | $10.897,26 | Sí. El mínimo de envío gratis está en $5.000.000 |

No apareció un segundo método (`envio_fijo_descuento` / `table_rate`) en el recuadro “Tu pedido” en estas tres pruebas. El costo se ve como una sola línea de envío debajo del total.

## Recorrido funcional

### Inicio
Header con logo, menú hamburguesa, mini carrito y WhatsApp. Slider de “única cuenta autorizada”. Acordeón **Envíos** con las mismas reglas de CABA ($4.700 / gratis desde $100.000), cordones y acarreo nacional. Buscador Ivory id **8891** (AJAX WooCommerce).

### Búsqueda Ivory
Consulta `boldo` (formulario 8891): overlay AJAX con imagen, precio, stock y categoría. Envío del formulario: “Search Results for: boldo”, **21 resultados**.

### Tienda y filtro WBW
Tienda: “Mostrando 1–16 de **691** resultados”. Filtro lateral de categorías con conteo. Marca **Blends de té (52)** → URL ` /tienda/?wpf_filter_cat_list_0=135&wpf_fbv=1 ` y “Mostrando 1–16 de **52** resultados”. El término 15 (Sin asignar) no aparece en el filtro.

### Fichas
- **Simple:** Cardamomo Fruto (Guatemala) x 1 Kg — `$129.800,00 SIN IVA`, botón Agregar al carrito. URL `/tienda/condimentos-y-especias/cardamomo-fruto-guatemala/`.
- **Variable con descuento de categoría:** Blend Nº 1, rango `$9.006,00`–`$17.100,00 SIN IVA` (el −5 % ya está en el rango). Atributo Kilos: 1 Kg / 500 gr / 250 gr. Widget de descuento WCD en la plantilla Elementor.
- **Variable sin stock:** Boldo Hojas entera premium — “Este producto no está disponible porque no hay stock.” Sigue siendo la ficha variable de referencia (galería, pestañas, variaciones 250 gr–25 Kg).

### Carrito
Ítem 250 gr a **$9.006**. Subtotal $9.006, IVA $0 en el carrito (el IVA se calcula en el checkout), Total $9.006. El envío no se calcula ahí.

### Checkout
Campos CUIT/CUIL o DNI y **Condición frente al IVA** (Consumidor Final por defecto, más Monotributo y las opciones IVA). Pagos: Transferencia bancaria y Contra reembolso (este último solo CABA, según el texto). Términos y condiciones obligatorios.

## Parámetros a conservar en el plugin nuevo

- Ivory: shortcode id 8891, AJAX de productos, mínimo 1 carácter.
- WBW: `wpf_filter_cat_list_0={term_id}` y `wpf_fbv=1`. Redirigir esos query args.
- Envío: **no sumar al Total**; etiqueta CABA `$4.700,00`; nacional `Acarreo hasta la empresa de transporte $4.700,00`; 1er cordón `$8.000,00`.
- Descuento Blend 250 gr: 9480 × 0,95 = 9006.

## Capturas

| Archivo | Qué muestra |
| --- | --- |
| `01-inicio.png` | Portada / slider |
| `02-busqueda-ajax.png` | Overlay Ivory “boldo” |
| `03-resultados-busqueda.png` | Página de resultados (archivo pequeño si el capture falló a media carga) |
| `04-tienda.png` | Archivo 691 productos + filtro |
| `05-filtro-blends-de-te.png` | Filtro Blends de té |
| `06-ficha-simple-cardamomo.png` | Ficha simple |
| `06-ficha-blend-variable-descuento.png` | Blend con descuento |
| `07-ficha-variable-boldo.png` | Variable sin stock |
| `08-carrito.png` | Carrito $9.006 |
| `09-checkout-caba.png` | Checkout: provincia CABA, CP, CUIT, IVA |
| `10-checkout-nacional-acarreo.png` | Pedido + acarreo $4.700 |
| `11-checkout-primer-cordon.png` | Pedido + envío $8.000 |
