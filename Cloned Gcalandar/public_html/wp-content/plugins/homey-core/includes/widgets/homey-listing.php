<?php
/*
 * Widget Name: Featured Listings
 * Version: 1.0
 * Author: Waqas Riaz
 * Author URI: http://favethemes.com/
 */

class Homey_listing extends WP_Widget {

	/**
	 * Register widget
	 **/
	public function __construct() {

		parent::__construct(
			'Homey_listing', // Base ID
			esc_html__( 'Homey: Listing Card', 'homey' ), // Name
			array( 'description' => esc_html__( 'Show listing card', 'homey' ), 'classname' => 'widget-card-properties widget-latest-properties') // Args
		);

	}


	/**
	 * Front-end display of widget
	 **/
	public function widget( $args, $instance ) {

		global $before_widget, $after_widget, $before_title, $after_title, $post;
		extract( $args );

		$homey_local = homey_get_localization();

		$allowed_html_array = array(
			'div' => array(
				'id' => array(),
				'class' => array()
			),
			'h3' => array(
				'class' => array()
			)
		);

		$title = apply_filters('widget_title', $instance['title'] );
		$items_num = $instance['items_num'];
		$widget_type = $instance['widget_type'];
		$listing_type = isset( $instance['listing_type'] ) ? $instance['listing_type'] : '';
		$room_type = isset( $instance['room_type'] ) ? $instance['room_type'] : '';
		$listing_city = isset( $instance['listing_city'] ) ? $instance['listing_city'] : '';
		$listing_area = isset( $instance['listing_area'] ) ? $instance['listing_area'] : '';
		$listing_state = isset( $instance['listing_state'] ) ? $instance['listing_state'] : '';
		$featured = $instance[ 'featured' ] ? 'true' : 'false';

		echo wp_kses( $before_widget, $allowed_html_array );


		if ( $title ) echo wp_kses( $before_title, $allowed_html_array ) . $title . wp_kses( $after_title, $allowed_html_array );
		?>

		<?php

		$tax_query = array();
		$meta_query = array();

		if (!empty($listing_type)) {
			$tax_query[] = array(
				'taxonomy' => 'listing_type',
				'field' => 'slug',
				'terms' => $listing_type
			);
		}
		if (!empty($room_type)) {
			$tax_query[] = array(
				'taxonomy' => 'room_type',
				'field' => 'slug',
				'terms' => $room_type
			);
		}
		if (!empty($listing_city)) {
			$tax_query[] = array(
				'taxonomy' => 'listing_city',
				'field' => 'slug',
				'terms' => $listing_city
			);
		}
		if (!empty($listing_area)) {
			$tax_query[] = array(
				'taxonomy' => 'listing_area',
				'field' => 'slug',
				'terms' => $listing_area
			);
		}
		if (!empty($listing_state)) {
			$tax_query[] = array(
				'taxonomy' => 'listing_state',
				'field' => 'slug',
				'terms' => $listing_state
			);
		}

		if($featured == 'true') {
			$meta_query[] = array(
	            'key' => 'homey_featured',
	            'value' => '1',
	            'compare' => '='
	        );
		}

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }

		$tax_count = count( $tax_query );
		if( $tax_count > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		$wp_qry = new WP_Query(
			array(
				'post_type' => 'listing',
				'posts_per_page' => $items_num,
				'ignore_sticky_posts' => 1,
				'post_status' => 'publish',
				'tax_query' => $tax_query,
				'meta_query' => $meta_query
			)
		);

		$token = wp_generate_password(5, false, false);
        if (is_rtl()) {
            $homey_rtl = "true";
        } else {
            $homey_rtl = "false";
        }

		$slider_class = $slider_id = '';
		if($widget_type == 'slider') { ?>

			<script>
				jQuery(document).ready(function(){
			        jQuery('#widget-slider-<?php echo $token; ?>').slick({
			        	rtl: <?php echo esc_attr( $homey_rtl ); ?>,
			            lazyLoad: 'ondemand',
			            infinite: true,
			            speed: 300,
			            slidesToShow: 1,
			            arrows: true,
			            adaptiveHeight: true
			        });
			    });
			</script>

		<?php
		$slider_class = 'widget-slider';	
		$slider_id = 'widget-slider-'.$token;	
		}

		?>

		<div class="widget-body">
	        <div id="<?php echo esc_attr($slider_id); ?>" class="<?php echo esc_attr($slider_class); ?> item-card-view">
			<?php 
			if( $wp_qry->have_posts() ): 
				while( $wp_qry->have_posts() ): $wp_qry->the_post(); 
					
					get_template_part('template-parts/listing/listing-card');
				endwhile; 
			endif;
			wp_reset_postdata(); 
			?>			
    
	        </div>
	    </div><!-- item-list-view -->


		<?php
		echo wp_kses( $after_widget, $allowed_html_array );

	}


