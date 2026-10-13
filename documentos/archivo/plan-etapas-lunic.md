# Plan de etapas: tema Lunic y plugin Distribuidora Lunic

**Fecha:** 13/10/2026.
**Detalle de cada tarea:** `documentos/auditoria-tema-y-plugin-lunic.md` (mismo número de tarea).

En Cursor se elige el modelo en el selector del agente antes de cada tarea. Si una tarea se traba dos veces con el modelo indicado, se repite con el de respaldo.

| Nombre | Slug en Cursor |
| --- | --- |
| Composer 2.5 | `composer-2.5` |
| Grok 4.6 medium | `cursor-grok-4.6-medium` |
| Grok 4.7 high | `grok-4.7-high` |
| Claude Opus 4.7 xhigh (solo respaldo) | `claude-opus-4-7-thinking-xhigh` |

## Etapa 1. Carrito visible (urgente)

| Tarea | Qué se hace | Modelo | Respaldo |
| --- | --- | --- | --- |
| 1.1 | Subir el plugin 0.1.16 al live — paquete en `documentos/paquete-distribuidora-lunic-0.1.16.zip`; ver `subida-plugin-0.1.16-tarea-1.1.md` | Composer 2.5 | Grok 4.6 medium |
| 1.2 | `page.php` muestra el carrito de WooCommerce si el widget no imprime nada — hecho en local (tema 0.3.63) | Grok 4.6 medium | Grok 4.7 high |
| 1.3 | Ícono de carrito en celular y tablet — hecho en local (tema 0.3.63) | Composer 2.5 | Grok 4.6 medium |
| 1.4 | Contador del carrito actualizado sin recargar — hecho en local (tema 0.3.63) | Grok 4.6 medium | Grok 4.7 high |

## Etapa 2. Envíos que se reflejan en la página

| Tarea | Qué se hace | Modelo | Respaldo |
| --- | --- | --- | --- |
| 2.1 | Un solo texto de envíos en inicio, carrito y checkout (`[lunic_envios]`) — hecho en local (plugin 0.1.17) | Grok 4.6 medium | Grok 4.7 high |
| 2.2 | Ayuda en el escritorio: dónde se cambia el texto y dónde los importes — hecho en local (plugin 0.1.18) | Composer 2.5 | Grok 4.6 medium |
| 2.3 | Confirmar y mostrar cómo se cobra el envío propio — hecho en local: se abona aparte | Grok 4.7 high | Claude Opus 4.7 xhigh |
| 2.4 | Actualizar `cart-shipping.php` a WooCommerce 11.2 — hecho en local (tema 0.3.64) | Grok 4.6 medium | Grok 4.7 high |

## Etapa 3. Seguridad

| Tarea | Qué se hace | Modelo | Respaldo |
| --- | --- | --- | --- |
| 3.1 | Dejar de bloquear la REST de WooCommerce a visitantes — hecho en local (plugin 0.1.18) | Grok 4.7 high | Claude Opus 4.7 xhigh |
| 3.2 | Un solo endurecimiento (Megadruid CMS); quitar cabeceras duplicadas — hecho en local | Grok 4.7 high | Grok 4.6 medium |
| 3.3 | `?lunic_preview=1` solo para administradores, o quitarlo — quitado | Grok 4.6 medium | Grok 4.7 high |
| 3.4 | Caché de página: clave con dominio, cookie del carrito y vaciado al cambiar productos — hecho en local | Grok 4.7 high | Claude Opus 4.7 xhigh |
| 3.5 | Formulario de contacto contra spam — hecho en local (plugin 0.1.19) | Grok 4.6 medium | Grok 4.7 high |
| 3.6 | Buscador con mínimo de letras y límite por IP — hecho en local (plugin 0.1.19) | Grok 4.6 medium | Grok 4.7 high |
| 3.7 | Importador CSV más estricto — hecho en local (plugin 0.1.20): MIME `text/csv`, `text/plain`, `application/vnd.ms-excel`, máx. 10 MiB | Composer 2.5 | Grok 4.6 medium |
| 3.8 | Texto de descuento sin HTML — hecho en local (plugin 0.1.20): AJAX sin `custom_text`; `.text()` en el widget | Composer 2.5 | Grok 4.6 medium |
| 3.9 | Solo administradores editan plantillas de Elementor — hecho en local (plugin 0.1.20); ver `seguridad-plantillas-elementor.md` | Composer 2.5 | Grok 4.6 medium |

