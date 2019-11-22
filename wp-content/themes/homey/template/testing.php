<?php
/**
 * Template Name: Unit Testing
 */
get_header();

$num = '12.2';

function homey_get_listings_count_from_last_24h($post_type ='post') {
    global $wpdb;

    $numposts = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(ID) ".
            "FROM {$wpdb->posts} ".
            "WHERE ".
                "post_status='publish' ".
                "AND post_type= %s ".
                "AND post_date> %s",
            $post_type, date('Y-m-d H:i:s', strtotime('-24 hours'))
        )
    );
    return $numposts;
}
echo homey_get_listings_count_from_last_24h('listing');


//============================================================

//homey_add_earning(14968);
/*$target_email = 'waqas@favethemes.com';

if(wp_mail( $target_email, 'test', 'body')) {
    echo 'done';
}*/

/*$review_listing_id = 256;
$review_link = get_permalink(256);
$review_id = 803;
$review_link .= '#review-'.$review_id;

$review_reservation_id = 14968;
$rating = '4';
$review_content = 'this is review content';

$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$write_review_link = add_query_arg( 
    array(
        'reservation_detail' => $review_reservation_id,
        'write_review' => 1,
    ),$reservation_page_link 
);

$is_guest = false;
$is_host = false;

$send_to_user_id = 1;
        
$role = homey_user_role_by_user_id($send_to_user_id);

if($role == 'homey_renter') { 
    $is_guest = true;
   
} else {
    
    $is_host = true; 
}

$review_link = get_permalink($review_listing_id);
$review_link .= '#review-'.$review_id;

$guest_review_link = get_author_posts_url( get_the_author_meta( $send_to_user_id ) );
$guest_review_link .= '#review-'.$review_id;

$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$write_review_link = add_query_arg( 
    array(
        'reservation_detail' => $review_reservation_id,
        'write_review' => 1,
    ),$reservation_page_link 
);


$email_subject = sprintf( esc_html__('A new rating has been received for reservation %s', 'homey'), $review_reservation_id );

$email_body = esc_html__("Rating: ", 'homey') . $rating . " <br/>";

$email_body .= esc_html__("Comment:", 'homey').' '.( $review_content ) . " <br/>";
$email_body .= '----------------------------------------- <br/>';

if($is_host) {
    $email_body .= esc_html__('You can view this at2', 'homey').' '.'<a href="'.esc_url($review_link).'">'.$review_link.'</a><br/>';
}

if($is_guest) {
    $email_body .= esc_html__('You can view this at1', 'homey').' '.'<a href="'.esc_url($guest_review_link).'">'.$guest_review_link.'</a><br/>';
}


$email_body .= esc_html__('You can write your review at', 'homey').' '.'<a href="'.esc_url($write_review_link).'">'.$write_review_link.'</a><br/>';

echo $email_body;*/

//homey_add_earning(14947);

/*$to = 'waqas@favethemes.com';
$subject = 'The subject';
$body = 'The email body content';
$headers = array('Content-Type: text/html; charset=UTF-8');
 
$em = wp_mail( $to, $subject, $body, $headers );
echo '<div class="testing">';
if($em) {
    echo 'success';
} else {
    echo 'failare';
}
echo '</div>';

$feed_name = 'Wooow';
$listing_id = 346;
$feed_url = 'https://login.smoobu.com/ical/detail/13523.ics?s=WeakzOj81V';*/

//homey_insert_icalendar_feeds_test($listing_id, $feed_name, $feed_url);

function homey_insert_icalendar_feeds_test($listing_id, $feed_name, $feed_url) {

if(empty($feed_url) || !intval($listing_id) || filter_var($feed_url, FILTER_VALIDATE_URL) === FALSE) {
    return;
}

$temp_array        = array();
$events_data_array = array();

$ical   = new ICal($feed_url);
$events = $ical->events();
//$ical_timezone = $ical->cal['VCALENDAR']['X-WR-TIMEZONE'];

echo '<pre>';
print_r($events);
wp_die();

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

$payout_amount = 14500;

$admin_email = 'waqas@favethemes.com';
$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$payouts_detail_link = add_query_arg( 
    array(
        'page' => 'payout-detail',
        'payout_id' => '10',
    ), 
    $wallet_page_link 
);

$admin_orgs = array(
    'payout_amount' => homey_formatted_price($payout_amount, false),
    'payout_link' => $payouts_detail_link
);
//homey_email_composer( $admin_email, 'admin_payout_request', $admin_orgs );

$host_orgs = array(
    'payout_amount' => homey_formatted_price($payout_amount, false),
    'host_name' => 'Judi Black'
);
//homey_email_composer( 'sales@favethemes.com', 'payout_request', $host_orgs );

}

get_footer(); 
?>