# Acta de cierre — etapas 1 a 6 (13/10/2026)

Trabajo en la copia local. **El live no se publicó.**

## Versiones finales

- Tema `lunic` **0.3.68**
- Plugin `distribuidora-lunic` **0.1.27**

Esos paquetes zip se armaron para este cierre y después se sacaron del repositorio. No están en el repo.

## Tarea 6.1 — subida (lista para el live, no ejecutada)

1. Copia de la base y de las carpetas del live.
2. Subir juntas `wp-content/themes/lunic/` y `wp-content/plugins/distribuidora-lunic/`.
3. Entrar al escritorio una vez (migraciones de Elementor del plugin).
4. Vaciar `wp-content/cache/lunic/`.
5. Completar **Lunic → Datos del sitio** (en live no se copian solos).
6. Revisar **WooCommerce → Envío** (mínimo de envío gratis en cordones).
7. Recorrer como visitante: inicio, tienda, ficha, carrito con producto, checkout (CABA con CP), mi cuenta, contacto. Escritorio y 390 px.

## Tareas 5.3, 5.4 y 5.5

- Plantillas WooCommerce del tema con `@version` alineada a WooCommerce 11.2 (empty cart 7.0.1, meta 11.2.0, content-single-product 3.6.0, no-products-found 7.8.0, description 2.0.0).
- Descripción del producto: `get_post_field('post_content', $id, 'raw')`.
- `gettext` de WooCommerce solo en carrito, checkout y peticiones `wc-ajax`.

## Documentos archivados en este cierre

Siguen en `archivo/`: `auditoria-tema-y-plugin-lunic.md`, `plan-etapas-lunic.md`, `envios-diagnostico.md`.  
Se sacaron del repo en la limpieza posterior y no hay que buscarlos: `subida-plugin-0.1.16-tarea-1.1.md`, `envios-donde-se-edita.md`, `seguridad-plantillas-elementor.md`, `implementacion-plugin-cms.md` y los zip de paquetes.
