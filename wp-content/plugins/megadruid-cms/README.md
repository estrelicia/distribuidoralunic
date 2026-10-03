# Megadruid CMS

Plugin de WordPress para marca del escritorio, login, panel de bienvenida, menús restringidos por rol y seguridad base detrás de Cloudflare. Reemplaza a White Label CMS y Megadruid Seguridad.

- **Versión:** 0.3.15  
- **Text domain:** `megadruid-cms`  
- **Opción:** `mdcms_settings` (única)  
- **Requisitos:** PHP 8.1+, WordPress 6.0+

El login usa el icono del sitio (cliente) y el estilo nativo de WordPress. Los logos pixel de Megadruid (`assets/img/`) quedan en la barra y en la caja del Escritorio.

## Instalación

Copiar la carpeta a `wp-content/plugins/megadruid-cms/` y activar en el escritorio. Los ajustes están en **Ajustes → Megadruid CMS** (capability `manage_options`).

En la lista de plugins hay enlaces **Ajustes** y **Manual**.

El manual en Markdown está en `docs/manual-de-usuario.md`. La misma guía se lee en la pestaña **Manual** del plugin.

## Estructura

| Archivo / clase | Función |
| --- | --- |
| `megadruid-cms.php` | Bootstrap, constantes, activación |
| `includes/class-settings.php` | Esquema, lectura, sanitización y guardado |
| `includes/class-access.php` | Quién administra la marca y quién recibe recortes |
| `includes/class-branding.php` | Barra y títulos |
| `includes/class-login.php` | Pantalla de login y vista previa |
| `includes/class-dashboard.php` | Escritorio, accesos Megadruid y paneles nativos |
| `includes/class-menus.php` | Ocultar ítems del menú y bloqueo por URL |
| `includes/class-admin-ui.php` | Ayuda, opciones de pantalla, avisos, CSS admin y editor |
| `includes/class-metaboxes.php` | Cajas del editor de entradas y páginas por rol |
| `includes/class-transfer.php` | Exportar, importar y restablecer JSON |
| `includes/class-legacy-import.php` | Copia única desde White Label CMS y Megadruid Seguridad |
| `includes/class-client-ip.php` | IP real detrás de Cloudflare |
| `includes/class-login-limit.php` | Límite de intentos de login por IP |
| `includes/class-hardening.php` | XML-RPC, REST, feeds, cabeceras, etc. |
| `includes/class-security.php` | Pantalla de la pestaña Seguridad |
| `includes/class-admin-layout.php` | Pestañas, cajas `postbox` y Dashicons |
| `includes/class-manual.php` | Manual en el escritorio y pestañas de Ayuda |
| `includes/class-admin.php` | Pantalla de ajustes (pestañas) |
| `assets/css/admin.css`, `assets/js/admin.js` | UI del admin del plugin |

## Pestañas de ajustes

- **Login** — logo del cliente (320 × 84 px) e imagen de fondo (1920 × 1080 px)  
- **Escritorio** — título, caja Megadruid, paneles nativos por rol  
- **Menús** — wp-admin (a quién + mapa) y barra negra de la tienda, por separado  
- **Ajustes** — ayuda/aviso/CSS, cajas del editor, JSON  
- **Seguridad** — tabla de IP, casillas de endurecimiento, máximo de fallos y ventana en minutos. Si Megadruid Seguridad sigue activo, el límite y el endurecimiento los aplica ese plugin; al guardar aquí se sincroniza `wbs_settings`.
- **Manual** — guía de uso (también en Ayuda de WordPress y en `docs/manual-de-usuario.md`)

## Cajas del editor (etapa 5.2)

En **Ajustes**, tabla «Cajas del editor»: por cada caja (extracto, slug, atributos de página, etc.) se eligen los roles para los que se oculta con `remove_meta_box`. Quien **administra la marca** (`Access::manages_brand()`) sigue viendo todas las cajas.

## Progreso del plan

Implementado en local hasta la **etapa 8**. White Label CMS y Megadruid Seguridad están desactivados en esta copia. Paquete: `documentos/paquete-megadruid-cms/`.

La importación desde `wlcms_options` y `wbs_settings` corre una sola vez en el escritorio y no borra esas opciones. Si Megadruid Seguridad sigue con el límite de login activo, Megadruid CMS no registra un segundo bloqueo.

## JSON

En **Ajustes**: exportar, importar (máximo 256 KB, solo claves del esquema) y restablecer con confirmación. Un archivo con PHP o que no es JSON se rechaza.

## Documentación del proyecto

- Plan completo: `documentos/implementacion-plugin-cms.md`  
- Estado general del sitio: `documentos/estado.md`
