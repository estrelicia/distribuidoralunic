# Etapa 11 — el mismo diseño

Fecha: 25/09/2026.

El sitio público sigue con el tema **Hello Elementor Child** y con Elementor, Elementor Pro, JetEngine, Ivory Search y Product Filter (WBW) activos. Esas plantillas son la referencia. El tema `lunic` se mira solo con `?lunic_preview=1`. No se activa en el sitio hasta que la tarea 11.12 cierre sin diferencias. No se copió PHP, JS ni CSS de esos plugins: el HTML y el CSS están reescritos en el tema.

Las capturas de referencia están en `documentos/linea-base/diseno/`. El índice es el README de esa carpeta.

Versiones al cerrar 11.5, 11.6 y 11.7: tema `lunic` 0.2.8, plugin `distribuidora-lunic` 0.1.2.

## Cómo se ve el tema sin activarlo

`Distribuidora_Lunic\Plugin::maybe_preview_theme()` cambia `template` y `stylesheet` a `lunic` cuando la URL trae `lunic_preview=1`, y saca el `template_include` del Theme Builder de Elementor Pro. El `functions.php` del tema, en `elementor/theme/register_locations`, quita las acciones de `get_header` y `get_footer` para que Elementor no se quede con la cabecera y el pie. Un `template_include` en prioridad 99999 fuerza las plantillas de portada, archivo, ficha y página cuando la hoja de estilos es `lunic`.

El CSS del tema oculta `.elementor-popup-modal` y `.elementor-lightbox`. Si no, al tocar Categorías se abrían a la vez el megamenú de Lunic y el popup 8261.

## 11.1 Referencias

Escritorio a 1440 px y teléfono a 390 px, con los cuatro plugins activos y sin `lunic_preview`.

El carrito y el checkout de escritorio se tomaron con Blend Nº 1, 250 gr, $9.006 y envío CABA $4.700. En 390 px, sin esa sesión, el checkout redirige al carrito vacío: `ref-checkout-390.png` y `ref-carro-390.png` son ese carrito vacío, con el acordeón de envíos abierto.

La ficha variable (Blend Nº 45) está sin stock y la miniatura no carga en el sitio actual. La referencia muestra el recuadro de imagen rota.

## 11.2 Encabezado (plantilla 7432)

Escritorio: logo (adjunto 64), menú Inicio, ítem actual con subrayado `#AADB1F`, carrito con importe y cantidad, WhatsApp `https://wa.me/5491123545375` y la línea verde debajo de la barra.

En 390 px la barra es logo, botón verde (pasa a X) y WhatsApp. El carrito no va en la barra: está dentro del panel, como en `ref-encabezado-390.png`.

## 11.3 Megamenú (popup 8261)

En escritorio se abre desde el enlace Categorías (`#menu_categorias`), a la derecha, ancho 50 vw, tres columnas (menús 113, 114 y 115).

En 390 px el panel usa el menú Inicio: Inicio, Categorías, Tienda, Quienes Somos, Contacto y Carrito. Categorías despliega los tres menús.

## 11.4 Pie (plantilla 7421)

Barra `#AADB1F`, títulos `#055902`, columnas logo, Tienda y Mi cuenta, horario, y contacto con WhatsApp, `(011) 15 2354-5375`, `info@distribuidoralunic.com.ar` y Av. José María Moreno 1280. Franja final `#4a4a4a`.

En 390 px el logo queda centrado. Tienda y Mi cuenta se abren con el botón del pie.

## 11.5 Inicio (página 77)

Medido sobre la página 77 con Elementor todavía activo.

El slider de Elementor mide unos 695 px de ancho y `100vh` de alto en escritorio, y `56vh` en el teléfono. El tema usa ese mismo cajón, con `object-fit: cover`. Las ocho diapositivas, en este orden:

1. `WhatsApp-Image-2026-08-20-at-3.36.24-PM.jpeg`
2. `WhatsApp-Image-2026-08-20-at-3.36.24-PM-1.jpeg`
3. `WhatsApp-Image-2026-07-29-at-4.14.01-PM.jpeg`
4. `Capullos-de-te.jpeg`
5. `Naranja-y-mix-de-citricos-en-rodajas.jpeg`
6. `Retiros-de-mercaderia.jpeg`
7. `Retiro-de-mercaderia.jpeg`
8. `Acarreo-Via-cargo.png`

Debajo: botón «Descargá la Lista de Precios» (el enlace de OneDrive de la página 77) y el acordeón «Envíos» cerrado, con el texto de la opción `configuraciones['envios']`.

«Productos de calidad» va a la derecha, color `#3f5600`. «Siempre al mejor precio» también a la derecha, color `#54595f`. El fondo de ese bloque es el patrón `uploads/2023/01/fondeado.jpg` al 32 % de opacidad.

«En oferta» es el mismo widget que la página 77: `WC_Widget_Products` con cinco productos en oferta, orden al azar y sin productos gratis. Las miniaturas no se muestran. Los cinco nombres cambian en cada carga, igual que en el sitio actual.

«Buscador de productos» va sobre la foto de almendras `uploads/2022/12/pexels-kafeel-ahmed-3997459-1.jpg`. El título es blanco. La caja es el shortcode `[lunic_search]` del plugin, con placeholder `Search here...`, sugerencias AJAX y botón de lupa.

En 390 px se ven la diapositiva, el botón verde, «+ Envíos» cerrado y el arranque de «Productos de calidad».

## 11.6 Tienda (archivo 7825)

Título verde, miga `INICIO / TIENDA` con regla `#AADB1F`, buscador con lupa, «Mostrando 1–16 de 691 resultados», «Ordenar por popularidad» y paginación.

La columna de categorías es el filtro del plugin (`lunic_cat`), con círculo, conteo y scroll. Blends de té sigue en 52. En 390 px esa lista queda a la vista, arriba de las fichas, como en `ref-tienda-390.png`.

Las fichas van en cuatro columnas en escritorio y dos en el teléfono. Borde verde, etiqueta fucsia «OFERTA» arriba a la derecha, precio verde, «SIN IVA» y botón verde «Seleccionar opciones» dentro de la ficha.

## 11.7 Ficha (plantilla 7620)

Orden: miga, galería a la izquierda, título verde, meta, extracto (el video), precio, texto de descuento si hay stock, agregar al carrito, pestañas y relacionados. El video usa la imagen destacada como póster.

Ficha simple (Blend frutal Campestre, 250 gr): precio tachado $29.800 y precio $28.310, más el texto de descuento de la categoría.

Ficha variable sin stock (Blend Nº 45): el recuadro de imagen rota, «Código No disponible», categorías Blends de té y Yuyos materos (sierras cordobesas), y el aviso en rojo «Este producto no está disponible porque no hay stock.». El texto de descuento no se imprime si no hay stock. La etiqueta «Código» sale de `woocommerce/single-product/meta.php` del tema.

En 390 px, si el producto se puede comprar, una barra fija al pie muestra el precio y el botón «Agregar». Ese botón dispara el agregar al carrito de la ficha.

## Qué sigue abierto

La 11.12 no está cerrada. Siguen el carrito con productos, el checkout, Mi cuenta, cookies y privacidad (11.8 y 11.9), la confirmación de Montserrat en el navegador (11.11) y la lista de diferencias vacía (11.12). La etapa 12 no empieza hasta esa lista.
