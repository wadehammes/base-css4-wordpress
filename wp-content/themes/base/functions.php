<?php
// Theme support options
require_once(get_template_directory().'/library/functions/theme-support.php');

// WP Head and other cleanup functions
require_once(get_template_directory().'/library/functions/cleanup.php');

// Register scripts and stylesheets
require_once(get_template_directory().'/library/functions/enqueue-scripts.php');

// Register custom menus and menu walkers
require_once(get_template_directory().'/library/functions/menu.php');
require_once(get_template_directory().'/library/functions/menu-walkers.php');

/*========================================
=            Custom Functions            =
========================================*/

/**
* Hyphenate - used to turn strings into slugs
**/
function hyphenate($str, array $noStrip = []) {
  // non-alpha and non-numeric characters become spaces
  $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
  $str = trim($str);
  $str = str_replace(" ", "-", $str);
  $str = strtolower($str);

  return $str;
}

// Register Options Page
if( function_exists('acf_add_options_page') ) {
  acf_add_options_page('Global Options');
}

/*********************
CUSTOM FUNCTIONS
*********************/
function get_post_count($categories) {
  global $wpdb;
  $post_count = 0;

    foreach($categories as $cat) {
      $querystr = "
        SELECT count
        FROM $wpdb->term_taxonomy, $wpdb->posts, $wpdb->term_relationships
        WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id
        AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
        AND $wpdb->term_taxonomy.term_id = $cat
        AND $wpdb->posts.post_status = 'publish'
      ";
      $result = $wpdb->get_var($querystr);
      $post_count += $result;
   }

   return $post_count;
}
?>
