<?php
if(!function_exists('homey_generate_ical_export_link')) {
	function homey_generate_ical_export_link($listing_id) {
		$homey_ical_id = get_post_meta($listing_id, 'homey_ical_id',true  );
        if($homey_ical_id=='') {
            $homey_ical_id= md5(uniqid(mt_rand(), true));
            update_post_meta($listing_id, 'homey_ical_id', $homey_ical_id);
        }

        $ican_feeds_page_link = homey_get_template_link_2('template/template-ical.php');
        if(!empty($ican_feeds_page_link)) {
        	$ican_feeds_page_link =  esc_url_raw ( add_query_arg( 'iCal', $homey_ical_id, $ican_feeds_page_link) ) ;
        	return $ican_feeds_page_link;
        }
        return '';
	}
}

if(!function_exists('homey_get_listing_id_by_ical_id')) {
	function homey_get_listing_id_by_ical_id($ical_id){

		$args = array(
			'post_type'   => 'listing',
			'post_status' => 'publish',
			'meta_query'  => array(
				array(
					'key' 	  => 'homey_ical_id',
					'value'   => $ical_id,
					'compare' => '=',
				)
			),
		);

		$listing_id = '';

	    $query = new WP_Query($args);
	    if($query->have_posts()):
	    	while ($query->have_posts()): $query->the_post();
	    		$listing_id = get_the_ID();
	    	endwhile;
	    endif;
	    wp_reset_postdata();

	    return $listing_id;   
	}
}

if(!function_exists('homey_get_booked_dates_for_icalendar')) {
	function homey_get_booked_dates_for_icalendar($listing_id) {
	    $args = array(
	            'post_type'        => 'homey_reservation',
	            'post_status'      => 'any',
	            'posts_per_page'   => -1,
	            'meta_query' => array(
	                    array(
	                        'key'       => 'reservation_listing_id',
	                        'value'     => $listing_id,
	                        'type'      => 'NUMERIC',
	                        'compare'   => '='
	                    ),
	                    array(
	                        'key'       =>  'reservation_status',
	                        'value'     =>  'booked',
	                        'compare'   =>  '='
	                    )
	                )
	            );
	    
	    $return_feeds = ''; 

	    $wpQry = new WP_Query($args);
	        
	    if ($wpQry->have_posts()) { $return_feeds = '';

	        while ($wpQry->have_posts()): $wpQry->the_post();

	            $resID = get_the_ID();

	            $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
	            $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

	            $check_in       =   new DateTime($check_in_date);
	            $check_in_unix  =   $check_in->getTimestamp();
	            $check_out      =   new DateTime($check_out_date);
	            $check_out_unix =   $check_out->getTimestamp();

	            $return_feeds = $return_feeds.homey_generate_ical_event($check_in_unix, $check_out_unix, $resID);

	        endwhile;
	        wp_reset_postdata();
	    }        
	    return $return_feeds;
	}
}

if(!function_exists('homey_generate_ical_event')) {
	function homey_generate_ical_event($check_in_unix, $check_out_unix, $resID) {
	$summary = $_SERVER['HTTP_HOST']." booking id ".$resID;

	$ical_event = "
BEGIN:VEVENT
SUMMARY:".homey_string_escaped($summary)."
DTSTART:".homey_convert_date_to_cal($check_in_unix)."
DTEND:".homey_convert_date_to_cal($check_out_unix)."
UID:" . md5(uniqid(mt_rand(), true)) . "@".$_SERVER['HTTP_HOST']."
DTSTAMP:" .homey_convert_date_to_cal(time())."
END:VEVENT
";

	return $ical_event;

	}
}


