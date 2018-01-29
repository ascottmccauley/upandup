<?php
/**
 * Woo-Functions
 * Any custom functions related specifically to a WooCommerce setup
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/************************
 * Helpers
************************/
// wp post delete $(wp post list --post_type='shop_order' --format=ids)
// Extend a permalink to include the $_GET variable 'prev'='postID';
function upandup_get_permalink_prev( $url ) {
	global $wp_query;
	$prev_id = get_queried_object_id();
	if ( $prev_id ) {
		$params = array_merge($_GET, array( 'prev' => $prev_id ) );
		$query_string = http_build_query( $params );
	} else {
		$query_string = http_build_query( $_GET );
	}
	$permalink = $url . '?' . $query_string;
	return $permalink;
}

// return true if customer or admin is logged in
function upandup_woo_customer() {
	if( is_user_logged_in() && ! current_user_can( 'subscriber') ) {
		return true;
	} else {
		return false;
	}
}

// allow shop manager to edit theme options
$shop_manager = get_role( 'shop_manager' );
$shop_manager->add_cap( 'edit_theme_options' );

/************************
 * Cleanup
************************/
// Make shop private
// Redirect to login page if unaccessible page is tried
function upandup_shop_private() {
	if( false == upandup_woo_customer() ) {
		// if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		if ( is_woocommerce() || is_cart() || is_checkout() ) {
			// allow access to cape-cod-jewelry and convertible-collection products
			if ( ! has_term( 'cape-cod-jewelry', 'product_cat' ) && ! has_term(  'convertible-collection', 'product_cat' ) ) {
				$redirect = home_url() . $_SERVER["REQUEST_URI"];
				// wp_redirect( wp_login_url( $redirect ) );
				wp_redirect( home_url() );
				exit;
			}
		}
	}
	// change my-account to edit-account
	if ( is_page( get_option('woocommerce_myaccount_page_id') ) && ! is_wc_endpoint_url( 'edit-account') && ! is_wc_endpoint_url( 'orders') && ! is_wc_endpoint_url( 'view-order') ) {
		wp_redirect( wc_customer_edit_account_url() );
		die();
	}
}
add_action( 'template_redirect', 'upandup_shop_private' );

// add message to subscribers
function upandup_subscriber_pending_notice( $content ) {
	if( is_user_logged_in() && current_user_can( 'subscriber' ) ) {
		echo '<div class="woocommerce-message notice">Your account is still being reviewed. To expedite the process please contact customer service.</div>';
	}
}
add_action( 'groundup_before_post', 'upandup_subscriber_pending_notice' );

// Revert to non wc lostpassword form
remove_filter( 'lostpassword_url', 'wc_lostpassword_url', 10 );

// Change redirect for customers
function upandup_woocommerce_login_redirect( $redirect, $request, $user ) {
	// Get the first of all the roles assigned to the user
	$role = $user->roles[0];

	$dashboard = admin_url();
	$shop = wc_get_page_permalink( 'shop' );

	if( $role == 'administrator' || $role == 'shop_manager' ) {
		//Redirect administrators to the dashboard
		$redirect = $dashboard;
	} elseif ( $role == 'customer' || $role == 'customer_limited' ) {
		//Redirect wholesale customers to the "shop" page
		$redirect = $shop;
	} else {
		//Redirect any other role to home
		$redirect = home_url();
	}

	$redirect = $shop;

	return $redirect;
}
add_filter( 'login_redirect', 'upandup_woocommerce_login_redirect', 30, 3 );
// add_filter( 'woocommerce_login_redirect', 'upandup_woocommerce_login_redirect', 20, 2 );

// change all my-account links to the edit account page instead.
function woocommerce_get_my_account_page_permalink() {
	return wc_customer_edit_account_url();
}
add_filter( 'woocommerce_get_my_account_page_permalink', 'woocommerce_get_my_account_page_permalink' );

// Fix $_product->get_children() to show products ordered by sku number
function upandup_woocommerce_get_children( $children ) {
	$skus = [];
	foreach( $children as $child ) {
		$_product = wc_get_product( $child );
		array_push( $skus, $_product->get_sku() );
	}
	array_multisort( $skus, $children );
	return $children;
}
add_filter( 'woocommerce_product_get_children', 'upandup_woocommerce_get_children', 10, 1 );


// Show links to children products and MSRP for grouped products when not logged in:
function upandup_public_woocommerce_single_product_summary() {
	global $product, $post;

	$grouped_products = $product->get_children();

	$previous_post = $post;

	if ( $grouped_products ) { ?>
		<table cellspacing="0" class="group_table">
		<?php foreach ( $grouped_products as $grouped_product ) {
			$post_object = get_post( $grouped_product );
			$grouped_product = get_product( $grouped_product );
			setup_postdata( $post = $post_object ); ?>


				<tr id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
					<td class="label">
							<label for="product-<?php echo $grouped_product->get_id(); ?>">
								<?php echo '<a href="' . esc_url( apply_filters( 'woocommerce_grouped_product_list_link', get_permalink( $grouped_product->get_id() ), $grouped_product->get_id() ) ) . '">' . $grouped_product->get_name() . '</a>'; ?>
							</label>
						</td>
						<?php do_action( 'woocommerce_grouped_product_list_before_price', $grouped_product ); ?>
						<td class="price">
							<?php
								echo $grouped_product->get_price_html();
								echo wc_get_stock_html( $grouped_product );
							?>
						</td>
				</tr>

		<?php } ?>
		</table>
		<?php // Return data to original post.
		setup_postdata( $post = $previous_post );
	}
}

// hide pricing and buy buttons for non-logged in users
if ( ! upandup_woo_customer() ) {
	// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	add_action( 'woocommerce_single_product_summary', 'upandup_public_woocommerce_single_product_summary', 10 );
}

// Change template for search.php to archive-product
function upandup_search_template( $template ) {
	if ( is_search() ) {
		return wc_get_template_part( 'archive', 'product' );
	} else {
		return $template;
	}
}
add_filter( 'template_include', 'upandup_search_template' );

// change posts_per page for search results
function upandup_search_per_page( $limits ) {
	global $wp_query;
	if ( is_search() ) {
		$wp_query->query_vars['posts_per_page'] = 30;
		$wp_query->query_vars['post_type'] = 'product';
	}
	return $limits;
}
add_filter( 'post_limits', 'upandup_search_per_page' );

// Remove Default Woocommerce Styling
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

function upandup_woo_theme_setup() {
	remove_theme_support( 'wc-product-gallery-zoom' );
	remove_theme_support( 'wc-product-gallery-lightbox' );
	remove_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'upandup_woo_theme_setup' );

// Remove Default Woocommerce Image Sizes
function upandup_woo_remove_image_size() {
	$default_sizes = array( 'shop_thumbnail', 'shop_catalog', 'shop_single' );
	foreach ( $default_sizes as $size ) {
		remove_image_size( $size );
	}
}
add_action( 'init', 'upandup_woo_remove_image_size' );

// Remove WooCommerce Styles
function upandup_woo_remove_styles() {
	wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
}
add_action( 'wp_print_styles', 'upandup_woo_remove_styles', 100 );

// Remove WooCommerce js
function upandup_woo_remove_js() {
	wp_dequeue_script( 'prettyPhoto' );
	wp_dequeue_script( 'prettyPhoto-init' );
	wp_dequeue_script( 'fancybox' );
	wp_dequeue_script( 'enable-lightbox' );
}
add_action( 'wp_print_scripts', 'upandup_woo_remove_js', 100 );

// Conditionally remove WooCommerce js
function upandup_woo_dequeue_script() {
	if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
		wp_dequeue_script( 'wc_price_slider' );
		wp_dequeue_script( 'wc-single-product' );
		wp_dequeue_script( 'wc-add-to-cart' );
		wp_dequeue_script( 'wc-cart-fragments' );
		wp_dequeue_script( 'wc-checkout' );
		wp_dequeue_script( 'wc-add-to-cart-variation' );
		wp_dequeue_script( 'wc-single-product' );
		wp_dequeue_script( 'wc-cart' );
		wp_dequeue_script( 'wc-chosen' );
		wp_dequeue_script( 'woocommerce' );
		wp_dequeue_script( 'prettyPhoto' );
		wp_dequeue_script( 'jquery-blockui' );
		wp_dequeue_script( 'jquery-placeholder' );
		wp_dequeue_script( 'fancybox' );
		wp_dequeue_script( 'jqueryui' );
	}
}
add_action( 'wp_enqueue_scripts', 'upandup_woo_dequeue_script', 99);

// Create custom sidebar for WooCommerce pages
function upandup_woo_register_sidebars( $sidebars ) {
	$woo_sidebars = array( 'Shop', 'Products', 'Product', 'Cart' );
	return array_merge( $sidebars, $woo_sidebars );
}
add_filter( 'groundup_register_sidebars', 'upandup_woo_register_sidebars' );

