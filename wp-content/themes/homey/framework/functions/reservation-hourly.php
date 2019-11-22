<?php
add_action( 'wp_ajax_nopriv_homey_add_hourly_reservation', 'homey_add_hourly_reservation' );
add_action( 'wp_ajax_homey_add_hourly_reservation', 'homey_add_hourly_reservation' );
if( !function_exists('homey_add_hourly_reservation') ) {
  /**
   * @throws Exception
   */
  function homey_add_hourly_reservation() {
    global $current_user;
    $current_user = wp_get_current_user();
    $userID       = $current_user->ID;
    $local = homey_get_localization();
    $allowded_html = array();
    $reservation_meta = array();
    
    $listing_id = intval($_POST['listing_id']);
    $listing_time_zone = get_post_meta($listing_id, "listing_timezone", true) ?? "Europe/Paris";
    $dateTimeZone = new \DateTimeZone($listing_time_zone);
    $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
    $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
    $start_hour    =  wp_kses ( $_POST['start_hour'], $allowded_html );
    $end_hour    =  wp_kses ( $_POST['end_hour'], $allowded_html );
    $guests   =  intval($_POST['guests']);
    $guest_message = stripslashes ( $_POST['guest_message'] );
    $title = $local['reservation_text'];
    $check_in_hour = $check_in_date.' '.$start_hour;
    $inputFormat = "Y-m-d H:i";
    // Use listing timezone to identify the region.
    $check_in_hour = new \DateTime($check_in_hour, $dateTimeZone);
    // @TODO Convert to UTC before save to base
    // $check_in_hour->setTimezone(new \DateTimeZone("UTC"));
    $check_in_hour = $check_in_hour->format($inputFormat);
    
    $check_out_hour = $check_in_date.' '.$end_hour;
    $check_out_hour = new \DateTime($check_out_hour, $dateTimeZone);
    // $check_out_hour->setTimezone(new \DateTimeZone("UTC"));
    $check_out_hour = $check_out_hour->format($inputFormat);
    
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
    
    $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
    $is_available = $check_availability['success'];
    $check_message = $check_availability['message'];
    
    if($is_available) {
      $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
      
      $reservation_meta['no_of_hours'] = $prices_array['hours_count'];
      $reservation_meta['additional_guests'] = $prices_array['additional_guests'];
      
      $upfront_payment = $prices_array['upfront_payment'];
      $balance = $prices_array['balance'];
      $total_price = $prices_array['total_price'];
      
      $reservation_meta['check_in_date'] = $check_in_date;
      $reservation_meta['check_in_hour'] = $check_in_hour;
      $reservation_meta['check_out_hour'] = $check_out_hour;
      $reservation_meta['start_hour'] = $start_hour;
      $reservation_meta['end_hour'] = $end_hour;
      $reservation_meta['guests'] = $guests;
      $reservation_meta['listing_id'] = $listing_id;
      
      $reservation_meta['price_per_hour'] = $prices_array['price_per_hour'];
      $reservation_meta['hours_total_price'] = $prices_array['hours_total_price']; //$prices_array['hours_total_price'];
      
      //$reservation_meta['cleaning_fee'] = $prices_array['cleaning_fee'];
      //$reservation_meta['city_fee'] = $prices_array['city_fee'];
      //$reservation_meta['services_fee'] = $prices_array['services_fee'];
      
      $reservation_meta['taxes'] = $prices_array['taxes'];
      $reservation_meta['taxes_percent'] = $prices_array['taxes_percent'];
      //$reservation_meta['security_deposit'] = $prices_array['security_deposit'];
      
      $reservation_meta['additional_guests_price'] = $prices_array['additional_guests_price'];
      $reservation_meta['additional_guests_total_price'] = $prices_array['additional_guests_total_price'];
      $reservation_meta['booking_has_weekend'] = $prices_array['booking_has_weekend'];
      $reservation_meta['booking_has_custom_pricing'] = $prices_array['booking_has_custom_pricing'];
      $reservation_meta['upfront'] = $upfront_payment;
      $reservation_meta['balance'] = $balance;
      $reservation_meta['total'] = $total_price;
      
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
      update_post_meta($reservation_id, 'reservation_checkin_hour', $check_in_hour);
      update_post_meta($reservation_id, 'reservation_checkout_hour', $check_out_hour);
      update_post_meta($reservation_id, 'reservation_guests', $guests);
      update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
      update_post_meta($reservation_id, 'reservation_status', 'under_review');
      update_post_meta($reservation_id, 'is_hourly', 'yes');
      
      update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
      update_post_meta($reservation_id, 'reservation_balance', $balance);
      update_post_meta($reservation_id, 'reservation_total', $total_price);
      
      $pending_dates_array = homey_get_booking_pending_hours($listing_id);
      update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);
      
      echo json_encode(
        array(
          'success' => true,
          'message' => $local['request_sent']
        )
      );
      
      $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
      homey_email_composer( $owner_email, 'new_reservation', $email_args );
      if($guest_message != ""){
        do_action('homey_create_messages_thread', $guest_message, $reservation_id);
      }
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

if( !function_exists('homey_add_hourly_instance_booking') ) {
  function homey_add_hourly_instance_booking($listing_id, $check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests, $renter_message) {
    global $current_user;
    $current_user = wp_get_current_user();
    $userID       = $current_user->ID;
    
    $local = homey_get_localization();
    $allowded_html = array();
    $reservation_meta = array();
    
    $listing_owner_id  =  get_post_field( 'post_author', $listing_id );
    $title = $local['reservation_text'];
    
    $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
    
    $reservation_meta['no_of_hours'] = $prices_array['hours_count'];
    $reservation_meta['additional_guests'] = $prices_array['additional_guests'];
    
    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];
    
    $reservation_meta['check_in_date'] = $check_in_date;
    $reservation_meta['check_in_hour'] = $check_in_hour;
    $reservation_meta['check_out_hour'] = $check_out_hour;
    $reservation_meta['start_hour'] = $start_hour;
    $reservation_meta['end_hour'] = $end_hour;
    $reservation_meta['guests'] = $guests;
    $reservation_meta['listing_id'] = $listing_id;
    
    $reservation_meta['price_per_hour'] = $prices_array['price_per_hour'];
    $reservation_meta['hours_total_price'] = $prices_array['hours_total_price']; //$prices_array['hours_total_price'];

//        $reservation_meta['cleaning_fee'] = $prices_array['cleaning_fee'];
//        $reservation_meta['city_fee'] = $prices_array['city_fee'];
//        $reservation_meta['services_fee'] = $prices_array['services_fee'];
    
    $reservation_meta['taxes'] = $prices_array['taxes'];
    $reservation_meta['taxes_percent'] = $prices_array['taxes_percent'];
//        $reservation_meta['security_deposit'] = $prices_array['security_deposit'];
    
    $reservation_meta['additional_guests_price'] = $prices_array['additional_guests_price'];
    $reservation_meta['additional_guests_total_price'] = $prices_array['additional_guests_total_price'];
    $reservation_meta['booking_has_weekend'] = $prices_array['booking_has_weekend'];
    $reservation_meta['booking_has_custom_pricing'] = $prices_array['booking_has_custom_pricing'];
    $reservation_meta['upfront'] = $upfront_payment;
    $reservation_meta['balance'] = $balance;
    $reservation_meta['total'] = $total_price;
    
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
    update_post_meta($reservation_id, 'reservation_checkin_hour', $check_in_hour);
    update_post_meta($reservation_id, 'reservation_checkout_hour', $check_out_hour);
    update_post_meta($reservation_id, 'reservation_guests', $guests);
    update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
    update_post_meta($reservation_id, 'reservation_status', 'booked');
    update_post_meta($reservation_id, 'is_hourly', 'yes');
    
    update_post_meta($reservation_id, 'reservation_upfront', $upfront_payment);
    update_post_meta($reservation_id, 'reservation_balance', $balance);
    update_post_meta($reservation_id, 'reservation_total', $total_price);
    
    //Book dates
    $booked_days_array = homey_make_hours_booked($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_booked_hours', $booked_days_array);
    
    return $reservation_id;
    
  }
}

if(!function_exists('check_hourly_booking_availability')) {
  function check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests) {
    $return_array = array();
    $local = homey_get_localization();
    $booking_proceed = true;
    
    $min_book_hours = get_post_meta($listing_id, 'homey_min_book_hours', true);
    
    $booking_hide_fields = homey_option('booking_hide_fields');
    
    $homey_allow_additional_guests = get_post_meta($listing_id, 'homey_allow_additional_guests', true);
    $allowed_guests = get_post_meta($listing_id, 'homey_guests', true);
    
    if( ($homey_allow_additional_guests != 'yes') && ($guests > $allowed_guests) ) {
      $return_array['success'] = false;
      $return_array['message'] = $local['guest_allowed'].' '.$allowed_guests;
      return $return_array;
    }
    
    if(strtotime($check_out_hour) <= strtotime($check_in_hour)) {
      $booking_proceed = false;
    }
    
    if(empty($check_in_date) && empty($check_in_hour) && empty($check_out_hour)) {
      $return_array['success'] = false;
      $return_array['message'] = $local['fill_all_fields'];
      return $return_array;
      
    }
    
    if(empty($check_in_date)) {
      $return_array['success'] = false;
      $return_array['message'] = $local['choose_checkin'];
      return $return_array;
      
    }
    
    if(empty($check_in_hour) || empty($start_hour)) {
      $return_array['success'] = false;
      $return_array['message'] = $local['choose_start_hour'];
      return $return_array;
      
    }
    
    if(empty($check_out_hour) || empty($end_hour)) {
      $return_array['success'] = false;
      $return_array['message'] = $local['choose_end_hour'];
      return $return_array;
      
    }
    
    
    $time_difference = abs( strtotime($check_in_hour) - strtotime($check_out_hour) );
    $hours_count      = $time_difference/3600;
    $hours_count      = floatval($hours_count);
    
    if($hours_count < $min_book_hours) {
      $return_array['success'] = false;
      $return_array['message'] = $local['min_book_hours_error'].' '.$min_book_hours;
      return $return_array;
    }
    
    if(empty($guests) && $booking_hide_fields['guests'] != 1) {
      $return_array['success'] = false;
      $return_array['message'] = $local['choose_guests'];
      return $return_array;
      
    }
    
    if(!$booking_proceed) {
      $return_array['success'] = false;
      $return_array['message'] = $local['ins_hourly_book_proceed'];
      return $return_array;
    }
    
    $reservation_booked_array = get_post_meta($listing_id, 'reservation_booked_hours', true);
    if(empty($reservation_booked_array)) {
      $reservation_booked_array = homey_get_booked_hours($listing_id);
    }
    /*
            $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_hours', true);
            if(empty($reservation_pending_array)) {
                $reservation_pending_array = homey_get_booking_pending_hours($listing_id);
            }
    */
    $check_in_hour      = new DateTime($check_in_hour);
    $check_in_hour_unix = $check_in_hour->format('U');
    
    $check_out_hour     = new DateTime($check_out_hour);
    $check_out_hour->modify('-30 minutes');
    $check_out_hour_unix = $check_out_hour->format('U');
    while ($check_in_hour_unix <= $check_out_hour_unix) {
      
      //echo $start_hour_unix.' ===== <br/>';
      /**
       * @Todo find in indisponibilites
       */
      
      if( array_key_exists($check_in_hour_unix, $reservation_booked_array)  /*|| array_key_exists($check_in_hour_unix, $reservation_pending_array)*/ ) {
        
        $return_array['success'] = false;
        $return_array['message'] = $local['hours_not_available'];
        if(homey_is_instance_page()) {
          $return_array['message'] = $local['hour_ins_unavailable'];
        }
        return $return_array;
        
      }
      $check_in_hour->modify('+30 minutes');
      $check_in_hour_unix = $check_in_hour->format('U');
    }
    
    //dates are available
    $return_array['success'] = true;
    $return_array['message'] = $local['hours_available'];
    return $return_array;
    
  }
}

add_action( 'wp_ajax_nopriv_check_booking_availability_on_hour_change', 'check_booking_availability_on_hour_change' );
add_action( 'wp_ajax_check_booking_availability_on_hour_change', 'check_booking_availability_on_hour_change' );
if(!function_exists('check_booking_availability_on_hour_change')) {
  function check_booking_availability_on_hour_change() {
    $local = homey_get_localization();
    $allowded_html = array();
    $booking_proceed = true;
    
    $listing_id = intval($_POST['listing_id']);
    $check_in_date     =  wp_kses ( $_POST['check_in_date'], $allowded_html );
    $start_hour    =  wp_kses ( $_POST['start_hour'], $allowded_html );
    $end_hour    =  wp_kses ( $_POST['end_hour'], $allowded_html );
    
    $check_in_hour = $check_in_date.' '.$start_hour;
    $check_out_hour = $check_in_date.' '.$end_hour;
    
    $min_book_hours = get_post_meta($listing_id, 'homey_min_book_hours', true);
    
    if(empty($check_in_date)) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['choose_checkin']
        )
      );
      wp_die();
      
    }
    if(empty($start_hour)) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['choose_start_hour']
        )
      );
      wp_die();
    }
    
    if(empty($end_hour)) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['choose_end_hour']
        )
      );
      wp_die();
    }
    
    if(strtotime($check_out_hour) <= strtotime($check_in_hour)) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['ins_hourly_book_proceed']
        )
      );
      wp_die();
    }
    
    $time_difference = abs( strtotime($check_in_hour) - strtotime($check_out_hour) );
    $hours_count      = $time_difference/3600;
    $hours_count      = floatval($hours_count);
    
    if($hours_count < $min_book_hours) {
      echo json_encode(
        array(
          'success' => false,
          'message' => $local['min_book_hours_error'].' '.$min_book_hours
        )
      );
      wp_die();
    }
    
    
    $reservation_booked_array = get_post_meta($listing_id, 'reservation_booked_hours', true);
    if(empty($reservation_booked_array)) {
      $reservation_booked_array = homey_get_booked_hours($listing_id);
    }
    /*
            $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_hours', true);
            if(empty($reservation_pending_array)) {
                $reservation_pending_array = homey_get_booking_pending_hours($listing_id);
            }
    */
    $check_in_hour      = new DateTime($check_in_hour);
    $check_in_hour_unix = $check_in_hour->format('U');
    
    $check_out_hour     = new DateTime($check_out_hour);
    $check_out_hour->modify('-30 minutes');
    $check_out_hour_unix = $check_out_hour->format('U');
    $listing_meta = get_post_meta($listing_id, "homey_calendar_meta", TRUE);
    $events = [];
    if($listing_meta && $listing_meta != "" && is_array($listing_meta)){
      $calendar = \App\Models\Calendar::query()->where("google_id", $listing_meta["google_id"])->first();
      $events = $calendar->events()->get();
    }
    $indisponibilites = $events;
    
    while ($check_in_hour_unix <= $check_out_hour_unix) {
      /**
       * @Todo Find in indisponibilites
       */
      $heureIndisponible = findFromIndisponibilites($indisponibilites, $check_in_hour_unix, $check_out_hour_unix);
      //echo $start_hour_unix.' ===== <br/>';
      if($heureIndisponible or
        array_key_exists($check_in_hour_unix, $reservation_booked_array) /* || array_key_exists($check_in_hour_unix, $reservation_pending_array) */) {
        
        echo json_encode(
          array(
            'booked' => json_encode($reservation_booked_array),
            'success' => false,
            'message' => $local['hours_not_available']
          )
        );
        wp_die();
        
      }
      $check_in_hour->modify('+30 minutes');
      $check_in_hour_unix = $check_in_hour->format('U');
    }
    echo json_encode(
      array(
        //'booked' => json_encode($reservation_booked_array),
        'checkout_in_unix' => $check_in_hour_unix,
        'success' => true,
        'message' => $local['hours_available']
      )
    );
    wp_die();
  }
}

