<?php
/**
 * Plugin Name: Megadruid CMS
 * Plugin URI: https://megadruid.com
 * Description: Marca del escritorio y login, menús para clientes y seguridad base para sitios detrás de Cloudflare.
 * Version: 0.3.16
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Megadruid agencia digital
 * License: GPL-2.0-or-later
 * Text Domain: megadruid-cms
 *
 * @package Megadruid_Cms
 */

defined('ABSPATH') || exit;

if (defined('MDCMS_VERSION')) {
    return;
}

define('MDCMS_VERSION', '0.3.16');
define('MDCMS_FILE', __FILE__);
define('MDCMS_PATH', plugin_dir_path(__FILE__));
define('MDCMS_URL', plugin_dir_url(__FILE__));
define('MDCMS_BASENAME', plugin_basename(__FILE__));

require_once MDCMS_PATH . 'includes/class-brand-pack.php';
require_once MDCMS_PATH . 'includes/class-settings.php';
require_once MDCMS_PATH . 'includes/class-plugin.php';

register_activation_hook(MDCMS_FILE, [Megadruid_Cms\Plugin::class, 'activate']);

add_action('plugins_loaded', static function (): void {
    Megadruid_Cms\Plugin::instance()->init();
});
