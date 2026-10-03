<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Quién ve el escritorio completo y quién recibe recortes de menú.
 */
final class Access {

    public static function user(?\WP_User $user = null): \WP_User {
        if ($user instanceof \WP_User) {
            return $user;
        }
        $current = wp_get_current_user();

        return $current instanceof \WP_User ? $current : new \WP_User();
    }

    /**
     * @return string
     */
    public static function primary_role(?\WP_User $user = null): string {
        $user = self::user($user);
        $roles = [];
        foreach ((array) $user->roles as $role) {
            if (is_string($role) && $role !== '') {
                $roles[] = $role;
            }
        }
        $role = $roles[0] ?? '';
        $filtered = apply_filters('mdcms_primary_role', $role, $roles, $user);

        return is_string($filtered) ? sanitize_key($filtered) : '';
    }

    /**
     * Administradores elegidos en Menús. Si la lista está vacía, cualquiera
     * con manage_options ve el escritorio completo.
     */
    public static function sees_full_admin(?\WP_User $user = null): bool {
        $user = self::user($user);
        if (!$user->exists() || !user_can($user, 'manage_options')) {
            return false;
        }
        $ids = Settings::get('full_access_admin_ids', []);
        if (!is_array($ids) || $ids === []) {
            return true;
        }

        return in_array((int) $user->ID, array_map('intval', $ids), true);
    }

    public static function manages_brand(?\WP_User $user = null): bool {
        return self::sees_full_admin($user);
    }

    public static function receives_trimmed(?\WP_User $user = null): bool {
        $user = self::user($user);
        if (!$user->exists()) {
            return false;
        }

        return !self::sees_full_admin($user);
    }
}
