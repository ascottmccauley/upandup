<?php
/**
 * Shipping Methods Display
 *
 * In 2.1 we show methods per package. This allows for multiple methods per order if so desired.
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
**/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; ?>
<tr class="shipping">
	<th><?php
		if ( $show_package_details ) {
			printf( __( 'Shipping #%d', 'woocommerce' ), $index + 1 );
		} else {
			_e( 'Shipping and Handling', 'woocommerce' );
		}
	?></th>
	<td>
		<?php if ( ! empty( $available_methods ) ) { if ( 1 === count( $available_methods ) ) {
				$method = current( $available_methods );
				echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?><input type="hidden" name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" id="shipping_method_<?php echo $index; ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" />
			<?php }elseif ( get_option( 'woocommerce_shipping_method_format' ) === 'select' ) { ?>
				<select name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" id="shipping_method_<?php echo $index; ?>" class="shipping_method">
					<?php foreach ( $available_methods as $method ) : ?>
						<option value="<?php echo esc_attr( $method->id ); ?>" <?php selected( $method->id, $chosen_method ); ?>><?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php }else { ?>
				<ul id="shipping_method">
					<?php foreach ( $available_methods as $method ) { ?>
						<li>
							<input type="radio" name="shipping_method[<?php echo $index; ?>]" data-index="<?php echo $index; ?>" id="shipping_method_<?php echo $index; ?>_<?php echo sanitize_title( $method->id ); ?>" value="<?php echo esc_attr( $method->id ); ?>" <?php checked( $method->id, $chosen_method ); ?> class="shipping_method" />
							<label for="shipping_method_<?php echo $index; ?>_<?php echo sanitize_title( $method->id ); ?>"><?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?></label>
						</li>
					<?php } ?>
				</ul>
			<?php }
			}elseif ( ! WC()->customer->get_shipping_state() || ! WC()->customer->get_shipping_postcode() ) {
			if ( is_cart() && get_option( 'woocommerce_enable_shipping_calc' ) === 'yes' ) {
				_e( 'Please use the shipping calculator to see available shipping methods.', 'woocommerce' );
			}elseif ( is_cart() ) {
				_e( 'Please continue to the checkout and enter your full address to see if there are any available shipping methods.', 'woocommerce' );
			}else {
				_e( 'Please fill in your details to see available shipping methods.', 'woocommerce' );
			}
		}else {
			if ( is_cart() ) {
				echo apply_filters( 'woocommerce_cart_no_shipping_available_html',
					'<div class="woocommerce-info">' . __( 'There doesn&lsquo;t seem to be any available shipping methods. Please double check your address, or contact us if you need any help.', 'woocommerce' ) . '</div>'
				);
			}else {echo apply_filters( 'woocommerce_no_shipping_available_html',
					'' . __( 'There doesn&lsquo;t seem to be any available shipping methods. Please double check your address, or contact us if you need any help.', 'woocommerce' ) . ''
				);
			}
		}
		if ( $show_package_details ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				if ( $values['data']->needs_shipping() ) {
						$product_names[] = $values['data']->get_title() . ' &times;' . $values['quantity'];
				}
			}
			echo '<p class="woocommerce-shipping-contents"><small>' . __( 'Shipping', 'woocommerce' ) . ': ' . implode( ', ', $product_names ) . '</small>';
		}
		
		if ( is_cart() ) {
			woocommerce_shipping_calculator();
		} ?>
	</td>
</tr>