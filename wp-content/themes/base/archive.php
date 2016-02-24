<?php
get_header(); ?>

	<main class="site">

		<div class="container clearfix">

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

        <?php if (function_exists('joints_page_navi')) { ?>
	        <?php joints_page_navi(); ?>
        <?php } else { ?>
	        <nav class="wp-prev-next">
		        <ul class="clearfix">
			        <li class="prev-link"><?php next_posts_link(__('&laquo; Older Entries', "jointstheme")) ?></li>
			        <li class="next-link"><?php previous_posts_link(__('Newer Entries &raquo;', "jointstheme")) ?></li>
		        </ul>
    	    </nav>
        <?php } ?>

	    <?php else : ?>

				<?php get_template_part( 'partials/missing', 'content' ); ?>

	    <?php endif; ?>

		</div>

	</main>

<?php
get_footer(); ?>