// Add custom sidebars to the correct pages
function upandup_woo_sidebars( $sidebars ) {
	if ( is_search() ) {
		$sidebars = array();
	} elseif ( is_product() ) {
		$sidebars = array( 'Product' );
	} elseif ( woocommerce_products_will_display() ) {
		$sidebars = array( 'Products', 'Shop' );
	} elseif ( is_woocommerce() ) {
		$sidebars = array( 'Shop' );
	} elseif ( is_cart() || is_checkout() ) {
		$sidebars = array( 'Cart' );
	}
	return $sidebars;
}
add_filter( 'groundup_sidebars', 'upandup_woo_sidebars' );

// Remove wp-admin and admin-bar for customers and non-admins
update_option( 'woocommerce_lock_down_admin', 'yes' );

// replace date_created with order_date for now.
function upandup_woo_order_get_date_created( $value, $data ) {
	$meta = $data->meta_data;
	foreach( $meta as $key ) {
		if( $key->key == 'ship_date' ){
			$order_date = $key->value;
		}
	}
	// wp_die($order_date);
	$date = new WC_DateTime( $order_date, new DateTimeZone( 'UTC' ) );
	return $date;
}
// add_filter( 'woocommerce_order_get_date_created', 'upandup_woo_order_get_date_created', 10, 2 );

// replace order_number with custom marathon order number.
function upandup_woocommerce_order_number( $order_number, $order ) {
	$marathon_number = get_post_meta( $order_number, 'marathon_order_number', true);
	if( $marathon_number ) {
		return $marathon_number;
	}
	return $order_number . ' (temporary)';
}
add_filter( 'woocommerce_order_number', 'upandup_woocommerce_order_number', 10, 2 );

// // add order-date query to my-orders
function upandup_woo_woocommerce_my_account_my_orders_query( $args ) {
	// $args['meta_key'] = 'ship_date';
	// $args['orderby'] = 'meta_value';
	// $args['order'] = 'DESC';
	$args['post_status'] = 'completed';
	// $orders  = wc_get_orders( $args );
	// if ( $orders->total > 0 ) {
	// 	foreach ( $orders as $order_data ) {
	// 		$order = $order_data[0];
	// 		$ship_date = get_post_meta( $order->id, 'ship_date', true );
	// 		$order_date = get_post_meta( $order->id, 'order_date', true );
	// 		if ( $ship_date ) {
	// 			$order->set_date_created( $ship_date );
	// 			$date = date_create_from_format( 'Y-m-d', $ship_date );
	// 		} elseif( $order_date ) {
	// 			$order->set_date_created( $order_date );
	// 			$date = date_create_from_format( 'Y-m-d', $order_date );
	// 		}
  //
	// 		if( $date ) {
	// 			wp_update_post(
	// 				array (
	// 					'ID'            => $order->ID, // ID of the post to update
	// 					'post_date'     => $date->format( 'Y-m-d H:i:s' ),
	// 					'post_date_gmt' => $date->format( 'Y-m-d H:i:s' ),
	// 				)
	// 			);
	// 		}
	// 	}
	// }
	// $args['orderby']='date';
	return $args;
}
// TODO: change this to not run every time, and maybe in a different place.
add_filter( 'woocommerce_my_account_my_orders_query', 'upandup_woo_woocommerce_my_account_my_orders_query' );

/************************
 * text changes
************************/
function upandup_woo_gettext( $translated_text, $text, $domain ) {
	switch ( $translated_text ) {
		case 'Total' :
			$translated_text = __( 'Total MSRP', 'woocommerce' );
			break;
		case 'Price' :
			$translated_text = __( 'MSRP', 'woocommerce' );
			break;
		case 'You may be interested in&hellip;' :
			$translated_text = __( 'Related Products', 'woocommerce' );
			break;
		case 'Order again' :
			$translated_text = __( 'Duplicate Entire Order', 'woocommerce' );
			break;
		case 'Billing details' :
			$translated_text = __( '', 'woocommerce' );
			break;
	}
	return $translated_text;
}
add_filter( 'gettext', 'upandup_woo_gettext', 20, 3 );

/************************
 * global
************************/
// Create new Customer Role with 'download' permissions
function upandup_woocommerce_user_roles() {
	// admin
	$admin_role = get_role( 'administrator' );
	$admin_role->add_cap( 'download_images', true );

	// Add Customer (Trusted)
	$customer_role = get_role( 'customer' );
	$customer_cap = $customer_role->capabilities;
	$download_images = array( 'download_images' => true );
	array_push( $customer_cap, $download_images );
	add_role( 'customer_trusted', __( 'Customer (Trusted)' ), $customer_cap );

	$customer_trusted_role = get_role( 'customer_trusted' );
	$customer_trusted_role->add_cap( 'download_images', true );

	// Employee
	$employee_role = get_role( 'employee' );
	$employee_role->add_cap( 'customer_limited', true );
	// TODO: disabled for now
	// $employee_role->add_cap( 'download_images', true );
	$employee_role->remove_cap( 'download_images' );


	// Add Customer (Limited)
	$subscriber_role = get_role( 'subscriber' );
	$subscriber_cap = $subscriber_role->capabilities;
	add_role( 'customer_limited', __( 'Customer (Limited)' ), $subscriber_cap );

}
add_action( 'after_setup_theme', 'upandup_woocommerce_user_roles' );

// Add "awaiting shipment" Order Status
function upandup_woo_awating_shipment_order_status() {
	register_post_status( 'wc-awating-shipment', array(
		'label'		=> 'Awaiting Shipment',
		'public'	=> true,
		'show_in_admin_status_list' => true, // show count All (12) , Completed (9) , Awaiting shipment (2) ...
		'label_count'	=> _n_noop( 'Awaiting Shipment <span class="count">(%s)</span>', 'Awaiting shipment <span class="count">(%s)</span>' )
	) );
}
add_action( 'init', 'upandup_woo_awating_shipment_order_status' );

// Add new statuses to admin
function upandup_wc_add_order_statuses( $order_statuses ) {
	$new_order_statuses = array();
  foreach ( $order_statuses as $key => $status ) {
    $new_order_statuses[ $key ] = $status;
    if ( 'wc-processing' === $key ) {
      // $new_order_statuses['wc-awaiting-shipment'] = 'Awaiting Shipment';
			$new_order_statuses['wc-invoiced'] = 'Invoiced';
    }
  }

  return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'upandup_wc_add_order_statuses' );

// Change WooCommerce wrappers
function woocommerce_output_content_wrapper() {
	echo '<main id="main" role="main">';
}
function woocommerce_output_content_wrapper_end() {
	echo '</main>';
}

// Move Breadcrumbs
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
// add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 5, 0 );