	/**
	 * Sanitize widget form values as they are saved
	 **/
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		/* Strip tags to remove HTML. For text inputs and textarea. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['items_num'] = strip_tags( $new_instance['items_num'] );
		$instance['listing_type'] = strip_tags( $new_instance['listing_type'] );
		$instance['room_type'] = strip_tags( $new_instance['room_type'] );
		$instance['listing_city'] = strip_tags( $new_instance['listing_city'] );
		$instance['listing_area'] = strip_tags( $new_instance['listing_area'] );
		$instance['listing_state'] = strip_tags( $new_instance['listing_state'] );
		$instance['widget_type'] = strip_tags( $new_instance['widget_type'] );
		$instance['featured'] = $new_instance['featured'];

		return $instance;

	}

	/**
	 * Back-end widget form
	 **/
	public function form( $instance ) {

		/* Default widget settings. */
		$defaults = array(
			'title' => 'Listings',
			'widget_type' => '',
			'items_num' => '5',
			'listing_type' => '',
			'room_type' => '',
			'listing_city' => '',
			'listing_area' => '',
			'listing_state' => '',
			'featured' => ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		$all = esc_html__('All', 'homey');

		$listing_types = homey_get_taxonomies_slug_array('listing_type', $all);
		$room_type = homey_get_taxonomies_slug_array('room_type', $all);
		$listing_city = homey_get_taxonomies_slug_array('listing_city', $all);
		$listing_area = homey_get_taxonomies_slug_array('listing_area', $all);
		$listing_state = homey_get_taxonomies_slug_array('listing_state', $all);

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'listing_type' ) ); ?>"><?php esc_html_e('Listing Type filter:', 'homey'); ?></label><br>
			<select id="<?php echo esc_attr( $this->get_field_id( 'listing_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_type' ) ); ?>">

				<?php

				foreach ( $listing_types as $key => $value ) :

					echo '<option value="' . $value . '" ' . selected( $instance['listing_type'], $value, true ) . '>' . $key . '</option>';

				endforeach;

				?>

			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'room_type' ) ); ?>"><?php esc_html_e('Room Type filter:', 'homey'); ?></label><br>
			<select id="<?php echo esc_attr( $this->get_field_id( 'room_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'room_type' ) ); ?>">

				<?php

				foreach ( $room_type as $key => $value ) :

					echo '<option value="' . $value . '" ' . selected( $instance['room_type'], $value, true ) . '>' . $key . '</option>';

				endforeach;

				?>

			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'listing_city' ) ); ?>"><?php esc_html_e('Listing City filter:', 'homey'); ?></label><br>
			<select id="<?php echo esc_attr( $this->get_field_id( 'listing_city' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_city' ) ); ?>">

				<?php

				foreach ( $listing_city as $key => $value ) :

					echo '<option value="' . $value . '" ' . selected( $instance['listing_city'], $value, true ) . '>' . $key . '</option>';

				endforeach;

				?>

			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'listing_area' ) ); ?>"><?php esc_html_e('Listing Area filter:', 'homey'); ?></label><br>
			<select id="<?php echo esc_attr( $this->get_field_id( 'listing_area' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_area' ) ); ?>">

				<?php

				foreach ( $listing_area as $key => $value ) :

					echo '<option value="' . $value . '" ' . selected( $instance['listing_area'], $value, true ) . '>' . $key . '</option>';

				endforeach;

				?>

			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'listing_state' ) ); ?>"><?php esc_html_e('Listing State filter:', 'homey'); ?></label><br>
			<select id="<?php echo esc_attr( $this->get_field_id( 'listing_state' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_state' ) ); ?>">

				<?php

				foreach ( $listing_state as $key => $value ) :

					echo '<option value="' . $value . '" ' . selected( $instance['listing_state'], $value, true ) . '>' . $key . '</option>';

				endforeach;

				?>

			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>"><?php esc_html_e('Maximum posts to show:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'items_num' ) ); ?>" value="<?php echo esc_attr( $instance['items_num'] ); ?>" size="1" />
		</p>
		<p>
			<input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'slider' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_type' ) ); ?>" <?php if ($instance["widget_type"] == 'slider')  echo 'checked="checked"'; ?> value="slider" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'slider' ) ); ?>"><?php esc_html_e( 'Display Listings as Slider', 'homey' ); ?></label><br />

			<input type="radio" id="<?php echo esc_attr( $this->get_field_id( 'entries' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_type' ) ); ?>" <?php if ($instance["widget_type"] == 'entries') echo 'checked="checked"'; ?> value="entries" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'entries' ) ); ?>"><?php esc_html_e( 'Display Listings as List', 'homey' ); ?></label>
		</p>

		<p>
			<input type="checkbox" <?php checked( $instance[ 'featured' ], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'featured' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>"><?php esc_html_e('Show only featured:', 'homey'); ?></label>
		</p>

		<?php
	}

}

if ( ! function_exists( 'Homey_listing_loader' ) ) {
	function Homey_listing_loader (){
		register_widget( 'Homey_listing' );
	}
	add_action( 'widgets_init', 'Homey_listing_loader' );
}