<?php
/**
 * The template for displaying product content in the single-product.php template
 * @version     1.6.4
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

 /**
  * woocommerce_before_single_product hook
  *
  * @hooked wc_print_notices - 10
 **/
  do_action( 'woocommerce_before_single_product' );
 if ( post_password_required() ) {
 	echo get_the_password_form();
 	return;
 }
 
 /**
  * woocommerce_before_single_product_summary hook
  *
  * @hooked woocommerce_show_product_sale_flash - 10
  * @hooked woocommerce_show_product_images - 20
 **/
 do_action( 'woocommerce_before_single_product_summary' ); ?>
 
<section itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class( 'row' ); ?>>
	<?php // Check to see if product has images before determining layout
	global $product;
	$image = upandup_woo_img_url( 'full' );
	if( ! empty( $image ) ) {
		$class = "small-12 medium-6 medium-push-6 columns";
	}else {
		$class = "small-12 columns";
	}?>
	<div class="<?php echo $class; ?>">
		<?php
			/**
			 * woocommerce_single_product_summary hook
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked upandup_woo_product_attributes - 40
			 * @hooked upandup_woo_product_description - 50
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			**/
			
			do_action( 'woocommerce_single_product_summary' ); ?>
	</div>
	<?php
	/**
	 * woocommerce_after_single_product_summary hook
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' ); ?>

	<meta itemprop="url" content="<?php the_permalink(); ?>">

</section>

<?php do_action( 'woocommerce_after_single_product' ); ?>
