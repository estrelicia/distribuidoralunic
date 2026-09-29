# Plugin de CMS y seguridad

**Fecha:** 29/09/2026.  
**Dónde se prueba:** copia local `https://distribuidoralunic.com.ar.dev/`. No se publica y no se toca el sitio en vivo.

Plugin nuevo, código propio, que reúne lo que hoy hacen dos plugins distintos:

- **White Label CMS** 2.7.14 (`wp-content/plugins/white-label-cms/`). Marca el escritorio, el login, los menús y el panel de bienvenida. Los ajustes viven en la opción `wlcms_options`.
- **Megadruid Seguridad** 1.0.2 (`wp-content/plugins/megadruid-seguridad/`). Capa para un WordPress detrás de Cloudflare. Los ajustes viven en `wbs_settings`.

Nombre propuesto del plugin: **Megadruid CMS**. Carpeta `wp-content/plugins/megadruid-cms/`. Text domain `megadruid-cms`. Opción única `mdcms_settings`. PHP 8.1. WordPress 6.0.

No se copian archivos, CSS, JavaScript ni imágenes de White Label CMS. Se reescribe el comportamiento. Los dos plugins viejos siguen activos hasta la etapa 9. Si los dos están activos a la vez, las protecciones de seguridad no deben duplicar el bloqueo de login.

## Modelos

En Cursor, antes de cada tarea, elegir el modelo en el selector del agente. Los tres que se usan acá:

| Nombre | Slug en Cursor | Para qué |
| --- | --- | --- |
| Composer 2.5 | `composer-2.5` | Pantallas, CSS, textos, andamiaje y tareas con el comportamiento ya definido. |
| Grok 4.6 medium | `cursor-grok-4.6-medium` | Hooks, formularios y el cableado entre ajustes y el escritorio. |
| Grok 4.7 high | `grok-4.7-high` | Login, IP, REST, menús del admin y cualquier campo que acepte CSS o HTML. |

Cada tarea indica un solo modelo. Si la tarea se traba, se repite con Grok 4.7 high. No hace falta otro modelo.

## Reglas para todas las etapas

- Capability de la pantalla de ajustes: `manage_options`. Nonce en cada guardado, importación, exportación y vista previa.
- El administrador que configura la marca sigue viendo WordPress completo. Las restricciones de menú aplican a los roles marcados, no a quien tiene la marca apagada para sí mismo.
- En esta tienda la REST anónima queda abierta. WooCommerce y Elementor la usan. La opción «cerrar toda la REST» nace apagada.
- No se confía en `X-Forwarded-For`. La IP del visitante sale de `CF-Connecting-IP` solo si `REMOTE_ADDR` está en los rangos oficiales de Cloudflare.
- El CSS y el JavaScript del login se guardan solo para quien administra. No se acepta PHP en esos campos.
- No se hace `unserialize` de un archivo subido. La importación es JSON validado.
- No se cambia el tema `lunic`, el plugin `distribuidora-lunic` ni los pedidos. No se vacía el carrito de prueba.
- Al terminar cada etapa, probar en el escritorio local. No activar el plugin nuevo en el sitio en vivo.

## Qué tiene que hacer

### Marca

- Ícono del desarrollador, nombre y pie con imagen, enlace y HTML.
- Logo de la barra de admin, ancho, texto alternativo, URL y reemplazo del texto «Hola».
- Imagen del menú lateral (abierto y colapsado), enlace y texto alternativo.
- Ocultar el logo y los enlaces de WordPress en la barra.
- Ocultar la versión de WordPress en el pie del admin.
- Título de las pantallas del admin.
- Ícono de salida del editor Gutenberg.

### Login

- Logo, logo retina, margen, ancho y alto.
- Color de fondo, imagen, pantalla completa, posición y repetición.
- Ocultar «Registrarse», «¿Olvidaste la contraseña?» y «Volver al sitio».
- Colores del formulario, etiquetas, botón, hover y enlaces (incluido el de privacidad).
- CSS propio y JavaScript propio.
- Vista previa sin guardar todavía en la opción definitiva.