// Add login/account and mini-cart to secondary-navbar and product categories to primary-navbar
function upandup_woo_add_to_nav( $items, $args ) {

	$menu_object = wp_get_nav_menu_object( $args->menu );

	if ( $menu_object->slug == 'primary' ) {
		if ( ! is_user_logged_in() ) {
			$items .= '<li class="login"><a href="' . wp_login_url() . '">Log In</a></li>';
		}
		elseif ( upandup_woo_customer() ) {
			$args = array(
				'taxonomy' => 'product_cat',
				'order' => 'ASC',
				'orderby' => 'name',
				'show_count' => false,
				'hierarchical' => true,
				'title_li' => '',
				'hide_empty' => true,
				'show_option_none' => '',
				'echo' => 0,
			);
			$categories = wp_list_categories( $args );
			$shop_link = '<a href="' . get_permalink( wc_get_page_id( 'shop' ) ) . '">Products</a>';
			$active = is_woocommerce() ? ' active': '';
			$items = '<li class="has-dropdown' . $active . '">' . $shop_link . '<ul class="dropdown">' . $categories . '</ul></li>' . $items;
		}
	}

	if ( $menu_object->slug == 'secondary' ) {
		$menu_object->count = $menu_object->count + 2;
		if ( upandup_woo_customer() ) {
			// Add Product Search and Orders
			$items .= '<li class="search has-form">' . get_product_search_form( $echo = false ) . '</li>';
			if ( ! current_user_can( 'customer_limited' ) ) {
				$orders_active = is_wc_endpoint_url( 'orders') ? ' active': '';
				$items .= '<li class="orders' . $orders_active . '"><a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' ) . '">Shipped Orders</a></li>';
			}
		}
		if ( is_user_logged_in() ) {
			// Add Orders/Account/Logout Link
			$account_active = is_wc_endpoint_url( 'edit-account') ? ' active': '';
			$items .= '<li class="account' . $account_active . '"><a href="' . wc_customer_edit_account_url() . '">Account</a></li>';
			$items .= '<li class="logout"><a href="' . wp_logout_url() . '">Log Out</a></li>';
		}
		if ( upandup_woo_customer() ) {
			// Add Minicart Link
			$cart_quantity = sizeof( WC()->cart->get_cart() );
			if ( $cart_quantity > 0 ) {
				$cart_subtotal = WC()->cart->get_cart_subtotal();
				$checkout_link = WC()->cart->get_cart_url();
				$items .= '<li class="cart"><a class="cart-contents" href="' . $checkout_link . '" title="' .  __( 'View your shopping cart' ) . '"><i class="icon-shopping-cart"></i> ' . $cart_quantity . ' items</a></li>';
			} else {
				$items .= '<li class="cart"><a class="cart-contents empty"></a></li>';
			}
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_items', 'upandup_woo_add_to_nav', 8, 2 );

// Ajaxify mini-cart
function woocommerce_header_add_to_cart_fragment( $fragments ) {
	ob_start();
	$cart_quantity = sizeof( WC()->cart->get_cart() );
	if ( $cart_quantity > 0 ) {
		$cart_subtotal = WC()->cart->get_cart_subtotal();
		$checkout_link = WC()->cart->get_cart_url();
		echo '<a class="cart-contents" href="' . $checkout_link . '" title="' .  __( 'View your shopping cart' ) . '"><i class="icon-shopping-cart"></i> ' . $cart_quantity . ' items</a>';
	} else {
		echo '<a class="cart-contents empty"></a>';
	}
	$fragments['a.cart-contents'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );

// Change "Home" in breadcrumbs to the "Shop" page.
function upandup_woo_breadcrumb_home_url() {
	$shop_page_id = wc_get_page_id( 'shop' );
	$shop_page    = get_post( $shop_page_id );
	return get_permalink( $shop_page );
}
add_filter( 'woocommerce_breadcrumb_home_url', 'upandup_woo_breadcrumb_home_url' );

// Style breadcrumbs to match zurb foundation
function upandup_woo_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter'] = '';
	$defaults['before'] = '<li>';
	$defaults['after'] = '</li>';
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'upandup_woo_breadcrumb_defaults' );

// Replace placeholder image with a search for the correct `sku.jpg` image.
// If no image is found
function upandup_woo_img_url( $size = 'thumbnail', $_product = '', $return = 'string' ) {
	global $post;
	global $product;

	if ( $_product == '' ) {
		$_product = $product;
	} elseif ( is_int( $_product ) ) {
		// try getting product by id
		$_product = wc_get_product( $_product );
	} elseif ( is_string( $_product ) ) {
		$sku = $_product;
	}
	if ( !$_product ) {
		return '';
	}

	if ( $size == 'full' || $size == 'large' ) {
		$img_url = wp_get_attachment_url( get_post_thumbnail_id() );
	} else {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $size );
		if ( ! empty( $img ) ) {
			$img_url = $img[0];
		}
	}

	// check /media/products/size/sku.jpg
	if ( empty( $img_url ) ) {
		// scan media folder for sku.jpg
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['path'];
		$upload_url = set_url_scheme( $upload_dir['url'] );
		$sku = $sku ? $sku : $_product->get_sku();
		// special case: if sku ends in "n" but not "-n", grab parent image (without "n");
		if ( substr( $sku,  -1 ) == 'n' && substr( $sku,  -2 ) != '-n' ) {
			// remove trailing 'n'
			$sku = rtrim( $sku, 'n' );
		}

		// Change naming conventions to 'large' and 'thumb'
		if ( $size == 'full' ) {
      $size = 'large';
		} elseif ( $size == 'thumbnail' ) {
			$size = 'thumb';
		}

		if ( file_exists( $upload_path . '/products/' . $size . '/' . $sku . '.jpg' ) ) {
				$img_url = $upload_url . '/products/' . $size . '/' . $sku . '.jpg';
		}
		elseif ( $_product->get_children() ) {
			// Check to see if this is a grouped product and load the first child image instead!
		 $children = $_product->get_children();
			$firstChild = wc_get_product( $children[0] );
			if ( $firstChild ) {
				$img_url = upandup_woo_img_url( $size, $children[0] );
			}
		} else {
			// load placeholder image
			$img_url = get_stylesheet_directory_uri() . '/assets/img/placeholder.jpg';
		}
	}
	return set_url_scheme( $img_url );
}

// shortcode to get products published in the last 60 days
// Use: [recent_products] [recent_products number=5 days=7]
function show_recent_products( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'number'  => -1,
		'days' => null,
		), $atts ) );

	// set default in case no variables included.
	if ($number == -1 && $days == null) {
		$number = 8;
	}
	// get the date range
	date_default_timezone_set('America/Chicago'); // CDT
	$current_date = date('Y-m-d');

	// create new WC loop with additional constraints
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => $number,
		'order' => 'DESC',
		'orderby' => 'date',
		'meta_query' => array(
    	array(
        'key'       => '_visibility',
        'value'     => 'visible',
        'compare'   => '=',
    	)
		)
	);

	// only set date query if number of days is included
	if( $days != null ){
		$days_ago = date( 'Y-m-d', strtotime( '-' . $days . ' days', strtotime( $current_date ) ) );
		$dateQuery = array('date_query' => array( 'after'=> $days_ago ));
		$args['date_query'] = $dateQuery;
	}

	ob_start();
	$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) {
			woocommerce_product_loop_start();
			while ( $loop->have_posts() ) : $loop->the_post();
				wc_get_template_part( 'content', 'product' );
			endwhile;
			woocommerce_product_loop_end();

		}
		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
}
add_shortcode( 'show_recent_products', 'show_recent_products');

/************************
 * shop page
************************/
// Products per page
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 35;' ), 20 );
// Remove Title from Store Front
add_filter( 'woocommerce_show_page_title', function() { return false; } );
// Remove product count after categories
add_filter( 'woocommerce_subcategory_count_html', function() { return false; } );

// Remove Result Count
remove_action( 'woocommerce_before_shop_loop','woocommerce_result_count', 20);
// Remove Catalog Ordering
remove_action( 'woocommerce_before_shop_loop','woocommerce_catalog_ordering', 30);

// Replace Category Thumbnail
remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
add_action( 'woocommerce_before_subcategory_title', 'upandup_woo_subcategory_thumbnail', 10 );

/**
 * woocommerce_subcategory_thumbnail
 *
 * rewrite the category thumbnail search to look for a category thumbnail or else return a random product thumbnail.
 * TODO: add back in the random product thumbnail feature
**/
if ( ! function_exists( 'upandup_woo_subcategory_thumbnail' ) ) {
	function upandup_woo_subcategory_thumbnail( $category ) {
		$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );
		// Get uploaded thumbnail
		if ( $thumbnail_id ) {
			if ( wp_is_mobile() ) {
				$image = wp_get_attachment_image_src( $thumbnail_id, 'small' );
			} else {
				$image = wp_get_attachment_image_src( $thumbnail_id, 'medium' );
			}
			$image = $image[0];
			// Prevent esc_url from breaking spaces in urls for image embeds
			// Ref: http://core.trac.wordpress.org/ticket/23605
			$image = str_replace( ' ', '%20', $image );
			echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '">';
		} else {
			$args = array(
				'orderby' => 'rand',
				'post_status' => 'publish',
				'post_type' => 'product',
				'numberposts' => 1,
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field' => 'slug',
						'terms' => $category->slug,
					)
				),
			);
			$posts = get_posts( $args );
			if ( $posts ) {
				foreach( $posts as $post ) {
					$image_url = upandup_woo_img_url( 'thumb', $post->ID );
					if ( ! empty ( $image_url ) ) {
						echo '<img src="' . $image_url . '" alt="' . esc_attr( $category->name ) . '">';
					}
				}
			}
		}
	}
}

/************************
 * products
************************/
// Remove *Sale*
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
// Replace thumbnail function
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
// Remove Rating
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
// Remove Price
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

// Add thumbnail first with alternate images
function upandup_woo_template_loop_product_thumbnail() {
	global $product;
	$upload_dir = wp_upload_dir();
	$upload_path = $upload_dir['path'];
	$upload_url = set_url_scheme( $upload_dir['url'] );
	$sku = $product->get_sku();

	$img_url = upandup_woo_img_url( 'thumbnail' );

	if ( ! empty( $img_url ) ) {
		$imagesArray = array( set_url_scheme( $img_url ) );
		foreach( glob( $upload_path . '/products/thumb/' . $sku . '-[a-z][a-z]*.jpg' ) as $img_path ) {
			$thumbnail = str_replace( $upload_path, $upload_url, $img_path );
			array_push( $imagesArray, $thumbnail );
		}

		echo '<div data-images="' . implode( " ", $imagesArray ) . '" href="' . upandup_get_permalink_prev( get_the_permalink() ) . '" class="square-thumb archive-thumbnail" style="background-image: url(\'' . $img_url . '\');"></div>';
	} else {
		echo '<a href="' . upandup_get_permalink_prev( get_the_permalink() ) . '" class="square-thumb placeholder" style="background: white;"></a>';
	}
}
add_action( 'woocommerce_before_shop_loop_item', 'upandup_woo_template_loop_product_thumbnail', 10 );

