# Acta — Megadruid CMS 0.3.19 (UI)

**Fecha:** 03/10/2026.  
**Entorno:** `https://distribuidoralunic.com.ar.dev/`. El sitio en vivo no se tocó.  
**Versión:** `wp-content/plugins/megadruid-cms/` **0.3.19**.  
**Base previa:** 0.3.16 en `main` (etapas 0–8).

## Qué se hizo

1. **Aviso de importación heredada.** `class-legacy-import.php` deja de dejar clavado `mdcms_legacy_import_notes` en cada visita; borra la opción si existe.
2. **Paleta Megadruid** en `assets/css/admin.css`: menta, violeta, oro. Sin lima/negro de la UI anterior.
3. **Login (pestaña del plugin).** Las dos líneas negras de las cards eran previews de imagen vacíos. `[hidden]` gana a `display:block`. Sin recuadro negro.
4. **Sin pestaña Ajustes.** Ese contenido pasó a **Escritorio**. JSON sigue en formularios propios. CSS extra y cajas del editor salieron de la pantalla (el código puede seguir aplicando valores ya guardados).
5. **Paneles del Escritorio.** Lista armada con los widgets reales (WooCommerce, Elementor, Easy WP SMTP, salud del sitio, etc.), más nativos. Catálogo en `mdcms_dashboard_panels`. Al desinstalar se borra esa opción junto con `mdcms_settings`.
6. **Copia de ajustes.** Control «Elegir archivo» con el mismo botón redondeado. Espacio entre **Guardar cambios** y la tarjeta JSON.

## Pestañas actuales

Login · Escritorio · Menús · Seguridad · Manual.

## Dónde está documentado

- Plan: `documentos/implementacion-plugin-cms.md`
- Estado del sitio: `documentos/estado.md`
- README del plugin: `wp-content/plugins/megadruid-cms/README.md`
- Manual: `wp-content/plugins/megadruid-cms/docs/manual-de-usuario.md`

## Qué no entra en este commit

Borrados locales de White Label CMS, Hello Elementor y temas Twenty* (no son el trabajo del plugin). El zip `documentos/paquete-megadruid-cms.zip`. El paquete carpeta `documentos/paquete-megadruid-cms/` sigue siendo la foto de 0.3.1.
