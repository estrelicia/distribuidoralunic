# Distribuidora Lunic

Tienda WooCommerce de Distribuidora Lunic. El tema activo es Lunic. Elementor, JetEngine, Ivory Search y Product Filter quedaron desactivados en esta copia.

## Dónde está cada cosa

- Plan y estado de las tareas: `documentos/analisis-plugin-lunic.md`
- Línea base de compra y PageSpeed: `documentos/linea-base/`
- Plugin propio: `wp-content/plugins/distribuidora-lunic/`
- Tema activo: `wp-content/themes/lunic/`

## Entorno local

Copia Laragon en `https://distribuidoralunic.com.ar.dev/`. `wp-config.php` no se versiona: hay que crearlo en cada máquina con la base local. Las imágenes de `wp-content/uploads/` tampoco se suben.

## Plugins propios activos en esta copia

`distribuidora-lunic` concentra envíos, descuentos, checkout, seguridad, búsqueda, filtro, categorías y el formulario de contacto. Los plugins viejos de envíos y descuentos se quitaron del disco. La política REST está en `documentos/rest-api-lunic.md`.
