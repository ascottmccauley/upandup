<?php 
/**
 * [email]
 * 
 * Encodes and creates an email link
 *
 * Examples:
 * [email]you@url.com[/email]
 *
 * [email address="you@url.com"]Your Name[/email]
 *
 * [email address="you@url.com" label="Your Name"]
**/
function shortcode_email( $atts, $content = null ) {
	extract( shortcode_atts( array(
	'address' => '',
	'label' => '', 
	), $atts ) );
	
	if($address == '') {
		$address = do_shortcode($content);
	}elseif($content != '') {
		$label = do_shortcode($content);
	}
	if($label == '') {
		$label = antispambot($address);
	}
	 
	return '<a href="mailto:' . antispambot($address) . '">' . $label . '</a>';
}
add_shortcode('email', 'shortcode_email');