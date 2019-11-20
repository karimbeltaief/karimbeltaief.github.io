<?php
/*
 * Widget Name: Featured Listings
 * Version: 1.0
 * Author: Waqas Riaz
 * Author URI: http://favethemes.com/
 */

class Homey_listing_listview extends WP_Widget {

	/**
	 * Register widget
	 **/
	public function __construct() {

		parent::__construct(
			'Homey_listing_listview', // Base ID
			esc_html__( 'Homey: Listing list', 'homey' ), // Name
			array( 'description' => esc_html__( 'Show listing with list view', 'homey' ), 'classname' => 'widget-list-properties widget-latest-properties') // Args
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

		$cgl_meta = homey_option('cgl_meta');
		$cgl_beds = homey_option('cgl_beds');
		$cgl_baths = homey_option('cgl_baths');
		$cgl_guests = homey_option('cgl_guests');
		$cgl_types = homey_option('cgl_types');

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
				'meta_query' => $meta_query,
			)
		);
		?>

		<div class="widget-body">
			<?php 
			$homey_prefix = 'homey_';
			$bedrooms_icon = homey_option('lgc_bedroom_icon'); 
			$bathroom_icon = homey_option('lgc_bathroom_icon'); 
			$guests_icon = homey_option('lgc_guests_icon');
			$price_separator = homey_option('currency_separator');

			if(!empty($bedrooms_icon)) {
			    $bedrooms_icon = '<i class="'.esc_attr($bedrooms_icon).'"></i>';
			}
			if(!empty($bathroom_icon)) {
			    $bathroom_icon = '<i class="'.esc_attr($bathroom_icon).'"></i>';
			}
			if(!empty($guests_icon)) {
			    $guests_icon = '<i class="'.esc_attr($guests_icon).'"></i>';
			}
			if( $wp_qry->have_posts() ): 
				while( $wp_qry->have_posts() ): $wp_qry->the_post(); 
					$listing_images = get_post_meta( get_the_ID(), $homey_prefix.'listing_images', false );
					$address        = get_post_meta( get_the_ID(), $homey_prefix.'listing_address', true );
					$bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
					$guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
					$beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
					$baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
					$night_price          = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
					$listing_author = homey_get_author();
					$enable_host = homey_option('enable_host');
					$compare_favorite = homey_option('compare_favorite');
					$rating = homey_option('rating');
					$listing_type = homey_taxonomy_simple('listing_type');

					$rating = homey_option('rating');
					$total_rating = get_post_meta( get_the_ID(), 'listing_total_rating', true );
					$listing_rating = homey_get_review_stars($total_rating, true, false);


			?>

					<div class="item-list-view">
			            <div class="media property-item">
			                <div class="media-left">
			                    <div class="item-media item-media-thumb">
			                        
			                        <?php homey_listing_featured(get_the_ID()); ?>

			                        <a href="<?php the_permalink(); ?>">
					                <?php
					                if( has_post_thumbnail( get_the_ID() ) ) {
					                    the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
					                }else{
					                    homey_image_placeholder( 'homey-listing-thumb' );
					                }
					                ?>
					                </a>
			                    </div>
			                </div>
			                <div class="media-body item-body clearfix">
			                    <div class="item-title-head">
			                        <div class="title-head-left">
			                            <h2 class="title">
			                            	<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			                        	</h2>

			                        	<?php if(!empty($night_price)) { ?>
			                            <span class="item-price">
			                            	<?php echo homey_formatted_price($night_price, true, false); ?><?php echo esc_attr($price_separator); ?><?php echo homey_option('glc_day_night_label');?>		
			                            </span>
			                        	<?php } ?>
			                        	
			                            <?php 
			                            if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
			                            <span class="list-inline rating stars">
			                                 <?php echo $listing_rating; ?>
			                            </span>
			                        	<?php } ?>
			                        </div>
			                    </div>

			                    <?php if($cgl_meta != 0) { ?>
			                    <ul class="item-amenities">
			                        <?php if($cgl_beds != 0) { ?>
			                        <li>
			                        	<?php echo $bedrooms_icon; ?>
			                        	<span class="total-beds"><?php echo esc_attr($bedrooms); ?></span>
			                        </li>
			                        <?php } ?>

			                        <?php if($cgl_baths != 0) { ?>
			                        <li>
			                        	<?php echo $bathroom_icon; ?>
			                        	<span class="total-baths"><?php echo esc_attr($baths); ?></span>
			                        </li>
			                        <?php } ?>

			                        <?php if($cgl_guests!= 0) { ?>
			                        <li>
			                        	<?php echo $guests_icon; ?>
			                        	<span class="total-guests"><?php echo esc_attr($guests); ?></span>
			                        </li>
			                        <?php } ?>

			                        <?php if($cgl_types != 0) { ?>
			                        <li><?php echo esc_attr($listing_type); ?></li>
			                        <?php } ?>
			                    </ul>
			                	<?php } ?>
			             
			                </div>
			            </div>
			        </div>
					
			<?php		
				endwhile; 
			endif;
			wp_reset_postdata(); 
			?>			
    
	    </div><!-- widget-body -->


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
			'items_num' => '3',
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
			<input type="checkbox" <?php checked( $instance[ 'featured' ], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'featured' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>"><?php esc_html_e('Show only featured:', 'homey'); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>"><?php esc_html_e('Maximum posts to show:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'items_num' ) ); ?>" value="<?php echo esc_attr( $instance['items_num'] ); ?>" size="1" />
		</p>

		<?php
	}

}

if ( ! function_exists( 'Homey_listing_listview_loader' ) ) {
	function Homey_listing_listview_loader (){
		register_widget( 'Homey_listing_listview' );
	}
	add_action( 'widgets_init', 'Homey_listing_listview_loader' );
}