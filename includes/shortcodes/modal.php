<?php 
/**
 * [modal] or [popup]
 *
 * Creates a modal that is automatically launched (default) or launched by a button
 *
 * Examples:
 * [popup]This is the text that will automatically pop-up when the page loads[/popup]
 *
 * [modal button="launch modal"]This text will load when the user presses the button created![/modal]
 * 
 * Nested Modals
 * To create multiple modals inside each other you have to add an extra = for each consecutive modal
 * [modal button="First"]First...[=modal button="Second"]Second...[==modal button="third"]Third...[==/modal][=/modal][/modal]
 **/
class Modal_Shortcode {
	protected static $modal;
	
	public static function shortcode_callback( $atts, $content = null) {
		extract( shortcode_atts( array(
		'text' => '',
		'button' => '',
		'style' => '',
		'size' => '', 
		), $atts ) );
		
		// get unique modal number 
		$modalNum = 'modal-' . substr(uniqid(), -4); //last 4 digits of uniqid will suffice
		if($text == '') {
			$text = $content;
		}
		
		// Remove = from [=modal] [=/modal] to allow for nested shortcodes.
		$text = str_replace('[=', '[', $text);
		$text = do_shortcode( $text );
		
		self::$modal .= '<div id="' . $modalNum . '" class="reveal-modal ' . $size . '" role="dialog" data-reveal>';
		self::$modal .= $text . '<a href="#" style="button" class="close-reveal-modal">&times;</a>';
		self::$modal .= '</div>';
		
		if($button != '') {
			$button = '<a href="#" data-reveal-id="' . $modalNum . '" role="button" class="button ' . $style . '">' . $button . '</a>';
		}else {
			self::$modal .= '<script style="text/javascript">
		        jQuery("#' . $modalNum . '").foundation("reveal","open");
			</script>';
		}
		
		add_action('wp_footer', array( __CLASS__, 'footer' ), 300);
		
		return $button;
	}
	public static function footer() {
		echo self::$modal;
	}
}
add_shortcode('modal', array('Modal_Shortcode', 'shortcode_callback'));
add_shortcode('popup', array('Modal_Shortcode', 'shortcode_callback'));