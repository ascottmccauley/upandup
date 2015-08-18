<?php
/*
Template Name: Missing Images
*/
?>
<?php
/**
 * @package groundup
 * @subpackage upandup
 */
?>
<?php get_header(); ?>
<main id="main" role="main">
  <?php if ( class_exists( 'Woocommerce' ) ) {
    $upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['path'];
    $args = array(
      'post_type' => 'product',
      'fields' => 'ids',
      'order' => 'ASC',
      'posts_per_page' => -1,
    );
    $products = get_posts( $args );
    echo '<h2 class="text-center">Missing Images</h2>';
    echo '<ul class="column-5">';
    foreach ( $products as $product ) {
      $sku = get_post_meta( $product, '_sku', true );
      // special case: if sku ends in "n" but not "-n", grab parent image (without "n");
      if ( substr( $sku,  -1 ) == 'n' && substr( $sku,  -2 ) != '-n' ) {
        // remove trailing 'n'
        $sku = rtrim( $sku, 'n' );
      }
      if ( ! file_exists( $upload_path . '/products/thumb/' . $sku . '.jpg') ) {
        echo '<li>' . $sku . '</li>';
      }
    }
    echo '</ul>';
  } else {
    echo '<h1>WooCommerce is not active.</h1>';
  } ?>
</main>
<?php get_footer(); ?>
