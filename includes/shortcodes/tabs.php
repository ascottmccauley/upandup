<?php 
/**
 * [tabs]
 *
 * Creates tabbed content
 *
 * Options: style="auto, vertical-nav, horizontal-nav, accordian
 * 
 * Example:
 * [tabs] [tab title="tab1]Content[/tab] [tab title="tab2"]Content[/tab] [/tabs]
 *
**/
function shortcode_tabs( $atts, $content = null, $tag ) {
	global $tabs;
	
	if( ! empty( $tabs ) ) {
		unset( $tabs );
		$tabs = array();
	}
	extract( shortcode_atts( array(
		'style' => '',
	), $atts ) );
	
	// Create tabs in array
	do_shortcode( $content );
	
	$content = '<dl class="tabs ' . $style . '" data-tab role="tablist">';
	$content .= implode( '', $tabs['title'] );
	$content .= '</dl>';
	$content .= '<div class="tabs-content ' . $style . '">';
	$content .= implode( '', $tabs['content'] );
	
	// Clear global array
	unset( $tabs );
	return $content;
}
add_shortcode( 'tabs', 'shortcode_tabs' );

function shortcode_tab( $atts, $content = null, $tag ) {
	global $tabs;
	
	extract( shortcode_atts( array(
		'title' => '',
		'text' => '',
	), $atts ) );
	
	// make first tab active by default
	if ( empty( $tabs ) ) {
		$active = ' active';
	}else {
		$active = '';
	}
	
	// get unique number
	$tabNum = 'tab-' . substr( uniqid(), -4 ); //last 4 digits of uniqid will suffice
	
	// add tabs to global array
	$tabs['title'][] = '<dd class="' . $active . '" role="presentational"><a href="#' . $tabNum . '" tabindex="0" aria-selected="false" controls="' . $tabNum . '" role="tab">' . $title . '</a></dd>';
	$tabs['content'][] = '<div class="content' . $active . '" id="' . $tabNum . '">' . do_shortcode(trim($content)) . '</div>';
}
add_shortcode( 'tab', 'shortcode_tab' );