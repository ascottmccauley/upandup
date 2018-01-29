<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php
// correct path to /media folder
update_option( 'upload_path', str_replace( '/wp/', '/', ABSPATH ) . 'media' );

// Rebuild search index regularly
if ( ! wp_next_scheduled( 'relevanssi_build_index' ) ) {
	wp_schedule_event( time(), 'daily', 'relevanssi_build_index' );
}

// Shortcodes
$shortcodes = scandir( get_stylesheet_directory()  . '/includes/shortcodes' );
// Remove '.' and '..' from array
$shortcodes = array_slice( $shortcodes, 2 );

foreach ( $shortcodes as $file ) {
	if ( ! $filepath = locate_template( 'includes/shortcodes/' . $file ) ) {
		trigger_error( sprintf( __( 'Error locating %s for inclusion', 'groundup' ), $file ), E_USER_ERROR );
	}
	require_once $filepath;
}

// Post Types
$post_types = scandir( get_stylesheet_directory()  . '/includes/post-types' );
// Remove '.' and '..' from array
$post_types = array_slice( $post_types, 2 );

foreach ( $post_types as $file ) {
	if ( ! $filepath = locate_template( 'includes/post-types/' . $file ) ) {
		trigger_error( sprintf( __( 'Error locating %s for inclusion', 'groundup' ), $file ), E_USER_ERROR );
	}
	require_once $filepath;
}

// WooCommerce
if ( class_exists( 'Woocommerce' ) ) {
	add_theme_support( 'woocommerce' );
	require_once('woocommerce/woo-functions.php');
}

// Add offcanvas wrappers
function upandup_wrapper_start() {
	echo '<div class="off-canvas-wrap" data-offcanvas><div class="inner-wrap">';
}
add_action( 'groundup_inside_body', 'upandup_wrapper_start' );

function upandup_wrapper_end() {
	echo '</div></div>';
}
add_action( 'wp_footer', 'upandup_wrapper_end', 999 );

// Top Bar walker
class Upandup_Topbar_Walker extends Walker_Nav_Menu {
	function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		$element->has_children = ! empty( $children_elements[ $element->ID ] );
		$element->classes[] = ( $element->current || $element->current_item_ancestor ) ? 'active' : '';
		$element->classes[] = ( $element->has_children && 1 !== $max_depth ) ? 'has-dropdown' : '';
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
	function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$item_html = '';
		parent::start_el( $item_html, $object, $depth, $args );
		// $output .= ( 0 == $depth ) ? '<li class="divider"></li>' : '';
		$classes = empty( $object->classes ) ? array() : (array) $object->classes;
		if ( in_array( 'label', $classes ) ) {
			// $output .= '<li class="divider"></li>';
			$item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '<label>$1</label>', $item_html );
		}
	if ( in_array( 'divider', $classes ) ) {
		$item_html = preg_replace( '/<a[^>]*>( .* )<\/a>/iU', '', $item_html );
	}
		$output .= $item_html;
	}
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "\n<ul class=\"sub-menu dropdown\">\n";
	}
}

class Upandup_Offcanvas_Walker extends Walker_Nav_Menu {
	function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		$element->has_children = ! empty( $children_elements[ $element->ID ] );
		$element->classes[] = ( $element->current || $element->current_item_ancestor ) ? 'active' : '';
		$element->classes[] = ( $element->has_children && 1 !== $max_depth ) ? 'has-submenu' : '';
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
	function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$item_html = '';
		parent::start_el( $item_html, $object, $depth, $args );
		$classes = empty( $object->classes ) ? array() : (array) $object->classes;
		if ( in_array( 'label', $classes ) ) {
			$item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '<label>$1</label>', $item_html );
		}
		$output .= $item_html;
	}
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '<ul class="right-submenu">';
		$output .= '<li class="back"><a href="#">' . __( 'Back', 'upandup' ) . '</a></li>';
	}
}

