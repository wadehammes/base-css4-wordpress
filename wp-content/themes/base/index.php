<?php
get_header();
$maxposts = get_option( 'posts_per_page', 15 );
?>

<div class="index">
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
</div>

<?php get_footer(); ?>
