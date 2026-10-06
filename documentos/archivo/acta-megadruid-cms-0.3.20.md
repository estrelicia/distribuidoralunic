# Acta — Megadruid CMS 0.3.20 (visibilidad)

**Fecha:** 06/10/2026.  
**Entorno:** `https://distribuidoralunic.com.ar.dev/`. El sitio en vivo no se tocó.  
**Versión:** `wp-content/plugins/megadruid-cms/` **0.3.20**.  
**Base previa:** 0.3.19.

## Qué se hizo

El plugin en WordPress queda visible solo para las cuentas tildadas en **Menús → Administradores con escritorio completo** (`full_access_admin_ids`).

- No aparece **Ajustes → Megadruid CMS** para el resto.
- No aparece en **Plugins** ni en avisos de actualización de ese plugin.
- La URL de ajustes, el guardado, el JSON y desactivar/borrar responden 403.
- Si la lista está vacía, cualquiera con `manage_options` lo sigue viendo (no quedar afuera).
- Login, barra y caja del Escritorio siguen para todos: eso es marca, no el panel.

Código: `Access::can_see_plugin()`, `Admin::guard()`, `all_plugins`, `site_transient_update_plugins`.

## Dónde está documentado

- README: `wp-content/plugins/megadruid-cms/README.md`
- Manual: `wp-content/plugins/megadruid-cms/docs/manual-de-usuario.md`
- Plan: `documentos/implementacion-plugin-cms.md`
- Estado: `documentos/estado.md`

## Qué no entra en este commit

Cambios de Elementor, tema Lunic, plugin Distribuidora Lunic, borrados de White Label CMS y temas Twenty*, `.htaccess`, zip `documentos/paquete-megadruid-cms.zip`. El paquete carpeta `documentos/paquete-megadruid-cms/` sigue siendo la foto de 0.3.1.