// change children to dropdown for wp_list_categories
function upandup_list_categories( $html ) {
	$html = preg_replace( '/\sclass=\'children\'/', ' class=\'dropdown\'', $html );
	return $html;
}
add_filter( 'wp_list_categories', 'upandup_list_categories' );

// change wp_list_categories css to include 'has-dropdown' when there are children
function upandup_category_css_class( $css_classes, $category, $depth, $args ) {
	// check if the category has any children and add the has-dropdown css_class
	$children = get_term_children( $category->term_id, $category->taxonomy );
	if ( $children ) {
		return array( $category->slug, 'has-dropdown' );
	} else {
		return array( $category->slug );
	}
}
add_filter( 'category_css_class', 'upandup_category_css_class', 1, 4 );

// Add offcanvas menu to #footer
function upandup_offcanvas_footer_menu () {
	echo '<a class="exit-off-canvas"></a>';
	echo '<aside class="right-off-canvas-menu">';
	echo '<ul class="off-canvas-list">';

	// Use 'mobile' menu or 'secondary' + 'primary' as a fallback
	$menu_object = groundup_get_menu_object( 'Mobile' );
	if ( $menu_object->count > 0 ) {
		wp_nav_menu( array(
			'container' => '',
			'items_wrap' => '%3$s',
			'menu' => $menu_object->term_id,
			'menu_class' => '',
			'walker'=> new Upandup_Offcanvas_Walker,
		) );
	} else {
		echo '<li><label class="off-canvas-label">Pages</label></li>';
		$menu_object =  groundup_get_menu_object( 'Primary' );
		if ( $menu_object->count > 0 ) {
			wp_nav_menu( array(
				'container' => '',
				'items_wrap' => '%3$s',
				'menu' => $menu_object->term_id,
				'menu_class' => '',
				'walker'=> new Upandup_Offcanvas_Walker,
			) );
		}
		$menu_object =  groundup_get_menu_object( 'Secondary' );
		echo '<li><label class="off-canvas-label">Functions</label></li>';
		// if ( $menu_object->count > 0 ) {
			wp_nav_menu( array(
				'container' => '',
				'items_wrap' => '%3$s',
				'menu' => $menu_object->term_id,
				'menu_class' => '',
				'walker'=> new Upandup_Offcanvas_Walker,
				'fallback_cb' => '',
			) );
		// }
	}
	echo '</ul>';
	echo '</aside>';
}
add_action( 'wp_footer', 'upandup_offcanvas_footer_menu' );

// Conditional Sidebar Ads
// Add custom ads to sidebar depending on current category
function upandup_before_widgets( $sidebar ) {
	if ( is_product_category( 'adults-lockets' ) ) { ?>
		<section class="widget ads">
			<a href="http://locketstudio.com" target="_blank">
				<img alt="ad" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/sidebar-lockets.jpg" />
			</a>
		</section>
	<?php }
}
add_action( 'groundup_before_widgets', 'upandup_before_widgets' );

// Add Favicons
function upandup_favicons() {
	$img_dir = get_stylesheet_directory_uri() . '/assets/img/'; ?>
	<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $img_dir; ?>apple-touch-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="<?php echo $img_dir; ?>apple-touch-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $img_dir; ?>apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="<?php echo $img_dir; ?>apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $img_dir; ?>apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="<?php echo $img_dir; ?>apple-touch-icon-120x120.png">
	<link rel="icon" type="image/png" href="<?php echo $img_dir; ?>favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="<?php echo $img_dir; ?>favicon-96x96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="<?php echo $img_dir; ?>favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="<?php echo $img_dir; ?>manifest.json">
	<meta name="apple-mobile-web-app-title" content="Marathon">
	<meta name="application-name" content="Marathon">
	<meta name="msapplication-TileColor" content="#686158">
	<meta name="theme-color" content="#686158">
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=EDGE" />
<?php }
add_action( 'wp_head', 'upandup_favicons' );

