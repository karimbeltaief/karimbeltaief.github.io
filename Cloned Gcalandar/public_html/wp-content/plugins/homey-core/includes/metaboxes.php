<?php
/*-----------------------------------------------------------------------------------*/
/*	Add Metaboxes
/*-----------------------------------------------------------------------------------*/

add_action( 'load-post.php', 'homey_meta_boxes_setup' );
add_action( 'load-post-new.php', 'homey_meta_boxes_setup' );

/* Meta box setup function. */
if ( !function_exists( 'homey_meta_boxes_setup' ) ) :
	function homey_meta_boxes_setup() {
		global $typenow;

		if ( $typenow == 'homey_reservation' ) {
			add_action( 'add_meta_boxes', 'homey_add_reservation_meta' );
			//add_action( 'save_post', 'homey_save_page_metaboxes', 10, 2 );

		} elseif ( $typenow == 'page' ) {
			add_action( 'add_meta_boxes', 'homey_load_page_metaboxes' );
			add_action( 'save_post', 'homey_save_page_metaboxes', 10, 2 );
		}

	}
endif;


if ( !function_exists( 'homey_add_reservation_meta' ) ) :
	function homey_add_reservation_meta() {
		add_meta_box('homey-reservation-meta', esc_html__('Reservation',   'homey'), 'homey_reservation_meta', 'homey_reservation', 'normal', 'high' );
	}
endif;

if ( !function_exists( 'homey_load_page_metaboxes' ) ) :
	function homey_load_page_metaboxes() {
		add_meta_box('homey-page-metaboxes', esc_html__('Page Sidebar',   'homey'), 'homey_page_metaboxes', 'page', 'side', 'high' );
	}
endif;

/*-----------------------------------------------------------------------------------*/
/*  Page sidebar metaboxes
/*-----------------------------------------------------------------------------------*/

if( !function_exists('homey_reservation_meta') ) {

	function homey_reservation_meta( $object, $box ) { 
		$homey_local = homey_get_localization();
		$homey_prefix = 'homey_';
		$reservationID = $object->ID;

		$post = get_post($reservationID);    

		$res_meta   = get_post_meta($reservationID, 'reservation_meta', true);
		$is_hourly = get_post_meta($reservationID, 'is_hourly', true);

	    $reservation_status = get_post_meta($reservationID, 'reservation_status', true);
	    $upfront_payment = get_post_meta($reservationID, 'reservation_upfront', true);
	    $upfront_payment = homey_formatted_price($upfront_payment);
	    $payment_link = homey_get_template_link_2('template/dashboard-payment.php');

	    if($is_hourly == 'yes') {
			$check_in_date = $res_meta['check_in_date'];
		    $start_hour = $res_meta['start_hour'];
		    $end_hour = $res_meta['end_hour'];
		    if($res_meta['no_of_hours'] > 1) {
                $hour_label = esc_html__('Hours', 'homey');
            } else {
                $hour_label = esc_html__('Hour', 'homey');
            }
	    } else {
		    $check_in = get_post_meta($reservationID, 'reservation_checkin_date', true);
		    $check_out = get_post_meta($reservationID, 'reservation_checkout_date', true);
		}


	    $guests = get_post_meta($reservationID, 'reservation_guests', true);
	    $listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
	    $pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
	    

	    $renter_msg = isset($res_meta['renter_msg']) ? $res_meta['renter_msg'] : '';

	    $renter_id = get_post_meta($reservationID, 'listing_renter', true);
	    $renter_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $renter_id);

	    $owner_id = get_post_meta($reservationID, 'listing_owner', true);
	    $owner_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $owner_id);

	    $guests_label = $homey_local['guest_label'];
	    if($guests > 1) {
	        $guests_label = $homey_local['guests_label'];
	    }
	?>
		
		<div class="wrap">
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<td class="manage-column">
						<strong><?php echo esc_attr($homey_local['date_label']); ?>:</strong>
					</td>
					<td>
						<?php echo esc_attr( get_the_date( get_option( 'date_format' ), $reservationID ));?>
						<br>
                        <?php echo esc_attr( get_the_date( get_option( 'time_format' ), $reservationID ));?>
					</td>
				</tr>
				<tr>
					<td class="manage-column">
						<strong><?php esc_html_e('From', 'homey'); ?>:</strong>
					</td>
					<td>
						<?php echo esc_attr($renter_info['name']); ?>
					</td>
				</tr>
				<tr>
					<td class="manage-column">
						<strong><?php esc_html_e('Listing', 'homey'); ?>:</strong>
					</td>
					<td>
						<?php echo get_the_title($listing_id); ?>
					</td>
				</tr>
			</table>
		</div>
		
		
		<div class="wrap">
			<h4><?php esc_html_e('Details', 'homey'); ?></h4>
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<td class="manage-column">
						<strong><?php echo esc_attr($homey_local['check_In']); ?>:</strong>
					</td>
					<td> 
                         <?php 
                         if($is_hourly == 'yes') {
                         	echo esc_attr($check_in_date).' '.esc_html__('at', 'homey-core').' '.date('g:i a',strtotime($start_hour));
                         } else {
                         	echo esc_attr($check_in);
                         }
                         ?>
					</td>
				</tr>
				<tr>
					<td class="manage-column">
						<strong><?php echo esc_attr($homey_local['check_Out']); ?>:</strong>
					</td>
					<td>
						<?php
						if($is_hourly == 'yes') {
                         	echo esc_attr($check_in_date).' '.esc_html__('at', 'homey-core').' '.date('g:i a',strtotime($end_hour));
                         } else {
                         	echo esc_attr($check_out);
                         }
						?>
					</td>
				</tr>

				<?php if($is_hourly == 'yes') { ?>
				<tr>
					<td class="manage-column">
						<strong><?php echo esc_attr($hour_label); ?>:</strong>
					</td>
					<td>
						<?php echo esc_attr($res_meta['no_of_hours']); ?>
					</td>
				</tr>
				<?php } else { ?>
					<tr>
						<td class="manage-column">
							<strong><?php echo esc_attr($homey_local['nights_label']); ?>:</strong>
						</td>
						<td>
							<?php echo esc_attr($res_meta['no_of_days']); ?>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td class="manage-column">
						<strong><?php echo esc_attr($guests_label); ?>:</strong>
					</td>
					<td>
						<?php echo esc_attr($guests); ?>
					</td>
				</tr>
				<?php if(!empty($res_meta['additional_guests'])) { ?>
                <tr>
					<td class="manage-column">
						<strong><?php echo esc_attr($homey_local['addinal_guest_text']); ?>:</strong>
					</td>
					<td>
						<?php echo esc_attr($res_meta['additional_guests']); ?>
					</td>
				</tr>
                <?php } ?>
				<!-- <tr>
					<td class="manage-column">
						<strong>Children:</strong>
					</td>
					<td>
						0
					</td>
				</tr> -->
			</table>
		</div>

		<?php if(!empty($renter_msg)) { ?>
		<div class="wrap">
			<h4><?php esc_html_e('Notes', 'homey'); ?></h4>
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<td>
						<?php echo esc_attr($renter_msg); ?>
					</td>
				</tr>
			</table>
		</div>
		<?php } ?>
		
		<div class="wrap">
			<h4><?php echo esc_attr($homey_local['payment_label']); ?></h4>
			<table class="wp-list-table widefat fixed striped">
				<?php 
				if($is_hourly == 'yes') { 
					echo homey_calculate_hourly_reservation_cost_admin($reservationID);
				} else {
					echo homey_calculate_reservation_cost_admin($reservationID); 
				}
				?>
			</table>
		</div>
		

		<?php
	}
}


