<?php
/**
 * Borra solo la opción del plugin.
 *
 * @package Megadruid_Cms
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('mdcms_settings');