// Add product info wrapper
function upandup_before_shop_loop_item() {
	echo '<div class="product-info">';
}
add_action( 'woocommerce_before_shop_loop_item', 'upandup_before_shop_loop_item', 20 );

function upandup_after_shop_loop_item() {
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'upandup_after_shop_loop_item', 5 );

// if product doesn't have a price, hide the link, otherwise remove .button class from "BUY" link and wrap it in an <h3>
function upandup_woo_loop_add_to_cart_link( $add_to_cart_text, $product ) {
	if ( $product->get_price() != '' ) {
	  $add_to_cart_text = str_replace( '">', '"><h3 class="woocommerce-loop-product__buy">', $add_to_cart_text );
		$add_to_cart_text = str_replace( '</a>', '</h3></a>', $add_to_cart_text );
		$add_to_cart_text = str_replace( '"button ', '"', $add_to_cart_text );
	} else {
		$add_to_cart_text = '';
	}
	return $add_to_cart_text;
};
add_filter( 'woocommerce_loop_add_to_cart_link', 'upandup_woo_loop_add_to_cart_link', 10, 2 );

// Add parent category to body class
// Add 'parent' class if only 1 category exists
function upandup_woo_body_class( $classes ) {
	global $wp_query;
	global $post;

	if ( is_product() ) {
		$categories = get_the_terms( $post->ID, 'product_cat' );
		if ( is_array( $categories ) && ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				if ( $category->parent == 0 ) {
					$classes[] = $category->slug;
				}
			}
		}
	}

	if ( is_product_category() ) {
		$current_category = $wp_query->get_queried_object();
		// add 'parent' class for top-level categories
		if ( $current_category->parent == 0 ) {
			if ( ! woocommerce_products_will_display() ) {
				$classes[] = 'parent';
			}
			$classes[] = $current_category->slug;
		} else {
			$parent = get_term_by( 'term_taxonomy_id', $current_category->parent, $current_category->taxonomy );
			$classes[] = $parent->slug;
		}
	}

	if ( is_shop() ) {
		$classes[] = 'shop';
	}

	return $classes;
}
add_filter( 'body_class', 'upandup_woo_body_class' );

// Add topmost category to html class
// Add 'parent' class if only 1 category exists
function upandup_woo_html_class( $output ) {
	global $wp_query;
	global $post;

	$classes = array();

	if ( is_product() ) {
		$categories = get_the_terms( $post->ID, 'product_cat' );
		if ( is_array( $categories ) && ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				if ( $category->parent == 0 ) {
					$output .= 'class="' . $category->slug .'"';
				}
			}
		}
	}

	if ( is_product_category() ) {
		$current_category = $wp_query->get_queried_object();
		if ( $current_category->parent == 0 && ! woocommerce_products_will_display() ) {
			$output .= 'class="parent ' . $current_category->slug .'"';
		} else {
			$parent = get_term_by( 'term_taxonomy_id', $current_category->parent, $current_category->taxonomy );
			$output .= 'class="' . $parent->slug . '"';
		}
	}

	return $output;
}
add_filter( 'language_attributes', 'upandup_woo_html_class' );

// add product-cat to classlist
function upandup_woo_product_cat_class( $classes, $class, $category ) {
	$classes[] = $category->slug;
	return $classes;
}
add_filter( 'product_cat_class', 'upandup_woo_product_cat_class', 10, 4 );


/************************
 * single-product
************************/
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 7 );

add_action( 'woocommerce_after_single_product_summary', 'woocommerce_show_product_images', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_product_tabs', 'woocommerce_product_reviews_tab', 30 );
remove_action( 'woocommerce_product_tab_panels', 'woocommerce_product_reviews_panel', 30 );

// remove "in stock" notification
function upandup_woo_stock_html(){
	return null;
}
add_action( 'woocommerce_get_stock_html', 'upandup_woo_stock_html' );

// add MSRP before price
function upandup_woocommerce_get_price_html( $price, $product ) {
	if( ! $product->is_type( 'grouped' ) ){
		$price = '<small>MSRP:</small> ' . $price;
	} else {
		$price = '';
	}
	return $price;
}
add_filter( 'woocommerce_get_price_html', 'upandup_woocommerce_get_price_html', 10, 2 );

// Change grouped products to order alphanumerically
function upandup_woo_grouped_children_args( $args ) {
	$args['orderby'] = 'title ';
	return $args;
}
add_filter( 'woocommerce_grouped_children_args', 'upandup_woo_grouped_children_args' );

// If there is only 1 image (and it is 'featured') do not include gallery
function upandup_woo_check_images() {
	global $product;
	$attachment_ids = $product->get_gallery_image_ids();
	if ( count( $attachment_ids ) < 1 ) {
		remove_action( 'woocommerce_before_single_product_summary','woocommerce_show_product_images', 20 );
	} elseif ( count( $attachment_ids ) == 1 && has_post_thumbnail() ) {
		// Check to see if only image in gallery is the featured thumbnail
		if ( $attachment_ids[0] == get_post_thumbnail_id() ) {
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
		}
	}
}
add_action( 'woocommerce_before_single_product_summary', 'upandup_woo_check_images', 5 );

// Add Product Attributes, Size, Weight and Tags
function upandup_woo_product_attributes() {
	global $product;
	$attributeList = '';

	if ( $product->has_weight() ) {
		$attributeList .= '<li>' . $product->get_weight() . ' ' . esc_attr( get_option( 'woocommerce_weight_unit' ) ) . '</li>';
	}
	if ( $product->has_dimensions() ) {
		$unit = get_option( 'woocommerce_dimension_unit' );
		$attributeList .= $product->get_length() ? '<li>Length: <strong>' . $product->get_length() . $unit . '</strong></li>' : '';
		$attributeList .= $product->get_width() ? '<li>Width: <strong>' . $product->get_width() . $unit . '</strong></li>' : '';
		$attributeList .= $product->get_height() ? '<li>Height: <strong>' . $product->get_height() . $unit . '</strong></li>' : '';
	}

	// Get Attributes
	$attributes = $product->get_attributes();
	if ( ! empty( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ($attribute['is_visible']) {
				if ( $attribute['is_taxonomy'] ) {
					$values = wc_get_product_terms( $product->get_id(), $attribute['name'], array( 'fields' => 'names' ) );
				}else {
					// Convert pipes to commas and display values
					$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
				}
				foreach ( $values as $value ) {
					$attributeName = str_replace( 'pa_' , '', $attribute['name'] );
					$attributeName = str_replace( '_' , ' ', $attributeName );
					$attributeName = ucwords( $attributeName );
					$attributeList .= '<li>' . $attributeName . ': <strong>' . $value . '</strong></li>';
				}
			}
		}
	}
	// Add Tags
	$attributeList .= wc_get_product_tag_list( $product->get_id(), ', ', '<li>Theme: ', '</li>' );

	if ( $attributeList != '' ) {
		echo '<h4>Attributes</h4>';
		echo '<ul class="product-attributes column-2">';
		echo $attributeList;
		echo '</ul>';
	}
}
add_action( 'woocommerce_single_product_summary', 'upandup_woo_product_attributes', 40 );

// Add to Cart Text
function upandup_woo_add_to_cart_text() {
	global $product;

	if( current_user_can( 'employee' ) ) {
		return 'Add Image';
	}
	$product_type = $product->get_type();
	switch ( $product_type ) {
		case 'external':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'grouped':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'simple':
			return __( 'Buy', 'woocommerce' );
			break;
		case 'variable':
			return __( 'Select Options', 'woocommerce' );
			break;
		default:
			return __( 'More Info', 'woocommerce' );
	}
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'upandup_woo_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'upandup_woo_add_to_cart_text' );

// change 'add to cart' button to 'view cart' if it's already in the cart
function upandup_woocommerce_loop_add_to_cart_link( $link, $product ) {
	// get id from link, and lookup if it's in the cart, and then change link to view cart
	global $woocommerce;

	foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
		$_product = $values['data'];

		if( $product->id == $_product->id ) {
			$link = '<a href="' . $woocommerce->cart->get_cart_url() . '" class="added_to_cart wc-forward" title="View Cart">View Cart</a>';
			break;
		}
	}

	return $link;
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'upandup_woocommerce_loop_add_to_cart_link', 5, 2 );

// Product Description
function upandup_woo_product_description() {
	global $post;
	if ( ! empty( $post->post_content ) ) {
		echo '<h4>Description</h4>';
		echo '<section class="description">';
		the_content();
		echo '</section>';
	}
}
add_action( 'woocommerce_single_product_summary', 'upandup_woo_product_description', 50 );

// Change Upsell Text - filter added to theme template /single-product/up-sells.php
function upandup_woo_upsell_text( $text ) {
	return '<h4>Similar Products</h4>';
}
add_filter( 'upandup_woo_upsell_text', 'upandup_woo_upsell_text' );

// add cross sells to the single product page.
function upandup_woo_list_cross_sells( $limit = 2, $columns = 2, $orderby = 'rand', $order = 'desc' ) {
	global $product;
	// Get visble cross sells then sort them at random.
	$cross_sells = array_map( 'wc_get_product', $product->get_cross_sell_ids() );
	// $cross_sells = array_map( 'wc_get_product', get_post_meta( get_the_ID(), '_crosssell_ids' ) );
	// Handle orderby and limit results.
	$orderby     = apply_filters( 'woocommerce_cross_sells_orderby', $orderby );
	// $cross_sells = wc_products_array_orderby( $cross_sells, $orderby, $order );
	$limit       = apply_filters( 'woocommerce_cross_sells_total', $limit );
	// $cross_sells = $limit > 0 ? array_slice( $cross_sells, 0, $limit ) : $cross_sells;

	if( count( $cross_sells ) > 0 && $cross_sells[0] !== false ) {
		wc_get_template( 'cart/cross-sells.php', array(
			'cross_sells' => $cross_sells,
			// Not used now, but used in previous version of up-sells.php.
			'posts_per_page' => $limit,
			'orderby'			 => $orderby,
			'columns'			 => $columns,
		) );
	}
}
add_action( 'woocommerce_after_single_product_summary', 'upandup_woo_list_cross_sells', 20 );

/************************
 * cart page
************************/
// Add back permalink for children products and -N products (hidden by default because they aren't $product->is_visible())
function upandup_woo_woocommerce_cart_item_permalink( $permalink, $cart_item, $cart_item_key ) {
	if( $permalink == '' ) {
		// see if product is a child product and get parent product_permalink
		$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$permalink = $_product->get_permalink();
	}
	return $permalink;
}
add_filter( 'woocommerce_cart_item_permalink', 'upandup_woo_woocommerce_cart_item_permalink', 10, 3 );

// Remove Cart Totals
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals' );

// Add the checkout link
add_action( 'woocommerce_cart_actions', 'woocommerce_button_proceed_to_checkout' );

// Change thumbnail
function upandup_woocommerce_cart_item_thumbnail( $image, $cart_item, $cart_item_key ) {
	$image_url = upandup_woo_img_url( 'thumbnail', $cart_item['product_id'] );
	if ( $image_url != '' ) {
		$thumbnail = '<img src="' . $image_url . '">';
	} else {
		$thumbnail = '';
	}
	return $thumbnail;
}
add_filter( 'woocommerce_cart_item_thumbnail', 'upandup_woocommerce_cart_item_thumbnail', 10, 3 );

// add 'Empty Cart' button
function upandup_woocommerce_cart_empty_cart() {
	$button = '<a class="button warning empty-cart-button" href="' . WC()->cart->get_cart_url() . '?empty-cart">' . __( 'Empty Cart', 'woocommerce' ) . '</a>';
	echo $button;
}
add_action( 'woocommerce_cart_actions', 'upandup_woocommerce_cart_empty_cart', 10 );

// Add 'Empty Cart' check
function upandup_woo_empty_cart() {
	global $woocommerce;

	if ( isset( $_GET['empty-cart'] ) ) {
		$woocommerce->cart->empty_cart();
	}
}
add_action( 'init', 'upandup_woo_empty_cart' );

// Add download images button to cart page
function upandup_woocommerce_cart_download_images() {
	if( current_user_can( 'download_images' ) ) {
		$downloads = [];
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['path'];
		$upload_url = set_url_scheme( $upload_dir['url'] );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			array_push( $downloads, $_product->get_sku() );
		}
		if( $downloads != [] ) {
			echo '<button class="secondary downloads downloadImage" data-files="' . implode( ' ', $downloads ) . '">Download All Images</button>';
		}
	}
}
add_action( 'woocommerce_cart_actions', 'upandup_woocommerce_cart_download_images', 15 );