function findFromIndisponibilites($indisponibilites, $check_in_hour_unix, $check_out_hour_unix){
  /**
   * @var \App\Models\Event $indisponibilite
   */
  foreach ($indisponibilites as $indisponibilite){
    $indStartUnix =  strtotime($indisponibilite->getStartedAtAttribute($indisponibilite->started_at));
    $indEndUnix =  strtotime($indisponibilite->getEndedAtAttribute($indisponibilite->ended_at));
    if(($indStartUnix >= $check_in_hour_unix && $indStartUnix < $check_out_hour_unix)
      or ($indEndUnix < $check_out_hour_unix && $indEndUnix >= $check_in_hour_unix)){
      return TRUE;
    }
  }
  return FALSE;
}

if(!function_exists('homey_get_booked_hours')) {
  function homey_get_booked_hours($listing_id) {
    $now = time();
    //$daysAgo = $now-3*24*60*60;
    $daysAgo = $now-1*24*60*60;
    
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
          'value'     => 'booked',
          'type'      => 'CHAR',
          'compare'   => '='
        )
      )
    );
    
    $booked_hours_array = get_post_meta($listing_id, 'reservation_booked_hours', true );
    $listing_timezone = get_post_meta($listing_id, 'listing_timezone', true )  ?? 'Europe/Paris';
    $timezone = new DateTimezone($listing_timezone);
   
    if( !is_array($booked_hours_array) || empty($booked_hours_array) ) {
      $booked_hours_array  = array();
    }
    
    $wpQry = new WP_Query($args);
    
    if ($wpQry->have_posts()) {
      
      while ($wpQry->have_posts()): $wpQry->the_post();
        
        $resID = get_the_ID();
        
        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
        
        $unix_time_start = strtotime ($check_in_date);
        
        if ($unix_time_start > $daysAgo) {
          
          $check_in       =   new DateTime($check_in_date, $timezone);
          $check_in_unix  =   $check_in->format('U');
          $check_out      =   new DateTime($check_out_date, $timezone);
          $check_out_unix =   $check_out->format('U');
          
          
          $booked_hours_array[$check_in_unix] = $resID;
          
          $check_in_unix =   $check_in->format('U');
          
          while ($check_in_unix < $check_out_unix){
            
            $booked_hours_array[$check_in_unix] = $resID;
            
            //$check_in->modify('+1 hour');
            $check_in->modify('+30 minutes');
            $check_in_unix =   $check_in->format('U');
          }
          
        }
      endwhile;
      wp_reset_postdata();
    }
    
    return $booked_hours_array;
    
  }
}


