<?php
/**
 * Show messages
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! $messages ) return; 

foreach ( $messages as $message ) { ?>
	<div class="alert-box success" data-alert tabindex="0" aria-live="assertive" role="dialogalert"><a href="#" tabindex="0" class="close" aria-label="Close Alert">&times;</a><i class="icon-check-circle"></i> <?php echo wp_kses_post( $message ); ?></div>
<?php } ?>
