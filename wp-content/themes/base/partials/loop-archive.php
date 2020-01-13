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

<article id="post-<?php the_ID(); ?>" <?php post_class('article article-archive'); ?>>
	<img itemprop="image" src="<?php echo $thumb_url; ?>" class="hidden" alt="<?php the_title(); ?>" />
	<span itemprop="mainEntityOfPage" class="hidden" content="<?php the_permalink(); ?>"></span>
	<span itemprop="publisher" class="hidden" content="<?php echo $publisher; ?>"></span>
	<time itemprop="dateModified" class="hidden" content="<?php echo $modifiedDate; ?>"></time>

	<header class="article-header">
		<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
	</header>

	<section class="article-content" itemprop="articleBody">
		<?php the_post_thumbnail('large'); ?>
		<?php the_excerpt(); ?>
	</section>

	<footer class="article-footer">
		<p class="tags"><?php the_tags('<span class="tags-title">' . __('Tags:', 'jointstheme') . '</span> ', ', ', ''); ?></p>
	</footer>

</article>