/************************
 * checkout page
************************/
// Remove Billing fields
function upandup_woo_checkout_fields( $fields ) {
  unset($fields['billing']['billing_first_name']);
  unset($fields['billing']['billing_last_name']);
  unset($fields['billing']['billing_company']);
  unset($fields['billing']['billing_address_1']);
  unset($fields['billing']['billing_address_2']);
  unset($fields['billing']['billing_city']);
  unset($fields['billing']['billing_postcode']);
  unset($fields['billing']['billing_country']);
  unset($fields['billing']['billing_state']);
  unset($fields['billing']['billing_phone']);
  unset($fields['billing']['billing_address_2']);
  unset($fields['billing']['billing_postcode']);
  unset($fields['billing']['billing_company']);
  unset($fields['billing']['billing_last_name']);
  unset($fields['billing']['billing_email']);
  unset($fields['billing']['billing_city']);

	unset($fields['shipping']['shipping_first_name']);
	unset($fields['shipping']['shipping_last_name']);
	unset($fields['shipping']['shipping_company']);
	unset($fields['shipping']['shipping_address_1']);
	unset($fields['shipping']['shipping_address_2']);
	unset($fields['shipping']['shipping_city']);
	unset($fields['shipping']['shipping_postcode']);
	unset($fields['shipping']['shipping_country']);
	unset($fields['shipping']['shipping_state']);
	unset($fields['shipping']['shipping_phone']);
	unset($fields['shipping']['shipping_address_2']);
	unset($fields['shipping']['shipping_postcode']);
	unset($fields['shipping']['shipping_company']);
	unset($fields['shipping']['shipping_last_name']);
	unset($fields['shipping']['shipping_email']);
	unset($fields['shipping']['shipping_city']);

	$fields['order']['marathon_ship_rate'] = array(
		'label' => __('Shipping Rate', 'woocommerce'), // Add custom field label
    'required' => true, // if field is required or not
    'clear' => false, // add clear or not
    'type' => 'select', // add field type
    'class' => array('marathon_ship_rate'),   // add class name
		'options' => array('Ground', '3 Day', '2 Day', 'Next Day')
	);
	$fields['order']['customer_buyer_name'] = array(
    'label' => __('Buyer Name', 'woocommerce'), // Add custom field label
    'placeholder' => _x('Your Name (required)', 'placeholder', 'woocommerce'), // Add custom field placeholder
    'required' => true, // if field is required or not
    'clear' => false, // add clear or not
    'type' => 'text', // add field type
    'class' => array('customer_buyer_name') // add class name
  );

	$fields['order']['customer_PO'] = array(
    'label' => __('Customer PO Number', 'woocommerce'), // Add custom field label
    'placeholder' => _x('optional', 'placeholder', 'woocommerce'), // Add custom field placeholder
    'required' => false, // if field is required or not
    'clear' => false, // add clear or not
    'type' => 'text', // add field type
    'class' => array('customer_PO')   // add class name
  );

	// move order comments to end
	$order_comments = $fields['order']['order_comments'];
	unset($fields['order']['order_comments']);
	$fields['order']['order_comments'] = $order_comments;

  return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'upandup_woo_checkout_fields' );


// Save Customer PO Number and Customer Name and shipping rate
function upandup_woocommerce_checkout_update_order_meta( $order_id ) {
	$rate = 'Ground';

	if ( ! empty( $_POST['marathon_ship_rate'] ) ) {
		switch ( intval( $_POST['marathon_ship_rate'] ) ) {
			case 3:
				$rate = 'Next Day';
				break;
			case 2:
				$rate = '2 Day';
				break;
			case 1:
				$rate = '3 Day';
				break;
			case 0:
				$rate = 'Ground';
				break;
			default:
				$rate = 'Error';
				break;
		}
  }
	update_post_meta( $order_id, 'marathon_ship_rate', $rate );

	if ( ! empty( $_POST['customer_PO'] ) ) {
    update_post_meta( $order_id, 'customer_po', sanitize_text_field( $_POST['customer_PO'] ) );
  }
	if ( ! empty( $_POST['customer_buyer_name'] ) ) {
    update_post_meta( $order_id, 'customer_buyer_name', sanitize_text_field( $_POST['customer_buyer_name'] ) );
  }
}
add_action( 'woocommerce_checkout_update_order_meta', 'upandup_woocommerce_checkout_update_order_meta' );

// Display order meta on Edit Page
function upandup_woocommerce_admin_order_data_after_billing_address( $order ){
	echo '<p><strong>'.__('Shipping Rate').':</strong> ' . get_post_meta( $order->id, 'marathon_ship_rate', true ) . '</p>';
	echo '<p><strong>'.__('Customer PO').':</strong> ' . get_post_meta( $order->id, 'customer_po', true ) . '</p>';
	echo '<p><strong>'.__('Buyer Name').':</strong> ' . get_post_meta( $order->id, 'customer_buyer_name', true ) . '</p>';
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'upandup_woocommerce_admin_order_data_after_billing_address', 10, 1 );

