# Estado del proyecto

**Fecha:** 13/10/2026.  
**Entorno:** copia local. El sitio en vivo **no se tocó** en este cierre.

Este archivo es el que hay que leer para seguir. El plan de etapas 1–6 de octubre 2026 quedó cerrado y archivado.

## Qué está activo

| Pieza | Versión |
| --- | --- |
| Tema `lunic` | **0.3.68** (`wp-content/themes/lunic/`). Script `theme.js` 0.2.4. |
| Plugin `distribuidora-lunic` | **0.1.27** |
| WooCommerce | 11.2. Moneda ARS. |
| Elementor libre | Activo. Plantillas asignadas en Lunic → Plantillas. |
| Elementor Pro, JetEngine, Ivory Search, Product Filter (WBW) | Apagados. Las carpetas siguen en disco. |
| Megadruid CMS | **0.3.20**. Reemplaza White Label CMS y Megadruid Seguridad (apagados en local). |

## Cierre 13/10/2026 (etapas 1 a 6)

Trabajo hecho en local según `documentos/archivo/auditoria-tema-y-plugin-lunic.md` (plan archivado).

- Carrito visible si el widget de Elementor no imprime nada; ícono y contador en celular y tablet.
- Texto de envíos unificado (`[lunic_envios]`). Importes solo en WooCommerce → Envío. El envío personalizado se abona aparte.
- Seguridad: REST de WooCommerce abierta a visitantes; un endurecimiento (Megadruid CMS); caché de página con dominio canónico y cookie de carrito; contacto y buscador con límite por IP; CSV más estricto; texto de descuento sin HTML; plantillas Elementor solo para administradores.
- Mantenimiento, descuentos por categoría (una sola lógica, sobre precio normal), CUIT/IVA opcionales pero validados, widgets Pro solo si Pro no está, ícono de carrito en el encabezado, envío PHP 8.2, ofertas del inicio por fecha, lightbox de Elementor habilitado (solo se oculta el popup viejo 8261).
- Datos de contacto, WhatsApp y lista de precios en **Lunic → Datos del sitio**. Menús solo en Apariencia → Menús. CSS de plantillas por `.lunic-slot--*`.
- Plantillas WooCommerce con `@version`. Descripción del producto con `get_post_field`. Traducciones de WooCommerce limitadas a carrito, checkout y `wc-ajax`.

Paquetes para el live: `documentos/paquete-tema-lunic-0.3.68.zip` y `documentos/paquete-distribuidora-lunic-0.1.27.zip`.  
Lista de subida: `documentos/subida-live.md`. Manual: `documentos/manual-cliente-lunic.md`.  
Acta: `documentos/archivo/acta-cierre-etapas-1-6-2026-10-13.md`.

## Envíos (comprobado en local)

El plugin **no guarda precios**: lee cada instancia de **Envío personalizado**. Con provincia y CP, CABA, cordones e interior coinciden con WooCommerce. El checkout necesita el código de provincia de WooCommerce (si llega el nombre, se normaliza con `get_states('AR')`).

En cordones, el mínimo de envío gratis se corrige en **WooCommerce → Envío**, no en el código. Andreani es otro plugin: si no cotiza, no aparece como opción.

## Escritorio Lunic

Menú **Lunic** (posición 55.4): Plantillas, Datos del sitio, Texto de envíos, Descuentos por categoría, Menú de categorías.

Opción `lunic_theme_templates` (cabecera, pie, tienda, ficha, mantenimiento). Opción `lunic_site` (contacto y lista de precios). Opción `wcd_discount_rules`. Texto de envíos en `configuraciones['envios']`.

## Megadruid CMS

Plugin **0.3.20** activo en disco (`wp-content/plugins/megadruid-cms/`). **No se versiona** en este repo: código, paquete y actas están en `documentos/megadruid-cms/` (ignorado por Git).

## REST

Visitantes: no se bloquea la Store API de WooCommerce (`/wp-json/wc/store/`). El endurecimiento de rutas de núcleo sigue en Megadruid CMS. Detalle: `documentos/rest-api-lunic.md`.

## Reglas que siguen

- No tocar pedidos, `wp_mainwp_*`, ni tablas de Wordfence.
- No reescribir el contenido de los productos.
- No commitear ni desplegar salvo pedido explícito.
- Al cambiar `style.css`, subir versión en cabecera y en `functions.php`. Si se toca `theme.js`, subir también esa versión.

## Dónde leer

| Para qué | Archivo |
| --- | --- |
| Este estado | `documentos/estado.md` |
| Manual del cliente (Lunic) | `documentos/manual-cliente-lunic.md` |
| Subida al live | `documentos/subida-live.md` |
| Megadruid CMS (fuera del repo) | `documentos/megadruid-cms/` |
| Política REST | `documentos/rest-api-lunic.md` |
| Capturas de diseño | `documentos/linea-base/diseno/` |
| Planes y actas cerrados | `documentos/archivo/` |
