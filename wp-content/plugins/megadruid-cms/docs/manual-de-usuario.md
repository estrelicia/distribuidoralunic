# Manual de usuario — Megadruid CMS

Este texto es para quien administra el sitio. No hace falta saber PHP. Los cambios se prueban en local antes de llevarlos a producción.

La misma guía vive dentro de WordPress: **Ajustes → Megadruid CMS → Manual**. En esa pantalla también está la pestaña **Ayuda** de WordPress (arriba a la derecha).

**Versión del plugin:** 0.3.20  
**Dónde se guarda:** una sola opción, `mdcms_settings`.  
**Quién puede entrar:** las cuentas tildadas en **Menús → Administradores con escritorio completo**. Tienen que poder gestionar opciones. Si la lista está vacía, cualquiera con esa capacidad ve el plugin (para no quedar afuera).

## Por dónde empezar

1. Andá a **Ajustes → Megadruid CMS**.
2. Ajustá el login en **Login**. Guardá cada pestaña por separado.
3. En **Menús** hay dos bloques: **menús de wp-admin** (a quién y qué ocultar, en la misma tarjeta) y **barra negra en la tienda**.
4. Revisá **Seguridad**: los valores de fábrica ya sirven para una tienda detrás de Cloudflare.

En la lista de plugins hay enlaces **Ajustes** y **Manual**, solo para esas mismas cuentas.

## Quién ve el plugin y el recorte

**Menús del escritorio** es wp-admin: menú izquierdo y atajos de la barra negra dentro del panel. En esa misma tarjeta agregás a quién se le oculta lo tildado.

**Barra negra en la tienda** es otra cosa: la barra de WordPress arriba cuando alguien navega el sitio público logueado. No es el menú de la izquierda.

Las cuentas tildadas en **Administradores con escritorio completo** son las únicas que ven Megadruid CMS en WordPress (menú Ajustes, fila en Plugins, URL de ajustes). El resto no lo ve ni puede desactivarlo. Esas mismas cuentas no se recortan. Si la lista está vacía, cualquiera con gestionar opciones ve el plugin y el escritorio completo (para no quedar afuera). Un administrador nuevo no entra hasta que lo marques. No podés destildarte a vos mismo.

La marca (login, barra, caja **Megadruid accesos directos** en el Escritorio) sigue visible para el resto: eso no es el panel del plugin.

## Marca fija

La barra de admin (isotipo) y el título «Megadruid AD» son fijos. El contacto está en la caja del Escritorio. El menú lateral de WordPress no lleva logo de Megadruid.

## Pestaña Login

Solo dos cosas: **logo del cliente** (medida recomendada **320 × 84 px**, PNG o SVG; se ajusta al recuadro sin recortar) e **imagen de fondo** opcional (**1920 × 1080 px**). El formulario sigue siendo el de WordPress.

**Vista previa del login** abre `wp-login.php` con un borrador de 15 minutos. No escribe la opción definitiva. **Guardar cambios** sí la actualiza.

## Pestaña Escritorio

Ocultar cada panel del Escritorio de WordPress (nativos y de otros plugins) por rol. En el Escritorio está la caja **Megadruid accesos directos** (portal del cliente y contacto).

En la misma pestaña: ocultar Ayuda y Opciones de pantalla del núcleo y avisos de actualización.

Exportar / importar JSON (máximo 256 KB, sin PHP) y restablecer de fábrica están **fuera** del botón Guardar: usan sus propios formularios para no mezclarse con el resto de la pestaña. Solo las cuentas que ven el plugin pueden usarlos.

En **esta** pantalla del plugin la pestaña Ayuda de WordPress no se oculta, aunque hayas marcado ocultarla en el resto del escritorio.

## Pestaña Menús

1. Quiénes ven este plugin y wp-admin completo.  
2. **Barra negra en la tienda:** sitio público, persona logueada.  
3. **Menús del escritorio:** primero a quién se le oculta; abajo el mapa (casillas). Lo tildado se esconde a esos perfiles y no se abre por URL. El recorte de menús ya no deja entrar a Megadruid CMS: solo las cuentas tildadas arriba.

## Pestaña Seguridad

La tabla muestra la IP de esta visita, si viene de Cloudflare y el país. El límite de login corta a los 5 fallos en 15 minutos (configurable). El mensaje de error no dice si el usuario existe.

No actives **REST anónima cerrada por completo** en esta tienda: WooCommerce y el buscador la necesitan. Ocultar usuarios de la REST sí puede quedar activo.

`readme.html` puede seguir viéndose si el servidor entrega el archivo sin pasar por WordPress. Eso no lo resuelve este plugin solo.

Si Megadruid Seguridad sigue activo, al guardar esta pestaña se sincronizan las mismas claves en `wbs_settings`.

## Qué no hace este plugin

- Envíos, descuentos, menú de categorías ni checkout: eso es Distribuidora Lunic y el tema Lunic.
- No borra White Label CMS ni Megadruid Seguridad ni sus opciones al desinstalarse. Solo borra `mdcms_settings` y `mdcms_dashboard_panels`.
- No sustituye Wordfence, 2FA ni las reglas del WAF de Cloudflare.
