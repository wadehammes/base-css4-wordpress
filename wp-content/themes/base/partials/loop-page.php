<section id="page-<?php the_ID(); ?>" <?php post_class('page'); ?> itemscope itemtype="http://schema.org/BlogPosting">

	<header class="page-header">
		<h1 class="page-title"><?php the_title(); ?></h1>
	</header>

	<section class="page-content" itemprop="postBody">
		<?php the_content(); ?>
	</section>

	<footer class="page-footer">
		<p><?php the_tags('<span class="tags">' . __('Tags:', 'jointstheme') . '</span> ', ', ', ''); ?></p>
	</footer>

</section>
