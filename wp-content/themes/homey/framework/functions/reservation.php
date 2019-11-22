<?php
add_action( 'wp_ajax_nopriv_homey_add_reservation', 'homey_add_reservation' );  
add_action( 'wp_ajax_homey_add_reservation', 'homey_add_reservation' );  
if( !function_exists('homey_add_reservation') ) {
    function homey_add_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );
        $guest_message = stripslashes ( $_POST['guest_message'] );
        $guests   =  intval($_POST['guests']);
        $title = $local['reservation_text'];

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];
        
        if ( !is_user_logged_in() || $userID === 0 ) {   
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['login_for_reservation'] 
                ) 
             );
             wp_die();
        }

        if($userID == $listing_owner_id) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['own_listing_error'] 
                ) 
             );
             wp_die();
        }
        
        if(!homey_is_renter()) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['host_user_cannot_book'] 
                ) 
             );
             wp_die();
        }

        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {

             echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['security_check_text'] 
                ) 
             );
             wp_die();
        }

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if($is_available) {
            $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
            
            $reservation_meta['no_of_days'] = $prices_array['days_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];
            $price_per_night = $prices_array['price_per_night'];
            $nights_total_price = $prices_array['nights_total_price'];
            $cleaning_fee = $prices_array['cleaning_fee'];
            $city_fee = $prices_array['city_fee'];
            $services_fee = $prices_array['services_fee'];
            $days_count = $prices_array['days_count'];
            $period_days = $prices_array['period_days'];
            $taxes = $prices_array['taxes'];
            $taxes_percent = $prices_array['taxes_percent'];
            $security_deposit = $prices_array['security_deposit'];
            $additional_guests = $prices_array['additional_guests'];
            $additional_guests_price = $prices_array['additional_guests_price'];
            $additional_guests_total_price = $prices_array['additional_guests_total_price'];
            $booking_has_weekend = $prices_array['booking_has_weekend'];
            $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['check_out_date'] = $check_out_date;
            $reservation_meta['guests'] = $guests;
            $reservation_meta['listing_id'] = $listing_id;
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total'] = $total_price;
            $reservation_meta['price_per_night'] = $price_per_night;
            $reservation_meta['nights_total_price'] = $nights_total_price;
            $reservation_meta['cleaning_fee'] = $cleaning_fee;
            $reservation_meta['city_fee'] = $city_fee;
            $reservation_meta['services_fee'] = $services_fee;
            $reservation_meta['period_days'] = $period_days;
            $reservation_meta['taxes'] = $taxes;
            $reservation_meta['taxes_percent'] = $taxes_percent;
            $reservation_meta['security_deposit'] = $security_deposit;
            $reservation_meta['additional_guests_price'] = $additional_guests_price;
            $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
            $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
            $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

            $reservation = array(
                'post_title'    => $title,
                'post_status'   => 'publish', 
                'post_type'     => 'homey_reservation' ,
                'post_author'   => $userID
            );
            $reservation_id =  wp_insert_post($reservation );  
            
            $reservation_update = array(
                'ID'         => $reservation_id,
                'post_title' => $title.' '.$reservation_id
            );
            wp_update_post( $reservation_update );

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
            update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'under_review');
            update_post_meta($reservation_id, 'is_hourly', 'no');

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            $pending_dates_array = homey_get_booking_pending_days($listing_id);      
            update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array); 

            echo json_encode( 
                array( 
                    'success' => true, 
                    'message' => $local['request_sent']
                ) 
            );

            do_action('homey_create_messages_thread', $guest_message, $reservation_id);
            
            $message_link = homey_thread_link_after_reservation($reservation_id);
            $email_args = array(
                'reservation_detail_url' => reservation_detail_link($reservation_id),
                'guest_message' => $guest_message,
                'message_link' => $message_link
            );
            homey_email_composer( $owner_email, 'new_reservation', $email_args );

            wp_die();

         } else { // end $check_availability
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $check_message
                ) 
             );
             wp_die();
         }

    } 
}

add_action( 'wp_ajax_nopriv_homey_reserve_period_host', 'homey_reserve_period_host' );  
add_action( 'wp_ajax_homey_reserve_period_host', 'homey_reserve_period_host' );  
if( !function_exists('homey_reserve_period_host') ) {
    function homey_reserve_period_host() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();


        $time = time();
        $date = date( 'Y-m-d H:i:s', $time );

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );
        $period_note   =  wp_kses ( $_POST['period_note'], $allowded_html );
        $title = $local['reservation_text'];
        $guests = 0;

        $owner = homey_usermeta($listing_owner_id);
        $owner_email = $owner['email'];
        
        if ( !is_user_logged_in() || $userID === 0 ) {   
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['login_for_reservation'] 
                ) 
             );
             wp_die();
        }
        
        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'period-security-nonce' ) ) {

             echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['security_check_text'] 
                ) 
             );
             wp_die();
        }

        if( $listing_owner_id != $userID ) {
            echo json_encode(
                array( 
                    'success' => false, 
                    'message' => $local['listing_owner_text']
                ) 
            );
            wp_die();
        }

        $check_availability = check_booking_availability_for_reserve_period_host($check_in_date, $check_out_date, $listing_id);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if($is_available) {
            $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
            
            /*$reservation_meta['no_of_days'] = $prices_array['days_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['check_out_date'] = $check_out_date;
            
            $reservation_meta['guests'] = '';
            $reservation_meta['listing_id'] = $listing_id;
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total'] = $total_price;*/

            $reservation_meta['renter_msg'] = $period_note;

            $reservation_meta['no_of_days'] = $prices_array['days_count'];
            $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

            $upfront_payment = $prices_array['upfront_payment'];
            $balance = $prices_array['balance'];
            $total_price = $prices_array['total_price'];
            $price_per_night = $prices_array['price_per_night'];
            $nights_total_price = $prices_array['nights_total_price'];
            $cleaning_fee = $prices_array['cleaning_fee'];
            $city_fee = $prices_array['city_fee'];
            $services_fee = $prices_array['services_fee'];
            $days_count = $prices_array['days_count'];
            $period_days = $prices_array['period_days'];
            $taxes = $prices_array['taxes'];
            $taxes_percent = $prices_array['taxes_percent'];
            $security_deposit = $prices_array['security_deposit'];
            $additional_guests = $prices_array['additional_guests'];
            $additional_guests_price = $prices_array['additional_guests_price'];
            $additional_guests_total_price = $prices_array['additional_guests_total_price'];
            $booking_has_weekend = $prices_array['booking_has_weekend'];
            $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

            $reservation_meta['check_in_date'] = $check_in_date;
            $reservation_meta['check_out_date'] = $check_out_date;
            $reservation_meta['guests'] = $guests;
            $reservation_meta['listing_id'] = $listing_id;
            $reservation_meta['upfront'] = $upfront_payment;
            $reservation_meta['balance'] = $balance;
            $reservation_meta['total'] = $total_price;
            $reservation_meta['price_per_night'] = $price_per_night;
            $reservation_meta['nights_total_price'] = $nights_total_price;
            $reservation_meta['cleaning_fee'] = $cleaning_fee;
            $reservation_meta['city_fee'] = $city_fee;
            $reservation_meta['services_fee'] = $services_fee;
            $reservation_meta['period_days'] = $period_days;
            $reservation_meta['taxes'] = $taxes;
            $reservation_meta['taxes_percent'] = $taxes_percent;
            $reservation_meta['security_deposit'] = $security_deposit;
            $reservation_meta['additional_guests_price'] = $additional_guests_price;
            $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
            $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
            $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

            $reservation = array(
                'post_title'    => $title,
                'post_status'   => 'publish', 
                'post_type'     => 'homey_reservation' ,
                'post_author'   => $userID
            );
            $reservation_id =  wp_insert_post($reservation );  
            
            $reservation_update = array(
                'ID'         => $reservation_id,
                'post_title' => $title.' '.$reservation_id
            );
            wp_update_post( $reservation_update );

            update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
            update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
            update_post_meta($reservation_id, 'listing_renter', $userID);
            update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
            update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
            update_post_meta($reservation_id, 'reservation_guests', $guests);
            update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
            update_post_meta($reservation_id, 'reservation_status', 'booked');

            update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
            update_post_meta($reservation_id, 'reservation_balance', $balance);
            update_post_meta($reservation_id, 'reservation_total', $total_price);

            $booked_dates_array = homey_get_booked_days_host_period($listing_id);      
            update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

            echo json_encode( 
                array( 
                    'success' => true,
                    'message' => $local['reserve_period_success']
                ) 
            );
            
            $invoiceID = homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $userID, 0, 0, '', 'Self' );
            
            update_post_meta( $invoiceID, 'invoice_payment_status', 1 );

            wp_die();

         } else { // end $check_availability
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $check_message
                ) 
             );
             wp_die();
         }

    } 
}

