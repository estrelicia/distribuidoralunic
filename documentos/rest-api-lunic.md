# REST API — política actual (tarea 1.5)

Implementada en `distribuidora-lunic` → módulo `security` (`class-hardening.php`). Replica lo que estaba en el tema hijo.

## Visitantes no autenticados

| Comportamiento | Detalle |
| --- | --- |
| Autenticación REST | Cualquier petición a `/wp-json/` devuelve **401** con código `rest_not_logged_in` si no hay sesión de WordPress. |
| Rutas `/wp/v2/*` | Además se eliminan del mapa de endpoints para invitados (doble capa). |
| Enlaces en HTML | No se imprimen `rest_output_link` ni cabecera `Link` hacia la API. |

## Usuarios con sesión (admin, editor, etc.)

Acceso REST completo según sus capacidades de WordPress.

## Qué no se bloquea

- **Checkout clásico** de WooCommerce (formulario + `admin-ajax.php` / `?wc-ajax=`): no depende de la REST pública.
- **REST con cookie de sesión** válida (p. ej. editor en el admin o Gutenberg logueado).
- **`/wp-json/lunic/v1/search`**: sugerencias del buscador, también para invitados. No reabre `/wp/v2`.

## Otros endurecimientos relacionados

- XML-RPC deshabilitado (`xmlrpc_enabled` → false).
- Feeds RSS/Atom deshabilitados (`wp_die` en hooks `do_feed*`).
- Pingbacks cerrados (`pings_open` → false).

## Etapas futuras

Si en la etapa 2 se expone un endpoint propio de búsqueda (`/wp-json/lunic/v1/...`), habrá que **excluirlo** del filtro `rest_authentication_errors` para invitados o registrarlo con autenticación explícita documentada aquí.
