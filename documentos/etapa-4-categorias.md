# Etapa 4 — grilla de categorías

Fecha: 25/09/2026. JetEngine sigue activo.

## 4.1 Meta y shortcode

- `imagen-web` queda registrado como meta de `product_cat` (objeto con `id` y `url`, el formato que ya guarda JetEngine).
- Shortcode `[lunic_categorias]`: categorías con productos, sin el término 15.
- Imagen: primero `imagen-web` (5 categorías: Aceites y salsas, Aditivos alimentarios, Arroz, Azúcar, Blends de té). Si no hay meta, la miniatura de WooCommerce. Si tampoco hay, el placeholder.
- Tarjeta: radio 10 px y fondo `#E6E6E6` al pasar el mouse. Grilla de 5 columnas; en menos de 782 px, 2 columnas.

## 4.2 Página 12340

El widget `jet-listing-grid` del listing **12348** en `/elementor-12340/` ahora imprime la grilla nueva. JetEngine no se desactivó.

En el navegador la página muestra **39** tarjetas, la misma cantidad de categorías públicas con productos (sin el término 15). Blends de té usa `blends-de-te.jpg`.
