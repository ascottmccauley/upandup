<?php 
/**
 * [contact]
 *
 * Creates a standard contact form.
 * Sends to admin email by default unless otherwise specified
 * Adds additional text fields as needed
 *
 * Example:
 * [contact email="you@domain.com"]
 * [contact fields="website, phone number"]
**/
function shortcode_contact( $atts, $content = null, $tag ) {
	extract( shortcode_atts( array(
	'email' => '', 
	'fields' => '',
	), $atts ) );
	
	$sendEmail = null;
	$error = null;
	
	// Default Fields
	$contactFields = array();
	$contactFields[ 'name' ] = array( 'type' => 'text', 'placeholder' => 'Your Name', 'required' => ' required' );
	$contactFields[ 'email' ] = array( 'type' => 'email', 'placeholder' => 'Your Email', 'required' => ' required' );
	
	// Add fields from shortcode
	if($fields != '' ) {
		foreach( explode( ',', str_replace( ', ',',',$fields ) ) as $field ) {
			$contactFields[ trim($field) ] = array( 'type' => 'text', 'placeholder' => 'Your ' . ucwords($field), 'required' => '' );
		}
	}
	
	$contactFields[ 'message' ] = array('type' => 'textarea', 'placeholder' => 'Your Message', 'required' => ' required' );
		
	// Get a unique hash based on the date
	$hash = contact_get_hash();
	
	// Process Inputs
	$is_valid_nonce = ( isset( $_POST[ 'contact_nonce' ] ) && wp_verify_nonce( $_POST[ 'contact_nonce' ], 'contact_nonce_contact_action' ) ) ? true : false;
	if( $is_valid_nonce == true ) {
		// Check Decoy Fields
		$fields = array( 'First Name', 'Last Name', 'Email 2', 'Address', 'Address2', 'City', 'State', 'Zipcode', 'Telephone', 'Phone' );
		foreach ( $fields as $decoy ) {
			if( isset( $_POST[ $decoy . $hash ] ) ) {
				if( $_POST[ $decoy . $hash ] != '' ) {
					wp_die( 'There has been a serious error. Sorry.' );
				}
			}
		}
		
		// Sanitize all other fields
		$errors = false;
		foreach( $contactFields as $name => $field) {
			if( isset( $_POST['contact' . $name . $hash] ) ) {
				if( $field['type'] == 'email' ) {
					$value = sanitize_email( $_POST['contact' . $name . $hash] );
					if( !is_email( $value ) ) {
						$value = false;
					}
				}else {
					$value = sanitize_text_field( $_POST['contact' . $name . $hash] );
				}
				if( $value === false ) {
					$contactFields[$name]['error'] = 'This value is invalid';
					$errors = true;
				}elseif ( $field['required'] != '' && $value == '' ) {
					$contactFields[$name]['error'] = 'This field is required';
					$errors = true;
				}else {
					$contactFields[$name]['value'] = $value;
				}
				$contactFields[$name];
			}
		}
		
		// Format and Send Email
		if( empty( $errors ) ) {
			// get email address
			if( $email == '') {
				$email = get_option( 'admin_email' );
			}
			$mailFrom = "From: " . $contactFields['name']['value'] . " <" . $contactFields['email']['value'] . ">\r\nReply-To:" . $contactFields['email']['value'];
			$mailSubject = $contactFields['name']['value'] ." sent you a message from " . get_option("blogname");
			$mailMessage = '';
			foreach( $contactFields as $name => $field ) {
				$mailMessage .= ucwords( $name ) . ": " . $field['value'] . "\r\n";
			}
			
			// Send Email
			$sendEmail = wp_mail($email, $mailSubject, $mailMessage, $mailFrom);
		}
	}

	// Display Contact Form	
	// Contact Form Submitted
	if( $sendEmail === true ) {
		$output = '<div class="alert-box success" data-alert><button type="button" class="close">&times;</button>Your message has been sent!</div>';
	}elseif( $sendEmail === false ) { 
		$output = '<div class="alert-box alert" data-alert><button type="button" class="close">&times;</button>There seems to be an error with sending the message. Sorry for the inconvenience!</div>';
	}else {
		$inputs = array();
		foreach( $contactFields as $name => $field ) {
			// Get Values
			$type = !empty( $field['type'] ) ? $field['type'] : 'text';
			$class = !empty( $field['error'] ) ? ' class="error"' : '';
			$error = !empty( $field['error'] ) ? $field['error'] : '';
			$value = !empty( $field['value'] ) ? $field['value'] : '';
			$placeholder = !empty( $field['placeholder'] ) ? ' placeholder="' . $field['placeholder'] . '"': '';
			$required = !empty( $field['required'] ) ? ' required="required"' : '';
			
			// Display Field
			if( $type != 'textarea' ) {
				$input = '<input type="' . $type . '" name="contact' . $name . $hash . '"' . $placeholder . '" value="' . $value . '"' . $required . $class . '>';
			}else {
				$input = '<textarea name="contact' . $name . $hash . '"' . $placeholder . '"' . $required . $class . ' rows="5">' . $value . '</textarea>';
			}
			if( $error ) {
				$input .= '<small class="error">' . $error . '</small>';
			}
			$inputs[$name] = $input;
		}
		
		$output = '<form method="post">';
		
		// Mix in decoy fields
		$output .= contact_get_decoy_fields();
		
		foreach( apply_filters( 'contact_contact_inputs', $inputs ) as $input ) {
			$output .= $input;
		}
		
		$output .= wp_nonce_field( 'contact_nonce_contact_action' , 'contact_nonce', true, false);
		$output .= '<input type="submit" name="submit" value="Send" class="button">';
		$output .= '</form>';
	}
	
	return $output;
	
}
add_shortcode( 'contact', 'shortcode_contact' );

// Creates a random 6 digit number for 24 hours
function contact_get_hash() {
	srand( date( 'Ymd' ) );
	$number = rand( 0,9999999 );
	$hash = substr( sha1( $number ), 0, 6);
	return $hash;
}

function contact_get_decoy_fields( ) {
	$hash = contact_get_hash();
	$decoy_fields = '';
	$fields = array( 'First Name', 'Last Name', 'Email 2', 'Address', 'Address2', 'City', 'State', 'Zipcode', 'Telephone', 'Phone' );
	foreach ( $fields as $field ) {
		$name = strtolower( str_replace( ' ', '', $field ) ) . $hash;
		$decoy_fields .= '<label class="hide" for="' . $name .'">' . ucwords( $field ) . ' *</label><input class="hide" name="' . $name . '" type="text" autocomplete="off">';
	}
	return $decoy_fields;
}