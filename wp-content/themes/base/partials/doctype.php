<!DOCTYPE html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- MOBILE -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!-- FAVICON -->
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

    <!-- RSS -->
    <link rel="feed" type="application/atom+xml" href="<?php bloginfo('atom_url'); ?>" title="Atom Feed">
    <link rel="feed" type="application/rss+xml" href="<?php bloginfo('rss2_url'); ?>" title="RSS Feed">

    <!-- TYPEKIT ACCOUNT -->
    <!-- typekit css link here -->

    <!-- HTML5SHIV -->
    <script async="async" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>

    <!-- DETECT SMART BROWSERS -->
    <script type="text/javascript" defer="defer">
        // Detects if visibilityState exists, which 99% means this browsers is modern with modern features
        if ("visibilityState" in document) {
            var doc = document.getElementsByTagName("html");
            doc[0].className = "modern-browser";
        }
    </script>

    <!-- LOAD WP ENQUEUES -->
    <?php wp_head(); ?>

    <!-- ADMIN SPECIFIC STYLING -->
    <?php if (is_user_logged_in()) { ?>
        <style>
            .fixed-header {
                top: 32px;
            }

            @media screen and (max-width: 600px) {
                #wpadminbar {
                    position: fixed !important;
                }
                .fixed-header {
                    top: 46px;
                }
            }
        </style>
    <?php } ?>
</head>
<body <?php body_class(); ?>>

    <!-- SITE -->
    <div class="bs-site p5">
