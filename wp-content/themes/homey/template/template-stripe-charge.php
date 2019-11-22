<?php
/**
 * Template Name: Stripe Charge Page
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 27/06/16
 * Time: 5:18 AM
 */

require_once( HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php' );
$allowed_html = array();

$current_user = wp_get_current_user();
$userID       =   $current_user->ID;
$user_email   =   $current_user->user_email;
$admin_email  =  get_bloginfo('admin_email');
$username     =   $current_user->user_login;
$submission_currency = homey_option('payment_currency');
$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$add_new_listing = homey_get_template_link('template/dashboard-submission.php');
$paymentMethod = 'Stripe';

$date = date( 'Y-m-d g:i:s', current_time( 'timestamp', 0 ));

$stripe_secret_key = homey_option('stripe_secret_key');
$stripe_publishable_key = homey_option('stripe_publishable_key');
$stripe = array(
    "secret_key"      => $stripe_secret_key,
    "publishable_key" => $stripe_publishable_key
);
\Stripe\Stripe::setApiKey($stripe['secret_key']);

/*echo '<pre>';
print_r($_POST);
wp_die();*/
if( is_email($_POST['stripeEmail']) ) {  // done
    $stripeEmail=  wp_kses ( esc_html($_POST['stripeEmail']) ,$allowed_html );
} else {
    wp_die('None Mail');
}

if( isset($_POST['userID']) && !is_numeric( $_POST['userID'] ) ) { //done
    die();
}

if( isset($_POST['listing_id']) && !is_numeric( $_POST['listing_id'] ) ) { //done
    die();
}

if( isset($_POST['reservation_id_for_stripe']) && !is_numeric( $_POST['reservation_id_for_stripe'] ) ) { //done
    die();
}

if( isset($_POST['reservation_pay']) && !is_numeric( $_POST['reservation_pay'] ) ) { //done
    die();
}


if( isset($_POST['pay_ammout']) && !is_numeric( $_POST['pay_ammout'] ) ) { //done
    die();
}

if( isset($_POST['featured_pay']) && !is_numeric( $_POST['featured_pay'] ) ){
    die();
}

if( isset($_POST['is_instance_booking']) && !is_numeric( $_POST['is_instance_booking'] ) ){
    die();
}

if( isset($_POST['is_hourly']) && !is_numeric( $_POST['is_hourly'] ) ){
    die();
}

if( isset($_POST['homey_stripe_recurring']) && !is_numeric( $_POST['homey_stripe_recurring'] ) ) {
    die();
}

if ( isset ($_POST['reservation_pay']) && $_POST['reservation_pay'] == 1  ) {
    
    try {
        $token  = wp_kses ( $_POST['stripeToken'] ,$allowed_html);
        
        $customer = \Stripe\Customer::create(array(
            "email" => $stripeEmail,
            "source" => $token // obtained with Stripe.js
        ));

        $userID      = intval( $_POST['userID'] );
        $reservation_id  = intval( $_POST['reservation_id_for_stripe'] );
        $pay_ammout  = intval( $_POST['pay_ammout'] );
        $is_instance_booking = isset($_POST['is_instance_booking']) ? $_POST['is_instance_booking'] : 0;
        $is_hourly = isset($_POST['is_hourly']) ? $_POST['is_hourly'] : 0;
        
        $charge = \Stripe\Charge::create(array(
            "amount" => $pay_ammout,
            'customer' => $customer->id,
            "currency" => $submission_currency,
        ));

        
        if( $is_instance_booking == 0 ) { 
            $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true );
            //echo $listing_id.''.$reservation_id;
           // echo "<pre>"; print_r($_POST); die();
            
            if($is_hourly == 1) {
                //Book hours
                $booked_days_array = homey_make_hours_booked($listing_id, $reservation_id);
                update_post_meta($listing_id, 'reservation_booked_hours', $booked_days_array);

                //Remove Pending Hours
                $pending_dates_array = homey_remove_booking_pending_hours($listing_id, $reservation_id);
                update_post_meta($listing_id, 'reservation_pending_hours', $pending_dates_array);

                
            } else {
                //Book dates
                $booked_days_array = homey_make_days_booked($listing_id, $reservation_id);
                update_post_meta($listing_id, 'reservation_dates', $booked_days_array);

                //Remove Pending Dates
                $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
                update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);
            }
            
            // Update reservation status
            update_post_meta( $reservation_id, 'reservation_status', 'booked' );

        } elseif( $is_instance_booking == 1 ) {
            $listing_id = $_POST['listing_id'];
            $guests = isset($_POST['guests']) ? $_POST['guests'] : '';
            $renter_message = isset($_POST['guest_message']) ? $_POST['guest_message'] : '';
            $check_in_date = isset($_POST['check_in_date']) ? $_POST['check_in_date'] : '';

            if($is_hourly == 1) {

                $check_in_hour = isset($_POST['check_in_hour']) ? $_POST['check_in_hour'] : '';
                $check_out_hour = isset($_POST['check_out_hour']) ? $_POST['check_out_hour'] : '';
                $start_hour = isset($_POST['start_hour']) ? $_POST['start_hour'] : '';
                $end_hour = isset($_POST['end_hour']) ? $_POST['end_hour'] : '';

                $reservation_id = homey_add_hourly_instance_booking($listing_id, $check_in_date, $check_in_hour, $check_out_hour, $start_hour, $end_hour, $guests, $renter_message);

            } else {
                
                $check_out_date = isset($_POST['check_out_date']) ? $_POST['check_out_date'] : '';
                $reservation_id = homey_add_instance_booking($listing_id, $check_in_date, $check_out_date, $guests, $renter_message);
            }

            //Create messages thread
            do_action('homey_create_messages_thread', $renter_message, $reservation_id);
        }

        

        //Add host earning history
        homey_add_earning($reservation_id);



        //Add invoice
        $invoiceID = homey_generate_invoice( 'reservation','one_time', $reservation_id, $date, $userID, 0, 0, '', $paymentMethod );
        
        update_post_meta( $invoiceID, 'invoice_payment_status', 1 );

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
        
        $return_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

        wp_redirect( $return_link ); exit;

    }
    catch (Exception $e) {
        print '<div class="alert alert-danger">
                <strong>Error!</strong> '.$e->getMessage().'
                </div>';
    }

} elseif( isset ($_POST['featured_pay']) && $_POST['featured_pay'] == 1 ) { 
    
    try {
        $token  = wp_kses ( $_POST['stripeToken'] ,$allowed_html);

        $customer = \Stripe\Customer::create(array(
            "email" => $stripeEmail,
            "source" => $token // obtained with Stripe.js
        ));

        $userID      = intval( $_POST['userID'] );
        $listing_id  = intval( $_POST['listing_id'] );
        $pay_ammout  = intval( $_POST['pay_ammout'] );


        $charge = \Stripe\Charge::create(array(
            "amount" => $pay_ammout,
            'customer' => $customer->id,
            "currency" => $submission_currency
        ));
        update_post_meta( $listing_id, 'homey_featured', 1 );
        update_post_meta( $listing_id, 'homey_featured_datetime', $date );
        $invoiceID = homey_generate_invoice( 'upgrade_featured','one_time', $listing_id, $date, $userID, 0, 0, '', $paymentMethod );
        update_post_meta( $invoiceID, 'invoice_payment_status', 1 );

        $args = array(
            'listing_title'  =>  get_the_title($listing_id),
            'listing_id'     =>  $listing_id,
            'invoice_no' =>  $invoiceID,
        );

        $return_link = add_query_arg( 
            array(
                'edit_listing' => $listing_id, 
                'featured' => true
            ), $add_new_listing );
        /*
         * Send email
         * */

        homey_email_composer( $user_email, 'featured_submission_listing', $args );
        homey_email_composer( $admin_email, 'admin_featured_submission_listing', $args );

        wp_redirect( $return_link ); exit;

    }
    catch (Exception $e) {
        print '<div class="alert alert-danger">
                <strong>Error!</strong> '.$e->getMessage().'
                </div>';
    }
}
?>