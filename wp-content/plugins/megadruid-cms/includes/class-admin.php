<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

final class Admin {

    private string $hook_suffix = '';

    public function register(): void {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'guard']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_filter('admin_body_class', [$this, 'body_class']);
        add_action('admin_post_' . Settings::SAVE_ACTION, [$this, 'save']);
        add_filter('plugin_action_links_' . MDCMS_BASENAME, [$this, 'action_links']);
        add_filter('all_plugins', [$this, 'hide_from_plugins_list']);
        add_filter('site_transient_update_plugins', [$this, 'hide_updates']);
    }

    public function body_class(string $classes): string {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && $screen->id === 'settings_page_' . Settings::PAGE_SLUG) {
            $classes .= ' mdcms-app-screen';
        }

        return $classes;
    }

    /**
     * @param string[] $links
     * @return string[]
     */
    public function action_links(array $links): array {
        if (!Access::can_see_plugin()) {
            return $links;
        }
        $url = admin_url('options-general.php?page=' . Settings::PAGE_SLUG);
        $manual = add_query_arg('tab', 'manual', $url);
        array_unshift(
            $links,
            '<a href="' . esc_url($url) . '">' . esc_html__('Ajustes', 'megadruid-cms') . '</a>',
            '<a href="' . esc_url($manual) . '">' . esc_html__('Manual', 'megadruid-cms') . '</a>'
        );

        return $links;
    }

    public function guard(): void {
        $page = isset($_GET['page']) ? sanitize_key((string) wp_unslash($_GET['page'])) : '';
        if ($page === Settings::PAGE_SLUG && !Access::can_see_plugin()) {
            Access::deny_plugin();
        }
        if (!$this->request_targets_this_plugin()) {
            return;
        }
        $action = isset($_REQUEST['action']) ? (string) wp_unslash($_REQUEST['action']) : '';
        if ($action === '-1' && isset($_REQUEST['action2'])) {
            $action = (string) wp_unslash($_REQUEST['action2']);
        }
        if (in_array($action, ['activate', 'activate-selected', 'deactivate', 'deactivate-selected', 'delete-selected'], true)
            && !Access::can_see_plugin()
        ) {
            Access::deny_plugin();
        }
    }

    /**
     * @param array<string, mixed> $plugins
     * @return array<string, mixed>
     */
    public function hide_from_plugins_list(array $plugins): array {
        if (!is_user_logged_in() || Access::can_see_plugin()) {
            return $plugins;
        }
        unset($plugins[MDCMS_BASENAME]);

        return $plugins;
    }

    /**
     * @param mixed $transient
     * @return mixed
     */
    public function hide_updates($transient) {
        if (!is_object($transient) || !is_user_logged_in() || Access::can_see_plugin()) {
            return $transient;
        }
        unset($transient->response[MDCMS_BASENAME], $transient->no_update[MDCMS_BASENAME]);

        return $transient;
    }

    private function request_targets_this_plugin(): bool {
        $plugin = isset($_REQUEST['plugin']) ? (string) wp_unslash($_REQUEST['plugin']) : '';
        if ($plugin === MDCMS_BASENAME) {
            return true;
        }
        $checked = isset($_REQUEST['checked']) && is_array($_REQUEST['checked']) ? $_REQUEST['checked'] : [];
        foreach ($checked as $slug) {
            if ((string) wp_unslash($slug) === MDCMS_BASENAME) {
                return true;
            }
        }

        return false;
    }

    public function menu(): void {
        if (!Access::can_see_plugin()) {
            return;
        }
        $hook = add_options_page(
            __('Megadruid CMS', 'megadruid-cms'),
            __('Megadruid CMS', 'megadruid-cms'),
            'manage_options',
            Settings::PAGE_SLUG,
            [$this, 'render_page']
        );
        $this->hook_suffix = is_string($hook) ? $hook : '';
        if ($this->hook_suffix !== '') {
            add_action('load-' . $this->hook_suffix, [$this, 'add_help']);
        }
    }

    public function add_help(): void {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }
        foreach (Manual::help_tabs() as $index => $tab) {
            $screen->add_help_tab([
                'id' => 'mdcms-help-' . $index,
                'title' => $tab['title'],
                'content' => $tab['content'],
            ]);
        }
        $manual_url = add_query_arg(
            [
                'page' => Settings::PAGE_SLUG,
                'tab' => 'manual',
            ],
            admin_url('options-general.php')
        );
        $screen->set_help_sidebar(
            '<p><strong>' . esc_html__('Megadruid CMS', 'megadruid-cms') . '</strong></p>'
            . '<p><a href="' . esc_url($manual_url) . '">' . esc_html__('Abrir el manual', 'megadruid-cms') . '</a></p>'
        );
    }

    public function assets(string $hook): void {
        if ($this->hook_suffix === '' || $hook !== $this->hook_suffix) {
            return;
        }
        $css = MDCMS_PATH . 'assets/css/admin.css';
        $js = MDCMS_PATH . 'assets/js/admin.js';
        wp_enqueue_style(
            'megadruid-cms-fonts',
            'https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800&display=swap',
            [],
            null
        );
        wp_enqueue_style(
            'megadruid-cms-admin',
            MDCMS_URL . 'assets/css/admin.css',
            ['dashicons', 'megadruid-cms-fonts'],
            file_exists($css) ? (string) filemtime($css) : MDCMS_VERSION
        );
        wp_enqueue_media();
        wp_enqueue_script(
            'megadruid-cms-admin',
            MDCMS_URL . 'assets/js/admin.js',
            ['jquery'],
            file_exists($js) ? (string) filemtime($js) : MDCMS_VERSION,
            true
        );
        wp_localize_script(
            'megadruid-cms-admin',
            'mdcmsAdmin',
            [
                'ajax' => admin_url('admin-ajax.php'),
                'searchNonce' => wp_create_nonce('mdcms_search_pages'),
                'rolePickPlaceholder' => __('Elegí un perfil…', 'megadruid-cms'),
                'roleRemove' => __('Quitar', 'megadruid-cms'),
            ]
        );
    }

    public function render_page(): void {
        if (!Access::can_see_plugin()) {
            Access::deny_plugin();
        }

        $tabs = Admin_Layout::tabs();
        $active = isset($_GET['tab']) ? sanitize_key((string) wp_unslash($_GET['tab'])) : 'login';
        if ($active === 'general') {
            $active = 'dashboard';
        }
        if (!isset($tabs[$active])) {
            $active = 'login';
        }

        $base_url = admin_url('options-general.php?page=' . Settings::PAGE_SLUG);
        $intro = (string) ($tabs[$active]['intro'] ?? '');
        $active_label = (string) ($tabs[$active]['label'] ?? '');
        $saveable = in_array($active, ['login', 'dashboard', 'menus', 'security'], true);
        ?>
        <div class="wrap mdcms-wrap">
            <div class="mdcms-app">
                <aside class="mdcms-app__rail" id="mdcms-app-rail">
                    <a class="mdcms-brand" href="https://megadruid.com" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('Megadruid', 'megadruid-cms'); ?>">
                        <img
                            class="mdcms-brand__logo"
                            src="<?php echo esc_url(Brand_Pack::mark_url()); ?>"
                            alt="<?php esc_attr_e('Megadruid', 'megadruid-cms'); ?>"
                            width="28"
                            height="28"
                        />
                    </a>
                    <nav class="mdcms-nav" aria-label="<?php esc_attr_e('Secciones de Megadruid CMS', 'megadruid-cms'); ?>">
                        <?php foreach ($tabs as $slug => $tab) : ?>
                            <a
                                href="<?php echo esc_url(add_query_arg('tab', $slug, $base_url)); ?>"
                                class="mdcms-nav__item<?php echo $slug === $active ? ' is-active' : ''; ?>"
                                title="<?php echo esc_attr((string) $tab['label']); ?>"
                            >
                                <span class="dashicons <?php echo esc_attr((string) $tab['icon']); ?>" aria-hidden="true"></span>
                                <span class="mdcms-nav__label"><?php echo esc_html((string) $tab['label']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <a
                        class="mdcms-nav__item mdcms-nav__help"
                        href="<?php echo esc_url(add_query_arg('tab', 'manual', $base_url)); ?>"
                        title="<?php esc_attr_e('Manual', 'megadruid-cms'); ?>"
                    >
                        <span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
                        <span class="mdcms-nav__label"><?php esc_html_e('Ayuda', 'megadruid-cms'); ?></span>
                    </a>
                </aside>
                <div class="mdcms-app__main">
                    <header class="mdcms-app__top">
                        <button type="button" class="mdcms-nav-toggle" data-mdcms-nav-toggle aria-expanded="false" aria-controls="mdcms-app-rail">
                            <span class="dashicons dashicons-menu-alt2" aria-hidden="true"></span>
                            <span class="screen-reader-text"><?php esc_html_e('Abrir menú', 'megadruid-cms'); ?></span>
                        </button>
                        <div class="mdcms-app__heading">
                            <h1><?php echo esc_html($active_label); ?></h1>
                            <?php if ($intro !== '') : ?>
                                <p class="mdcms-app__intro"><?php echo esc_html($intro); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="mdcms-app__toolbar">
                            <span class="mdcms-pill mdcms-pill--ink"><?php echo esc_html(sprintf(/* translators: %s: plugin version */ __('v%s', 'megadruid-cms'), MDCMS_VERSION)); ?></span>
                            <a
                                class="mdcms-icon-btn"
                                href="<?php echo esc_url(add_query_arg('tab', 'manual', $base_url)); ?>"
                                title="<?php esc_attr_e('Manual', 'megadruid-cms'); ?>"
                            >
                                <span class="dashicons dashicons-book-alt" aria-hidden="true"></span>
                                <span class="screen-reader-text"><?php esc_html_e('Manual', 'megadruid-cms'); ?></span>
                            </a>
                        </div>
                    </header>
                    <div class="mdcms-app__alerts">
                        <?php $this->render_notice(); ?>
                    </div>
                    <div class="mdcms-app__body">
            <?php if ($active === 'manual') : ?>
                <?php Manual::render(); ?>
            <?php elseif ($active === 'dashboard') : ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mdcms-form">
                    <?php wp_nonce_field(Settings::SAVE_ACTION, 'mdcms_nonce'); ?>
                    <input type="hidden" name="action" value="<?php echo esc_attr(Settings::SAVE_ACTION); ?>" />
                    <input type="hidden" name="tab" value="dashboard" />
                    <div class="mdcms-stack">
                        <?php Dashboard::render_settings(); ?>
                        <?php $this->render_general_options(); ?>
                    </div>
                    <div class="mdcms-actions">
                        <?php submit_button(__('Guardar cambios', 'megadruid-cms'), 'mdcms-btn mdcms-btn--primary', 'submit', false); ?>
                    </div>
                </form>
                <div class="mdcms-stack mdcms-stack--after-save">
                    <?php Transfer::render_settings(); ?>
                </div>
            <?php else : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mdcms-form">
                <?php wp_nonce_field(Settings::SAVE_ACTION, 'mdcms_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr(Settings::SAVE_ACTION); ?>" />
                <input type="hidden" name="tab" value="<?php echo esc_attr($active); ?>" />
                <div class="mdcms-stack">
                    <?php $this->render_tab_panel($active); ?>
                </div>
                <?php if ($saveable) : ?>
                    <div class="mdcms-actions">
                        <?php if ($active === 'login') : ?>
                            <?php submit_button(__('Vista previa del login', 'megadruid-cms'), 'mdcms-btn mdcms-btn--ghost', 'mdcms_preview', false); ?>
                        <?php endif; ?>
                        <?php submit_button(__('Guardar cambios', 'megadruid-cms'), 'mdcms-btn mdcms-btn--primary', 'submit', false); ?>
                    </div>
                <?php endif; ?>
            </form>
            <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function save(): void {
        if (!Access::can_see_plugin()) {
            Access::deny_plugin();
        }
        check_admin_referer(Settings::SAVE_ACTION, 'mdcms_nonce');

        $posted = isset($_POST['mdcms']) && is_array($_POST['mdcms'])
            ? wp_unslash($_POST['mdcms'])
            : [];
        $tab_posted = isset($_POST['tab']) ? sanitize_key((string) wp_unslash($_POST['tab'])) : '';
        if ($tab_posted === 'menus') {
            if (!array_key_exists('full_access_admin_ids', $posted)) {
                $posted['full_access_admin_ids'] = [];
            }
            $current_id = get_current_user_id();
            if ($current_id > 0 && current_user_can('manage_options')) {
                $ids = array_map('intval', (array) $posted['full_access_admin_ids']);
                if (!in_array($current_id, $ids, true)) {
                    $ids[] = $current_id;
                    $posted['full_access_admin_ids'] = $ids;
                }
            }
            if (!array_key_exists('hide_menu_roles', $posted)) {
                $posted['hide_menu_roles'] = [];
            }
            if (!array_key_exists('hide_frontend_admin_bar_roles', $posted)) {
                $posted['hide_frontend_admin_bar_roles'] = [];
            }
            $posted = Menus::fill_missing($posted);
        }
        if ($tab_posted === 'general') {
            $tab_posted = 'dashboard';
        }
        if ($tab_posted === 'dashboard') {
            $posted = Dashboard::fill_missing($posted);
            if (!array_key_exists('hide_help_box', $posted)) {
                $posted['hide_help_box'] = 0;
            }
            if (!array_key_exists('hide_screen_options', $posted)) {
                $posted['hide_screen_options'] = 0;
            }
            if (!array_key_exists('hide_nag_messages', $posted)) {
                $posted['hide_nag_messages'] = 0;
            }
        }
        if ($tab_posted === 'security') {
            $posted = Security::fill_missing($posted);
        }

        $tabs = Admin_Layout::tabs();
        $tab = isset($tabs[$tab_posted]) ? $tab_posted : 'login';
        if ($tab === 'manual') {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'page' => Settings::PAGE_SLUG,
                        'tab' => 'manual',
                    ],
                    admin_url('options-general.php')
                )
            );
            exit;
        }

        if ($tab_posted === 'login' && isset($_POST['mdcms_preview'])) {
            Login::store_preview($posted);
            wp_safe_redirect(add_query_arg('mdcms_preview', '1', wp_login_url()));
            exit;
        }
        Settings::update($posted);
        if ($tab_posted === 'security') {
            Security::sync_legacy_plugin();
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
     * @return array<string, array{label: string, icon: string, intro: string}>
     */
    private function tabs(): array {
        return Admin_Layout::tabs();
    }

    private function render_notice(): void {
        Legacy_Import::render_notice();
        if (isset($_GET['mdcms_imported']) && (string) $_GET['mdcms_imported'] === '1') {
            echo '<div class="notice notice-success is-dismissible"><p>';
            esc_html_e('JSON importado. Las claves desconocidas no se guardaron.', 'megadruid-cms');
            echo '</p></div>';
        }
        if (isset($_GET['mdcms_reset']) && (string) $_GET['mdcms_reset'] === '1') {
            echo '<div class="notice notice-success is-dismissible"><p>';
            esc_html_e('Ajustes restablecidos a los valores de fábrica.', 'megadruid-cms');
            echo '</p></div>';
        }
        $transfer_error = isset($_GET['mdcms_transfer_error']) ? sanitize_key((string) $_GET['mdcms_transfer_error']) : '';
        if ($transfer_error !== '') {
            $messages = [
                'json' => __('El archivo no es JSON válido.', 'megadruid-cms'),
                'php' => __('El archivo contiene PHP y no se importó.', 'megadruid-cms'),
                'size' => __('El archivo está vacío o supera 256 KB.', 'megadruid-cms'),
                'upload' => __('No se pudo leer el archivo subido.', 'megadruid-cms'),
                'empty' => __('El JSON no trae ninguna clave conocida.', 'megadruid-cms'),
                'confirm' => __('Marcá la confirmación para restablecer.', 'megadruid-cms'),
            ];
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($messages[$transfer_error] ?? __('No se pudo completar la operación.', 'megadruid-cms')) . '</p></div>';
        }
        if (!isset($_GET['updated']) || (string) $_GET['updated'] !== '1') {
            return;
        }
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('Ajustes guardados.', 'megadruid-cms');
        echo '</p></div>';
    }

    private function render_tab_panel(string $tab): void {
        if ($tab === 'login') {
            $this->render_login_tab();
            return;
        }
        if ($tab === 'menus') {
            $this->render_menus_tab();
            return;
        }
        if ($tab === 'dashboard') {
            return;
        }
        if ($tab === 'security') {
            $this->render_security_tab();
        }
    }

    private function render_login_tab(): void {
        $logo = (string) Settings::get('login_logo', '');
        $image = (string) Settings::get('login_background_image', '');
        $width = Login::LOGO_WIDTH;
        $height = Login::LOGO_HEIGHT;
        ?>
        <?php
        Admin_Layout::open_card(
            __('Logo del cliente', 'megadruid-cms'),
            sprintf(
                /* translators: 1: width in px, 2: height in px */
                __('Reemplaza el logo de WordPress. Usá un PNG o SVG de %1$d × %2$d px (el recuadro del login tiene ese tamaño; si el archivo es más grande, se ajusta sin recortar).', 'megadruid-cms'),
                $width,
                $height
            ),
            'dashicons-format-image'
        );
        ?>
        <table class="form-table" role="presentation">
            <?php
            $this->render_media_row(
                'mdcms_login_logo',
                'mdcms[login_logo]',
                __('Logo', 'megadruid-cms'),
                $logo,
                __('Elegir logo de login', 'megadruid-cms'),
                sprintf(
                    /* translators: 1: width in px, 2: height in px */
                    __('Medida recomendada: %1$d × %2$d px. Fondo transparente. Horizontal o cuadrado, centrado.', 'megadruid-cms'),
                    $width,
                    $height
                )
            );
            ?>
        </table>
        <?php Admin_Layout::close_card(); ?>
        <?php
        Admin_Layout::open_card(
            __('Imagen de fondo', 'megadruid-cms'),
            __('Opcional. Cubre toda la pantalla de acceso; el formulario de WordPress queda igual.', 'megadruid-cms'),
            'dashicons-format-gallery'
        );
        ?>
        <table class="form-table" role="presentation">
            <?php
            $this->render_media_row(
                'mdcms_login_background_image',
                'mdcms[login_background_image]',
                __('Fondo', 'megadruid-cms'),
                $image,
                __('Elegir fondo de login', 'megadruid-cms'),
                __('Medida recomendada: 1920 × 1080 px (JPG o WebP). Se recorta al centro si el recuadro es distinto.', 'megadruid-cms')
            );
            ?>
        </table>
        <?php Admin_Layout::close_card(); ?>
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

    private function render_general_options(): void {
        $hide_help = (bool) Settings::get('hide_help_box', false);
        $hide_screen = (bool) Settings::get('hide_screen_options', false);
        $hide_nags = (bool) Settings::get('hide_nag_messages', false);
        Admin_Layout::open_card(
            __('Ayuda y avisos', 'megadruid-cms'),
            __('En esta pantalla la pestaña Ayuda de WordPress sigue visible, para que puedas leer el manual contextual.', 'megadruid-cms'),
            'dashicons-editor-help'
        );
        ?>
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
        <?php
        Admin_Layout::close_card();
    }

    private function render_security_tab(): void {
        Security::render_settings();
    }

    private function render_menus_tab(): void {
        $saved_admins = Settings::get('full_access_admin_ids', []);
        if (!is_array($saved_admins)) {
            $saved_admins = [];
        }
        $admins = get_users([
            'capability' => 'manage_options',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);
        $selected_admins = array_map('intval', $saved_admins);
        if ($selected_admins === []) {
            foreach ($admins as $admin) {
                if ($admin instanceof \WP_User) {
                    $selected_admins[] = (int) $admin->ID;
                }
            }
        }
        $roles = wp_roles()->roles;
        $role_labels = [];
        foreach ($roles as $slug => $role) {
            $role_labels[(string) $slug] = translate_user_role((string) ($role['name'] ?? $slug));
        }
        $hide_menu_roles = Menus::hide_menu_roles();
        $hide_bar_roles = Settings::get('hide_frontend_admin_bar_roles', []);
        if (!is_array($hide_bar_roles)) {
            $hide_bar_roles = [];
        }
        ?>
        <?php
        Admin_Layout::open_card(
            __('Administradores con escritorio completo', 'megadruid-cms'),
            __('Solo estas cuentas ven Megadruid CMS (Ajustes, lista de plugins) y el escritorio completo. El resto, aunque sea administrador, no ve el plugin y se recorta si su perfil está abajo en «A quién se le oculta». No podés sacarte a vos mismo. Un administrador nuevo no entra acá hasta que lo marques.', 'megadruid-cms'),
            'dashicons-unlock',
            true
        );
        ?>
        <fieldset>
            <legend class="screen-reader-text"><?php esc_html_e('Administradores con escritorio completo', 'megadruid-cms'); ?></legend>
            <?php foreach ($admins as $admin) : ?>
                <?php
                if (!$admin instanceof \WP_User) {
                    continue;
                }
                $id = 'mdcms_full_admin_' . (int) $admin->ID;
                $label = $admin->display_name !== '' ? $admin->display_name : $admin->user_login;
                ?>
                <label for="<?php echo esc_attr($id); ?>" class="mdcms-role">
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($id); ?>"
                        name="mdcms[full_access_admin_ids][]"
                        value="<?php echo esc_attr((string) (int) $admin->ID); ?>"
                        <?php checked(in_array((int) $admin->ID, $selected_admins, true)); ?>
                    />
                    <?php echo esc_html($label); ?>
                    <span class="description">(<?php echo esc_html($admin->user_login); ?>)</span>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php
        Admin_Layout::close_card();
        Admin_Layout::open_card(
            __('Barra negra en la tienda (no es el menú del escritorio)', 'megadruid-cms'),
            __('Cuando una persona entra a la tienda o al sitio público —no a /wp-admin— y está logueada, WordPress pone una barra negra arriba. Acá agregás a qué perfiles se les oculta esa barra. No toca el menú de la izquierda del escritorio.', 'megadruid-cms'),
            'dashicons-visibility',
            true
        );
        Menus::render_role_adder('mdcms[hide_frontend_admin_bar_roles][]', $hide_bar_roles, $role_labels, false);
        Admin_Layout::close_card();
        Menus::render_inventory($hide_menu_roles, $role_labels);
    }
}
