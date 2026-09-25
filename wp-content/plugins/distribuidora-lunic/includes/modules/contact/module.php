<?php

namespace Distribuidora_Lunic\Modules\Contact;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        add_shortcode('lunic_contact', [$this, 'render']);
        add_action('admin_post_nopriv_lunic_contact', [$this, 'handle']);
        add_action('admin_post_lunic_contact', [$this, 'handle']);
    }

    public function render(): string {
        $sent = isset($_GET['contacto']) && $_GET['contacto'] === 'ok';
        $html = '';
        if ($sent) {
            $html .= '<p>Recibimos tu consulta. Te respondemos a la brevedad.</p>';
        }
        $html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        $html .= '<input type="hidden" name="action" value="lunic_contact" />';
        $html .= wp_nonce_field('lunic_contact', 'lunic_contact_nonce', true, false);
        $html .= '<p><label>Nombre<br><input required name="nombre" type="text" /></label></p>';
        $html .= '<p><label>Correo electrónico<br><input required name="email" type="email" /></label></p>';
        $html .= '<p><label>Teléfono<br><input name="telefono" type="text" /></label></p>';
        $html .= '<p><label>Consulta<br><textarea required name="consulta" rows="5"></textarea></label></p>';
        $html .= '<p><button type="submit">Enviar</button></p></form>';
        return $html;
    }

    public function handle(): void {
        if (!isset($_POST['lunic_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lunic_contact_nonce'])), 'lunic_contact')) {
            wp_die('No se pudo enviar el formulario.');
        }
        $nombre = isset($_POST['nombre']) ? sanitize_text_field(wp_unslash($_POST['nombre'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $telefono = isset($_POST['telefono']) ? sanitize_text_field(wp_unslash($_POST['telefono'])) : '';
        $consulta = isset($_POST['consulta']) ? sanitize_textarea_field(wp_unslash($_POST['consulta'])) : '';
        $body = "Nombre: {$nombre}\nCorreo: {$email}\nTeléfono: {$telefono}\n\n{$consulta}";
        wp_mail('info@distribuidoralunic.com.ar', 'Consulta desde la web', $body, ['Reply-To: ' . $email]);
        wp_safe_redirect(add_query_arg('contacto', 'ok', wp_get_referer() ?: home_url('/contacto/')));
        exit;
    }
}
