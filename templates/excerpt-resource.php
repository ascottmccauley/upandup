<?php
/**
 *
 * @package groundup
 * @subpackage upandup
 */
?>
<?php 
// Get meta specific to resources
$resourceID = get_post_meta( $post->ID, '_resource_media', true );
$resourceURL = wp_get_attachment_url( $resourceID );
$resourceType = get_post_mime_type( $resourceID );

$thumbnail = get_the_post_thumbnail( $post->ID, 'medium' );
if ( $thumbnail != '' ) { ?>
	<li <?php post_class( 'unstyled' ); ?>>
		<section class="entry-content">
			<a href="<?php echo $resourceURL; ?>" rel="alternate" title="<?php the_title_attribute(); ?>" class="bookmark" target="_blank" type="<?php echo $resourceType; ?>">
				<figure>
					<?php echo $thumbnail; ?>
				</figure>
				<figcaption>
					<?php the_title(); ?>
				</figcaption>
			</a>
		</section>
	</li>
<?php } else { ?>
	<a href="<?php echo $resourceURL; ?>" rel="alternate" title="<?php the_title_attribute(); ?>" class="bookmark" target="_blank" type="<?php echo $resourceType; ?>"><h5 class="entry-title"><i class="icon-file-text"></i> <?php the_title(); ?></h5></a>
<?php } ?>
