<?php 
if(!function_exists ('crawler_post_function')){
	function crawler_post_function($atts,$content){
		extract(shortcode_atts(array(
			'style'			=>	'left',
		),$atts));
		crawler();
	}
}
add_shortcode('crawlers','crawler_post_function');

if(!function_exists ('crawler_keyword_function')){
	function crawler_keyword_function($atts,$content){
		extract(shortcode_atts(array(
			'style'			=>	'left',
		),$atts));
		crawler_keyword();
	}
}
add_shortcode('crawlers_keyword','crawler_keyword_function');
