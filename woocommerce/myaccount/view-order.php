<?php
/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p><?php
  // ASM Custom - use order_date from meta if there is one instead of the order date
  // $order_date = get_post_meta( $order->get_id(), 'order_date', true );
  // $order_date = $order_date ? $order_date : wc_format_datetime( $order->get_date_created(), 'm/d/Y' );

  $ship_date = get_post_meta( $order->get_id(), 'ship_date', true );
  if ( $ship_date ) {
    $order_status = 'shipped on <strong>' . date( wc_date_format(), strtotime( $ship_date ) ) . '</strong>';
  } else {
    $order_status = 'is <strong>' . $order->get_status() . '</strong>';
  }

  echo '<p>Invoice #<strong>' . $order->get_order_number() . ' ' . $order_status;

  $tracking_number = get_post_meta( $order->get_id(), 'tracking_number', true );
  if ( $tracking_number ) {
    echo '<p>Tracking Number: <strong>' . $tracking_number . '</strong></p>';
  }

	/* translators: 1: order number 2: order date 3: order status */
	// printf(
	// 	__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
	// 	'<mark class="order-number">' . $order->get_order_number() . '</mark>',
	// 	'<mark class="order-date">' . $order_date . '</mark>',
	// 	'<mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark>'
	// );

  // ASM End Custom

?></p>

<?php if ( $notes = $order->get_customer_order_notes() ) : ?>
	<h2><?php _e( 'Order updates', 'woocommerce' ); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
		<li class="woocommerce-OrderUpdate comment note">
			<div class="woocommerce-OrderUpdate-inner comment_container">
				<div class="woocommerce-OrderUpdate-text comment-text">
					<p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
					<div class="woocommerce-OrderUpdate-description description">
						<?php echo wpautop( wptexturize( $note->comment_content ) ); ?>
					</div>
	  				<div class="clear"></div>
	  			</div>
				<div class="clear"></div>
			</div>
		</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>

<?php do_action( 'woocommerce_view_order', $order->get_id() ); ?>