if(!function_exists('homey_get_booking_pending_hours')) {
  function homey_get_booking_pending_hours($listing_id) {
    $now = time();
    //$daysAgo = $now-3*24*60*60;
    $daysAgo = $now-1*24*60*60;
    
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
    
    $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_hours', true );
    
    if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
      $pending_dates_array  = array();
    }
    
    $wpQry = new WP_Query($args);
    
    if ($wpQry->have_posts()) {
      
      while ($wpQry->have_posts()): $wpQry->the_post();
        
        $resID = get_the_ID();
        
        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
        
        $unix_time_start = strtotime ($check_in_date);
        
        if ($unix_time_start > $daysAgo) {
          
          $check_in       =   new DateTime($check_in_date);
          $check_in_unix  =   $check_in->format('U');
          $check_out      =   new DateTime($check_out_date);
          $check_out_unix =   $check_out->format('U');
          
          
          $pending_dates_array[$check_in_unix] = $resID;
          
          $check_in_unix =   $check_in->format('U');
          
          while ($check_in_unix < $check_out_unix){
            
            $pending_dates_array[$check_in_unix] = $resID;
            
            //$check_in->modify('+1 hour');
            $check_in->modify('+30 minutes');
            $check_in_unix =   $check_in->format('U');
          }
          
        }
      endwhile;
      wp_reset_postdata();
    }
    
    return $pending_dates_array;
    
  }
}

if(!function_exists('homey_get_hourly_prices')) {
  function homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests) {
    $prefix = 'homey_';
    
    $enable_services_fee = homey_option('enable_services_fee');
    $enable_taxes = homey_option('enable_taxes');
    $reservation_payment_type = homey_option('reservation_payment');
    $booking_percent = homey_option('booking_percent');
    $tax_type = homey_option('tax_type');
    $apply_taxes_on_service_fee  =   homey_option('apply_taxes_on_service_fee');
    $taxes_percent_global  =   homey_option('taxes_percent');
    $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);
    
    $period_price = get_post_meta($listing_id, 'homey_hourly_custom_period', true);
    if(empty($period_price)) {
      $period_price =  array();
    }
    
    $taxes_final = 0;
    $taxes_percent = 0;
    $total_price = 0;
    $total_guests_price = 0;
    $upfront_payment = 0;
    $prices_array['hours_total_price'] = 0;
    $booking_has_weekend = 0;
    $booking_has_custom_pricing = 0;
    $balance = 0;
    $period_hours = 0;
    $security_deposit = '';
    $additional_guests = '';
    $additional_guests_total_price = '';
    $services_fee_final = '';
    $taxes_fee_final = '';
    $prices_array = array();
    
    $listing_guests          = floatval( get_post_meta($listing_id, $prefix.'guests', true) );
    $hourly_price            = floatval( get_post_meta($listing_id, $prefix.'hour_price', true));
    $price_per_hour          = $hourly_price;
    
    $weekends_price          = floatval( get_post_meta($listing_id, $prefix.'hourly_weekends_price', true) );
    $weekends_days           = get_post_meta($listing_id, $prefix.'weekends_days', true);
    //$priceWeek               = floatval( get_post_meta($listing_id, $prefix.'priceWeek', true) ); // 7 hours
    //$priceMonthly            = floatval( get_post_meta($listing_id, $prefix.'priceMonthly', true) );  // 30 hours
    $security_deposit        = floatval( get_post_meta($listing_id, $prefix.'security_deposit', true) );
    
    $cleaning_fee            = floatval( get_post_meta($listing_id, $prefix.'cleaning_fee', true) );
    $cleaning_fee_type       = get_post_meta($listing_id, $prefix.'cleaning_fee_type', true);
    
    $city_fee                = floatval( get_post_meta($listing_id, $prefix.'city_fee', true) );
    $city_fee_type           = get_post_meta($listing_id, $prefix.'city_fee_type', true);
    
    $extra_guests_price      = floatval( get_post_meta($listing_id, $prefix.'additional_guests_price', true) );
    $additional_guests_price = $extra_guests_price;
    
    $allow_additional_guests = get_post_meta($listing_id, $prefix.'allow_additional_guests', true);
    
    $check_in        =  new DateTime($check_in_hour);
    $check_in_unix   =  $check_in->format('U');
    $check_out       =  new DateTime($check_out_hour);
    $check_out_unix  =  $check_out->format('U');
    
    $time_difference = abs( strtotime($check_in_hour) - strtotime($check_out_hour) );
    $hours_count      = $time_difference/3600;
    $hours_count      = floatval($hours_count);
    
    $total_price  = $price_per_hour * $hours_count;
    
    if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['hour_price'] ) &&  $period_price[$check_in_unix]['hour_price']!=0 ){
      $price_per_hour = $period_price[$check_in_unix]['hour_price'];
      
      $booking_has_custom_pricing = 1;
      $period_hours = $period_hours + 1;
    }
    
    /*if( $days_count > 7 && $priceWeek != 0 ) {
        $price_per_hour = $priceWeek;
    }

    if( $days_count > 30 && $priceMonthly != 0 ) {
        $price_per_hour = $priceMonthly;
    }*/
    
    
    // Check additional guests price
    if( $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests) ) {
      if( $guests > $listing_guests) {
        $additional_guests = $guests - $listing_guests;
        
        $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);
        
        $total_guests_price = $total_guests_price + $guests_price_return;
      }
    }
    
    //$check_in->modify('tomorrow');
    $check_in_unix =   $check_in->format('U');
    
    $weekday = date('N', $check_in_unix);
    if(homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
      $booking_has_weekend = 1;
      $price_per_hour = $weekends_price;
      $total_price = $weekends_price * $hours_count;
    }
    
    
    if( $cleaning_fee_type == 'daily' ) {
      $cleaning_fee = $cleaning_fee * $hours_count;
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
    /*if($enable_services_fee == 1) {
        $services_fee_type  = homey_option('services_fee_type');
        $services_fee  =   homey_option('services_fee');
        $price_for_services_fee = $total_price + $total_guests_price;
        $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
        $total_price = $total_price + $services_fee_final;
    }*/
    
    
    /*if( $city_fee_type == 'daily' ) {
        $city_fee = $city_fee * $hours_count;
        $total_price = $total_price + $city_fee;
    } else {
        $total_price = $total_price + $city_fee;
    }*/
    
    /**if(!empty($security_deposit) && $security_deposit != 0) {
    $total_price = $total_price + $security_deposit;
    }**/
    
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
    
    $prices_array['price_per_hour'] = $price_per_hour;
    $prices_array['hours_total_price'] = $price_per_hour * $hours_count; //$prices_array['hours_total_price'];
    $prices_array['total_price']     = $total_price;
    $prices_array['check_in_hour']   = $check_in_hour;
    $prices_array['check_out_hour']  = $check_out_hour;
    //$prices_array['cleaning_fee']    = $cleaning_fee;
    //$prices_array['city_fee']        = $city_fee;
    //$prices_array['services_fee']    = $services_fee_final;
    $prices_array['hours_count']      = $hours_count;
    //$prices_array['period_hours']      = $period_hours;
    $prices_array['taxes']           = $taxes_final;
    $prices_array['taxes_percent']   = $taxes_percent;
    //$prices_array['security_deposit'] = $security_deposit;
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



if(!function_exists('homey_check_hourly_weekend')) {
  function homey_check_hourly_weekend($weekday, $weekends_days, $weekends_price) {
    
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

if(!function_exists('homey_cal_hourly_weekend_price') ) {
  function homey_cal_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price){
    $weekday = date('N', $check_in_unix);
    
    $return_array = array();
    
    if($weekends_days == 'sat_sun' && ($weekday ==6 || $weekday==7)) {
      $return_price = homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price);
      
      $return_array['weekend'] = 'yes';
      $return_array['weekend_price'] = $return_price;
      
    } elseif($weekends_days == 'fri_sat' && ($weekday ==5 || $weekday==6)) {
      $return_price = homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price);
      
      $return_array['weekend'] = 'yes';
      $return_array['weekend_price'] = $return_price;
      
    } elseif($weekends_days == 'fri_sat_sun' && ($weekday ==5 || $weekday ==6 || $weekday==7)) {
      $return_price = homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price);
      
      $return_array['weekend'] = 'yes';
      $return_array['weekend_price'] = $return_price;
      
    } else {
      $return_array['weekend'] = 'no';
      $return_array['weekend_price'] = '';
    }
    
    return $return_array;
    
  }
}


