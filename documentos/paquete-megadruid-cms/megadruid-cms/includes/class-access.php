<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Quién configura la marca y quién recibe el escritorio recortado.
 * La etapa 4 aplica el recorte. Acá solo se responde la pregunta.
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
     * Primer rol del usuario. El filtro mdcms_primary_role permite elegir otro
     * cuando la cuenta tiene varios.
     *
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
     * Administra la marca: puede guardar estos ajustes y no está en los roles recortados.
     * Esa cuenta sigue viendo el escritorio completo.
     */
    public static function manages_brand(?\WP_User $user = null): bool {
        $user = self::user($user);
        if (!$user->exists() || !user_can($user, 'manage_options')) {
            return false;
        }

        return !self::receives_trimmed($user);
    }

    /**
     * El rol principal está marcado para el escritorio recortado.
     */
    public static function receives_trimmed(?\WP_User $user = null): bool {
        $user = self::user($user);
        if (!$user->exists()) {
            return false;
        }
        $role = self::primary_role($user);
        if ($role === '') {
            return false;
        }
        $marked = Settings::get('restricted_roles', []);
        if (!is_array($marked)) {
            return false;
        }

        return in_array($role, $marked, true);
    }
}