## Etapa 4. Funciones

| Tarea | Qué se hace | Modelo | Respaldo |
| --- | --- | --- | --- |
| 4.1 | Modo mantenimiento que funcione — hecho en local (tema 0.3.65): un solo `template_include`; el mantenimiento gana sobre las plantillas del tema | Grok 4.6 medium | Grok 4.7 high |
| 4.2 | Descuentos por categoría con una sola lógica — hecho en local (plugin 0.1.21): se calculan sobre el precio normal y no se acumulan con la oferta | Grok 4.7 high | Claude Opus 4.7 xhigh |
| 4.3 | Validar CUIT y condición de IVA en el checkout — hecho en local (plugin 0.1.21): CUIT con dígito verificador o DNI de 7–8 dígitos; IVA solo de la lista. Siguen siendo opcionales | Grok 4.6 medium | Grok 4.7 high |
| 4.4 | Carrito vacío con el hook de WooCommerce — hecho en local (tema 0.3.65): `woocommerce_cart_is_empty` y `@version 7.0.1` | Composer 2.5 | Grok 4.6 medium |
| 4.5 | Reemplazos de widgets Pro solo si Pro no está — hecho en local (plugin 0.1.22) | Grok 4.6 medium | Grok 4.7 high |
| 4.6 | Encabezado de Elementor sin el widget Pro de carrito — hecho en local (plugin 0.1.22): widget `woocommerce-menu-cart` y shortcode `[lunic_cart_link]` | Grok 4.6 medium | Grok 4.7 high |
| 4.7 | Declarar propiedades del método de envío (PHP 8.2) — hecho en local (plugin 0.1.23) | Composer 2.5 | Grok 4.6 medium |
| 4.8 | Ofertas del inicio con orden estable — hecho en local (tema 0.3.66): `orderby` = `date` | Composer 2.5 | Grok 4.6 medium |
| 4.9 | Ventanas y galerías de Elementor — hecho en local (tema 0.3.66): solo oculta popup 8261; lightbox habilitado | Composer 2.5 | Grok 4.6 medium |

## Etapa 5. Mantenimiento del código

| Tarea | Qué se hace | Modelo | Respaldo |
| --- | --- | --- | --- |
| 5.1 | Sacar IDs y datos fijos — hecho en local (plugin 0.1.27, tema 0.3.67): Lunic → Datos del sitio; menús solo en Apariencia → Menús; CSS por `.lunic-slot--*` | Grok 4.6 medium | Grok 4.7 high |
| 5.2 | Un solo filtro `template_include` — hecho junto con 4.1 (tema 0.3.65); sigue habiendo uno solo | Grok 4.6 medium | Grok 4.7 high |
| 5.3 | Plantillas de WooCommerce con `@version` — hecho en local (tema 0.3.68) | Composer 2.5 | Grok 4.6 medium |
| 5.4 | Descripción del producto con la API de WordPress — hecho en local (tema 0.3.68): `get_post_field` | Composer 2.5 | Grok 4.6 medium |
| 5.5 | Traducciones sin filtro global — hecho en local (tema 0.3.68): `gettext` solo en carrito, checkout y `wc-ajax` | Composer 2.5 | Grok 4.6 medium |

## Etapa 6. Publicación y cierre

| Tarea | Qué se hace | Modelo | Respaldo |
| --- | --- | --- | --- |
| 6.1 | Copia y subida — paquetes en `documentos/`; el live no se tocó; pasos en `archivo/acta-cierre-etapas-1-6-2026-10-13.md` | Composer 2.5 | Grok 4.6 medium |
| 6.2 | Actualizar `estado.md` y el manual del cliente — hecho (`manual-cliente-lunic.md`) | Composer 2.5 | Grok 4.6 medium |

## Orden

1. Etapa 1 completa.
2. Tareas 2.1 y 2.2.
3. Tareas 3.1 y 3.2, después el resto de la etapa 3.
4. Tareas 4.1 y 4.2.
5. Lo demás, y al final la etapa 6.

## Cuántas tareas tiene cada modelo

| Modelo | Tareas |
| --- | --- |
| Composer 2.5 | 15 |
| Grok 4.6 medium | 13 |
| Grok 4.7 high | 5 |
