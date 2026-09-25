<?php
get_header();
echo '<h1>Quiénes somos</h1>';
echo '<p>Distribuidora Lunic es una empresa familiar que nació con la idea de ofrecer productos alimenticios de gran calidad, a un buen precio, con atención personalizada. Comercializamos legumbres, semillas, cereales, frutos secos, aceites, harinas y especias, por mayor y por menor.</p>';
while (have_posts()) {
    the_post();
    the_content();
}
get_footer();
