<?php 
/**
 * [button]
 *
 *
 * Creates a button
 *
 * Examples:
 * [button style="(radius|round)(mini|small|large)(alert|success|secondary|disabled)" url/link="http://#"]This is a button[/button]
 * or
 * [button text="This is a button." url="http://#"]
*/
function shortcode_button( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'url'  => '',
		'link' => '',
		'style' => '', /* radius, round */
		'text' => '', 
		), $atts ) );
	
	if($text == ''){
		$text = do_shortcode($content);
	}
	// Allow user to use link="" or url=""
	if($link != '') {
			$url = $link;
	}elseif($url == '') {
		$url = $atts[0];
	}
	// Add http:// if user did not include it
	if(!strpos($url, 'http') === 0) { 
		$url = 'http://' . $url;
	}
	$url .= ' href="' . $url . '"';
	
	$output = '<a href="' . $url . '" class="button '. $style;
	$output .= '">';
	$output .= $text;
	$output .= '</a>';
	
	return $output;
}
add_shortcode('button', 'shortcode_button'); 