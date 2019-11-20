<?php
class homey_contact_us extends WP_Widget {
	
	
	/**
	 * Register widget
	**/
	public function __construct() {
		
		parent::__construct(
	 		'homey_contact', // Base ID
			esc_html__( 'Homey: Contact Us', 'homey' ), // Name
			array( 'description' => esc_html__( 'Contact us widget', 'homey' ), 'classname' => 'widget-contact' ) // Args
		);
		
	}

	
	/**
	 * Front-end display of widget
	**/
	public function widget( $args, $instance ) {
				
		extract( $args );

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
		$about_text = $instance['about_text'];
		$address = $instance['address'];
		$phone = $instance['phone'];
		$fax = isset($instance['fax']) ? $instance['fax'] : '';
		$email = $instance['email'];
		$more_url = $instance['more_url'];
		
		echo wp_kses( $before_widget, $allowed_html_array );
			
			
			if ( $title ) echo wp_kses( $before_title, $allowed_html_array ) . $title . wp_kses( $after_title, $allowed_html_array );
			?>
           
            <div class="widget-body">
                <div class="contact_text"><?php echo wp_kses_post( $about_text ); ?></div>
                <ul class="list-unstyled">
                    <?php if( !empty($address) ) { ?>
                    <li><i class="fa fa-map-marker"></i> <?php echo esc_attr( $address ); ?></li>
                    <?php } ?>

                    <?php if( !empty($phone) ) { ?>
                    <li><i class="fa fa-phone-square"></i> <?php echo esc_attr( $phone ); ?></li>
                    <?php } ?>

					<?php if( !empty($fax) ) { ?>
						<li><i class="fa fa-fax"></i> <?php echo esc_attr( $fax ); ?></li>
					<?php } ?>

                    <?php if( !empty($email) ) { ?>
                    <li><i class="fa fa-envelope-o"></i> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_attr( $email ); ?></a></li>
                    <?php } ?>
                    <?php if( !empty($more_url) ) { ?>
                	<li><i class="fa fa-arrow-circle-o-right"></i> <a href="<?php echo esc_url( $more_url ); ?>"><?php esc_html_e( 'Contact us', 'homey' ); ?></a></li>
                	<?php } ?>
                </ul>
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
		$instance['about_text'] = $new_instance['about_text'];
		$instance['phone'] = strip_tags( $new_instance['phone'] );
		$instance['fax'] = strip_tags( $new_instance['fax'] );
		$instance['email'] = strip_tags( $new_instance['email'] );
		$instance['address'] = strip_tags( $new_instance['address'] );
		$instance['more_url'] = strip_tags( $new_instance['more_url'] );
		
		return $instance;
		
	}
	
	
	/**
	 * Back-end widget form
	**/
	public function form( $instance ) {
		
		/* Default widget settings. */
		$defaults = array(
			'title' => 'Contact Us',
			'address' => '',
			'phone' => '',
			'fax' => '',
			'email' => '',
			'about_text' => '',
			'more_url' => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'about_text' )); ?>"><?php esc_html_e('Text:', 'homey'); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'about_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'about_text' ) ); ?>"><?php echo esc_textarea( $instance['about_text'] ); ?></textarea>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'address' )); ?>"><?php esc_html_e('Address:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'address' )); ?>" name="<?php echo esc_attr( $this->get_field_name( 'address' )); ?>" value="<?php echo esc_attr( $instance['address']); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>"><?php esc_html_e('Phone Number:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'phone' ) ); ?>" value="<?php echo esc_attr( $instance['phone'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'fax' ) ); ?>"><?php esc_html_e('Fax:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'fax' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fax' ) ); ?>" value="<?php echo esc_attr( $instance['fax'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>"><?php esc_html_e('Email:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'email' ) ); ?>" value="<?php echo esc_attr( $instance['email'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'more_url' ) ); ?>"><?php esc_html_e('Link:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'more_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'more_url' ) ); ?>" value="<?php echo esc_url( $instance['more_url'] ); ?>" class="widefat" />
		</p>
		
	<?php
	}

}

if ( ! function_exists( 'homey_contact_us_loader' ) ) {
    function homey_contact_us_loader (){
     register_widget( 'homey_contact_us' );
    }
     add_action( 'widgets_init', 'homey_contact_us_loader' );
}