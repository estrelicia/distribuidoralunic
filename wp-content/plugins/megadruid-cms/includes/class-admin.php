<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

final class Admin {

    private string $hook_suffix = '';

    public function register(): void {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('admin_post_' . Settings::SAVE_ACTION, [$this, 'save']);
        add_action('admin_post_nopriv_' . Settings::SAVE_ACTION, [$this, 'save']);
        add_filter('plugin_action_links_' . MDCMS_BASENAME, [$this, 'action_links']);
    }

    /**
     * @param string[] $links
     * @return string[]
     */
    public function action_links(array $links): array {
        if (!current_user_can('manage_options')) {
            return $links;
        }
        $url = admin_url('options-general.php?page=' . Settings::PAGE_SLUG);
        array_unshift(
            $links,
            '<a href="' . esc_url($url) . '">' . esc_html__('Ajustes', 'megadruid-cms') . '</a>'
        );

        return $links;
    }

    public function menu(): void {
        $hook = add_options_page(
            __('Megadruid CMS', 'megadruid-cms'),
            __('Megadruid CMS', 'megadruid-cms'),
            'manage_options',
            Settings::PAGE_SLUG,
            [$this, 'render_page']
        );
        $this->hook_suffix = is_string($hook) ? $hook : '';
    }

    public function assets(string $hook): void {
        if ($this->hook_suffix === '' || $hook !== $this->hook_suffix) {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style(
            'megadruid-cms-admin',
            MDCMS_URL . 'assets/css/admin.css',
            ['wp-color-picker'],
            MDCMS_VERSION
        );
        wp_enqueue_media();
        wp_enqueue_script(
            'megadruid-cms-admin',
            MDCMS_URL . 'assets/js/admin.js',
            ['wp-color-picker'],
            MDCMS_VERSION,
            true
        );
        wp_localize_script(
            'megadruid-cms-admin',
            'mdcmsAdmin',
            [
                'ajax' => admin_url('admin-ajax.php'),
                'searchNonce' => wp_create_nonce('mdcms_search_pages'),
            ]
        );
    }

    public function render_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $tabs = $this->tabs();
        $active = isset($_GET['tab']) ? sanitize_key((string) wp_unslash($_GET['tab'])) : 'branding';
        if (!isset($tabs[$active])) {
            $active = 'branding';
        }

        $base_url = admin_url('options-general.php?page=' . Settings::PAGE_SLUG);
        ?>
        <div class="wrap mdcms-wrap">
            <h1><?php esc_html_e('Megadruid CMS', 'megadruid-cms'); ?></h1>
            <p class="description">
                <?php esc_html_e('Marca del escritorio, login y seguridad base. Las opciones de cada pestaña se implementan por etapas.', 'megadruid-cms'); ?>
            </p>
            <?php $this->render_notice(); ?>
            <nav class="nav-tab-wrapper mdcms-tabs" aria-label="<?php esc_attr_e('Secciones', 'megadruid-cms'); ?>">
                <?php foreach ($tabs as $slug => $label) : ?>
                    <a
                        href="<?php echo esc_url(add_query_arg('tab', $slug, $base_url)); ?>"
                        class="nav-tab<?php echo $slug === $active ? ' nav-tab-active' : ''; ?>"
                    ><?php echo esc_html($label); ?></a>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mdcms-form">
                <?php wp_nonce_field(Settings::SAVE_ACTION, 'mdcms_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr(Settings::SAVE_ACTION); ?>" />
                <input type="hidden" name="tab" value="<?php echo esc_attr($active); ?>" />
                <div class="mdcms-panel">
                    <?php $this->render_tab_panel($active); ?>
                </div>
                <?php if (in_array($active, ['branding', 'login', 'dashboard', 'menus', 'general'], true)) : ?>
                    <?php if ($active === 'login') : ?>
                        <?php submit_button(__('Vista previa del login', 'megadruid-cms'), 'secondary', 'mdcms_preview', false); ?>
                    <?php endif; ?>
                    <?php submit_button(__('Guardar cambios', 'megadruid-cms')); ?>
                <?php endif; ?>
            </form>
        </div>
        <?php
    }

    public function save(): void {
        if (!current_user_can('manage_options')) {
            wp_die(
                esc_html__('No tenés permiso para guardar estos ajustes.', 'megadruid-cms'),
                esc_html__('Permiso denegado', 'megadruid-cms'),
                ['response' => 403]
            );
        }
        check_admin_referer(Settings::SAVE_ACTION, 'mdcms_nonce');

        $posted = isset($_POST['mdcms']) && is_array($_POST['mdcms'])
            ? wp_unslash($_POST['mdcms'])
            : [];
        $tab_posted = isset($_POST['tab']) ? sanitize_key((string) wp_unslash($_POST['tab'])) : '';
        if ($tab_posted === 'menus') {
            if (!array_key_exists('restricted_roles', $posted)) {
                $posted['restricted_roles'] = [];
            }
            if (!array_key_exists('hide_frontend_admin_bar_roles', $posted)) {
                $posted['hide_frontend_admin_bar_roles'] = [];
            }
            $posted = Menus::fill_missing($posted);
        }
        if ($tab_posted === 'dashboard') {
            $posted = Dashboard::fill_missing($posted);
        }
        if ($tab_posted === 'branding') {
            if (!array_key_exists('hide_wp_logo', $posted)) {
                $posted['hide_wp_logo'] = 0;
            }
            if (!array_key_exists('hide_wp_version', $posted)) {
                $posted['hide_wp_version'] = 0;
            }
        }
        if ($tab_posted === 'login') {
            if (!array_key_exists('login_background_fullscreen', $posted)) {
                $posted['login_background_fullscreen'] = 0;
            }
            if (!array_key_exists('login_hide_register', $posted)) {
                $posted['login_hide_register'] = 0;
            }
            if (!array_key_exists('login_hide_lost_password', $posted)) {
                $posted['login_hide_lost_password'] = 0;
            }
            if (!array_key_exists('login_hide_back_to', $posted)) {
                $posted['login_hide_back_to'] = 0;
            }
        }
        if ($tab_posted === 'general') {
            if (!array_key_exists('hide_help_box', $posted)) {
                $posted['hide_help_box'] = 0;
            }
            if (!array_key_exists('hide_screen_options', $posted)) {
                $posted['hide_screen_options'] = 0;
            }
            if (!array_key_exists('hide_nag_messages', $posted)) {
                $posted['hide_nag_messages'] = 0;
            }
            $posted = Metaboxes::fill_missing($posted);
        }
        if ($tab_posted === 'login' && isset($_POST['mdcms_preview'])) {
            Login::store_preview($posted);
            wp_safe_redirect(add_query_arg('mdcms_preview', '1', wp_login_url()));
            exit;
        }
        Settings::update($posted);

        $tabs = $this->tabs();
        $tab = isset($_POST['tab']) ? sanitize_key((string) wp_unslash($_POST['tab'])) : 'branding';
        if (!isset($tabs[$tab])) {
            $tab = 'branding';
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => Settings::PAGE_SLUG,
                    'tab' => $tab,
                    'updated' => '1',
                ],
                admin_url('options-general.php')
            )
        );
        exit;
    }

    /**
     * @return array<string, string>
     */
    private function tabs(): array {
        return [
            'branding'  => __('Marca', 'megadruid-cms'),
            'login'     => __('Login', 'megadruid-cms'),
            'dashboard' => __('Escritorio', 'megadruid-cms'),
            'menus'     => __('Menús', 'megadruid-cms'),
            'general'   => __('Ajustes', 'megadruid-cms'),
            'security'  => __('Seguridad', 'megadruid-cms'),
        ];
    }

    private function render_notice(): void {
        if (isset($_GET['mdcms_wizard_done']) && (string) $_GET['mdcms_wizard_done'] === '1') {
            echo '<div class="notice notice-success is-dismissible"><p>';
            esc_html_e('Asistente finalizado. Los ajustes ya están en las pestañas del plugin.', 'megadruid-cms');
            echo '</p></div>';
        }
        if (!isset($_GET['updated']) || (string) $_GET['updated'] !== '1') {
            return;
        }
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('Ajustes guardados.', 'megadruid-cms');
        echo '</p></div>';
    }

    private function render_tab_panel(string $tab): void {
        if ($tab === 'branding') {
            $this->render_branding_tab();
            return;
        }
        if ($tab === 'login') {
            $this->render_login_tab();
            return;
        }
        if ($tab === 'general') {
            $this->render_general_tab();
            return;
        }
        if ($tab === 'menus') {
            $this->render_menus_tab();
            return;
        }
        if ($tab === 'dashboard') {
            Dashboard::render_settings();
            return;
        }

        $messages = [
            'security'  => __('Capa de seguridad detrás de Cloudflare.', 'megadruid-cms'),
        ];
        $text = $messages[$tab] ?? '';
        ?>
        <p class="mdcms-placeholder">
            <?php
            echo esc_html(
                sprintf(
                    /* translators: %s: short description of the tab */
                    __('Pestaña en preparación: %s', 'megadruid-cms'),
                    $text
                )
            );
            ?>
        </p>
        <?php
    }

    private function render_branding_tab(): void {
        $logo = (string) Settings::get('admin_bar_logo', '');
        $width = (int) Settings::get('admin_bar_logo_width', 20);
        $alt = (string) Settings::get('admin_bar_alt_text', '');
        $url = (string) Settings::get('admin_bar_url', '');
        $howdy = (string) Settings::get('admin_bar_howdy_text', '');
        $hide = (bool) Settings::get('hide_wp_logo', false);
        $side_menu = (string) Settings::get('side_menu_image', '');
        $side_collapsed = (string) Settings::get('collapsed_side_menu_image', '');
        $side_link = (string) Settings::get('side_menu_link_url', '');
        $side_alt = (string) Settings::get('side_menu_alt_text', '');
        $footer_image = (string) Settings::get('footer_image', '');
        $footer_url = (string) Settings::get('footer_url', '');
        $footer_text = (string) Settings::get('footer_text', '');
        $footer_html = (string) Settings::get('footer_html', '');
        $hide_version = (bool) Settings::get('hide_wp_version', false);
        $page_title = (string) Settings::get('custom_page_title', '');
        $gutenberg_exit = (string) Settings::get('gutenberg_exit_icon', 'wordpress');
        $gutenberg_custom = (string) Settings::get('gutenberg_exit_custom_icon', '');
        ?>
        <h2 class="mdcms-section-title"><?php esc_html_e('Barra de admin', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Logo de la barra', 'megadruid-cms'); ?></th>
                <td>
                    <div class="mdcms-media" data-mdcms-media>
                        <img
                            data-mdcms-media-preview
                            class="mdcms-media__preview"
                            src="<?php echo esc_url($logo); ?>"
                            alt=""
                            <?php echo $logo === '' ? 'hidden' : ''; ?>
                        />
                        <input
                            type="url"
                            class="regular-text"
                            id="mdcms_admin_bar_logo"
                            name="mdcms[admin_bar_logo]"
                            value="<?php echo esc_url($logo); ?>"
                            data-mdcms-media-url
                        />
                        <p class="mdcms-media__actions">
                            <button
                                type="button"
                                class="button"
                                data-mdcms-media-choose
                                data-title="<?php esc_attr_e('Elegir logo de la barra', 'megadruid-cms'); ?>"
                                data-button="<?php esc_attr_e('Usar esta imagen', 'megadruid-cms'); ?>"
                            ><?php esc_html_e('Elegir imagen', 'megadruid-cms'); ?></button>
                            <button type="button" class="button-link" data-mdcms-media-clear>
                                <?php esc_html_e('Quitar', 'megadruid-cms'); ?>
                            </button>
                        </p>
                        <p class="description">
                            <?php esc_html_e('Reemplaza el logo de WordPress. Altura máxima 20 px.', 'megadruid-cms'); ?>
                        </p>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_admin_bar_logo_width"><?php esc_html_e('Ancho del logo', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        class="small-text"
                        id="mdcms_admin_bar_logo_width"
                        name="mdcms[admin_bar_logo_width]"
                        value="<?php echo esc_attr((string) $width); ?>"
                        min="8"
                        max="240"
                        step="1"
                    />
                    <span><?php esc_html_e('px', 'megadruid-cms'); ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_admin_bar_alt_text"><?php esc_html_e('Texto alternativo', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="mdcms_admin_bar_alt_text"
                        name="mdcms[admin_bar_alt_text]"
                        value="<?php echo esc_attr($alt); ?>"
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_admin_bar_url"><?php esc_html_e('URL del logo', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="url"
                        class="regular-text"
                        id="mdcms_admin_bar_url"
                        name="mdcms[admin_bar_url]"
                        value="<?php echo esc_url($url); ?>"
                    />
                    <p class="description">
                        <?php esc_html_e('El logo abre esta dirección. Si queda vacío, no es un enlace.', 'megadruid-cms'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_admin_bar_howdy_text"><?php esc_html_e('Texto de saludo', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="mdcms_admin_bar_howdy_text"
                        name="mdcms[admin_bar_howdy_text]"
                        value="<?php echo esc_attr($howdy); ?>"
                    />
                    <p class="description">
                        <?php esc_html_e('Reemplaza «Hola,». Un espacio quita la palabra y deja el nombre.', 'megadruid-cms'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Menú de WordPress', 'megadruid-cms'); ?></th>
                <td>
                    <label for="mdcms_hide_wp_logo">
                        <input
                            type="checkbox"
                            id="mdcms_hide_wp_logo"
                            name="mdcms[hide_wp_logo]"
                            value="1"
                            <?php checked($hide); ?>
                        />
                        <?php esc_html_e('Ocultar el ícono y el menú de WordPress en la barra', 'megadruid-cms'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <h2 class="mdcms-section-title"><?php esc_html_e('Menú lateral', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <?php
            $this->render_media_row(
                'mdcms_side_menu_image',
                'mdcms[side_menu_image]',
                __('Imagen con menú abierto', 'megadruid-cms'),
                $side_menu,
                __('Elegir imagen del menú', 'megadruid-cms'),
                __('Aparece arriba del menú. Ancho máximo recomendado 160 px.', 'megadruid-cms')
            );
            $this->render_media_row(
                'mdcms_collapsed_side_menu_image',
                'mdcms[collapsed_side_menu_image]',
                __('Imagen con menú colapsado', 'megadruid-cms'),
                $side_collapsed,
                __('Elegir imagen colapsada', 'megadruid-cms'),
                __('Se usa al plegar el menú. Si queda vacío, se reutiliza la imagen grande.', 'megadruid-cms')
            );
            ?>
            <tr>
                <th scope="row">
                    <label for="mdcms_side_menu_link_url"><?php esc_html_e('Enlace del logo', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="url"
                        class="regular-text"
                        id="mdcms_side_menu_link_url"
                        name="mdcms[side_menu_link_url]"
                        value="<?php echo esc_url($side_link); ?>"
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_side_menu_alt_text"><?php esc_html_e('Texto alternativo', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="mdcms_side_menu_alt_text"
                        name="mdcms[side_menu_alt_text]"
                        value="<?php echo esc_attr($side_alt); ?>"
                    />
                </td>
            </tr>
        </table>

        <h2 class="mdcms-section-title"><?php esc_html_e('Pie y título del navegador', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <?php
            $this->render_media_row(
                'mdcms_footer_image',
                'mdcms[footer_image]',
                __('Imagen del pie', 'megadruid-cms'),
                $footer_image,
                __('Elegir imagen del pie', 'megadruid-cms'),
                __('Reemplaza el texto de agradecimiento de WordPress. Altura máxima 50 px.', 'megadruid-cms')
            );
            ?>
            <tr>
                <th scope="row">
                    <label for="mdcms_footer_text"><?php esc_html_e('Texto del pie', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="mdcms_footer_text"
                        name="mdcms[footer_text]"
                        value="<?php echo esc_attr($footer_text); ?>"
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_footer_url"><?php esc_html_e('URL del pie', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="url"
                        class="regular-text"
                        id="mdcms_footer_url"
                        name="mdcms[footer_url]"
                        value="<?php echo esc_url($footer_url); ?>"
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_footer_html"><?php esc_html_e('HTML del pie', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <textarea
                        class="large-text code"
                        rows="4"
                        id="mdcms_footer_html"
                        name="mdcms[footer_html]"
                    ><?php echo esc_textarea($footer_html); ?></textarea>
                    <p class="description">
                        <?php esc_html_e('Si completás este campo, reemplaza imagen y texto del pie.', 'megadruid-cms'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_custom_page_title"><?php esc_html_e('Título en la pestaña', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="mdcms_custom_page_title"
                        name="mdcms[custom_page_title]"
                        value="<?php echo esc_attr($page_title); ?>"
                    />
                    <p class="description">
                        <?php esc_html_e('Reemplaza la palabra «WordPress» en el título del navegador del admin.', 'megadruid-cms'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Versión de WordPress', 'megadruid-cms'); ?></th>
                <td>
                    <label for="mdcms_hide_wp_version">
                        <input
                            type="checkbox"
                            id="mdcms_hide_wp_version"
                            name="mdcms[hide_wp_version]"
                            value="1"
                            <?php checked($hide_version); ?>
                        />
                        <?php esc_html_e('Ocultar la versión en el pie derecho del escritorio', 'megadruid-cms'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <h2 class="mdcms-section-title"><?php esc_html_e('Editor de bloques', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mdcms_gutenberg_exit_icon"><?php esc_html_e('Ícono de salida', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <select id="mdcms_gutenberg_exit_icon" name="mdcms[gutenberg_exit_icon]">
                        <option value="wordpress" <?php selected($gutenberg_exit, 'wordpress'); ?>>
                            <?php esc_html_e('Ícono de WordPress', 'megadruid-cms'); ?>
                        </option>
                        <option value="exit" <?php selected($gutenberg_exit, 'exit'); ?>>
                            <?php esc_html_e('Ícono de salida', 'megadruid-cms'); ?>
                        </option>
                        <option value="admin_bar" <?php selected($gutenberg_exit, 'admin_bar'); ?>>
                            <?php esc_html_e('Logo de la barra de admin', 'megadruid-cms'); ?>
                        </option>
                        <option value="custom" <?php selected($gutenberg_exit, 'custom'); ?>>
                            <?php esc_html_e('Imagen personalizada', 'megadruid-cms'); ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr data-mdcms-gutenberg-custom <?php echo $gutenberg_exit !== 'custom' ? 'hidden' : ''; ?>>
                <th scope="row"><?php esc_html_e('Imagen personalizada', 'megadruid-cms'); ?></th>
                <td>
                    <div class="mdcms-media" data-mdcms-media>
                        <img
                            data-mdcms-media-preview
                            class="mdcms-media__preview"
                            src="<?php echo esc_url($gutenberg_custom); ?>"
                            alt=""
                            <?php echo $gutenberg_custom === '' ? 'hidden' : ''; ?>
                        />
                        <input
                            type="url"
                            class="regular-text"
                            id="mdcms_gutenberg_exit_custom_icon"
                            name="mdcms[gutenberg_exit_custom_icon]"
                            value="<?php echo esc_url($gutenberg_custom); ?>"
                            data-mdcms-media-url
                        />
                        <p class="mdcms-media__actions">
                            <button
                                type="button"
                                class="button"
                                data-mdcms-media-choose
                                data-title="<?php esc_attr_e('Elegir ícono de salida', 'megadruid-cms'); ?>"
                                data-button="<?php esc_attr_e('Usar esta imagen', 'megadruid-cms'); ?>"
                            ><?php esc_html_e('Elegir imagen', 'megadruid-cms'); ?></button>
                            <button type="button" class="button-link" data-mdcms-media-clear>
                                <?php esc_html_e('Quitar', 'megadruid-cms'); ?>
                            </button>
                        </p>
                        <p class="description">
                            <?php esc_html_e('Tamaño máximo recomendado 50 × 50 px.', 'megadruid-cms'); ?>
                        </p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    private function render_login_tab(): void {
        $logo = (string) Settings::get('login_logo', '');
        $retina = (string) Settings::get('retina_login_logo', '');
        $width = (int) Settings::get('login_logo_width', 0);
        $height = (int) Settings::get('login_logo_height', 0);
        $margin = (int) Settings::get('login_logo_bottom_margin', 0);
        $color = (string) Settings::get('login_background_color', '');
        $image = (string) Settings::get('login_background_image', '');
        $fullscreen = (bool) Settings::get('login_background_fullscreen', false);
        $position = (string) Settings::get('login_background_position', 'center center');
        $repeat = (string) Settings::get('login_background_repeat', 'no-repeat');
        $label_color = (string) Settings::get('login_form_label_color', '');
        $form_bg = (string) Settings::get('login_form_background_color', '');
        $button_color = (string) Settings::get('login_form_button_color', '');
        $button_text = (string) Settings::get('login_form_button_text_color', '');
        $button_hover = (string) Settings::get('login_form_button_hover_color', '');
        $button_text_hover = (string) Settings::get('login_form_button_text_hover_color', '');
        $link_color = (string) Settings::get('login_link_color', '');
        $link_hover = (string) Settings::get('login_link_hover_color', '');
        $privacy_color = (string) Settings::get('login_privacy_link_color', '');
        $privacy_hover = (string) Settings::get('login_privacy_link_hover_color', '');
        $hide_register = (bool) Settings::get('login_hide_register', false);
        $hide_lost = (bool) Settings::get('login_hide_lost_password', false);
        $hide_back = (bool) Settings::get('login_hide_back_to', false);
        $positions = [
            'center center' => __('Centro', 'megadruid-cms'),
            'center top' => __('Arriba al centro', 'megadruid-cms'),
            'center bottom' => __('Abajo al centro', 'megadruid-cms'),
            'left top' => __('Arriba a la izquierda', 'megadruid-cms'),
            'left center' => __('Centro a la izquierda', 'megadruid-cms'),
            'left bottom' => __('Abajo a la izquierda', 'megadruid-cms'),
            'right top' => __('Arriba a la derecha', 'megadruid-cms'),
            'right center' => __('Centro a la derecha', 'megadruid-cms'),
            'right bottom' => __('Abajo a la derecha', 'megadruid-cms'),
        ];
        $repeats = [
            'no-repeat' => __('Sin repetir', 'megadruid-cms'),
            'repeat' => __('Repetir', 'megadruid-cms'),
            'repeat-y' => __('Repetir en vertical', 'megadruid-cms'),
        ];
        ?>
        <p class="mdcms-placeholder">
            <?php esc_html_e('Este CSS se imprime solo en wp-login.php. La tienda no lo carga. La vista previa usa una copia temporal y no guarda hasta que confirmás con Guardar cambios.', 'megadruid-cms'); ?>
        </p>
        <h2 class="mdcms-section-title"><?php esc_html_e('Logo', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <?php
            $this->render_media_row(
                'mdcms_login_logo',
                'mdcms[login_logo]',
                __('Logo de login', 'megadruid-cms'),
                $logo,
                __('Elegir logo de login', 'megadruid-cms'),
                __('Reemplaza el logo de WordPress. Ancho máximo 320 px.', 'megadruid-cms')
            );
            $this->render_media_row(
                'mdcms_retina_login_logo',
                'mdcms[retina_login_logo]',
                __('Logo retina', 'megadruid-cms'),
                $retina,
                __('Elegir logo retina', 'megadruid-cms'),
                __('Versión al doble de resolución para pantallas densas.', 'megadruid-cms')
            );
            ?>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_logo_width"><?php esc_html_e('Ancho', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        class="small-text"
                        id="mdcms_login_logo_width"
                        name="mdcms[login_logo_width]"
                        value="<?php echo esc_attr((string) $width); ?>"
                        min="0"
                        max="320"
                        step="1"
                    />
                    <span><?php esc_html_e('px (0 = automático, máximo 320)', 'megadruid-cms'); ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_logo_height"><?php esc_html_e('Alto', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        class="small-text"
                        id="mdcms_login_logo_height"
                        name="mdcms[login_logo_height]"
                        value="<?php echo esc_attr((string) $height); ?>"
                        min="0"
                        max="400"
                        step="1"
                    />
                    <span><?php esc_html_e('px', 'megadruid-cms'); ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_logo_bottom_margin"><?php esc_html_e('Margen inferior', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        class="small-text"
                        id="mdcms_login_logo_bottom_margin"
                        name="mdcms[login_logo_bottom_margin]"
                        value="<?php echo esc_attr((string) $margin); ?>"
                        min="0"
                        max="200"
                        step="1"
                    />
                    <span><?php esc_html_e('px', 'megadruid-cms'); ?></span>
                </td>
            </tr>
        </table>
        <h2 class="mdcms-section-title"><?php esc_html_e('Fondo', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mdcms_login_background_color"><?php esc_html_e('Color de fondo', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="mdcms-color"
                        id="mdcms_login_background_color"
                        name="mdcms[login_background_color]"
                        value="<?php echo esc_attr($color); ?>"
                    />
                </td>
            </tr>
            <?php
            $this->render_media_row(
                'mdcms_login_background_image',
                'mdcms[login_background_image]',
                __('Imagen de fondo', 'megadruid-cms'),
                $image,
                __('Elegir fondo de login', 'megadruid-cms'),
                __('Se aplica solo a la pantalla de inicio de sesión.', 'megadruid-cms')
            );
            ?>
            <tr>
                <th scope="row"><?php esc_html_e('Pantalla completa', 'megadruid-cms'); ?></th>
                <td>
                    <label for="mdcms_login_background_fullscreen">
                        <input
                            type="checkbox"
                            id="mdcms_login_background_fullscreen"
                            name="mdcms[login_background_fullscreen]"
                            value="1"
                            <?php checked($fullscreen); ?>
                        />
                        <?php esc_html_e('Estirar la imagen a todo el fondo', 'megadruid-cms'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_background_position"><?php esc_html_e('Posición', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <select id="mdcms_login_background_position" name="mdcms[login_background_position]">
                        <?php foreach ($positions as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($position, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_background_repeat"><?php esc_html_e('Repetición', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <select id="mdcms_login_background_repeat" name="mdcms[login_background_repeat]">
                        <?php foreach ($repeats as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($repeat, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <h2 class="mdcms-section-title"><?php esc_html_e('Formulario', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <?php
            $this->render_color_row(
                'mdcms_login_form_label_color',
                'mdcms[login_form_label_color]',
                __('Color de etiquetas', 'megadruid-cms'),
                $label_color
            );
            $this->render_color_row(
                'mdcms_login_form_background_color',
                'mdcms[login_form_background_color]',
                __('Fondo del formulario', 'megadruid-cms'),
                $form_bg
            );
            $this->render_color_row(
                'mdcms_login_form_button_color',
                'mdcms[login_form_button_color]',
                __('Color del botón', 'megadruid-cms'),
                $button_color
            );
            $this->render_color_row(
                'mdcms_login_form_button_text_color',
                'mdcms[login_form_button_text_color]',
                __('Texto del botón', 'megadruid-cms'),
                $button_text
            );
            $this->render_color_row(
                'mdcms_login_form_button_hover_color',
                'mdcms[login_form_button_hover_color]',
                __('Botón al pasar el mouse', 'megadruid-cms'),
                $button_hover
            );
            $this->render_color_row(
                'mdcms_login_form_button_text_hover_color',
                'mdcms[login_form_button_text_hover_color]',
                __('Texto del botón al pasar el mouse', 'megadruid-cms'),
                $button_text_hover
            );
            ?>
        </table>

        <h2 class="mdcms-section-title"><?php esc_html_e('Enlaces', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <?php
            $this->render_color_row(
                'mdcms_login_link_color',
                'mdcms[login_link_color]',
                __('Color de enlaces', 'megadruid-cms'),
                $link_color
            );
            $this->render_color_row(
                'mdcms_login_link_hover_color',
                'mdcms[login_link_hover_color]',
                __('Enlaces al pasar el mouse', 'megadruid-cms'),
                $link_hover
            );
            $this->render_color_row(
                'mdcms_login_privacy_link_color',
                'mdcms[login_privacy_link_color]',
                __('Enlace de privacidad', 'megadruid-cms'),
                $privacy_color
            );
            $this->render_color_row(
                'mdcms_login_privacy_link_hover_color',
                'mdcms[login_privacy_link_hover_color]',
                __('Privacidad al pasar el mouse', 'megadruid-cms'),
                $privacy_hover
            );
            ?>
            <tr>
                <th scope="row"><?php esc_html_e('Visibilidad', 'megadruid-cms'); ?></th>
                <td>
                    <label for="mdcms_login_hide_register" class="mdcms-role">
                        <input
                            type="checkbox"
                            id="mdcms_login_hide_register"
                            name="mdcms[login_hide_register]"
                            value="1"
                            <?php checked($hide_register); ?>
                        />
                        <?php esc_html_e('Ocultar «Registrarse»', 'megadruid-cms'); ?>
                    </label>
                    <label for="mdcms_login_hide_lost_password" class="mdcms-role">
                        <input
                            type="checkbox"
                            id="mdcms_login_hide_lost_password"
                            name="mdcms[login_hide_lost_password]"
                            value="1"
                            <?php checked($hide_lost); ?>
                        />
                        <?php esc_html_e('Ocultar «¿Olvidaste tu contraseña?»', 'megadruid-cms'); ?>
                    </label>
                    <label for="mdcms_login_hide_back_to" class="mdcms-role">
                        <input
                            type="checkbox"
                            id="mdcms_login_hide_back_to"
                            name="mdcms[login_hide_back_to]"
                            value="1"
                            <?php checked($hide_back); ?>
                        />
                        <?php esc_html_e('Ocultar «Volver al sitio»', 'megadruid-cms'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <h2 class="mdcms-section-title"><?php esc_html_e('CSS y JavaScript propios', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mdcms_login_custom_css"><?php esc_html_e('CSS propio', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <textarea class="large-text code" rows="6" id="mdcms_login_custom_css" name="mdcms[login_custom_css]"><?php echo esc_textarea((string) Settings::get('login_custom_css', '')); ?></textarea>
                    <p class="description"><?php esc_html_e('Se imprime solo en wp-login.php. No se acepta PHP.', 'megadruid-cms'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_login_custom_js"><?php esc_html_e('JavaScript propio', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <textarea class="large-text code" rows="6" id="mdcms_login_custom_js" name="mdcms[login_custom_js]"><?php echo esc_textarea((string) Settings::get('login_custom_js', '')); ?></textarea>
                    <p class="description"><?php esc_html_e('Se imprime en el pie del login, no en la tienda. Solo quien administra puede guardarlo.', 'megadruid-cms'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    private function render_color_row(
        string $input_id,
        string $name,
        string $label,
        string $value
    ): void {
        ?>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr($input_id); ?>"><?php echo esc_html($label); ?></label>
            </th>
            <td>
                <input
                    type="text"
                    class="mdcms-color"
                    id="<?php echo esc_attr($input_id); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    value="<?php echo esc_attr($value); ?>"
                />
            </td>
        </tr>
        <?php
    }

    private function render_media_row(
        string $input_id,
        string $name,
        string $label,
        string $value,
        string $choose_title,
        string $description = ''
    ): void {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td>
                <div class="mdcms-media" data-mdcms-media>
                    <img
                        data-mdcms-media-preview
                        class="mdcms-media__preview"
                        src="<?php echo esc_url($value); ?>"
                        alt=""
                        <?php echo $value === '' ? 'hidden' : ''; ?>
                    />
                    <input
                        type="url"
                        class="regular-text"
                        id="<?php echo esc_attr($input_id); ?>"
                        name="<?php echo esc_attr($name); ?>"
                        value="<?php echo esc_url($value); ?>"
                        data-mdcms-media-url
                    />
                    <p class="mdcms-media__actions">
                        <button
                            type="button"
                            class="button"
                            data-mdcms-media-choose
                            data-title="<?php echo esc_attr($choose_title); ?>"
                            data-button="<?php esc_attr_e('Usar esta imagen', 'megadruid-cms'); ?>"
                        ><?php esc_html_e('Elegir imagen', 'megadruid-cms'); ?></button>
                        <button type="button" class="button-link" data-mdcms-media-clear>
                            <?php esc_html_e('Quitar', 'megadruid-cms'); ?>
                        </button>
                    </p>
                    <?php if ($description !== '') : ?>
                        <p class="description"><?php echo esc_html($description); ?></p>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
    }

    private function render_general_tab(): void {
        if (!Wizard::is_complete() || isset($_GET['mdcms_step'])) {
            Wizard::render();
        } elseif (current_user_can('manage_options')) {
            $restart = add_query_arg('mdcms_step', '1', admin_url('options-general.php?page=' . Settings::PAGE_SLUG . '&tab=general'));
            echo '<p><a class="button" href="' . esc_url($restart) . '">' . esc_html__('Volver a abrir el asistente', 'megadruid-cms') . '</a></p>';
        }
        $hide_help = (bool) Settings::get('hide_help_box', false);
        $hide_screen = (bool) Settings::get('hide_screen_options', false);
        $hide_nags = (bool) Settings::get('hide_nag_messages', false);
        $admin_css = (string) Settings::get('admin_custom_css', '');
        $editor_css = (string) Settings::get('editor_stylesheet', '');
        ?>
        <p class="mdcms-placeholder">
            <?php esc_html_e('Estas opciones aplican a todo el escritorio. El CSS extra no se carga en la tienda. La importación JSON llega en la etapa 6.', 'megadruid-cms'); ?>
        </p>
        <h2 class="mdcms-section-title"><?php esc_html_e('Ayuda y avisos', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Pestañas', 'megadruid-cms'); ?></th>
                <td>
                    <label for="mdcms_hide_help_box" class="mdcms-role">
                        <input type="checkbox" id="mdcms_hide_help_box" name="mdcms[hide_help_box]" value="1" <?php checked($hide_help); ?> />
                        <?php esc_html_e('Ocultar la pestaña Ayuda', 'megadruid-cms'); ?>
                    </label>
                    <label for="mdcms_hide_screen_options" class="mdcms-role">
                        <input type="checkbox" id="mdcms_hide_screen_options" name="mdcms[hide_screen_options]" value="1" <?php checked($hide_screen); ?> />
                        <?php esc_html_e('Ocultar Opciones de pantalla', 'megadruid-cms'); ?>
                    </label>
                    <label for="mdcms_hide_nag_messages" class="mdcms-role">
                        <input type="checkbox" id="mdcms_hide_nag_messages" name="mdcms[hide_nag_messages]" value="1" <?php checked($hide_nags); ?> />
                        <?php esc_html_e('Ocultar avisos de actualización de WordPress', 'megadruid-cms'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <h2 class="mdcms-section-title"><?php esc_html_e('CSS', 'megadruid-cms'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="mdcms_admin_custom_css"><?php esc_html_e('CSS extra del admin', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <textarea class="large-text code" rows="6" id="mdcms_admin_custom_css" name="mdcms[admin_custom_css]"><?php echo esc_textarea($admin_css); ?></textarea>
                    <p class="description"><?php esc_html_e('Solo en el escritorio. No se acepta PHP.', 'megadruid-cms'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mdcms_editor_stylesheet"><?php esc_html_e('Hoja del editor', 'megadruid-cms'); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        class="regular-text"
                        id="mdcms_editor_stylesheet"
                        name="mdcms[editor_stylesheet]"
                        value="<?php echo esc_attr($editor_css); ?>"
                    />
                    <p class="description">
                        <?php esc_html_e('URL absoluta (https://…) o ruta relativa al tema activo, por ejemplo editor-style.css.', 'megadruid-cms'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
        Metaboxes::render_settings();
    }

    private function render_menus_tab(): void {
        $marked = Settings::get('restricted_roles', []);
        if (!is_array($marked)) {
            $marked = [];
        }
        $roles = wp_roles()->roles;
        ?>
        <p class="mdcms-placeholder">
            <?php esc_html_e('Los roles marcados reciben el escritorio recortado. Quien administra la marca sigue viendo WordPress completo. Los menús marcados abajo se ocultan y no se pueden abrir por URL.', 'megadruid-cms'); ?>
        </p>
        <fieldset>
            <legend class="screen-reader-text"><?php esc_html_e('Roles con escritorio recortado', 'megadruid-cms'); ?></legend>
            <?php foreach ($roles as $slug => $role) : ?>
                <?php
                $slug = (string) $slug;
                $label = translate_user_role((string) ($role['name'] ?? $slug));
                $id = 'mdcms_role_' . $slug;
                ?>
                <label for="<?php echo esc_attr($id); ?>" class="mdcms-role">
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($id); ?>"
                        name="mdcms[restricted_roles][]"
                        value="<?php echo esc_attr($slug); ?>"
                        <?php checked(in_array($slug, $marked, true)); ?>
                    />
                    <?php echo esc_html($label); ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php
        $hide_bar_roles = Settings::get('hide_frontend_admin_bar_roles', []);
        if (!is_array($hide_bar_roles)) {
            $hide_bar_roles = [];
        }
        ?>
        <h2 class="mdcms-section-title"><?php esc_html_e('Barra en el sitio público', 'megadruid-cms'); ?></h2>
        <p class="description">
            <?php esc_html_e('En la tienda y el resto del frente, sin el escritorio. Quien administra la marca la sigue viendo.', 'megadruid-cms'); ?>
        </p>
        <fieldset>
            <legend class="screen-reader-text"><?php esc_html_e('Ocultar barra de admin en el frente', 'megadruid-cms'); ?></legend>
            <?php foreach ($roles as $slug => $role) : ?>
                <?php
                $slug = (string) $slug;
                $label = translate_user_role((string) ($role['name'] ?? $slug));
                $id = 'mdcms_hide_bar_' . $slug;
                ?>
                <label for="<?php echo esc_attr($id); ?>" class="mdcms-role">
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($id); ?>"
                        name="mdcms[hide_frontend_admin_bar_roles][]"
                        value="<?php echo esc_attr($slug); ?>"
                        <?php checked(in_array($slug, $hide_bar_roles, true)); ?>
                    />
                    <?php echo esc_html($label); ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php
        Menus::render_inventory();
    }
}
