<?php
// Single Post Template
$thumb_id = get_post_thumbnail_id();
$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'large', true);
$thumb_url = $thumb_url_array[0];
// Get Author Information
$curauth = get_the_author_meta('ID');
$author = get_the_author();
$publisher = get_bloginfo('name');
$modifiedDate = get_the_modified_date( 'Y-m-d H:i:s' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('bs-article'); ?> itemscope itemtype="http://schema.org/BlogPosting">

	<header class="article-header">
		<h1 class="entry-title single-title" itemprop="headline"><?php the_title(); ?></h1>
	</header>

	<section class="article-content" itemprop="articleBody">
		<?php the_post_thumbnail('full'); ?>
		<?php the_content(); ?>
	</section>

	<footer class="article-footer">
		<p class="tags"><?php the_tags('<span class="tags-title">' . __('Tags:', 'jointstheme') . '</span> ', ', ', ''); ?></p>
	</footer>

	<?php get_template_part('partials/blog', 'structureddata'); ?>

</article>
