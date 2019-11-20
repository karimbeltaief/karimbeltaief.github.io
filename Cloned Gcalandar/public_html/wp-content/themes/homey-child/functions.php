<?php
//include ( get_stylesheet_directory() .'/inc/child-register-scripts.php' );

function homey_enqueue_styles() {
    
    // enqueue parent styles
    wp_enqueue_style('homey-parent-theme', get_template_directory_uri() .'/style.css');
    
    // enqueue child styles
    wp_enqueue_style('homey-child-theme', get_stylesheet_directory_uri() .'/style.css', array('homey-parent-theme'));
}

add_action('wp_enqueue_scripts', 'homey_enqueue_styles', 100);

//include ( get_stylesheet_directory() .'/inc/register-scripts.php' );

// Register
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_register_fti', 'homey_register_fti' );
add_action( 'wp_ajax_homey_register_fti', 'homey_register_fti' );

if( !function_exists('homey_register_fti') ) {
    function homey_register_fti() {
        //$local = homey_get_localization();
    	
        check_ajax_referer('homey_register_nonce', 'homey_register_security');

        $allowed_html = array();

        $usermane          = trim( sanitize_text_field( wp_kses( $_POST['username'], $allowed_html ) ));
        $email             = trim( sanitize_text_field( wp_kses( $_POST['useremail'], $allowed_html ) ));
        $term_condition    = isset($_POST['term_condition']) ? wp_kses( $_POST['term_condition'], $allowed_html ) : '';
        $enable_password = homey_option('enable_password');
        //$response = $_POST["g-recaptcha-response"];

        $user_role = get_option( 'default_role' );

        if( isset( $_POST['role'] ) && $_POST['role'] != '' ){
            $user_role = isset( $_POST['role'] ) ? sanitize_text_field( wp_kses( $_POST['role'], $allowed_html ) ) : $user_role;
        } else {
            $user_role = $user_role;
        }

        $term_condition = ( $term_condition == 'on') ? true : false;

        if( !$term_condition ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('You need to agree with terms & conditions.', 'homey-login-register') ) );
            wp_die();
        }

        if( $_POST['role'] == '' ){
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Role field is empty', 'homey-login-register') ) );
            wp_die();
        }

        if( empty( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The username field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( strlen( $usermane ) < 3 ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Minimum 3 characters required', 'homey-login-register') ) );
            wp_die();
        }
        if (preg_match("/^[0-9A-Za-z_]+$/", $usermane) == 0) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid username (do not use special characters or spaces)!', 'homey-login-register') ) );
            wp_die();
        }
        if( username_exists( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This username is already registered.', 'homey-login-register') ) );
            wp_die();
        }
        if( empty( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The email field is empty.', 'homey-login-register') ) );
            wp_die();
        }

        if( email_exists( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This email address is already registered.', 'homey-login-register') ) );
            wp_die();
        }

        if( !is_email( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid email address.', 'homey-login-register') ) );
            wp_die();
        }

        if( $enable_password == 'yes' ){
            $user_pass         = trim( sanitize_text_field(wp_kses( $_POST['register_pass'] ,$allowed_html) ) );
            $user_pass_retype  = trim( sanitize_text_field(wp_kses( $_POST['register_pass_retype'] ,$allowed_html) ) );

            if ($user_pass == '' || $user_pass_retype == '' ) {
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('One of the password field is empty!', 'homey-login-register') ) );
                wp_die();
            }

            if ($user_pass !== $user_pass_retype ){
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('Passwords do not match', 'homey-login-register') ) );
                wp_die();
            }
        }

        $enable_forms_gdpr = homey_option('enable_forms_gdpr');

        if( $enable_forms_gdpr != 0 ) {
            $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
            if ( empty($privacy_policy) ) {
                echo json_encode(array(
                    'success' => false,
                    'msg' => homey_option('forms_gdpr_validation')
                ));
                wp_die();
            }
        }

        homey_google_recaptcha_callback();

        if($enable_password == 'yes' ) {
            $user_password = $user_pass;
        } else {
            $user_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        }
        $user_id = wp_create_user( $usermane, $user_password, $email );

        if ( is_wp_error($user_id) ) {
            echo json_encode( array( 'success' => false, 'msg' => $user_id ) );
            wp_die();
        } else {

            wp_update_user( array( 'ID' => $user_id, 'role' => $user_role ) );

            if( $enable_password =='yes' ) {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('Your account was created and you can login now!', 'homey-login-register') ) );
            } else {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('An email with the generated password was sent!', 'homey-login-register') ) );
            }
            homey_wp_new_user_notification( $user_id, $user_password );
        }
        wp_die();

    }
}


if( !function_exists('homey_child_calculate_hourly_reservation_cost') ) {
  function homey_child_calculate_hourly_reservation_cost($reservation_id, $collapse = false) {
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
    $host_fee = ($host_fee_percent / 100) * $total_price;
    //echo $host_fee;
    if($is_host) {
		$total_price = $total_price - $host_fee;
    } else {
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
    if($is_host) {
        if(!empty($host_fee) && $host_fee != 0) {
          $output .= '<li> Host Fee : <span>'.homey_formatted_price($host_fee).'</span></li>';
        }
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
        $upfront_payment = $upfront_payment - $host_fee;
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


if(!function_exists('homey_child_reservation_count')) {
    function homey_child_reservation_count($user_id) {
        $args = array(
            'post_type'        =>  'homey_reservation',
            'posts_per_page'    => -1,
        );

        if( homey_is_renter() ) {
            $meta_query[] = array(
                'key' => 'listing_renter',
                'value' => $user_id,
                'compare' => '='
            );
            $args['meta_query'] = $meta_query;
        } else {
            $meta_query[] = array(
                'key' => 'listing_owner',
                'value' => $user_id,
                'compare' => '='
            );
            $meta_query[] = array(
                'key' => 'reservation_status',
                'value' => 'under_review',
                'compare' => '='
            );
            $args['meta_query'] = $meta_query;
        }
        $Qry = new WP_Query($args);
        $founds = $Qry->found_posts;

        return $founds;

    }
}