### Escritorio

- Título del escritorio.
- Ocultar paneles por rol: de un vistazo, actividad, comentarios recientes, borrador rápido, noticias y eventos, y «ocultar todos».
- Quitar el recuadro vacío cuando no queda ningún panel.
- Hasta dos paneles de bienvenida, cada uno visible por rol, con o sin título.
- Tipos de bienvenida: HTML, una página de WordPress, una plantilla de Elementor y una de Beaver Builder.
- Panel RSS propio: cantidad de ítems, contenido del post, introducción, logo y título.

### Menús

- Lista de menús laterales y de la barra de admin, con casillas para ocultarlos.
- Rol «admin de marca»: ve el escritorio recortado y no entra a los menús ocultos aunque escriba la URL.
- La barra de admin se puede ocultar por completo en el sitio público.
- Al reconstruir el menú, WooCommerce y el menú Lunic no se desarman si no fueron marcados para ocultarse.

### Ajustes generales

- Ocultar la pestaña Ayuda y Opciones de pantalla.
- Ocultar avisos de actualización de WordPress en el escritorio.
- CSS extra del admin.
- Hoja de estilos del editor (URL o ruta del tema).
- Cajas de entradas: extracto, slug, etiquetas, autor, comentarios, revisiones, debate, categorías, campos personalizados, trackbacks.
- Cajas de páginas: campos personalizados, autor, debate, revisiones, atributos, slug.
- Asistente de puesta en marcha (logo, nombre, pie, panel de bienvenida).
- Exportar e importar JSON. Restablecer a los valores de fábrica.
- Enlace «Ajustes» en la lista de plugins.

### Seguridad (lo que hoy es Megadruid Seguridad)

Valores de fábrica, seguros para esta tienda:

| Ajuste | Fábrica |
| --- | --- |
| Confiar IP de Cloudflare | sí |
| Límite de login | sí, 5 fallos en 15 minutos, entre 3 y 20 fallos y entre 5 y 120 minutos |
| Error de login genérico | sí |
| XML-RPC y pingbacks | apagados |
| `?author=` y `/author/` | redirigen a la home; autores fuera del sitemap y del oEmbed |
| REST `/wp/v2/users` | oculta a quien no tiene `list_users` |
| REST anónima cerrada por completo | no |
| `readme.html` y `license.txt` | 404 |
| Versión de WordPress en el HTML y `?ver=` del núcleo | oculta |
| Feeds RSS y Atom | apagados |
| Editor de archivos (`DISALLOW_FILE_EDIT`) | apagado |
| Contraseñas de aplicación | apagadas |
| Cabeceras `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy` | sí |
| HSTS desde PHP | no (Cloudflare ya lo envía) |
| Cabecera `X-Powered-By` | se quita |
| Enlaces RSD, WLW, shortlink y posts adyacentes | se quitan |

La pantalla muestra la IP detectada, si `REMOTE_ADDR` es Cloudflare y el país `CF-IPCountry`.

El límite de login corre en `authenticate` con prioridad 5, antes de comprobar la contraseña. Cuenta fallos por IP. Un acierto limpia el contador.

## Etapa 0 — Esqueleto

**0.1 Arranque del plugin.** Composer 2.5.  
Archivo principal, constantes, carga de clases, activación que crea `mdcms_settings` si no existe, desinstalación que borra solo esa opción. Menú en Ajustes → Megadruid CMS, con pestañas vacías: Marca, Login, Escritorio, Menús, Ajustes, Seguridad.  
Hecho cuando el plugin se activa en local y la pantalla abre sin avisos de PHP.

**0.2 Lectura y guardado.** Grok 4.6 medium.  
Una clase de ajustes: valores de fábrica, `get`, `update`, sanitización por tipo (texto, URL, color, entero, booleano, lista de roles, HTML permitido). Nonce y `manage_options`.  
Hecho cuando un campo de prueba se guarda y vuelve a leerse, y un usuario sin `manage_options` recibe error.

