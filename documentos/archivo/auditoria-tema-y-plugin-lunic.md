# Auditoría del tema Lunic y del plugin Distribuidora Lunic

**Fecha:** 13/10/2026.
**Alcance:** tema `wp-content/themes/lunic/` (0.3.62) y plugin `wp-content/plugins/distribuidora-lunic/` (0.1.16 en local). Revisión de seguridad y de funciones, más los dos reclamos del cliente: el carrito no aparece y los cambios de envío no se ven en la página.
**Dónde se probó:** copia local `https://distribuidoralunic.com.ar.dev/` y, solo lectura, el sitio en vivo `https://distribuidoralunic.com.ar/`.

Este documento no cambia código. Es la lista de correcciones, ordenada por etapas.

## Resumen

1. **El carrito sale vacío en el sitio en vivo porque el tema y el plugin quedaron con versiones que no se combinan.** En el live está el tema 0.3.62 y el plugin 0.1.12. Desde la 0.3.61 el tema deja que Elementor dibuje la página del carrito, en vez de forzar `[woocommerce_cart]`. La página del carrito usa el widget «Cart» de Elementor Pro. Sin Pro, ese widget solo se reemplaza con el plugin 0.1.16, que no está subido. En el live el bloque `elementor-widget-woocommerce-cart` imprime un contenedor vacío.
2. **En celular y tablet no hay ícono de carrito en el encabezado.** El `style.css` del tema esconde el carrito hasta 1024 px (líneas 1096, 1121 y 1156) y no pone otro en su lugar. Solo queda el ítem «Carrito» dentro del menú.
3. **El texto de envíos que se edita en Lunic → Texto de envíos no llega al carrito ni al checkout.** Esas dos páginas son de Elementor y tienen un acordeón «Envíos» con el texto copiado a mano. El tema saltea su propio acordeón cuando la página es de Elementor (`lunic_maybe_envios_accordion()`, `functions.php` 448–458). En la copia local ya difieren: el plugin dice «se adicionan $500 por bulto extra» y el carrito sigue mostrando «Por cada kilo adicional sobre 25 kg: $500». Solo el inicio usa el texto del plugin.
4. **El plugin bloquea la REST de WooCommerce a los visitantes.** `GET /wp-json/wc/store/v1/cart` responde 401 en el live. Lo hace el módulo de seguridad del plugin (`security/class-hardening.php` 59–67), que además se pisa con el endurecimiento de Megadruid CMS.
5. **El modo mantenimiento no funciona.** El filtro de plantillas del tema (prioridad 99999) corre después del de mantenimiento y vuelve a elegir la plantilla normal.

Lo más urgente es la etapa 1: deja el carrito visible en el live.

## Evidencia tomada

| Qué | Resultado |
| --- | --- |
| Versión del tema en el live | `themes/lunic/style.css?ver=0.3.62` |
| Versión del plugin en el live | `distribuidora-lunic/assets/js/search.js?ver=0.1.12`. `assets/css/elementor-preview.css` (nuevo en 0.1.16) da 404 |
| Carrito en el live | El bloque `woocommerce-cart` está en el HTML pero vacío. No hay `woocommerce-cart-form` ni mensaje de carrito vacío |
| Encabezado local | Plantilla Elementor 7432: logo, menú, `woocommerce-menu-cart` (Pro) e ícono |
| Encabezado live | Encabezado del tema (`lunic-cart`), oculto en celular por CSS |
| Páginas de Elementor | Carrito 7 y checkout 8 en modo builder, con acordeón «Envíos» propio |
| Texto de envíos | Opción `configuraciones['envios']`, distinta del acordeón del carrito |
| Zonas de envío | 6 zonas con `custom_shipping`. Provincia y Nacional también con Andreani. `woocommerce_shipping_cost_requires_address = yes` |
| REST para visitantes en el live | `/wp-json/wc/store/v1/cart` → 401. `/wp-json/lunic/v1/search` → 200 |
| `?lunic_preview=1` en el live | 200 para cualquier visitante |

## Modelos

En Cursor se elige el modelo en el selector del agente antes de cada tarea.

