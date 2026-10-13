# Plantillas de Elementor y roles (tarea 3.9)

**Fecha:** 13/10/2026. **Plugin:** `distribuidora-lunic` 0.1.20+.

## Por qué importa

El tema `lunic` imprime el HTML de Elementor tal cual en cabecera, pie, tienda, ficha y mantenimiento. Eso es normal con un constructor, pero implica que **solo personas de confianza** deben poder editar plantillas de la biblioteca (`elementor_library`).

## Qué hace el plugin

Desde la versión **0.1.20**, `includes/class-elementor-access.php` exige la capacidad `manage_options` (administrador) para crear, editar o borrar entradas de `elementor_library`. Un **gestor de tienda** (`shop_manager`) o un **editor** ya no pueden abrir la biblioteca de Elementor ni editar esas plantillas, aunque tengan `edit_posts` en otros contenidos.

## Comprobación en local (13/10/2026)

| Rol | `unfiltered_html` | `manage_options` | Biblioteca Elementor (con 0.1.20) |
| --- | --- | --- | --- |
| Administrador | sí | sí | sí |
| Editor | sí (por defecto en WordPress) | no | no |
| Gestor de tienda | no | no | no |
| Cliente | no | no | no |

## Manual para el cliente

1. Solo cuentas **Administrador** deben diseñar o cambiar plantillas en Elementor.
2. Para operar la tienda (pedidos, productos, envíos, descuentos Lunic), usar **Gestor de tienda** o el menú **Lunic**; no hace falta Elementor.
3. No crear usuarios con rol **Editor** si van a tocar el sitio público: en WordPress ese rol incluye `unfiltered_html` en entradas y páginas normales.
4. Tras subir el plugin 0.1.20 al live, probar con un usuario gestor de tienda: en el escritorio no debe aparecer acceso a **Plantillas** de Elementor ni poder abrir `edit.php?post_type=elementor_library`.

## Archivos del tema que muestran HTML de Elementor (sin cambios)

- `header.php`, `footer.php`, `archive-product.php`, `single-product.php`, `maintenance.php` — vía `lunic_elementor_slot()` en `functions.php`.
