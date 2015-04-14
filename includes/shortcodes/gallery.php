<?php 
/**
 * [gallery]
 *
 *
 * Remove the standard gallery and enhance it.
 * Also clean up the standard [caption]
**/

remove_shortcode('gallery', 'gallery_shortcode');
function shortcode_gallery($attr) {
	$post = get_post();
	
	static $instance = 0;
	$instance++;

	if (!empty($attr['ids'])) {
		if (empty($attr['orderby'])) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}

	$output = apply_filters('post_gallery', '', $attr);

	if ($output != '') {
		return $output;
	}

	if (isset($attr['orderby'])) {
		$attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
		if (!$attr['orderby']) {
			unset($attr['orderby']);
		}
	}

	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => '',
		'icontag'    => '',
		'captiontag' => '',
		'columns'    => '',
		'size'       => '_n',
		'include'    => '',
		'exclude'    => ''
	), $attr));

	$id = intval($id);

	if ($order === 'RAND') {
		$orderby = 'none';
	}

	if (!empty($include)) {
		$_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

		$attachments = array();
		foreach ($_attachments as $key => $val) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif (!empty($exclude)) {
		$attachments = get_children(array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
	} else {
		$attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
	}

	if (empty($attachments)) {
		return '';
	}

	if (is_feed()) {
		$output = "\n";
		foreach ($attachments as $att_id => $attachment) {
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		}
		return $output;
	}
	
	$output = '<div class="gallery">';

	foreach ($attachments as $id => $attachment) {
		$image = wp_get_attachment_image_src($id, 'large');
		$imageURL = $image[0];
		$thumb = wp_get_attachment_image_src($id, $size);
		$thumbURL = $thumb[0];
		if (trim($attachment->post_excerpt)) {
			$caption = '<div class="caption">' . wptexturize($attachment->post_excerpt) . '</div>';
		}else {
			$caption = '';
		}
		$output .= '<a class="image-link" href="' . $imageURL . '"><img src="' . $thumbURL . '">' . $caption . '</a>';
	}

	$output .= '</div>';
	return $output;
}
add_shortcode('gallery', 'shortcode_gallery');

/**
 * [caption]
 *
 *
 * Fixes the default wordpress caption output
**/
function shortcode_caption($output, $attr, $content) {
	if (is_feed()) {
    return $output;
  }
  $defaults = array(
		'id'      => '',
		'align'   => 'alignnone',
		'width'   => '',
		'caption' => ''
	);

	$attr = shortcode_atts($defaults, $attr);

	// If the width is less than 1 or there is no caption, return the content wrapped between the [caption] tags
	if ($attr['width'] < 1 || empty($attr['caption'])) {
		return $content;
	}

	// Set up the attributes for the caption <figure>
	$attributes  = (!empty($attr['id']) ? ' id="' . esc_attr($attr['id']) . '"' : '' );
	$attributes .= ' class="thumbnai ' . esc_attr($attr['align']) . '"';

	$output  = '<figure' . $attributes .'>';
	$output .= do_shortcode($content);
	$output .= '<figcaption class="caption">' . $attr['caption'] . '</figcaption>';
	$output .= '</figure>';

  return $output;
}
add_filter('img_caption_shortcode', 'shortcode_caption', 10, 3);
