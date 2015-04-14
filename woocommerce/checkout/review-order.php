<?php
/**
 * Review order form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="medium-6 large-4 columns">
	<h5 id="order_review_heading"><?php _e( 'Your order', 'woocommerce' ); ?></h5>
	<table class="shop_table full">
		<thead>
			<tr>
				<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
				<th class="product-MSRP"><?php _e( 'MSRP', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php	do_action( 'woocommerce_review_order_before_cart_contents' );
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) { ?>
					<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
						<td class="product-name">
							<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ); ?>
						</td>
						<td class="product-MSRP">
							<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', '<span class="product-quantity">' . sprintf(' %s &times;', $cart_item['quantity'] ) . '</span>', $cart_item, $cart_item_key ); ?>
							<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_price( $_product )) ?>
							<?php echo WC()->cart->get_item_data( $cart_item ); ?>
						</td>
					</tr>
				<?php	}
			}
			do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
		</tbody>
		<tfoot>
			<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) {
				do_action( 'woocommerce_review_order_before_shipping' );
				wc_cart_totals_shipping_html();
				do_action( 'woocommerce_review_order_after_shipping' );
			}
			foreach ( WC()->cart->get_fees() as $fee ) { ?>
				<tr class="fee">
					<th><?php echo esc_html( $fee->name ); ?></th>
					<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
				</tr>
			<?php } ?>

			<?php if ( WC()->cart->tax_display_cart === 'excl' ) { ?>
				<?php if ( get_option( 'woocommerce_tax_total_display' ) === 'itemized' ) { ?>
					<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { ?>
						<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
							<th><?php echo esc_html( $tax->label ); ?></th>
							<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
						</tr>
					<?php } ?>
				<?php }else { ?>
					<tr class="tax-total">
						<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
						<td><?php echo wc_price( WC()->cart->get_taxes_total() ); ?></td>
					</tr>
				<?php } ?>
			<?php } ?>

			<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) { ?>
				<tr class="order-discount coupon-<?php echo esc_attr( $code ); ?>">
					<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
					<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
				</tr>
			<?php }
			do_action( 'woocommerce_review_order_before_order_total' ); ?>

			<tr class="order-total">
				<th><?php _e( 'Order Total', 'woocommerce' ); ?></th>
				<td><?php wc_cart_totals_order_total_html(); ?></td>
			</tr>

			<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

		</tfoot>
	</table>
</div>