**0.3 Quién ve las restricciones.** Grok 4.7 high.  
Dos preguntas en código: «¿este usuario administra la marca?» y «¿este usuario recibe el escritorio recortado?». Quien administra la marca no pierde menús. El rol se toma del usuario, con un filtro por si tiene varios roles.  
Hecho cuando un administrador de prueba sigue viendo Ajustes y un rol editor de prueba queda marcado como destinatario de las restricciones, sin aplicarlas todavía.

## Etapa 1 — Marca

**1.1 Barra de admin.** Grok 4.6 medium.  
Logo, ancho, texto alternativo, URL, texto «Hola» y ocultar el menú de WordPress. El logo abre la URL configurada.  
Hecho cuando la barra muestra el logo y el saludo nuevo, y el ícono de WordPress desaparece solo si la casilla está activa.

**1.2 Menú lateral y pie.** Composer 2.5.  
Imagen arriba del menú (abierta y colapsada), enlace, texto alternativo. Pie con imagen, URL y HTML. Ocultar la versión en el pie. Título de la pestaña del navegador en el admin.  
Hecho cuando el menú colapsado usa la imagen chica y el pie ya no dice la versión de WordPress.

**1.3 Gutenberg.** Composer 2.5.  
Ícono de salida del editor, el de WordPress o uno subido.  
Hecho cuando al editar una página el botón de salir usa el ícono elegido.

## Etapa 2 — Login

**2.1 Logo y fondo.** Grok 4.6 medium.  
Logo, retina, medidas, color, imagen, posición y repetición. El CSS se imprime solo en `wp-login.php`.  
Hecho cuando la pantalla de login local muestra logo y fondo, y la tienda pública no carga ese CSS.

**2.2 Formulario y enlaces.** Composer 2.5.  
Colores de etiquetas, botón y enlaces. Ocultar registro, contraseña perdida y volver al sitio.  
Hecho cuando esas tres casillas ocultan los enlaces y el botón usa el color guardado.

**2.3 CSS, JavaScript y vista previa.** Grok 4.7 high.  
CSS propio escapado como CSS. JavaScript solo si quien guarda tiene `manage_options`, y se imprime en el pie del login, no en el sitio público. La vista previa guarda en un transient de corta vida, no en `mdcms_settings`, hasta que se confirma.  
Hecho cuando la vista previa cambia el login sin tocar la opción definitiva, y un guardado real sí la actualiza.

## Etapa 3 — Escritorio

**3.1 Paneles nativos.** Grok 4.6 medium.  
Título del escritorio. Ocultar cada panel, u ocultar todos, solo para los roles elegidos. Quitar el borde vacío.  
Hecho cuando un editor deja de ver «De un vistazo» y un administrador de marca lo sigue viendo.

**3.2 Bienvenida HTML y página.** Grok 4.6 medium.  
Dos paneles. HTML con `wp_kses_post`. Página de WordPress renderizada en el escritorio, sin la barra de admin del frente. Buscador de páginas por AJAX, con nonce y `manage_options`.  
Hecho cuando el escritorio muestra el HTML y, en el segundo panel, el contenido de una página elegida.

**3.3 Bienvenida Elementor y Beaver.** Grok 4.7 high.  
Si Elementor está activo, el panel puede mostrar una plantilla de la librería. Si Beaver Builder está activo, lo mismo con su layout. Si el constructor no está, el selector no se ofrece. No se ejecuta el constructor en el frente de la tienda.  
Hecho cuando una plantilla de Elementor ya publicada se ve en el escritorio y la ficha de producto no cambia.

**3.4 RSS.** Composer 2.5.  
Panel con feed, cantidad, extracto o contenido, logo y título. El feed se cachea en un transient.  
Hecho cuando el panel lista la cantidad pedida y un feed inválido muestra un aviso, no un error de PHP.

## Etapa 4 — Menús

