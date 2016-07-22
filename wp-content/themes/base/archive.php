<?php
get_header(); ?>
	<div class="archive">

		<h1 class="header text-small mt4 mb1">
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

	</div>

<?php
get_footer(); ?>
