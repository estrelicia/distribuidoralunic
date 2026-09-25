# Distribuidora Lunic

Tienda WooCommerce de Distribuidora Lunic. El tema activo es Hello Elementor (hijo). Header, inicio, tienda, ficha, carrito y footer los sigue dibujando Elementor Pro, con JetEngine, Ivory Search y Product Filter, para conservar la apariencia anterior. Envíos, descuentos, campos de checkout y cabeceras de seguridad viven en el plugin `distribuidora-lunic`.

## Dónde está cada cosa

- Plan y estado de las tareas: `documentos/analisis-plugin-lunic.md`
- Línea base de compra y PageSpeed: `documentos/linea-base/`
- Plugin propio: `wp-content/plugins/distribuidora-lunic/`
- Tema activo: `wp-content/themes/lunic/`

## Entorno local

Copia Laragon en `https://distribuidoralunic.com.ar.dev/`. `wp-config.php` no se versiona: hay que crearlo en cada máquina con la base local. Las imágenes de `wp-content/uploads/` tampoco se suben.

## Plugins propios activos en esta copia

`distribuidora-lunic` concentra envíos, descuentos, checkout, seguridad, búsqueda, filtro, categorías y el formulario de contacto. Los plugins viejos de envíos y descuentos se quitaron del disco. La política REST está en `documentos/rest-api-lunic.md`.
