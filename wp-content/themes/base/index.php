<?php get_header(); ?>

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<?php get_template_part( 'partials/loop', 'archive' ); ?>
		<?php endwhile; ?>

		<?php if ( function_exists( 'wp_pagenavi' ) ) : ?>
			<nav class="prev-next">
			  <?php wp_pagenavi(array('query' => $query_post)); ?>
			</nav>
		<?php endif; ?>

		<?php else : ?>
			<?php get_template_part( 'partials/missing', 'content' ); ?>
		<?php endif; ?>

<?php get_footer(); ?>