// Add Extra Logos to #footer
function upandup_extra_logos() {
	echo '<a href="' . get_term_link( 'children', 'product_cat' ) . '" class="logo kiddiekraft"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-kiddiekraft.png"></a>';
	echo '<a href="' . get_term_link( 'pre-teen', 'product_cat' ) . '" class="logo steppingstones"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-steppingstones.png"></a>';
	echo '<a href="https://locketstudio.com" target="_blank" class="logo locketstudio"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-locketstudio.png"></a>';
	echo '<a href="' . get_term_link( 'convertible-collection', 'product_cat' ) . '" class="logo lestage"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-lestage.png"></a>';
	echo '<a href="' . get_term_link( 'cape-cod-jewelry', 'product_cat' ) . '" class="logo cape-cod"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-cape-cod.png"></a>';
	echo '<a href="' . get_term_link( 'convertible-collection', 'product_cat' ) . '" class="logo convertible"><img src="' . get_stylesheet_directory_uri() . '/assets/img/logo-convertible.png"></a>';
}
add_action( 'wp_footer', 'upandup_extra_logos' );

// Remove default page_title for pages
function upandup_page_title( $page_title ) {
	if ( is_page() ) {
		$page_title = null;
	}
	return $page_title;
}
add_filter( 'page_title', 'upandup_page_title' );

// Add modernizr stop parent from deferring it
function upandup_modernizr() {
	wp_enqueue_script( 'modernizr', get_stylesheet_directory_uri() . '/assets/js/modernizr.js', null, null, false );
}
add_action( 'wp_enqueue_scripts', 'upandup_modernizr' );

function groundup_defer_script( $tag, $handle ) {
	if ( $handle != 'jquery' && $handle != 'modernizr' ) {
		$tag = str_replace(' src', ' defer="defer" src', $tag );
	}
	return $tag;
}

// IE shims
function upandup_ie_fix() {
	echo '<!--[if lt IE 9]>
  <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
  <script src="//s3.amazonaws.com/nwapi/nwmatcher/nwmatcher-1.2.5-min.js"></script>
  <script src="//html5base.googlecode.com/svn-history/r38/trunk/js/selectivizr-1.0.3b.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.1.0/respond.min.js"></script>
<![endif]-->';
}
add_action( 'wp_head', 'upandup_ie_fix' );

function upandup_ie() {
	global $wp_scripts;
	wp_enqueue_script( 'ie', get_stylesheet_directory_uri() . '/assets/js/ie.js', null, null, false );
	$wp_scripts->add_data( 'ie', 'conditional', 'IE' );
}
add_action( 'wp_enqueue_scripts', 'upandup_ie' );

/************************
 * Temporary
************************/
// Add popup once for users that have logged-in
// TODO: This should eventually be extracted into a separate post-type with settings
// TODO: This should eventually store a field in the user db to see if it has been seen
function upandup_tmp_popup() {
	// check if this has been shown before for the user
	$popup_ver = 'testing1';
	if( is_user_logged_in() && !current_user_can( 'subscriber' ) ) {
		$user_id = get_current_user_id();
		if ( $popup_ver !== get_user_meta( $user_id,  'popups', true ) )  {
			// get contents of popup
			$page = get_page_by_title( 'Popup' );
			if ( $page ) {
				$content = apply_filters( 'the_content', $page->post_content );

				// show the popup
				$modalNum = 'modal-' . substr(uniqid(), -4); //last 4 digits of uniqid will suffice

				$modal = '<div id="' . $modalNum . '" class="reveal-modal ' . $size . '" role="dialog" data-reveal>';
				// get content from posts
				$modal .= $content;
				$modal .= '<a href="#" style="button" class="close-reveal-modal">&times;</a>';
				$modal .= '</div>';

				$modalScript = '<script style="text/javascript">
				var popuptimer = setInterval(popuptime, 1000);
				function popuptime() {
					if (typeof jQuery("#' . $modalNum . '").foundation == "function") {
						jQuery("#' . $modalNum . '").foundation("reveal","open");
						clearInterval(popuptimer);
					}
				}
				</script>';

				echo $modal;
				echo $modalScript;

				// update user_meta to show that the modal has been seen
				update_usermeta( $user_id, 'popups', $popup_ver );
			}
		}
	}
}
add_action( 'wp_footer', 'upandup_tmp_popup' );

