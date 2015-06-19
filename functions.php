<?php
/**
 * @package upandup
 */
?>
<?php
// Shortcodes
$shortcode_dir = get_stylesheet_directory() . '/includes/shortcodes/';
foreach(  glob( $shortcode_dir . 'shortcode-*.php' ) as $shortcode ) {
	require_once( $shortcode );
}

// Post Types
$post_type_dir = get_stylesheet_directory() . '/includes/post-types/';
foreach(  glob( $post_type_dir . 'post-type-*.php' ) as $post_type ) {
	require_once( $post_type );
}

// Remove [gallery] from the_content();
function  upandup_remove_gallery( $content ) {
    preg_match_all( '/'. get_shortcode_regex() .'/s', $content, $matches, PREG_SET_ORDER );
    if ( ! empty( $matches ) ) {
        foreach ( $matches as $shortcode ) {
            if ( 'gallery' === $shortcode[2] ) {
                $pos = strpos( $content, $shortcode[0] );
                if ($pos !== false)
                    return substr_replace( $content, '', $pos, strlen($shortcode[0]) );
            }
        }
    }
    return $content;
}

// Image Sizes
update_option( 'thumbnail_size_w', 200 );
update_option( 'thumbnail_size_h', 200 );
update_option( 'thumbnail_crop', true );
update_option( 'medium_size_w', 600 );
update_option( 'medium_size_h', 600 );
update_option( 'large_size_w', 900 );
update_option( 'large_size_h', 900 );
update_option( 'embed_size_w', 1500 );
update_option( 'embed_size_h', 1200 );

add_image_size( 'small', 400, 400 );
add_image_size( 'xLarge', 1200, 1000 );
add_image_size( 'full', 1500, 1200 );

// Add offcanvas wrappers
function upandup_wrapper_start() {
	echo '<div class="wrapper">';
}
add_action( 'groundup_inside_body', 'upandup_wrapper_start' );
function upandup_wrapper_end() {
	echo '</div>';
}
add_action( 'wp_footer', 'upandup_wrapper_end', 999 );

function upandup_nav_link() {
	echo '<a class="nav-link" href="#nav-mobile"><span></span></a>';
	echo get_template_part( 'templates/nav', 'mobile');
}
add_action( 'wp_footer', 'upandup_nav_link', 998 );