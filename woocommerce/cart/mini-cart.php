<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see     http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.5.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
 	exit; // Exit if accessed directly
 }
 ?>

<?php do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( sizeof( WC()->cart->get_cart() ) > 0 ) { ?>
	<table class="mini-cart full">

		<?php	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

					$product_name  = apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key );
					$thumbnail     = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('tiny'), $cart_item, $cart_item_key );
					$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );

					if ( $_product->is_visible() ) {
						$permalink = '<a href="' . get_permalink( $product_id ) . '">';
						$permalink_close = '</a>';
					}else {
						$permalink = '';
						$permalink_close = '';
					} ?>
					<tr>
						<?php // Don't load thumbnails for mobile users
						if(!wp_is_mobile()){ ?>
							<td class="product-thumbnail hide-for-small"><?php echo $permalink . str_replace( array( 'http:', 'https:' ), '', $thumbnail ) . $permalink_close; ?></td>
						<?php } ?>
						<td class="title"><?php echo $permalink . $product_name . $permalink_close; ?>
						<td class="price"><?php echo $permalink . apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ) . $permalink_close; ?></td>
					</tr>
				<?php }
			}	?>
	</table>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="button expand wc-forward"><i class="icon-shopping-cart"></i> <?php _e( 'View Cart', 'woocommerce' ); ?></a>
<?php }else { ?>

		<p class="empty"><?php _e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php }

do_action( 'woocommerce_after_mini_cart' ); ?>
