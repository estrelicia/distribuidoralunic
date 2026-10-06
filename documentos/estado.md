# Estado del proyecto

**Fecha:** 06/10/2026.  
**Entorno:** copia local `https://distribuidoralunic.com.ar.dev/`. No está publicado. El sitio en vivo no se tocó.

Este archivo es el que hay que leer para seguir. El plan largo, las actas de cada etapa, las mediciones y las capturas viejas están en `documentos/archivo/`.

## Qué está activo

| Pieza | Estado |
| --- | --- |
| Tema | `lunic` **0.3.59** (`wp-content/themes/lunic/`). El script sigue en **0.2.4**. |
| Plugin | `distribuidora-lunic` **0.1.12** |
| WooCommerce | Sigue. Moneda ARS. |
| Elementor libre | Activo. Pinta el pie (7421), la tienda y la búsqueda (7825), la ficha (7620), Quiénes somos, cookies y privacidad. |
| Elementor Pro, JetEngine, Ivory Search, Product Filter (WBW) | Apagados. Las carpetas siguen en disco. |
| Easy WP SMTP, reSmush.it, Prime Mover, MainWP Child | Fuera de esta migración. |
| White Label CMS, Megadruid Seguridad | **Desactivados** en local (02/10/2026). Carpetas y opciones siguen. Los reemplaza **Megadruid CMS 0.3.19**. |

La opción `lunic_theme_templates` no se borra: cabecera 7432, pie 7421, megamenú 8261, tienda 7825, ficha 7620. El desplegable de Categorías ya no usa el megamenú 8261: lo arma el plugin.

En el escritorio, el menú **Lunic** está en la posición 55.4, justo arriba de WooCommerce (55.5). Adentro están Plantillas, Descuentos por categoría, Menú de categorías y Texto de envíos. Las reglas siguen en la opción `wcd_discount_rules`.

## Qué se hizo

Etapas 0 a 12, cerradas entre el 25 y el 26/09/2026. El detalle está en `archivo/analisis-plugin-lunic.md` y en las actas `archivo/etapa-*.md`.

1. Backup de archivos y base (25/09/2026).
2. Envíos, descuentos por categoría, CUIT/CUIL o DNI y condición frente al IVA pasaron al plugin. Los montos y los porcentajes no cambiaron.
3. Búsqueda de productos, filtro por categoría y grilla de 39 categorías reemplazan a Ivory Search, WBW y JetEngine.
4. Tema `lunic` con la misma estructura que el sitio de referencia: cabecera, pie, inicio, tienda, ficha, carrito, checkout, cuenta, contacto y categorías.
5. Corte del 26/09/2026: el tema público pasó a `lunic`. El primer intento (etapa 7) se revirtió porque el diseño no coincidía; el segundo (etapa 12) quedó.
6. Caché de página del tema, conteos de categoría en transient y limpieza de índices de plugins ya apagados (etapa 9). PageSpeed de laboratorio: inicio móvil 51 → 85, inicio escritorio 55 → 98. Números completos en `archivo/pagespeed-despues.md`.
7. Etapa 13 (28/09/2026): una sola escala de tipo, botones, campos, tarjetas y cajas en todas las pantallas, en 1440 px y en 390 px. Tema al cerrar las tareas: 0.3.51. Acta: `archivo/etapa-13-rediseno-interfaz.md`.
8. El mismo día, tema **0.3.53**: el carrito de escritorio se centra (máximo 820 px). Precio, cantidad y subtotal quedan bajo su título. En «Total de la compra» la etiqueta va a la izquierda y el importe a la derecha. La caja de totales mide lo mismo que la tabla.
9. El 29/09/2026, checkout, envíos, menú de categorías, importación CSV y el menú de descuentos. Detalle abajo.

## Checkout (29/09/2026, tema 0.3.54 a 0.3.56)

La columna derecha se partía: el título «Tu pedido» quedaba arriba y el resumen empezaba recién al terminar la columna izquierda. El ancho también era el del carrito (820 px).

