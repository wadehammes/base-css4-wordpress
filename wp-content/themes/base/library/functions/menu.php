<?php
// Register menus
register_nav_menus(
	array(
		'main-nav' => __( 'The Main Menu', 'experiustheme' ),  // Main nav in header
		'social-nav' => __( 'Social Menu', 'experiustheme' )   // Social navigation
	)
);

// The Top Menu
function experius_top_nav() {
	 wp_nav_menu(array(
      'container' => false,                           // Remove nav container
      'container_class' => '',                        // Class of container
      'menu' => 'The Top Menu', 'experiustheme',      // Menu name
      'menu_class' => 'top-bar-menu ul-none',   // Adding custom nav class
      'theme_location' => 'main-nav',                 // Where it's located in the theme
      'before' => '',                                 // Before each link <a>
      'after' => '',                                  // After each link </a>
      'link_before' => '',                            // Before each link text
      'link_after' => '',                             // After each link text
      'depth' => 5,                                   // Limit the depth of the nav
      'fallback_cb' => false,                         // Fallback function (see below)
      'walker' => new Top_Bar_Walker()
    ));
} /* End Top Menu */

// The Footer Menu
function experius_social_nav() {
	 wp_nav_menu(array(
      'container' => false,                           // Remove nav container
      'container_class' => '',                        // Class of container
      'menu' => 'Social Menu', 'experiustheme',       // Menu name
      'menu_class' => 'social-menu ul-none',          // Adding custom nav class
      'theme_location' => 'social-nav',               // Where it's located in the theme
      'before' => '',                                 // Before each link <a>
      'after' => '',                                  // After each link </a>
      'link_before' => '',                            // Before each link text
      'link_after' => '',                             // After each link text
      'depth' => 5,                                   // Limit the depth of the nav
      'fallback_cb' => false,                         // Fallback function (see below)
      'walker' => new Top_Bar_Walker()
    ));
} /* End Top Menu */
?>
