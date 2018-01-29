<?php
/**
 * Slideshow
 *
 * Used for displaying the custom_post_type "slide" with the shortcode [slideshow location="XYZ"]
**/
global $slideshow;
global $slideshow_options;
if ( ! empty( $slideshow ) ) {
	$slideshow_args = array(
		'posts_per_page' => -1,
		'post_type' => 'slide',
		'order' => 'ASC',
		'tax_query' => array(
			array(
				'taxonomy' => 'slideshow',
				'field' => 'slug',
				'terms' => $slideshow
			)
		)
	);
	$slideshow_query = new WP_Query( $slideshow_args );
	if ( $slideshow_query->have_posts() ) { ?>
		<div class="slideshow <?php echo $slideshow; ?>">
			<?php while ( $slideshow_query->have_posts() ) : $slideshow_query->the_post(); ?>
				<?php $slide_url = set_url_scheme( get_post_meta( get_the_ID(), '_slide_url', true ) );
					if ( $slide_url != '' ) {
						echo '<a class="slide" target="_blank" href="' . $slide_url . '">';
					}else {
						echo '<div>';
					} ?>
					<?php if ( has_post_thumbnail() ) {
						set_url_scheme( the_post_thumbnail( 'large' ) );
						if ( has_excerpt() ) { ?>
							<div class="caption"><?php the_excerpt(); ?></div>
						<?php }
					}else { ?>
						<h3><?php the_title(); ?></h3>
						<?php the_excerpt(); ?>
					<?php }
				if ( $slide_url != '' ) {
					echo '</a>';
				}else {
					echo '</div>';
				}
			 endwhile; ?>
		</div>
	<?php }
}