if(!function_exists('homey_get_hourly_weekend_price')) {
  function homey_get_hourly_weekend_price($check_in_unix, $weekends_price, $price_per_hour, $weekends_days, $period_price) {
    if( isset($period_price[$check_in_unix]) && isset( $period_price[$check_in_unix]['weekend_price'] ) &&  $period_price[$check_in_unix]['weekend_price']!=0 ){
      
      $return_price = $period_price[$check_in_unix]['weekend_price'];
      
    } elseif(!empty($weekends_price) && $weekends_price != 0) {
      $return_price = $weekends_price;
    } else {
      $return_price = $price_per_hour;
    }
    
    return $return_price;
  }
}

add_action( 'wp_ajax_nopriv_homey_calculate_hourly_booking_cost', 'homey_calculate_hourly_booking_cost_ajax' );
add_action( 'wp_ajax_homey_calculate_hourly_booking_cost', 'homey_calculate_hourly_booking_cost_ajax' );

if( !function_exists('homey_calculate_hourly_booking_cost_ajax') ) {
  function homey_calculate_hourly_booking_cost_ajax() {
    $prefix = 'homey_';
    $local = homey_get_localization();
    $allowded_html = array();
    $output = '';
    
    $listing_id     = intval($_POST['listing_id']);
    $check_in_date  = wp_kses ( $_POST['check_in_date'], $allowded_html );
    $start_hour     = wp_kses ( $_POST['start_hour'], $allowded_html );
    $end_hour       = wp_kses ( $_POST['end_hour'], $allowded_html );
    $guests         = intval($_POST['guests']);
    /**
     * patch
     */
    $json           = wp_kses( $_POST['json'], $allowded_html );
    
    $check_in_hour = $check_in_date.' '.$start_hour;
    $check_out_hour = $check_in_date.' '.$end_hour;
    
    $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
    
    $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
    $no_of_hours = $prices_array['hours_count'];
    
    $hours_total_price = homey_formatted_price($prices_array['hours_total_price'], false);
    $prices_array['hours_total_price'] = $prices_array['hours_total_price'];
    
    //$cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
    //$services_fee = $prices_array['services_fee'];
    $taxes = $prices_array['taxes'];
    $taxes_percent = $prices_array['taxes_percent'];
    //$city_fee = homey_formatted_price($prices_array['city_fee']);
    //$security_deposit = $prices_array['security_deposit'];
    $additional_guests = $prices_array['additional_guests'];
    $additional_guests_price = $prices_array['additional_guests_price'];
    $additional_guests_total_price = $prices_array['additional_guests_total_price'];
    
    $booking_has_weekend = $prices_array['booking_has_weekend'];
    $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];
    
    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];
    
    if($no_of_hours > 1) {
      $hour_label = $local['hours_label'];
    } else {
      $hour_label = $local['hour_label'];
    }
    
    if($additional_guests > 1) {
      $add_guest_label = $local['cs_add_guests'];
    } else {
      $add_guest_label = $local['cs_add_guest'];
    }
    $jsonArray = [
      "resume" => [
        "cs_total"          => esc_attr($local['cs_total']),
        "cs_tax_fees"       => esc_attr($local['cs_tax_fees']),
        "total_price"       => ($total_price),
        "cs_view_details"   => esc_attr($local['cs_view_details'])
      ],
      "details" => [
        /**
         * placeholder
         */
      ]
    ];
    
    
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
      $output .= '<li>'.esc_attr($no_of_hours).' '.esc_attr($hour_label).' ('.esc_attr($local['with_custom_period_and_weekend_label']).') <span>'.$prices_array['hours_total_price'].'</span></li>';
      $price_per_hour_array = [
        "label" => [
          "label" => [
            "no_of_hours"                           => esc_attr($no_of_hours),
            "hour_label"                            => esc_attr($hour_label),
            "with_custom_period_and_weekend_label"  => '('.esc_attr($local['with_custom_period_and_weekend_label']).')'
          ],
          "separator" => ' '
        ],
        "value" => $prices_array['hours_total_price']
      ];
      
    } elseif($booking_has_weekend == 1) {
      $output .= '<li>'.esc_attr($price_per_hour).' x '.esc_attr($no_of_hours).' '.esc_attr($hour_label).' ('.esc_attr($with_weekend_label).') <span>'.$hours_total_price.'</span></li>';
      $price_per_hour_array = [
        "label" => [
          "label" => [
            "price_per_hour"    => $prices_array['price_per_hour'],
            "no_of_hours"       => esc_attr($no_of_hours),
            "hour_label"        => esc_attr($hour_label),
            "with_weekend_label"=> esc_attr($with_weekend_label)
          ],
          "separator" => 'x'
        ],
        "value" => $prices_array['hours_total_price']
      ];
    } elseif($booking_has_custom_pricing == 1) {
      $output .= '<li>'.esc_attr($no_of_hours).' '.esc_attr($hour_label).' ('.esc_attr($local['with_custom_period_label']).') <span>'.$hours_total_price.'</span></li>';
      $price_per_hour_array = [
        "label" => [
          "label" => [
            "no_of_hours"               => esc_attr($no_of_hours),
            "hour_label"                => esc_attr($hour_label),
            "with_custom_period_label"  => '(' . esc_attr($local['with_custom_period_label']) .')'
          ],
          "separator" => ' '
        ],
        "value" => $prices_array['hours_total_price']
      ];
    } else {
      $output .= '<li>'.esc_attr($price_per_hour).' x '.esc_attr($no_of_hours).' '.esc_attr($hour_label).' <span>'.$hours_total_price.'</span></li>';
      $price_per_hour_array = [
        "label" => [
          "label" => [
            "price_per_hour"    => $prices_array['price_per_hour'],
            "no_of_hours"       => esc_attr($no_of_hours),
            "hour_label"        => esc_attr($hour_label)
          ],
          "separator" => 'x'
        ],
        "value" => $prices_array['hours_total_price']
      ];
    }
    $jsonArray["details"]["price_per_hour"] = $price_per_hour_array;
    
    if(!empty($additional_guests)) {
      $output .= '<li>'.esc_attr($additional_guests).' '.esc_attr($add_guest_label).' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
      $jsonArray["details"]["additional_guests"] = [
        "label" => [
          "label" => [
            "additional_guests"    => esc_attr($additional_guests),
            "add_guest_label"      => esc_attr($add_guest_label),
          ],
          "separator" => ' '
        ],
        "value" => ($additional_guests_total_price)
      ];
    }
    
    /*if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
        $output .= '<li>'.esc_attr($local['cs_cleaning_fee']).' <span>'.esc_attr($cleaning_fee).'</span></li>';
        $jsonArray["details"]["cleaning_fee"] = [
            "label" => esc_attr($local['cs_cleaning_fee']),
            "value" => $prices_array['cleaning_fee']
        ];
    }*/
    
    /*if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
        $output .= '<li>'.esc_attr($local['cs_city_fee']).' <span>'.esc_attr($city_fee).'</span></li>';
        $jsonArray["details"]["city_fee"] = [
            "label" => esc_attr($local['cs_city_fee']),
            "value" => $prices_array['city_fee']
        ];
    }*/
    
    /*if(!empty($security_deposit) && $security_deposit != 0) {
        $output .= '<li>'.esc_attr($local['cs_sec_deposit']).' <span>'.homey_formatted_price($security_deposit).'</span></li>';
        $jsonArray["details"]["cs_sec_deposit"] = [
            "label" => esc_attr($local['cs_sec_deposit']),
            "value" => ($security_deposit)
        ];
    }*/
    
    /*if(!empty($services_fee) && $services_fee != 0 ) {
        $output .= '<li>'.esc_attr($local['cs_services_fee']).' <span>'.homey_formatted_price($services_fee).'</span></li>';
        $jsonArray["details"]["cs_services_fee"] = [
            "label" => esc_attr($local['cs_services_fee']),
            "value" => ($services_fee)
        ];
    }*/
    
    if(!empty($taxes) && $taxes != 0 ) {
      $output .= '<li>'.esc_attr($local['cs_taxes']).' '.esc_attr($taxes_percent).'% <span>'.homey_formatted_price($taxes).'</span></li>';
      $jsonArray["details"]["cs_taxes"] = [
        "label" => [
          "label" => [
            "cs_taxes"      => esc_attr($local['cs_taxes']),
            "taxes_percent" => esc_attr($taxes_percent).'%',
          ],
          "separator" => ' '
        ],
        "value" => ($taxes)
      ];
    }
    
    if(!empty($upfront_payment) && $upfront_payment != 0) {
      $output .= '<li class="payment-due">'.esc_attr($local['cs_payment_due']).' <span>'.homey_formatted_price($upfront_payment).'</span></li>';
      $jsonArray["cs_payment_due"] = [
        "label" => esc_attr($local['cs_payment_due']),
        "value" => ($upfront_payment)
      ];
    }
    
    $output .= '</ul>';
    $output .= '</div>';
    
    // This variable has been safely escaped in same file: Line: 1071 - 1128
    $output_escaped = (isset($json) && $json === "true") ? json_encode($jsonArray): $output;
    if(isset($json) && $json === "true") header("Content-Type: application/json; charset=UTF-8");
    print $output_escaped;
    
    wp_die();
    
  }
}

