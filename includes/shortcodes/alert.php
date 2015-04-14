<?php 
/**
 * [alert]
 *
 * Creates an alert message at the top of the page
 *
 * Examples:
 * [alert style="(alert|success|secondary)"]This is an alert[/alert]
 * or
 * [alert text="This is an alert."]
**/
function shortcode_alert( $atts, $content = null ) {
	extract( shortcode_atts( array(
	'style' => '  ', /* alert, success, secondary */
	'text' => '', 
	), $atts ) );
	
	if($text == '') {
		$text = do_shortcode($content);
	}
	//add a space before $style;
	$style = $style != '' ? ' ' . $style : '';
	
	$output = '<div data-alert class="alert-box'. $style . '">';
	$output .= $text;
	$output .= '<button class="close">&times;</button>';
	$output .= '</div>';
	
	return $output;
}
add_shortcode('alert', 'shortcode_alert');
