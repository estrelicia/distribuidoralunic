# Megadruid CMS

Plugin de WordPress para marca del escritorio y login, menús para clientes y seguridad base detrás de Cloudflare. Reemplaza a White Label CMS y Megadruid Seguridad.

- **Versión:** 0.3.20  
- **Text domain:** `megadruid-cms`  
- **Opciones:** `mdcms_settings` (ajustes) y `mdcms_dashboard_panels` (catálogo de widgets del Escritorio)  
- **Requisitos:** PHP 8.1+, WordPress 6.0+

El login usa el logo del cliente y el estilo nativo de WordPress. Los logos pixel de Megadruid (`assets/img/`) quedan en la barra y en la caja del Escritorio.

## Instalación

Copiar la carpeta a `wp-content/plugins/megadruid-cms/` y activar. Los ajustes están en **Ajustes → Megadruid CMS**.

Solo las cuentas tildadas en **Menús → Administradores con escritorio completo** ven el plugin en WordPress: el menú, la lista de plugins y los enlaces **Ajustes** / **Manual**. El resto, aunque sea administrador, no lo ve y no puede abrir la URL, guardar ni desactivarlo. Si esa lista está vacía, cualquiera con `manage_options` lo ve (para no quedar afuera). La marca (login, barra, caja del Escritorio) sigue aplicando para todos.

El manual está en `docs/manual-de-usuario.md` y en la pestaña **Manual**.

## Estructura

| Archivo / clase | Función |
| --- | --- |
| `megadruid-cms.php` | Bootstrap, constantes, activación |
| `includes/class-settings.php` | Esquema, lectura, sanitización y guardado |
| `includes/class-access.php` | Quién ve el plugin, quién administra la marca y quién recibe recortes |
| `includes/class-branding.php` | Barra y títulos |
| `includes/class-login.php` | Pantalla de login y vista previa |
| `includes/class-dashboard.php` | Caja Megadruid, catálogo y ocultar paneles (nativos y de otros plugins) |
| `includes/class-menus.php` | Ocultar ítems del menú y bloqueo por URL |
| `includes/class-admin-ui.php` | Ayuda, opciones de pantalla, avisos; CSS admin/editor si hay valor guardado |
| `includes/class-metaboxes.php` | Cajas del editor por rol (sin pantalla; aplica valores ya guardados) |
| `includes/class-transfer.php` | Exportar, importar y restablecer JSON |
| `includes/class-legacy-import.php` | Copia única desde White Label CMS y Megadruid Seguridad |
| `includes/class-client-ip.php` | IP real detrás de Cloudflare |
| `includes/class-login-limit.php` | Límite de intentos de login por IP |
| `includes/class-hardening.php` | XML-RPC, REST, feeds, cabeceras, etc. |
| `includes/class-security.php` | Pantalla de la pestaña Seguridad |
| `includes/class-admin-layout.php` | Shell de pestañas y cards (sin postbox de WordPress) |
| `includes/class-manual.php` | Manual en el escritorio y pestañas de Ayuda |
| `includes/class-admin.php` | Pantalla de ajustes; oculta el plugin a quien no está tildado |
| `assets/css/admin.css`, `assets/js/admin.js` | UI: paleta menta/violeta/oro |

## Pestañas

- **Login** — logo del cliente (320 × 84 px) e imagen de fondo (1920 × 1080 px)  
- **Escritorio** — todos los paneles del Escritorio por rol, ayuda/avisos, JSON  
- **Menús** — quién ve el plugin y wp-admin completo, mapa de menús, barra negra de la tienda  
- **Seguridad** — IP, endurecimiento, máximo de fallos. Si Megadruid Seguridad sigue activo, el límite y el endurecimiento los aplica ese plugin; al guardar aquí se sincroniza `wbs_settings`.  
- **Manual** — la misma guía que `docs/manual-de-usuario.md`

No hay pestaña Ajustes. Un enlace `tab=general` abre Escritorio.

## Paneles del Escritorio

Al abrir el Escritorio de WordPress se guardan id y título de cada widget. Esa lista se muestra en la pestaña Escritorio. Si falta un panel nuevo, hay que visitar `index.php` y volver. Quien **administra la marca** (`Access::manages_brand()`) sigue viendo todos.

## Progreso del plan

Etapas **0 a 8** hechas en local. White Label CMS y Megadruid Seguridad desactivados en esta copia. UI de marca y pestañas: **0.3.19**. Visibilidad del plugin: **0.3.20**.

La importación desde `wlcms_options` y `wbs_settings` corre una sola vez (`mdcms_legacy_imported`) y no borra esas opciones. El aviso largo de claves sin equivalente no se guarda de forma permanente.

Si Megadruid Seguridad sigue con el límite de login activo, Megadruid CMS no registra un segundo bloqueo.

## JSON

En **Escritorio**, tarjeta Copia de ajustes: exportar, importar (máximo 256 KB, solo claves del esquema) y restablecer con confirmación. Un archivo con PHP o que no es JSON se rechaza. Elige archivo con el botón del plugin, no el control nativo de Windows. Solo pueden usarlo las cuentas que ven el plugin.

## Documentación del proyecto

- Plan: `documentos/implementacion-plugin-cms.md`  
- Estado: `documentos/estado.md`  
- Acta UI: `documentos/archivo/acta-megadruid-cms-0.3.19.md`  
- Acta visibilidad: `documentos/archivo/acta-megadruid-cms-0.3.20.md`  
- Acta 0–8: `documentos/paquete-megadruid-cms/documentacion/acta-cierre-etapa-8.md`
