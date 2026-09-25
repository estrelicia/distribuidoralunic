# Distribuidora Lunic

Tienda WooCommerce de Distribuidora Lunic. El trabajo en curso reemplaza Elementor Pro, JetEngine, Ivory Search y Product Filter por un plugin propio y, más adelante, un tema `lunic`.

## Dónde está cada cosa

- Plan y estado de las tareas: `documentos/analisis-plugin-lunic.md`
- Línea base de compra y PageSpeed: `documentos/linea-base/`
- Plugin propio: `wp-content/plugins/distribuidora-lunic/`

## Entorno local

Copia Laragon en `https://distribuidoralunic.com.ar.dev/`. `wp-config.php` no se versiona: hay que crearlo en cada máquina con la base local. Las imágenes de `wp-content/uploads/` tampoco se suben.

## Plugins propios activos en esta copia

`distribuidora-lunic` concentra envíos (`custom_shipping`, mismas zonas) y descuentos por categoría (opción `wcd_discount_rules`). Los plugins «Envíos Personalizados» y «Descuentos por Categoría» quedan desactivados en la base local para no aplicar la misma regla dos veces. El código viejo sigue en el repositorio hasta la etapa 7.
