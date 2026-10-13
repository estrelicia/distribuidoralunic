# REST API — política actual

Implementada en `distribuidora-lunic` (módulo security) y en **Megadruid CMS** (endurecimiento).

## Visitantes no autenticados

- Las rutas de núcleo `/wp/v2/` (usuarios, etc.) siguen restringidas por el endurecimiento de Megadruid CMS.
- **Store API de WooCommerce** (`/wp-json/wc/store/` y relacionadas) **no se bloquea**: el carrito y el checkout las necesitan. Cambio de la tarea 3.1 (octubre 2026).

## Usuarios con sesión

Acceso REST según las capacidades de WordPress.

## Qué no se bloquea

- Checkout clásico (`admin-ajax.php` / `?wc-ajax=`).
- Store API de WooCommerce para invitados.
- REST con cookie de sesión válida.