function upandup_subscriber_popup() {
	// check if this has been shown before for the user
	$popup_ver = 'subscriber';
	$user_id = get_current_user_id();
	if( is_user_logged_in() && current_user_can( 'subscriber' ) ) {
		if ( $popup_ver !== get_user_meta( $user_id, 'subscriberModal', true ) )  {
			// get contents of popup
			$page = get_page_by_title( 'Subscriber Popup' );
			if ( $page ) {
				$content = apply_filters( 'the_content', $page->post_content );

				// show the popup
				$modalNum = 'modal-' . substr(uniqid(), -4); //last 4 digits of uniqid will suffice

				$modal = '<div id="' . $modalNum . '" class="reveal-modal ' . $size . '" role="dialog" data-reveal>';
				// get content from posts
				$modal .= $content;
				$modal .= '<a href="#" style="button" class="close-reveal-modal">&times;</a>';
				$modal .= '</div>';

				$modalScript = '<script style="text/javascript">
				var popuptimer = setInterval(popuptime, 1000);
				function popuptime() {
					if (typeof jQuery("#' . $modalNum . '").foundation == "function") {
						jQuery("#' . $modalNum . '").foundation("reveal","open");
						clearInterval(popuptimer);
					}
				}
				</script>';

				echo $modal;
				echo $modalScript;

				// update user_meta to show that the modal has been seen
				update_usermeta( $user_id, 'subscriberModal', $popup_ver );
			}
		}
	}
}
add_action( 'wp_footer', 'upandup_subscriber_popup' );


// Change field description for log in
function upandup_login_form_defaults( $defaults ) {
	$defaults['label_username'] = 'Username or Email';
	return $defaults;
}
add_filter( 'login_form_defaults', 'upandup_login_form_defaults' );

// Change default login username label
// add_filter( 'gettext', 'upandup_login_label' );
// add_filter( 'ngettext', 'upandup_login_label' );
// function upandup_login_label( $translated ) {
//    $translated = str_ireplace( 'Username or Email Address', 'Account Number', $translated );
//    return $translated;
// }

// Add Login Warning
// TODO: This could be preventing error messages from showing on registration page when duplicate email is used.
function groundup_login_error_message( $error_message ) {
	global $errors;

	if ( array_key_exists( 'invalid_username', $errors->errors ) || array_key_exists( 'incorrect_password', $errors->errors ) ) {
		$error_message = '<strong>ERROR</strong>: Invalid username or password.
		<br/ ><br/ ><strong>Marathon is installing new security protocols. If you have not already, you will need to <a href="' . wp_registration_url() . '">register</a> for web access so we can verify you.</strong>
		<br/ ><br/ >Your account will be locked out after 10 unsuccessful attempts. <br />If you have already registered with the new security protocols and would like to reset your username or password please <a href="' . wp_lostpassword_url() . '"> click here</a>.';
	}

	return $error_message;
}

