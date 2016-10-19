<?php get_header(); ?>

	<h1 class="page-title">
		<span><?php _e('Search Results for:', 'jointstheme'); ?></span>
		<?php echo esc_attr(get_search_query()); ?>
	</h1>

	<div class="content-loop">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<?php get_template_part( 'partials/loop', 'archive' ); ?>
		<?php endwhile; if(found_posts() > $maxposts) :  ?>
		<nav class="page-navigation">
			<?php if ( function_exists( 'wp_pagenavi' ) ) wp_pagenavi(); ?>
		</nav>
		<?php endif; ?>
		<?php else : ?>
			<?php get_template_part( 'partials/missing', 'content' ); ?>
		<?php endif; ?>
	</div>

<?php get_footer(); ?>