// Show shipping address on checkout page:
function upandup_woocommerce_after_checkout_billing_form() {
	$user_id = get_current_user_id();
	$user = get_userdata( $user_id );

	if ( ! $user )
	  return;

  $shipping_first_name = get_user_meta( $user_id, 'shipping_first_name', true );
  $shipping_last_name = get_user_meta( $user_id, 'shipping_last_name', true );
  $shipping_company = get_user_meta( $user_id, 'shipping_company', true );
  $shipping_address_1 = get_user_meta( $user_id, 'shipping_address_1', true );
  $shipping_address_2 = get_user_meta( $user_id, 'shipping_address_2', true );
  $shipping_city = get_user_meta( $user_id, 'shipping_city', true );
  $shipping_state = get_user_meta( $user_id, 'shipping_state', true );
  $shipping_postcode = get_user_meta( $user_id, 'shipping_postcode', true );
  $shipping_country = get_user_meta( $user_id, 'shipping_country', true );

	$address = $shipping_first_name . ' ' . $shipping_last_name . '</br>';
	$address .= $shipping_company ? $shipping_company . '</br>' : '';
	$address .= $shipping_address_1 . '</br>';
	$address .= $shipping_address_2 ? $shipping_address_2 . '</br>' : '';
	$address .= $shipping_city . ' ' . $shipping_state . ', ' . $shipping_postcode . ' ' . $shipping_country;

	echo '<h3>Default Shipping Address:</h3>';
	echo '<h5>' . $address . '</h5>';
}
add_action( 'woocommerce_after_checkout_billing_form', 'upandup_woocommerce_after_checkout_billing_form', 50 );

// Remove login form
update_option( 'woocommerce_enable_guest_checkout', 'no' );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

// Remove Coupons
update_option( 'woocommerce_enable_coupons', 'no' );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

// Add Account Number to Checkout Page
function upandup_woocommerce_before_checkout_form() {
	$user_id = get_current_user_id();
	$user = get_userdata( $user_id );

	if ( !$user )
	  return;

	$account_number = get_user_meta( $user_id, 'account_number', true );

	echo '<h4 class="text-center">Account Number: ' . $account_number . '</h4>';
}
add_action( 'woocommerce_before_checkout_form', 'upandup_woocommerce_before_checkout_form' );

// Start out with same shipping address
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

// hide '(free)' and other costs from shipping labels
function upandup_woo_cart_shipping_method_full_label( $label, $method ) {
	$label = $method->label;
	return $label;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'upandup_woo_cart_shipping_method_full_label', 10, 2 );

// Change # of rows for Order Notes
function custom_override_checkout_fields( $fields ) {
    $fields['order']['order_comments']['custom_attributes'] = array('rows' => 6);
    return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// make billing phone not required and billing_email not usernaame
function upandup_woo_billing_fields( $address_fields ) {
	$address_fields['billing_phone']['required'] = false;
	$address_fields['billing_email']['required'] = false;
	return $address_fields;
}
add_filter( 'woocommerce_billing_fields', 'upandup_woo_billing_fields', 10, 1 );

// ignore default value of billing_email
function upandup_woo_checkout_get_value( $value, $input ) {
	if ( $input == 'billing_email' ) {
		if ( upandup_woo_customer() ) {
			$current_user = wp_get_current_user();
			// return billing email or nothing
			if ( $meta = get_user_meta( $current_user->ID, $input, true ) ) {
				return $meta;
			} else {
				return ''; //important that this is not `null`
			}
		}
	}
}
add_filter( 'woocommerce_checkout_get_value', 'upandup_woo_checkout_get_value', 10, 2 );

/************************
 * Orders page
*************************/

// add header to orders page
function upandup_woocommerce_before_account_orders( $has_orders ) {
	if( $has_orders ) {
		echo '<h3 class="text-center">Shipped Orders</h3>';
	}
}
add_action( 'woocommerce_before_account_orders', 'upandup_woocommerce_before_account_orders' );


/************************
 * Order Details page
*************************/
// localize WP javascript
function upandup_localize_main_js() {
	global $woocommerce, $post;

  $wpVars = array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'upandup_marathon_nonce' ),
  );
  wp_localize_script( 'groundup-main', 'wpVars', $wpVars );
}
add_action( 'wp_enqueue_scripts', 'upandup_localize_main_js', 200 ); //delay until after 'groundup-main' has been enqueued in /groundup

