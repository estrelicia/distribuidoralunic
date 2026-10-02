# Acta de cierre — Megadruid CMS etapa 8

**Fecha:** 02/10/2026.  
**Entorno:** copia local `https://distribuidoralunic.com.ar.dev/`. El sitio en vivo no se tocó.  
**Plugin:** Megadruid CMS **0.3.1**.

## 8.1 Plugins viejos apagados

Se desactivaron (sin borrar carpetas ni opciones):

- `white-label-cms/wlcms-plugin.php`
- `megadruid-seguridad/megadruid-seguridad.php`

Siguen en disco y en la base: `wlcms_options` y `wbs_settings`.

Comprobado en un request nuevo de WordPress:

- `WBS_VERSION` y `WLCMS_VERSION` no están definidos.
- Un solo filtro `authenticate` de límite: `Megadruid_Cms\Login_Limit::block_locked` prioridad 5. No hay `WBS_Login`.
- El endurecimiento lo aplica Megadruid CMS (`Hardening::delegated_to_legacy()` = no).
- Menú **Lunic** y **WooCommerce** siguen en el escritorio. Los menús de White Label CMS y Seguridad Megadruid no.
- Un `POST` a `wp-login.php` con usuario y clave inventados responde **Credenciales incorrectas.** (mensaje único).
- El login carga. En esta copia el logo de login de WLCMS estaba vacío, así que se ve el logo de WordPress de fábrica.

## 8.2 Recorrido de la tienda

Sin agregar al carrito, sin pedido y sin vaciar el carrito de prueba (Blend Nº 1). La sesión del navegador de automatización no tiene ese carrito: `/carro/` se vio vacío **solo en esa sesión**. No se tocó el carrito de prueba del sitio.

| Pantalla | Resultado |
| --- | --- |
| Inicio `/` | 200. Cabecera, slider, ofertas, buscador, pie. |
| Tienda `/tienda/` | 200. 691 productos, Blends de té (52), filtro de categorías. |
| Ficha Campestre Frutal | 200. Precio tachado $29.800 y precio $28.310. No se pulsó Agregar. |
| Carrito `/carro/` | 200. |
| Checkout `/finalizar-comprar/` | 302 a `/carro/` si el carrito de esa sesión está vacío (comportamiento de WooCommerce). |
| Login `/wp-login.php` | 200. |

También a **390 px**: portada con menú hamburguesa, WhatsApp y el mismo contenido.

REST anónima:

| Ruta | Código |
| --- | --- |
| `/wp-json/wp/v2/users` | **401** |
| `/wp-json/wp/v2/product` | 401 (lo cierra `distribuidora-lunic`, política ya documentada) |
| `/wp-json/wc/store/v1/products` | 401 (igual) |
| `/wp-json/lunic/v1/search?q=te` | **200** (buscador) |

El catálogo público de productos sigue en HTML (`/tienda/` y fichas). La REST de WooCommerce para invitados no se reabrió: eso lo sigue bloqueando el módulo de seguridad de `distribuidora-lunic`, no Megadruid CMS (`rest_auth_required` sigue apagado en `mdcms_settings`).

## Nota sobre `readme.html`

Una petición HTTP a `/readme.html` devolvió 200 porque Apache sirve el archivo estático de la raíz de WordPress sin pasar por PHP. El hook de Megadruid CMS solo actúa si la petición entra a WordPress. Igual que en Megadruid Seguridad.

## Paquete

Copia del plugin y de esta documentación: `documentos/paquete-megadruid-cms/`.