- El checkout usa máximo 1140 px, centrado. Carrito y cuenta siguen en 820 px.
- `woocommerce/checkout/form-checkout.php` envuelve el título y el resumen en `.lunic-checkout__summary`. Desde 900 px el formulario es una grilla de dos columnas y ese bloque ocupa la columna derecha, en la misma fila que los datos.
- Bajo 782 px el formulario vuelve a una columna.
- El país y la provincia ocupan el ancho del campo. El `select` oculto de Select2 no se fuerza a 100 %: si se le aplica, la página desborda.
- «Realizar el pedido» ocupa todo el ancho de la caja de pago.

Medido en `/finalizar-comprar/`: a 1440 px las dos columnas arrancan a la misma altura; a 390 px se apilan y no hay scroll horizontal. El carrito de prueba no se vació.

## Envíos (29/09/2026, tema 0.3.57)

El monto de cada zona ya era el configurado, pero se veía dos veces: una antes del IVA y otra después del total. El total de WooCommerce no lo incluía, y el resumen no cerraba.

Si la zona solo tiene `custom_shipping`, la fila de antes del IVA queda oculta (`.lunic-shipping-silent` en `woocommerce/cart/cart-shipping.php`) y el precio se muestra una sola vez, debajo del total. Ese importe no entra en el total. El umbral de envío gratis se compara con el subtotal de productos, sin IVA.

Si en la misma zona hay otro método, por ejemplo Andreani, se ven las dos opciones. El envío propio sigue fuera del total. La cotización de Andreani, si se elige, entra en el total. El plugin `andreani-shipping` está en el repositorio. En esta copia hay una instancia (43) con credenciales. El cotizador de ficha está en modo automático, posición flotante, y no se ve en la ficha: la plantilla de producto es Elementor y no dispara ese gancho. Queda pendiente mostrarlo al final de los precios.

Carrito de prueba, sin cambiar el contenido:

| Destino | Envío | Total WooCommerce |
| --- | --- | --- |
| CABA, CP 1424 | $4.700 | $10.897,26 |
| Primer cordón, CP 1602 | $8.000 | $10.897,26 |
| Córdoba, CP 5000 | Acarreo $4.700 | $10.897,26 |

El texto público de envíos coincide en CABA, los tres cordones y el acarreo de interior y del resto de provincia. No está en el cálculo: el tope de 25 kg, los $500 por bulto extra y el acarreo gratis con Vía Cargo. La frase «no utilizamos Andreani» coincide con el checkout de hoy y deja de coincidir si Andreani se carga en una zona.

El país del checkout no está fijo en el código. WooCommerce vende y envía solo a Argentina, y el país por defecto es CABA.

## Menú de categorías (29/09/2026, plugin 0.1.12)

El desplegable se editaba en Elementor. Ahora está en **Lunic → Menú de categorías**.

Tres columnas, como el popup, y un grupo «Sin mostrar». Se arrastra cada categoría. Lo que queda en «Sin mostrar» no aparece. El orden de la columna es el orden del menú. Al guardar se escribe la opción `lunic_mega_menu` y se vacía la caché de página.

Hasta el primer guardado, las columnas salen de los menús Categorías 01, 02 y 03 (113, 114 y 115). «Sin asignar» (término 15) no entra. El mismo orden se usa en el desplegable de escritorio y en el menú del celular. El módulo es `includes/modules/mega-menu/`.

## Escritorio y CSV (29/09/2026)

**Descuentos por categoría** pasó del menú WooCommerce a **Lunic → Descuentos por categoría**. La pantalla y la opción no cambiaron.

La importación de productos por CSV acepta el tipo que Excel en Windows guarda como `application/vnd.ms-excel`, solo si el archivo termina en `.csv` y quien sube puede administrar WooCommerce. Un `.xlsx` sigue sin ser un CSV. El código está en `includes/class-csv-import.php`.

## Megadruid CMS (en desarrollo local)

Plugin **`megadruid-cms` 0.3.19** en `wp-content/plugins/megadruid-cms/`. Ajustes en **Ajustes → Megadruid CMS**. Opción `mdcms_settings`. Catálogo de paneles del Escritorio: opción `mdcms_dashboard_panels` (se arma al visitar el Escritorio de WordPress).