/*-----------------------------------------------------------------------------------*/
/*  Page sidebar metaboxes
/*-----------------------------------------------------------------------------------*/

if( !function_exists('homey_page_metaboxes') ):

	function homey_page_metaboxes( $object, $box ) {
		global $wp_registered_sidebars;

		$homey_meta = homey_get_sidebar_meta( $object->ID );
		wp_nonce_field( plugin_basename( __FILE__ ), 'homey_page_nonce' );

		$sidebar = $homey_meta['homey_sidebar'];
		$sidebar_position = $homey_meta['sidebar_position'];
		$selected_sidebar = $homey_meta['selected_sidebar'];
		?>
		<div class="homey_meta_control custom_sidebar_js">
			<p><?php esc_html_e('Show Sidebar?', 'homey' ); ?></p>
			<select id="homey_page_sidebar" name="homey[homey_sidebar]" class="homey-dropdown widefat">
				<option value="no" <?php selected( $sidebar, 'no' );?>><?php esc_html_e( 'No', 'homey' ); ?></option>
				<option value="yes" <?php selected( $sidebar, 'yes' );?>><?php esc_html_e( 'Yes', 'homey' ); ?></option>
			</select>
		</div>

		<div class="homeythemes_meta_control homey_selected_sidebar" style="display: none;">
			<p><?php esc_html_e('Sidebar Position', 'homey' ); ?></p>
			<select name="homey[sidebar_position]" class="homey-dropdown widefat">
				<option value="right" <?php selected( $sidebar_position, 'right' );?>><?php esc_html_e( 'Right', 'homey' ); ?></option>
				<option value="left" <?php selected( $sidebar_position, 'left' );?>><?php esc_html_e( 'Left', 'homey' ); ?></option>
			</select>
		</div>

		<div class="homeythemes_meta_control homey_selected_sidebar" style="display: none;">
			<p><?php esc_html_e('Select Sidebar', 'homey' ); ?></p>
			<select name="homey[selected_sidebar]" class="homey-dropdown widefat">
				<option value=""><?php echo esc_html__('Default', 'homey'); ?></option>
				<?php
				foreach( $wp_registered_sidebars as $sidebar ) { ?>
					<option value="<?php echo esc_attr($sidebar['id']); ?>" <?php selected( $selected_sidebar, $sidebar['id'] );?>><?php echo esc_attr($sidebar['name']); ?></option>
					<?php
				}
				?>
			</select>
		</div>

		<?php
	}

endif; // end   homey_page_metaboxes

/*-----------------------------------------------------------------------------------*/
/* Save sidebar page Meta
/*-----------------------------------------------------------------------------------*/
if ( !function_exists( 'homey_save_page_metaboxes' ) ) :
	function homey_save_page_metaboxes( $post_id, $post ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;


		if ( $post->post_type == 'page' && isset( $_POST['homey'] ) ) {
			$post_type = get_post_type_object( $post->post_type );
			if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
				return $post_id;

			$homey_meta = array();

			$homey_meta['homey_sidebar'] = isset( $_POST['homey']['homey_sidebar'] ) ? $_POST['homey']['homey_sidebar'] : 'no';

			$homey_meta['sidebar_position'] = isset( $_POST['homey']['sidebar_position'] ) ? $_POST['homey']['sidebar_position'] : 'right';

			$homey_meta['selected_sidebar'] = isset( $_POST['homey']['selected_sidebar'] ) ? $_POST['homey']['selected_sidebar'] : 'default-sidebar';

			update_post_meta( $post_id, '_homey_sidebar_meta', $homey_meta );

		}
	}
endif;