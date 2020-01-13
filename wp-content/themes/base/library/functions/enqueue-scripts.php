<?php
function base_scripts_and_styles() {
  global $wp_styles;
  if (!is_admin()) {
    $theme_version = wp_get_theme()->Version;

  	// Removes WP version of jQuery
    wp_deregister_script('jquery');
    wp_deregister_script('jquery-core');
    wp_deregister_script('imagesloaded');
    wp_deregister_script('thickbox');

    // Remove unecessary Wordpress commenting
    wp_deregister_script('comment-reply');

    // Remove unnecessary WP-Embed script
    wp_deregister_script( 'wp-embed' );

    // Loads jQuery from CDNJS
    wp_register_script( 'jquery-core', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js', array(), '2.2.4', true );
    wp_add_inline_script( 'jquery-core', 'window.jQuery||document.write("<script src='.esc_url(get_template_directory_uri()).'/library/vendor/jquery.min.js><\/script>")');
    wp_enqueue_script('jquery-core');

    // Polyfill for Interection Observer
    wp_enqueue_script( 'site-io', 'https://cdn.polyfill.io/v3/polyfill.js?features=IntersectionObserver', array(), '', true );

    // Register Flickity
    wp_enqueue_script( 'flickity', "https://unpkg.com/flickity@2.2.1/dist/flickity.pkgd.min.js", array(), '', true );
    wp_enqueue_script( 'flickity-bg', "https://unpkg.com/flickity-bg-lazyload@1.0.1/bg-lazyload.js", array(), '', true );
    wp_enqueue_script( 'flickity-fade', "https://unpkg.com/flickity-fade@1.0.0/flickity-fade.js", array(), '', true );

    // Register Isotope
    wp_enqueue_script( 'isotope', "https://unpkg.com/isotope-layout@3.0.6/dist/isotope.pkgd.min.js", array(), '', true );

    // Register ImagesLoaded
    wp_enqueue_script( 'imagesLoaded', "https://unpkg.com/imagesloaded@4.1.4/imagesloaded.pkgd.min.js", array(), '', true );

    // Adding scripts file in the footer
    wp_enqueue_script( 'site-js', get_template_directory_uri() . '/library/js/application.js', array(), '', true );

    // Register main stylesheet
    wp_enqueue_style( 'site-css', get_template_directory_uri() . '/library/css/base.css', array(), '', 'all' );
  }
  if (is_user_logged_in()) {
    wp_enqueue_style( 'dashicons' );
  }
}
add_action('wp_enqueue_scripts', 'base_scripts_and_styles', 999);
?>
