<?php
/**
 * Show error messages
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! $messages ) return; ?>
<div class="alert-box alert" data-alert tabindex="0" aria-live="assertive" role="dialogalert"><ul>
	<?php foreach ( $messages as $message ) { ?>
		<li><i class="icon-exclamation-triangle"></i> <?php echo wp_kses_post( $message ); ?></li>
	<?php } ?>
</div>