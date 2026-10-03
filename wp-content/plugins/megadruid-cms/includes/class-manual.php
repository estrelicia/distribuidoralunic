<?php

namespace Megadruid_Cms;

defined('ABSPATH') || exit;

/**
 * Manual de usuario dentro del escritorio.
 */
final class Manual {

    public static function render(): void {
        ?>
        <div class="mdcms-manual">
            <p class="mdcms-manual__lead">
                <?php esc_html_e('Este texto es para quien administra el sitio. No hace falta saber PHP. Los cambios se prueban en local antes de llevarlos a producción.', 'megadruid-cms'); ?>
            </p>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Por dónde empezar', 'megadruid-cms'); ?></h2>
                <ol class="mdcms-manual__steps">
                    <li><?php esc_html_e('Andá a Ajustes → Megadruid CMS.', 'megadruid-cms'); ?></li>
                    <li><?php esc_html_e('Ajustá el login en la pestaña Login. Guardá cada pestaña por separado.', 'megadruid-cms'); ?></li>
                    <li><?php esc_html_e('En Menús hay dos bloques: menús de wp-admin (a quién y qué ocultar) y, aparte, la barra negra de la tienda.', 'megadruid-cms'); ?></li>
                    <li><?php esc_html_e('Revisá Seguridad: los valores de fábrica ya sirven para una tienda detrás de Cloudflare.', 'megadruid-cms'); ?></li>
                </ol>
            </section>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Quién ve el recorte', 'megadruid-cms'); ?></h2>
                <p><?php esc_html_e('«Menús del escritorio» es wp-admin: menú izquierdo y atajos de la barra negra dentro del panel. Ahí mismo agregás a quién se le oculta lo tildado.', 'megadruid-cms'); ?></p>
                <p><?php esc_html_e('«Barra negra en la tienda» es otra cosa: la barra de WordPress arriba cuando alguien navega el sitio público logueado, no el menú del escritorio.', 'megadruid-cms'); ?></p>
            </section>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Marca fija', 'megadruid-cms'); ?></h2>
                <p><?php esc_html_e('La barra de admin (isotipo) y el título «Megadruid AD» son fijos. El contacto está en la caja del Escritorio. El menú lateral de WordPress no lleva logo de Megadruid.', 'megadruid-cms'); ?></p>
            </section>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Pestaña Login', 'megadruid-cms'); ?></h2>
                <p><?php esc_html_e('Solo el logo del cliente (320 × 84 px) y, si querés, una imagen de fondo (1920 × 1080 px). El formulario queda como WordPress.', 'megadruid-cms'); ?></p>
                <p><?php esc_html_e('«Vista previa del login» abre wp-login.php con un borrador de 15 minutos. No escribe la opción definitiva. «Guardar cambios» sí la actualiza.', 'megadruid-cms'); ?></p>
            </section>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Pestaña Escritorio', 'megadruid-cms'); ?></h2>
                <p><?php esc_html_e('Todos los paneles del Escritorio de WordPress (nativos y de otros plugins), a quién se le ocultan. En el Escritorio está la caja «Megadruid accesos directos» con el portal del cliente y el contacto. También: ocultar Ayuda y Opciones de pantalla, avisos de actualización, exportar/importar JSON (máximo 256 KB, sin PHP) y restablecer de fábrica.', 'megadruid-cms'); ?></p>
            </section>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Pestaña Menús', 'megadruid-cms'); ?></h2>
                <p><?php esc_html_e('Primero, quiénes ven wp-admin completo. Después, barra negra en la tienda (sitio público). Por último, en la misma tarjeta de menús del escritorio: a quién se le oculta y el mapa de casillas.', 'megadruid-cms'); ?></p>
                <p><?php esc_html_e('Las cuentas tildadas como administradores completos no se recortan, aunque hayas agregado el perfil Administrador.', 'megadruid-cms'); ?></p>
            </section>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Pestaña Seguridad', 'megadruid-cms'); ?></h2>
                <p><?php esc_html_e('La tabla de arriba muestra la IP de esta visita, si viene de Cloudflare y el país. El límite de login corta a los 5 fallos en 15 minutos (configurable). El mensaje de error no dice si el usuario existe.', 'megadruid-cms'); ?></p>
                <p><?php esc_html_e('No actives «REST anónima cerrada por completo» en esta tienda: WooCommerce y el buscador la necesitan. Ocultar usuarios de la REST sí puede quedar activo.', 'megadruid-cms'); ?></p>
                <p><?php esc_html_e('readme.html puede seguir viéndose si el servidor entrega el archivo sin pasar por WordPress. Eso no lo resuelve este plugin solo.', 'megadruid-cms'); ?></p>
            </section>

            <section class="mdcms-manual__section">
                <h2><?php esc_html_e('Qué no hace este plugin', 'megadruid-cms'); ?></h2>
                <ul>
                    <li><?php esc_html_e('Envíos, descuentos, menú de categorías ni checkout: eso es Distribuidora Lunic y el tema Lunic.', 'megadruid-cms'); ?></li>
                    <li><?php esc_html_e('No borra White Label CMS ni Megadruid Seguridad ni sus opciones al desinstalarse. Solo borra mdcms_settings y mdcms_dashboard_panels.', 'megadruid-cms'); ?></li>
                    <li><?php esc_html_e('No sustituye Wordfence, 2FA ni las reglas del WAF de Cloudflare.', 'megadruid-cms'); ?></li>
                </ul>
            </section>
        </div>
        <?php
    }

    /**
     * @return array<int, array{title: string, content: string}>
     */
    public static function help_tabs(): array {
        return [
            [
                'title' => __('Resumen', 'megadruid-cms'),
                'content' => '<p>' . esc_html__('Megadruid CMS marca el escritorio y el login, recorta menús por rol y aplica una capa de seguridad para sitios detrás de Cloudflare. Cada pestaña se guarda sola.', 'megadruid-cms') . '</p>',
            ],
            [
                'title' => __('Roles', 'megadruid-cms'),
                'content' => '<p>' . esc_html__('El recorte de menús de wp-admin va en la tarjeta «Menús del escritorio» (a quién y qué). La barra negra de la tienda es independiente.', 'megadruid-cms') . '</p>',
            ],
        ];
    }
}
