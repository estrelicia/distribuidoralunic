# Diagnóstico de envíos (13/10/2026)

## Cómo está armado

| Qué | Dónde se edita | Qué hace |
| --- | --- | --- |
| Texto del acordeón «Envíos» | **Lunic → Texto de envíos** (`configuraciones['envios']`) | Solo informativo en inicio, carrito y checkout |
| Importes y zonas | **WooCommerce → Ajustes → Envío** → método **Envío Personalizado** (`custom_shipping`) | Cálculo real por zona (CABA, cordones, PBA, nacional) |
| Andreani | Plugin **andreani-shipping** + misma pantalla de zonas | Cotización aparte; si devuelve precio, entra en el **total** de WooCommerce |

El plugin `distribuidora-lunic` **no guarda los precios de envío**: los lee de cada instancia de WooCommerce y muestra el importe **debajo del total** con «(se abona aparte)», sin sumarlo al total (salvo Andreani u otro método distinto).

## Pruebas en local (13/10/2026)

| Destino | CP | Tarifa custom_shipping | ¿Coincide con WC? |
| --- | --- | --- | --- |
| CABA | 1424 | $4.700 | Sí |
| 1.er cordón | 1602 | $8.000 | Sí |
| Interior (Córdoba) | 5000 | Acarreo $4.700 | Sí (zona nacional, modo acarreo) |
| CABA, subtotal > $100.000 | 1424 | Gratuito | Sí |

## Problemas encontrados

### 1. Monto mínimo mal cargado en cordones (datos, no código)

En WooCommerce, las instancias **1.er, 2.º y 3.er cordón** tenían `min_amount` = **5.000.000** en lugar de **100.000** (como CABA). Con eso, el envío gratis por monto **nunca** se activaba en esas zonas.

**Corrección en local:** se actualizó a `100000` en las instancias #38, #40 y #41. En **live**, revisar **WooCommerce → Envío** en cada zona de cordón.

### 2. Dirección obligatoria en el carrito

`woocommerce_shipping_cost_requires_address = yes`: hasta que el cliente ingresa **provincia y CP** (calculadora del carrito o checkout), WooCommerce **no muestra tarifas**. El acordeón de Lunic sí se ve, pero el importe calculado aparece recién después de la dirección, debajo del total.

### 3. Andreani sin cotización

En zona PBA (#15) hay Andreani (#43) habilitado, pero en la prueba con CP 1900 **solo apareció** `custom_shipping`. Andreani devolvió **0 tarifas** (credenciales, peso/bultos del producto o API). Eso es del plugin Andreani, no del método personalizado.

### 4. Dos lugares distintos

Cambiar el texto en **Lunic → Texto de envíos** **no** cambia los importes. Hay que editar cada zona en **WooCommerce → Envío**.

## Checkout CABA: «No hay envío disponible»

WooCommerce matchea zonas con el **código** de provincia (`C` = CABA), no con el texto «Ciudad Autónoma de Buenos Aires». Si el formulario manda el nombre completo, cae en la zona «resto del mundo» sin **Envío personalizado** y aparece ese mensaje.

También hace falta **código postal** y **ciudad** (Argentina los marca obligatorios). Sin CP, WooCommerce puede no mostrar tarifas hasta completar la dirección.

**Corrección (plugin 0.1.26):** en el checkout, la provincia se normaliza con la lista oficial de estados de WooCommerce (`get_states('AR')`), sin alias fijos en código.

## Cambio de código (plugin 0.1.24)

- Umbral de envío gratis: se usa `WC()->cart->get_subtotal()` (productos sin IVA), alineado con la documentación del proyecto.
- Etiquetas de envío en carrito: compatibilidad con `get_method_id()` en tarifas de WooCommerce 11.
