# Paquete Megadruid CMS 0.3.1

Copia autocontenida **congelada en 0.3.1** (cierre etapas 0–8). El código que se mantiene en Lunic es `wp-content/plugins/megadruid-cms/` (**0.3.19**). No actualizar esta carpeta a ciegas: es archivo, no el working copy.

## Contenido

| Ruta | Qué es |
| --- | --- |
| `megadruid-cms/` | Plugin listo para copiar a `wp-content/plugins/` |
| `documentacion/README-plugin.md` | Manual del plugin |
| `documentacion/implementacion-plugin-cms.md` | Plan de etapas 0–8 |
| `documentacion/acta-cierre-etapa-8.md` | Cierre en la copia local Lunic (8.1 y 8.2) |

## Instalación

1. Copiar la carpeta `megadruid-cms` a `wp-content/plugins/`.
2. Activar **Megadruid CMS**.
3. Ir a **Ajustes → Megadruid CMS**.
4. PHP 8.1+ y WordPress 6.0+.

Si el sitio ya tenía White Label CMS o Megadruid Seguridad, desactivarlos **después** de activar este plugin. Las opciones `wlcms_options` y `wbs_settings` no se borran; la primera visita de un administrador copia lo mapeable a `mdcms_settings`.

## Qué no entra

Envíos, descuentos, menú de categorías, cotizador Andreani y el carrito vacío siguen en el plugin `distribuidora-lunic` y en el tema `lunic`.
