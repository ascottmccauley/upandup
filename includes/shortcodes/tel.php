<?php 
/**
 * [tel] / [phone]
 * 
 * Encodes and creates an telephone link
 *
 * Examples:
 * [tel]555-555-1234[/tel]
 *
 * [tel number="555-555-1234"]Call Me[/tel]
 *
 * [tel number="555-555-1234" label="Call Me"]
 *
 * [tel SMS="true" label="Text Me"]555-555-1234[/tel]
**/
function shortcode_tel( $atts, $content = null ) {
	extract( shortcode_atts( array(
	'number' => '',
	'label' => '',
	'button' => '',
	'style' => '',
	'text' => '',
	'SMS' => '',
	), $atts ) );
	
	// Set $number and $label based on available info
	if( $label == '' ) {
		if( $content == '' && $number != '' ) {
			$label = antispambot( $number );
		}else {
			$label = antispambot( $content );
		}
	}
	if( $number == '' ) {
		$number = $content;
	}
	
	// Set $link
	$number = preg_replace( '/[^0-9]/', '', $number ); //strip out all non-numeric characters
	if( ! substr( $number, 0, 1 ) == 1 ) { // add leading 1 if it's not there
		$number = '+1' . $number;
	}
	// Text or Call?
	if(  $text == true || $SMS == true ) {
		$link = 'sms:' . antispambot( $number );
	}else {
		$link = 'tel:+1' . antispambot( $number );
	}
	
	//add a space before $style;
	$style = $style != '' ? ' ' . $style : '';
	
	// Set $class
	if($button != '' || $style != '') {
		$class = 'class="button' . $style . '"';
	}else {
	 $class = '';
	}
	return '<a ' . $class . 'href="' . $link . '">' . $label . '</a>';
}
add_shortcode( 'tel', 'shortcode_tel' );
add_shortcode( 'phone', 'shortcode_tel' );
