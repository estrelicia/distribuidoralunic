# Acta — Ficha producto, pestaña Descripción (tema 0.3.59)

**Fecha:** 06/10/2026.  
**Entorno:** `https://distribuidoralunic.com.ar.dev/`. El sitio en vivo no se tocó.  
**Versión:** tema `lunic` **0.3.59**. Fallback en `distribuidora-lunic` (el plugin sigue en 0.1.12).  
**Base previa:** tema 0.3.57 en `main`.

## Qué se veía

En [Blend Nº 39](https://distribuidoralunic.com.ar.dev/tienda/blends-de-te/blend-no-39-hierbas-para-mate-cedron-poleo-menta-boldo-burrito/) la pestaña **Descripción** rompía el layout: hueco grande, otra ficha adentro y el pie lima pegado al título «Descripción». Pasaba en los productos con descripción, no solo en ese.

## Causa

1. El template de WooCommerce `single-product/tabs/description.php` llama `the_content()`.
2. La ficha pública la pinta Elementor (plantilla **7620**). Durante ese render, `the_content()` toma el documento 7620 y vuelve a imprimir header/single/footer (**7421**) dentro de `#tab-description`.
3. En **48** productos publicados el `post_content` ya es ese HTML de Elementor (el copy queda mezclado, a menudo junto al markup del footer). Hay **245** productos con descripción no vacía; el resto es texto clásico.

No se reescribió la base. El carrito de prueba (Blend Nº 1) no se tocó.

## Qué se cambió

| Archivo | Rol |
| --- | --- |
| `wp-content/themes/lunic/functions.php` | Callback de la pestaña; lee `post_content` por ID de producto; `lunic_sanitize_product_description()` deja texto; `lunic_elementor_slot()` no reentra. Estilos encola **0.3.59**. |
| `wp-content/themes/lunic/woocommerce/single-product/tabs/description.php` | Override: no usa `the_content()`. |
| `wp-content/themes/lunic/style.css` | Versión 0.3.59. La descripción no desborda; se oculta un `.elementor-location-*` si volviera a colarse. |
| `wp-content/plugins/distribuidora-lunic/includes/class-elementor-fallback.php` | El widget `woocommerce-product-data-tabs` usa siempre el markup de WooCommerce. |

## Cómo se comprobó

- Blend Nº 39: texto (cedrón, poleo, menta, boldo, burrito) en la pestaña; un solo pie; sin `.elementor-7620` adentro de `.lunic-product-description`.
- Blend Nº 37: descripción clásica (hierba buena, rosa mosqueta, etc.) sin romper la página.

## Publicar sin Prime Mover

Es un cambio de archivos. En el servidor: `git pull` (o copiar tema + el PHP del fallback). No migrar la base. Vaciar caché si hay.

Limpiar las 48 descripciones en el admin de WooCommerce es opcional: sirve para que el editor muestre texto plano, no para el layout público.

## Qué no entra en este commit

Borrados locales de White Label CMS, Hello Elementor y temas Twenty*. El zip `documentos/paquete-megadruid-cms.zip`. `.htaccess`.
