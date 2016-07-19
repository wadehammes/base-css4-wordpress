<?php
/*
 * Plugin Name: Better RSS Feeds
 * Plugin URI: http://wordpress.org/plugins/xslt/
 * Author: Waterloo Plugins
 * Description: Make your RSS feeds look better! Your RSS feed is at <a href="../?feed=rss2">http://yoursite.com/?feed=rss2</a>
 * Version: 1.0.9
 * License: GPL2+
 */
 
if(!defined('WPINC'))
	die;

class XSLT{
	static $credit=false;
	
	function add_template($arg){
		if(is_feed()&&(strpos(get_query_var('feed'),'feed')===0||strpos(get_query_var('feed'),'rss')===0)&&$arg==='rss2'){
			echo '<?xml-stylesheet type="text/xsl" href="'.get_bloginfo('home').'/wp-content/plugins/xslt/template.xsl"?>';
		}
	}
	
	function feed_content_type($content_type,$type){
		if($type==='rss2')
			return 'text/xml';
		return $content_type;
	}

	function encoded_url(){
		echo '<encoded>';
		$host=@parse_url(home_url());
		echo rawurlencode(esc_url(apply_filters('self_link',set_url_scheme('http://'.$host['host'].wp_unslash($_SERVER['REQUEST_URI'])))));
		echo '</encoded>';
	}

	function credit(){
		if(self::$credit&&(is_home()||is_front_page()))
			echo 'Powered by <a href="https://wordpress.org/plugins/xslt/" title="WordPress RSS Feed Plugin">RSS Feeds Plugin</a>';
	}
	
	function is_bot(){
		static $is_bot=null;
		if($is_bot!==null)
			return $is_bot;
		
		return $is_bot=((!empty($_SERVER['HTTP_USER_AGENT']) && preg_match('~alexa|baidu|crawler|google|msn|yahoo~i',$_SERVER['HTTP_USER_AGENT']) || preg_match('~bot($|[^a-z])~i',$_SERVER['HTTP_USER_AGENT'])));
	}
}

$xslt=new XSLT();
add_action('rss_head',array(&$xslt,'encoded_url'));
add_action('rss2_head',array(&$xslt,'encoded_url'));
if(!$xslt->is_bot()){
	add_filter('feed_content_type',array(&$xslt,'feed_content_type'),10,2);
	add_action('rss_tag_pre',array(&$xslt,'add_template'));
}
add_action('loop_start',array(&$xslt,'credit'));
