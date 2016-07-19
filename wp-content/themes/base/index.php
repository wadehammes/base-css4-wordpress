<?php get_header(); ?>

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<?php get_template_part( 'partials/loop', 'archive' ); ?>
		<?php endwhile; ?>

		<?php if (function_exists('joints_page_navi')) { ?>
			<?php joints_page_navi(); ?>
		<?php } else { ?>

		<nav class="prev-next">
		  <?php if ( function_exists( 'wp_pagenavi' ) ) wp_pagenavi(array('query' => $query_post)); ?>
		</nav>
		<?php } ?>

		<?php else : ?>
			<?php get_template_part( 'partials/missing', 'content' ); ?>
		<?php endif; ?>

<?php get_footer(); ?>