add_action( 'wp_ajax_homey_add_ical_feeds', 'homey_add_ical_feeds' );
if(!function_exists('homey_add_ical_feeds')) {
	function homey_add_ical_feeds() {
		global $current_user;
        $current_user 	= wp_get_current_user();
        $userID       	= $current_user->ID;
        $local 			= homey_get_localization();
        $store_feeds_array = array();
        $temp_array 	= array();
        $allowded_html 	= array();

        $listing_id 	= intval($_POST['listing_id']);
        $the_post  		= get_post($listing_id);
        $post_owner 	= $the_post->post_author;
        $ical_feed_name = $_POST['ical_feed_name'];
        $ical_feed_url 	= $_POST['ical_feed_url'];

        if ( !is_user_logged_in() || $userID === 0 ) {   
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => esc_html__('Login required', 'homey') 
                ) 
             );
             wp_die();
        }

        if(!is_numeric($listing_id)) {
        	echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey') 
                ) 
             );
             wp_die();
        }

        if($userID != $post_owner) {
        	echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => esc_html__("You don't have rights to do this.", 'homey') 
                ) 
             );
             wp_die();
        }

        foreach ($ical_feed_url as $key => $value) {
        	if(!empty($value)) {
        		$temp_array['feed_url'] = esc_url_raw($value);
        		$temp_array['feed_name'] = esc_html($ical_feed_name[$key]);
        		$store_feeds_array[] = $temp_array;
        	}
        }

        if( !empty($store_feeds_array)) {
            update_post_meta($listing_id, 'homey_ical_feeds_meta', $store_feeds_array);
            homey_import_icalendar_feeds($listing_id);
        }

        $dashboard_submission = homey_get_template_link('template/dashboard-submission.php');
        $return_url  = add_query_arg( 
            array(
                'edit_listing' => $listing_id,
                'tab' => 'calendar'
            ),
            $dashboard_submission 
        );

        echo json_encode( 
            array( 
                'success' => true, 
                'message' => $local['feeds_imported'],
                'url' => $return_url,
            ) 
         );
         wp_die();

	}
}

if(!function_exists('homey_import_icalendar_feeds')) {
	function homey_import_icalendar_feeds($listing_id) {
		$ical_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta', true); 

		foreach ($ical_feeds_meta as $key => $value) {
			$feed_name = $value['feed_name'];
			$feed_url  = $value['feed_url'];
			//echo $feed_name.' = '.$feed_url.'<br/>';
			homey_insert_icalendar_feeds($listing_id, $feed_name, $feed_url);
		}
		/*echo '<pre>';
		print_r($ical_feeds_meta);*/
	}
}
//homey_import_icalendar_feeds(11263);

if(!function_exists('homey_insert_icalendar_feeds')) {
	function homey_insert_icalendar_feeds($listing_id, $feed_name, $feed_url) {

	    if(empty($feed_url) || !intval($listing_id) || filter_var($feed_url, FILTER_VALIDATE_URL) === FALSE) {
	        return;
	    }

	    $temp_array        = array();
	    $events_data_array = array();

	    $ical   = new ICal($feed_url);
	    $events = $ical->events();
	    //$ical_timezone = $ical->cal['VCALENDAR']['X-WR-TIMEZONE'];
	    
	    foreach ($events as $event) {

	        $start_time_unix = $end_time_unix= '';

	        if(isset($event['DTSTART'])) {
	            $start_time_unix = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
	        }

	        if(isset($event['DTEND'])) {
	            $end_time_unix = $ical->iCalDateToUnixTimestamp($event['DTEND']);
	        }

	        if(!empty($start_time_unix) && !empty($end_time_unix) && !empty($feed_name)) {
	             
	            $temp_array['start_time_unix'] = $start_time_unix;
	            $temp_array['end_time_unix']   = $end_time_unix;
	            $temp_array['feed_name']       = $feed_name;

	            $events_data_array[]           = $temp_array;
	        }   
	    }

	    $booked_dates_array  = get_post_meta($listing_id, 'reservation_dates',true);

	    if(!empty($booked_dates_array)) {
		    $events_data_to_unset     = array_keys( $booked_dates_array, $events_data_array[0]['feed_name'] );
		    foreach ($events_data_to_unset as $key=>$timestamp){
		        unset($booked_dates_array[$timestamp]);
		    }
		    update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);
		}

	    foreach($events_data_array as $data) {
	        $start_time_unix = $data['start_time_unix'];
	        $end_time_unix = $data['end_time_unix'];
	        $feed_name = $data['feed_name'];
	        homey_add_listing_booking_dates($listing_id, $start_time_unix, $end_time_unix, $feed_name);   
	    }

	    /*echo '<pre>';
	    print_r($events_data_array);*/
	    
	}
}

