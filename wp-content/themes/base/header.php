<!--[if lt IE 7 ]><html<?php language_attributes(); ?> class="ie6"><![endif]-->
<!--[if (IE 7)&!(IEMobile) ]><html <?php language_attributes(); ?> class="ie7"><![endif]-->
<!--[if (IE 8)&!(IEMobile) ]><html <?php language_attributes(); ?> class="ie8"><![endif]-->
<!--[if (IE 9)&!(IEMobile) ]><html <?php language_attributes(); ?> class="ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <!-- MOBILE -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!-- FAVICON -->
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

    <!-- RSS -->
    <link rel="feed" type="application/atom+xml" href="<?php bloginfo('atom_url'); ?>" title="Atom Feed">
    <link rel="feed" type="application/rss+xml" href="<?php bloginfo('rss2_url'); ?>" title="RSS Feed">

    <!-- DETECT SMART BROWSERS -->
    <script type="text/javascript" defer="defer">
      /*=============================================
      =            Detect Smart Browsers            =
      =============================================*/
      if ('visibilityState' in document) {
        var doc = document.getElementsByTagName("html");
        doc[0].className = 'modern-browser';
      }
    </script>

    <?php wp_head(); ?>

    <!-- TYPEKIT ACCOUNT -->
    <script src="https://use.typekit.net/shd4pjs.js"></script>
    <script>try{Typekit.load({ async: true });}catch(e){}</script>

    <!-- SCRIPT -->
    <script async="async" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>

    <!-- GA -->
    <script type="text/javascript">
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', '', 'auto');
      ga('send', 'pageview');
    </script>

    <?php if (is_user_logged_in()) { ?>
      <style>
        .header-nav.slide {
          top: 32px;
        }

        @media screen and (max-width: 600px) {
          #wpadminbar {
            position: fixed !important;
          }
          .header-nav.slide {
            top: 46px;
          }
        }
      </style>
    <?php } ?>
  </head>

  <body <?php body_class(); ?>>

    <!-- Begin Site Container -->
    <div class="site-container relative p4 z4">
