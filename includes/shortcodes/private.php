<?php 
/**
 * [private][restricted]
 *
 * content within this shortcode will only show to users who are logged in
 * an optional message can be shown to users who are not logged in
 *
 * Example:
 * You are viewing [private message="normal content"]<strong>restricted content</strong>[/private].
**/
function shortcode_private( $atts, $content = null ) {
	extract( shortcode_atts( array(
	'message' => '',
	), $atts ) );
	
	if(is_user_logged_in()) {
		return $content;
	}else {
		return $message;
	}
}
add_shortcode('private', 'shortcode_private');
add_shortcode('restricted', 'shortcode_private');