<?php
get_header();
$count_posts = wp_count_posts();
$published_posts = $count_posts->publish;
$maxposts = get_option( 'posts_per_page', 15 );
?>
<div class="archive">
	<h1 class="page-title">
		<?php if (is_category()) { ?>
			<span><?php _e("", "base"); ?></span> <?php single_cat_title(); ?>
		<?php } elseif (is_tag()) { ?>
			<span><?php _e("", "base"); ?></span> <?php single_tag_title(); ?>
		<?php } elseif (is_author()) {
			global $post;
			$author_id = $post->post_author;
			?>
			<span><?php _e("", "base"); ?></span> <?php echo get_the_author_meta('display_name', $author_id); ?>
		<?php } elseif (is_day()) { ?>
			<span><?php _e("", "base"); ?></span> <?php the_time('l, F j, Y'); ?>
		<?php } elseif (is_month()) { ?>
			<span><?php _e("", "base"); ?></span> <?php the_time('F Y'); ?>
		<?php } elseif (is_year()) { ?>
			<span><?php _e("", "base"); ?></span> <?php the_time('Y'); ?>
		<?php } ?>
	</h1>

	<div class="content-loop">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<?php get_template_part( 'partials/loop', 'archive' ); ?>
		<?php endwhile; if($published_posts > $maxposts) :  ?>
			<nav class="page-navigation">
				<?php if ( function_exists( 'wp_pagenavi' ) ) wp_pagenavi(); ?>
			</nav>
		<?php endif; else : ?>
			<?php get_template_part( 'partials/missing', 'content' ); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
