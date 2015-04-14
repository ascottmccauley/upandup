<?php
/**
 * Checkout shipping information form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="woocommerce-shipping-fields medium-6 large-4 columns">
	<?php if ( WC()->cart->needs_shipping_address() === true ) {
		if ( empty( $_POST ) ) {
			$ship_to_different_address = get_option( 'woocommerce_ship_to_destination' ) === 'shipping' ? 1 : 0;
			$ship_to_different_address = apply_filters( 'woocommerce_ship_to_different_address_checked', $ship_to_different_address );
		} else {
			$ship_to_different_address = $checkout->get_value( 'ship_to_different_address' );
		} ?>
		<div id="ship-to-different-address">
			<h5><input id="ship-to-different-address-checkbox" class="input-checkbox" <?php checked( $ship_to_different_address, 1 ); ?> type="checkbox" name="ship_to_different_address" value="1"> <?php _e( 'Ship to a different address?', 'woocommerce' ); ?></h5>
		</div>
		<div class="shipping_address">
			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout );
			foreach ( $checkout->checkout_fields['shipping'] as $key => $field ) {
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			}
			do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>
		</div>
	<?php } ?>
	<?php // Order Notes
	do_action( 'woocommerce_before_order_notes', $checkout );
	if ( apply_filters( 'woocommerce_enable_order_notes_field', get_option( 'woocommerce_enable_order_comments', 'yes' ) === 'yes' ) ) { ?>
		<div class="order-notes">
			<h5><?php _e( 'Order Notes', 'woocommerce' ); ?></h5>
			<?php foreach ( $checkout->checkout_fields['order'] as $key => $field ) {
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			} ?>
		</div>
	<?php }
	do_action( 'woocommerce_after_order_notes', $checkout ); ?>
</div>