# Análisis y plan: plugin Distribuidora Lunic

Fecha del relevamiento: 25 de septiembre de 2026.  
Entorno analizado: copia local `distribuidoralunic.com.ar` (Laragon), base `distribuidoralunic.com.ar`, sitio `https://distribuidoralunic.com.ar.dev/`.  
Tema activo: Hello Elementor Child (`hello-theme-child-master`) sobre Hello Elementor.  
WooCommerce 11.1.2. Moneda ARS. País por defecto Argentina (CABA). Portada: página Inicio (ID 77). Tienda: ID 6.

Este documento describe el estado real del sitio, qué funcionalidad hay que conservar al sacar los plugins pagos o de terceros, cómo unificarla en un plugin propio y en qué orden implementarla. Cada tarea tiene una IA de Cursor asignada.

## 1. Conclusión

Sacar Elementor Pro, JetEngine, Ivory Search y Product Filter for WooCommerce (WBW) sin perder la tienda es viable, porque el sitio usa una porción acotada de cada uno. No es viable copiar su código: son productos con licencia. Hay que reescribir, en código propio, solo lo que esta tienda usa de verdad.

Elementor Pro no se puede desactivar el primer día. Hoy sostiene el header, el footer, la ficha de producto, el archivo de tienda, el carrito, el checkout, Mi cuenta y el megamenú de categorías. Si se apaga antes de tener reemplazo, la tienda deja de verse y de cobrarse.

El camino que preserva las funciones y además acerca el sitio a un PageSpeed alto es este:

1. Crear el plugin `Distribuidora Lunic` y mudarle primero la lógica propia (envíos, descuentos, campos de checkout).
2. Reemplazar búsqueda, filtro de categorías y los dos listados de JetEngine.
3. Construir un tema propio liviano que reemplace las plantillas de Elementor Pro.
4. Recién entonces desactivar los cuatro plugins.
5. Ajustar móvil y rendimiento sobre esa base, sin el peso de los constructores.

Un 100 % estable en PageSpeed Insights (móvil y escritorio, en cada medición) no se puede prometer para un catálogo de 691 productos y 1.975 variaciones. Sí es realista, después del corte, apuntar a 90–98 en móvil y 95–100 en escritorio en las plantillas principales (inicio, tienda, ficha, carrito). Con Elementor Pro y JetEngine activos, ese rango no se alcanza: el HTML, el CSS y el JavaScript de los constructores son el costo dominante.

## 2. Plugins activos hoy

| Plugin | Versión | Rol en este sitio |
| --- | --- | --- |
| WooCommerce | 11.1.2 | Tienda. Se mantiene. |
| Elementor | 4.1.4 | Maquetado de páginas y kit global. |
| Elementor Pro | 3.33.2 | Theme Builder, WooCommerce, popup, formulario, slides, menú. |
| JetEngine | 3.8.0 | Dos listings y un campo de imagen en categorías. |
| Ivory Search | 5.5.18 | Búsqueda AJAX de productos en inicio y tienda. |
| Product Filter for WooCommerce (WBW) | 3.1.5 | Un filtro: categorías, jerárquico, AJAX. |
| Envíos Personalizados WooCommerce | 1.8 (constante interna 1.0.6) | Método `custom_shipping` por zona. |
| Descuentos por Categoría | 1.1 (constante interna 1.0.4) | 11 reglas de −5 %. |
| Easy WP SMTP | — | Correo. Se mantiene, fuera de este plugin. |
| reSmush.it | — | Compresión de imágenes. Se mantiene hasta tener pipeline propio. |
| Prime Mover | — | Migraciones. No forma parte de la tienda pública. |
| MainWP Child | — | Administración remota. No forma parte de la tienda pública. |
| White Label CMS | — | Marca del escritorio. No forma parte de la tienda pública. |

Hay un desajuste de versiones: Elementor libre está en 4.1.4 y Elementor Pro en 3.33.2. Conviene no actualizar Pro a ciegas durante la migración; el reemplazo evita depender de ese par.

## 3. Qué está construido con cada plugin

### 3.1 Páginas y plantillas que el visitante ve

| Superficie | ID | Qué la dibuja | Widgets o piezas usadas |
| --- | --- | --- | --- |
| Header global | 7432 | Elementor Pro, condición `include/general` | Logo, menú Pro, mini carrito Woo, un ícono |
| Footer global | 7421 | Elementor Pro, `include/general` | Logo, menú, títulos, texto, icon box |
| Megamenú categorías | 8261 | Popup de Elementor Pro, ancho 50 vw, abre con `a[href="#menu_categorias"]` | Tres menús: Categorías 01, 02 y 03 (10, 12 y 11 ítems) |
| Inicio | 77 | Elementor (mezcla libre y Pro) | Slides Pro, botón, acordeón, títulos, widget de productos Woo, shortcode Ivory Search |
| Quiénes somos | 45 | Elementor libre | Títulos, texto, imagen, image box, pestañas |
| Contacto | 53 | Elementor | Título, formulario Pro, image box, Google Maps |
| Cookies / privacidad | 1146, 3 | Elementor libre | Imagen y texto |
| Carrito | 7 | Elementor Pro | Avisos Woo, carrito Woo, acordeón |
| Checkout | 8 | Elementor Pro | Checkout Woo, acordeón |
| Mi cuenta | 7575 | Elementor Pro | Widget Mi cuenta |
| Ficha de producto | 7620 | Elementor Pro, condición `include/product` | Miga, galería, título, meta, extracto, precio, widget de descuento por categoría, agregar al carrito, pestañas |
| Archivo de tienda | 7825 | Elementor Pro, `include/product_archive` | Título de archivo, miga, shortcode Ivory Search, widget `woofilters`, loop de productos Pro |
| Mantenimiento | 7348 | Elementor | Imagen y títulos. No está asignada como portada. |
| Página 12340 | 12340 | JetEngine | Un `jet-listing-grid` (título oculto). No es una página de menú principal. |

El kit global (ID 22) define la identidad visual: Montserrat, primario `#4B4B4B`, acento `#AADB1F`, texto `#2F2F2F`, verde de tipografía `#055902`, fucsia `#E42886`. Esas variables tienen que pasar al tema nuevo. Si no, el sitio “funciona” pero no se ve igual.

Menús de WordPress: Inicio (6 ítems, ubicación `menu-1`), Celular (6), Footer (`menu-2`), y los tres de categorías del popup. El tema hijo solo registra esas dos ubicaciones clásicas; el header real lo pinta Elementor Pro, no `header.php`.

