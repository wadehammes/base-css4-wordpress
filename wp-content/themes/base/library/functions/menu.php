<?php
// Register menus
register_nav_menus(array(
		'main-nav' => __( 'Main Navigation', 'base' ),  // Main nav in header
	)
);

// Main Navigation
function main_top_nav() {
  wp_nav_menu(array(
    'container' => false,                           // Remove nav container
    'container_class' => '',                        // Class of container
    'menu' => 'Main Navigation', 'base',      			// Menu name
    'menu_class' => 'navigation main-navigation',   // Adding custom nav class
    'theme_location' => 'main-nav',                 // Where it's located in the theme
    'before' => '',                                 // Before each link <a>
    'after' => '',                                  // After each link </a>
    'link_before' => '',                            // Before each link text
    'link_after' => '',                             // After each link text
    'depth' => 5,                                   // Limit the depth of the nav
    'fallback_cb' => false                          // Fallback function
  ));
}
?>
