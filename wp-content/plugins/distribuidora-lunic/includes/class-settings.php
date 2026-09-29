<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

final class Settings {

    private static ?Settings $instance = null;

    public const MENU_SLUG = 'distribuidora-lunic';

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_menu', [$this, 'register_envios_menu'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_post_lunic_save_settings', [$this, 'save']);
        add_action('admin_post_lunic_save_envios', [$this, 'save_envios']);
    }

    public function register_menu(): void {
        add_menu_page(
            __('Plantillas del tema', 'distribuidora-lunic'),
            __('Lunic', 'distribuidora-lunic'),
            'manage_woocommerce',
            self::MENU_SLUG,
            [$this, 'render_page'],
            'dashicons-store',
            56
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Plantillas del tema', 'distribuidora-lunic'),
            __('Plantillas', 'distribuidora-lunic'),
            'manage_woocommerce',
            self::MENU_SLUG,
            [$this, 'render_page']
        );
    }

    /**
     * @return array<string, array{label: string, file: string, types: string[]}>
     */
    public static function template_slots(): array {
        return [
            'header' => [
                'label' => __('Encabezado', 'distribuidora-lunic'),
                'file' => 'header.php',
                'types' => ['header'],
            ],
            'footer' => [
                'label' => __('Pie', 'distribuidora-lunic'),
                'file' => 'footer.php',
                'types' => ['footer'],
            ],
            'categories' => [
                'label' => __('Menú de categorías', 'distribuidora-lunic'),
                'file' => 'header.php',
                'types' => ['popup'],
            ],
            'shop' => [
                'label' => __('Tienda', 'distribuidora-lunic'),
                'file' => 'woocommerce/archive-product.php',
                'types' => ['product-archive'],
            ],
            'product' => [
                'label' => __('Ficha de producto', 'distribuidora-lunic'),
                'file' => 'woocommerce/single-product.php',
                'types' => ['product'],
            ],
            'maintenance' => [
                'label' => __('Mantenimiento', 'distribuidora-lunic'),
                'file' => 'maintenance.php',
                'types' => ['page'],
            ],
        ];
    }

    public function enqueue_admin_assets(string $hook): void {
        if ($hook !== 'toplevel_page_' . self::MENU_SLUG) {
            return;
        }
        wp_enqueue_style(
            'distribuidora-lunic-admin',
            DISTRIBUIDORA_LUNIC_URL . 'assets/css/admin.css',
            [],
            DISTRIBUIDORA_LUNIC_VERSION
        );
    }

    public function save(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso.', 'distribuidora-lunic'));
        }
        check_admin_referer('lunic_save_settings');
        update_option('lunic_maintenance', isset($_POST['lunic_maintenance']) ? 1 : 0);
        $posted = isset($_POST['lunic_theme_templates']) && is_array($_POST['lunic_theme_templates'])
            ? wp_unslash($_POST['lunic_theme_templates'])
            : [];
        $clean = [];
        foreach (self::template_slots() as $slot => $meta) {
            $id = absint($posted[$slot] ?? 0);
            if ($id > 0) {
                $post = get_post($id);
                $types = wp_get_object_terms($id, 'elementor_library_type', ['fields' => 'slugs']);
                $type = is_array($types) && isset($types[0]) ? (string) $types[0] : '';
                if (!$post || $post->post_type !== 'elementor_library' || $post->post_status !== 'publish' || !in_array($type, $meta['types'], true)) {
                    $id = 0;
                }
            }
            $clean[$slot] = $id;
        }
        update_option('lunic_theme_templates', $clean);
        Page_Cache::flush();
        wp_safe_redirect(admin_url('admin.php?page=' . self::MENU_SLUG . '&updated=1'));
        exit;
    }

    public function render_page(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso para ver esta página.', 'distribuidora-lunic'));
        }

        $saved = get_option('lunic_theme_templates');
        if (!is_array($saved)) {
            $saved = [];
        }
        $templates = $this->elementor_templates();
        ?>
        <div class="wrap distribuidora-lunic-settings">
            <h1><?php esc_html_e('Plantillas del tema', 'distribuidora-lunic'); ?></h1>
            <?php if (!empty($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Plantillas guardadas.', 'distribuidora-lunic'); ?></p></div>
            <?php endif; ?>
            <p class="description">
                <?php esc_html_e('Cada sección usa la plantilla del tema. Si elegís una plantilla de Elementor, esa sección pasa a mostrarla.', 'distribuidora-lunic'); ?>
            </p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('lunic_save_settings'); ?>
                <input type="hidden" name="action" value="lunic_save_settings" />
                <table class="widefat striped lunic-template-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Sección', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Plantilla del tema', 'distribuidora-lunic'); ?></th>
                            <th><?php esc_html_e('Plantilla de Elementor', 'distribuidora-lunic'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (self::template_slots() as $slot => $meta) : ?>
                            <?php $selected = absint($saved[$slot] ?? 0); ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($meta['label']); ?></th>
                                <td><code><?php echo esc_html($meta['file']); ?></code></td>
                                <td>
                                    <select name="lunic_theme_templates[<?php echo esc_attr($slot); ?>]">
                                        <option value="0"><?php esc_html_e('Usar la plantilla del tema', 'distribuidora-lunic'); ?></option>
                                        <?php foreach ($templates as $template) : ?>
                                            <?php if (!in_array($template['type'], $meta['types'], true)) { continue; } ?>
                                            <option value="<?php echo esc_attr((string) $template['id']); ?>" <?php selected($selected, $template['id']); ?>>
                                                <?php echo esc_html($template['title'] . ' (' . $template['type_label'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($selected > 0) : ?>
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $selected . '&action=elementor')); ?>">
                                            <?php esc_html_e('Editar', 'distribuidora-lunic'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p>
                    <label>
                        <input type="checkbox" name="lunic_maintenance" value="1" <?php checked((bool) get_option('lunic_maintenance')); ?> />
                        <?php esc_html_e('Mostrar la pantalla de mantenimiento a quien no administra el sitio', 'distribuidora-lunic'); ?>
                    </label>
                </p>
                <?php submit_button(__('Guardar plantillas', 'distribuidora-lunic')); ?>
            </form>
            <p class="description">
                <?php
                echo wp_kses(
                    sprintf(
                        /* translators: 1: envíos admin URL, 2: discounts admin URL */
                        __('El texto del acordeón «Envíos» se cambia en <a href="%1$s">Texto de envíos</a>. Las reglas de descuento, en <a href="%2$s">Descuentos por Categoría</a>.', 'distribuidora-lunic'),
                        esc_url(admin_url('admin.php?page=lunic-envios')),
                        esc_url(admin_url('admin.php?page=wcd-category-discounts'))
                    ),
                    ['a' => ['href' => []]]
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * @return array<int, array{id: int, title: string, type: string, type_label: string}>
     */
    private function elementor_templates(): array {
        $posts = get_posts([
            'post_type' => 'elementor_library',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        $labels = [
            'header' => __('Encabezado', 'distribuidora-lunic'),
            'footer' => __('Pie', 'distribuidora-lunic'),
            'page' => __('Página', 'distribuidora-lunic'),
            'popup' => __('Ventana', 'distribuidora-lunic'),
            'product' => __('Ficha', 'distribuidora-lunic'),
            'product-archive' => __('Tienda', 'distribuidora-lunic'),
        ];
        $options = [];
        foreach ($posts as $post) {
            $types = wp_get_object_terms($post->ID, 'elementor_library_type', ['fields' => 'slugs']);
            $type = is_array($types) && isset($types[0]) ? (string) $types[0] : '';
            if ($type === '') {
                continue;
            }
            $options[] = [
                'id' => (int) $post->ID,
                'title' => $post->post_title,
                'type' => $type,
                'type_label' => $labels[$type] ?? $type,
            ];
        }
        return $options;
    }

    public function register_envios_menu(): void {
        add_submenu_page(
            self::MENU_SLUG,
            __('Texto de envíos', 'distribuidora-lunic'),
            __('Texto de envíos', 'distribuidora-lunic'),
            'manage_woocommerce',
            'lunic-envios',
            [$this, 'render_envios']
        );
    }

    public function save_envios(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso.', 'distribuidora-lunic'));
        }
        check_admin_referer('lunic_save_envios');
        $option = get_option('configuraciones');
        if (!is_array($option)) {
            $option = [];
        }
        $html = isset($_POST['lunic_envios_html']) ? wp_unslash($_POST['lunic_envios_html']) : '';
        $option['envios'] = wp_kses_post($html);
        update_option('configuraciones', $option);
        Page_Cache::flush();
        wp_safe_redirect(admin_url('admin.php?page=lunic-envios&updated=1'));
        exit;
    }

    public function render_envios(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso para ver esta página.', 'distribuidora-lunic'));
        }
        $option = get_option('configuraciones');
        $html = is_array($option) && isset($option['envios']) ? (string) $option['envios'] : '';
        ?>
        <div class="wrap distribuidora-lunic-settings">
            <h1><?php esc_html_e('Texto de envíos', 'distribuidora-lunic'); ?></h1>
            <?php if (!empty($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Texto guardado.', 'distribuidora-lunic'); ?></p></div>
            <?php endif; ?>
            <p class="description">
                <?php esc_html_e('Este HTML es el contenido del acordeón «Envíos» en el inicio, el carrito y el checkout. Los importes de cada zona se editan aparte, en WooCommerce → Ajustes → Envío.', 'distribuidora-lunic'); ?>
            </p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('lunic_save_envios'); ?>
                <input type="hidden" name="action" value="lunic_save_envios" />
                <?php
                wp_editor($html, 'lunic_envios_html', [
                    'textarea_name' => 'lunic_envios_html',
                    'textarea_rows' => 18,
                    'media_buttons' => true,
                ]);
                ?>
                <?php submit_button(__('Guardar texto', 'distribuidora-lunic')); ?>
            </form>
        </div>
        <?php
    }
}
