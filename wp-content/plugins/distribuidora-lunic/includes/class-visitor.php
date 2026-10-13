<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

final class Visitor {

    public static function ip(): string {
        if (class_exists(\Megadruid_Cms\Client_Ip::class)) {
            $ip = \Megadruid_Cms\Client_Ip::client_ip();
            return is_string($ip) && $ip !== '' ? $ip : '0.0.0.0';
        }
        if (class_exists(\WBS_IP::class)) {
            return \WBS_IP::client_ip();
        }
        $remote = isset($_SERVER['REMOTE_ADDR']) ? (string) wp_unslash($_SERVER['REMOTE_ADDR']) : '';
        $remote = preg_replace('/[^0-9a-fA-F:.%]/', '', $remote) ?? '';
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '0.0.0.0';
    }

    public static function limited(string $bucket, int $limit, int $window): bool {
        $key = 'lunic_rl_' . md5($bucket . '|' . self::ip());
        $now = time();
        $hits = get_transient($key);
        if (!is_array($hits) || ($now - (int) ($hits['t'] ?? 0)) >= $window) {
            set_transient($key, ['n' => 1, 't' => $now], $window);
            return false;
        }
        if ((int) ($hits['n'] ?? 0) >= $limit) {
            return true;
        }
        $hits['n'] = (int) $hits['n'] + 1;
        $left = max(1, $window - ($now - (int) $hits['t']));
        set_transient($key, $hits, $left);
        return false;
    }
}