**4.1 Inventario.** Grok 4.7 high.  
Leer `$menu`, `$submenu` y la barra de admin después de que los plugins registraron los suyos. Guardar qué ítems están ocultos por rol. Excluir de la lista el propio menú de Megadruid CMS para no poder ocultarlo y quedar afuera.  
Hecho cuando la pantalla lista WooCommerce, Lunic, Entradas, Páginas y Ajustes, y el guardado conserva solo slugs que existían.

**4.2 Aplicar el recorte.** Grok 4.7 high.  
Quitar ítems del menú lateral y de la barra para el rol marcado. Bloquear el acceso directo a la URL del ítem oculto. No reconstruir de cero los submenús de WooCommerce ni el de Lunic si no están en la lista de ocultos.  
Hecho cuando un editor no ve el menú marcado ni puede abrirlo por URL, y el administrador de marca sigue entrando. Con nada marcado, el escritorio queda igual que hoy.

**4.3 Barra en el sitio público.** Composer 2.5.  
Casilla para ocultar la barra de admin en el frente, por rol.  
Hecho cuando el rol marcado navega la tienda sin barra y el administrador de marca la conserva.

## Etapa 5 — Ajustes generales

**5.1 Ayuda, avisos y CSS de admin.** Grok 4.6 medium.  
Ocultar Ayuda y Opciones de pantalla. Quitar los avisos de actualización del núcleo. CSS extra solo en el admin, sanitizado. Hoja del editor por URL absoluta o ruta relativa al tema activo.  
Hecho cuando las dos pestañas desaparecen, el aviso de actualización del núcleo no se muestra y el editor carga la hoja indicada.

**5.2 Cajas de entradas y páginas.** Composer 2.5.  
`remove_meta_box` para las cajas listadas arriba, solo en el rol marcado.  
Hecho cuando «Extracto» desaparece en entradas y «Atributos de página» desaparece en páginas, y el resto de cajas sigue.

**5.3 Asistente.** Composer 2.5.  
Cuatro pasos: logo de login, nombre y pie, panel de bienvenida, confirmación. Escribe en las mismas claves que las pestañas. Se puede saltar.  
Hecho cuando al terminar el asistente el login y el escritorio muestran lo elegido, sin una segunda opción en la base.

## Etapa 6 — Importar, exportar y traer lo ya configurado

**6.1 JSON.** Grok 4.7 high.  
Exportar `mdcms_settings` como JSON con nonce. Importar solo claves conocidas, con los mismos sanitizadores del guardado. Rechazar el archivo si no es JSON o si supera un tamaño corto. Botón de restablecer con confirmación.  
Hecho cuando un exportar/importar en local devuelve los mismos ajustes, y un archivo con una clave desconocida o con PHP no la guarda.

**6.2 Traer White Label CMS y Megadruid Seguridad.** Grok 4.7 high.  
Lectura de una sola vez de `wlcms_options` y `wbs_settings` hacia `mdcms_settings`. No borra las opciones viejas. Mapa explícito de cada clave. Lo que no tenga equivalente se ignora y se anota en un aviso de admin.  
Hecho cuando, en local, el logo y el límite de login coinciden con lo que ya estaba configurado, y las opciones viejas siguen en la base.

## Etapa 7 — Seguridad

**7.1 IP de Cloudflare.** Grok 4.7 high.  
Rangos oficiales IPv4 e IPv6, refresco periódico, comprobación de que `REMOTE_ADDR` pertenece a Cloudflare antes de leer `CF-Connecting-IP`. Función de IP del cliente usada por el límite de login. País desde `CF-IPCountry` solo como dato en la pantalla.  
Hecho cuando, con una petición local sin Cloudflare, la IP es `REMOTE_ADDR`, y un test con `REMOTE_ADDR` de Cloudflare y `CF-Connecting-IP` de laboratorio usa esa segunda IP.

