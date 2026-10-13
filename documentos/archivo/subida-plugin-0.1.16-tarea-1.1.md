# Tarea 1.1 — Subir Distribuidora Lunic 0.1.16 al live

**Fecha:** 13/10/2026.

## Qué se preparó en local

- Plugin verificado en `wp-content/plugins/distribuidora-lunic/` versión **0.1.16** (commit `b8ce055f`).
- Migración Elementor ya aplicada en local: `lunic_elementor_pro_woo_migrated = 3`.
- Widget registrado: `Distribuidora_Lunic\Elementor_Woo_Cart_Widget` en la página Carrito (ID 7).
- Paquete para el hosting: **`documentos/paquete-distribuidora-lunic-0.1.16.zip`** (carpeta `distribuidora-lunic` lista para descomprimir).

## Antes de subir (en el live)

1. Copia de la base de datos del sitio en producción.
2. Copia de la carpeta actual `wp-content/plugins/distribuidora-lunic/` en el servidor (por si hay que volver atrás).

## Subida en el hosting

1. Desactivar el plugin **Distribuidora Lunic** en el escritorio del live (opcional pero recomendable).
2. Por FTP, SFTP o el administrador de archivos del hosting:
   - Borrar o renombrar `wp-content/plugins/distribuidora-lunic/` (por ejemplo `distribuidora-lunic-backup-0.1.12`).
   - Subir el contenido del zip de modo que quede `wp-content/plugins/distribuidora-lunic/distribuidora-lunic.php` (no anidar otra carpeta `distribuidora-lunic` dentro).
3. Activar de nuevo **Distribuidora Lunic**.
4. Entrar al escritorio de WordPress **una vez** con un usuario administrador (dispara la migración de widgets Pro → fallbacks si en el live la opción no está en `3`).
5. Vaciar la caché del plugin: borrar `wp-content/cache/lunic/*.html` o en local **Lunic → Plantillas → Guardar** (vacía la caché).
6. Si usás caché del hosting (LiteSpeed, Cloudflare, etc.), purgar también ahí.

## Comprobar en el live

| Comprobación | Resultado esperado |
| --- | --- |
| `…/wp-content/plugins/distribuidora-lunic/assets/css/elementor-preview.css` | HTTP 200 (hoy da 404 con 0.1.12) |
| `…/distribuidora-lunic/assets/js/search.js?ver=` | `ver=0.1.16` |
| `/carro/` con al menos un producto | Tabla del carrito, totales, «Finalizar Compra» |
| `/carro/` vacío | Mensaje de carrito vacío (no bloque HTML vacío del widget) |
| `/finalizar-comprar/` | Formulario de checkout |
| `/mi-cuenta/` | Cuenta de cliente |

## Estado del live antes de esta subida (referencia)

- Tema: `lunic` **0.3.62**
- Plugin: **0.1.12** (widget Cart de Elementor sin reemplazo → contenedor vacío en `/carro/`)

## Después de subir

Marcar la tarea 1.1 como hecha en el plan y seguir con **1.2** (red de seguridad en `page.php`) si hace falta.