El CSS del tema hijo está comentado en `functions.php` (el `add_action` de `hello_elementor_child_enqueue_scripts` no corre). Los estilos visibles viven en Elementor, no en `style.css`.

### 3.2 Elementor Pro: funciones que hay que reemplazar

Inventario de widgets en contenido publicado (no en revisiones):

De Pro, y por eso se rompen si se apaga Pro:

- Theme Builder: header, footer, ficha, archivo de productos.
- Popup del megamenú.
- `slides` en la portada.
- `form` en Contacto.
- `nav-menu` (header, footer, popup).
- Widgets WooCommerce: mini carrito, carrito, checkout, mi cuenta, miga de pan, galería, título, precio, meta, extracto, agregar al carrito, pestañas de datos, loop de archivo, título de archivo.

De Elementor libre, reescribibles en HTML/CSS del tema:

- Secciones y columnas (el experimento Container está inactivo: el sitio sigue en el modelo section/column).
- `heading`, `text-editor`, `image`, `image-box`, `tabs`, `accordion`, `button`, `icon`, `icon-box`, `google_maps`, `shortcode`.

No hace falta clonar el editor de Elementor. Hace falta que esas pantallas se vean y se comporten igual para el cliente.

### 3.3 JetEngine: uso real, muy chico

| Pieza | Uso |
| --- | --- |
| Listing `ListingProductos` (7616) | Fuente: productos. Tipo Elementor. El listado de términos de categoría es el que está enganchado a la página 12340. |
| Listing `ListadoCategoriasProductos` (12348) | Fuente: términos de `product_cat`. Tarjeta con imagen, hover gris, borde 10 px y `jet-listing-dynamic-terms`. |
| Meta de taxonomía `imagen-web` | Campo media sobre `product_cat`. Hay 5 términos con valor. |
| CPT `configuraciones` | Definido en JetEngine (campo WYSIWYG `envios`). No hay posts publicados de ese tipo. La opción `configuraciones` sigue en `wp_options` como resto. |

JetEngine no está modelando el catálogo. El catálogo es WooCommerce normal: 691 productos publicados, 1.975 variaciones, un atributo (`pa_productos-granel`, “Productos a Granel”) y categorías de producto (las más grandes: Ofertas 205, Hierbas medicinales 120, Condimentos y Especias 92).

Reemplazo: un módulo de grilla de categorías que lee `product_cat` y el meta `imagen-web`, más la portada de categoría de WooCommerce cuando no hay meta. El CPT `configuraciones` puede quedar como página de opciones del plugin nuevo, no como un custom post type.

### 3.4 Ivory Search: uso real

Hay cuatro formularios guardados. Los que importan al frente son los AJAX:

| Formulario | ID | Comportamiento |
| --- | --- | --- |
| Custom Search Form | 8888 | Sin AJAX. Incluye posts, páginas, adjuntos y productos. |
| Default Search Form | 8889 | Sin AJAX. Posts, páginas y productos. |
| AJAX Search Form | 8890 | AJAX, imagen, extracto de 20 palabras, alto máximo 400 px. |
| AJAX Search Form for WooCommerce | 8891 | Solo productos. AJAX, imagen, extracto, categorías. Índice de productos, categorías y el atributo a granel. |

Inicio y el archivo de tienda insertan el shortcode `[ivory-search id="…"]`. El índice propio está en `wp_is_inverted_index`. La opción `is_index` limita la indexación a productos y a taxonomías de catálogo.

Reemplazo: un buscador de productos con sugerencias AJAX (título, imagen, precio, categoría) y una página de resultados de WooCommerce. No hace falta indexar posts ni adjuntos: la tienda busca productos.

### 3.5 WBW Product Filter: uso real

Hay un solo filtro, id 1, título “Productos”. Está en el archivo de tienda (widget `woofilters`). Configuración que el cliente percibe:

- Visible en escritorio y móvil.
- AJAX encendido.
- Oculta categorías sin productos.
- Una sola dimensión: categorías, lista vertical jerárquica, 1 columna, alto máximo 700 px.
- Muestra la cantidad de productos.
- Oculta vacías.
- Excluye el término 15.
- Ítems marcados arriba y en negrita.
- Filtra también por variaciones.
- Lógica entre filtros: AND (hoy hay un solo grupo).
- Texto vacío: “No se encontraron productos”.
- El botón “Aplicar” existe en la config pero el filtrado automático de botón no es el modo principal (`show_filtering_button` = 0, `enable_ajax` = 1).
- Ancho 100 % también en móvil.

No se usan precio, atributos, etiquetas ni búsqueda dentro del filtro. El atributo “Productos a Granel” no está en este filtro.

Reemplazo: un filtro de categorías jerárquico, con conteo, exclusión del término 15, AJAX sobre el loop de la tienda y estado en la URL (para que el resultado se pueda compartir y lo indexe Google).

Las tablas `wp_wpf_*` (la de metadatos pesa 3,38 MB) y las tablas viejas `wp_woof_*` son de este filtro y de un WOOF anterior. Se pueden retirar cuando el filtro nuevo esté en producción.

### 3.6 Envíos Personalizados (se absorbe, no se tira)

Método `custom_shipping`. El costo se muestra después del total y el plugin intenta dejarlo fuera del total del pedido. Hay modo normal (gratis desde un mínimo, si no un precio fijo) y modo acarreo (gratis desde otro mínimo; si no, un costo de acarreo “hasta la empresa de transporte”).

Zonas y métodos habilitados hoy:

| Zona | Métodos activos |
| --- | --- |
| CABA (10) | `table_rate`, `envio_fijo_descuento`, `custom_shipping` |
| Nacional (7) | `envio_fijo_descuento`, `custom_shipping` |
| Provincia de Buenos Aires (15) | ambos custom |
| Primer, Segundo y Tercer Cordón (11, 12, 14) | ambos custom |

Instancias vivas de `custom_shipping`:

| Instancia | Título | Modo | Números |
| --- | --- | --- | --- |
| 36 | Envío Nacional | Acarreo | acarreo $4.700; gratis desde $100.000 |
| 37 | Provincia Buenos Aires | Acarreo | acarreo $4.700; gratis desde $100.000 |
| 38 | 1er Cordón | Normal | $8.000; mínimo de gratis en $5.000.000 (en la práctica, nunca gratis) |
| 39 | Envío CABA | Normal | $4.700; gratis desde $100.000 |
| 40 | 2do Cordón | Normal | $10.000; mínimo $5.000.000 |
| 41 | 3er Cordón | Normal | $12.000; mínimo $5.000.000 |

