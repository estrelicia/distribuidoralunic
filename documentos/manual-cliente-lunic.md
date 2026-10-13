# Manual del cliente — Distribuidora Lunic

**Fecha:** 13/10/2026.  
**Tema:** `lunic` 0.3.68. **Plugin:** `distribuidora-lunic` 0.1.27.

Este archivo es la guía de uso. El estado técnico está en `documentos/estado.md`.

## Escritorio Lunic

Menú **Lunic** (arriba de WooCommerce):

| Pantalla | Para qué |
| --- | --- |
| Plantillas | Asignar encabezado, pie, tienda, ficha y mantenimiento de Elementor |
| Datos del sitio | WhatsApp, teléfono, correo, dirección, horario, lista de precios y página de categorías |
| Texto de envíos | Acordeón informativo de inicio, carrito y checkout |
| Descuentos por categoría | Porcentaje o monto por categoría de producto |
| Menú de categorías | Tres columnas del desplegable «Categorías» |

Logo: **Apariencia → Personalizar → Identidad del sitio**.  
Menús del encabezado y del pie: **Apariencia → Menús**.

Solo cuentas **Administrador** deben editar plantillas de Elementor. Para pedidos y productos alcanza **Gestor de tienda**.

## Envíos: dos lugares distintos

Cambiar uno no cambia el otro.

1. **Lunic → Texto de envíos** — texto del acordeón. No mueve importes.
2. **WooCommerce → Ajustes → Envío** — en cada zona, método **Envío personalizado**: precio, mínimo para envío gratis y acarreo.

El importe del envío personalizado se muestra **debajo del total** y **se abona aparte** (no entra en el total de WooCommerce ni en una pasarela). Andreani, si cotiza en la misma zona, sí entra en el total.

En el checkout hace falta país Argentina, provincia del desplegable, ciudad y código postal. Sin dirección, WooCommerce no muestra tarifas.

## Qué subir al sitio en vivo

Subir **juntas** las carpetas:

- `wp-content/themes/lunic/` (0.3.68)
- `wp-content/plugins/distribuidora-lunic/` (0.1.27)

Paquetes locales: `documentos/paquete-tema-lunic-0.3.68.zip` y `documentos/paquete-distribuidora-lunic-0.1.27.zip`.

Después: entrar al escritorio una vez, vaciar `wp-content/cache/lunic/`, completar **Lunic → Datos del sitio** y revisar **WooCommerce → Envío** (mínimos de envío gratis en cada zona). Recorrer inicio, tienda, ficha, carrito, checkout, cuenta y contacto, en escritorio y en celular.
