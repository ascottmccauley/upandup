<?php
/**
 * @package groundup
 * @subpackage upandup
 */
?>
<?php // Gallery Style
$backgrounds = get_post_gallery( get_the_ID(), false );
if ( $backgrounds != '' ) { ?>
	<section class="section">
		<header><h1><?php the_title(); ?></h1></header>
		<?php $backgrounds = explode( ',', $backgrounds['ids'] );
			foreach( $backgrounds as $background ) {
			$image = get_post( $background );
			$background = $image->guid;
			$title = $image->post_title;
			$caption = $image->post_excerpt; ?>
			<section class="slide" data-src="<?php echo $background; ?>">
				<article>
					<section class="content">
						<?php 
						if ( !wp_is_mobile() ) {
							if ( $title ) {
								echo '<h2>' . $title . '</h2>';
							}
							if ( $caption ) {
								echo '<p>' . $caption . '</p>';
							}
						} ?>
					</section>
				</article>
			</section>
		<?php } ?>
	</section>
<?php } else {
	// Single Style
	$background = get_post_thumbnail_id ( $post->ID );
	if ( $background == '' ) {
		$background = ''; // replace with default background
	}else {
		$image = get_post( $background );
		$background = $image->guid;
	}?>
	<section class="section" data-src="<?php echo $background; ?>">
		<article>
			<section class="content">
				<?php 
				if ( !wp_is_mobile() ) {
					$title = get_the_title();
					$caption = get_the_excerpt();
					if ( $title ) {
						echo '<h2>' . the_title() . '</h2>';
					}
					if ( $caption ) {
						echo '<p>' . $caption . '</p>';
					}
				} ?>
			</section>
		</article>
	</section>
<?php } ?>