| Nombre | Slug en Cursor | Para qué |
| --- | --- | --- |
| Composer 2.5 | `composer-2.5` | CSS, plantillas, textos, cambios chicos con el comportamiento ya definido. |
| Grok 4.6 medium | `cursor-grok-4.6-medium` | Hooks de WordPress y WooCommerce, shortcodes, cableado entre ajustes y la página. |
| Grok 4.7 high | `grok-4.7-high` | Seguridad (REST, cabeceras, caché, límites), dinero (totales, descuentos) y datos de Elementor. |
| Claude Opus 4.7 xhigh | `claude-opus-4-7-thinking-xhigh` | Solo de respaldo, si Grok 4.7 high no cierra una tarea de dinero o de migración de datos. |

Cada tarea tiene un modelo y uno de respaldo. Si la tarea se traba dos veces con el primero, se pasa al de respaldo.

## Reglas para todas las etapas

- Antes de subir al live: copia de la base de datos y de `wp-content/themes/lunic/` y `wp-content/plugins/distribuidora-lunic/`.
- Tema y plugin se suben juntos. Cada etapa sube la versión en `style.css` + `wp_enqueue_style` del tema y en `distribuidora-lunic.php` (cabecera y `DISTRIBUIDORA_LUNIC_VERSION`).
- No se vacía el carrito de prueba ni se hacen pedidos reales.
- No se reescribe `post_content` de productos. Los arreglos de presentación son al mostrar.
- Después de cada subida se borra la caché del plugin (`wp-content/cache/lunic/*.html`) o se guarda Lunic → Plantillas, que la vacía.
- Cada tarea termina probando en el navegador local: escritorio (1366 px) y celular (390 px).

## Etapa 1. Carrito visible (urgente)

### 1.1 Subir el plugin 0.1.16 al live

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** el live tiene el tema 0.3.62 y el plugin 0.1.12. El tema ya no fuerza `[woocommerce_cart]` en páginas de Elementor (`page.php` 20–21). El plugin 0.1.12 no registra el reemplazo del widget Pro `woocommerce-cart` (`class-elementor-fallback.php`, `class-elementor-fallback-widgets.php`).
- **Corrección:** subir la carpeta completa `wp-content/plugins/distribuidora-lunic/` (0.1.16). Al primer ingreso al escritorio corre la migración `lunic_elementor_pro_woo_migrated = 3`, que convierte los widgets Pro de carrito, checkout y cuenta en los del plugin. Por eso va primero la copia de la base.
- **Prueba:** en el live, con un producto en el carrito, `/carro/` muestra la tabla, los totales y el botón «Finalizar Compra». `/finalizar-comprar/` y `/mi-cuenta/` siguen andando.

### 1.2 Red de seguridad en `page.php` si el widget de carrito no imprime nada

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** si el plugin vuelve a quedar desfasado, el carrito vuelve a quedar en blanco sin aviso.
- **Corrección:** en `page.php`, en carrito, checkout y cuenta, si el HTML de `the_content()` no contiene `woocommerce-cart-form`, `cart-empty`, `woocommerce-checkout` o `woocommerce-MyAccount`, imprimir el shortcode de WooCommerce. Alternativa: que el tema muestre un aviso solo a administradores cuando la versión del plugin sea menor a la requerida.
- **Prueba:** desactivar el plugin en local: el carrito sigue visible con el shortcode.

### 1.3 Ícono de carrito en celular y tablet

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `style.css` 1096 y 1121 esconden `.elementor-widget-woocommerce-menu-cart` hasta 1024 px; 1156 esconde `.lunic-header__bar .lunic-cart` en celular. Con encabezado de Elementor, `header.php` 15–17 no imprime `lunic_cart_link()`.
- **Corrección:** mostrar un ícono de carrito con contador en celular y tablet, al lado de WhatsApp y del botón de menú. Con encabezado de Elementor, imprimir `lunic_cart_link()` junto al botón de menú que ya agrega `header.php` 17. Ajustar la posición absoluta del botón de menú (`style.css` 1101–1107) para que no se encimen.
- **Prueba:** 390 px y 800 px, el ícono se ve y lleva a `/carro/`. El contador coincide con el carrito.

### 1.4 Contador del carrito al día

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** en el inicio se sacan `wc-cart-fragments`, `wc-add-to-cart` y jQuery (`functions.php` 179–193). El contador del encabezado no se refresca sin recargar. El encabezado entra en la caché de página del plugin.
- **Corrección:** agregar `lunic_cart_link()` a los fragmentos (`woocommerce_add_to_cart_fragments`) y cargar `wc-cart-fragments` en las páginas que muestran el ícono, o un script propio liviano que pida el contador a `wc-ajax=get_refreshed_fragments`.
- **Prueba:** agregar desde la ficha y volver al inicio: el número cambia sin recargar.

