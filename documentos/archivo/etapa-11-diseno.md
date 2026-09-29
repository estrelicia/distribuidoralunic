# Etapa 11 — el mismo diseño

Fecha: 25/09/2026. Corte de la etapa 12: 26/09/2026.

Hasta el corte, el sitio público seguía con el tema **Hello Elementor Child** y con Elementor, Elementor Pro, JetEngine, Ivory Search y Product Filter (WBW) activos. Esas plantillas fueron la referencia. El tema `lunic` se miró con `?lunic_preview=1` hasta la 11.12. Desde el 26/09/2026 el tema público es `lunic`. Elementor Pro, JetEngine, Ivory Search y Product Filter están desactivados. Elementor libre sigue activo. No se copió PHP, JS ni CSS de esos plugins: el HTML y el CSS están reescritos en el tema.

Las capturas de referencia están en `documentos/linea-base/diseno/`. El índice es el README de esa carpeta.

Versiones al cerrar 11.10 y 11.11: tema `lunic` 0.3.6, plugin `distribuidora-lunic` 0.1.3.

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

## 11.8 Carrito, checkout y Mi cuenta

Carrito con productos (Blend Nº 1, 250 gr, $9.006, envío CABA $4.700). No hay título de página. La tabla va en un recuadro verde: Producto, Precio, Cantidad, Subtotal en `#055902`, quitar en `#E42886`. El botón «Actualizar el carrito de compras» ocupa el ancho de la tabla y queda verde pálido cuando está deshabilitado. No hay campo de cupón. «Total de la compra» va debajo, del mismo ancho. El Total de WooCommerce es $10.897,26 (subtotal $9.006 + IVA $1.891,26). El envío $4.700 se imprime otra vez después de ese total y no entra en él. «Finalizar Compra» ocupa el ancho de esa caja.

El acordeón de envíos se abre en el carrito y en el checkout («− Envíos»). En el inicio sigue cerrado. El carrito vacío mantiene «Tu carrito está vacío.», el botón gris «Volver a la tienda» y el acordeón abierto.

Checkout: «Datos de facturación», etiquetas verdes, campos con fondo `#f6f7f7`, Nombre y Apellido, CUIT/CUIL o DNI, Condición frente al IVA, «Dirección de envío (si es diferente a la dirección de facturación)», notas, transferencia bancaria directa y contra reembolso, y «Realizar el pedido». Desde 900 px los datos quedan a la izquierda y «Tu pedido» a la derecha. Por debajo de 782 px van en una columna, igual que la plantilla medida a 869 px.

Mi cuenta no tiene título de página. En escritorio, «Iniciar sesión» y «Registrarme» van lado a lado, con etiquetas y botones verdes, «Recordarme» y «¿Olvidaste la contraseña?». En 390 px una columna queda debajo de la otra.

## 11.9 Contacto e institucionales

Contacto (página 53): título verde centrado, Nombre, Correo electrónico, Teléfono, Consulta y «Enviar» a todo el ancho del formulario. Los cuatro datos usan los PNG de `uploads/2022/12/` (teléfono, correo, dirección, horario). El teléfono de esta página es `(011) 15 3560-0573`. El fondo es `uploads/2023/01/fondeado_cereales.jpg`. El mapa mide 40 vh y apunta a Av. José María Moreno 1280. En el dominio `.dev` Google puede tapar el mapa con el aviso de dominio; la referencia lo muestra igual.

Quiénes somos no agrega un título extra: Elementor sigue pintando «Sobre Nosotros», el texto y las fotos. Cookies y privacidad muestran el título de la página en negro (Hello no lo oculta en esas dos) más la imagen y el texto de Elementor.

En 390 px el contacto apila el formulario y los datos. El carrito, el checkout y Mi cuenta también pasan a una columna, con el menú hamburguesa.

## 11.10 Grilla de categorías (página 12340)

Shortcode del plugin en la página Elementor 12340 (`?lunic_preview=1`). Treinta y nueve categorías de producto, sin el término 15 («Sin asignar»). Orden por nombre descendente, igual que Jet en vivo: la primera tarjeta es Yuyos materos (sierras cordobesas).

Cada tarjeta es horizontal: imagen a la izquierda (meta `imagen-web` del término o placeholder de WooCommerce si no hay imagen), nombre a la derecha. Borde redondeado 10 px, hover de fondo `#E6E6E6`. En escritorio la grilla usa cinco columnas; por debajo de 782 px, dos columnas (comprobado a ~390 px frente a `ref-categorias-390.png`).

## 11.11 Montserrat y kit visual

El tema carga Montserrat 400, 700 y 900 desde Google Fonts con `display=swap`. Con Elementor activo, el kit global (post 22) sigue imponiendo Noto Sans en `body`; el CSS del tema fuerza `var(--lunic-font)` en `body.theme-lunic`, enlaces del encabezado, pie, panel móvil, páginas `.lunic-page` y tarjetas de categorías.

En el navegador (preview): el menú, los títulos de ficha (`font-weight: 900`), los precios en verde `#055902` y los botones de tienda/ficha con fondo `#AADB1F` y texto `#055902`. Los botones principales miden al menos 44 px de alto (p. ej. «Agregar al carrito» y «Seleccionar opciones»).

## 11.12 Lista firmada

Fecha: 26/09/2026. Comparación del tema `lunic` (`?lunic_preview=1`, antes del corte) contra `documentos/linea-base/diseno/ref-*.png`.

Diferencias de maquetación: ninguna. No vuelve ninguna pantalla a su tarea de la etapa 11.

Notas que no son diferencias de diseño: el carrito de la sesión de prueba muestra $9.006 y la captura del encabezado se tomó en $0,00; los cinco productos de «En oferta» cambian en cada carga.

## Etapa 12

El 26/09/2026 se activó `lunic` y se desactivaron Elementor Pro, JetEngine, Ivory Search y Product Filter. Elementor libre quedó activo. Las carpetas no se borraron. El sitio público ya no usa `?lunic_preview=1`.