Siguen activos métodos de plugins que ya no están en la carpeta: `envio_fijo_descuento` y `table_rate` (prioridad “acarreo”). El cliente puede estar viendo más de una opción de envío por zona. Al unificar, el plugin nuevo debe ser el único método en esas zonas. Las tarifas de arriba se copian como están; no se “corrigen” los mínimos de $5.000.000 salvo que el negocio lo pida. Esos números parecen un tope para que el envío nunca sea gratis en los cordones.

El tema hijo, además, oculta el resto de métodos cuando existe `free_shipping`. Ese método nativo no es el que está configurado; la regla casi no actúa sobre `custom_shipping`.

Compatibilidad ya declarada: HPOS y bloques de carrito/checkout. Hay tablas `wp_wc_orders` (HPOS) y también pedidos clásicos en `wp_posts` (1.508 completados, 423 cancelados, 118 en espera, 26 en proceso). El módulo de envíos tiene que seguir leyendo pedidos por la API de WooCommerce, no por `wp_posts` directo.

### 3.7 Descuentos por categoría (se absorbe)

Opción `wcd_discount_rules`. Once reglas, todas descuento 5 % porcentual, prioridad 1, y excluyen productos que ya tienen precio de oferta:

Blend ICE TEA, Blend sin teína, Blends Te rooibos, Blends de té, Blends green tea, Blends importados, Blends oolong, Tisanas y topping, Yuyos materos, Oferta TEMPORAL, Blends frutales.

Comportamiento a conservar tal cual:

- Se aplica la primera regla cuya categoría coincida, ordenada por prioridad.
- El cálculo sale del precio original, no se encadena.
- En productos simples, el HTML muestra precio tachado y precio nuevo si el resultado es menor.
- En variables, no se reescribe el HTML del precio (WooCommerce muestra el rango y el precio de la variación).
- El carrito recalcula en `woocommerce_before_calculate_totals`.
- Hay un widget de Elementor en la ficha (`wcd_category_discount`) con AJAX al cambiar de variación.

Ese widget pasa a ser un bloque del template de ficha del tema, no un widget de Elementor.

### 3.8 Lógica que hoy vive en el tema hijo

`functions.php` del child theme, y que el plugin debe absorber para que el tema pueda ser solo presentación:

- Cabeceras de seguridad (frame, nosniff, XSS, referrer, CSP `upgrade-insecure-requests`, HSTS, Permissions-Policy).
- XML-RPC apagado, feeds apagados, generator y enlaces extra del head quitados, `wp-embed` quitado.
- REST API de `/wp/` cerrada para quien no inició sesión.
- Etiqueta de oferta traducida a “Oferta”.
- Emails transaccionales diferidos en el checkout.
- Checkout: campos CUIT/CUIL o DNI y condición frente al IVA (Consumidor Final, Monotributo, Responsable Inscripto, no Inscripto, no Responsable, Exento). Se guardan como meta del pedido y se muestran en el admin.
- Ocultar otros envíos si hay `free_shipping`.

El cierre total de la REST API para anónimos choca con bloques y con algunas llamadas de WooCommerce en el front. Al pasar a un tema clásico con AJAX propio hay que permitir solo las rutas que el buscador, el filtro y el carrito necesitan, y dejar cerrado el resto. No conviene copiar el cierre ciego si el checkout nuevo usa Store API.

## 4. Catálogo y base de datos (impacto en velocidad)

| Dato | Valor |
| --- | --- |
| Productos publicados | 691 |
| Variaciones | 1.975 |
| Adjuntos | 2.487 |
| Revisiones | 2.951 |
| Pedidos clásicos completados | 1.508 |
| Autoload | 0,25 MB en 1.291 opciones (aceptable; hay basura de plugins viejos) |

Tablas más pesadas:

| Tabla | Tamaño | Nota |
| --- | --- | --- |
| `wp_postmeta` | 30,55 MB | Elementor, Jet, revisiones |
| `wp_woocommerce_order_itemmeta` | 19,55 MB | Pedidos. No tocar. |
| `wp_posts` | 18,33 MB | Incluye 2.951 revisiones |
| `wp_mainwp_child_changes_meta` | 14,55 MB | MainWP. No es la tienda. |
| `wp_wffilemods` | 9,44 MB | Wordfence ya no está instalado |
| `wp_e_submissions_values` | 8,39 MB | Formularios Elementor |
| `wp_wc_order_product_lookup` | 7,83 MB | WooCommerce |
| `wp_e_submissions` | 5,08 MB | Formularios Elementor |
| `wp_wfknownfilelist` | 4,52 MB | Wordfence residual |
| `wp_options` | 4,19 MB | Incluye restos de Ultimate Member, ElementsKit, Jetpack, WOOF, YITH, BetterDocs, PDF invoices |
| `wp_wpf_meta_data` | 3,38 MB | Índice del filtro WBW |

Esa basura no rompe la tienda, pero alarga backups y algunas consultas de opciones. Se limpia en la etapa de rendimiento, con backup previo, y solo tablas u opciones de plugins que ya no existen.

## 5. Licencias y límite de la “funcionalidad exacta”

Elementor Pro, JetEngine, Ivory Search y WBW no se reempaquetan dentro del plugin nuevo. Copiar sus PHP, JS o CSS viola la licencia y además arrastra el peso que se quiere sacar.

“La misma funcionalidad” significa, para el cliente de Distribuidora Lunic:

- Las mismas pantallas de compra: inicio, tienda, categoría, ficha, carrito, checkout, mi cuenta, contacto, institucionales.
- El mismo buscador AJAX de productos.
- El mismo filtro de categorías con conteo y AJAX.
- Las mismas 11 reglas de descuento y la misma presentación de precio.
- Las mismas seis tarifas de envío, con acarreo donde hoy está activo.
- Los mismos campos fiscales del checkout.
- La misma identidad (colores, Montserrat, logo, menús, megamenú de categorías).

No significa el editor visual de Elementor, el CPT genérico de JetEngine, ni los otros tres formularios de Ivory que no están en el header. Esas herramientas de backoffice se reemplazan por pantallas de ajustes del plugin (envíos, descuentos, buscador, filtro, datos de contacto y textos institucionales editables).

## 6. Arquitectura propuesta

Un plugin y un tema. El plugin concentra la lógica y las opciones. El tema concentra el HTML. Meter las plantillas solo dentro del plugin (sin tema) pelea con WordPress: header, footer, ficha y archivo los resuelve el tema. Por eso el tema es parte del mismo entregable, delgado, y el plugin lo declara como dependencia.