## Etapa 2. Envíos que se reflejan en la página

### 2.1 Un solo texto de envíos en inicio, carrito y checkout

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** el carrito (7) y el checkout (8) tienen el acordeón de Elementor con texto fijo (widgets `#bee8310` y `#8ecfaaa`). `lunic_maybe_envios_accordion()` (`functions.php` 448–458) no imprime el del plugin en páginas de Elementor.
- **Corrección:**
  1. Registrar en el plugin el shortcode `[lunic_envios]` que llame a `lunic_envios_accordion()` (o mover esa función al plugin y que el tema la use).
  2. En el editor de Elementor de carrito y checkout, reemplazar el acordeón por un widget Shortcode con `[lunic_envios]`. Si se hace por migración, misma técnica que `lunic_elementor_pro_woo_migrated`, con su propia opción de versión.
  3. Dejar `lunic_maybe_envios_accordion()` solo para páginas que no son de Elementor.
- **Prueba:** cambiar una línea en Lunic → Texto de envíos y verla igual en inicio, carrito y checkout.

### 2.2 Aclarar dónde se cambia cada dato de envío

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** hay dos lugares y el cliente puede confundirlos. El texto informativo está en Lunic → Texto de envíos. Los importes (CABA $4.700, mínimo $100.000, cordones $8.000 / $10.000 / $12.000, acarreo $4.700) están en WooCommerce → Ajustes → Envío, método «Envío Personalizado» de cada zona. Cambiar uno no cambia el otro. Además el costo solo aparece después de cargar la dirección (`woocommerce_shipping_cost_requires_address = yes`).
- **Corrección:** texto de ayuda en la pantalla Texto de envíos con el enlace a las zonas, y aviso en la ayuda del método. Agregar la explicación al manual del cliente.
- **Prueba:** el cliente cambia un importe y un texto siguiendo la ayuda, sin preguntar.

### 2.3 Confirmar la regla de cobro del envío

- **Modelo:** Grok 4.7 high. **Respaldo:** Claude Opus 4.7 xhigh.
- **Problema:** `shipping/class-totals.php` 36–49 resta el costo de `custom_shipping` del total. El pedido se cobra sin el envío y el importe queda en el meta `_real_shipping_total`. Hoy no hay pasarela online activa, así que se cobra aparte, pero si se activa Mercado Pago u otra, se cobrará de menos.
- **Corrección:** confirmar con el cliente que el envío propio se paga aparte. Si es así, documentarlo y mostrar en el email del pedido «Envío: $X (se abona aparte)», porque hoy `adjust_order_totals` (68–78) solo muestra el nombre. Si debe cobrarse online, quitar `adjust_calculated_total`.
- **Prueba:** pedido de prueba en local con CABA bajo $100.000: el email y el pedido muestran el importe del envío y el total coincide con lo que se cobra.

### 2.4 Actualizar `cart-shipping.php` a WooCommerce 11.2

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** la copia del tema es de la 8.8.0; WooCommerce local está en 11.2.0.
- **Corrección:** partir de `wp-content/plugins/woocommerce/templates/cart/cart-shipping.php` 11.2.0 y volver a aplicar solo el bloque `only_custom` (líneas 22–44 de la copia actual).
- **Prueba:** CABA (solo envío propio) y Nacional (envío propio + Andreani) en carrito y checkout: la elección de método y los totales se comportan igual que antes.

## Etapa 3. Seguridad

### 3.1 Dejar de bloquear la REST de WooCommerce

- **Modelo:** Grok 4.7 high. **Respaldo:** Claude Opus 4.7 xhigh.
- **Problema:** `security/class-hardening.php` 59–67 devuelve 401 a todo visitante salvo `/wp-json/lunic/v1/`. Rompe la Store API (bloques de carrito y checkout, que el plugin declara compatibles), webhooks de pasarelas y cualquier integración. `trim_wp_core_endpoints` (70–80) borra además todas las rutas `/wp/*`.
- **Corrección:** quitar el bloqueo general. Para cortar la enumeración de usuarios alcanza con lo que ya hace Megadruid CMS (`block_rest_users`). Si se quiere mantener algo en el plugin, cerrar solo `/wp/v2/users` a visitantes.
- **Prueba:** `/wp-json/wc/store/v1/cart` responde 200 a un visitante; `/wp-json/wp/v2/users` sigue cerrado.

