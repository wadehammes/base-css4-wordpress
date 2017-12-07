<?php
get_header();
$count_posts = wp_count_posts();
$published_posts = $count_posts->publish;
$maxposts = get_option( 'posts_per_page', 15 );
?>

<div class="index">
	<div class="content-loop">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<?php get_template_part( 'partials/loop', 'archive' ); ?>
		<?php endwhile; if($published_posts > $maxposts) {  ?>
			<nav class="page-navigation">
				<?php if ( function_exists( 'wp_pagenavi' ) ) wp_pagenavi(); ?>
			</nav>
		<?php } ?>
		<?php else : ?>
			<?php get_template_part( 'partials/missing', 'content' ); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
