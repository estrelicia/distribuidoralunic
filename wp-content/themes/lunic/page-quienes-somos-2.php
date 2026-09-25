<?php
get_header();
echo '<article class="lunic-page">';
while (have_posts()) {
    the_post();
    the_content();
}
echo '</article>';
get_footer();
