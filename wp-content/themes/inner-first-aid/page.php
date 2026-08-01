<?php
/**
 * Page template. Elementor hooks into the_content for builder pages.
 *
 * @package inner-first-aid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ifa-page">
		<?php the_content(); ?>
	</div>
	<?php
endwhile;

get_footer();
