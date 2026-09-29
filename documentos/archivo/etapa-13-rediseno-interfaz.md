# Etapa 13 — unificar la interfaz

**Fecha:** 28/09/2026.  
**Estado:** 13.1 a 13.8 cerradas el 28/09/2026 en local. El carrito se centró después, en el tema `lunic` 0.3.53.  
**Alcance:** tema `lunic` en local (`https://distribuidoralunic.com.ar.dev/`). No se publica. No se cambia el sitio en vivo.

La etapa 11 dejó la misma estructura que el sitio de referencia: cabecera, pie, inicio, tienda, ficha, carrito, checkout, cuenta, contacto y grilla de categorías. Esta etapa no cambia esa estructura ni la marca. Unifica tipografía, botones, campos, tarjetas, espaciado y jerarquía para que todas las pantallas usen los mismos patrones, en escritorio y en el teléfono.

Referencia visual: `documentos/linea-base/diseno/` y el cierre de `documentos/etapa-11-diseno.md`. Colores que se mantienen: verde `#055902`, acento `#AADB1F`, texto `#2F2F2F`, fucsia `#E42886` solo para oferta y quitar, WhatsApp `#368D00`, gris de apoyo `#4B4B4B`. Fuente: Montserrat 400, 700 y 900.

## Qué no se toca

- Porcentajes de descuento y textos de descuento por categoría.
- Montos de envío (CABA $4.700, primer cordón, acarreo). El envío sigue fuera del total de WooCommerce.
- Pedidos, `wp_mainwp_*`, opción `lunic_theme_templates`.
- Carrito de prueba: Blend Nº 1, 250 gr. No agregar Hibiscus ni otro producto.
- Elementor Pro, JetEngine, Ivory Search y Product Filter siguen apagados. Elementor libre puede seguir activo.
- No reescribir el contenido de los productos. Los `????` guardados en la base no son de esta etapa.
- No commitear ni desplegar salvo que se pida aparte.

## Cortes de responsive

Se fijan y no se reinventan:

| Ancho | Comportamiento |
| --- | --- |
| Hasta 781 px | Una columna. Menú hamburguesa. Tienda en 2 columnas de producto. Pie en una columna. |
| 782–1024 px | Cabecera compacta (logo, hamburguesa, WhatsApp). Pie en 2 columnas. |
| Desde 1025 px | Menú de escritorio en una fila. Tienda en 4 columnas. Ficha en dos columnas. |

Todo control táctil (botón, enlace de menú, cantidad, selector) mide al menos 44 px de alto. Ninguna pantalla puede generar scroll horizontal.

## Modelos

Cada tarea nombra un solo modelo. No se mezclan en la misma tarea.

| Modelo | Cuándo |
| --- | --- |
| **Cursor Grok 4.7 high** | Decisiones de sistema, pantallas con layout frágil (cabecera, ficha, checkout, inicio) y el cierre visual. |
| **Cursor Grok 4.6 medium** | Pantallas ya resueltas en la etapa 11, donde hay que aplicar el sistema sin rearmar la maqueta. |
| **Composer 2.5** | Aplicación mecánica de tokens ya definidos: avisos, migas, estados vacíos, foco, campos repetidos. |

Orden: 13.1 primero. 13.2 a 13.7 pueden ir en paralelo recién cuando 13.1 está cerrada. 13.8 al final, con todas las pantallas ya tocadas.

## Sistema que deben seguir todas las tareas

13.1 lo escribe en `style.css` como variables y clases. El resto solo consume ese sistema. No se inventan radios, verdes ni tamaños nuevos en una pantalla.

**Tipo**

| Uso | Tamaño | Peso | Color |
| --- | --- | --- | --- |
| Título de página (tienda, contacto, cuenta) | 2rem, 1.6rem bajo 782 px | 900 | `#055902` |
| Título de producto en ficha | 1.7rem, 1.35rem bajo 782 px | 900 | `#055902` |
| Título de bloque (horario, «Tu pedido», «En oferta») | 1.35rem | 700 | `#055902` |
| Nombre en tarjeta de tienda | 1.05rem | 700 | `#055902` |
| Precio | 1.25rem en ficha, 1.05rem en tarjeta | 700 | `#055902` |
| Texto | 1rem, interlineado 1.45 | 400 | `#2F2F2F` |
| Texto largo de ficha | 0.875rem, interlineado 1.4 | 400 | `#2F2F2F` |
| Meta, miga, ayuda | 0.875rem | 400 | `#666` |