```
wp-content/plugins/distribuidora-lunic/
  distribuidora-lunic.php
  includes/
    class-plugin.php
    class-settings.php
    modules/
      shipping/          método custom_shipping y panel de zonas
      discounts/         reglas y precio en ficha/carrito
      checkout/          CUIT y condición IVA
      search/            AJAX de productos
      catalog-filter/    categorías jerárquicas
      categories/        imagen-web y grilla
  assets/css, assets/js  solo lo que usa cada pantalla
  templates/             overrides Woo que el tema puede copiar

wp-content/themes/lunic/
  style.css, functions.php, header.php, footer.php
  front-page.php, page.php
  woocommerce/           archivo, ficha, carrito, checkout, mi cuenta
```

Opciones del plugin (una sola pantalla “Lunic” con pestañas):

- Envíos: por zona, modo normal o acarreo, mínimos y costos. Al activar, deshabilita `envio_fijo_descuento` y `table_rate` en esas zonas después de una confirmación.
- Descuentos: las reglas actuales importadas, editables.
- Checkout: etiquetas de CUIT y condiciones de IVA.
- Buscador: mínimo de caracteres, cantidad de sugerencias, mostrar imagen y categoría.
- Filtro: término excluido (hoy 15), alto máximo, AJAX sí/no.
- Apariencia: colores del kit, logo, teléfono, redes, texto de acarreo.
- Rendimiento: diferir JS no crítico, precarga de Montserrat local.

Módulos independientes. Se puede activar el de envíos mientras Elementor sigue dibujando la ficha. Así cada etapa se prueba sin un corte único.

## 7. Opciones de enfoque

### Opción A, recomendada: tema propio + plugin, y después se apagan los cuatro

Es la única que cumple las dos metas a la vez (mismas funciones de esta tienda, y PageSpeed cerca del techo). El editor visual se pierde. Los textos de Quiénes somos, Contacto, cookies y privacidad se pasan a campos del plugin o al editor de bloques nativo de esas cuatro páginas, que son estáticas.

Esfuerzo: el mayor. Riesgo de regresión: controlable si el corte es el último paso y hay lista de pruebas de compra.

### Opción B: sacar solo JetEngine, Ivory y WBW, y dejar Elementor Pro

Más corta y conserva el editor. La tienda sigue dependiendo de la licencia Pro y del desajuste 4.1.4 / 3.33.2. PageSpeed móvil va a seguir lejos de 90 mientras el header, la ficha y el archivo los genere Pro. No responde al pedido de quitar Elementor Pro.

### Opción C: apagar Pro ya y “ir viendo”

Rompe header, ficha, tienda, carrito, checkout y mi cuenta. No es un plan.

Se implementa la opción A.

## 8. Móvil

Hoy el archivo de tienda apila el filtro (alto 700 px) encima o al lado del loop, el popup de categorías mide 50 vw con animación, y el header de Elementor carga el mini carrito y el menú Pro en cada página. En un teléfono, 700 px de categorías antes de los productos es el problema principal de la tienda.

En el tema nuevo:

- Header sticky bajo: logo, buscador, carrito. Menú en panel a pantalla completa, no en un popup de 50 vw.
- El megamenú de tres columnas pasa a un acordeón de categorías dentro de ese panel. En escritorio puede seguir siendo el panel ancho actual.
- En la tienda, el filtro es un botón “Categorías” que abre un panel y muestra cuántos filtros están activos. El listado de productos queda primero.
- Ficha: galería con swipe, precio y botón de compra visibles sin bajar hasta el final (barra fija inferior con precio y “Agregar”).
- Checkout en una columna, campos grandes, CUIT y IVA juntos, envío explicado en la misma tarjeta del total (el costo sigue mostrado aparte, como ahora).
- Tipografía y botones con área táctil de al menos 44 px.
- Tablas del carrito convertidas en fichas por producto debajo de 782 px.

## 9. PageSpeed

Causas actuales, en orden de impacto:

1. CSS y JS de Elementor, Elementor Pro, JetEngine, Ivory y WBW en páginas donde no hacen falta, y a menudo en todas.
2. Google Fonts (Montserrat) en el kit, con `elementor_font_display = auto`.
3. Galería y loop de productos con variaciones (1.975) y HTML de constructor.
4. Imágenes de catálogo (2.487 adjuntos) sin un esquema único de tamaños modernos (AVIF/WebP) servidos por el tema.
5. Revisiones (2.951) y tablas huérfanas, que no bajan el LCP pero sí el tiempo de backup y de admin.
6. No hay plugin de caché de página en la lista de activos.

Medidas, atadas al tema nuevo y no a otro constructor:

- Una hoja CSS por tipo de página (inicio, tienda, ficha, checkout), sin framework.
- Montserrat autoalojada, `font-display: swap`, solo los pesos 400, 700 y 900 que usa el kit.
- JS del buscador, del filtro y del carrito cargado solo en esas pantallas, en el footer, sin jQuery si el módulo no lo necesita.
- Imágenes con `width`/`height`, `loading="lazy"` fuera del primer pantallazo, y `fetchpriority="high"` en el slide o la foto principal.
- El primer slide de inicio y el logo en el HTML inicial, sin carrusel de JS si alcanza un fade en CSS. Si el carrusel actual tiene varias diapositivas, se conserva el comportamiento con un script mínimo.
- Filtro y búsqueda por `admin-ajax` o REST propia cacheada en transients de corta vida. Conteos de categoría precalculados, no un índice de 3 MB como WBW.
- Caché de página para anónimos (inicio, categorías, fichas). El carrito y el checkout quedan fuera.
- Limpieza de revisiones y de tablas Wordfence / WOOF cuando el negocio confirme que esos plugins no vuelven.
- No prometer 100 en la ficha de un variable con muchas variaciones: el HTML de WooCommerce pesa. El objetivo de esa plantilla es 90+ en móvil.

Medición: PageSpeed Insights y Lighthouse sobre inicio, una categoría, una ficha simple, una ficha variable, carrito y checkout, en móvil y escritorio, antes del corte (línea base) y después de cada etapa de front.

## 10. Riesgos

