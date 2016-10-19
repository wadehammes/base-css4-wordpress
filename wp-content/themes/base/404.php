<?php
$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

get_header(); ?>
	<div class="e404 p3">
		<h1 class="text-huge white pt1">404</h1>

		<p class="white">This page wasn't found, trying searching or go back <a href="/">home</a>.</p>

		<div class="mb1"><?php get_search_form(); ?></div>

		<p class="text-x-small bc block mb4">If you think you are here by mistake, <a href="mailto:wade@trackmaven.com?subject=404 Page Error&body=The url <?php echo $url ?> has 404d by mistake I think, please fix.%0D%0A%0D%0AThank you!">email us</a>!</p>
	</div>
<?php get_footer(); ?>
