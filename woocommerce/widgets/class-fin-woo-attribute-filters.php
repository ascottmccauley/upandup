<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Adds Foo_Widget widget.
 */
class Upandup_Woo_Attribute_Filters extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'attribute_filters', // Base ID
			__( 'Attribute Filters', 'upandup' ), // Name
			array( 'description' => __( 'Lists all attributes of currently shown products to be filtered out as needed', 'upandup' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $_chosen_attributes;
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		// Only show on pages that show products
		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return;
		}
		
		$current_term = is_tax() ? get_queried_object()->term_id : '';
		$current_tax  = is_tax() ? get_queried_object()->taxonomy : '';
		foreach(wc_get_attribute_taxonomies() as $taxonomy) {
			$tax = 'pa_' . $taxonomy->attribute_name;
			$get_terms_args = array( 'hide_empty' => '1' );
			$orderby = wc_attribute_orderby( $tax );
			
			switch ( $orderby ) {
				case 'name' :
					$get_terms_args['orderby']    = 'name';
					$get_terms_args['menu_order'] = false;
				break;
				case 'id' :
					$get_terms_args['orderby']    = 'id';
					$get_terms_args['order']      = 'ASC';
					$get_terms_args['menu_order'] = false;
				break;
				case 'menu_order' :
					$get_terms_args['menu_order'] = 'ASC';
				break;
			}
			
			$terms = get_terms( $tax, $get_terms_args );
			if ( 0 < count( $terms ) ) {
				ob_start();
				echo '<h5>' . $taxonomy->attribute_label . '</h5>';
				echo '<ul>';
				$found = false;
				// Force found when option is selected - do not force found on taxonomy attributes
				if ( !is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $tax, $_chosen_attributes ) ) {
					$found = true;
				}
				foreach ( $terms as $term ) {
					// Get count based on current view - uses transients
					$transient_name = 'wc_ln_count_' . md5( sanitize_key($tax) . sanitize_key( $term->term_taxonomy_id ) );
					if ( false === $_products_in_term = ( get_transient( $transient_name ) ) ) {
						$_products_in_term = get_objects_in_term( $term->term_id, $tax);
						set_transient( $transient_name, $_products_in_term );
					}
	
					$option_is_set = ( isset( $_chosen_attributes[$tax] ) && in_array( $term->term_id, $_chosen_attributes[$tax]['terms'] ) );
					
					// skip the term for the current archive
					if ( $current_term == $term->term_id ) {
						continue;
					}
	
					// Only show options with count > 0
	
					$count = sizeof( array_intersect( $_products_in_term, WC()->query->filtered_product_ids ) );
	
					if ( 0 < $count && $current_term !== $term->term_id ) {
						$found = true;
					}
					
					if($option_is_set) {
						$found = true;
					}
					
					if ( 0 == $count && !$option_is_set ) {
						continue;
					}
	
					$arg = 'filter_' . sanitize_title( $taxonomy->attribute_label );
	
					$current_filter = ( isset( $_GET[ $arg ] ) ) ? explode( ',', $_GET[ $arg ] ) : array();
	
					if ( ! is_array( $current_filter ) ) {
						$current_filter = array();
					}
	
					$current_filter = array_map( 'esc_attr', $current_filter );
	
					if ( ! in_array( $term->term_id, $current_filter ) ) {
						$current_filter[] = $term->term_id;
					}
					
					// Base Link decided by current page
					if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
						$link = home_url();
					} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id('shop') ) ) {
						$link = get_post_type_archive_link( 'product' );
					} else {
						$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
					}
	
					// All current filters
					if ( $_chosen_attributes ) {
						foreach ( $_chosen_attributes as $name => $data ) {
							if ( $name !== $tax) {
	
								// Exclude query arg for current term archive term
								while ( in_array( $current_term, $data['terms'] ) ) {
									$key = array_search( $current_term, $data );
									unset( $data['terms'][$key] );
								}
	
								// Remove pa_ and sanitize
								$filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );
	
								if ( ! empty( $data['terms'] ) ) {
									$link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
								}
								
							}
						}
					}
	
					// Min/Max
					if ( isset( $_GET['min_price'] ) ) {
						$link = add_query_arg( 'min_price', $_GET['min_price'], $link );
					}
	
					if ( isset( $_GET['max_price'] ) ) {
						$link = add_query_arg( 'max_price', $_GET['max_price'], $link );
					}
	
					// Orderby
					if ( isset( $_GET['orderby'] ) ) {
						$link = add_query_arg( 'orderby', $_GET['orderby'], $link );
					}
	
					// Current Filter = this widget
					if ( isset( $_chosen_attributes[ $tax] ) && is_array( $_chosen_attributes[ $tax]['terms'] ) && in_array( $term->term_id, $_chosen_attributes[ $tax]['terms'] ) ) {
	
						$class = 'class="filtered"';
	
						// Remove this term is $current_filter has more than 1 term filtered
						if ( sizeof( $current_filter ) > 1 ) {
							$current_filter_without_this = array_diff( $current_filter, array( $term->term_id ) );
							$link = add_query_arg( $arg, implode( ',', $current_filter_without_this ), $link );
						}
	
					} else {
	
						$class = '';
						$link = add_query_arg( $arg, implode( ',', $current_filter ), $link );
	
					}
	
					// Search Arg
					if ( get_search_query() ) {
						$link = add_query_arg( 's', get_search_query(), $link );
					}
	
					// Post Type Arg
					if ( isset( $_GET['post_type'] ) ) {
						$link = add_query_arg( 'post_type', $_GET['post_type'], $link );
					}
					
					echo '<li ' . $class . '>';
					
					echo ( $count > 0 || $option_is_set ) ? '<a href="' . esc_url( apply_filters( 'woocommerce_layered_nav_link', $link ) ) . '">' : '<span>';
	
					echo $term->name;
	
					echo ( $count > 0 || $option_is_set ) ? '</a>' : '</span>';
	
					echo ' <span class="count">' . $count . '</span></li>';
					
				}//end foreach
				
				echo '</ul>';
				
				// Output the contents
				if ( !$found ) {
					ob_end_clean();
				} else {
					echo ob_get_clean();
				}
			}
		}
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'upandup' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}