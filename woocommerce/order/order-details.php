<?php
/**
 * Order details
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$order = wc_get_order( $order_id );

?>
<h2><?php _e( 'Order Details', 'woocommerce' ); ?></h2>
<table class="shop_table order_details">
	<thead>
		<tr>
			<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-total"><?php _e( 'Total MSRP', 'woocommerce' ); ?></th>
			<th class="prodct-reorder"><?php _e( 'Reorder', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach( $order->get_items() as $item_id => $item ) {
				wc_get_template( 'order/order-details-item.php', array(
					'order'   => $order,
					'item_id' => $item_id,
					'item'    => $item,
					'product' => apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item )
				) );
			}
		?>
		<?php do_action( 'woocommerce_order_items_table', $order ); ?>
	</tbody>
	<tfoot>
		<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
				<tr>
					<th scope="row"><?php echo $total['label']; ?></th>
					<td><?php echo $total['value']; ?></td>
					<td></td>
				</tr>
				<?php
			}
		?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>

<header>
	<h2><?php _e( 'Customer details', 'woocommerce' ); ?></h2>
</header>
<table class="shop_table shop_table_responsive customer_details">
<?php
	if ( $order->billing_email ) {
		echo '<tr><th>' . __( 'Email:', 'woocommerce' ) . '</th><td data-title="' . __( 'Email', 'woocommerce' ) . '">' . $order->billing_email . '</td></tr>';
	}

	if ( $order->billing_phone ) {
		echo '<tr><th>' . __( 'Telephone:', 'woocommerce' ) . '</th><td data-title="' . __( 'Telephone', 'woocommerce' ) . '">' . $order->billing_phone . '</td></tr>';
	}

	// Additional customer details hook
	do_action( 'woocommerce_order_details_after_customer_details', $order );
?>
</table>

<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>

<?php wc_get_template( 'order/order-details-customer.php', array( 'order' =>  $order ) ); ?>