if( !function_exists('homey_add_instance_booking') ) {
    function homey_add_instance_booking($listing_id, $check_in_date, $check_out_date, $guests, $renter_message) {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $reservation_meta = array();
        
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $title = $local['reservation_text'];

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
        /*$upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $reservation_meta['no_of_days'] = $prices_array['days_count'];
        $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

        $reservation_meta['check_in_date'] = $check_in_date;
        $reservation_meta['check_out_date'] = $check_out_date;
        $reservation_meta['guests'] = $guests;
        $reservation_meta['listing_id'] = $listing_id;
        $reservation_meta['renter_msg'] = $renter_message;

        $reservation_meta['upfront'] = $upfront_payment;
        $reservation_meta['balance'] = $balance;
        $reservation_meta['total'] = $total_price;*/

        $reservation_meta['no_of_days'] = $prices_array['days_count'];
        $reservation_meta['additional_guests'] = $prices_array['additional_guests'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];
        $price_per_night = $prices_array['price_per_night'];
        $nights_total_price = $prices_array['nights_total_price'];
        $cleaning_fee = $prices_array['cleaning_fee'];
        $city_fee = $prices_array['city_fee'];
        $services_fee = $prices_array['services_fee'];
        $days_count = $prices_array['days_count'];
        $period_days = $prices_array['period_days'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];
        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

        $reservation_meta['check_in_date'] = $check_in_date;
        $reservation_meta['check_out_date'] = $check_out_date;
        $reservation_meta['guests'] = $guests;
        $reservation_meta['listing_id'] = $listing_id;
        $reservation_meta['upfront'] = $upfront_payment;
        $reservation_meta['balance'] = $balance;
        $reservation_meta['total'] = $total_price;
        $reservation_meta['price_per_night'] = $price_per_night;
        $reservation_meta['nights_total_price'] = $nights_total_price;
        $reservation_meta['cleaning_fee'] = $cleaning_fee;
        $reservation_meta['city_fee'] = $city_fee;
        $reservation_meta['services_fee'] = $services_fee;
        $reservation_meta['period_days'] = $period_days;
        $reservation_meta['taxes'] = $taxes;
        $reservation_meta['taxes_percent'] = $taxes_percent;
        $reservation_meta['security_deposit'] = $security_deposit;
        $reservation_meta['additional_guests_price'] = $additional_guests_price;
        $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
        $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
        $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

        $reservation = array(
            'post_title'    => $title,
            'post_status'   => 'publish', 
            'post_type'     => 'homey_reservation' ,
            'post_author'   => $userID
        );
        $reservation_id =  wp_insert_post($reservation );  
        
        $reservation_update = array(
            'ID'         => $reservation_id,
            'post_title' => $title.' '.$reservation_id
        );
        wp_update_post( $reservation_update );

        update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
        update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
        update_post_meta($reservation_id, 'listing_renter', $userID);
        update_post_meta($reservation_id, 'reservation_checkin_date', $check_in_date);
        update_post_meta($reservation_id, 'reservation_checkout_date', $check_out_date);
        update_post_meta($reservation_id, 'reservation_guests', $guests);
        update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
        update_post_meta($reservation_id, 'reservation_status', 'booked');
        update_post_meta($reservation_id, 'is_hourly', 'no');

        update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
        update_post_meta($reservation_id, 'reservation_balance', $balance);
        update_post_meta($reservation_id, 'reservation_total', $total_price);

        //Book dates
        $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

        return $reservation_id;

    } 
}


if (!function_exists("homey_get_booking_pending_days")) {
    function homey_get_booking_pending_days($listing_id) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $args = array(
            'post_type'        => 'homey_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'       => 'reservation_listing_id',
                        'value'     => $listing_id,
                        'type'      => 'NUMERIC',
                        'compare'   => '='
                    ),
                    array(
                        'key'       => 'reservation_status',
                        'value'     => 'declined',
                        'type'      => 'CHAR',
                        'compare'   => '!='
                    ),
                    array(
                        'key'       => 'reservation_status',
                        'value'     => 'cancelled',
                        'type'      => 'CHAR',
                        'compare'   => '!='
                    )
                )
            );
        
        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_dates', true );
        
        if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
            $pending_dates_array  = array();
        }
        
        $wpQry = new WP_Query($args);
        
        if ($wpQry->have_posts()) {    

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();
                
                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
                $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

                $unix_time_start = strtotime ($check_in_date);
                
                if ($unix_time_start > $daysAgo) {
                    $check_in       =   new DateTime($check_in_date);
                    $check_in_unix  =   $check_in->getTimestamp();
                    $check_out      =   new DateTime($check_out_date);
                    $check_out_unix =   $check_out->getTimestamp();

                    
                    $pending_dates_array[$check_in_unix] = $resID;

                    $check_in_unix =   $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix){
                    
                        $pending_dates_array[$check_in_unix] = $resID;

                        $check_in->modify('tomorrow');
                        $check_in_unix =   $check_in->getTimestamp();
                    }          
                }
            endwhile;
            wp_reset_postdata();
        }        
      
        return $pending_dates_array;
        
    }
}

if (!function_exists("homey_get_booked_days")) {
    function homey_get_booked_days($listing_id) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

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
        
        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true );
        
        if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
            $booked_dates_array  = array();
        }

        $wpQry = new WP_Query($args);
        
        if ($wpQry->have_posts()) {    

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();
                
                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
                $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

                $unix_time_start = strtotime ($check_in_date);
                
                if ($unix_time_start > $daysAgo) {
                    $check_in       =   new DateTime($check_in_date);
                    $check_in_unix  =   $check_in->getTimestamp();
                    $check_out      =   new DateTime($check_out_date);
                    $check_out_unix =   $check_out->getTimestamp();

                    
                    $booked_dates_array[$check_in_unix] = $resID;

                    $check_in_unix =   $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix){
                    
                        $booked_dates_array[$check_in_unix] = $resID;

                        $check_in->modify('tomorrow');
                        $check_in_unix =   $check_in->getTimestamp();
                    }          
                }
            endwhile;
            wp_reset_postdata();
        }        
      
        return $booked_dates_array;
        
    }
}

if (!function_exists("homey_make_days_booked")) {
    function homey_make_days_booked($listing_id, $resID) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );
        
        $reservation_dates_array = get_post_meta($listing_id, 'reservation_dates', true );
        
        if( !is_array($reservation_dates_array) || empty($reservation_dates_array) ) {
            $reservation_dates_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);
                
        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix  =   $check_in->getTimestamp();
            $check_out      =   new DateTime($check_out_date);
            $check_out_unix =   $check_out->getTimestamp();

            $check_in_unix =   $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix){
            
                $reservation_dates_array[$check_in_unix] = $resID;

                $check_in->modify('tomorrow');
                $check_in_unix =   $check_in->getTimestamp();
            }          
        }     
      
        return $reservation_dates_array;
    }
}

if (!function_exists("homey_remove_booking_pending_days")) {
    function homey_remove_booking_pending_days($listing_id, $resID) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );
        
        $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_dates', true );
        
        if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
            $pending_dates_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);
                
        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix  =   $check_in->getTimestamp();
            $check_out      =   new DateTime($check_out_date);
            $check_out_unix =   $check_out->getTimestamp();

            $check_in_unix =   $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix){
            
                unset($pending_dates_array[$check_in_unix]);

                $check_in->modify('tomorrow');
                $check_in_unix =   $check_in->getTimestamp();
            }          
        }     
      
        return $pending_dates_array;
    }
}

if (!function_exists("homey_remove_booking_booked_days")) {
    function homey_remove_booking_booked_days($listing_id, $resID) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );
        
        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true );
        
        if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
            $booked_dates_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);
                
        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix  =   $check_in->getTimestamp();
            $check_out      =   new DateTime($check_out_date);
            $check_out_unix =   $check_out->getTimestamp();

            $check_in_unix =   $check_in->getTimestamp();

            while ($check_in_unix < $check_out_unix){
            
                unset($booked_dates_array[$check_in_unix]);

                $check_in->modify('tomorrow');
                $check_in_unix =   $check_in->getTimestamp();
            }          
        }     
      
        return $booked_dates_array;
    }
}

if (!function_exists("homey_get_booked_days_host_period")) {
    function homey_get_booked_days_host_period($listing_id) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $args = array(
            'post_type'        => 'homey_reservation',
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'meta_query' => array(
                    'relation' => 'AND', // Optional, defaults to "AND"
                    array(
                        'key'       => 'reservation_listing_id',
                        'value'     => $listing_id,
                        'type'      => 'NUMERIC',
                        'compare'   => '='
                    ),
                    array(
                        'key'     => 'reservation_status',
                        'value'   => 'booked',
                        'compare' => '='
                    )
                )
            );
        
        $booked_dates_array = get_post_meta($listing_id, 'reservation_dates', true );
        
        if( !is_array($booked_dates_array) || empty($booked_dates_array) ) {
            $booked_dates_array  = array();
        }

        $wpQry = new WP_Query($args);
        
        if ($wpQry->have_posts()) {    

            while ($wpQry->have_posts()): $wpQry->the_post();

                $resID = get_the_ID();
                
                $check_in_date  = get_post_meta( $resID, 'reservation_checkin_date', true );
                $check_out_date = get_post_meta( $resID, 'reservation_checkout_date', true );

                $unix_time_start = strtotime ($check_in_date);
                
                if ($unix_time_start > $daysAgo) {
                    $check_in       =   new DateTime($check_in_date);
                    $check_in_unix  =   $check_in->getTimestamp();
                    $check_out      =   new DateTime($check_out_date);
                    $check_out_unix =   $check_out->getTimestamp();

                    
                    $booked_dates_array[$check_in_unix] = $resID;

                    $check_in_unix =   $check_in->getTimestamp();

                    while ($check_in_unix < $check_out_unix){
                    
                        $booked_dates_array[$check_in_unix] = $resID;

                        $check_in->modify('tomorrow');
                        $check_in_unix =   $check_in->getTimestamp();
                    }          
                }
            endwhile;
            wp_reset_postdata();
        }        
      
        return $booked_dates_array;
        
    }
}

