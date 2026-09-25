# Etapa 1 — tareas 1.4 y 1.5

Fecha: 25/09/2026.

## 1.4 Checkout fiscal

**Origen:** tema hijo `hello-theme-child-master/functions.php` (campos y meta de pedido).

**Destino:** `wp-content/plugins/distribuidora-lunic/includes/modules/checkout/`

| Campo checkout | Meta de pedido (sin cambiar clave) |
| --- | --- |
| `cuitcuil` | `CUIT/CUIL o DNI` |
| `condicioniva` | `Condición frente al IVA` |

Opciones de IVA: Consumidor Final, Responsable Monotributo, IVA Responsable Inscripto, IVA Responsable no Inscripto, IVA no Responsable, IVA Sujeto Exento.

- Guardado con `$order->update_meta_data()` (compatible HPOS).
- Caja en el admin del pedido: `woocommerce_admin_order_data_after_billing_address`.

**Prueba CLI (pedido simulado):** meta `20123456789` y `Consumidor Final` persistidos y leídos con `get_meta()`.

## 1.5 Seguridad y tienda

**Origen:** mismo `functions.php` del tema hijo.

**Destino:** `wp-content/plugins/distribuidora-lunic/includes/modules/security/`

Incluye:

- Cabeceras HTTP (X-Frame-Options, CSP `upgrade-insecure-requests`, HSTS, Permissions-Policy, etc.).
- XML-RPC off, limpieza de `<head>`, feeds deshabilitados, sin `wp-embed`.
- REST para invitados: ver `documentos/rest-api-lunic.md`.
- Etiqueta de oferta: `Sale!` → **Oferta** (`woocommerce_sale_flash`).
- Emails transaccionales diferidos en checkout (`woocommerce_defer_transactional_emails`).
- `the_generator` vacío; sin emails de auto-actualización de plugins/temas.

**Prueba cabeceras** (respuesta local): `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security` presentes.

El tema hijo conserva solo el filtro de ocultar envío gratis frente a otros métodos y el CSS del child; la lógica duplicada se eliminó del `functions.php`.
