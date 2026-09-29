=== Megadruid Seguridad ===
Contributors: megadruid
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.0.2
License: GPLv2 or later

Capa base de seguridad para WordPress detrás de Cloudflare.

== Description ==

Plugin de Megadruid para instalarse en los sitios de la agencia.
Funciona aunque otro plugin (agencia-core, Wordfence, etc.) ya mitigue parte de lo mismo: las protecciones se aplican igual.

Complementa el WAF de Cloudflare (plan Free). No reemplaza 2FA ni las reglas Custom/Rate Limit del panel CF.

= Lo que hace por defecto =

* IP real vía `CF-Connecting-IP` solo si `REMOTE_ADDR` está en rangos oficiales de Cloudflare. Nunca `X-Forwarded-For`.
* Límite de login en el filtro `authenticate` (prioridad 5): bloquea **antes** de validar la contraseña. 5 fallos / 15 min por IP.
* Mensaje de login genérico (sin enumerar usuarios).
* XML-RPC y pingbacks desactivados.
* `/?author=` y archivos de autor → 301 a la home; autores fuera del sitemap y del oEmbed.
* REST `/wp/v2/users` oculta a anónimos. WooCommerce y Elementor siguen funcionando.
* 404 a `readme.html` y `license.txt`.
* Oculta versión de WordPress.
* Feeds RSS desactivados.
* Editor de archivos del admin desactivado.
* Contraseñas de aplicación desactivadas.
* Cabeceras: X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy.

= Opciones que vienen apagadas =

* REST anónima 100% cerrada (rompe Woo / Elementor público). Activar solo en brochure tipo megadruid.com.
* HSTS desde PHP (Cloudflare ya lo envía).

= Cloudflare =

El sitio debe estar con nube naranja. Activar IP Geolocation en el panel CF.

== Changelog ==

= 1.0.2 =
* Marca Megadruid: textos, text domain y menú de ajustes.

= 1.0.1 =
* Carpeta `megadruid-seguridad`.

= 1.0.0 =
* Primera versión.