Hoy conviven títulos de 3rem, 2.6rem, 2.4rem y 1.7rem, y pesos 700, 800 y 900. El de página pasa a una sola escala. «Productos de calidad» puede seguir más grande que un título de página, pero usa el mismo verde y el mismo peso 900, no un verde aparte (`#3f5600`).

**Botones**

| Clase | Uso | Fondo | Texto | Radio |
| --- | --- | --- | --- | --- |
| Primario | Agregar, seleccionar, finalizar, enviar, lista de precios | `#AADB1F` | `#055902` | 6 px |
| Secundario | Volver a la tienda, actualizar deshabilitado, limpiar | `#E6E6E6` | `#333` | 6 px |
| Texto | Olvidé la contraseña, limpiar variación | ninguno | `#055902` | — |

Alto mínimo 44 px. Ancho completo dentro de tarjeta, formulario y caja de totales. El primario deshabilitado baja la opacidad a 0.55 y no cambia de color. Se elimina el píldora de 25 px del inicio y el radio de 4 px de la ficha.

**Campos**

Una sola caja: alto 44 px, fondo `#F6F7F7`, borde 1 px `#DDD`, radio 6 px, texto 1rem. Aplica a búsqueda, checkout, cuenta, contacto y cantidad. Se deja el subrayado suelto del contacto y el botón de lupa gris distinto.

**Superficies**

- Tarjeta de producto y de categoría: borde 1 px `#D5E7A4`, radio 8 px, fondo blanco.
- Caja de totales, pedido y tabla del carrito: mismo borde y radio 8 px (hoy la tabla usa 12 px y otro verde).
- Miga: mayúsculas, filete inferior 3 px `#AADB1F`.
- Separación entre bloques de una misma pantalla: 0.75rem. Entre título, meta, precio y botón de una ficha o tarjeta: 0.35rem.

**Fucsia**

Solo la etiqueta «OFERTA» y el enlace de quitar del carrito. No se usa en botones ni en títulos.

---

## 13.1 Tokens y clases base

**Modelo:** Cursor Grok 4.7 high.

Define las variables y las clases de la tabla anterior en `wp-content/themes/lunic/style.css`, y sube la versión del tema en `functions.php` junto con el CSS. Reemplaza los radios, altos y colores sueltos que ya cubre el sistema (botón de ficha, botón de tienda, CTA del inicio, campos de checkout y de contacto) sin rediseñar todavía cada pantalla.

**Hecho cuando:** un botón primario, uno secundario y un campo se ven iguales en ficha, tienda y checkout; Montserrat gana a Noto Sans del kit de Elementor en `body.theme-lunic`; no hay scroll horizontal en inicio, tienda y ficha a 390 px y a 1440 px.

**No hace:** mover widgets, cambiar la ficha de columnas, ni tocar PHP de negocio.

**Cerrada:** 28/09/2026. Botón primario (acento y verde, radio 6 px), secundario gris y campo `#F6F7F7` coinciden en tienda, ficha y checkout. Montserrat queda en el cuerpo, el inicio y los títulos del pie.

## 13.2 Cabecera, menú y pie

**Modelo:** Cursor Grok 4.7 high.

La cabecera ya falló por especificidad (columna que tapa el botón, `z-index`, `top: 50%`). Esta tarea la recorre de nuevo con el sistema: logo, ítem actual con filete `#AADB1F`, carrito con importe, WhatsApp, hamburguesa bajo 1024 px, panel y megamenú de categorías. El pie usa los mismos títulos de bloque, la barra `#AADB1F` y, bajo 781 px, una columna centrada con Tienda y Mi cuenta desplegables.

**Hecho cuando:** a 1440 px el menú es una fila; a 390 px se ve logo, hamburguesa y WhatsApp, el panel abre y Categorías despliega las tres listas; el pie no se sale de la pantalla. El carrito de prueba sigue en 1 ítem.

**No hace:** cambiar el número de WhatsApp ni los menús 113, 114 y 115.

**Cerrada:** 28/09/2026. A 1440 px el menú es una fila, Inicio lleva el filete `#AADB1F` y el carrito de prueba sigue en $ 9,006.00 / 1. Categorías abre el megamenú. A 390 px el panel abre y despliega las tres listas. El pie no se sale de la pantalla.

## 13.3 Inicio

**Modelo:** Cursor Grok 4.7 high.