| Riesgo | Cómo se evita |
| --- | --- |
| Apagar Pro demasiado pronto | El primer corte (7.2) se revirtió porque el tema no se veía igual. El segundo corte es la etapa 12, y solo después de la comparación 11.12. |
| Dos métodos de envío a la vez | El módulo nuevo convive hasta la prueba; en el corte se desactivan `envio_fijo_descuento` y `table_rate`. |
| El costo de envío “fuera del total” cambia el cobro | Se replica la lógica actual y se compara un pedido de prueba por zona (CABA, cordón, nacional con acarreo) contra un pedido igual hecho antes del cambio. |
| Descuento doble en variaciones | Se portan los mismos hooks y la misma exclusión de ofertas individuales. Casos de prueba: simple con regla, variable con oferta, variable sin oferta, producto en dos categorías. |
| REST cerrada rompe el checkout | El buscador y el filtro usan endpoints propios. No se reabre `/wp/v2/*` a anónimos. |
| Pérdida de URLs | Se mantienen slugs (`/tienda/`, ficha, categorías). El filtro nuevo usa query args de WooCommerce (`product_cat` o el parámetro actual del filtro, documentado en la tarea) y redirecciona los parámetros viejos de WBW. |
| Contenido institucional a mano | Quiénes somos, contacto, cookies y privacidad se exportan a HTML del tema antes de apagar Elementor. |
| HPOS y pedidos viejos | Lectura solo por `wc_get_order()`. |

## 11. Plan por etapas

Leyenda de IA, tal como aparecen en Cursor:

- **Cursor Grok 4.7 High**: lógica de negocio, paridad con lo existente, corte y rendimiento.
- **Composer 2.5**: esqueleto, pantallas de admin, migraciones de opciones y tareas bien cerradas.
- **Cursor Grok 4.6 medium**: HTML, CSS y comportamiento en móvil.

Cada tarea se hace en la copia local, con los plugins viejos todavía activos hasta la etapa 7. No se borra código de terceros hasta que la lista de pruebas de esa etapa pase.

### Etapa 0. Línea base

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 0.1 | Backup de archivos y base, y anotación de versiones (este documento ya fija el inventario). | Composer 2.5 | **Hecho 25/09/2026** — `C:\laragon\backups\distribuidoralunic.com.ar\2026-09-25_1318\` (`database.sql`, `files.zip`, `MANIFEST.md`). Detalle: `documentos/etapa-0-tarea-0.1-backup.md`. |
| 0.2 | Recorrido de compra de referencia: inicio, búsqueda, filtro de una categoría, ficha simple, ficha variable, carrito, checkout con CUIT, envío CABA, envío nacional en acarreo, cordón. Guardar capturas y totales. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — `documentos/linea-base/` (README + `capturas/`). Mismo carrito $9.006 + IVA $1.891,26 = Total $10.897,26. Envío **fuera del total**: CABA $4.700; nacional acarreo $4.700; 1er cordón $8.000. |
| 0.3 | Lighthouse móvil y escritorio de inicio, tienda, una ficha y checkout. Anotar LCP, INP, CLS y peso de JS/CSS. | Composer 2.5 | **Hecho 25/09/2026** — Sección 13 y `documentos/linea-base/lighthouse/` (JSON + `summary.json`). |

### Etapa 1. Plugin y lógica propia

Sin cambiar lo que ve el cliente. Elementor sigue en pie.

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 1.1 | Crear `distribuidora-lunic` con cargador de módulos, pantalla de ajustes y declaración HPOS. | Composer 2.5 | **Hecho 25/09/2026** — `wp-content/plugins/distribuidora-lunic/` activo en local; menú **Lunic** en el admin. |
| 1.2 | Mudar Envíos Personalizados: mismas instancias, mismos números, mismo texto de acarreo, mismo criterio de no sumar al total si ese es el comportamiento verificado en 0.2. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — Mismo `custom_shipping` y mismas instancias. Carrito de prueba: total $10.897,26; envío aparte CABA $4.700, cordón $8.000, nacional acarreo $4.700. Detalle: `documentos/etapa-1-tareas-1.2-1.3.md`. |
| 1.3 | Mudar descuentos: importar `wcd_discount_rules`, mismos hooks, mismo HTML de precio, AJAX de variación desacoplado de Elementor. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — 11 reglas de `wcd_discount_rules`. Variación 15498 a $9.006 (−5 %). Una oferta individual no recibe otro 5 %. |
| 1.4 | Mudar campos CUIT/CUIL o DNI y condición IVA, meta de pedido y caja en el admin. | Composer 2.5 | **Hecho 25/09/2026** — Módulo `checkout`; mismas claves de meta. Detalle: `documentos/etapa-1-tareas-1.4-1.5.md`. |
| 1.5 | Mudar cabeceras de seguridad, etiqueta “Oferta” y emails diferidos. Dejar la REST documentada, sin cerrar de más. | Composer 2.5 | **Hecho 25/09/2026** — Módulo `security`; REST en `documentos/rest-api-lunic.md`. |

### Etapa 2. Búsqueda

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 2.1 | Endpoint de sugerencias: productos publicados, título, imagen, precio (con descuento de categoría ya aplicado), categoría, enlace. Mínimo 1 carácter, como Ivory en el formulario 8891. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — `GET /wp-json/lunic/v1/search`. «boldo» y «cardamomo» devuelven productos publicados. |
| 2.2 | Caja visual y shortcode `[lunic_search]`, colocado en paralelo al de Ivory sin quitar el viejo. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — Shortcode en portada y tienda, junto a Ivory. Cierra con Escape y clic afuera. |

### Etapa 3. Filtro de tienda

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 3.1 | Lista jerárquica de `product_cat`, conteo, ocultar vacías, excluir término 15, marcar activos, filtrar variaciones, texto “No se encontraron productos”. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — Blends de té (135) cuenta 52, igual que la línea base. Término 15 excluido. |
| 3.2 | AJAX del loop y query en la URL. Mapa de parámetros viejos de WBW hacia el parámetro nuevo. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — `lunic_cat` en la URL. `wpf_filter_cat_list_0` redirige a ese parámetro. |
| 3.3 | Presentación escritorio (columna) y móvil (panel). Alto máximo 700 px solo en escritorio. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — En menos de 782 px el listado va en un panel; el alto de 700 px queda solo en escritorio. |

### Etapa 4. Lo poco de JetEngine

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 4.1 | Registrar el meta `imagen-web` en categorías de producto y una grilla `[lunic_categorias]` con la tarjeta actual (radio 10 px, hover `#E6E6E6`, imagen o miniatura de la categoría). | Composer 2.5 | **Hecho 25/09/2026** — 5 categorías con `imagen-web`; el resto usa la miniatura de WooCommerce. |
| 4.2 | Sustituir el listing de la página 12340 por esa grilla, sin apagar JetEngine todavía. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — El widget del listing 12348 renderiza `[lunic_categorias]` (39 categorías con productos, sin el término 15). |

