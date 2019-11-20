<?php
class homey_currency_switcher extends WP_Widget {
	
	
	/**
	 * Register widget
	**/
	public function __construct() {
		
		parent::__construct(
	 		'homey_currency_switcher', // Base ID
			esc_html__( 'Homey: Currency Switcher', 'homey' ), // Name
			array( 'description' => esc_html__( 'Show currency switcher', 'homey' ), 'classname' => 'widget-currency-switcher' ) // Args
		);
		
	}

	
	/**
	 * Front-end display of widget
	**/
	public function widget( $args, $instance ) {
				
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		
		echo $before_widget;
			
			
			if ( $title ) echo $before_title . $title . $after_title;
			?>
           
            <div class="widget-body">
                <?php
                $currency_switcher_enable = homey_option('currency_converter');
				$default_currency = homey_option('default_currency');
				$is_multi_currency = 0;

				if( $currency_switcher_enable != 0 && $is_multi_currency != 1 ) {
				    if (class_exists('FCC_Currencies')) {

				        $supported_currencies = '';

				        $currencies = FCC_Currencies::get_currency_codes();

				        $current_currency = homey_get_wpc_current_currency();

				        if($currencies) {
				            foreach ($currencies as $currency) {
				                
				                $supported_currencies .='<option '.selected($currency->currency_code, $current_currency, false).' value="' . esc_attr($currency->currency_code) . '">'.esc_attr($currency->currency_code).'</option>';
				            }
				        }
				        ?>
				        <select class="selectpicker homey-currency-switcher" data-dropup-auto="false">
				        	<?php echo $supported_currencies; ?>
				        </select>

				     <?php
				    }
				}
                ?>
            </div>

	    <?php 
		echo $after_widget;
		
	}
	
	
	/**
	 * Sanitize widget form values as they are saved
	**/
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();

		/* Strip tags to remove HTML. For text inputs and textarea. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		return $instance;
		
	}
	
	
	/**
	 * Back-end widget form
	**/
	public function form( $instance ) {
		
		/* Default widget settings. */
		$defaults = array(
			'title' => 'Currency Switcher',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		

		
	<?php
	}

}

if ( ! function_exists( 'homey_currency_switcher_loader' ) ) {
    function homey_currency_switcher_loader (){
     register_widget( 'homey_currency_switcher' );
    }
     add_action( 'widgets_init', 'homey_currency_switcher_loader' );
}