if(!function_exists('check_booking_availability')) {
    function check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests) {
        $return_array = array();
        $local = homey_get_localization();
        $booking_proceed = true;
        $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
        $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

        $booking_hide_fields = homey_option('booking_hide_fields');

        $homey_allow_additional_guests = get_post_meta($listing_id, 'homey_allow_additional_guests', true);
        $allowed_guests = get_post_meta($listing_id, 'homey_guests', true);

        if(!empty($allowed_guests)) {
            if( ($homey_allow_additional_guests != 'yes') && ($guests > $allowed_guests)) {
                $return_array['success'] = false;
                $return_array['message'] = $local['guest_allowed'].' '.$allowed_guests;
                return $return_array;
            } 
        }

        if(strtotime($check_out_date) <= strtotime($check_in_date)) {
            $booking_proceed = false;
        }

        if(empty($check_in_date) && empty($check_out_date) && empty($guests)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['fill_all_fields'];
            return $return_array;

        } 

        if(empty($check_in_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_checkin'];
            return $return_array;

        }

        if(empty($check_out_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_checkout'];
            return $return_array;

        }


        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count); 

        if($days_count < $min_book_days) {
            $return_array['success'] = false;
            $return_array['message'] = $local['min_book_days_error'].' '.$min_book_days;
            return $return_array;
        }

        if(($days_count > $max_book_days) && !empty($max_book_days)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['max_book_days_error'].' '.$max_book_days;
            return $return_array;
        }

        if(empty($guests) && $booking_hide_fields['guests'] != 1) {
            $return_array['success'] = false;
            $return_array['message'] = $local['choose_guests'];
            return $return_array;

        }

        if(!$booking_proceed) {
            $return_array['success'] = false;
            $return_array['message'] = $local['ins_book_proceed'];
            return $return_array;
        }


        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);
        $check_out->modify('yesterday');
        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {
            
            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) || array_key_exists($check_in_unix, $reservation_unavailable_array) ) {
                
                $return_array['success'] = false;
                $return_array['message'] = $local['dates_not_available'];
                if(homey_is_instance_page()) {
                    $return_array['message'] = $local['ins_unavailable'];
                }
                return $return_array; //dates are not available

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }

        //dates are available
        $return_array['success'] = true;
        $return_array['message'] = $local['dates_available'];
        return $return_array;
        
    }
}

if(!function_exists('check_booking_availability_for_reserve_period_host')) {
    function check_booking_availability_for_reserve_period_host($check_in_date, $check_out_date, $listing_id) {
        $return_array = array();
        $local = homey_get_localization();
        $booking_proceed = true;
        
        if(strtotime($check_out_date) <= strtotime($check_in_date)) {
            $booking_proceed = false;
        }

        if(empty($check_in_date) && empty($check_out_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['fill_all_fields'];
            return $return_array;

        } 

        if(empty($check_in_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['start_date_label'];
            return $return_array;

        }

        if(empty($check_out_date)) {
            $return_array['success'] = false;
            $return_array['message'] = $local['end_date_label'];
            return $return_array;

        }


        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count); 

        if(!$booking_proceed) {
            $return_array['success'] = false;
            $return_array['message'] = $local['ins_book_proceed'];
            return $return_array;
        }


        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);
        $check_out->modify('yesterday');
        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {
            
            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) ) {
                
                $return_array['success'] = false;
                $return_array['message'] = $local['dates_not_available'];
                if(homey_is_instance_page()) {
                    $return_array['message'] = $local['ins_unavailable'];
                }
                return $return_array; //dates are not available

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }

        //dates are available
        $return_array['success'] = true;
        $return_array['message'] = $local['dates_available'];
        return $return_array;
        
    }
}


add_action( 'wp_ajax_nopriv_check_booking_availability_on_date_change', 'check_booking_availability_on_date_change' );  
add_action( 'wp_ajax_check_booking_availability_on_date_change', 'check_booking_availability_on_date_change' ); 
if(!function_exists('check_booking_availability_on_date_change')) {
    function check_booking_availability_on_date_change() {
        $local = homey_get_localization();
        $allowded_html = array();
        $booking_proceed = true;

        $listing_id = intval($_POST['listing_id']);
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );

        $min_book_days = get_post_meta($listing_id, 'homey_min_book_days', true);
        $max_book_days = get_post_meta($listing_id, 'homey_max_book_days', true);

        if(strtotime($check_out_date) <= strtotime($check_in_date)) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['ins_book_proceed']
                ) 
             );
             wp_die();
        }

        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count); 

        if($days_count < $min_book_days) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['min_book_days_error'].' '.$min_book_days
                ) 
             );
             wp_die();
        }

        if(($days_count > $max_book_days) && !empty($max_book_days)) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['max_book_days_error'].' '.$max_book_days
                ) 
             );
             wp_die();
        }

        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);
        $check_out->modify('yesterday');
        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {
            
            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) || array_key_exists($check_in_unix, $reservation_unavailable_array) ) {
                
                echo json_encode( 
                    array( 
                        'success' => false, 
                        'message' => $local['dates_not_available']
                    ) 
                 );
                 wp_die();

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }
        echo json_encode( 
            array( 
                'success' => true, 
                'message' => $local['dates_available']
            ) 
         );
         wp_die();
    }
}

add_action('wp_ajax_nopriv_homey_instance_booking', 'homey_instance_booking');
add_action('wp_ajax_homey_instance_booking', 'homey_instance_booking');
if(!function_exists('homey_instance_booking')) {
    function homey_instance_booking() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $instace_page_link = homey_get_template_link_2('template/template-instance-booking.php');
        
        if ( !is_user_logged_in() || $userID === 0 ) {   
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['login_for_reservation'] 
                ) 
             );
             wp_die();
        }

        if ( empty($instace_page_link) ) {   
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['instance_booking_page'] 
                ) 
             );
             wp_die();
        }
        
        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'reservation-security-nonce' ) ) {

             echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['security_check_text'] 
                ) 
             );
             wp_die();
        }

        $listing_id = intval($_POST['listing_id']);
        $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
        $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date    =  wp_kses ( $_POST['check_out_date'], $allowded_html );
        $guests   =  intval($_POST['guests']);

        if($userID == $listing_owner_id) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['own_listing_error'] 
                ) 
             );
             wp_die();
        }

        if(!homey_is_renter()) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['host_user_cannot_book'] 
                ) 
             );
             wp_die();
        }

        $instance_page = add_query_arg( array(
            'check_in' => $check_in_date,
            'check_out' => $check_out_date,
            'guest' => $guests,
            'listing_id' => $listing_id,
        ), $instace_page_link );

        echo json_encode( 
            array( 
                'success' => true, 
                'message' => '',
                'instance_url' =>  $instance_page
            ) 
         );
        wp_die();
    }
}


add_action( 'wp_ajax_nopriv_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax' );  
add_action( 'wp_ajax_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax' ); 

if( !function_exists('homey_calculate_booking_cost_ajax') ) {
    function homey_calculate_booking_cost_ajax() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_POST['listing_id']);
        $check_in_date  = wp_kses ( $_POST['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $_POST['check_out_date'], $allowded_html );
        $guests         = intval($_POST['guests']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
        
        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
                $output .= '<div class="payment-list-price-detail-total-price">'.esc_attr($local['cs_total']).'</div>';
                $output .= '<div class="payment-list-price-detail-note">'.esc_attr($local['cs_tax_fees']).'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
                $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
                $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target="#collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.esc_attr($local['cs_view_details']).'</a>';
            $output .= '</div>';
        $output .= '</div>';
        
        $output .= '<div class="collapse" id="collapseExample">';
            $output .= '<ul>';

                if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) { 
                    $output .= '<li>'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($local['with_custom_period_and_weekend_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

                } elseif($booking_has_weekend == 1) {
                    $output .= '<li>'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($with_weekend_label).') <span>'.$nights_total_price.'</span></li>';

                } elseif($booking_has_custom_pricing == 1) { 
                    $output .= '<li>'.esc_attr($no_of_days).' '.esc_attr($night_label).' ('.esc_attr($local['with_custom_period_label']).') <span>'.esc_attr($nights_total_price).'</span></li>';

                } else {
                    $output .= '<li>'.($price_per_night).' x '.esc_attr($no_of_days).' '.esc_attr($night_label).' <span>'.$nights_total_price.'</span></li>';
                }

                if(!empty($additional_guests)) {
                    $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
                }

                if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
                    $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.($cleaning_fee).'</span></li>';
                }

                if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
                    $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.($city_fee).'</span></li>';
                }
                
                if(!empty($security_deposit) && $security_deposit != 0) {
                    $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
                }
                
                if(!empty($services_fee) && $services_fee != 0 ) {
                    $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
                }

                if(!empty($taxes) && $taxes != 0 ) {
                    $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
                }

                if(!empty($upfront_payment) && $upfront_payment != 0) {
                    $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                }

            $output .= '</ul>';
        $output .= '</div>';

        // This variable has been safely escaped in same file: Line: 1071 - 1128
        $output_escaped = $output;
        print $output_escaped;
        
        wp_die();

    } 
}


if( !function_exists('homey_calculate_booking_cost_instance') ) {
    function homey_calculate_booking_cost_instance() {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id     = intval($_GET['listing_id']);
        $check_in_date  = wp_kses ( $_GET['check_in'], $allowded_html );
        $check_out_date = wp_kses ( $_GET['check_out'], $allowded_html );
        $guests         = intval($_GET['guest']);

        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
        
        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';
            $output .= '<div class="pull-left">';
                $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
                $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
            $output .= '</div>';

            $output .= '<div class="pull-right text-right">';
                $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
                $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target="#collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
            $output .= '</div>';
        $output .= '</div>';
        
        $output .= '<div class="collapse" id="collapseExample">';
            $output .= '<ul>';

                if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) { 
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';
                    
                } elseif($booking_has_weekend == 1) {
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

                } elseif($booking_has_custom_pricing == 1) { 
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

                } else {
                    $output .= '<li>'.$price_per_night.' x '.$no_of_days.' '.$night_label.' <span>'.$nights_total_price.'</span></li>';
                }

                if(!empty($additional_guests)) {
                    $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
                }

                if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
                    $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
                }

                if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
                    $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
                }
                
                if(!empty($security_deposit) && $security_deposit != 0) {
                    $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
                }
                
                if(!empty($services_fee) && $services_fee != 0 ) {
                    $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
                }

                if(!empty($taxes) && $taxes != 0 ) {
                    $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
                }

                if(!empty($upfront_payment) && $upfront_payment != 0) {
                    $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                }

                if(!empty($balance) && $balance != 0) {
                    $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
                }
            $output .= '</ul>';
        $output .= '</div>';

        return $output;
    } 
}

