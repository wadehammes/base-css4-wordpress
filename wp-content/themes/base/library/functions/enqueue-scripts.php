<?php
function base_scripts_and_styles() {
  global $wp_styles; // Call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way
  if (!is_admin()) {
    $theme_version = wp_get_theme()->Version;

  	// Removes WP version of jQuery
  	wp_deregister_script('jquery');

    // Remove unecessary Wordpress commenting
    wp_deregister_script('comment-reply');

    // Remove unnecessary WP-Embed script
    wp_deregister_script( 'wp-embed' );

    // Loads jQuery from CDNJS
    wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js', array(), '2.2.4', true );
    wp_add_inline_script( 'jquery', 'window.jQuery||document.write("<script src='.esc_url(get_template_directory_uri()).'/library/vendor/jquery.min.js><\/script>")');
    wp_enqueue_script('jquery');

    // Adding scripts file in the footer
    wp_enqueue_script( 'site-js', get_template_directory_uri() . '/library/js/application.js', array(), '', true );

    // Register main stylesheet
    wp_enqueue_style( 'site-css', get_template_directory_uri() . '/library/css/base.css', array(), '', 'all' );

    // Add FontAwesome
    wp_enqueue_style( 'font-awesome-5', 'https://pro.fontawesome.com/releases/v5.7.1/css/all.css', array(), '5.7.1', 'all' );
  }
}
add_action('wp_enqueue_scripts', 'base_scripts_and_styles', 999);
?>
