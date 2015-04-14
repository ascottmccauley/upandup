<?php 
/**
 * [pricing]
 *
 * Options: [pricing_info] should have style="title, price, description, bullet-item, bullet, cta-button, or button"
 *
 * Example: [pricing] [pricing_info style="title"]Title[/pricing_info] [pricing_info text="description] [pricing_info style="bullet"]Bullet[/pricing_info] [/pricing]
**/
function shortcode_pricing( $atts, $content = null, $tag ) {
	extract( shortcode_atts( array(
		'style' => 'description',
	), $atts ) );
	if($tag == 'pricing') {
		// create container
		return '<ul class="pricing-table">' . do_shortcode($content) . '</ul>';
	}else {
		// create li
		if($text == ''){
			$text = do_shortcode($content);
		}
		
		$style = ($style == 'bullet' ? 'bullet-item' : $style); // change bullet to bullet-item
		$style = ($style == 'button' ? 'cta-button' : $style); // change button to cta-button
		
		return '<li class="' . $style . '">' . $text . '</li>';
	}
}
add_shortcode('pricing', 'shortcode_pricing');
add_shortcode('pricing_info', 'shortcode_pricing');