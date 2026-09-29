# Etapas 2 y 3 — búsqueda y filtro

Fecha: 25/09/2026. Ivory Search y WBW siguen activos.

## Etapa 2. Búsqueda

- Endpoint público: `GET /wp-json/lunic/v1/search?q=` (mínimo 1 carácter). También `admin-ajax.php?action=lunic_search`.
- Devuelve productos publicados: título, imagen, precio visible (con el descuento de categoría ya aplicado), primera categoría y enlace.
- Shortcode `[lunic_search]`. Se imprime en la portada (antes del contenido) y en la tienda (antes del loop), al lado del buscador de Ivory, que no se quitó.
- El panel de sugerencias cuelga debajo del campo (no tapa el header). Escape y un clic afuera lo cierran.
- Prueba: «boldo» y «cardamomo» devuelven 8 productos cada una.

La ruta `lunic/v1` queda exceptuada del cierre de REST para invitados. El resto de `/wp/v2` sigue cerrado. Ver `documentos/rest-api-lunic.md`.

## Etapa 3. Filtro de tienda

- Shortcode `[lunic_filter]`, también impreso antes del loop de la tienda.
- Árbol de `product_cat`: sin vacías, sin el término 15 (Sin asignar) ni sus hijas, con conteo. El activo va en negrita.
- Parámetro `lunic_cat` (ID de término). Recargar o compartir la URL conserva el filtro. `history.pushState` permite volver atrás.
- La URL vieja `wpf_filter_cat_list_0` redirige a `lunic_cat` y descarta `wpf_fbv`.
- El query incluye hijas de la categoría, así entran los productos variables de esa rama.
- Sin resultados: «No se encontraron productos».
- Escritorio: lista con alto máximo 700 px. Por debajo de 782 px: botón «Categorías» y panel; el loop queda primero.

Prueba: Blends de té (término 135) tiene conteo **52**, el mismo de la línea base 0.2.