El inicio mezcla un título de 3rem, un CTA píldora verde, un acordeón y un buscador con estilos propios. Se alinean al sistema: slider igual (8 diapositivas, `56vh` en el teléfono), botón «Descargá la Lista de Precios» primario, acordeón «Envíos» cerrado, bloque «Productos de calidad» / «Siempre al mejor precio» con la escala de títulos, «En oferta» con las mismas tarjetas que la tienda (sin miniatura, como ahora), buscador con el campo unificado.

**Hecho cuando:** a 390 px se ve diapositiva, botón, acordeón cerrado y el arranque del bloque de calidad, sin scroll horizontal. El enlace de la lista de precios no cambia.

**Cerrada:** 28/09/2026. Ocho diapositivas, slider a 56 vh en 390 px, botón primario, acordeón cerrado, títulos en la escala y ofertas en tarjetas sin miniatura. El enlace de OneDrive no cambió.

## 13.4 Tienda y grilla de categorías

**Modelo:** Cursor Grok 4.6 medium.

La maqueta ya está (4 columnas / 2 en el teléfono, filtro arriba en 390 px, 39 categorías en 5 columnas / 2 en el teléfono). Esta tarea solo unifica tarjeta, etiqueta OFERTA, precio, «SIN IVA», botón «Seleccionar opciones», buscador, orden, paginación y tarjeta horizontal de categoría al borde y radio del sistema.

**Hecho cuando:** `/tienda/` y la página de categorías se ven con la misma tarjeta, el mismo botón y el mismo campo. Blends de té sigue en 52. El filtro `lunic_cat` no se reescribe.

**Cerrada:** 28/09/2026. Tienda con tarjeta de borde `#D5E7A4` y radio 8 px, etiqueta OFERTA en fucsia, botón «Seleccionar opciones», buscador, orden y paginación del sistema. Cuatro columnas desde 1025 px y dos hasta 1024 px. Blends de té sigue en 52. La grilla de categorías tiene 39 tarjetas con el mismo borde, radio y verde, en 5 columnas y en 2 bajo 782 px. El filtro `lunic_cat` no se reescribió.

## 13.5 Ficha de producto

**Modelo:** Cursor Grok 4.7 high.

Es la pantalla más frágil: galería con flexslider, variaciones, descripción a ancho completo debajo de foto y compra, pestañas, barra fija «Agregar» bajo 781 px. Se aplica la escala de tipo, el botón primario, el campo de cantidad y de variación, y el espaciado corto (cerca de 0.35rem entre título, meta, precio y botón; la descripción pegada al borde inferior de la galería, sin pisar las miniaturas).

**Hecho cuando:** Hibiscus muestra rango, al elegir 250 gr el precio tachado y el precio de oferta, y el botón se habilita. No se envía el formulario. Campestre simple sigue con tachado $29.800 y precio $28.310. Blend Nº 45 sigue sin stock, sin texto de descuento. A 390 px la barra fija no tapa el botón ni genera scroll horizontal.

**Cerrada:** 28/09/2026. Hibiscus con rango y, en 250 gr, tachado $9.705 y oferta $5.635, botón habilitado, sin enviar. Campestre tachado $29.800 y precio $28.310 (−5%). Blend Nº 45 sin stock y sin texto de descuento. A 390 px la barra «Agregar» muestra el precio de la variación y se oculta mientras el botón real está en pantalla. El carrito de prueba sigue en $9.006 / 1.

## 13.6 Carrito, checkout y Mi cuenta

**Modelo:** Cursor Grok 4.6 medium.

Misma maqueta de la 11.8, con cajas, tablas, campos y botones del sistema. El carrito conserva la tabla, «Actualizar» a ancho completo, sin cupón, totales y envío fuera del total. El checkout, desde 900 px, deja los datos a la izquierda y «Tu pedido» a la derecha; debajo, una columna. Mi cuenta: «Iniciar sesión» y «Registrarme» en dos columnas desde 782 px y una debajo de la otra en el teléfono.

**Hecho cuando:** con el carrito de prueba, subtotal $9.006, IVA 21% $1.891,26, total Woo $10.897,26 y envío CABA $4.700 fuera de ese total. El carrito vacío sigue con el secundario «Volver a la tienda» y el acordeón de envíos abierto. A 390 px todo es una columna y los campos no se salen.

**Cerrada:** 28/09/2026. Carrito con subtotal $9.006, IVA $1.891,26, total $10.897,26 y envío CABA $4.700 fuera de ese total; cupón oculto; «Actualizar» a ancho completo. Checkout en dos columnas desde 900 px y una columna en el teléfono. Mi cuenta en dos columnas en escritorio y apilada a 390 px. El carrito vacío, sin tocar la sesión, conserva «Volver a la tienda» y el acordeón de envíos abierto.

