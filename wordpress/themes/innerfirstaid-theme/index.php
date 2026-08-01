<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @package InnerFirstAid
 */

get_header();
?>

<main id="primary" class="site-main py-24">
	<div class="max-w-4xl mx-auto px-6">
		<?php
		if ( have_posts() ) :

			if ( is_home() && ! is_front_page() ) :
				?>
				<header class="mb-12">
					<h1 class="page-title screen-reader-text text-4xl font-bold text-primary mb-4"><?php single_post_title(); ?></h1>
				</header>
				<?php
			endif;

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'mb-16 border-b border-gray-100 pb-12' ); ?>>
					<header class="entry-header mb-4">
						<h2 class="entry-title text-2xl font-bold text-primary hover:underline">
							<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
						</h2>
					</header>

					<div class="entry-content text-gray-600 leading-relaxed">
						<?php
						the_excerpt();
						?>
					</div>
				</article>
				<?php
			endwhile;

			the_posts_navigation();

		else :
			?>
			<section class="no-results not-found text-center py-12">
				<h1 class="page-title text-2xl font-semibold mb-4"><?php esc_html_e( 'Nothing Found', 'innerfirstaid' ); ?></h1>
				<p class="text-gray-500"><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'innerfirstaid' ); ?></p>
			</section>
			<?php
		endif;
		?>
	</div>
</main><!-- #primary -->

<?php
get_footer();
