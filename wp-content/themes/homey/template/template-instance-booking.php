<?php
/**
 * Template Name: Instance Booking
 */ 
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}
get_header();

global $post, $current_user, $homey_prefix, $homey_local;

$listing_id = isset($_GET['listing_id']) ? $_GET['listing_id'] : '';
$homey_booking_type = homey_booking_type_by_id($listing_id);

if($homey_booking_type == 'per_hour') {
    get_template_part('template-parts/instance-booking/hourly');
} else {
    get_template_part('template-parts/instance-booking/nightly');
}

get_footer(); ?>