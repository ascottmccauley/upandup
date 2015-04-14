<?php 
/**
 * [panel]
 *
 * Creates a panel
 *
 * Example:
 * [panel]This is panel[/panel]
 * or
 * [panel text="This is a panel."]
**/
function shortcode_panel( $atts, $content = null ) {
	extract( shortcode_atts( array(
	'style' => '', /* callout */
	'close' => 'false', /* display close link */
	'text' => '', 
	), $atts ) );
	
	if($text == ''){
		$text = do_shortcode($content);
	}
	
	$output = '<div class="panel ' . $style . '">';
	$output .= $text;
	$output .= '</div>';
	
	return $output;
}
add_shortcode('panel', 'shortcode_panel');