if( !function_exists('homey_calculate_booking_cost') ) {
    function homey_calculate_booking_cost($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);
        
        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
        
        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
                $output .= '<div class="pull-left">';
                    $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
                    $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
                $output .= '</div>';

                $output .= '<div class="pull-right text-right">';
                    $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
                    $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target="#collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
                $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse" id="collapseExample">';
        }


        $output .= $start_div;
            $output .= '<ul>';
            
                if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) { 
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';
                    
                } elseif($booking_has_weekend == 1) {
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

                } elseif($booking_has_custom_pricing == 1) { 
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

                } else {
                    $output .= '<li>'.$price_per_night.' x '.$no_of_days.' '.$night_label.' <span>'.$nights_total_price.'</span></li>';
                }

                if(!empty($additional_guests)) {
                    $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
                }
                
                if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
                    $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
                }

                if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
                    $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
                }

                if(!empty($security_deposit) && $security_deposit != 0) {
                    $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
                }
                
                if(!empty($services_fee) && $services_fee != 0 ) {
                    $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
                }

                if(!empty($taxes) && $taxes != 0 ) {
                    $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
                }

                if(!empty($upfront_payment) && $upfront_payment != 0) {
                    $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                }

                if(!empty($balance) && $balance != 0) {
                    $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
                }

            $output .= '</ul>';
         $output .= '</div>';

        return $output;
    } 
}

if( !function_exists('homey_calculate_reservation_cost') ) {
    function homey_calculate_reservation_cost($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);
        
        //$prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
        
        $price_per_night = homey_formatted_price($reservation_meta['price_per_night'], true);
        $no_of_days = $reservation_meta['no_of_days'];

        $nights_total_price = homey_formatted_price($reservation_meta['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = (float) $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $invoice_id = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '';
        $reservation_detail_id = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
        $is_host = false;
        if( (!empty($invoice_id) || !empty($reservation_detail_id) ) && homey_is_host()) {
            $is_host = true;
        }

        if($is_host) {
            $total_price = $total_price - $services_fee;
        }

        $start_div = '<div class="payment-list">';

        if($collapse) {
            $output = '<div class="payment-list-price-detail clearfix">';
                $output .= '<div class="pull-left">';
                    $output .= '<div class="payment-list-price-detail-total-price">'.$local['cs_total'].'</div>';
                    $output .= '<div class="payment-list-price-detail-note">'.$local['cs_tax_fees'].'</div>';
                $output .= '</div>';

                $output .= '<div class="pull-right text-right">';
                    $output .= '<div class="payment-list-price-detail-total-price">'.homey_formatted_price($total_price).'</div>';
                    $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target="#collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">'.$local['cs_view_details'].'</a>';
                $output .= '</div>';
            $output .= '</div>';

            $start_div  = '<div class="collapse" id="collapseExample">';
        }


        $output .= $start_div;
            $output .= '<ul>';
            
                if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) { 
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$nights_total_price.'</span></li>';
                    
                } elseif($booking_has_weekend == 1) {
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') <span>'.$nights_total_price.'</span></li>';

                } elseif($booking_has_custom_pricing == 1) { 
                    $output .= '<li>'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') <span>'.$nights_total_price.'</span></li>';

                } else {
                    $output .= '<li>'.$price_per_night.' x '.$no_of_days.' '.$night_label.' <span>'.$nights_total_price.'</span></li>';
                }

                if(!empty($additional_guests)) {
                    $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
                }
                
                if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
                    $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
                }

                if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
                    $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
                }

                if(!empty($security_deposit) && $security_deposit != 0) {
                    $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
                }
                

                if(!empty($services_fee) && $services_fee != 0 && !$is_host) {
                    $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
                }

                if(!empty($taxes) && $taxes != 0 ) {
                    $output .= '<li>'.$local['cs_taxes'].' '.$taxes_percent.'% <span>'.homey_formatted_price($taxes).'</span></li>';
                }

                if(!empty($upfront_payment) && $upfront_payment != 0) {
                    if($is_host) {
                        $upfront_payment = $upfront_payment - $services_fee;
                    }
                    $output .= '<li class="payment-due">'.$local['cs_payment_due'].' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
                }

                if(!empty($balance) && $balance != 0) {
                    $output .= '<li><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' '.homey_formatted_price($balance).' '.$local['cs_pay_rest_2'].'</li>';
                }

            $output .= '</ul>';
         $output .= '</div>';

        return $output;
    } 
}

if( !function_exists('homey_calculate_booking_cost_admin') ) {
    function homey_calculate_booking_cost_admin($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);
        
        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
        
        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }
            
        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) { 
            $output .= '<tr>
                    <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].')</td> 
                    <td>'.$nights_total_price.'</td>
                    </tr>';
            
        } elseif($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } elseif($booking_has_custom_pricing == 1) { 
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') </td> 
                <td>'.$nights_total_price.'</td>
                </tr>';

        } else {
            $output .= '<tr>
                <td class="manage-column">'.$price_per_night.' x '.$no_of_days.' '.$night_label.' </td>
                <td>'.$nights_total_price.'</td>
                </tr>';
        }

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';
        
        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }
        
        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }

        
        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';
        

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }

            

        return $output;
    } 
}

if( !function_exists('homey_calculate_reservation_cost_admin') ) {
    function homey_calculate_reservation_cost_admin($reservation_id, $collapse = false) {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        if(empty($reservation_id)) {
            return;
        }
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);
    
        
        $price_per_night = homey_formatted_price($reservation_meta['price_per_night'], true);
        $no_of_days = $reservation_meta['no_of_days'];

        $nights_total_price = homey_formatted_price($reservation_meta['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
        $services_fee = $reservation_meta['services_fee'];
        $taxes = $reservation_meta['taxes'];
        $taxes_percent = $reservation_meta['taxes_percent'];
        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
        $security_deposit = $reservation_meta['security_deposit'];
        $additional_guests = $reservation_meta['additional_guests'];
        $additional_guests_price = $reservation_meta['additional_guests_price'];
        $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];

        $upfront_payment = $reservation_meta['upfront'];
        $balance = $reservation_meta['balance'];
        $total_price = $reservation_meta['total'];

        $booking_has_weekend = $reservation_meta['booking_has_weekend'];
        $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        if($no_of_days > 1) {
            $night_label = homey_option('glc_day_nights_label');
        } else {
            $night_label = homey_option('glc_day_night_label');
        }

        if($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }
            
        if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) { 
            $output .= '<tr>
                    <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_and_weekend_label'].')</td> 
                    <td>'.$nights_total_price.'</td>
                    </tr>';
            
        } elseif($booking_has_weekend == 1) {
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$with_weekend_label.') </td>
                <td>'.$nights_total_price.'</td>
                </tr>';

        } elseif($booking_has_custom_pricing == 1) { 
            $output .= '<tr>
                <td class="manage-column">'.$no_of_days.' '.$night_label.' ('.$local['with_custom_period_label'].') </td> 
                <td>'.$nights_total_price.'</td>
                </tr>';

        } else {
            $output .= '<tr>
                <td class="manage-column">'.$price_per_night.' x '.$no_of_days.' '.$night_label.' </td>
                <td>'.$nights_total_price.'</td>
                </tr>';
        }

        if(!empty($additional_guests)) {
            $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
        }

        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';
        
        if(!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
        }
        
        if(!empty($services_fee) && $services_fee != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
        }

        if(!empty($taxes) && $taxes != 0 ) {
            $output .= '<tr><td class="manage-column">'.$local['cs_taxes'].' '.$taxes_percent.'%</td> <td>'.homey_formatted_price($taxes).'</td></tr>';
        }

        
        $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_total'].'</strong></td> <td><strong>'.homey_formatted_price($total_price).'</strong></td></tr>';
        

        if(!empty($upfront_payment) && $upfront_payment != 0) {
            $output .= '<tr class="payment-due"><td class="manage-column"><strong>'.$local['cs_payment_due'].'</strong></td> <td><strong>'.homey_formatted_price($upfront_payment).'</strong></td></tr>';
        }

        if(!empty($balance) && $balance != 0) {
            $output .= '<tr><td class="manage-column"><i class="fa fa-info-circle"></i> '.$local['cs_pay_rest_1'].' <strong>'.homey_formatted_price($balance).'</strong> '.$local['cs_pay_rest_2'].'</td></tr>';
        }

            

        return $output;
    } 
}