Etapas **0 a 8** cerradas el 02/10/2026. El 03/10/2026 quedó la UI de marca Megadruid y el recorte de pestañas (sin pestaña Ajustes). White Label CMS y Megadruid Seguridad están **apagados**. El límite de login y el endurecimiento los aplica solo Megadruid CMS.

Documentación viva:

| Para qué | Archivo |
| --- | --- |
| Plan de etapas | `documentos/implementacion-plugin-cms.md` |
| README del plugin | `wp-content/plugins/megadruid-cms/README.md` |
| Manual de uso | `wp-content/plugins/megadruid-cms/docs/manual-de-usuario.md` (también pestaña Manual) |
| Acta etapas 0–8 | `documentos/paquete-megadruid-cms/documentacion/acta-cierre-etapa-8.md` |
| Acta UI 0.3.19 | `documentos/archivo/acta-megadruid-cms-0.3.19.md` |

El paquete `documentos/paquete-megadruid-cms/` es una copia de **0.3.1**. El código que se edita y se instala en esta copia es `wp-content/plugins/megadruid-cms/`.

## Ficha: pestaña Descripción (06/10/2026, tema 0.3.59)

En el Blend Nº 39 (y en cualquier ficha con descripción) la pestaña **Descripción** volvía a pintar la plantilla Elementor 7620 y el pie 7421: hueco enorme, barras verdes y el texto mezclado con el footer.

Causa: el template de WooCommerce usa `the_content()`. Con Theme Builder, Elementor toma el ID de la plantilla 7620 y mete otra vez la ficha completa dentro de la pestaña. En **48 productos** publicados ese HTML ya está guardado en `post_content` (el resto de descripciones no vacías son texto normal; hay 245 con contenido).

Qué hace el tema ahora:

- No llama `the_content()` en esa pestaña. Callback `lunic_print_product_description_tab` (filtro `woocommerce_product_tabs`, prioridad 99) y override `woocommerce/single-product/tabs/description.php`.
- Lee `post_content` del producto en la base y deja solo etiquetas de texto (`p`, listas, negritas, etc.). Si el campo trae un dump de Elementor, se ve el copy y no el layout.
- `lunic_elementor_slot()` no reentra: si la ficha 7620 se está pintando, no se vuelve a pedir el mismo documento.
- El fallback de Elementor (`woocommerce-product-data-tabs` en `distribuidora-lunic`) usa siempre el markup de WooCommerce, no el HTML anidado del widget.

Comprobado en local: Blend Nº 39 (`…/blend-no-39-hierbas-para-mate-…`) y Blend Nº 37. El carrito de prueba no se tocó.

Acta: `documentos/archivo/acta-ficha-producto-descripcion-0.3.59.md`.

No hace falta Prime Mover ni reescribir productos en la base para que deje de romperse la pantalla. Subir el tema (y el fallback del plugin) alcanza. Limpiar las 48 descripciones en el editor de WooCommerce es opcional y aparte.

## Pendiente

- **Carrito vacío.** Al vaciarlo se ve el recuadro «Tu carrito está vacío», el botón «Volver a la tienda» y el acordeón de envíos. Tiene que volver solo a la tienda. No se vació el carrito de prueba.
- **Cotizador Andreani.** En modo automático no aparece en la ficha. Tiene que quedar al final de los precios.

## Cómo subir al live

No importar un paquete de Prime Mover encima del live: reemplaza la base y se pierden los pedidos, clientes y stock posteriores a la copia. Prime Mover no fusiona.

Para un cambio de código (como la ficha 0.3.59) alcanza **Git**: `git pull` en el servidor, o copiar las carpetas del tema y del plugin. No hay que migrar la base.

Si el live todavía no corre sobre este repo:

1. En el live, exportar con Prime Mover base, medios, plugins y temas. Ese paquete no se importa. Queda de respaldo.
2. Subir solo `wp-content/themes/lunic`, `wp-content/plugins/distribuidora-lunic` y, si se quiere el plugin disponible, `wp-content/plugins/andreani-shipping`. No reemplazar la base ni `wp-content/uploads`.
3. En el live, activar el tema `lunic` y el plugin `distribuidora-lunic`. Desactivar Elementor Pro, JetEngine, Ivory Search, el filtro WBW y el plugin viejo de envíos. Si ese último sigue activo, el envío nuevo no se registra.
4. Confirmar que `lunic_theme_templates` exista con los mismos IDs. Esas páginas ya están en la base del live.

Los cambios de productos hechos solo en el local no viajan con las carpetas. Andreani no cotiza hasta configurarlo en una zona del live.

## Marca

Verde `#055902`, acento `#AADB1F`, texto `#2F2F2F`, fucsia `#E42886` solo en OFERTA y en quitar, WhatsApp `#368D00`. Montserrat 400, 700 y 900.

Título de página: 2rem, 1.6rem bajo 782 px, peso 900. Título de bloque: 1.35rem, peso 700. Botón primario: fondo `#AADB1F`, texto `#055902`, radio 6 px, alto mínimo 44 px. Botón secundario: fondo `#E6E6E6`, texto `#333`. Campo: alto 44 px, fondo `#F6F7F7`, borde `#DDD`, radio 6 px. Tarjeta y caja: borde `#D5E7A4`, radio 8 px.

Hasta 781 px: una columna, menú hamburguesa, tienda en 2 columnas, pie en una columna. Desde 1025 px: menú en una fila, tienda en 4 columnas.

## Reglas que siguen

- CABA $4.700, primer cordón $8.000, acarreo nacional $4.700. El envío queda fuera del total de WooCommerce.
- Descuentos por categoría como están (Blends de té, −5 %). Campestre: tachado $29.800 y precio $28.310.
- Carrito de prueba: Blend Nº 1, 250 gr, subtotal $9.006, IVA 21 % $1.891,26, total Woo $10.897,26. No agregar Hibiscus ni vaciar ese carrito.
- No tocar pedidos, `wp_mainwp_*`, ni las tablas de Wordfence.
- No reescribir el contenido de los productos. Los `????` de Hibiscus están en la descripción guardada.
- No commitear ni desplegar salvo pedido explícito.
- Al cambiar `style.css`, subir la versión en la cabecera y en `functions.php` juntos. Si se toca `theme.js`, subir también esa versión.

## Diferencias que quedan

No son tareas abiertas. El plan las dejó anotadas el 28/09/2026.

- **Quiénes somos.** El título lo pinta Elementor. A 390 px «Sobre Nosotros» mide 24 px; la escala del tema es 1.6rem.
- **Categorías** (`/elementor-12340/`). La grilla está alineada (39 tarjetas). La página no muestra un título. En WordPress se llama «Elementor #12340».
- **Ficha.** Los `????` de Hibiscus y los subtítulos de la descripción están en el contenido, no en el CSS del tema. El desborde de la pestaña Descripción (plantilla 7620 anidada) quedó resuelto en el tema 0.3.59.
- **Carrito vacío.** Sigue la pantalla vacía. El cambio a volver a la tienda está en Pendiente, más arriba.

## Dónde seguir leyendo

| Para qué | Archivo |
| --- | --- |
| Este estado | `documentos/estado.md` |
| Plan de Megadruid CMS | `documentos/implementacion-plugin-cms.md` |
| Manual Megadruid CMS | `wp-content/plugins/megadruid-cms/docs/manual-de-usuario.md` |
| Acta UI 0.3.19 | `documentos/archivo/acta-megadruid-cms-0.3.19.md` |
| Acta ficha Descripción 0.3.59 | `documentos/archivo/acta-ficha-producto-descripcion-0.3.59.md` |
| Paquete Megadruid CMS (0.3.1) | `documentos/paquete-megadruid-cms/` |
| Política REST | `documentos/rest-api-lunic.md` |
| Capturas de referencia (1440 px y 390 px) | `documentos/linea-base/diseno/` |
| Plan, actas, PageSpeed y capturas anteriores | `documentos/archivo/` |
