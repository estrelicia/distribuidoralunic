<?php

namespace Distribuidora_Lunic\Modules\MegaMenu;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        require_once __DIR__ . '/class-menu.php';
        require_once __DIR__ . '/class-admin.php';
        if (is_admin()) {
            (new Admin())->register();
        }
        if (get_option('lunic_mega_menu_ready') !== '1') {
            \Distribuidora_Lunic\Page_Cache::flush();
            update_option('lunic_mega_menu_ready', '1', false);
        }
    }
}
