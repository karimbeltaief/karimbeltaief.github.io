<?php
/*
 * Widget Name: Latest Review
 * Version: 1.0
 * Author: Waqas Riaz
 * Author URI: http://favethemes.com/
 */

class Homey_reviews extends WP_Widget {

	/**
	 * Register widget
	 **/
	public function __construct() {

		parent::__construct(
			'Homey_latest_reviews', // Base ID
			esc_html__( 'Homey: Reviews', 'homey' ), // Name
			array( 'description' => esc_html__( 'Show latest reviews', 'homey' ), 'classname' => 'widget-latest-reviews') // Args
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

		echo wp_kses( $before_widget, $allowed_html_array );


		if ( $title ) echo wp_kses( $before_title, $allowed_html_array ) . $title . wp_kses( $after_title, $allowed_html_array );
		

		$wp_qry = new WP_Query(
			array(
				'post_type' => 'homey_review',
				'posts_per_page' => $items_num,
				'ignore_sticky_posts' => 1,
				'post_status' => 'publish'
			)
		);
		?>

		<div class="widget-body">

			<?php 
			if( $wp_qry->have_posts() ): 
				while( $wp_qry->have_posts() ): $wp_qry->the_post(); 
					$listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);
					$rating = get_post_meta(get_the_ID(), 'homey_rating', true);
					$review_author = homey_get_author('70', '70', 'img-circle');
			?>

					<div class="review-block">
			            <div class="media">
			                <div class="media-left">
			                    <a class="media-object">
									<?php echo $review_author['photo']; ?>
								</a>
			                </div>
			                <div class="media-body media-middle">
			                    <div class="msg-user-info">
			                        <div class="msg-user-left">
			                            <h2 class="title"><a href="<?php echo get_permalink($listing_id); ?>/#review-<?php the_ID();?>"><?php echo get_the_title($listing_id); ?></a></h2>

			                            <div class="message-date">
			                                
			                                	<i class="fa fa-calendar-o"></i><?php printf( __( '%s ago', 'homey' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
			                                
			                                
			                                <span class="rating">
			                                	<?php echo homey_get_review_stars($rating, true, true, false ); ?>
			                            	</span>
			                            </div>
			                        </div>
			                    </div>
			                    <?php echo homey_get_content(15); ?>
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

		$instance = array();

		/* Strip tags to remove HTML. For text inputs and textarea. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['items_num'] = strip_tags( $new_instance['items_num'] );
		

		return $instance;

	}

	/**
	 * Back-end widget form
	 **/
	public function form( $instance ) {

		/* Default widget settings. */
		$defaults = array(
			'title' => 'Latest Reviews',
			'items_num' => '3',
			'listing_type' => ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>"><?php esc_html_e('Maximum posts to show:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'items_num' ) ); ?>" value="<?php echo esc_attr( $instance['items_num'] ); ?>" size="1" />
		</p>

		<?php
	}

}

if ( ! function_exists( 'Homey_reviews_loader' ) ) {
	function Homey_reviews_loader (){
		register_widget( 'Homey_reviews' );
	}
	add_action( 'widgets_init', 'Homey_reviews_loader' );
}