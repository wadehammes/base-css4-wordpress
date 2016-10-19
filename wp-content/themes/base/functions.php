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

// Register sidebars/widget areas
require_once(get_template_directory().'/library/functions/sidebar.php');

// Makes WordPress comments suck less
// require_once(get_template_directory().'/library/functions/comments.php');

// Replace 'older/newer' post links with numbered navigation
// require_once(get_template_directory().'/library/functions/page-navi.php');

// Adds support for multiple languages
// require_once(get_template_directory().'/library/translation/translation.php');

// Adds site styles to the WordPress editor
// require_once(get_template_directory().'/library/functions/editor-styles.php');

// Related post function - no need to rely on plugins
// require_once(get_template_directory().'/library/functions/related-posts.php');

// Use this as a template for custom post types
// require_once(get_template_directory().'/library/functions/custom-post-type.php');

// Customize the WordPress login menu
// require_once(get_template_directory().'/library/functions/login.php');

// Customize the WordPress admin
// require_once(get_template_directory().'/library/functions/admin.php');

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

/*********************
RESET FILTERS ON NEXT/PREV POST
*********************/
function filter_next_post_sort($sort) {
    $sort = "ORDER BY p.post_title ASC LIMIT 1";
    return $sort;
}
function filter_next_post_where($where) {
    global $post, $wpdb;
    return $wpdb->prepare("WHERE p.post_title > '%s' AND p.post_type = '". get_post_type($post)."' AND p.post_status = 'publish'",$post->post_title);
}

function filter_previous_post_sort($sort) {
    $sort = "ORDER BY p.post_title DESC LIMIT 1";
    return $sort;
}
function filter_previous_post_where($where) {
    global $post, $wpdb;
    return $wpdb->prepare("WHERE p.post_title < '%s' AND p.post_type = '". get_post_type($post)."' AND p.post_status = 'publish'",$post->post_title);
}

add_filter('get_next_post_sort',   'filter_next_post_sort');
add_filter('get_next_post_where',  'filter_next_post_where');

add_filter('get_previous_post_sort',  'filter_previous_post_sort');
add_filter('get_previous_post_where', 'filter_previous_post_where');

/*********************
CUSTOM TAXONOMY CONDITIONAL
*********************/
function has_custax( $custax, $_post = null ) {
  if ( empty( $custax ) )
    return false;
  if ( $_post )
    $_post = get_post( $_post );
  else
    $_post =& $GLOBALS['post'];
  if ( !$_post )
    return false;
  $r = is_object_in_term( $_post->ID, 'custax', $custax );
  if ( is_wp_error( $r ) )
    return false;
  return $r;
}

function bac_manual_auto_excerpt($text) {
    global $post;
    $raw_excerpt = $text;
    if ( '' == $text ) {
        $text = get_the_content('');
        $text = strip_shortcodes( $text );
        $text = apply_filters('the_content', $text);
        $text = str_replace(']]>', ']]&gt;', $text);
    }
    $text = strip_tags($text);
    /*** Change the excerpt words length. If you like. ***/
    $excerpt_length = apply_filters('excerpt_length', 30);

    /*** Change the Excerpt ending. If you like. ***/
    $excerpt_end = ' <a class="read-more" href="'. get_permalink($post->ID) . '">' . 'Read More' . '</a>';
    $excerpt_more = apply_filters('excerpt_more', ' ' . $excerpt_end);

    $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
    return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
}
add_filter('get_the_excerpt', 'bac_manual_auto_excerpt', 5);


//Remove Empty Paragraphs from Content
add_filter('the_content', 'remove_empty_tags_recursive', 20, 1);

function remove_empty_tags_recursive ($str, $repto = NULL) {
   $str = force_balance_tags($str);
   //** Return if string not given or empty.
   if (!is_string ($str)
   || trim ($str) == '')
  return $str;

  //** Recursive empty HTML tags.
  return preg_replace(
    //** Pattern written by Junaid Atari.
    '~\s?<p>(\s|&nbsp;)+</p>\s?~',

    //** Replace with nothing if string empty.
    !is_string ($repto) ? '' : $repto,

    //** Source string
    $str
  );
}
?>