///////// New Registration Form Options
// Add fields
function upandup_register_form() {
	$first_name = ( ! empty( $_POST['first_name'] ) ) ? trim( $_POST['first_name'] ) : '';
	$last_name = ( ! empty( $_POST['last_name'] ) ) ? trim( $_POST['last_name'] ) : '';
	$account_number = ( ! empty( $_POST['account_number'] ) ) ? trim( $_POST['account_number'] ) : '';

	// add fields ?>
	<p><label for="first_name"><?php _e( 'First Name', 'upandup' ) ?><br/><input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr( wp_unslash( $first_name ) ); ?>" size="25" /></label></p>
	<p><label for="last_name"><?php _e( 'Last Name', 'upandup' ) ?><br/><input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr( wp_unslash( $last_name ) ); ?>" size="25" /></label></p>
	<p><label for="account_number"><?php _e( 'Account Number', 'upandup' ) ?><br/><input type="text" name="account_number" id="account_number" class="input" value="<?php echo esc_attr( wp_unslash( $account_number ) ); ?>" size="25" /></label></p>
	<p>If you do not know your account number please contact us at <a href="tel:+1<?php echo antispambot( '8004511515' ); ?>"><?php echo antispambot( '1-800-451-1515' ); ?></a></p>

<?php }
add_action( 'register_form', 'upandup_register_form' );

// Validate fields
function upandup_registration_errors( $errors, $sanitized_user_login, $user_email ) {
	if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
    $errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a first name.', 'upandup' ) );
  }
	if ( empty( $_POST['last_name'] ) || ! empty( $_POST['last_name'] ) && trim( $_POST['last_name'] ) == '' ) {
    $errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a last name.', 'upandup' ) );
  }
	if ( empty( $_POST['account_number'] ) || ! empty( $_POST['account_number'] ) && trim( $_POST['account_number'] ) == '' ) {
    $errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include an account number.', 'upandup' ) );
  }
  return $errors;
}
add_action( 'registration_errors', 'upandup_registration_errors', 5, 3 );

// save new fields
function upandup_user_register( $user_id ) {
	if ( ! empty( $_POST['first_name'] ) ) {
    update_user_meta( $user_id, 'first_name', trim( $_POST['first_name'] ) );
  }
	if ( ! empty( $_POST['last_name'] ) ) {
    update_user_meta( $user_id, 'last_name', trim( $_POST['last_name'] ) );
  }
	if ( ! empty( $_POST['account_number'] ) ) {
    update_user_meta( $user_id, 'account_number', trim( $_POST['account_number'] ) );
  }
}
add_action( 'user_register', 'upandup_user_register' );

// add fields to new user email
function upandup_wp_new_user_notification_email_admin( $wp_new_user_notification_email_admin, $user, $blogname ) {
	// $account_number = get_user_meta( $user->ID, 'account_number', true );
	// $user_info = get_userdata( $user->ID );
	// $buyer_name = $user_info->first_name . ' ' . $user_info->last_name;
	// $wp_new_user_notification_email_admin['message'] .= "\r\nAccount Number: " . $account_number;
	// $wp_new_user_notification_email_admin['message'] .= "\r\nBuyer Name: " . $buyer_name;
	// return $wp_new_user_notification_email_admin;
	return null;
}
add_filter( 'wp_new_user_notification_email_admin', 'upandup_wp_new_user_notification_email_admin', 5, 3 );

// Change Customer New Registration Email
function upandup_wp_new_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {
	global $wpdb;

	// Generate something random for a password reset key.
	$key = wp_generate_password( 20, false );
	/** This action is documented in wp-login.php */
	do_action( 'retrieve_password_key', $user->user_login, $key );
	// Now insert the key, hashed, into the DB.
	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}
	$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
	$switched_locale = switch_to_locale( get_user_locale( $user ) );

	$message  = sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
	$message .= __( 'To set your password please click on the link below. You will then be notified once your account has been set up and is ready to use.' ) . "\r\n\r\n";
	$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";

	$wp_new_user_notification_email['message'] = $message;

	return $wp_new_user_notification_email;
}
add_filter( 'wp_new_user_notification_email', 'upandup_wp_new_user_notification_email', 5, 3 );

// change wp_mail From address
function upandup_mail_from( $email ) {
  return "mail@marathon-co.com";
}
add_filter( 'wp_mail_from', 'upandup_mail_from' );