// helper function or zipping files below:
function create_zip( $files = array(), $destination_folder = '', $filename = '', $overwrite = false ) {
	//if the zip file already exists and overwrite is false, return false
	$destination = trailingslashit( $destination_folder ) . $filename;
	if( file_exists( $destination ) ) {
		return $destination;
	}

	$valid_files = array();

	if( is_array( $files ) ) {
		foreach( $files as $file ) {
			if( file_exists( $file ) ) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if( count( $valid_files ) ) {
		//create the archive
		if ( ! file_exists( $destination_folder ) ) {
      mkdir( $destination_folder, 0757, true);
		}

		$zip = new ZipArchive();
		if( $zip->open( $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true ) {
			return file_exists( $destination );
		}
		//add the files
		foreach( $valid_files as $file ) {
			$new_filename = substr( $file, strrpos( $file,'/' ) + 1 );
			$zip->addFile( $file, $new_filename );
		}

		//close the zip -- done!
		$zip->close();

		//check to make sure the file exists
		return $valid_files;
	} else {
		return $files;
	}
}

// Remove payment method from everything
function upandup_woocommerce_get_order_item_totals( $total_rows ) {
	unset( $total_rows['payment_method'] );
	unset( $total_rows['cart_subtotal'] );
	$total_rows['order_total']['label'] = 'Total MSRP:';
	return $total_rows;
}
add_filter( 'woocommerce_get_order_item_totals', 'upandup_woocommerce_get_order_item_totals' );

////////// Image Request Stuffs ///////
// Ajax Image Request
function upandup_ajax_request_images() {
  global $phpmailer, $woocommerce;

  $response = array();
	if ( check_ajax_referer( 'upandup_marathon_nonce', 'nonce', false ) ) {

		// get order_number and username
		$current_user = wp_get_current_user();
		$customer = $current_user->user_login;
		$customer_email = $current_user->user_email;
		// $customer_email = sanitize_email($_POST['customer_email']);

		$account_number = get_user_meta( $current_user->ID, 'account_number', true );

		$response = [];
		$downloads = [];
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['path'];
		$upload_url = set_url_scheme( $upload_dir['url'] );

		// use $_POST values, should come in as $sku
		if( !empty( $_POST['image_items'] ) ) {
			$dt = [];
			foreach ( $_POST['image_items'] as $sku ) {
				array_push( $dt, $sku );
				$thumbnail = upandup_woo_img_url( 'thumbnail', $sku );
				if ( ! empty( $thumbnail ) ) {
					$full_size_image = str_replace( 'thumb', 'source', $thumbnail );
					$full_size_image = str_replace( $upload_url, $upload_path, $full_size_image );
					array_push( $downloads, $full_size_image );

					// glob uses -[a-z][a-z]* specifically to keep products like ES207 from showing thumbnails for ES207-P
					// scan thumb directory instead of source for speed.
					foreach( glob( $upload_path . '/products/thumb/' . $sku . '-[a-z][a-z]*.jpg' ) as $img_path ) {
					  // $thumbnail = str_replace( $upload_url, $upload_path, $img_path );
						$full_size_image = str_replace( 'thumb', 'source', $img_path );
						array_push( $downloads, $full_size_image );
					}
				}
			}
		} else {
			array_push( $response, array( 'error' => 'image_items empty' ) );
		}
		array_push( $response, array( 'image_items' => $dt ) );

		$zip_folder = $upload_path . '/downloads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
		$zip_file = $account_number . '-' . substr( uniqid(), -4 ) . '.zip';
		$zip_success = create_zip( $downloads, $zip_folder, $zip_file );

		array_push( $response, array( 'filesRequested' => $downloads ) );

		if ( $zip_success == 'error' ) {
			array_push( $response, array( 'zipSuccess' => 'false', 'error' => $zip_success ) );
		} else {
			// send email to admin
			array_push( $response, array( 'zipSuccess' => 'true', 'zippedFiles' => $zip_success ) );
	    $email_to = 'image@marathon-co.com';

	    $email_subject = 'Image Request From Account: ' . $account_number;
			// create .zip file of images with unique id and put into dated folder
			$zip_url = str_replace( $upload_path, $upload_url, trailingslashit( $zip_folder ) . $zip_file );

			$email_message = "Account: " . $account_number;
			$email_message .= "\r\nCustomer email: " . $customer_email;
	    $email_message .= "\r\nImages can be downloaded at " . $zip_url ;
			$email_message .= "\r\nImages will be removed from the server after 30 days";
			$email_message .= "\r\n\r\nImages Requested:";
			foreach ($dt as $image_item) {
				$email_message .= "\r\n" . $image_item;
			}
			$email_from = 'NoReply@marathon-co.com';
	    $headers = 'From: Marathon Company <' . $email_from . '>' . "\r\n";
	    $success = wp_mail( $email_to, $email_subject, $email_message, $headers );

	    if ( $success ) {
				// send confirmation email
				$email_to = $customer_email;

		    $email_subject = 'Your Image Request Is Being Processed';

				$email_message = "Thank you for requesting images from marathon-co.com. Your request is under review. We will get back to you shortly.";
				$email_from = 'NoReply@marathon-co.com';
		    $headers = 'From: Marathon Company <' . $email_from . '>' . "\r\n";

				wp_mail( $email_to, $email_subject, $email_message, $headers );

	      array_push( $response, array( 'email_sent' => 'true' ) );
				array_push( $response, array( 'success' => true ) );
	    } else {
	      array_push( $response, array( 'email_sent' => 'could not send' ) );
	    }
		}
  } else {
    array_push( $response, array( 'error' => 'invalid nonce' ) );
  }

  // echo the response
  header( 'Content-Type: application/json' );
  echo json_encode( $response );
  die();
}
add_action( 'wp_ajax_request_images', 'upandup_ajax_request_images' );
add_action( 'wp_ajax_nopriv_request_images', 'upandup_ajax_request_images' );

// Ajax Download Images
function upandup_ajax_download_images() {
  global $phpmailer, $woocommerce;

  $response = array();
	if ( check_ajax_referer( 'upandup_marathon_nonce', 'nonce', false ) ) {

		// get order_number and username
		$current_user = wp_get_current_user();
		$account_number = get_user_meta( $current_user->ID, 'account_number', true );

		$response = [];
		$downloads = [];
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['path'];
		$upload_url = set_url_scheme( $upload_dir['url'] );

		// use $_POST values, should come in as $sku
		if( !empty( $_POST['image_items'] ) ) {
			$dt = [];
			foreach ( $_POST['image_items'] as $sku ) {
				array_push( $dt, $sku );
				$thumbnail = upandup_woo_img_url( 'thumbnail', $sku );
				if ( ! empty( $thumbnail ) ) {
					$full_size_image = str_replace( 'thumb', 'source', $thumbnail );
					$full_size_image = str_replace( $upload_url, $upload_path, $full_size_image );
					array_push( $downloads, $full_size_image );

					// glob uses -[a-z][a-z]* specifically to keep products like ES207 from showing thumbnails for ES207-P
					// scan thumb directory instead of source for speed.
					foreach( glob( $upload_path . '/products/thumb/' . $sku . '-[a-z][a-z]*.jpg' ) as $img_path ) {
					  // $thumbnail = str_replace( $upload_url, $upload_path, $img_path );
						$full_size_image = str_replace( 'thumb', 'source', $img_path );
						array_push( $downloads, $full_size_image );
					}
				}
			}
		} else {
			array_push( $response, array( 'error' => 'image_items empty' ) );
		}
		array_push( $response, array( 'image_items' => $dt ) );

		if( count( $_POST['image_items'] == 1 ) ) {
			$zip_file = 'Marathon-' . $_POST['image_items'] . '-images.zip';
		} else {
			$zip_file = 'Marathon-' . substr( uniqid(), -4 ) . '-images.zip';
		}
		$zip_folder = $upload_path . '/downloads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
		$zip_success = create_zip( $downloads, $zip_folder, $zip_file );

		array_push( $response, array( 'filesRequested' => $downloads ) );

		if ( $zip_success == 'error' ) {
			array_push( $response, array( 'zipSuccess' => 'false', 'error' => $zip_success ) );
		} else {
			// send email to admin
			array_push( $response, array( 'zipSuccess' => 'true', 'zippedFiles' => $zip_success ) );

			$zip_url = str_replace( $upload_path, $upload_url, trailingslashit( $zip_folder ) . $zip_file );

	    array_push( $response, array( 'zip_url' => $zip_url ) );
			array_push( $response, array( 'success'=> true ) );
			array_push( $response, array( 'zip_name' => $zip_file ) );
		}
  } else {
    array_push( $response, array( 'error' => 'invalid nonce' ) );
  }

  // echo the response
  header( 'Content-Type: application/json' );
  echo json_encode( $response );
  die();
}
add_action( 'wp_ajax_download_images', 'upandup_ajax_download_images' );
add_action( 'wp_ajax_nopriv_download_images', 'upandup_ajax_download_images' );


// Add re-order button next to each order on my-account/orders page
function upandup_woocommerce_my_account_my_orders_actions( $actions, $order ) {
	$actions['order-again'] = array(
		'url'  => wp_nonce_url( add_query_arg( 'order_again', $order->id ) , 'woocommerce-order_again' ),
		'name' => __( 'Reorder', 'woocommerce' )
	);
	return $actions;
}
add_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );

// show shipping information, customer buyer, tracking number and marathon order number
function upandup_woocommerce_order_details_after_order_table( $order ) {
	$customer_buyer_name = get_post_meta( $order->id, 'customer_buyer_name', true );
	$customer_po = get_post_meta( $order->id, 'customer_po', true );
	$marathon_ship_rate = get_post_meta( $order->id, 'marathon_ship_rate', true );
	$ship_date = get_post_meta( $order->id, 'ship_date', true );
	$tracking_number = get_post_meta( $order->id, 'tracking_number', true );

	echo $customer_buyer_name ? '<p>Buyer Name: <strong>' . $customer_buyer_name . '</strong></p>' : '';
	echo $customer_po ? '<p>Purchase Order: <strong>' . $customer_po . '</strong></p>' : '';
	echo $marathon_ship_rate ? '<p>Shipping Rate: <strong>' . $marathon_ship_rate . '</strong></p>' : '';
	echo $ship_date ? '<p>Shipped Date: <strong>' . $ship_date . '</strong></p>' : '';
	echo $tracking_number ? '<p>Tracking Number: <strong>' . $tracking_number . '</strong></p>' : '';
}
add_action( 'woocommerce_order_details_after_order_table', 'upandup_woocommerce_order_details_after_order_table', 10, 1 );

/************************
 * account pages
************************/
function sanitize_account_number( $account_number ){
	return int( $account_number );
}
$args = array(
    'object_subtype' => 'user',
    'sanitize_callback' => 'sanitize_account_number',
    'auth_callback' => 'authorize_account_number',
    'type' => 'integer',
    'description' => 'Account Number',
    'single' => true,
    'show_in_rest' => true,
);
register_meta( 'post', 'account_number', $args );

// Add account number to admin profile fields
function upandup_user_profile_edit_action( $user ) {
	if ( current_user_can( 'edit_user', $user->ID ) ) {
		$account_disabled = ' ';
	} else {
		$account_disabled = ' disabled';
	} ?>
	<table class="form-table">
		<tr class="user-account-wrap">
			<th><label for="account_number"><?php _e('Account Number: '); ?> </label></th>
			<td><input<?php echo $account_disabled; ?> name="account_number" value="<?php echo get_user_meta( $user->ID, 'account_number', true ); ?>" type="text">
		</tr>
	</table>
<?php }
add_action( 'show_user_profile', 'upandup_user_profile_edit_action', 10, 1 );
add_action( 'edit_user_profile', 'upandup_user_profile_edit_action', 10, 1 );

function upandup_user_profile_update( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) ) {
		update_user_meta( $user_id, 'account_number', sanitize_text_field( $_POST['account_number'] ) );
	} else {
		return false;
	}
}
add_action( 'personal_options_update', 'upandup_user_profile_update', 10, 1 );
add_action( 'edit_user_profile_update', 'upandup_user_profile_update', 10, 1 );

// Add account number to My Account pages
function upandup_woocommerce_edit_account_form() {
	$current_user = wp_get_current_user();

  if ( !$current_user )
    return;

  $account_number = get_user_meta( $current_user->ID, 'account_number', true );
	$username = $current_user->user_login;

	// update username if value has been changed here
	$updatedUsername = sanitize_user( $_POST['username'], true );
	if( !empty( $updatedUsername ) && $updatedUsername != $username ) {
		if ( username_exists( $updatedUsername ) ) {
			$error = 'Username already exists';
		} else {
			 global $wpdb;

			 // Query to change the username
			 $query = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->users SET user_login = %s WHERE user_login = %s", $updatedUsername, $current_user->user_login ) );

			 if ( $query ) {
				 $username = $updatedUsername;
				 // wp_set_auth_cookie( $current_user->ID );
			 }
		 }
	}
	?>

	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="account_number"><?php _e( 'Account Number', 'woocommerce' ); ?> </label>
		<input disabled type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_number" id="account_number" value="<?php echo $account_number; ?>" />
	</p>
	<?php if( !empty( $error ) ) {
		echo '<p class="error">Username already exists</p>';
	} ?>
	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="username"><?php _e( 'Username', 'woocommerce' ); ?> </label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" value="<?php echo $username; ?>" />
	</p>

<?php }
add_action( 'woocommerce_edit_account_form_start', 'upandup_woocommerce_edit_account_form' );

