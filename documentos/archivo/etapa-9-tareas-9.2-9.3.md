# Etapa 9 — tareas 9.2 y 9.3

**Fecha:** 26/09/2026.

## 9.2 — Conteos de categoría en transient

- Nuevo `Category_Tree` en `includes/modules/catalog-filter/class-category-tree.php`.
- Transient `lunic_cat_filter_v1` (15 min de respaldo); se invalida al guardar o borrar productos y al crear, editar o borrar `product_cat`.
- El shortcode `[lunic_filter]` ya no llama a `get_terms()` en cada request si el transient está caliente.
- Redirección `wpf_filter_cat_list_0` → `lunic_cat` enganchada en `template_redirect` (estaba definida pero sin hook).
- Tras el corte (etapa 12), tablas del índice WBW eliminadas en la pasada 9.3 (`wp_wpf_*`, `wp_woof_*`).
- Comprobación: `/tienda/?lunic_cat=135` sigue mostrando **Blends de té (52)**.

Plugin `distribuidora-lunic` **0.1.5**.

## 9.3 — Limpieza de base

**Backup previo:** `C:\laragon\backups\distribuidoralunic.com.ar\2026-09-26_1958\database-pre-9.3.sql` (~73 MB).

| Acción | Resultado |
| --- | --- |
| Revisiones (`post_type=revision`) | 2.951 eliminadas |
| Tablas WBW / WOOF / Ivory / YITH / WPForms tareas | 10 tablas eliminadas (incl. `wp_is_inverted_index`) |
| Opciones autoload de plugins ausentes | 124 filas borradas |
| Autoload total | 207.067 → **92.953** bytes (~55 % menos) |
| `wp_mainwp_*` | Sin cambios (MainWP Child activo) |
| Pedidos WooCommerce | Sin cambios |

Tablas Wordfence (`wp_wf*`): no existían en esta base.

Script usado una vez: `documentos/_run-cleanup-9.3.php` (más `documentos/_drop-wbw.php` para el resto de tablas `wp_wpf_*` / `wp_woof_*` cuyo nombre no entraba en el primer `fnmatch`).

**Comprobación:** inicio y tienda responden 200; filtro y conteos correctos.
