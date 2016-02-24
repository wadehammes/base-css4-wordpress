<ul class="ul-none">
  <?php
    /*=========================================
    =           Partner Logo Links            =
    =========================================*/
    if( have_rows('partners', 'options') ):
    while ( have_rows('partners', 'options') ) : the_row();
    $partner = get_sub_field('partner_title');
    $partner = hyphenate($partner);
    $partner_logo = get_sub_field('partner_logo');
    ?>
    <li class="partner-logo">
      <a href="<?php the_sub_field('partner_url'); ?>" target="_blank" class="partner-link inline-block mr3">
        <img src="<?php echo $partner_logo['url']; ?>" alt="<?php echo $partner_logo['alt']; ?>" />
      </a>
    </li>
  <?php endwhile; endif; ?>
</ul>
