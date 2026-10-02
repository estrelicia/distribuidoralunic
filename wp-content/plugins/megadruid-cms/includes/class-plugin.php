<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

final class Plugin {

    private static ?Plugin $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void {
        require_once MDCMS_PATH . 'includes/class-access.php';
        require_once MDCMS_PATH . 'includes/class-branding.php';
        require_once MDCMS_PATH . 'includes/class-login.php';
        require_once MDCMS_PATH . 'includes/class-dashboard.php';
        require_once MDCMS_PATH . 'includes/class-menus.php';
        require_once MDCMS_PATH . 'includes/class-admin-ui.php';
        require_once MDCMS_PATH . 'includes/class-metaboxes.php';
        require_once MDCMS_PATH . 'includes/class-wizard.php';
        (new Branding())->register();
        (new Login())->register();
        (new Dashboard())->register();
        (new Menus())->register();
        (new Admin_Ui())->register();
        (new Metaboxes())->register();
        (new Wizard())->register();

        if (is_admin()) {
            require_once MDCMS_PATH . 'includes/class-admin.php';
            (new Admin())->register();
        }
    }

    public static function activate(): void {
        Settings::ensure_option_exists();
    }
}
