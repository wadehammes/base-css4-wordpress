<?php get_header(); ?>

		<div class="index-loop">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<?php get_template_part( 'partials/loop', 'archive' ); ?>
			<?php endwhile; ?>
		</div>
		<nav class="page-navigation">
			<?php if ( function_exists( 'wp_pagenavi' ) ) wp_pagenavi(); ?>
		</nav>
		<?php else : ?>
			<?php get_template_part( 'partials/missing', 'content' ); ?>
		<?php endif; ?>

<?php get_footer(); ?>
