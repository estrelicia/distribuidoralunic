# Megadruid CMS

Plugin de WordPress para marca del escritorio, login, panel de bienvenida, menús restringidos por rol y (en etapas posteriores) seguridad. Reemplaza de forma gradual a White Label CMS y Megadruid Seguridad.

- **Versión:** 0.2.3  
- **Text domain:** `megadruid-cms`  
- **Opción:** `mdcms_settings` (única)  
- **Requisitos:** PHP 8.1+, WordPress 6.0+

## Instalación

Copiar la carpeta a `wp-content/plugins/megadruid-cms/` y activar en el escritorio. Los ajustes están en **Ajustes → Megadruid CMS** (capability `manage_options`).

En la lista de plugins hay un enlace **Ajustes** directo.

## Estructura

| Archivo / clase | Función |
| --- | --- |
| `megadruid-cms.php` | Bootstrap, constantes, activación |
| `includes/class-settings.php` | Esquema, lectura, sanitización y guardado |
| `includes/class-access.php` | Quién administra la marca y quién recibe recortes |
| `includes/class-branding.php` | Barra, menú lateral, pie, títulos |
| `includes/class-login.php` | Pantalla de login y vista previa |
| `includes/class-dashboard.php` | Paneles de bienvenida y feeds |
| `includes/class-menus.php` | Ocultar ítems del menú y bloqueo por URL |
| `includes/class-admin-ui.php` | Ayuda, opciones de pantalla, avisos, CSS admin y editor |
| `includes/class-metaboxes.php` | Cajas del editor de entradas y páginas por rol |
| `includes/class-wizard.php` | Asistente de puesta en marcha (4 pasos) |
| `includes/class-admin.php` | Pantalla de ajustes (pestañas) |
| `assets/css/admin.css`, `assets/js/admin.js` | UI del admin del plugin |

## Pestañas de ajustes

- **Marca** — logos, textos, pie, barra y menú lateral  
- **Login** — imagen, fondo, CSS/JS y vista previa  
- **Escritorio** — título, paneles de bienvenida, widgets ocultos, feeds  
- **Menús** — ítems ocultos por rol y barra en el frente  
- **Ajustes** — asistente, ayuda/aviso/CSS, cajas del editor  
- **Seguridad** — reservada para la etapa 7 (aún sin lógica)

## Asistente (etapa 5.3)

Cuatro pasos opcionales en la pestaña **Ajustes**: logo de login, nombre y pie, primer panel de bienvenida, confirmación. Escribe en las mismas claves que el resto de pestañas. Se puede saltar un paso sin guardar. Al finalizar se marca `wizard_completed` y se puede volver a abrir desde un botón en la misma pestaña.

## Cajas del editor (etapa 5.2)

En **Ajustes**, tabla «Cajas del editor»: por cada caja (extracto, slug, atributos de página, etc.) se eligen los roles para los que se oculta con `remove_meta_box`. Quien **administra la marca** (`Access::manages_brand()`) sigue viendo todas las cajas.

## Progreso del plan

Implementado en local hasta la **etapa 5.3** del plan en `documentos/implementacion-plugin-cms.md` (etapas 0–5). Pendiente: importación JSON, migración desde WLCMS/WBS, seguridad y cierre (etapas 6–9).

White Label CMS y Megadruid Seguridad pueden seguir activos hasta el cierre planificado; evitar duplicar límites de login cuando ambos estén encendidos.

## Documentación del proyecto

- Plan completo: `documentos/implementacion-plugin-cms.md`  
- Estado general del sitio: `documentos/estado.md`
