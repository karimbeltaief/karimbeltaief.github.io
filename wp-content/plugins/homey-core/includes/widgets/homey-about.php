<?php
class homey_about_site extends WP_Widget {
	
	
	/**
	 * Register widget
	**/
	public function __construct() {
		
		parent::__construct(
	 		'homey_about_widget', // Base ID
			esc_html__( 'Homey: About Site', 'homey' ), // Name
			array( 'description' => esc_html__( 'About site widget', 'homey' ), 'classname' => 'widget-about' ) // Args
		);
		
	}

	
	/**
	 * Front-end display of widget
	**/
	public function widget( $args, $instance ) {
				
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		$img_url = $instance['img_url'];
		$about_text = $instance['about_text'];
		$more_url = $instance['more_url'];

		$allowed_html_array = array(
			'div' => array(
				'id' => array(),
				'class' => array()
			),
			'h3' => array(
				'class' => array()
			)
		);
		
		echo wp_kses( $before_widget, $allowed_html_array );
			
			
			if ( $title ) echo wp_kses( $before_title, $allowed_html_array ) . $title . wp_kses( $after_title, $allowed_html_array );
			?>
           
            <div class="widget-body">
                <?php if( !empty($img_url) ) { ?>
                <p>
                    <img src="<?php echo esc_url( $img_url ); ?>" alt="img">
                </p>
                <?php } ?>
                
                <p><?php echo wp_kses_post( $about_text ); ?></p>
                <?php if( !empty($more_url) ) { ?>
                <p class="read"><a href="<?php echo esc_url($more_url);?>"><?php esc_html_e('Read more', 'homey'); ?> <i class="fa fa-caret-right"></i></a></p>
                <?php } ?>
            </div>

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
		$instance['img_url'] = strip_tags( $new_instance['img_url'] );
		$instance['about_text'] = strip_tags( $new_instance['about_text'] );
		$instance['more_url'] = strip_tags( $new_instance['more_url'] );
		
		return $instance;
		
	}
	
	
	/**
	 * Back-end widget form
	**/
	public function form( $instance ) {
		
		/* Default widget settings. */
		$defaults = array(
			'title' => 'About Site',
			'img_url' => '',
			'about_text' => '',
			'more_url' => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'img_url' ) ); ?>"><?php esc_html_e('Image Url:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'img_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'img_url' ) ); ?>" value="<?php echo esc_url( $instance['img_url'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'about_text' ) ); ?>"><?php esc_html_e('Text:', 'homey'); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'about_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'about_text' ) ); ?>"><?php echo wp_kses_post( $instance['about_text'] ); ?></textarea>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'more_url' ) ); ?>"><?php esc_html_e('Read More Link:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'more_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'more_url' ) ); ?>" value="<?php echo esc_url( $instance['more_url'] ); ?>" class="widefat" />
		</p>
		
	<?php
	}

}

if ( ! function_exists( 'homey_about_site_loader' ) ) {
    function homey_about_site_loader (){
     register_widget( 'homey_about_site' );
    }
     add_action( 'widgets_init', 'homey_about_site_loader' );
}