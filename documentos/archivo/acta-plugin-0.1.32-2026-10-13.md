# Acta — plugin 0.1.29 a 0.1.32 (13/10/2026)

Cierra el trabajo posterior al acta de etapas 1 a 6 (plugin 0.1.27, tema 0.3.68).

## Versiones

| Pieza | Versión | Qué pasó |
| --- | --- | --- |
| Tema `lunic` | **0.3.68** | No se modificó. `theme.js` sigue en 0.2.4. |
| Plugin `distribuidora-lunic` | **0.1.32** | Cambios de esta acta. |

La plantilla `wp-content/themes/lunic/woocommerce/cart/cart-shipping.php` oculta el importe solo cuando el único método es el envío propio. Andreani y cualquier otro método siguen en la lista y entran en el total de WooCommerce. No hizo falta tocarla.

## 0.1.29 — revisión del plugin

- Mi cuenta actualiza la dirección de la sesión sin pisar un envío ya elegido, y el carrito puede cotizar con la facturación.
- El mínimo de envío gratis en 0 ya no deja el envío gratis siempre.
- El hash de precios de variaciones incluye las reglas de descuento. La etiqueta usa decimales localizados.
- Una oferta vencida no bloquea las reglas de descuento por categoría.
- La IP del visitante sale de Megadruid Seguridad si ese plugin está activo.
- El formulario de contacto usa el correo de **Lunic → Datos del sitio** y avisa si el envío del mail falla.
- La categoría excluida del filtro y la página de categorías salen de los ajustes, no de un ID fijo.
- La búsqueda no sugiere productos ocultos y corta el texto pedido.
- `Referrer-Policy` y `Permissions-Policy` quedan en valores que el navegador acepta. Se sacó `X-XSS-Protection`.

## 0.1.30 — carrito con solo facturación

WooCommerce no calcula envío si en la sesión faltan provincia o código postal de envío. Con la tienda en «enviar a la dirección de facturación», el plugin completa esos datos en la sesión a partir de la facturación. No reescribe la dirección guardada en la cuenta.

## 0.1.32 — código postal, buscador y caché

### Andreani y el código postal

Andreani guarda en la sesión el último código postal cotizado (`andreani_cp_destino`) y, al abrir el checkout, lo copia sobre facturación y envío. Por eso un checkout a Tandil 7000 podía mostrar 5152.

Antes de que Andreani haga esa copia, el plugin pone en esa clave el código postal que ya tiene el cliente. Al guardar la dirección en Mi cuenta, si Andreani tenía un código guardado, también se actualiza.

El plugin **no quita** tarifas de Andreani ni de ningún otro método.

### Cuándo cotiza Andreani

Andreani solo cotiza si **cada** producto del carrito tiene peso y alto, ancho y largo. Si a uno le falta cualquiera, no devuelve opciones. El acarreo del envío propio sigue, porque no pide esas medidas.

En una prueba de carrito de visitante en el live (13/10/2026), Eucalipto Hojas Secas de 1 kg hacia Tandil 7000, Mar del Plata 7600 y Córdoba 5000 devolvió solo el acarreo de $4.700. No se creó ningún pedido.

En la ficha del live, las variantes de Eucalipto, Cebada Perlada, Cola de Caballo, Hongos de Pino, Cedrón, Ortiga, Tilo y Cedrón prensado figuran con peso y medidas en «N/D». En esta copia local algunas variantes de 1 kg sí tienen esos datos. Hay que cargarlos en el WooCommerce del live; el plugin no los inventa ni reescribe fichas.

CABA y los tres cordones no tienen el método Andreani. Provincia de Buenos Aires y el envío nacional sí.

En esta copia local la API de Andreani no se puede probar: el antivirus intercepta HTTPS y PHP recibe un error de certificado. Eso no pasa en el live.

### Buscador y `/tienda`

En el live, sin Megadruid CMS, carga la clase `Hardening`. Esa clase enganchaba el cartel de oferta a un método que no tiene (el método está en el módulo de seguridad). La tienda cortaba con un error fatal. Esos dos enganches duplicados se sacaron de `Hardening`. El cartel «Oferta» sigue en el módulo de seguridad.

La caché de página (`wp-content/cache/lunic/`, 15 minutos) usaba un nombre de archivo que no cambiaba al subir el plugin por FTP, y servía HTML viejo junto a JS y CSS nuevos. Ahora el nombre incluye la versión del plugin, y al cambiar la versión del plugin o del tema se vacía la carpeta.

El script del buscador engancha cada formulario `.lunic-search`, no solo el primero. Si el producto no tiene foto, igual reserva el recuadro. Una respuesta que llega tarde no pisa lo que el visitante ya escribió.

## Qué subir

Tema 0.3.68 y plugin 0.1.32 juntos. Pasos: `documentos/subida-live.md`.