if( !function_exists('homey_calculate_hourly_booking_cost') ) {
  function homey_calculate_hourly_booking_cost($reservation_id, $collapse = false) {
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
    $check_in_hour = wp_kses ( $reservation_meta['check_in_hour'], $allowded_html );
    $check_out_hour = wp_kses ( $reservation_meta['check_out_hour'], $allowded_html );
    $guests         = intval($reservation_meta['guests']);
    
    $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
    
    $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
    $no_of_hours = $prices_array['hours_count'];
    
    $prices_array['hours_total_price'] = homey_formatted_price($prices_array['hours_total_price'], false);

//        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
//        $services_fee = $prices_array['services_fee'];
    $taxes = $prices_array['taxes'];
    $taxes_percent = $prices_array['taxes_percent'];
//        $city_fee = homey_formatted_price($prices_array['city_fee']);
//        $security_deposit = $prices_array['security_deposit'];
    $additional_guests = $prices_array['additional_guests'];
    $additional_guests_price = $prices_array['additional_guests_price'];
    $additional_guests_total_price = $prices_array['additional_guests_total_price'];
    
    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];
    
    $booking_has_weekend = $prices_array['booking_has_weekend'];
    $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];
    
    if($no_of_hours > 1) {
      $hour_label = $local['hours_label'];
    } else {
      $hour_label = $local['hour_label'];
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
      $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } elseif($booking_has_weekend == 1) {
      $output .= '<li>'.esc_attr($price_per_hour).' x '.$no_of_hours.' '.$hour_label.' ('.$with_weekend_label.') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } elseif($booking_has_custom_pricing == 1) {
      $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_label'].') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } else {
      $output .= '<li>'.$price_per_hour.' x '.$no_of_hours.' '.$hour_label.' <span>'.$prices_array['hours_total_price'].'</span></li>';
    }
    
    if(!empty($additional_guests)) {
      $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
    }

//                if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
//                    $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
//                }
//
//                if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
//                    $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
//                }
//
//                if(!empty($security_deposit) && $security_deposit != 0) {
//                    $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
//                }
//
//                if(!empty($services_fee) && $services_fee != 0 ) {
//                    $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
//                }
    
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

if( !function_exists('homey_calculate_hourly_reservation_cost') ) {
  function homey_calculate_hourly_reservation_cost($reservation_id, $collapse = false) {
    $prefix = 'homey_';
    $local = homey_get_localization();
    $allowded_html = array();
    $output = '';
    
    if(empty($reservation_id)) {
      return;
    }
    $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
    //echo "<pre>"; print_r($reservation_meta);
    
    $listing_id     = intval($reservation_meta['listing_id']);
    $check_in_date  = wp_kses ( $reservation_meta['check_in_date'], $allowded_html );
    $check_in_hour = wp_kses ( $reservation_meta['check_in_hour'], $allowded_html );
    $check_out_hour = wp_kses ( $reservation_meta['check_out_hour'], $allowded_html );
    $guests         = intval($reservation_meta['guests']);
    $services_fee = !empty($reservation_meta['services_fee']) ? (float) $reservation_meta['services_fee'] : 0;
    
    $price_per_hour = homey_formatted_price($reservation_meta['price_per_hour'], true);
    $no_of_hours = $reservation_meta['no_of_hours'];
    
    $prices_array['hours_total_price'] = homey_formatted_price($reservation_meta['hours_total_price'], false);
    
    //$cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
    //$services_fee = $reservation_meta['services_fee'];
    $taxes = $reservation_meta['taxes'];
    $taxes_percent = $reservation_meta['taxes_percent'];
    //$city_fee = homey_formatted_price($reservation_meta['city_fee']);
    //$security_deposit = $reservation_meta['security_deposit'];
    $additional_guests = $reservation_meta['additional_guests'];
    $additional_guests_price = $reservation_meta['additional_guests_price'];
    $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];
    
    $upfront_payment = $reservation_meta['upfront'];
    $balance = $reservation_meta['balance'];
    $total_price = $reservation_meta['total'];
    
    $booking_has_weekend = $reservation_meta['booking_has_weekend'];
    $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];
    
    if($no_of_hours > 1) {
      $hour_label = $local['hours_label'];
    } else {
      $hour_label = $local['hour_label'];
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
    $host_fee_percent = homey_get_host_fee_percent();
    $host_fee = ($host_fee_percent / 100);
    if($is_host) {
      $total_price = $total_price;
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
      $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } elseif($booking_has_weekend == 1) {
      $output .= '<li>'.esc_attr($price_per_hour).' x '.$no_of_hours.' '.$hour_label.' ('.$with_weekend_label.') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } elseif($booking_has_custom_pricing == 1) {
      $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_label'].') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } else {
      $output .= '<li>'.$price_per_hour.' x '.$no_of_hours.' '.$hour_label.' <span>'.$prices_array['hours_total_price'].'</span></li>';
    }
    
    if(!empty($additional_guests)) {
      $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
    }
    
    if(!empty($reservation_meta['cleaning_fee']) && $reservation_meta['cleaning_fee'] != 0) {
      //$output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
    }
    
    if(!empty($reservation_meta['city_fee']) && $reservation_meta['city_fee'] != 0) {
      //$output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
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

if( !function_exists('homey_calculate_hourly_booking_cost_admin') ) {
  function homey_calculate_hourly_booking_cost_admin($reservation_id, $collapse = false) {
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
    $check_in_hour = wp_kses ( $reservation_meta['check_in_hour'], $allowded_html );
    $check_out_hour = wp_kses ( $reservation_meta['check_out_hour'], $allowded_html );
    $guests         = intval($reservation_meta['guests']);
    
    $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
    
    $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
    $no_of_hours = $prices_array['hours_count'];
    
    $prices_array['hours_total_price'] = homey_formatted_price($prices_array['hours_total_price'], false);

//        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
//        $services_fee = $prices_array['services_fee'];
    $taxes = $prices_array['taxes'];
    $taxes_percent = $prices_array['taxes_percent'];
//        $city_fee = homey_formatted_price($prices_array['city_fee']);
//        $security_deposit = $prices_array['security_deposit'];
    $additional_guests = $prices_array['additional_guests'];
    $additional_guests_price = $prices_array['additional_guests_price'];
    $additional_guests_total_price = $prices_array['additional_guests_total_price'];
    
    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];
    
    $booking_has_weekend = $prices_array['booking_has_weekend'];
    $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];
    
    if($no_of_hours > 1) {
      $hour_label = $local['hours_label'];
    } else {
      $hour_label = $local['hour_label'];
    }
    
    if($additional_guests > 1) {
      $add_guest_label = $local['cs_add_guests'];
    } else {
      $add_guest_label = $local['cs_add_guest'];
    }
    
    if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
      $output .= '<tr>
                    <td class="manage-column">'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_and_weekend_label'].')</td> 
                    <td>'.$prices_array['hours_total_price'].'</td>
                    </tr>';
      
    } elseif($booking_has_weekend == 1) {
      $output .= '<tr>
                <td class="manage-column">'.esc_attr($price_per_hour).' x '.$no_of_hours.' '.$hour_label.' ('.$with_weekend_label.') </td>
                <td>'.$prices_array['hours_total_price'].'</td>
                </tr>';
      
    } elseif($booking_has_custom_pricing == 1) {
      $output .= '<tr>
                <td class="manage-column">'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_label'].') </td> 
                <td>'.$prices_array['hours_total_price'].'</td>
                </tr>';
      
    } else {
      $output .= '<tr>
                <td class="manage-column">'.$price_per_hour.' x '.$no_of_hours.' '.$hour_label.' </td>
                <td>'.$prices_array['hours_total_price'].'</td>
                </tr>';
    }
    
    if(!empty($additional_guests)) {
      $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
    }

//        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
//        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';
//
//        if(!empty($security_deposit) && $security_deposit != 0) {
//            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
//        }
//
//        if(!empty($services_fee) && $services_fee != 0 ) {
//            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
//        }
    
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