if(!function_exists('homey_get_prices')) {
    function homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests) {
        $prefix = 'homey_';

        $enable_services_fee = homey_option('enable_services_fee');
        $enable_taxes = homey_option('enable_taxes');
        $reservation_payment_type = homey_option('reservation_payment');
        $booking_percent = homey_option('booking_percent');
        $tax_type = homey_option('tax_type');
        $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
        $taxes_percent_global  =   homey_option('taxes_percent');
        $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

        $period_price = get_post_meta($listing_id, 'homey_custom_period', true);
        if(empty($period_price)) {
            $period_price =  array();
        }
       
        $taxes_final = 0;
        $taxes_percent = 0;
        $total_price = 0;
        $total_guests_price = 0;
        $upfront_payment = 0;
        $nights_total_price = 0;
        $booking_has_weekend = 0;
        $booking_has_custom_pricing = 0;
        $balance = 0;
        $period_days = 0;
        $security_deposit = '';
        $additional_guests = '';
        $additional_guests_total_price = '';
        $services_fee_final = '';
        $taxes_fee_final = '';
        $prices_array = array();

        $listing_guests          = floatval( get_post_meta($listing_id, $prefix.'guests', true) );
        $nightly_price           = floatval( get_post_meta($listing_id, $prefix.'night_price', true));
        $price_per_night         = $nightly_price;
        $weekends_price          = floatval( get_post_meta($listing_id, $prefix.'weekends_price', true) );
        $weekends_days           = get_post_meta($listing_id, $prefix.'weekends_days', true);
        $priceWeek               = floatval( get_post_meta($listing_id, $prefix.'priceWeek', true) ); // 7 Nights 
        $priceMonthly            = floatval( get_post_meta($listing_id, $prefix.'priceMonthly', true) );  // 30 Nights
        $security_deposit        = floatval( get_post_meta($listing_id, $prefix.'security_deposit', true) );

        $cleaning_fee            = floatval( get_post_meta($listing_id, $prefix.'cleaning_fee', true) );
        $cleaning_fee_type       = get_post_meta($listing_id, $prefix.'cleaning_fee_type', true);

        $city_fee                = floatval( get_post_meta($listing_id, $prefix.'city_fee', true) );
        $city_fee_type           = get_post_meta($listing_id, $prefix.'city_fee_type', true);
        
        $extra_guests_price      = floatval( get_post_meta($listing_id, $prefix.'additional_guests_price', true) ); 
        $additional_guests_price = $extra_guests_price;

        $allow_additional_guests = get_post_meta($listing_id, $prefix.'allow_additional_guests', true);

        $check_in        =  new DateTime($check_in_date);
        $check_in_unix   =  $check_in->getTimestamp();
        $check_out       =  new DateTime($check_out_date);
        $check_out_unix  =  $check_out->getTimestamp();
    
        $time_difference = abs( strtotime($check_in_date) - strtotime($check_out_date) );
        $days_count      = $time_difference/86400;
        $days_count      = intval($days_count); 

        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['night_price'] ) &&  $period_price[$check_in_unix]['night_price']!=0 ){
            $price_per_night = $period_price[$check_in_unix]['night_price'];

            $booking_has_custom_pricing = 1;
            $period_days = $period_days + 1;
        }

        if( $days_count > 7 && $priceWeek != 0 ) {
            $price_per_night = $priceWeek;
        }

        if( $days_count > 30 && $priceMonthly != 0 ) {
            $price_per_night = $priceMonthly;
        }


        // Check additional guests price
        if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
            if( $guests > $listing_guests) {
                $additional_guests = $guests - $listing_guests;

                $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                $total_guests_price = $total_guests_price + $guests_price_return;
            }
        }

        // Check for weekend and add weekend price
        $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);
        $nights_total_price = $nights_total_price + $returnPrice;
        $total_price = $total_price + $returnPrice;


        $check_in->modify('tomorrow');
        $check_in_unix =   $check_in->getTimestamp();

        $weekday = date('N', $check_in_unix);
        if(homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
            $booking_has_weekend = 1;
        }

        while ($check_in_unix < $check_out_unix) {
            

            $weekday = date('N', $check_in_unix);
            if(homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
                $booking_has_weekend = 1;
            }

            if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['night_price'] ) &&  $period_price[$check_in_unix]['night_price']!=0 ){
            
                $price_per_night = $period_price[$check_in_unix]['night_price'];
                $booking_has_custom_pricing = 1;
                $period_days = $period_days + 1;
            } else {
                $price_per_night = $price_per_night;
            }

            if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
                if( $guests > $listing_guests) {
                    $additional_guests = $guests - $listing_guests;

                    $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

                    $total_guests_price = $total_guests_price + $guests_price_return;
                }
            }
            
            $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

            $nights_total_price = $nights_total_price + $returnPrice;
            $total_price = $total_price + $returnPrice;

            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();

        }

        if( $cleaning_fee_type == 'daily' ) {
            $cleaning_fee = $cleaning_fee * $days_count;
            $total_price = $total_price + $cleaning_fee;
        } else {
            $total_price = $total_price + $cleaning_fee;
        }


        //Calculate taxes based of original price (Excluding city, security deposit etc)
        if($enable_taxes == 1) {
            
            if($tax_type == 'global_tax') {
                $taxes_percent = $taxes_percent_global;
            } else {
                if(!empty($single_listing_tax)) {
                    $taxes_percent = $single_listing_tax;
                }
            }

            $taxes_final = homey_calculate_taxes($taxes_percent, $total_price);
            $total_price = $total_price + $taxes_final;
        }


        //Calculate sevices fee based of original price (Excluding cleaning, city, sevices fee etc)
        if($enable_services_fee == 1) {
            $services_fee_type  = homey_option('services_fee_type');
            $services_fee  =   homey_option('services_fee');
            $price_for_services_fee = $total_price + $total_guests_price;
            $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
            $total_price = $total_price + $services_fee_final;
        }


        if( $city_fee_type == 'daily' ) {
            $city_fee = $city_fee * $days_count;
            $total_price = $total_price + $city_fee;
        } else {
            $total_price = $total_price + $city_fee;
        }

        if(!empty($security_deposit) && $security_deposit != 0) {
            $total_price = $total_price + $security_deposit;
        }

        if($total_guests_price !=0) {
            $total_price = $total_price + $total_guests_price;
        }


        if($reservation_payment_type == 'percent') {
            if(!empty($booking_percent) && $booking_percent != 0) {
                $upfront_payment = round($booking_percent*$total_price/100,2);
            }

        } elseif($reservation_payment_type == 'full') {
            $upfront_payment = $total_price;

        } elseif($reservation_payment_type == 'only_security') {
            $upfront_payment = $security_deposit;

        } elseif($reservation_payment_type == 'only_services') {
            $upfront_payment = $services_fee_final;

        } elseif($reservation_payment_type == 'services_security') {
            $upfront_payment = $security_deposit+$services_fee_final;
        }

        $balance = $total_price - $upfront_payment;

        $prices_array['price_per_night'] = $price_per_night;
        $prices_array['nights_total_price'] = $nights_total_price;
        $prices_array['total_price']     = $total_price;
        $prices_array['check_in_date']   = $check_in_date;
        $prices_array['check_out_date']  = $check_out_date;
        $prices_array['cleaning_fee']    = $cleaning_fee;
        $prices_array['city_fee']        = $city_fee;
        $prices_array['services_fee']    = $services_fee_final;
        $prices_array['days_count']      = $days_count;
        $prices_array['period_days']      = $period_days;
        $prices_array['taxes']           = $taxes_final;
        $prices_array['taxes_percent']   = $taxes_percent;
        $prices_array['security_deposit'] = $security_deposit;
        $prices_array['additional_guests'] = $additional_guests;
        $prices_array['additional_guests_price'] = $additional_guests_price;
        $prices_array['additional_guests_total_price'] = $total_guests_price;
        $prices_array['booking_has_weekend'] = $booking_has_weekend;
        $prices_array['booking_has_custom_pricing'] = $booking_has_custom_pricing;
        $prices_array['balance'] = $balance;
        $prices_array['upfront_payment'] = $upfront_payment;

        return $prices_array;

    }
}

if(!function_exists('homey_calculate_guests_price')) {
    function homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price) {
        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['guest_price'] ) &&  $period_price[$check_in_unix]['guest_price']!=0 ) {
            $return_guest_price = $period_price[$check_in_unix]['guest_price'] * $additional_guests;
        } else {
            $return_guest_price = $additional_guests_price * $additional_guests;
        }
        return $return_guest_price;
    }
}

if(!function_exists('homey_calculate_services_fee')) {
    function  homey_calculate_services_fee($services_fee_type, $services_fee, $total_price) {
        
        if( !empty($services_fee) && $services_fee != 0 ) {
            if ( $services_fee_type == 'percent') {
              
                if( empty($services_fee) || $services_fee == 0 ) {
                    $fee = 0;

                } else {
                    $fee = round($services_fee*$total_price/100,2);
                }

            } else {
                $fee = $services_fee;
            }
            return $fee;
        }
        return '';
        
    }
}

if(!function_exists('homey_calculate_taxes')) {
    function homey_calculate_taxes($taxes_percent, $total_price) {
        
        if( empty($taxes_percent) || $taxes_percent == 0 ) {
            $taxes = 0;
        } else {
            $taxes = round($taxes_percent*$total_price/100,2);
        }
        return $taxes;
        
    }
}

if(!function_exists('homey_check_weekend')) {
    function homey_check_weekend($weekday, $weekends_days, $weekends_price) {

        if(empty($weekends_price) && $weekends_price == 0 ) {
            return false;

        } else {

            if($weekends_days == 'sat_sun' && ($weekday ==6 || $weekday==7)) {
                return true;

            } elseif($weekends_days == 'fri_sat' && ($weekday ==5 || $weekday==6)) {
                    return true;

            } elseif($weekends_days == 'fri_sat_sun' && ($weekday ==5 || $weekday ==6 || $weekday==7)) {
                   return true;

            } else {
                return false;
            }
        }
        return false;

    }
}

if(!function_exists('homey_cal_weekend_price') ) {
    function homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price){
        $weekday = date('N', $check_in_unix);

        if($weekends_days == 'sat_sun' && ($weekday ==6 || $weekday==7)) {
            $return_price = homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

        } elseif($weekends_days == 'fri_sat' && ($weekday ==5 || $weekday==6)) {
                $return_price = homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

        } elseif($weekends_days == 'fri_sat_sun' && ($weekday ==5 || $weekday ==6 || $weekday==7)) {
               $return_price = homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

        } else { 
            $return_price = $price_per_night;
        }
      
        return $return_price;
                
    }
}


if(!function_exists('homey_get_weekend_price')) {
    function homey_get_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price) {
        if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['weekend_price'] ) &&  $period_price[$check_in_unix]['weekend_price']!=0 ){

            $return_price = $period_price[$check_in_unix]['weekend_price'];
        
        } elseif(!empty($weekends_price) && $weekends_price != 0) {
            $return_price = $weekends_price;
        } else {
            $return_price = $price_per_night;
        }

        return $return_price;
    }
}

