<?php
/**
 * Show messages
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! $messages ) return; 

foreach ( $messages as $message ) { ?>
	<div class="alert-box info" data-alert tabindex="0" aria-live="assertive" role="dialogalert"><i class="icon-info-circle"></i> <?php echo wp_kses_post( $message ); ?></div>
<?php } ?>