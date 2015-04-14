<?php 
/**
 * [row]
 *
 *
 * Creates a row 
 *
 * Example:
 * [row][/row]
**/
function shortcode_row( $atts, $content = null ) {
	return '<div class="row">' . do_shortcode($content) . '</div>';
}
add_shortcode( 'row', 'shortcode_row' );

/**
 * [column] or [span]
 *
 *
 * Creates a column
 *
 * Example:
 * [column span="(small-#|medium-#|large-#|one-third, three-fourths...)" offset="(small-offset-#|medium-offset-#|large-offset-#)" centered="(true|false)"]Your Content[/column]
**/
function shortcode_column( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'span' => '',
		'centered' => '',
		'offset' => '',
		), $atts ) );
	
	// get span as [column span=""] or first variable	[column "small-4 large-8"]
	if($span != '') {
		$span = $span;
	}else {
		$span = $atts[0];
	}
	// if span is just an integer, add large-#
	if(is_integer($span)) {
		$span = 'large-' . $span;
	}
	
	
	if($centered != '') {
		$centered = ' ' . $centered;
	}
	
	if($offset != '') {
		$offset = $offset;
	}

	return '<div class="column ' . esc_attr($span) . esc_attr($offset) . esc_attr($centered) . '">' . do_shortcode($content) . '</div>';
}
add_shortcode( 'column', 'shortcode_column' );
add_shortcode( 'span', 'shortcode_column' );