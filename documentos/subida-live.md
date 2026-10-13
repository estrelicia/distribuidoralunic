# Subida al live (13/10/2026)

Esta copia **no publica** sola. El plugin se copia a mano. Hacer backup de base y de `wp-content` en el live **antes** de copiar.

## Qué subir juntos

| Pieza | Versión | Carpeta |
| --- | --- | --- |
| Tema | **0.3.68** | `wp-content/themes/lunic/` |
| Plugin | **0.1.32** | `wp-content/plugins/distribuidora-lunic/` |

## Pasos

1. Copia de la base y de las dos carpetas en el live.
2. Reemplazar tema y plugin **juntos** (mismas versiones de esta copia).
3. Entrar al escritorio una vez (corren migraciones de widgets Elementor y shortcode de envíos).
4. Vaciar `wp-content/cache/lunic/` una vez. Desde el 0.1.32 el plugin también la vacía al cambiar la versión del plugin o del tema, pero el HTML viejo puede seguir en disco hasta esa primera visita.
5. **Lunic → Datos del sitio:** completar WhatsApp, teléfono, correo, dirección, horario, lista de precios y página de categorías si el live no tiene esa opción.
6. **WooCommerce → Envío:** revisar el mínimo de envío gratis en 1.er, 2.º y 3.er cordón (en local había 5.000.000; debe coincidir con la política, p. ej. 100.000 como CABA).
7. Para que Andreani cotice: en cada producto del carrito, peso y alto, ancho y largo. En el live esas medidas no están en las variantes que sí las tienen en esta copia. CABA y los cordones no tienen método Andreani.
8. Recorrer como visitante (1366 px y 390 px): inicio, tienda, buscador, una ficha, carrito con producto, checkout con CABA (provincia del desplegable + CP + ciudad) y con un destino de Provincia de Buenos Aires o nacional si se quiere ver Andreani, mi cuenta, contacto.

## Qué no hace falta

- Subir `wp-admin`, `wp-includes` ni el núcleo de WordPress por estos cambios.
- Activar Elementor Pro para que se vean carrito, checkout y el ícono del carrito del encabezado.