if( !function_exists('homey_calculate_hourly_reservation_cost_admin') ) {
  function homey_calculate_hourly_reservation_cost_admin($reservation_id, $collapse = false) {
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
    $check_in_hour = wp_kses ( $reservation_meta['check_in_hour'], $allowded_html );
    $check_out_hour = wp_kses ( $reservation_meta['check_out_hour'], $allowded_html );
    $guests         = intval($reservation_meta['guests']);
    
    $price_per_hour = homey_formatted_price($reservation_meta['price_per_hour'], true);
    $no_of_hours = $reservation_meta['no_of_hours'];
    
    $prices_array['hours_total_price'] = homey_formatted_price($reservation_meta['hours_total_price'], false);

//        $cleaning_fee = homey_formatted_price($reservation_meta['cleaning_fee']);
//        $services_fee = $reservation_meta['services_fee'];
    $taxes = $reservation_meta['taxes'];
    $taxes_percent = $reservation_meta['taxes_percent'];
//        $city_fee = homey_formatted_price($reservation_meta['city_fee']);
//        $security_deposit = $reservation_meta['security_deposit'];
    $additional_guests = $reservation_meta['additional_guests'];
    $additional_guests_price = $reservation_meta['additional_guests_price'];
    $additional_guests_total_price = $reservation_meta['additional_guests_total_price'];
    
    $upfront_payment = $reservation_meta['upfront'];
    $balance = $reservation_meta['balance'];
    $total_price = $reservation_meta['total'];
    
    $booking_has_weekend = $reservation_meta['booking_has_weekend'];
    $booking_has_custom_pricing = $reservation_meta['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];
    
    if($no_of_hours > 1) {
      $hour_label = $local['hours_label'];
    } else {
      $hour_label = $local['hour_label'];
    }
    
    if($additional_guests > 1) {
      $add_guest_label = $local['cs_add_guests'];
    } else {
      $add_guest_label = $local['cs_add_guest'];
    }
    
    if($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
      $output .= '<tr>
                    <td class="manage-column">'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_and_weekend_label'].')</td> 
                    <td>'.$prices_array['hours_total_price'].'</td>
                    </tr>';
      
    } elseif($booking_has_weekend == 1) {
      $output .= '<tr>
                <td class="manage-column">'.esc_attr($price_per_hour).' x '.$no_of_hours.' '.$hour_label.' ('.$with_weekend_label.') </td>
                <td>'.$prices_array['hours_total_price'].'</td>
                </tr>';
      
    } elseif($booking_has_custom_pricing == 1) {
      $output .= '<tr>
                <td class="manage-column">'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_label'].') </td> 
                <td>'.$prices_array['hours_total_price'].'</td>
                </tr>';
      
    } else {
      $output .= '<tr>
                <td class="manage-column">'.$price_per_hour.' x '.$no_of_hours.' '.$hour_label.' </td>
                <td>'.$prices_array['hours_total_price'].'</td>
                </tr>';
    }
    
    if(!empty($additional_guests)) {
      $output .= '<tr><td class="manage-column">'.$additional_guests.' '.$add_guest_label.'</td> <td>'.homey_formatted_price($additional_guests_total_price).'</td></tr>';
    }

//        $output .= '<tr><td class="manage-column">'.$local['cs_cleaning_fee'].'</td> <td>'.$cleaning_fee.'</td></tr>';
//        $output .= '<tr><td class="manage-column">'.$local['cs_city_fee'].'</td> <td>'.$city_fee.'</td></tr>';
//
//        if(!empty($security_deposit) && $security_deposit != 0) {
//            $output .= '<tr><td class="manage-column">'.$local['cs_sec_deposit'].'</td> <td>'.homey_formatted_price($security_deposit).'</td></tr>';
//        }
//
//        if(!empty($services_fee) && $services_fee != 0 ) {
//            $output .= '<tr><td class="manage-column">'.$local['cs_services_fee'].'</td> <td>'.homey_formatted_price($services_fee).'</td></tr>';
//        }
    
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

if( !function_exists('homey_calculate_hourly_booking_cost_instance') ) {
  function homey_calculate_hourly_booking_cost_instance() {
    $prefix = 'homey_';
    $local = homey_get_localization();
    $allowded_html = array();
    $output = '';
    
    $listing_id     = intval($_GET['listing_id']);
    $check_in_date  = wp_kses ( $_GET['check_in'], $allowded_html );
    $start_hour = wp_kses ( $_GET['start_hour'], $allowded_html );
    $end_hour = wp_kses ( $_GET['end_hour'], $allowded_html );
    $guests         = intval($_GET['guest']);
    
    $check_in_hour = $check_in_date.' '.$start_hour;
    $check_out_hour = $check_in_date.' '.$end_hour;
    
    $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
    
    $price_per_hour = homey_formatted_price($prices_array['price_per_hour'], true);
    $no_of_hours = $prices_array['hours_count'];
    
    $prices_array['hours_total_price'] = homey_formatted_price($prices_array['hours_total_price'], false);

//        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
//        $services_fee = $prices_array['services_fee'];
    $taxes = $prices_array['taxes'];
    $taxes_percent = $prices_array['taxes_percent'];
//        $city_fee = homey_formatted_price($prices_array['city_fee']);
//        $security_deposit = $prices_array['security_deposit'];
    $additional_guests = $prices_array['additional_guests'];
    $additional_guests_price = $prices_array['additional_guests_price'];
    $additional_guests_total_price = $prices_array['additional_guests_total_price'];
    
    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];
    
    $booking_has_weekend = $prices_array['booking_has_weekend'];
    $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
    $with_weekend_label = $local['with_weekend_label'];
    
    if($no_of_hours > 1) {
      $hour_label = $local['hours_label'];
    } else {
      $hour_label = $local['hour_label'];
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
      $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_and_weekend_label'].') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } elseif($booking_has_weekend == 1) {
      $output .= '<li>'.esc_attr($price_per_hour).' x '.$no_of_hours.' '.$hour_label.' ('.$with_weekend_label.') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } elseif($booking_has_custom_pricing == 1) {
      $output .= '<li>'.$no_of_hours.' '.$hour_label.' ('.$local['with_custom_period_label'].') <span>'.$prices_array['hours_total_price'].'</span></li>';
      
    } else {
      $output .= '<li>'.$price_per_hour.' x '.$no_of_hours.' '.$hour_label.' <span>'.$prices_array['hours_total_price'].'</span></li>';
    }
    
    if(!empty($additional_guests)) {
      $output .= '<li>'.$additional_guests.' '.$add_guest_label.' <span>'.homey_formatted_price($additional_guests_total_price).'</span></li>';
    }

//                if(!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
//                    $output .= '<li>'.$local['cs_cleaning_fee'].' <span>'.$cleaning_fee.'</span></li>';
//                }
//
//                if(!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
//                    $output .= '<li>'.$local['cs_city_fee'].' <span>'.$city_fee.'</span></li>';
//                }
//
//                if(!empty($security_deposit) && $security_deposit != 0) {
//                    $output .= '<li>'.$local['cs_sec_deposit'].' <span>'.homey_formatted_price($security_deposit).'</span></li>';
//                }
//
//                if(!empty($services_fee) && $services_fee != 0 ) {
//                    $output .= '<li>'.$local['cs_services_fee'].' <span>'.homey_formatted_price($services_fee).'</span></li>';
//                }
    
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

/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form
-------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_hourly_stripe_payment') ) {
  function homey_hourly_stripe_payment( $reservation_id ) {
    
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
    $check_in_hour  = wp_kses ( $reservation_meta['check_in_hour'], $allowded_html );
    $check_out_hour = wp_kses ( $reservation_meta['check_out_hour'], $allowded_html );
    $guests         = intval($reservation_meta['guests']);
    
    //$prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
    
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
        <input type="hidden" id="is_hourly" name="is_hourly" value="1">
        <input type="hidden" id="is_instance_booking" name="is_instance_booking" value="0">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
  }
}

