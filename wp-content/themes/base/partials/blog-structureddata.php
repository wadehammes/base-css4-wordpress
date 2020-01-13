<?php
$thumb_id = get_post_thumbnail_id();
$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'large', true);
$thumb_url = $thumb_url_array[0];
$thumb_width = $thumb_url_array[1];
$thumb_height = $thumb_url_array[2];

// Get Author Information
$author = get_the_author();
$publisher = get_bloginfo('name');
$modified_date = get_the_modified_date( 'Y-m-d H:i:s' );
$published_date = get_the_time('Y-m-d H:i:s'); ?>

<div class="blog-structured-data" hidden>
  <!-- Image -->
  <div itemprop="image" itemscope itemtype="http://schema.org/ImageObject" hidden>
    <link itemprop="url" content="<?php echo $thumb_url; ?>"/>
    <img itemprop="image" src="<?php echo $thumb_url; ?>">
    <meta itemprop="height" content="<?php echo $thumb_height; ?>"/>
    <meta itemprop="width" content="<?php echo $thumb_width; ?>"/>
  </div>

  <!-- Entity -->
  <span itemprop="mainEntityOfPage" hidden><?php the_permalink(); ?></span>

  <!-- Permalink -->
  <a href="<?php the_permalink(); ?>" rel="bookmark" hidden><?php the_permalink(); ?></a>

  <!-- Dates -->
  <time class="published" itemprop="datePublished" content="<?php echo $published_date; ?>" title="<?php echo $published_date; ?>" datetime="<?php echo $published_date; ?>" pubdate hidden></time>
  <time class="updated" itemprop="dateModified" content="<?php echo $modified_date; ?>" title="<?php echo $modified_date; ?>" datetime="<?php echo $modified_date; ?>" hidden></time>

  <!-- Author -->
  <span itemprop="author" rel="author" name="<?php echo $author; ?>" hidden><?php echo $author; ?></span>

  <!-- Publisher -->
  <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" hidden>
    <span itemprop="name" hidden><?php bloginfo('name'); ?></span>
    <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
      <link itemprop="url" content="<?php echo get_template_directory_uri(); ?>/library/img/skyword-s-logo.png">
      <meta itemprop="width" content="230">
      <meta itemprop="height" content="60">
    </div>
  </div>
</div>