**No hace:** recalcular envíos ni impuestos.

## 13.7 Contacto, institucionales, avisos y estados vacíos

**Modelo:** Composer 2.5.

Con los tokens ya cerrados, unifica lo repetido:

- Contacto: título de página, campos del sistema, botón primario a ancho completo, datos y mapa como están.
- Quiénes somos, cookies y privacidad: el título de página del sistema cuando el tema lo pinta. No se remaqueta el contenido que sigue en Elementor.
- Avisos de WooCommerce (éxito, error, info), miga, foco visible y el estado sin resultados de búsqueda: mismo texto, mismo borde `#D5E7A4`, mismo botón secundario.

**Hecho cuando:** contacto a 390 px apila formulario y datos; un aviso de Woo y la búsqueda sin resultados usan el mismo recuadro y el mismo botón que el carrito vacío.

**Cerrada:** 28/09/2026. Contacto con título 2rem / 1.6rem, campos de 44 px y «Enviar» a ancho completo; a 390 px el formulario queda arriba y los datos abajo. El aviso de consulta, el de WooCommerce y la búsqueda sin resultados usan el recuadro de borde `#D5E7A4`, radio 8 px y el botón gris «Volver a la tienda» (o «Ver carrito» en el aviso). Cookies y privacidad llevan el título del tema. La miga queda en 0.875rem, `#666` y filete `#AADB1F`.

## 13.8 Cierre: consistencia y mobile

**Modelo:** Cursor Grok 4.7 high.

Recorre inicio, tienda, ficha simple, ficha variable, ficha sin stock, carrito con producto, carrito vacío, checkout, cuenta, contacto y categorías, a 390 px y a 1440 px. Anota solo diferencias contra el sistema de este documento (tipo, botón, campo, radio, color, alto táctil, scroll horizontal). Corrige las que sean del tema. No abre una pantalla nueva.

**Hecho cuando:** la lista de diferencias queda en este archivo, debajo de esta tarea, con fecha. Si una pantalla no se puede alinear porque el HTML lo pinta Elementor libre, se anota y no se reactiva Elementor Pro para forzarla.

**Cerrada:** 28/09/2026. Recorrido a 1440 px y a 390 px. Se corrigió en el tema el alto del filtro (44 px), el color de la miga y el botón secundario dentro del aviso de WooCommerce.

### Diferencias que quedan — 28/09/2026

- **Quiénes somos.** El tema no imprime el h1: Elementor oculta el título y la plantilla solo vuelca el contenido. «Sobre Nosotros», «Algunos de nuestros productos» y «Beneficios» los pinta Elementor en verde 900. A 1440 px miden 2rem; a 390 px «Sobre Nosotros» mide 24 px. No se reescribió ese HTML.
- **Grilla de categorías** (`/elementor-12340/`). El tema no imprime título (el nombre interno de la página es «Elementor #12340»). Las 39 tarjetas sí usan el borde `#D5E7A4`, el radio 8 px y el verde, en 5 columnas y en 2 bajo 782 px.
- **Ficha.** Los `????` de Hibiscus siguen en el texto guardado del producto. Los subtítulos de la descripción los pinta Elementor. No se reescribieron.
- **Carrito vacío.** Se comprobó el HTML sin vaciar la sesión de prueba (sigue en $9.006 / 1): recuadro, botón secundario y acordeón de envíos abierto. No se abrió esa pantalla en el navegador con la sesión actual.

**Después del cierre:** 28/09/2026, tema `lunic` 0.3.53. El carrito de escritorio se centra (máximo 820 px). Precio, cantidad y subtotal quedan bajo el título de su columna. En «Total de la compra» la etiqueta va a la izquierda y el importe a la derecha. La caja de totales mide lo mismo que la tabla. El carrito de prueba sigue en $9.006 / 1.

---

## Cómo se ejecuta cada tarea

1. Leer este archivo y la sección 11 de `documentos/etapa-11-diseno.md` de esa pantalla.
2. Trabajar solo en el tema `lunic` (y en el plugin solo si el shortcode de búsqueda, filtro o categorías no puede heredar el CSS).
3. Subir la versión de `style.css` en la cabecera y en `functions.php` en el mismo cambio. Si se toca `theme.js`, subir también esa versión.
4. Probar en el navegador la pantalla de la tarea, a 390 px y en escritorio, antes de darla por cerrada.
5. No commitear al terminar una tarea, salvo pedido explícito.