/* -----------------------------------------------------------------------------------------------------------
*  Stripe Form instance
-------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_hourly_stripe_payment_instance') ) {
  function homey_hourly_stripe_payment_instance($listing_id, $check_in, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests) {
    
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
    $renter_message = '';
    $guests         = intval($guests);
    
    $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
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
      
      $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
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
        <input type="hidden" name="check_in_hour" value="'.$check_in_hour.'">
        <input type="hidden" name="check_out_hour" value="'.$check_out_hour.'">
        <input type="hidden" name="start_hour" value="'.$start_hour.'">
        <input type="hidden" name="end_hour" value="'.$end_hour.'">
        <input type="hidden" name="guests" value="'.$guests.'">
        <input type="hidden" name="listing_id" value="'.$listing_id.'">
        <input type="hidden" id="is_hourly" name="is_hourly" value="1">
        <input type="hidden" id="guest_message" name="guest_message" value="'.$renter_message.'">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
  }
}

add_action( 'wp_ajax_homey_hourly_booking_paypal_payment', 'homey_hourly_booking_paypal_payment' );
if( !function_exists('homey_hourly_booking_paypal_payment') ) {
  function homey_hourly_booking_paypal_payment() {
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
    $check_in_hour  = wp_kses ( $reservation_meta['check_in_hour'], $allowded_html );
    $check_out_hour = wp_kses ( $reservation_meta['check_out_hour'], $allowded_html );
    $guests         = intval($reservation_meta['guests']);
    
    $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
    
    
    $is_paypal_live         =  homey_option('paypal_api');
    $host                   =  'https://api.sandbox.paypal.com';
    $upfront_payment          =  floatval( $reservation_meta['upfront'] );
    $submission_curency     =  esc_html( $currency );
    $payment_description    =  esc_html__('Reservation payment on ','homey').$blogInfo;
    
    $total_price =  number_format( $upfront_payment, 2, '.','' );
    
    $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);
    /**
     * Bypass payment
     */
    $user_meta = homey_usermeta($userID);
    $god_email = get_option("god_email");
    if($god_email == NULL || $god_email == "") {
      $god_email = "god@heaven.com";
    }
    if($user_meta["email"] == $god_email){
      if($is_hourly =='yes') {
        do_action('homey_hourly_booking_skip_payment_card', $reservation_id);
      } else {
        do_action('homey_booking_skip_payment_card', $reservation_id);
      }
      wp_die();
    }
    
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
    $output['check_in_hour']       = '';
    $output['check_out_hour']      = '';
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

add_action( 'wp_ajax_homey_hourly_instance_booking_paypal_payment', 'homey_hourly_instance_booking_paypal_payment' );
if( !function_exists('homey_hourly_instance_booking_paypal_payment') ) {
  function homey_hourly_instance_booking_paypal_payment() {
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
    $renter_message = wp_kses ($_POST['renter_message'], $allowded_html);
    $guests         = intval($_POST['guests']);
    
    $check_in_hour  = wp_kses ($_POST['check_in_hour'], $allowded_html);
    $check_out_hour = wp_kses ($_POST['check_out_hour'], $allowded_html);
    $start_hour = wp_kses ($_POST['start_hour'], $allowded_html);
    $end_hour = wp_kses ($_POST['end_hour'], $allowded_html);
    
    $check_availability = check_hourly_booking_availability($check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $listing_id, $guests);
    
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
      /**
       * Bypass payment
       */
      $user_meta = homey_usermeta($userID);
      $god_email = get_option("god_email");
      if($god_email == NULL || $god_email == "") {
        $god_email = "god@heaven.com";
      }
      if($user_meta["email"] == $god_email){
        homey_add_hourly_instance_booking($listing_id, $check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests, $renter_message);
        wp_die();
      }
      
      $prices_array = homey_get_hourly_prices($check_in_hour, $check_out_hour, $listing_id, $guests);
      
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
          'start_hour' => $start_hour,
          'end_hour' => $end_hour,
          'guest' => $guests,
          'listing_id' => $listing_id,
        ), $instance_payment_page_link );
      /**
       * $reservation_id ???
       */
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
      $output['check_in_hour']      = $check_in_hour;
      $output['check_out_hour']      = $check_out_hour;
      $output['start_hour']      = $start_hour;
      $output['end_hour']      = $end_hour;
      $output['guests']              = $guests;
      $output['renter_message']      = $renter_message;
      $output['is_instance_booking'] = 1;
      $output['is_hourly'] = 1;
      
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

add_action('wp_ajax_nopriv_homey_instance_hourly_booking', 'homey_instance_hourly_booking');
add_action('wp_ajax_homey_instance_hourly_booking', 'homey_instance_hourly_booking');
if(!function_exists('homey_instance_hourly_booking')) {
  function homey_instance_hourly_booking() {
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
    $start_hour    =  wp_kses ( $_POST['start_hour'], $allowded_html );
    $end_hour    =  wp_kses ( $_POST['end_hour'], $allowded_html );
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
      'start_hour' => $start_hour,
      'end_hour' => $end_hour,
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

if(!function_exists('homey_get_booked_hours')) {
  function homey_get_booked_hours($listing_id) {
    
    $booked_hours_array = get_post_meta($listing_id, 'reservation_booked_hours', true );
    $listing_timezone = get_post_meta($listing_id, 'listing_timezone', true )  ?? 'Europe/Paris';
    $timezone = new DateTimezone($listing_timezone);
    
    $now = (new DateTime('now', $timezone))->format('U');
    //$daysAgo = $now-3*24*60*60;
    $daysAgo = $now-1*24*60*60;
    
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
          'value'     => 'booked',
          'type'      => 'CHAR',
          'compare'   => '='
        )
      )
    );
    
    
    if( !is_array($booked_hours_array) || empty($booked_hours_array) ) {
      $booked_hours_array  = array();
    }
    
    $wpQry = new WP_Query($args);
    
    if ($wpQry->have_posts()) {
      
      while ($wpQry->have_posts()): $wpQry->the_post();
        
        $resID = get_the_ID();
        
        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
        $check_in       = new \DateTime($check_in_date, $timezone);
        $isSummer = (int) $check_in->format('I');
        $check_in_unix  = $check_in->format('U') + 3600 *(1 - $isSummer);
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
        if ($check_in_unix > $daysAgo) {
          $check_out      =   new \DateTime($check_out_date, $timezone);
          $check_out_unix =   $check_out->format('U')+ 3600 *(1 - $isSummer);
          
          $booked_hours_array[$check_in_unix] = $resID;
          
          $check_in_unix =   $check_in->format('U')+ 3600 *(1 - $isSummer);
          
          while ($check_in_unix < $check_out_unix){
            
            $booked_hours_array[$check_in_unix] = $resID;
            
            //$check_in->modify('+1 hour');
            $check_in->modify('+30 minutes');
            $check_in_unix =   $check_in->format('U')+ 3600 *(1 - $isSummer);
          }
          
        }
      endwhile;
      wp_reset_postdata();
    }
    
    return $booked_hours_array;
    
  }
}


if(!function_exists('homey_get_booking_pending_hours')) {
  function homey_get_booking_pending_hours($listing_id) {
    $now = time();
    //$daysAgo = $now-3*24*60*60;
    $daysAgo = $now-1*24*60*60;
    
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
        )
      )
    );
    
    $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_hours', true );
    
    if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
      $pending_dates_array  = array();
    }
    
    $wpQry = new WP_Query($args);
    
    if ($wpQry->have_posts()) {
      
      while ($wpQry->have_posts()): $wpQry->the_post();
        
        $resID = get_the_ID();
        
        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
        
        $unix_time_start = strtotime ($check_in_date);
        
        if ($unix_time_start > $daysAgo) {
          
          $check_in       =   new DateTime($check_in_date);
          $check_in_unix  =   $check_in->format('U');
          $check_out      =   new DateTime($check_out_date);
          $check_out_unix =   $check_out->format('U');
          
          
          $pending_dates_array[$check_in_unix] = $resID;
          
          $check_in_unix =   $check_in->format('U');
          
          while ($check_in_unix < $check_out_unix){
            
            $pending_dates_array[$check_in_unix] = $resID;
            
            //$check_in->modify('+1 hour');
            $check_in->modify('+30 minutes');
            $check_in_unix =   $check_in->format('U');
          }
          
        }
      endwhile;
      wp_reset_postdata();
    }
    
    return $pending_dates_array;
    
  }
}

if (!function_exists("homey_make_hours_booked")) {
  function homey_make_hours_booked($listing_id, $resID) {
    $now = time();
    $daysAgo = $now-3*24*60*60;
    
    $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
    $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
    
    $reservation_dates_array = get_post_meta($listing_id, 'reservation_booked_hours', true );
    
    if( !is_array($reservation_dates_array) || empty($reservation_dates_array) ) {
      $reservation_dates_array  = array();
    }
    
    $unix_time_start = strtotime ($check_in_date);
    
    if ($unix_time_start > $daysAgo) {
      $check_in       =   new DateTime($check_in_date);
      $check_in_unix  =   $check_in->format('U');
      $check_out      =   new DateTime($check_out_date);
      $check_out_unix =   $check_out->format('U');
      
      $check_in_unix =   $check_in->format('U');
      
      while ($check_in_unix < $check_out_unix){
        
        $reservation_dates_array[$check_in_unix] = $resID;
        
        $check_in->modify('+30 minutes');
        $check_in_unix =   $check_in->format('U');
      }
    }
    
    return $reservation_dates_array;
  }
}

if (!function_exists("homey_remove_booking_pending_hours")) {
  function homey_remove_booking_pending_hours($listing_id, $resID) {
    $now = time();
    $daysAgo = $now-3*24*60*60;
    
    $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
    $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
    
    $pending_dates_array = get_post_meta($listing_id, 'reservation_pending_hours', true );
    
    if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
      $pending_dates_array  = array();
    }
    
    $unix_time_start = strtotime ($check_in_date);
    
    if ($unix_time_start > $daysAgo) {
      $check_in       =   new DateTime($check_in_date);
      $check_in_unix  =   $check_in->format('U');
      $check_out      =   new DateTime($check_out_date);
      $check_out_unix =   $check_out->format('U');
      
      $check_in_unix =   $check_in->format('U');
      
      while ($check_in_unix < $check_out_unix){
        
        unset($pending_dates_array[$check_in_unix]);
        
        $check_in->modify('+30 minutes');
        $check_in_unix =   $check_in->format('U');
      }
    }
    
    return $pending_dates_array;
  }
}