// check for duplicate username when changing on myaccount page
// Save username change on account details page
function upandup_woocommerce_save_account_details_errors( $errors, $user ) {
	// update username if value has been changed here
	$user = get_user_by( 'ID', $user->ID );
	$updatedUsername = sanitize_user( $_POST['username'], true );
	if( !empty( $updatedUsername ) && $updatedUsername != $user->user_login ) {
		if ( username_exists( $updatedUsername ) ) {
			$errors->add( 'duplicate username', __( 'This username is already taken' ) );
		}
	}
	return $errors;
}
add_action( 'woocommerce_save_account_details_errors', 'upandup_woocommerce_save_account_details_errors', 10, 2 );

// Save username change on account details page
function upandup_woocommerce_save_account_details( $user_id ) {
	$current_user = wp_get_current_user();

	if ( !$current_user )
		return;

	$username = $current_user->user_login;

	// update username if value has been changed here
	$updatedUsername = sanitize_user( $_POST['username'], true );
	if( !empty( $updatedUsername ) && $updatedUsername != $username ) {
		if ( ! username_exists( $updatedUsername ) ) {
			global $wpdb;

			// Query to change the username
			$query = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->users SET user_login = %s WHERE user_login = %s", $updatedUsername, $current_user->user_login ) );

			if ( $query ) {
				$username = $updatedUsername;
				wp_set_auth_cookie( $current_user->ID );
			}
		}
	}
}
add_action( 'woocommerce_save_account_details', 'upandup_woocommerce_save_account_details' );

function upandup_woo_recent_products() {
	// get recent orders
	$customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
		'numberposts' => 30,
		'meta_key'    => '_customer_user',
		'meta_value'  => get_current_user_id(),
		'post_type'   => wc_get_order_types( 'view-orders' ),
		'post_status' => array_keys( wc_get_order_statuses() ),
	) ) );
	if ( $customer_orders ) {
		$ordered_products = array();
		foreach ( $customer_orders as $customer_order ) {
			$order = wc_get_order( $customer_order );
			// get products
			foreach( $order->get_items() as $item ) {
				array_push( $ordered_products, $item['product_id'] );
			}
		}
		if( is_array( $ordered_products ) ) {
			// trim list to 10 unique products;
			$ordered_products = array_slice( array_unique( $ordered_products ), 0, 10 );?>
			<table class="woocommerce-recent-orders shop_table shop_table_responsive my_account_orders recent-orders-table">
				<thead>
					<tr>
						<th colspan="2">Recently Ordered Items</th>
					</tr>
				</thead>
				<?php foreach ( $ordered_products as $product_id ) {
					$_product = wc_get_product( $product_id );
					if( $_product === false ) {
						continue;
					} ?>
					<tr>
						<td>
							<a href="<?php echo get_permalink( $product_id ); ?>"><?php echo $_product->get_title(); ?></a>
						</td>
						<td>
							<a href="<?php echo esc_url( $_product->add_to_cart_url() ); ?>" class="button tiny">Buy</a>
						</td>
					</tr>
				<?php } ?>
			</table>
		<?php }
		}
}
// TODO: disabled for now
// add_action( 'woocommerce_after_account_orders', 'upandup_woo_recent_products' );

// Add back permalink for children products and -N products (hidden by default because they aren't $product->is_visible())
function upandup_woo_woocommerce_order_item_permalink( $permalink, $item, $order ) {
	if( $permalink == '' ) {
		// see if product is a child product and get parent product_permalink
		$product_id   = (int) $item->get_product_id();
		$_product = wc_get_product( $product_id );
		if($_product) {
			$permalink = $_product->get_permalink();
		}
	}
	return $permalink;
}
add_filter( 'woocommerce_order_item_permalink', 'upandup_woo_woocommerce_order_item_permalink', 10, 3 );

// remove order-status and msrp totals from previous orders
function upandup_woo_account_orders_columns($columns) {
	return array(
		'order-number'  => __( 'Invoice', 'woocommerce' ),
		'order-date'    => __( 'Shipped Date', 'woocommerce' ),
		// 'order-status'  => __( 'Status', 'woocommerce' ),
		// 'order-total'   => __( 'Total', 'woocommerce' ),
		'order-actions' => '&nbsp;',
	);
}
add_filter( 'woocommerce_account_orders_columns', 'upandup_woo_account_orders_columns' );

// remove my-account navigation
function upandup_woo_account_menu_items( $items ) {
	$items = [];
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'upandup_woo_account_menu_items' );


/************************
 * Store Notices
************************/
// prettier add to cart notices.
function upandup_woo_add_to_cart_message_html( $message, $product_id ) {
	// get correct category back link
	$terms = wc_get_product_terms( $product_id, 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) );
	$terms = wc_get_product_terms( $product_id, 'product_cat' );

	// main.js has script that includes window.go(-2);
	$output = '<a href="#" class="wc-backward button" id="back">Continue Shopping</a>';
	$output .= $message;
	return $output;
}
add_filter( 'wc_add_to_cart_message_html', 'upandup_woo_add_to_cart_message_html', 0, 2 );

// Plugin Hack
function relevanssi_remove_punct_not_numbers( $a ) {
	$a = strip_tags($a);
	$a = stripslashes($a);

	$a = str_replace("·", '', $a);
	$a = str_replace("…", '', $a);
	$a = str_replace("€", '', $a);
	$a = str_replace("&shy;", '', $a);
	$a = str_replace(chr(194) . chr(160), ' ', $a);
	$a = str_replace("&nbsp;", ' ', $a);
	$a = str_replace('’', ' ', $a);
	$a = str_replace("'", ' ', $a);
	$a = str_replace("’", ' ', $a);
	$a = str_replace("‘", ' ', $a);
	$a = str_replace("”", ' ', $a);
	$a = str_replace("“", ' ', $a);
	$a = str_replace("„", ' ', $a);
	$a = str_replace("´", ' ', $a);
	$a = str_replace("—", ' ', $a);
	$a = str_replace("–", ' ', $a);
	$a = str_replace("×", ' ', $a);

  $a = preg_replace('/((?!(\.\d)):punct:)+/u', ' ', $a);
	//$a = preg_replace('/:punct:+/u', ' ', $a);
  $a = preg_replace('/:space:+/', ' ', $a);
	$a = trim($a);

        return $a;
}

/************************
 * Emails
************************/
// Add account number, username, marathon_ship_rate, customer_buyer_name, and customer_po to customer details in emails
function upandup_woo_email_customer_details_fields( $fields, $sent_to_admin, $order ) {
	$user = get_user_by( 'id', $order->customer_user );
	$user_login = wptexturize( $user->user_login );
	$account_number = get_user_meta( $user->ID, 'account_number', true );

	$username = array(
		'label' => __( 'Username', 'upandup' ),
		'value' => $user_login,
	);

	$account = array(
		'label' => __( 'Account', 'upandup' ),
		'value' => $account_number,
	);

	// Add account to beginning of associative array
	$fields = array( 'account' => $account ) + $fields = array( 'username' => $username ) + $fields;
	return $fields;
}
add_filter( 'woocommerce_email_customer_details_fields', 'upandup_woo_email_customer_details_fields', 5, 3 );

// Add All Order Meta Keys to Emails
function my_custom_checkout_field_order_meta_keys( $keys ) {
  echo '<h2>Order Details:</h2>';
	$keys['Order Date'] = 'order_date';
	$keys['Shipping Rate'] = 'marathon_ship_rate';
  $keys['Buyer Name'] = 'customer_buyer_name';
  $keys['Purchase Order'] = 'customer_po';
	$keys['Shipped Date'] = 'ship_date';
	$keys['Tracking Number'] = 'tracking_number';
  return $keys;
}
add_filter( 'woocommerce_email_order_meta_keys', 'my_custom_checkout_field_order_meta_keys' );



// Only send admin email if order_status is 'processing'
function upandup_woocommerce_email( $email_class ) {
	// remove new_order email when going from pending straight to completed.
	remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
}
add_action( 'woocommerce_email', 'upandup_woocommerce_email' );



/************************
 * Hacks
************************/
// Have relevanssi work with products starting with a decimal
if ( function_exists( 'relevanssi_remove_punct' ) ) {
	remove_filter('relevanssi_remove_punctuation', 'relevanssi_remove_punct');
	add_filter('relevanssi_remove_punctuation', 'relevanssi_remove_punct_not_numbers');
}
// Bring back shipping rates
// add_filter( 'woocommerce_enable_deprecated_additional_flat_rates', function() { return true; } );

/************************
 * Specific Templates
************************/
// TODO: Not working.
// TODO: be sure to check errors on previous orders page.
// require_once('engrave.php');

// TODO: add all numeric usernames to account_number meta TMP should only be used once
// if(current_user_can('edit_users')) {
// 	$customers = get_users('role=customer');
// 	foreach ($customers as $user) {
// 		if(ctype_digit( $user->user_login )){
// 			update_user_meta($user->ID,'account_number',$user->user_login);
// 		}
// 	}
// }
