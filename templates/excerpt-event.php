<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php 
// Get meta specific to events
$event_start_date = get_post_meta( $post->ID, '_event_start_date', true );
$event_end_date = get_post_meta( $post->ID, '_event_end_date', true );
$event_location = get_post_meta( $post->ID, '_event_location', true );
$event_venue = get_post_meta( $post->ID, '_event_venue', true );
$event_url = get_post_meta( $post->ID, '_event_url', true );

// convert dates to a more readable format
$event_dates = get_date_range( strtotime($event_start_date), strtotime($event_end_date ) ); ?>

<article <?php post_class(); ?>>
	<section class="section-content">
		<dl>
			<dt>
				<a href="<?php echo $event_url ? $event_url . '" target="_blank"' : get_the_permalink() ?>" rel="bookmark" title="Link to <?php the_title_attribute(); ?>"><h5><?php the_title(); ?></h5></a>
			</dt>
			<?php // List Dates
			if ( $event_dates != '' ) {
				echo '<dd>' . $event_dates . '</dd>';
			}
			// List location
			if ( $event_location != '' ) {
				echo '<dd>' . $event_location . '</dd>';
			}
			// List venue
			if ( $event_venue != '' ) {
				echo '<dd>' . $event_venue . '</dd>';
			} ?>
		</dl>
	</section>
	<?php // Thumbnail
	$thumbnail = get_the_post_thumbnail($post->ID, 'small');
	if($thumbnail != '') { ?>
		<aside class="entry-thumbnail">
			<a href="<?php echo $event_url ? $event_url . '" target="_blank"' : get_the_permalink() ?>" rel="bookmark" title="Link to <?php the_title_attribute(); ?>"><?php echo $thumbnail; ?></a>
		</aside>
	<?php } ?>
</article>