### 3.2 Un solo endurecimiento: Megadruid CMS

- **Modelo:** Grok 4.7 high. **Respaldo:** Grok 4.6 medium.
- **Problema:** `security/module.php` 10 se apaga solo si existe `cabeceras_seguridad`, que ya no existe. Corre siempre junto con `megadruid-cms/includes/class-hardening.php`. Las dos mandan cabeceras distintas (`Referrer-Policy`, HSTS sin comprobar SSL, `Permissions-Policy` con `payment=()`), cierran XML-RPC y feeds.
- **Corrección:** que el módulo no se registre si Megadruid CMS está activo (`class_exists` de su clase o `defined` de su constante). Mover lo que es de tienda (texto «Oferta», `woocommerce_defer_transactional_emails`) a otro módulo. HSTS solo con `is_ssl()`. Sacar `payment=()`.
- **Prueba:** una sola copia de cada cabecera en la respuesta. Feeds y XML-RPC siguen cerrados por Megadruid.

### 3.3 Vista previa del tema solo para administradores

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** `class-plugin.php` 33–49: cualquiera con `?lunic_preview=1` cambia el tema de esa petición. Hoy el tema activo ya es Lunic, así que no tiene uso.
- **Corrección:** borrar `maybe_preview_theme()`. Si se quiere conservar, exigir `current_user_can('edit_theme_options')`.
- **Prueba:** visitante con `?lunic_preview=1` ve la página normal.

### 3.4 Caché de página: clave, cookies y vaciado

- **Modelo:** Grok 4.7 high. **Respaldo:** Claude Opus 4.7 xhigh.
- **Problemas en `class-page-cache.php`:**
  - La clave es solo la ruta (134–136). Un `Host` falso puede dejar guardada una página con enlaces a otro dominio.
  - No se tiene en cuenta la cookie `woocommerce_cart_hash` (106–112).
  - Solo se vacía al guardar plantillas, texto de envíos y mega menú. Precio, stock, descuentos o un producto nuevo tardan hasta 15 minutos en verse.
- **Corrección:** incluir host y esquema de `home_url()` en la clave y no guardar si el `Host` pedido es otro. Tratar `woocommerce_cart_hash` como cookie privada. Vaciar en `save_post_product`, `woocommerce_update_product`, `woocommerce_product_set_stock`, `woocommerce_variation_set_stock`, cambios de términos `product_cat` y al guardar Descuentos por Categoría.
- **Prueba:** cambiar el precio de un producto y ver el nuevo precio como visitante en menos de un minuto. Petición con `Host` falso no genera archivo de caché.

### 3.5 Formulario de contacto contra spam

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** `contact/module.php` 32–43 tiene nonce, pero no valida el email con `is_email()`, no tiene límite por IP ni trampa para bots. Manda igual con `Reply-To` vacío.
- **Corrección:** validar email, campo trampa oculto, límite por IP (transient, por ejemplo 3 envíos cada 10 minutos). La IP sale como en Megadruid CMS (Cloudflare solo si `REMOTE_ADDR` es de Cloudflare). Mensaje de error claro al visitante.
- **Prueba:** cuarto envío seguido rechazado. Email inválido rechazado. Envío normal llega.

### 3.6 Buscador con límite

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** `search/class-suggestions.php` 15–51: ruta REST y AJAX públicas, sin mínimo de letras ni límite por IP. `search.js` pide cada 200 ms.
- **Corrección:** mínimo 2 letras, caché corta de la respuesta por término (transient de 5 minutos), límite por IP, quitar la versión AJAX duplicada si nada la usa, subir el retardo de `search.js` a 300 ms.
- **Prueba:** sugerencias siguen funcionando en el buscador; ráfaga de pedidos devuelve 429.

### 3.7 Importador CSV más estricto

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `class-csv-import.php` 31–47 acepta `text/html` y `application/octet-stream` como CSV.
- **Corrección:** aceptar solo `text/csv`, `text/plain` y `application/vnd.ms-excel`, con extensión `.csv` y tamaño máximo.
- **Prueba:** un `.csv` real se importa; un `.html` renombrado se rechaza.