**7.2 Límite de login.** Grok 4.7 high.  
Bloqueo en `authenticate` prioridad 5. Contador por IP en transient. Mensaje único que no dice si el usuario existe. Un login correcto limpia el contador. No cuenta un fallo si la IP ya está bloqueada.  
Hecho cuando el sexto intento en 15 minutos no llega a comprobar la contraseña, y el mensaje es el mismo para usuario inexistente y contraseña mala.

**7.3 Endurecimiento.** Grok 4.6 medium.  
XML-RPC, autores, REST de usuarios, readme, versión, feeds, editor de archivos, contraseñas de aplicación, cabeceras y HSTS, con los valores de fábrica de la tabla. `rest_auth_required` apagado.  
Hecho cuando `/wp-json/wp/v2/users` responde 401 a un anónimo, la ficha de un producto y la tienda siguen cargando, y `readme.html` responde 404.

**7.4 Pantalla Seguridad.** Composer 2.5.  
Las casillas y los dos números, más la tabla de IP, Cloudflare y país. Textos en español, los mismos criterios que el plugin actual.  
Hecho cuando cambiar «Máximo de fallos» a 3 y guardar hace que el cuarto intento bloquee.

## Etapa 8 — Cierre en la copia local

**8.1 Apagar los dos plugins viejos.** Grok 4.6 medium.  
Desactivar White Label CMS y Megadruid Seguridad en la copia local. No borrar sus carpetas ni sus opciones en esta etapa. Confirmar que no quedan dos límites de login ni dos logos.  
Hecho cuando solo Megadruid CMS aparece activo y el escritorio, el login y un `wp login` fallido se comportan como en las etapas 1 a 7.

**8.2 Recorrido de la tienda.** Composer 2.5.  
Inicio, tienda, ficha, carrito y checkout en escritorio y en 390 px. No agregar al carrito, no hacer el pedido, no vaciar el carrito. Confirmar que el menú Lunic, WooCommerce y la REST pública de productos siguen.  
Hecho cuando esas pantallas cargan y un anónimo no puede listar usuarios por REST.

## Orden y esfuerzo

| Etapa | Tareas | Modelo que más pesa |
| --- | --- | --- |
| 0 Esqueleto | 0.1 Composer 2.5, 0.2 Grok 4.6 medium, 0.3 Grok 4.7 high | Grok 4.7 high en los roles |
| 1 Marca | 1.1 Grok 4.6 medium, 1.2 y 1.3 Composer 2.5 | Grok 4.6 medium |
| 2 Login | 2.1 Grok 4.6 medium, 2.2 Composer 2.5, 2.3 Grok 4.7 high | Grok 4.7 high |
| 3 Escritorio | 3.1 y 3.2 Grok 4.6 medium, 3.3 Grok 4.7 high, 3.4 Composer 2.5 | Grok 4.7 high en Elementor |
| 4 Menús | 4.1 y 4.2 Grok 4.7 high, 4.3 Composer 2.5 | Grok 4.7 high |
| 5 Ajustes | 5.1 Grok 4.6 medium, 5.2 y 5.3 Composer 2.5 | Grok 4.6 medium |
| 6 Datos | 6.1 y 6.2 Grok 4.7 high | Grok 4.7 high |
| 7 Seguridad | 7.1 y 7.2 Grok 4.7 high, 7.3 Grok 4.6 medium, 7.4 Composer 2.5 | Grok 4.7 high |
| 8 Cierre | 8.1 Grok 4.6 medium, 8.2 Composer 2.5 | Grok 4.6 medium |

Se implementa en este orden. La etapa 4 no empieza antes de la 0.3. La etapa 7 puede avanzar en paralelo con la 1 solo después de la 0.2, porque no comparte pantallas con la marca salvo la opción única.

## Fuera de este plugin

No entra en Megadruid CMS: envíos, descuentos, menú de categorías, cotizador de Andreani, ni el redireccionamiento del carrito vacío. Eso sigue en `distribuidora-lunic` y en el tema `lunic`.

No se copia el asistente de Video User Manuals ni su marca. El asistente de la etapa 5.3 es el de Megadruid.