function upandup_mail_from_name( $name ) {
  return "Marathon Company";
}
add_filter( 'wp_mail_from_name', 'upandup_mail_from_name' );

// Change Register Message
function upandup_login_message( $message ) {
	if( FALSE !== strpos( $message, 'Register') ) {
		$message = '<p class="message register text-center">Apply for Web Access.</p>';
	} elseif( empty( $message ) ) {
		// $message = '<p class="message"><strong>Marathon is installing new security protocols. If you have not already, you will need to <a href="' . wp_registration_url() . '">register</a> for web access so we can verify you.</strong></p>';
	}
	return $message;
}
add_action( 'login_message', 'upandup_login_message' );

// promote subscribers with 'approved' user_meta to customers
// this is a workaround to REST API not allowing user roles
function promote_subscriber() {
	$user = wp_get_current_user();
	if ( !$user ) {
	  return;
	} else {
		$customer = in_array( 'customer', (array) $user->roles );
		$subscriber = in_array( 'subscriber', (array) $user->roles );
		$employee = in_array( 'employee', (array) $user->roles );
		$limited = in_array( 'customer_limited', (array) $user->roles );
		$limitedFlag = get_user_meta( $user->ID, 'limited', true );
		$employeeFlag = get_user_meta( $user->ID, 'employee', true );
		$customerFlag = get_user_meta( $user->ID, 'approved', true );
		if ( ( false != $customerFlag && '' != $customerFlag ) && true == $subscriber ) {
			// upgrade approved user to customer
			$user->remove_role( 'subscriber' );
			$user->add_role( 'customer' );

			$args = array(
				'post_type'   => 'shop_order',
				'post_status' => 'wc-completed',
				'numberposts' => -1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $user->ID,
			);
			$customer_orders = wc_get_orders( $args );
			if ( $customer_orders ) {
				foreach ( $customer_orders as $customer_order ) {
					// var_dump($customer_order->ID);
					$ship_date = get_post_meta( $customer_order->ID, 'ship_date', true );
					if ( $ship_date ) {
						$date = date_create_from_format( 'Y-m-d', $ship_date );
						$customer_order->set_date_created( $ship_date );
						$success = wp_update_post(
							array (
								'ID'            => $customer_order->ID, // ID of the post to update
								'post_date'     => $date->format( 'Y-m-d H:i:s' ),
								'post_date_gmt' => $date->format( 'Y-m-d H:i:s' ),
							)
						);
					}
				}
			}
		} elseif ( ( false != $employeeFlag && '' != $employeeFlag ) && true == $subscriber ) {
			// upgrade approved user to customer
			$user->remove_role( 'subscriber' );
			$user->add_role( 'employee' );
		} elseif ( ( false != $limitedFlag && '' != $limitedFlag ) && true == $subscriber ) {
			// upgrade approved user to customer
			$user->remove_role( 'subscriber' );
			$user->add_role( 'customer_limited' );
		}
	}
	if ( $customerFlag == false && true == $customer ) {
		$user->remove_role( 'customer' );
		$user->add_role( 'subscriber' );
	}
}
add_action( 'init', 'promote_subscriber' );

// remove zxcvbn password meter
function upandup_remove_zxcvbn() {
	wp_dequeue_script('password-strength-meter');
  wp_dequeue_script('user-profile');
  wp_deregister_script('user-profile');
	wp_dequeue_script('wc-password-strength-meter');

  $suffix = SCRIPT_DEBUG ? '' : '.min';
  wp_enqueue_script( 'user-profile', "/wp-admin/js/user-profile$suffix.js", array( 'jquery', 'wp-util' ), false, 1 );
}
add_action( 'login_enqueue_scripts', 'upandup_remove_zxcvbn' );
add_action( 'wp_enqueue_scripts', 'upandup_remove_zxcvbn' );

