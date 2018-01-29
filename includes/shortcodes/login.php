<?php
/**
 * [login] & [logout]
 *
 * Creates the login form, complete with honeypot fields to reduce spam, or adds a Logout button to logout the current user.
 *
 *
**/
function shortcode_login( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'text' => __('Log Out', 'fin'),
		'redirect' => 'current',
		'class' => 'logout button',
		'login' => true,
		'logout' => true,
	), $atts ) );

	// Check current url for redirect query
	if(isset($_REQUEST['redirect_to'])) {
		$redirect = $_REQUEST['redirect_to'];
	}elseif($redirect == 'current') {
		$redirect = get_the_permalink();
	}elseif($redirect == 'home') {
		$redirect = home_url();
	}

	if( !is_user_logged_in() && $login != false ) {
		$output = wp_login_form( array( 'echo' => false, 'redirect' => $redirect ) );
	}elseif( $logout != false ) {
		$output = '<a href="' . esc_url(wp_logout_url($redirect)). '" class="' . $class . '" >'  . esc_html($text) . '</a>';
	}
	return $output;
}
add_shortcode( 'login', 'shortcode_login' );
add_shortcode( 'logout', 'shortcode_login' );