if(!function_exists('homey_add_listing_booking_dates')) {
	function  homey_add_listing_booking_dates($listing_id, $start_time_unix, $end_time_unix, $feed_name){
	    $now = time();
	    $daysAgo = $now-3*24*60*60;

	    //change date format and remove hours, mins
	    $start_date      = gmdate("Y-m-d 0:0:0", $start_time_unix);
	    $start_date_unix = strtotime($start_date);
	    $end_date        = gmdate("Y-m-d 0:0:0", $end_time_unix);
	    $end_date_unix   = strtotime($end_date);

	    if ($end_date_unix < $daysAgo){
	        return;
	    }
	    
	    $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true );
	  
	    if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
	        $booked_dates_array  = array();
	    }
	    
	    
	    $start_date_unix    = gmdate("Y-m-d\TH:i:s\Z", $start_date_unix);
	    $end_date_unix      = gmdate("Y-m-d\TH:i:s\Z", $end_date_unix);
	    
	    $check_in       = new DateTime($start_date_unix);
	    $check_in_unix  = $check_in->getTimestamp();
	    $check_out      = new DateTime($end_date_unix);
	    $check_out_unix = $check_out->getTimestamp();
	  
	    $booked_dates_array[$check_in_unix] = $feed_name;
	    $check_in_unix =   $check_in->getTimestamp();
	    
	    while ($check_in_unix < $check_out_unix){
	                    
	        $booked_dates_array[$check_in_unix] = $feed_name;

	        $check_in->modify('tomorrow');
	        $check_in_unix =   $check_in->getTimestamp();
	    }
	    //Update booked dates meta
	    update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

	}
}


add_action( 'wp_ajax_homey_remove_ical_feeds', 'homey_remove_ical_feeds' );
if(!function_exists('homey_remove_ical_feeds')) {
	function homey_remove_ical_feeds() {
		global $current_user;
        $current_user 	= wp_get_current_user();
        $userID       	= $current_user->ID;
        $local 			= homey_get_localization();
        $allowded_html 	= array();

        $listing_id 	= intval($_POST['listing_id']);
        $the_post  		= get_post($listing_id);
        $post_owner 	= $the_post->post_author;
        $remove_index = $_POST['remove_index'];

        if ( !is_user_logged_in() || $userID === 0 ) {   
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => esc_html__('Login required', 'homey') 
                ) 
             );
             wp_die();
        }

        if(!is_numeric($listing_id) || !intval($listing_id)) {
        	echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => esc_html__('Something went wrong, please contact site administer', 'homey') 
                ) 
             );
             wp_die();
        }

        if($userID != $post_owner) {
        	echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => esc_html__("You don't have rights to do this.", 'homey') 
                ) 
             );
             wp_die();
        }

        // Remove feed link
        $homey_ical_feeds_meta = get_post_meta($listing_id, 'homey_ical_feeds_meta',true);
        $feed_for_delete =   $homey_ical_feeds_meta[$remove_index]['feed_name'];
        unset($homey_ical_feeds_meta[$remove_index]);
        update_post_meta($listing_id, 'homey_ical_feeds_meta', $homey_ical_feeds_meta);

        //Remove reserved dates 
        $reservation_dates  = get_post_meta($listing_id, 'reservation_dates',true  ); 
            $array = array();
        foreach($reservation_dates as $key => $value){
            if($feed_for_delete == $value){
                unset($reservation_dates[$key]);
            }
        }
        update_post_meta($listing_id, 'reservation_dates', $reservation_dates);

        homey_import_icalendar_feeds($listing_id);

        echo json_encode( 
            array( 
                'success' => true, 
                'message' => esc_html__("Removed Successfully.", 'homey') 
            ) 
         );
         wp_die();

	}
}