### 3.8 Texto de descuento sin HTML

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `discounts/class-calculator.php` 108–118 devuelve el `custom_text` que manda el navegador y el widget lo pinta con `.html()` (`class-elementor-widget.php` 58–65).
- **Corrección:** no devolver `custom_text` desde el servidor y pintar con `.text()`.
- **Prueba:** el cartel de descuento de una variación sigue apareciendo.

### 3.9 Salida de Elementor sin escapar

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `header.php` 16 y 36–38, `footer.php` 5, `archive-product.php` 6, `single-product.php` 11 y `maintenance.php` 17 hacen `echo` del HTML de Elementor. Es lo normal con un constructor, pero solo es seguro si únicamente administradores editan plantillas.
- **Corrección:** no hace falta tocar el código. Confirmar que ningún rol que no sea administrador tenga `unfiltered_html` ni acceso a la biblioteca de Elementor, y anotarlo en el manual.
- **Prueba:** con un editor o gestor de tienda, la biblioteca de Elementor no se puede abrir.

## Etapa 4. Funciones

### 4.1 Modo mantenimiento que funcione

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** `functions.php` 68–88 (prioridad 99999) pisa al filtro de mantenimiento (275–287).
- **Corrección:** un solo `template_include` con este orden: editor de Elementor, mantenimiento (si está activo y el usuario no es administrador), plantillas del tema.
- **Prueba:** activar mantenimiento en Lunic → Plantillas; en una ventana privada, inicio, tienda, ficha y páginas muestran la pantalla de mantenimiento; el administrador ve el sitio.

### 4.2 Descuentos por categoría: lógica clara

- **Modelo:** Grok 4.7 high. **Respaldo:** Claude Opus 4.7 xhigh.
- **Problema:** `apply_cart_discounts` (81–106) marca `$cart_processed = true` y llama a `apply_category_discount`, que sale sin descontar por esa misma marca (36–38). El `set_price` nunca corre; el descuento del carrito depende solo del filtro `woocommerce_product_get_price`. Además, si la regla no excluye ofertas, el descuento de categoría se suma sobre el precio de oferta.
- **Corrección:** decidir con el cliente si el descuento de categoría se acumula con la oferta. Dejar un solo camino (el filtro de precio o `set_price`, no los dos) y una función interna de cálculo sin la marca. Vaciar `wcd_discount_rules_cache` al guardar reglas.
- **Prueba:** tres casos en local con el 5 % actual: producto sin oferta, producto en oferta con regla que excluye ofertas y sin exclusión. Precio de la ficha, carrito y pedido iguales.

### 4.3 CUIT y condición de IVA validados

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** `checkout/class-fields.php` 15–48 no valida en `woocommerce_checkout_process`. La condición de IVA acepta cualquier texto.
- **Corrección:** validar formato de CUIT (11 dígitos con dígito verificador) o DNI, y que la condición de IVA sea una de las opciones. Decidir con el cliente si son obligatorios.
- **Prueba:** CUIT inválido muestra error y no crea el pedido.

### 4.4 Carrito vacío con el hook de WooCommerce

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `woocommerce/cart/cart-empty.php` no dispara `woocommerce_cart_is_empty`.
- **Corrección:** llamar `do_action('woocommerce_cart_is_empty')` antes del mensaje y poner la cabecera `@version` de WooCommerce.
- **Prueba:** carrito vacío se ve igual; los avisos de WooCommerce aparecen.

### 4.5 Reemplazos de widgets Pro solo si Pro no está

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** `class-elementor-fallback.php` 52–71 da de baja y reemplaza los widgets de WooCommerce siempre.
- **Corrección:** registrar los reemplazos solo si Elementor Pro no está activo.
- **Prueba:** con Pro apagado, carrito, checkout y cuenta se editan y se ven.

### 4.6 Encabezado de Elementor sin widgets Pro

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** la plantilla 7432 usa `woocommerce-menu-cart`, que es de Pro. Depende de que el plugin lo reemplace.
- **Corrección:** cambiar en Elementor ese widget por un Shortcode `[lunic_cart_link]` (shortcode nuevo que imprime `lunic_cart_link()`), o que el plugin registre también el reemplazo de `woocommerce-menu-cart` con un marcador en el editor como los demás.
- **Prueba:** en el editor de Elementor el ícono se puede seleccionar; en la tienda se ve con el contador.

