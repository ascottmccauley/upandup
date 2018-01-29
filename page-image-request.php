<?php
/*
Template Name: Image Request
*/
?>
<?php
/**
 * @package groundup
 * @subpackage upandup
 */
?>
<?php get_header();

// Loop through customers completed orders and pull out all products that have been ordered.
$customer_orders = get_posts( array(
    'numberposts' => -1,
    'meta_key'    => '_customer_user',
    'meta_value'  => get_current_user_id(),
    'post_type'   => wc_get_order_types(),
    'post_status' => 'wc-completed',
) );

$ordered_products = array();

foreach ( $customer_orders as $customer_order ) {
  $order = wc_get_order( $customer_order );
  foreach ( $order->get_items() as $item ) {
    $_product = new WC_Product( $item['product_id'] );
    // don't add duplicate products, add to array as $ordered_products['sku'] = $_product
    if( !array_key_exists( $_product->get_sku(), $ordered_products ) ) {
      $ordered_products[$_product->get_sku()] = $_product;
    }
  }
}

if( count( $ordered_products > 1 ) ) {
  // sort array by sku
  ksort( $ordered_products, SORT_NATURAL );
}
// TODO: need to figure out how to paginate this.
// TODO: add option to sort by order date
// TODO: allow ajax product search

 ?>
<main id="main" role="main">
  <div class="loop">
    <?php while( have_posts() ) : the_post(); ?>
      <article <?php post_class(); ?>>
        <section class="entry-content">
          <?php the_content(); ?>
        </section>
      </article>
    <?php endwhile; ?>
  </div>
  <?php $len = count( $ordered_products );
  if ( class_exists( 'Woocommerce' ) && $len > 1 && is_user_logged_in() && ! current_user_can( 'subscriber' ) ) {
    $i = 0; ?>
    <form class="requestImagesForm">
      <ul class="products">
        <?php
        foreach ( $ordered_products as $sku => $_product ) {
          $thumbnail = upandup_woo_img_url( 'small', $_product ); ?>
          <li class="imageRequestItem" data-sku="<?php echo $sku; ?>">
            <a href="#" class="imageRequestLink">
              <div class="square-thumb" style="background-image: url('<?php echo $thumbnail; ?>')"></div>
            </a>
            <div class="product-info">
              <a href="#" class="imageRequestLink">
                <h2 class="woocommerce-loop-product__title woocommerce-image-request-product__title"><?php echo $_product->get_title(); ?></h2>
              </a>
            </div>
          </li>
        <?php } ?>
      </ul>
      <p class="requestSuccess hide">Your request has been submitted. Someone will be contacting you soon.</p>
      <p class="text-center"><button type="submit" class="secondary requestImages button large">Request Images </button></p>
    </form>
  <?php } else {
    echo '<h4>Error</h4>';
    echo '<p>The system cannot find any products that have been shipped to this account.</p>';
  } ?>
</main>
<?php get_footer(); ?>
