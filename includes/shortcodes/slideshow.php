<?php 
/**
 * [slideshow]
 *
 * Output posts from the base_slideshow custom post type
 * Use location="" attribute to pull in posts from a specific location
 * from the slideshow_location taxonomy
 *
 * Example:
 * [slideshow location="home"]
**/

function shortcode_slideshow( $atts ) {
  extract( shortcode_atts( array(
    'location' => '',
    'options' => 'infinite: true, speed: 300, fade: true, slidesToShow: 1, autoplay: true, autoplaySpeed: 5000',
  ), $atts ) );
  global $slideshow;
  global $slideshow_options;
  $slideshow = $location;
  $slideshow_options = $options;
  ob_start();
  get_template_part( 'templates/slideshow', $location );
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}
add_shortcode( 'slideshow', 'shortcode_slideshow' );