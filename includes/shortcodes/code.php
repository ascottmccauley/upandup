<?php 
/**
 * [code] or [pre]
 *
 * wraps content in <code></code> or <pre></pre> tags
 * also overrides all shortcodes contained inside content
**/
function shortcode_code( $atts, $content = null, $tag ) {
	$content = esc_html($content);
	return '<' . $tag . '>' . $content . '</' . $tag . '>';
}
add_shortcode( 'code', 'shortcode_code' );
add_shortcode( 'pre', 'shortcode_code' ); 