### 4.7 Propiedades del método de envío (PHP 8.2)

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `shipping/class-method.php` 25–29 crea `min_amount`, `cost`, `carry_*` sin declararlas. PHP 8.2 avisa como obsoleto.
- **Corrección:** declarar las propiedades en la clase.
- **Prueba:** con `WP_DEBUG` encendido, el carrito no deja avisos en `debug.log`.

### 4.8 Ofertas del inicio con orden estable

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `front-page.php` 34–41 pide productos al azar y la caché congela esa selección 15 minutos.
- **Corrección:** orden por fecha o por popularidad.
- **Prueba:** el inicio muestra las mismas ofertas en recargas seguidas.

### 4.9 Ventanas y galerías de Elementor

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `style.css` 97–98 esconde `.elementor-popup-modal` y `.elementor-lightbox` en toda la tienda.
- **Corrección:** confirmar si se usa la ampliación de fotos de Elementor. Si se usa, acotar la regla a la ventana de categorías vieja.
- **Prueba:** al tocar una foto en una página de Elementor, se comporta como se decidió.

## Etapa 5. Mantenimiento del código

### 5.1 IDs fijos

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Problema:** menús 16, 102–115 (`functions.php` 27–32, se escriben en cada carga si faltan), página 12340 (`functions.php` 269, `page.php` 6), logo 64 (`functions.php` 294), WhatsApp en `header.php` 28 y `footer.php` 19, datos de contacto en `page-contacto.php` 12–15, enlace de OneDrive en `front-page.php` 30, CSS atado a `.elementor-7421`, `.elementor-7825`, `.elementor-7620`.
- **Corrección:** pasar teléfono, email, dirección, lista de precios y página de categorías a una pantalla de ajustes del plugin. Asignar menús solo desde Apariencia → Menús. CSS con clases propias puestas en las plantillas.
- **Prueba:** cambiar el teléfono desde el escritorio y verlo en encabezado, pie y contacto.

### 5.2 Un solo filtro `template_include`

- **Modelo:** Grok 4.6 medium. **Respaldo:** Grok 4.7 high.
- **Corrección:** es la misma función de 4.1. Se hace junto.

### 5.3 Plantillas de WooCommerce con versión

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `cart-empty.php`, `single-product/meta.php`, `content-single-product.php` y `loop/no-products-found.php` no tienen `@version`. `single-product/tabs/description.php` tiene la versión del tema.
- **Corrección:** cabecera `@version` igual a la de WooCommerce de la que se copió, para que WooCommerce → Estado avise al quedar viejas.
- **Prueba:** WooCommerce → Estado → Plantillas no muestra plantillas desactualizadas.

### 5.4 Descripción con la API de WordPress

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `lunic_product_description_html()` (`functions.php` 417–429) lee `post_content` con `$wpdb`.
- **Corrección:** `get_post_field('post_content', $id, 'raw')`.
- **Prueba:** Blend Nº 39 y SIN TEINA Nº 5 se ven igual que hoy.

### 5.5 Traducciones sin filtro global

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Problema:** `functions.php` 317–330 corre en cada texto de WooCommerce.
- **Corrección:** limitar el filtro a carrito y checkout, o llevar los textos a un archivo de traducción.
- **Prueba:** «Actualizar el carrito de compras», «Total de la compra» y «Finalizar Compra» siguen igual.

## Etapa 6. Publicación y cierre

### 6.1 Paquete y subida

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Pasos:**
  1. Copia de la base y de las dos carpetas en el live.
  2. Subir juntas `wp-content/themes/lunic/` y `wp-content/plugins/distribuidora-lunic/`.
  3. Entrar al escritorio una vez (corren las migraciones).
  4. Vaciar `wp-content/cache/lunic/`.
  5. Recorrer como visitante: inicio, tienda, una ficha, carrito con producto, checkout, mi cuenta, contacto. En 1366 px y 390 px.

### 6.2 Documentación

- **Modelo:** Composer 2.5. **Respaldo:** Grok 4.6 medium.
- **Corrección:** actualizar `documentos/estado.md` con las versiones finales, y el manual del cliente con dónde se cambian textos e importes de envío.

## Orden recomendado

1. Etapa 1 completa. 1.1 resuelve el carrito en el live en el día.
2. Etapa 2.1 y 2.2 (envíos).
3. Etapa 3.1 y 3.2 (REST y cabeceras), después el resto de la etapa 3.
4. Etapa 4.1 y 4.2.
5. Lo demás.
