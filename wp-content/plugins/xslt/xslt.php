<?php
/*
 * Plugin Name: Better RSS Feeds
 * Plugin URI: http://wordpress.org/plugins/xslt/
 * Author: Waterloo Plugins
 * Description: Make your RSS feeds look pretty! Your RSS feed is at <a href="../?feed=rss2">http://yoursite.com/?feed=rss2</a>
 * Version: 1.0.8
 * License: GPL2+
 */
 
if (!defined('WPINC'))
	die;

class XSLT{
	function add_template($arg){
		if (strpos(end(headers_list()), 'Content-Type')!==false) {
			remove_filter('option_blog_charset', array(&$this,'add_template'));
			header("Content-Type: text/xml");
			return $arg.'"?><?xml-stylesheet type="text/xsl" href="'.get_bloginfo('home').'/wp-content/plugins/xslt/template.xsl';
		}
		else
			return $arg;
	}
	
	function maybe_add_hook() {
		global $xslt;
		if (!self::is_bot())
			add_action('wp', array($xslt,'charset_hook'));
	}

	function charset_hook($arg){
		if (is_feed() && (strpos(get_query_var('feed'), 'feed')===0 || strpos(get_query_var('feed'), 'rss')===0))
			add_filter('option_blog_charset', array(&$this,'add_template'));
	}

	function encoded_url(){
		echo '<encoded>';
		$host=@parse_url(home_url());
		echo rawurlencode(esc_url(apply_filters('self_link', set_url_scheme('http://'.$host['host'].wp_unslash($_SERVER['REQUEST_URI'])))));
		echo '</encoded>';
	}
	
	function is_bot(){
		static $is_bot=null;
		if ($is_bot!==null)
			return $is_bot;
		
		if (is_user_logged_in())
			return $is_bot=false;
		
		return $is_bot=(!empty($_SERVER['HTTP_USER_AGENT']) && (preg_match('~alexa|baidu|crawler|google|msn|yahoo~i', $_SERVER['HTTP_USER_AGENT']) || preg_match('~bot($|[^a-z])~i', $_SERVER['HTTP_USER_AGENT'])));
	}
}

$xslt = new XSLT();

add_action('rss_head', array(&$xslt, 'encoded_url'));
add_action('rss2_head', array(&$xslt, 'encoded_url'));
add_action('init', array(&$xslt, 'maybe_add_hook'));