### Etapa 5. Tema Lunic (páginas y chrome)

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 5.1 | Tema `lunic`: soportes WooCommerce, logo, menús Inicio, Celular y Footer, colores y Montserrat local del kit. | Composer 2.5 | **Hecho 25/09/2026** — Tema en `wp-content/themes/lunic/`. El sitio sigue con Hello Elementor Child hasta la etapa 7. |
| 5.2 | Header, footer y panel de categorías (los tres menús del popup) en escritorio y móvil. Mini carrito con el número de ítems. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — Header con logo, menús, buscador y carrito. Panel con Categorías 01, 02 y 03. |
| 5.3 | Portada: slider, bloque de productos, acordeón y buscador. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — Mismas imágenes del slider y enlace a la lista de precios. |
| 5.4 | Quiénes somos, contacto (formulario con los mismos campos y reCAPTCHA que ya usa WPForms si sigue siendo el destino; si el formulario vivo es el de Elementor Pro, rehacer esos campos en el plugin), cookies y privacidad. Mapa con la misma dirección, sin API key (el sitio no tiene `elementor_google_maps_api_key`). | Cursor Grok 4.7 High | **Hecho 25/09/2026** — Formulario a info@distribuidoralunic.com.ar. Mapa OpenStreetMap de Moreno 1280. |
| 5.5 | Plantilla de mantenimiento, apagada por opción, por si se vuelve a usar la de ID 7348. | Composer 2.5 | **Hecho 25/09/2026** — Casilla en el menú Lunic. Apagada. |

### Etapa 6. Plantillas WooCommerce

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 6.1 | Archivo de tienda: título, miga, buscador, filtro nuevo, ordenar, loop. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — Con el tema Lunic, Blends de té sigue en 52 resultados, con orden y paginación. |
| 6.2 | Ficha: galería, título, meta, extracto, precio, texto de descuento, agregar al carrito, pestañas, productos relacionados si hoy se muestran en las pestañas de datos. Barra móvil de compra. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — Plantilla de ficha con barra «Agregar» en móvil. |
| 6.3 | Carrito, checkout y mi cuenta con los campos fiscales y el desglose de envío. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — Esas páginas usan los shortcodes de WooCommerce (CUIT/IVA y envío aparte siguen en el plugin). |
| 6.4 | Acordeones que hoy están bajo carrito y checkout: pasar su texto al tema. | Composer 2.5 | **Hecho 25/09/2026** — El acordeón «Envíos» lee la opción `configuraciones`. |

### Etapa 7. Corte

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 7.1 | Activar el tema `lunic` en local. Dejar los plugins viejos activos una pasada, para comparar. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — Tema activo. Inicio, tienda (52 de Blends de té) y ficha de cardamomo cargan. |
| 7.2 | Desactivar Elementor Pro, Elementor, JetEngine, Ivory Search y WBW. Vaciar CSS de Elementor generado. | Cursor Grok 4.7 High | **Hecho y después revertido, 25/09/2026.** El corte se ejecutó. El tema Lunic no reproducía el diseño, así que los cinco volvieron a activarse y el tema volvió a Hello. No se repite hasta cerrar la etapa 11. El segundo corte es la etapa 12. |
| 7.3 | Dejar en las zonas solo `custom_shipping`. Conservar las opciones viejas en un export por si hay que volver atrás. | Cursor Grok 4.7 High | **Hecho 25/09/2026** — Cada zona ya tenía una sola instancia. Copia en `documentos/export-envios-zonas.json`. |
| 7.4 | Quitar los plugins viejos de envíos y descuentos cuando sus módulos pasaron las pruebas 1.2 y 1.3. | Composer 2.5 | **Hecho 25/09/2026** — Carpetas «Envíos Personalizados» y «Descuentos por Categoría» eliminadas del disco. |

### Etapa 8. Móvil y pulido

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 8.1 | Pasada de 390 px y 768 px sobre inicio, tienda, ficha, carrito y checkout. Corregir desbordes, foco y orden de tabulación. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — En 390 px la ficha de cardamomo no desborda. Precio visible y barra «Agregar». |
| 8.2 | Estados vacíos: búsqueda sin resultados, filtro sin productos, carrito vacío, categoría sin imagen. | Cursor Grok 4.6 medium | **Hecho 25/09/2026** — Textos con enlace a la tienda. Categoría sin foto dice «Sin imagen». |

### Etapa 9. Rendimiento

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 9.1 | CSS/JS condicional, fuentes locales, imágenes del primer pantallazo, caché de anónimos. | Cursor Grok 4.7 High | Lighthouse móvil de inicio y de una ficha simple sube respecto de la línea base y queda en el rango 90–98, o queda documentado el recurso que lo impide. |
| 9.2 | Conteos de categoría en transient. Quitar el índice WBW solo después del corte. | Composer 2.5 | Filtrar no recalcula el árbol completo en cada request. |
| 9.3 | Limpieza de revisiones, tablas `wp_wf*`, `wp_woof_*` y opciones autoload de plugins ausentes (Ultimate Member, ElementsKit, Jetpack, BetterDocs, YITH, WOOF). No tocar pedidos ni `wp_mainwp_*` si MainWP Child sigue activo. | Composer 2.5 | Backup previo. El admin y el front cargan. El autoload baja. |
| 9.4 | Segunda medición PageSpeed y ajuste fino (LCP de portada, INP del filtro y del agregar al carrito). | Cursor Grok 4.7 High | Informe corto en `documentos/pagespeed-despues.md` con antes/después. |

### Etapa 10. Hueco para funciones nuevas

El plugin ya tiene módulos y ajustes. A partir de acá entran pedidos futuros (cuenta corriente, listas de precio, mínimo de compra, etc.) como módulos nuevos, sin volver a Elementor.

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 10.1 | Documentar en el propio plugin cómo se registra un módulo (una página en `documentos/modulos.md`, solo cuando haga falta el primero). | Composer 2.5 | Un módulo de ejemplo vacío carga y aparece como pestaña apagada. |

### Etapa 11. El mismo diseño

Las etapas 5, 6 y 8 dejaron armadas las pantallas del tema `lunic`, pero con otra maquetación: otra cabecera, un pie oscuro, un inicio distinto, una tienda distinta y un contacto distinto. Por eso, al cumplir la 7.2, la tienda funcionaba y no se veía igual. Esta etapa no agrega funciones. Copia la apariencia que hoy dibujan Elementor Pro, JetEngine, Ivory Search y Product Filter.