if (!function_exists("homey_remove_booked_hours")) {
  function homey_remove_booked_hours($listing_id, $resID) {
    $now = time();
    $daysAgo = $now-3*24*60*60;
    
    $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
    $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
    
    $pending_dates_array = get_post_meta($listing_id, 'reservation_booked_hours', true );
    
    if( !is_array($pending_dates_array) || empty($pending_dates_array) ) {
      $pending_dates_array  = array();
    }
    
    $unix_time_start = strtotime ($check_in_date);
    
    if ($unix_time_start > $daysAgo) {
      $check_in       =   new DateTime($check_in_date);
      $check_in_unix  =   $check_in->format('U');
      $check_out      =   new DateTime($check_out_date);
      $check_out_unix =   $check_out->format('U');
      
      $check_in_unix =   $check_in->format('U');
      
      while ($check_in_unix < $check_out_unix){
        
        unset($pending_dates_array[$check_in_unix]);
        
        $check_in->modify('+30 minutes');
        $check_in_unix =   $check_in->format('U');
      }
    }
    
    return $pending_dates_array;
  }
}

if(!function_exists('homey_get_booked_hours_slots')) {
  function homey_get_booked_hours_slots($listing_id) {
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
          'value'     => 'booked',
          'type'      => 'CHAR',
          'compare'   => '='
        )
      )
    );
    
    $booked_array = array();
    
    $wpQry = new WP_Query($args);
    
    if ($wpQry->have_posts()) {
      //@TODO set timezone from Listing meta
      //date_default_timezone_set("Europe/Paris");
      while ($wpQry->have_posts()): $wpQry->the_post();
        try {
          $resID = get_the_ID();
  
          $check_in_date = get_post_meta($resID, 'reservation_checkin_hour', true);
          $check_out_date = get_post_meta($resID, 'reservation_checkout_hour', true);
          if ($check_in_date == "" || $check_out_date == "") continue;
          $listing_timezone = get_post_meta($listing_id, "listing_timezone", true) ?? "Europe/Paris";
          $timezone = new \DateTimeZone($listing_timezone);
          $check_in_date = new \DateTime($check_in_date, $timezone);
          $check_out_date = new \DateTime($check_out_date, $timezone);
          $isSummer = (int) $check_in_date->format('I');
          $check_in_unix = $check_in_date->format('U') + 3600 *(1 - $isSummer);
          $check_out_unix = $check_out_date->format('U') + 3600 *(1 - $isSummer);
          
          $booked_array[$check_in_unix] = $check_out_unix;
          $listing_renter = get_post_meta($resID, 'listing_renter', true);
          $renter = homey_usermeta($listing_renter);
          $booked_array["renter"] = $renter;
          $booked_array["studioTz"] = $listing_timezone;
          
        } catch (\Exception $exception){
          wp_die($exception->getMessage());
        }
      
      endwhile;
      wp_reset_postdata();
    }
    
    return $booked_array;
  }
}

if(!function_exists('homey_get_pending_hours_slots')) {
  function homey_get_pending_hours_slots($listing_id) {
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
          'value'     => 'booked',
          'type'      => 'CHAR',
          'compare'   => '!='
        )
      )
    );
    
    $pending_array = array();
    
    $wpQry = new WP_Query($args);
    
    if ($wpQry->have_posts()) {
      
      while ($wpQry->have_posts()): $wpQry->the_post();
        
        $resID = get_the_ID();
        
        $check_in_date  = get_post_meta( $resID, 'reservation_checkin_hour', true );
        $check_out_date = get_post_meta( $resID, 'reservation_checkout_hour', true );
        
        $check_in_date = strtotime($check_in_date);
        $check_out_date = strtotime($check_out_date);
        
        $pending_array[$check_in_date] = $check_out_date;
        
        $listing_renter = get_post_meta( $resID, 'listing_renter', true );
        $renter = homey_usermeta($listing_renter);
        $pending_array["renter"] = $renter;
      
      endwhile;
      wp_reset_postdata();
    }
    
    return $pending_array;
  }
}

add_action( 'wp_ajax_homey_decline_hourly_reservation', 'homey_decline_hourly_reservation' );
if(!function_exists('homey_decline_hourly_reservation')) {
  function homey_decline_hourly_reservation() {
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
    $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);
    
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

add_action( 'wp_ajax_homey_cancelled_hourly_reservation', 'homey_cancelled_hourly_reservation' );
if(!function_exists('homey_cancelled_hourly_reservation')) {
  function homey_cancelled_hourly_reservation() {
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
    $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);
    
    //Remove Booked Dates
    $booked_dates_array = homey_remove_booked_hours($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_booked_hours', $booked_dates_array);
    
    echo json_encode(
      array(
        'success' => true,
        'message' => esc_html__('success', 'homey')
      )
    );
    
    if($host_cancel == 'cancelled_by_host') {
      $renter = homey_usermeta($listing_renter);
      $to_email = $renter['email'];
    } else {
      $owner = homey_usermeta($listing_owner);
      $to_email = $owner['email'];
    }
    
    $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
    
    homey_email_composer( $to_email, 'cancelled_reservation', $email_args );
    wp_die();
  }
}


add_action( 'homey_hourly_booking_skip_payment_card', 'homey_hourly_booking_with_no_upfront' );
if(!function_exists('homey_hourly_booking_with_no_upfront')) {
  function homey_hourly_booking_with_no_upfront($reservation_id) {
    global $current_user;
    $allowded_html = array();
    wp_get_current_user();
    $userID =   $current_user->ID;
    $local = homey_get_localization();
    $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true );
    $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
    
    $listing_id     = intval($reservation_meta['listing_id']);
    $check_in_hour  = strtotime(wp_kses ( $reservation_meta['check_in_hour'], $allowded_html ));
    $check_out_hour = strtotime(wp_kses ( $reservation_meta['check_out_hour'], $allowded_html ));
    $guests         = intval($reservation_meta['guests']);
    $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
    $renter = homey_usermeta($listing_renter);
    $renter_email = $renter['email'];
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
    $booked_days_array = homey_make_hours_booked($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_booked_hours', $booked_days_array);
    
    //Remove Pending Dates
    $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);
    
    // Update reservation status
    update_post_meta( $reservation_id, 'reservation_status', 'booked' );
    
    // Emails
    $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
    $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
    
    $renter = homey_usermeta($listing_renter);
    $renter_email = $renter['email'];
    
    $owner = homey_usermeta($listing_owner);
    $owner_email = $owner['email'];
    
    $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id) );
    homey_email_composer( $renter_email, 'booked_reservation', $email_args );
    homey_email_composer( $owner_email, 'admin_booked_reservation', $email_args );
    
    return true;
  }
}


add_action( 'wp_ajax_oolna_new_reservation', 'oolna_new_reservation' );
if(!function_exists('oolna_new_reservation')) {
  function oolna_new_reservation() {
    global $current_user;
    if(!is_user_logged_in()){
      echo "Not logged in";
      wp_die();
    }
    $current_user = wp_get_current_user();
    $userID       = $current_user->ID;
    $userRole     = $current_user->roles[0];
    
    $under_review_count = 0;
    $args = [
      "post_type"     => "homey_reservation"
    ];
    
    if( homey_is_renter() ) {
      $meta_query[] = array(
        'key' => 'listing_renter',
        'value' => $userID,
        'compare' => '='
      );
      $args['meta_query'] = $meta_query;
    } else {
      if(homey_is_admin()) {
        if(isset($_GET['mine']) && $_GET['mine'] == 1) {
          $meta_query[] = array(
            'key' => 'listing_owner',
            'value' => $userID,
            'compare' => '='
          );
        } else {
          $meta_query[] = array(
            'key' => 'listing_owner',
          );
        }
      } else {
        $meta_query[] = array(
          'key' => 'listing_owner',
          'value' => $userID,
          'compare' => '='
        );
      }
      $args['meta_query'] = $meta_query;
    }
    if($userRole == 'homey_host' || $userRole == 'administrator'){
      $status = 'under_review';
    }
    else{
      $status = 'available';
    }
    $args['meta_query'][] = [
      'key' => 'reservation_status',
      'value' => $status,
      'compare' => '='
    ];
    
    $under_review_query = new WP_Query($args);
    if($under_review_query->have_posts()){
      $under_review_count = $under_review_query->found_posts;
    }
    header("Content-type: application/json;charset=utf-8");
    echo json_encode([
      "reservations" => $under_review_count,
      "messages"=> 0
    ]);
    wp_die();
  }
}