add_action( 'wp_ajax_homey_confirm_reservation', 'homey_confirm_reservation' );  
if(!function_exists('homey_confirm_reservation')) {
    function homey_confirm_reservation() {
        global $current_user;
        $reservation_id = intval($_POST['reservation_id']);
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];
        $local = homey_get_localization();

        $no_upfront = homey_option('reservation_payment');

        $date = date( 'Y-m-d g:i:s', current_time( 'timestamp', 0 ));


        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);


        if( $listing_owner != $userID ) {
            echo json_encode(
                array( 
                    'success' => false, 
                    'message' => homey_get_reservation_notification('not_owner')
                ) 
            );
            wp_die();
        }

        
        // If no upfront option select then book at this step
        if($no_upfront == 'no_upfront') { 

            if($is_hourly =='yes') {
              do_action('homey_hourly_booking_skip_payment_card', $reservation_id);
            } else {
              do_action('homey_booking_skip_payment_card', $reservation_id);
            }

            echo json_encode(
                array( 
                    'success' => true, 
                    'message' => homey_get_reservation_notification('booked')
                ) 
            );

        } else {
            // Set reservation status from under_review to available
            update_post_meta($reservation_id, 'reservation_status', 'available');
            update_post_meta($reservation_id, 'reservation_confirm_date_time', $date );

            echo json_encode(
                array( 
                    'success' => true, 
                    'message' => homey_get_reservation_notification('available')
                ) 
            );

            $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
            homey_email_composer( $renter_email, 'confirm_reservation', $email_args );
        }

        wp_die();
    }
}
add_action('homey_booking_skip_payment_card', 'homey_booking_with_no_upfront');
if(!function_exists('homey_booking_with_no_upfront')) {
    function homey_booking_with_no_upfront($reservation_id) {
        global $current_user;
        $local = homey_get_localization();
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $allowded_html = array();
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true );
        $reservation_meta = get_post_meta( $reservation_id, 'reservation_meta', true );
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];
        $check_in_hour  = strtotime(wp_kses ( $reservation_meta['check_in_hour'], $allowded_html ));
        $check_out_hour = strtotime(wp_kses ( $reservation_meta['check_out_hour'], $allowded_html ));
        $args = [
            "post_type" => "homey_reservation",
            "meta_query" => [
                [
                    "key" => "reservation_status",
                    "value" => ["under_review", "available"],
                    "compare" => "IN"
                ],
                [
                    "key" => "reservation_listing_id",
                    "value" => $listing_id,
                    "compare" => "="
                ],
                [
                    "key" => "listing_renter",
                    "value" => $userID,
                    "compare" => "!="
                ]
            ]
        ];

        $query = new WP_Query($args);
        if($query->have_posts()){
            $posts = $query->posts;
            foreach ($posts as $key => $post) {
                $resmeta  = get_post_meta( $post->ID, 'reservation_meta', true );
                $cInHour  = strtotime(wp_kses ( $resmeta['check_in_hour'], $allowded_html ));
                $cOutHour = strtotime(wp_kses ( $resmeta['check_out_hour'], $allowded_html ));
                if((($cInHour >= $check_in_hour && $cInHour < $check_out_hour)
                        || ($cOutHour > $check_in_hour && $cOutHour <= $check_out_hour))
                    || (($check_in_hour >= $cInHour && $check_in_hour < $cOutHour)
                        || ($check_out_hour > $cInHour && $check_out_hour <= $cOutHour))
                ){
                    /**
                     * Annuler toutes les réservations des autres utilisateur qui coincident avec ce date et les heures
                     */
                    $reason = homey_option('homey_declined_reservation');
                    $prevValue = get_post_meta($post->ID, 'reservation_status', true);
                    update_post_meta( $post->ID, 'reservation_status', 'declined', $prevValue );
                    update_post_meta( $post->ID, 'res_decline_reason', $reason);
                    $email_args = array('reservation_detail_url' => reservation_detail_link($post->ID) );
                    homey_email_composer( $renter_email, 'declined_reservation', $email_args );
                }
            }
        }
        
        //Book days
        $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        // Update reservation status
        update_post_meta( $reservation_id, 'reservation_status', 'booked' );

        // Emails
        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);

        $owner = homey_usermeta($listing_owner);
        $owner_email = $owner['email'];

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        homey_email_composer( $renter_email, 'booked_reservation', $email_args );
        homey_email_composer( $owner_email, 'admin_booked_reservation', $email_args );

        return true;
    }
}

add_action( 'wp_ajax_homey_decline_reservation', 'homey_decline_reservation' );  
if(!function_exists('homey_decline_reservation')) {
    function homey_decline_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $reason = sanitize_text_field($_POST['reason']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

        $renter = homey_usermeta($listing_renter);
        $renter_email = $renter['email'];

        if( $listing_owner != $userID ) {
            echo json_encode(
                array( 
                    'success' => false, 
                    'message' => $local['listing_owner_text']
                ) 
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'declined');
        update_post_meta($reservation_id, 'res_decline_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        echo json_encode(
            array( 
                'success' => true, 
                'message' => esc_html__('success', 'homey')
            ) 
        );

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        homey_email_composer( $renter_email, 'declined_reservation', $email_args );
        wp_die();
    }
}

add_action( 'wp_ajax_homey_cancelled_reservation', 'homey_cancelled_reservation' );  
if(!function_exists('homey_cancelled_reservation')) {
    function homey_cancelled_reservation() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();

        $reservation_id = intval($_POST['reservation_id']);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $reason = sanitize_text_field($_POST['reason']);
        $host_cancel = sanitize_text_field($_POST['host_cancel']);

        $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
        $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

        

        if( ($listing_renter != $userID) && ($listing_owner != $userID) ) {
            echo json_encode(
                array( 
                    'success' => false, 
                    'message' => $local['listing_renter_text']
                ) 
            );
            wp_die();
        }

        if(empty($reason)) {
            echo json_encode(
                array( 
                    'success' => false, 
                    'message' => $local['reason_text_req']
                ) 
            );
            wp_die();
        }

        // Set reservation status from under_review to available
        update_post_meta($reservation_id, 'reservation_status', 'cancelled');
        update_post_meta($reservation_id, 'res_cancel_reason', $reason);

        //Remove Pending Dates
        $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

        //Remove Booked Dates
        $booked_dates_array = homey_remove_booking_booked_days($listing_id, $reservation_id);
        update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

        if($host_cancel == 'cancelled_by_host') {
            $renter = homey_usermeta($listing_renter);
            $to_email = $renter['email'];
        } else {
            $owner = homey_usermeta($listing_owner);
            $to_email = $owner['email'];
        }
        

        echo json_encode(
            array( 
                'success' => true, 
                'message' => esc_html__('success', 'homey')
            ) 
        );

        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
        
        homey_email_composer( $to_email, 'cancelled_reservation', $email_args );
        wp_die();
    }
}


if(!function_exists('homey_get_reservation_days')) {
    function homey_get_reservation_days($listing_id) {
        $args=array(
        'post_type'        => 'homey_reservation',
        'post_status'      => 'any',
        'posts_per_page'   => -1,
        'meta_query' => array(
                array(
                    'key'       => 'reservation_id',
                    'value'     => $listing_id,
                    'type'      => 'NUMERIC',
                    'compare'   => '='
                ),
                array(
                    'key'       =>  'booking_status',
                    'value'     =>  'confirmed',
                    'compare'   =>  '='
                )
            )
        );
    }
}

