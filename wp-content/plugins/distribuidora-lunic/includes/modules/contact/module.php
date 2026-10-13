<?php

namespace Distribuidora_Lunic\Modules\Contact;

use Distribuidora_Lunic\Visitor;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        add_shortcode('lunic_contact', [$this, 'render']);
        add_action('admin_post_nopriv_lunic_contact', [$this, 'handle']);
        add_action('admin_post_lunic_contact', [$this, 'handle']);
    }

    public function render(): string {
        $code = isset($_GET['contacto']) ? sanitize_key(wp_unslash((string) $_GET['contacto'])) : '';
        $html = '<div class="lunic-contact__form">';
        if ($code === 'ok') {
            $html .= '<p class="lunic-empty">Recibimos tu consulta. Te respondemos a la brevedad.</p>';
        } elseif ($code === 'correo') {
            $html .= '<p class="lunic-empty">El correo electrónico no es válido.</p>';
        } elseif ($code === 'limite') {
            $html .= '<p class="lunic-empty">Esperá unos minutos antes de enviar otra consulta.</p>';
        } elseif ($code === 'vacio') {
            $html .= '<p class="lunic-empty">Completá el nombre y la consulta.</p>';
        } elseif ($code === 'error') {
            $html .= '<p class="lunic-empty">No pudimos enviar la consulta. Probá de nuevo o escribinos por WhatsApp.</p>';
        }
        $html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        $html .= '<input type="hidden" name="action" value="lunic_contact" />';
        $html .= wp_nonce_field('lunic_contact', 'lunic_contact_nonce', true, false);
        $html .= '<p style="position:absolute;left:-9999px;height:0;overflow:hidden" aria-hidden="true"><label>Empresa<br><input name="empresa" type="text" tabindex="-1" autocomplete="off" /></label></p>';
        $html .= '<p><label>Nombre<br><input required name="nombre" type="text" /></label></p>';
        $html .= '<p><label>Correo electrónico<br><input required name="email" type="email" /></label></p>';
        $html .= '<p><label>Teléfono<br><input name="telefono" type="text" /></label></p>';
        $html .= '<p><label>Consulta<br><textarea required name="consulta" rows="5"></textarea></label></p>';
        $html .= '<p><button type="submit">Enviar</button></p></form></div>';
        return $html;
    }

    public function handle(): void {
        $reason = $this->rejection();
        if ($reason === 'nonce') {
            wp_die('No se pudo enviar el formulario.');
        }
        if ($reason === 'honeypot') {
            $this->redirect('ok');
        }
        if ($reason !== '') {
            $this->redirect($reason);
        }
        $nombre = isset($_POST['nombre']) ? sanitize_text_field(wp_unslash($_POST['nombre'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $telefono = isset($_POST['telefono']) ? sanitize_text_field(wp_unslash($_POST['telefono'])) : '';
        $consulta = isset($_POST['consulta']) ? sanitize_textarea_field(wp_unslash($_POST['consulta'])) : '';
        $body = "Nombre: {$nombre}\nCorreo: {$email}\nTeléfono: {$telefono}\n\n{$consulta}";
        $site = get_option('lunic_site', []);
        $to = is_array($site) ? sanitize_email((string) ($site['email'] ?? '')) : '';
        if ($to === '') {
            $to = (string) get_option('admin_email');
        }
        $sent = wp_mail($to, 'Consulta desde la web', $body, ['Reply-To: ' . $email]);
        $this->redirect($sent ? 'ok' : 'error');
    }

    public function rejection(): string {
        if (!isset($_POST['lunic_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lunic_contact_nonce'])), 'lunic_contact')) {
            return 'nonce';
        }
        $honeypot = isset($_POST['empresa']) ? trim((string) wp_unslash($_POST['empresa'])) : '';
        if ($honeypot !== '') {
            return 'honeypot';
        }
        $nombre = isset($_POST['nombre']) ? sanitize_text_field(wp_unslash($_POST['nombre'])) : '';
        $consulta = isset($_POST['consulta']) ? sanitize_textarea_field(wp_unslash($_POST['consulta'])) : '';
        if ($nombre === '' || $consulta === '') {
            return 'vacio';
        }
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        if (!is_email($email)) {
            return 'correo';
        }
        if (Visitor::limited('contact', 3, 10 * MINUTE_IN_SECONDS)) {
            return 'limite';
        }
        return '';
    }

    private function redirect(string $code): void {
        wp_safe_redirect(add_query_arg('contacto', $code, wp_get_referer() ?: home_url('/contacto/')));
        exit;
    }
}
