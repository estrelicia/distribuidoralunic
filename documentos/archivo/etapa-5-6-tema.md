# Etapas 5 y 6 — tema Lunic y plantillas WooCommerce

Fecha: 25/09/2026.

El tema está en `wp-content/themes/lunic/`. El sitio público sigue con **Hello Elementor Child**. El corte (activar Lunic y apagar Elementor) es la etapa 7. Mientras Lunic está activo, un filtro deja de usar las plantillas del Theme Builder de Elementor Pro para que se vean el header, la portada y la tienda propios.

## Etapa 5

- Soportes de WooCommerce, logo (adjunto 64), menús Inicio (16), Celular (102), Footer (103) y los tres de categorías (113, 114, 115).
- Colores del kit: `#4B4B4B`, `#AADB1F`, `#2F2F2F`, `#055902`, `#E42886`. La fuente pedida es Montserrat; si el navegador no la tiene, usa Segoe UI.
- Header con buscador, carrito (cantidad) y panel de categorías. En menos de 782 px el menú es un botón.
- Portada: slider con las mismas imágenes, botón a la lista de precios, productos recientes, acordeón Envíos y buscador.
- Quiénes somos, cookies y privacidad salen del contenido de la página. Contacto: formulario Nombre, Correo, Teléfono y Consulta hacia `info@distribuidoralunic.com.ar`, más mapa de OpenStreetMap en Av. José María Moreno 1280. En esta copia Laragon `wp_mail` no tiene servidor de correo, así que el envío de prueba no salió de la máquina.
- Mantenimiento: casilla en el menú Lunic, apagada. Con el tema Lunic activo, quien no administra ve `maintenance.php`.

## Etapa 6

- Tienda: título, miga, buscador, filtro `lunic_cat`, orden y loop. Con Lunic activo, Blends de té sigue mostrando 52 resultados y la paginación.
- Ficha: galería, título, precio, texto de descuento, agregar al carrito, meta, pestañas y relacionados. Barra fija «Agregar» en móvil.
- Carrito, checkout y mi cuenta usan los shortcodes de WooCommerce, así siguen los campos CUIT/IVA y el envío mostrado aparte.
- El acordeón «Envíos» de carrito y checkout lee el texto de la opción `configuraciones`.
