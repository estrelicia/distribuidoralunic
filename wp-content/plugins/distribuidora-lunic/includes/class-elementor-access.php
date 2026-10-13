<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

/**
 * Las plantillas de Elementor se imprimen sin escapar en el tema; solo administradores deben poder editarlas.
 */
class Elementor_Access {

    public function register(): void {
        add_action('init', [$this, 'restrict_library_to_admins'], 100);
    }

    public function restrict_library_to_admins(): void {
        $object = get_post_type_object('elementor_library');
        if (!$object instanceof \WP_Post_Type) {
            return;
        }
        $cap = 'manage_options';
        $object->cap->edit_post = $cap;
        $object->cap->read_post = $cap;
        $object->cap->delete_post = $cap;
        $object->cap->edit_posts = $cap;
        $object->cap->edit_others_posts = $cap;
        $object->cap->publish_posts = $cap;
        $object->cap->read_private_posts = $cap;
        $object->cap->create_posts = $cap;
        $object->cap->delete_posts = $cap;
        $object->cap->delete_private_posts = $cap;
        $object->cap->delete_published_posts = $cap;
        $object->cap->delete_others_posts = $cap;
        $object->cap->edit_private_posts = $cap;
        $object->cap->edit_published_posts = $cap;
    }
}
