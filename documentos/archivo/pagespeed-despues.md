# PageSpeed después de la etapa 9

**Fecha:** 26/09/2026. Host: `https://distribuidoralunic.com.ar.dev/`.  
Lighthouse **12.8.2**, categoría Performance, caché de navegador fría. El visitante anónimo pega en la caché de página de Lunic (TTL 15 min), calentada antes de cada corrida.

JSON: `documentos/linea-base/lighthouse/despues94-*.json`.  
Línea base (25/09/2026, con Elementor Pro, Jet, Ivory y WBW): sección 13 de `analisis-plugin-lunic.md`.

## Antes / después

| URL | Dispositivo | Performance antes | Ahora | LCP antes | LCP ahora |
| --- | --- | --- | --- | --- | --- |
| Inicio (`/`) | móvil | 51 | **85** | 12,3 s | **4,2 s** |
| Inicio (`/`) | escritorio | 55 | **98** | 12,6 s | **1,0 s** |
| Tienda (`/tienda/`) | móvil | 55 | **70** | 9,5 s | **6,5 s** |
| Tienda (`/tienda/`) | escritorio | 55 | **89** | 8,9 s | **1,2 s** |
| Ficha (cardamomo) | móvil | 53 | **69** | 8,5 s | **6,2 s** |
| Ficha (cardamomo) | escritorio | 55 | **94** | 8,0 s | **1,3 s** |
| Checkout anónimo | móvil | 48 | **73** | 12,3 s | **4,0 s** |
| Checkout anónimo | escritorio | 55 | **89** | 8,0 s | **1,1 s** |

El checkout anónimo redirige a `/carro/` (carrito vacío de esa sesión de laboratorio). CLS quedó en 0–0,019. TBT del inicio móvil: 0 ms. JS del inicio: 630 KB → **27 KB**.

La corrida intermedia de la tarea 9.1 (solo móvil, antes de este ajuste) había dejado el inicio en 78 / LCP 4,6 s y la ficha en 72 / LCP 5,7 s. La ficha móvil de esta pasada (69 / 6,2 s) entra en la variación del laboratorio; el escritorio de la misma ficha subió a 94.

## Ajuste de esta pasada

- En la portada no se carga jQuery ni el JS de WooCommerce (el buscador y el menú no lo usan). El CSS de WooCommerce pasa a `media="print"` y se aplica después del primer pintado. La grilla de productos la resuelve `style.css` del tema.
- En tienda, ficha, carrito y checkout los scripts van al pie, para que jQuery no bloquee el primer pintado. Agregar al carrito sigue funcionando.
- La primera diapositiva es `assets/hero-1.webp` (107 KB, antes el JPEG de 154 KB), con `fetchpriority="high"` en la imagen y en el preload.
- Tema `lunic` **0.3.8**.

## LCP de la portada

En móvil el LCP sigue siendo la foto del slider (4,2 s). De ese tiempo, 2,5 s son demora hasta que empieza la descarga y 0,9 s son demora de pintado. Lo que todavía bloquea el render es `style.css` del tema (ahorro estimado 440 ms, junto con `woocommerce-smallscreen.css`). Bajar de ahí implica partir la hoja del tema; no se hizo en esta pasada.

En escritorio el inicio queda en **98**, dentro de la banda 90–98.

## INP del filtro y de agregar al carrito

Lighthouse de laboratorio no simula interacción, igual que en la línea base, así que no hay un número de INP de campo. Lo medido en el navegador:

- Filtro «Blends de té»: el listado pasa a 52 resultados y la URL queda en `?lunic_cat=135`, sin recargar la página. «Blend ICE TEA» pasa a 4 resultados. El manejador del clic no generó un evento de entrada de 16 ms o más (PerformanceEventTiming).
- «Agregar al carrito» en cardamomo: el aviso de WooCommerce apareció y el encabezado pasó de 1 a 2 ítems. El producto de prueba se quitó después; el carrito volvió a Blend Nº 1, subtotal $9.006, envío CABA $4.700.

TBT de laboratorio (proxy de bloqueo del hilo principal): tienda móvil 140 ms, ficha móvil 80 ms, inicio 0 ms.
