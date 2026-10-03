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
        Brand_Pack::seed();
        require_once MDCMS_PATH . 'includes/class-branding.php';
        require_once MDCMS_PATH . 'includes/class-login.php';
        require_once MDCMS_PATH . 'includes/class-dashboard.php';
        require_once MDCMS_PATH . 'includes/class-menus.php';
        require_once MDCMS_PATH . 'includes/class-admin-ui.php';
        require_once MDCMS_PATH . 'includes/class-metaboxes.php';
        require_once MDCMS_PATH . 'includes/class-client-ip.php';
        require_once MDCMS_PATH . 'includes/class-login-limit.php';
        require_once MDCMS_PATH . 'includes/class-transfer.php';
        require_once MDCMS_PATH . 'includes/class-legacy-import.php';
        require_once MDCMS_PATH . 'includes/class-hardening.php';
        require_once MDCMS_PATH . 'includes/class-security.php';
        (new Branding())->register();
        (new Login())->register();
        (new Dashboard())->register();
        (new Menus())->register();
        (new Admin_Ui())->register();
        (new Metaboxes())->register();
        (new Client_Ip())->register();
        (new Login_Limit())->register();
        (new Transfer())->register();
        (new Legacy_Import())->register();
        (new Hardening())->register();

        if (is_admin()) {
            require_once MDCMS_PATH . 'includes/class-admin-layout.php';
            require_once MDCMS_PATH . 'includes/class-manual.php';
            require_once MDCMS_PATH . 'includes/class-admin.php';
            (new Admin())->register();
        }
    }

    public static function activate(): void {
        Settings::ensure_option_exists();
        Brand_Pack::seed();
    }
}