Mientras dura la etapa 11 el sitio público sigue con el tema Hello y con esos cuatro plugins activos. Son la referencia. El tema `lunic` se revisa en una vista aparte. No se activa en el sitio hasta que la comparación de la 11.12 coincida. No se copia el PHP, el JS ni el CSS de esos plugins (sección 5): se reescribe el HTML y el CSS del tema hasta medir y verse igual.

Identidad que ya está medida y no se reinventa: Montserrat; primario `#4B4B4B`; acento `#AADB1F`; texto `#2F2F2F`; verde `#055902`; fucsia `#E42886`; logo adjunto 64. Cada superficie se compara con la plantilla indicada, en escritorio y en 390 px, contra una captura tomada con los cuatro plugins activos.

Detalle de lo cerrado hasta la 11.7: `documentos/etapa-11-diseno.md`.

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 11.1 | Capturas de referencia, con los cuatro plugins activos, de encabezado, megamenú, pie, inicio, tienda, ficha simple, ficha variable, carrito, checkout, Mi cuenta, Contacto, Quiénes somos, cookies, privacidad y la grilla de categorías. Escritorio y 390 px. | Cursor Grok 4.6 medium | **Hecho, 25/09/2026.** Escritorio y 390 px en `documentos/linea-base/diseno/`. El índice está en el README de esa carpeta. El carrito y el checkout de escritorio tienen un producto (Blend Nº 1, $9.006, envío $4.700). En 390 px el checkout redirige al carrito vacío. |
| 11.2 | Encabezado como la plantilla 7432. Logo, menú Inicio, ítem actual con subrayado verde, ícono de WhatsApp, mini carrito con importe y cantidad, línea `#AADB1F`. En el teléfono: logo, botón verde de menú, pastilla del carrito y WhatsApp. | Cursor Grok 4.6 medium | **Hecho, 25/09/2026.** Escritorio: logo, menú, ítem actual con subrayado, carrito con importe y cantidad, WhatsApp y línea `#AADB1F`. En 390 px: logo, botón verde (pasa a X al abrirse) y WhatsApp, como la referencia. El carrito está en el panel. |
| 11.3 | Megamenú como el popup 8261. Se abre desde Categorías, ancho 50 vw, tres columnas (menús 113, 114 y 115). En 390 px pasa al panel del encabezado, con los mismos ítems. | Cursor Grok 4.6 medium | **Hecho, 25/09/2026.** En escritorio se abre desde Categorías, a la derecha, 50 vw, tres columnas y los mismos ítems que el popup 8261. En 390 px el panel muestra Inicio, Categorías, Tienda, Quienes Somos, Contacto y Carrito; Categorías despliega los tres menús. |
| 11.4 | Pie como la plantilla 7421. Barra completa `#AADB1F`, cuatro columnas: logo, menú vertical (Tienda, Mi cuenta), «Horario comercial» y «Contacto» con el teléfono `(011) 15 2354-5375`, el correo y Moreno 1280. | Cursor Grok 4.6 medium | **Hecho, 25/09/2026.** Barra `#AADB1F`, títulos `#055902`, Tienda y Mi cuenta, horario, contacto con WhatsApp, correo y Moreno 1280, franja `#4a4a4a`. En 390 px el logo queda centrado y Tienda / Mi cuenta se abren con el botón del pie, como la referencia. |
| 11.5 | Inicio como la página 77. Mismas diapositivas y el mismo orden, botón «Descargá la Lista de Precios», acordeón «Envíos» con el texto de `configuraciones['envios']`, títulos «Productos de calidad», «Siempre al mejor precio» y «En oferta», fichas de producto como el widget actual, y el bloque «Buscador de productos» sobre la foto de almendras. El buscador es el del plugin, con la misma caja y el mismo comportamiento AJAX. | Cursor Grok 4.7 High | **Hecho, 25/09/2026.** Slider de 695 px y 100vh (56vh en el teléfono), las ocho diapositivas, botón verde, «+ Envíos» cerrado, títulos y lista «En oferta» de cinco productos en oferta. El buscador queda sobre la foto de almendras, con la caja y el botón de lupa. |
| 11.6 | Tienda como el archivo 7825. Título verde, miga `INICIO / TIENDA` con regla verde, buscador, columna de categorías con conteo y scroll, fichas con borde verde, etiqueta fucsia «OFERTA», precio verde, «SIN IVA», botón verde «Seleccionar opciones», cantidad de resultados, orden y paginación. El filtro es el del plugin, presentado como el de WBW. | Cursor Grok 4.7 High | **Hecho, 25/09/2026.** En escritorio: título verde, miga, buscador con lupa, cuatro columnas, OFERTA arriba a la derecha y «Seleccionar opciones». En 390 px la lista de categorías queda a la vista, con Blends de té en 52. |
| 11.7 | Ficha como la plantilla 7620. Miga, galería, título, meta, extracto, precio, texto de descuento, agregar al carrito, pestañas y relacionados. En 390 px, barra fija con precio y «Agregar». | Cursor Grok 4.7 High | **Hecho, 25/09/2026.** Ficha simple: miga, foto, título verde, categoría, video y precio tachado $29.800 / $28.310. Ficha variable sin stock: «Código No disponible», categorías y el aviso en rojo. En 390 px, barra fija con el precio y «Agregar». |
| 11.8 | Carrito (página 7), checkout (página 8) y Mi cuenta (página 7575) con la misma composición que esas plantillas, incluido el acordeón de envíos. CUIT, condición frente al IVA y el costo de envío fuera del total siguen en el plugin. | Cursor Grok 4.7 High | **Carrito vacío listo, 25/09/2026.** «Tu carrito está vacío.», botón gris «Volver a la tienda» y «+ Envíos» cerrado. Faltan el carrito con productos, el checkout y Mi cuenta. |
| 11.9 | Contacto como la página 53. Título verde, formulario con Nombre, Correo electrónico, Teléfono y Consulta, botón verde «Enviar», los cuatro datos con ícono (teléfono `(011) 15 3560-0573`, correo, dirección, horarios) y el mapa de Moreno 1280. Quiénes somos (45), cookies (1146) y privacidad (3) conservan sus bloques, imágenes y textos. | Cursor Grok 4.7 High | **Parcial, 25/09/2026.** Contacto tiene el título, los cuatro campos, «Enviar» a todo el ancho de la columna, los datos y el mapa. Quiénes somos se ve igual porque Elementor sigue pintando esa página en la vista previa. Faltan cookies y privacidad. |
| 11.10 | Grilla de categorías de la página 12340. Mismas tarjetas: radio 10 px, hover `#E6E6E6`, imagen `imagen-web` o miniatura, cinco columnas en escritorio y dos en menos de 782 px. | Composer 2.5 | **Escritorio listo, 25/09/2026.** Tarjetas horizontales, imagen a la izquierda y cinco columnas. El término 15 no aparece. |
| 11.11 | Montserrat en el tema, pesos 400, 700 y 900, `font-display: swap`. Botones, enlaces y fichas usan los colores del kit. Área táctil de al menos 44 px. | Composer 2.5 | Montserrat 400, 700 y 900 se carga desde Google Fonts con `display=swap`. Falta confirmar en el navegador que encabezado, título de ficha y botones computan esa familia. |
| 11.12 | Comparación lado a lado de todas las capturas de 11.1 contra el tema `lunic`. Lista firmada de diferencias. Si una pantalla difiere, vuelve a su tarea de esta etapa. | Cursor Grok 4.7 High | **En curso, 25/09/2026.** El sitio público sigue en Hello, con Elementor, Elementor Pro, JetEngine, Ivory Search y WBW activos. La vista del tema es `?lunic_preview=1`. La 11.1 ya tiene las referencias de escritorio y de 390 px. Ya coinciden el encabezado, el megamenú, el pie, el inicio, la tienda, la ficha simple, la ficha variable y la grilla de categorías. Siguen abiertos el carrito con productos, el checkout, Mi cuenta, cookies y privacidad. |

