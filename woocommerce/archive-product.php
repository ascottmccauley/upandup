<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 * @version     2.0.0
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );

/**
 * woocommerce_before_main_content hook
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
**/
do_action( 'woocommerce_before_main_content' );

do_action( 'woocommerce_archive_description' );

if ( have_posts() ) {

	/**
	 * woocommerce_before_shop_loop hook
	 *
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	**/
	remove_action( 'woocommerce_before_shop_loop','woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop','woocommerce_catalog_ordering', 30 );
	do_action( 'woocommerce_before_shop_loop' ); ?>
				
		<?php $subcats = woocommerce_product_subcategories();
		if( woocommerce_products_will_display() ) { ?>
			<ul class="product-list small-block-grid-2 medium-block-grid-4 large-block-grid-6">
				<?php while ( have_posts() ) : the_post();
					wc_get_template_part( 'content', 'product' ); ?>
				<?php endwhile; ?>
			</ul>
			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
		}	?>
<?php }else {
	wc_get_template( 'loop/no-products-found.php' ); 
}
	

/**
 * woocommerce_after_main_content hook
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
**/
do_action( 'woocommerce_after_main_content' );

/**
 * woocommerce_sidebar hook
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );