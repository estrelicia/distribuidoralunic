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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_menu(): void {
        add_menu_page(
            __('Distribuidora Lunic', 'distribuidora-lunic'),
            __('Lunic', 'distribuidora-lunic'),
            'manage_woocommerce',
            self::MENU_SLUG,
            [$this, 'render_page'],
            'dashicons-store',
            56
        );
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

    public function render_page(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso para ver esta página.', 'distribuidora-lunic'));
        }

        $modules = Plugin::instance()->modules();
        ?>
        <div class="wrap distribuidora-lunic-settings">
            <h1><?php esc_html_e('Distribuidora Lunic', 'distribuidora-lunic'); ?></h1>
            <p class="description">
                <?php esc_html_e(
                    'Ajustes de la tienda. Los módulos se irán habilitando en las próximas etapas del plan de migración.',
                    'distribuidora-lunic'
                ); ?>
            </p>
            <div class="lunic-settings-card">
                <h2><?php esc_html_e('Estado', 'distribuidora-lunic'); ?></h2>
                <p>
                    <?php
                    printf(
                        /* translators: %s: plugin version */
                        esc_html__('Versión del plugin: %s', 'distribuidora-lunic'),
                        esc_html(DISTRIBUIDORA_LUNIC_VERSION)
                    );
                    ?>
                </p>
                <p>
                    <?php
                    printf(
                        /* translators: %d: number of loaded modules */
                        esc_html__('Módulos cargados: %d', 'distribuidora-lunic'),
                        count($modules)
                    );
                    ?>
                </p>
                <?php if (!empty($modules)) : ?>
                    <ul class="lunic-module-list">
                        <?php foreach (array_keys($modules) as $slug) : ?>
                            <li><code><?php echo esc_html($slug); ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