### Etapa 12. Segundo corte

Solo empieza si la 11.12 cerró sin diferencias. Repite la 7.2 sobre un tema que ya se ve igual.

| ID | Tarea | IA | Listo cuando |
| --- | --- | --- | --- |
| 12.1 | Activar el tema `lunic`. Desactivar Elementor Pro, JetEngine, Ivory Search y Product Filter. Desactivar también Elementor libre: es el motor de esas plantillas y, si queda activo, vuelve a pintar las páginas. No borrar las carpetas en este paso. | Cursor Grok 4.7 High | El HTML público no pide CSS ni JS de esos plugins. El buscador, el filtro y la grilla salen del plugin `distribuidora-lunic`. |
| 12.2 | Repetir las capturas de 11.1 con los plugins apagados y compararlas con la referencia. Recorrer la prueba de la sección 14. | Cursor Grok 4.7 High | Cada pantalla coincide con su captura de referencia. Envíos, descuentos, CUIT e IVA dan los mismos importes de la línea base. |
| 12.3 | Si una pantalla no coincide, reactivar los cuatro plugins y Elementor, volver el tema a Hello y devolver esa pantalla a la etapa 11. No se deja el sitio a medias. | Cursor Grok 4.7 High | O la 12.2 pasó, o el sitio público quedó otra vez como la referencia y la diferencia quedó anotada en la tarea 11 que corresponda. |

## 12. Orden y qué no hacer en paralelo

Secuencia obligatoria: 0 → 1 → (2, 3 y 4 pueden ir en paralelo) → 5 → 6 → 7 → 8 → 11 → 12 → 9 → 10.

La 7.2 no se repite antes de la 11.12. La etapa 9 no empieza con Elementor Pro, JetEngine, Ivory Search o Product Filter activos.

No desactivar Elementor Pro, JetEngine, Ivory Search ni Product Filter en las etapas 1 a 6 ni durante la 11. No borrar tablas de Wordfence ni de WBW antes de la 9.3. No cambiar los importes de envío ni los porcentajes de descuento como “mejora”: eso es una decisión comercial, no técnica.

## 13. Línea base de PageSpeed

Medición **25/09/2026** en `https://distribuidoralunic.com.ar.dev/` con Lighthouse 12 (Chrome headless, categoría Performance). Caché de navegador fría en cada corrida. Los JSON completos están en `documentos/linea-base/lighthouse/`.

**INP:** en estas corridas de laboratorio Lighthouse no reportó INP (no hubo interacción simulada); comparar en etapa 10 con datos de campo si hace falta.

| URL | Dispositivo | Performance | LCP | INP | CLS | JS transferido | CSS transferido |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Inicio (`/`) | móvil | 51 | 12,3 s | — | 0 | 630 KB | 644 KB |
| Inicio (`/`) | escritorio | 55 | 12,6 s | — | 0 | 630 KB | 644 KB |
| Tienda (`/tienda/`) | móvil | 55 | 9,5 s | — | 0,013 | 704 KB | 946 KB |
| Tienda (`/tienda/`) | escritorio | 55 | 8,9 s | — | 0,018 | 704 KB | 946 KB |
| Ficha (cardamomo) | móvil | 53 | 8,5 s | — | 0 | 742 KB | 759 KB |
| Ficha (cardamomo) | escritorio | 55 | 8,0 s | — | 0,002 | 742 KB | 759 KB |
| Checkout (`/finalizar-comprar/`) | móvil | 48 | 12,3 s | — | 0,084 | 569 KB | 630 KB |
| Checkout (`/finalizar-comprar/`) | escritorio | 55 | 8,0 s | — | 0,049 | 569 KB | 630 KB |

## 14. Prueba de aceptación del corte (etapa 12)

La lista de abajo se ejecuta en la etapa 12, con el tema `lunic` y sin los cuatro plugins. Además, cada pantalla tiene que coincidir con la captura de la tarea 11.1. La etapa 7.2 ya se había ejecutado una vez; no vuelve a valer hasta que pase esta comparación.

- Buscar un producto conocido y abrir la ficha desde la sugerencia.
- Filtrar una categoría padre y una hija; el conteo y los productos coinciden con la línea base.
- Producto de “Blends de té” muestra −5 % y el tachado. Producto con precio de oferta dentro de una categoría con regla no recibe otro 5 %.
- Variación cambia el precio y el texto de descuento.
- Carrito: cantidad, quitar, seguir comprando.
- Checkout: CUIT, condición IVA, CABA a $4.700 (gratis si el subtotal llega a $100.000), 1er cordón a $8.000, nacional en modo acarreo.
- El pedido en el admin muestra envío, CUIT e IVA.
- Mi cuenta: ver ese pedido.
- Menú de categorías abre en escritorio y en 390 px.
- Contacto envía el correo.
- No queda en pantalla el texto `[ivory-search]` ni un aviso de widget de Elementor faltante.
- Header, footer y ficha no piden CSS de Elementor en el HTML.