/*-----------------------------------------------------------------------------------*/
// Add in-review post status Expired
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists('homey_approved_post_status') ) {
    function homey_approved_post_status() {

        $args = array(
            'label'                     => _x( 'Approved', 'Approved', 'homey' ),
            'label_count'               => _n_noop( 'Approved (%s)',  'Approved (%s)', 'homey' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'publish', $args );

    }
    
}

if ( ! function_exists('homey_in-review_post_status') ) {
    function homey_in_review_post_status() {

        $args = array(
            'label'                     => _x( 'Waiting Approval', 'Waiting Approval', 'homey' ),
            'label_count'               => _n_noop( 'Waiting Approval (%s)',  'Waiting Approval (%s)', 'homey' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'pending', $args );

    }
   
}

if ( ! function_exists('homey_declined_post_status') ) {
    function homey_declined_post_status() {

        $args = array(
            'label'                     => _x( 'Declined', 'Status General Name', 'homey' ),
            'label_count'               => _n_noop( 'Declined (%s)',  'Declined (%s)', 'homey' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'declined', $args );

    }
    add_action( 'init', 'homey_declined_post_status', 1 );
}


/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form
-------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_stripe_payment') ) {
    function homey_stripe_payment( $reservation_id ) {

        require_once( HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php' );
        $stripe_secret_key = homey_option('stripe_secret_key');
        $stripe_publishable_key = homey_option('stripe_publishable_key');
        $allowded_html = array();

        $stripe = array(
            "secret_key" => $stripe_secret_key,
            "publishable_key" => $stripe_publishable_key
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $submission_currency = homey_option('payment_currency');
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);
        
        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
        $upfront_payment = floatval( $reservation_meta['upfront'] );

        if( $submission_currency == 'JPY') {
            $upfront_payment = $upfront_payment;
        } else {
            $upfront_payment = $upfront_payment * 100;
        }
        

        print '
        <div class="homey_stripe_simple">
            <script src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="' . $stripe_publishable_key . '"
            data-amount="' . $upfront_payment . '"
            data-email="' . $user_email . '"
            data-zip-code="true"
            data-billing-address="true"
            data-locale="'.get_locale().'"
            data-currency="' . $submission_currency . '"
            data-label="' . esc_html__('Pay with Credit Card', 'homey') . '"
            data-description="' . esc_html__('Reservation Payment', 'homey') . '">
            </script>
        </div>
        <input type="hidden" id="reservation_id_for_stripe" name="reservation_id_for_stripe" value="' . $reservation_id . '">
        <input type="hidden" id="reservation_pay" name="reservation_pay" value="1">
        <input type="hidden" id="is_hourly" name="is_hourly" value="0">
        <input type="hidden" id="is_instance_booking" name="is_instance_booking" value="0">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
    }
}

/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form
-------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_stripe_payment_instance') ) {
    function homey_stripe_payment_instance($listing_id, $check_in, $check_out, $guests) {

        require_once( HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php' );
        $stripe_secret_key = homey_option('stripe_secret_key');
        $stripe_publishable_key = homey_option('stripe_publishable_key');
        $allowded_html = array();

        $stripe = array(
            "secret_key" => $stripe_secret_key,
            "publishable_key" => $stripe_publishable_key
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $submission_currency = homey_option('payment_currency');
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $listing_id     = intval($listing_id);
        $check_in_date  = wp_kses ($check_in, $allowded_html);
        $check_out_date = wp_kses ($check_out, $allowded_html);
        $renter_message = '';//$renter_message;
        $guests         = intval($guests);

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if(!$is_available) {

            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $check_message,
                    'payment_execute_url' => ''
                ) 
             );
            wp_die();

        } else {

            $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
            $upfront_payment  =  floatval( $prices_array['upfront_payment'] );
        }

        if( $submission_currency == 'JPY') {
            $upfront_payment = $upfront_payment;
        } else {
            $upfront_payment = $upfront_payment * 100;
        }
        

        print '
        <div class="homey_stripe_simple">
            <script src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="' . $stripe_publishable_key . '"
            data-amount="' . $upfront_payment . '"
            data-email="' . $user_email . '"
            data-zip-code="true"
            data-billing-address="true"
            data-locale="'.get_locale().'"
            data-currency="' . $submission_currency . '"
            data-label="' . esc_html__('Pay with Credit Card', 'homey') . '"
            data-description="' . esc_html__('Reservation Payment', 'homey') . '">
            </script>
        </div>
        <input type="hidden" id="reservation_id_for_stripe" name="reservation_id_for_stripe" value="0">
        <input type="hidden" id="reservation_pay" name="reservation_pay" value="1">
        <input type="hidden" id="is_instance_booking" name="is_instance_booking" value="1">
        <input type="hidden" name="check_in_date" value="'.$check_in_date.'">
        <input type="hidden" name="check_out_date" value="'.$check_out_date.'">
        <input type="hidden" name="guests" value="'.$guests.'">
        <input type="hidden" name="listing_id" value="'.$listing_id.'">
        <input type="hidden" id="guest_message" name="guest_message" value="'.$renter_message.'">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
    }
}

add_action( 'wp_ajax_homey_booking_paypal_payment', 'homey_booking_paypal_payment' );  
if( !function_exists('homey_booking_paypal_payment') ) {
    function homey_booking_paypal_payment() {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url( home_url('/') );
        wp_get_current_user();
        $userID =   $current_user->ID;
        $local = homey_get_localization();
        $reservation_id = intval($_POST['reservation_id']);

        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'checkout-security-nonce' ) ) {

             echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['security_check_text'] 
                ) 
             );
             wp_die();
        }

        if(empty($reservation_id)) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['something_went_wrong'] 
                ) 
             );
             wp_die();
        }

        $reservation = get_post($reservation_id);

        if( $reservation->post_author != $userID ) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['belong_to'] 
                ) 
             );
             wp_die();
        }
        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);

        if( $reservation_status != 'available') {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['something_went_wrong'] 
                ) 
             );
             wp_die();
        }


        $currency = homey_option('payment_currency');
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        

        $listing_id     = intval($reservation_meta['listing_id']);
        $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
        $check_out_date = wp_kses ( $reservation_meta['check_out_date'], $allowded_html );
        $guests         = intval($reservation_meta['guests']);
        
        $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);

        
        $is_paypal_live         =  homey_option('paypal_api');
        $host                   =  'https://api.sandbox.paypal.com';
        $upfront_payment          =  floatval( $reservation_meta['upfront'] );
        $submission_curency     =  esc_html( $currency );
        $payment_description    =  esc_html__('Reservation payment on ','homey').$blogInfo;

        $total_price =  number_format( $upfront_payment, 2, '.','' );

        // Check if payal live
        if( $is_paypal_live =='live'){
            $host='https://api.paypal.com';
        }

        $url             =   $host.'/v1/oauth2/token';
        $postArgs        =   'grant_type=client_credentials';

        // Get Access token
        $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
        $url             =   $host.'/v1/payments/payment';

        $payment_page_link = homey_get_template_link_2('template/dashboard-payment.php');
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
        
        $cancel_link = add_query_arg( array('reservation_id' => $reservation_id), $payment_page_link );
        $return_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $return_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array("payment_method" => "paypal"),
        );

        /* Prepare basic payment details
        *--------------------------------------*/
        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total_price,
                'currency' => $submission_curency,
                'details' => array(
                    'subtotal' => $total_price,
                    'tax' => '0.00',
                    'shipping' => '0.00'
                )
            ),
            'description' => $payment_description
        );


        /* Prepare individual items
        *--------------------------------------*/ 
        $payment['transactions'][0]['item_list']['items'][] = array(
            'quantity' => '1',
            'name' => esc_html__('Reservation Payment','homey'),
            'price' => $total_price,
            'currency' => $submission_curency,
            'sku' => 'Paid Reservation',
        );

        /* Convert PHP array into json format
        *--------------------------------------*/
        $jsonEncode = json_encode($payment);
        $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

        //print_r($json_response);
        foreach ($json_response['links'] as $link) {
            if($link['rel'] == 'execute'){
                $payment_execute_url = $link['href'];
            } else  if($link['rel'] == 'approval_url'){
                $payment_approval_url = $link['href'];
            }
        }

        // Save data in database for further use on processor page
        $output['payment_execute_url'] = $payment_execute_url;
        $output['paypal_token']        = $paypal_token;
        $output['reservation_id']      = $reservation_id;

        $output['listing_id']          = '';
        $output['check_in_date']       = '';
        $output['check_out_date']      = '';
        $output['guests']              = '';
        $output['renter_message']      = '';
        $output['is_instance_booking'] = 0;
        $output['is_hourly'] = 1;

        $save_output[$userID]   =   $output;
        update_option('homey_paypal_transfer',$save_output);

        echo json_encode( 
            array( 
                'success' => true, 
                'message' => 'success',
                'payment_execute_url' => $payment_approval_url
            ) 
         );

        wp_die();
    }
}

add_action( 'wp_ajax_homey_instance_booking_paypal_payment', 'homey_instance_booking_paypal_payment' );  
if( !function_exists('homey_instance_booking_paypal_payment') ) {
    function homey_instance_booking_paypal_payment() {
        global $current_user;
        $allowded_html = array();
        $blogInfo = esc_url( home_url('/') );
        wp_get_current_user();
        $userID =   $current_user->ID;
        $local = homey_get_localization();
        
        //check security
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'checkout-security-nonce' ) ) {

             echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['security_check_text'] 
                ) 
             );
             wp_die();
        }

        $currency = homey_option('payment_currency');

        $listing_id     = intval($_POST['listing_id']);
        $check_in_date  = wp_kses ($_POST['check_in'], $allowded_html);
        $check_out_date = wp_kses ($_POST['check_out'], $allowded_html);
        $renter_message = wp_kses ($_POST['renter_message'], $allowded_html);
        $guests         = intval($_POST['guests']);

        $check_availability = check_booking_availability($check_in_date, $check_out_date, $listing_id, $guests);
        $is_available = $check_availability['success'];
        $check_message = $check_availability['message'];

        if(!$is_available) {

            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $check_message,
                    'payment_execute_url' => ''
                ) 
             );
            wp_die();

        } else {
            $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests);
            
            $is_paypal_live         =  homey_option('paypal_api');
            $host                   =  'https://api.sandbox.paypal.com';
            $upfront_payment          =  floatval( $prices_array['upfront_payment'] );
            $submission_curency     =  esc_html( $currency );
            $payment_description    =  esc_html__('Reservation payment on ','homey').$blogInfo;

            $total_price =  number_format( $upfront_payment, 2, '.','' );

            // Check if payal live
            if( $is_paypal_live =='live'){
                $host='https://api.paypal.com';
            }

            $url             =   $host.'/v1/oauth2/token';
            $postArgs        =   'grant_type=client_credentials';

            // Get Access token
            $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
            $url             =   $host.'/v1/payments/payment';

            $instance_payment_page_link = homey_get_template_link_2('template/template-instance-booking.php');
            $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
            
            $cancel_link = add_query_arg( 
                array(
                    'check_in' => $check_in_date,
                    'check_out' => $check_out_date,
                    'guest' => $guests,
                    'listing_id' => $listing_id,
                ), $instance_payment_page_link );
            $reservation_id = NULL;
            $return_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

            $payment = array(
                'intent' => 'sale',
                "redirect_urls" => array(
                    "return_url" => $return_link,
                    "cancel_url" => $cancel_link
                ),
                'payer' => array("payment_method" => "paypal"),
            );

            /* Prepare basic payment details
            *--------------------------------------*/
            $payment['transactions'][0] = array(
                'amount' => array(
                    'total' => $total_price,
                    'currency' => $submission_curency,
                    'details' => array(
                        'subtotal' => $total_price,
                        'tax' => '0.00',
                        'shipping' => '0.00'
                    )
                ),
                'description' => $payment_description
            );


            /* Prepare individual items
            *--------------------------------------*/ 
            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity' => '1',
                'name' => esc_html__('Reservation Payment','homey'),
                'price' => $total_price,
                'currency' => $submission_curency,
                'sku' => 'Paid Reservation',
            );

            /* Convert PHP array into json format
            *--------------------------------------*/
            $jsonEncode = json_encode($payment);
            $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

            //print_r($json_response);
            foreach ($json_response['links'] as $link) {
                if($link['rel'] == 'execute'){
                    $payment_execute_url = $link['href'];
                } else  if($link['rel'] == 'approval_url'){
                    $payment_approval_url = $link['href'];
                }
            }

            // Save data in database for further use on processor page
            $output['payment_execute_url'] = $payment_execute_url;
            $output['paypal_token']        = $paypal_token;
            $output['reservation_id']      = '';
            $output['listing_id']          = $listing_id;
            $output['check_in_date']       = $check_in_date;
            $output['check_out_date']      = $check_out_date;
            $output['guests']              = $guests;
            $output['renter_message']      = $renter_message;
            $output['is_instance_booking'] = 1;

            $save_output[$userID]   =   $output;
            update_option('homey_paypal_transfer',$save_output);

            echo json_encode( 
                array( 
                    'success' => true, 
                    'message' => $local['processing_text'],
                    'payment_execute_url' => $payment_approval_url
                ) 
             );
            wp_die();
        }
    }
}
  