// add specific password requirements
add_action( 'validate_password_reset', 'validateComplexPassword', 10 );
add_action( 'user_profile_update_errors', 'validateComplexPassword', 10 );
add_action( 'woocommerce_save_account_details_errors', 'validateComplexPassword', 10 );

function validateComplexPassword( $errors ) {

	$password = ( isset( $_POST[ 'pass1' ] ) && trim( $_POST[ 'pass1' ] ) ) ? $_POST[ 'pass1' ] : null;
	if ( ! $password ) {
		$password = ( isset( $_POST[ 'password_1' ] ) && trim( $_POST[ 'password_1' ] ) ) ? $_POST[ 'password_1' ] : null;
	}

	// no password or already has password error
	if ( empty( $password ) || ( $errors->get_error_data( 'pass' ) ) ) {
		return $errors;
	}

	// validate
	if ( ! isStrongPassword( $password ) ) {
		$errors->add( 'pass', '<strong>ERROR</strong>: Your password must contain: <br />at least 6 characters<br />a letter A -> Z<br />a number 0 -> 9<br />a special character<br />[^£$%&*()}{@#~?><>,|=_+-].</li>' ); // your complex password error message
	}
	return $errors;
}

function isStrongPassword( $password ) {
	if ( strlen( $password ) < 6 ) {
		return false;
	} elseif ( ! preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $password) ) {
		return false;
	} elseif ( ! preg_match("#[a-zA-Z]+#", $password ) ) {
		return false;
	} elseif ( ! preg_match("#[0-9]+#", $password ) ) {
		return false;
	}	else {
		return true;
	}
}

// change password hint text:
function upandup_password_hint( $hint ) {
	$hint = 'To make your password stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).';
	return $hint;
}
add_filter( 'password_hint', 'upandup_password_hint' );

// send out email to admin after user resets password
function upandup_check_user_status( $user ) {
	if ( !$user ) {
	  return;
	} else {
		$verified = get_user_meta( $user->ID, 'verified', true );
		$subscriber = in_array( 'subscriber', (array) $user->roles );

		if ( true == $subscriber && ( false == $verified || '' == $verified ) && ( false == $approved || '' == $approved ) ) {
			// First Log In
			// send email out to admin about new user
			$account_number = get_user_meta( $user->ID, 'account_number', true );
			$user_info = get_userdata( $user->ID );
			$buyer_name = $user_info->first_name . ' ' . $user_info->last_name;

			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n";
			$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n";
			$message .= sprintf( __( 'Account Number: %s' ), $account_number ) . "\r\n";
			$message .= sprintf( __( 'Buyer Name: %s' ), $buyer_name ) . "\r\n";

			$wp_new_user_notification_email_admin = array(
				'to'      => get_option( 'admin_email' ),
				'subject' => __( 'New User Registration - Account ' . $account_number  ),
				'message' => $message,
				'headers' => '',
			);

			// DO NOT FILTER RESULTS BECAUSE WE ARE ADDING THE FILTER THAT NULLIFIES IT
			// $wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );

			wp_mail(
				$wp_new_user_notification_email_admin['to'],
				wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
				$wp_new_user_notification_email_admin['message'],
				$wp_new_user_notification_email_admin['headers']
			);

			add_user_meta( $user->ID, 'verified', true );
		}
	}
}
add_action( 'password_reset', 'upandup_check_user_status', 10, 1 );

// disable admin email of "user has changed password"
add_filter( 'wp_password_change_notification_email', '__return_false' );

// fix srcset ssl error
add_filter( 'wp_get_attachment_url', 'set_url_scheme' );
add_filter( 'wp_calculate_image_srcset', function($sources) {
 $filtered_sources = array();
 foreach($sources as $source_key=>$source) {
  $source['url'] = str_replace('http://','https://', $source['url']);
  $filtered_sources[$source_key] = $source;
 }
 return $filtered_sources;
});