add_action( 'wp_ajax_homey_instance_step_1', 'homey_instance_step_1' );  
if( !function_exists('homey_instance_step_1') ) {
    function homey_instance_step_1() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();

        $first_name     =  wp_kses ( $_POST['first_name'], $allowded_html );
        $last_name    =  wp_kses ( $_POST['last_name'], $allowded_html );
        $phone    =  wp_kses ( $_POST['phone'], $allowded_html );

        if ( !is_user_logged_in() || $userID === 0 ) {   
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['login_for_reservation'] 
                ) 
             );
             wp_die();
        }

        if(empty($first_name)) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['first_name_req'] 
                ) 
             );
             wp_die();
        }

        if(empty($last_name)) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['last_name_req'] 
                ) 
             );
             wp_die();
        }

        if(empty($phone)) {
            echo json_encode( 
                array( 
                    'success' => false, 
                    'message' => $local['phone_req'] 
                ) 
             );
             wp_die();
        }
        

        update_user_meta( $userID, 'first_name', $first_name);
        update_user_meta( $userID, 'last_name', $last_name);
        update_user_meta( $userID, 'phone', $phone);

        echo json_encode( 
            array( 
                'success' => true, 
                'message' => '' 
            ) 
         );
         wp_die();
    }
}


if(!function_exists('homey_get_reservation_label')) {
    function homey_get_reservation_label($status) {
        $status_label = '';
        $local = homey_get_localization();

        if(homey_is_renter()) {
            
            if($status == 'under_review') {
                $status_label = '<span class="label label-warning">'.$local['under_review_label'].'</span>';
            } elseif($status == 'available') {
                $status_label = '<span class="label label-secondary">'.$local['res_avail_label'].'</span>';
            } 

        } else {
            if($status == 'under_review') {
                $status_label = '<span class="label label-secondary">'.$local['new_label'].'</span>';
                
            } elseif($status == 'available') {
                $status_label = '<span class="label label-secondary">'.$local['payment_process_label'].'</span>';
            } 
        }

        if($status == 'booked') {
            $status_label = '<span class="label label-success">'.$local['res_booked_label'].'</span>';

        } elseif ($status == 'declined') {
            $status_label = '<span class="label label-danger">'.$local['res_declined_label'].'</span>';

        } elseif ($status == 'cancelled') {
            $status_label = '<span class="label label-grey">'.$local['res_cancelled_label'].'</span>';
        }

        return $status_label;

    }
}

if(!function_exists('homey_reservation_label')) {
    function homey_reservation_label($status) {
        echo homey_get_reservation_label($status);
    }
}

if(!function_exists('homey_get_reservation_notification')) {
    function homey_get_reservation_notification($status) {
        $notification = '';
        $local = homey_get_localization();

        if( homey_is_renter() ) {

            if($status == 'under_review') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.esc_html__('Your request has been submitted to the host to be confirmed availability.', 'homey').'
                            </div>';
            } elseif($status == 'available') {
                $notification = '<div class="alert alert-info alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('So far so good! Host confirmed availability for this reservation. Complete the payment due.', 'homey').'
                                    </div>';
            } elseif($status == 'booked') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('Well done! Payment received the reservation has been booked.', 'homey').'
                                    </div>';

            } elseif($status == 'declined') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('Your reservation has been declined by the host', 'homey').'
                                </div>';
            } elseif($status == 'cancelled') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('You have cancellated the reservation', 'homey').'
                                </div>';
            }

        } else {
            if($status == 'under_review') {
                $notification = '<div class="alert alert-info alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.esc_html__('Horay! You received a new reservation. Confirm Availability.', 'homey').'
                            </div>';
            } elseif($status == 'available') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('You confirmed availability for this reservation.', 'homey').'
                                    </div>';
            } elseif($status == 'booked') {
                $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                        '.esc_html__('Congratulations! The reservation has been booked.', 'homey').'
                                    </div>';

            } elseif($status == 'declined') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('You have declined the reservation', 'homey').'
                                </div>';

            } elseif($status == 'cancelled') {
                $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    '.esc_html__('The reservation has been cancellated', 'homey').'
                                </div>';
            }
        }

        if($status == 'not_owner') {
            $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                '.$local['listing_owner_text'].'
                            </div>';
        }

        return $notification;

    }
}

if(!function_exists('homey_reservation_notification')) {
    function homey_reservation_notification($status) {
        echo homey_get_reservation_notification($status);
    }
}

if(!function_exists('homey_get_reservation_action')) {
    function homey_get_reservation_action($status, $upfront_payment, $payment_link, $ID, $class) {
        $action = '';
        $local = homey_get_localization();

        if(homey_is_renter()) {

            if($status == 'under_review') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i>'.esc_html__('Submitted', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'available') {
                $action = '<a href="'.esc_url($payment_link).'" class="btn btn-success '.esc_attr($class).'">'.esc_html__('Pay Now', 'homey').' '.$upfront_payment.'</a>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            } elseif ($status == 'booked') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Booked', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light btn-full-width" data-toggle="collapse" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';
            }

        } else {
            $checkoutdate = get_post_meta( $ID, "reservation_checkout_hour", true );
            $checkoutdate = strtotime($checkoutdate);
            $now = strtotime("now");
            if($status == 'under_review') {
                if($checkoutdate >= $now){
                    $action = '<button data-reservation_id="'.intval($ID).'" class="confirm-reservation btn btn-success '.esc_attr($class).'">'.esc_html__('Confirm Availability', 'homey').'</button>';
                }
                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';
            } elseif ($status == 'available') {
                $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i>'.esc_html__('Available', 'homey').'</span>';

                $action .= '<button class="btn btn-grey-light '.esc_attr($class).'" data-toggle="collapse" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Decline', 'homey').'</button>';


            } elseif ($status == 'booked') {
                    $action = '<span class="btn btn-success-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Booked', 'homey').'</span>';

                    $action .= '<button class="btn btn-grey-light btn-full-width" data-toggle="collapse" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">'.esc_html__('Cancel', 'homey').'</button>';

            }

        }

        if ($status == 'declined') {
            $action = '<span class="btn btn-danger-outlined '.esc_attr($class).'"><i class="fa fa-check-circle-o"></i> '.esc_html__('Declined', 'homey').'</span>';
        }

        return $action;

    }
}

if(!function_exists('homey_reservation_action')) {
    function homey_reservation_action($status, $upfront_payment, $payment_link, $ID, $class) {
        echo homey_get_reservation_action($status, $upfront_payment, $payment_link, $ID, $class);
    }
}

add_action( 'wp_ajax_homey_make_date_unavaiable', 'homey_make_date_unavaiable' ); 
if(!function_exists('homey_make_date_unavaiable')) {
    function homey_make_date_unavaiable() {
        global $current_user;
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $current_user   = wp_get_current_user();
        $userID         = $current_user->ID;
        $local          = homey_get_localization();

        $listing_id     = intval($_POST['listing_id']);
        $the_post       = get_post($listing_id);
        $post_owner     = $the_post->post_author;
        $selected_date = $_POST['selected_date'];

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

        $check_in_date  = $selected_date;
        
        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true );
        
        if( !is_array($reservation_unavailable_array) || empty($reservation_unavailable_array) ) {
            $reservation_unavailable_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);
                
        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix =   $check_in->getTimestamp();

            if( array_key_exists($check_in_unix, $reservation_unavailable_array)) {
                unset($reservation_unavailable_array[$check_in_unix]);
                echo json_encode( 
                    array( 
                        'success' => true, 
                        'message' => 'made_available' 
                    ) 
                 );
            } else {
                $reservation_unavailable_array[$check_in_unix] = $listing_id;
                echo json_encode( 
                    array( 
                        'success' => true, 
                        'message' => 'made_unavailable' 
                    ) 
                 );
            }      
        }     
      
        //return $reservation_unavailable_array;

        //$unavailable_days_array = homey_get_unavailable_dates($listing_id, $selected_date);
        update_post_meta($listing_id, 'reservation_unavailable', $reservation_unavailable_array);

        /*echo json_encode( 
            array( 
                'success' => true, 
                'message' => '' 
            ) 
         );*/
         wp_die();
    }
}

/*if (!function_exists("homey_get_unavailable_dates")) {
    function homey_get_unavailable_dates($listing_id, $selected_date) {
        $now = time();
        $daysAgo = $now-3*24*60*60;

        $check_in_date  = $selected_date;
        
        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true );
        
        if( !is_array($reservation_unavailable_array) || empty($reservation_unavailable_array) ) {
            $reservation_unavailable_array  = array();
        }

        $unix_time_start = strtotime ($check_in_date);
                
        if ($unix_time_start > $daysAgo) {
            $check_in       =   new DateTime($check_in_date);
            $check_in_unix =   $check_in->getTimestamp();

            if( array_key_exists($check_in_unix, $reservation_unavailable_array) {
                unset($reservation_unavailable_array[$check_in_unix]);
            } else {
                $reservation_unavailable_array[$check_in_unix] = $listing_id;
            }      
        }     
      
        return $reservation_unavailable_array;
    }
}*/

















