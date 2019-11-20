<?php
global $current_user, $homey_local, $homey_prefix, $reservationID, $owner_info, $renter_info, $renter_id, $owner_id;
$blogInfo = esc_url( home_url('/') );
wp_get_current_user();
$userID =   $current_user->ID;

$messages_page = homey_get_template_link_2('template/dashboard-messages.php');
$booking_hide_fields = homey_option('booking_hide_fields');

$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
$reservation_status = $notification = $status_label = $notification = '';
$upfront_payment = $check_in = $check_out = $guests = $pets = $renter_msg = '';
$payment_link = '';
if(!empty($reservationID)) {

    if(homey_is_renter()) {
        $back_to_list = homey_get_template_link('template/dashboard-reservations.php');
    } else {
        if(!homey_listing_guest($reservationID)) {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations.php');
        } else {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations2.php');
        }
    }

    $post = get_post($reservationID);    
    $current_date = date( 'Y-m-d', current_time( 'timestamp', 0 ));
    $current_date_unix = strtotime($current_date );

    $reservation_status = get_post_meta($reservationID, 'reservation_status', true);
    $upfront_payment = get_post_meta($reservationID, 'reservation_upfront', true);
    $upfront_payment = homey_formatted_price($upfront_payment);
    $payment_link = homey_get_template_link_2('template/dashboard-payment.php');

    $check_in = get_post_meta($reservationID, 'reservation_checkin_date', true);
    $check_out = get_post_meta($reservationID, 'reservation_checkout_date', true);
    $guests = get_post_meta($reservationID, 'reservation_guests', true);
    $listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
    $pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
    $res_meta   = get_post_meta($reservationID, 'reservation_meta', true);

    $renter_msg = isset($res_meta['renter_msg']) ? $res_meta['renter_msg'] : '';

    $renter_id = get_post_meta($reservationID, 'listing_renter', true);
    $renter_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $renter_id);

    $owner_id = get_post_meta($reservationID, 'listing_owner', true);
    $owner_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $owner_id);

    $payment_link = add_query_arg( array(
            'reservation_id' => $reservationID,
        ), $payment_link );

    $chcek_reservation_thread = homey_chcek_reservation_thread($reservationID);

    if($chcek_reservation_thread != '') {
        $messages_page_link = add_query_arg( array(
            'thread_id' => $chcek_reservation_thread
        ), $messages_page );
    } else {
        $messages_page_link = add_query_arg( array(
            'reservation_id' => $reservationID,
            'message' => 'new',
        ), $messages_page );
    }
    

    $guests_label = homey_option('cmn_guest_label');
    if($guests > 1) {
        $guests_label = homey_option('cmn_guests_label');
    }

}

if( ($post->post_author != $userID) && homey_listing_guest($reservationID) ) {
    echo('Are you kidding?');
    
} else {
?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">
                        <input type="hidden" id="resrv_id" value="<?php echo intval($reservationID); ?>">
                        <?php homey_reservation_notification($reservation_status, $reservationID); ?>

                        <div class="block">
                            <div class="block-head">
                                <div class="block-left">
                                    <h2 class="title"><?php echo esc_attr($homey_local['reservation_label']); ?> <?php echo '#'.$reservationID.' '.homey_get_reservation_label($reservation_status, $reservationID); ?></h2>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <div class="custom-actions">
                                        
                                        <?php if($reservation_status == 'booked' && $current_date_unix >= strtotime($check_in)) { ?>
                                        <?php //if($reservation_status == 'booked') { ?>
                                        <button class="btn-action" data-toggle="collapse" data-target="#review-form" aria-expanded="false" aria-controls="collapseExample" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['review_btn']); ?>">
                                            <i class="fa fa-star"></i>
                                        </button>
                                        <?php } ?>

                                        <button onclick="window.print();" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['print_btn']); ?>"><i class="fa fa-print"></i></button>

                                        <a href="<?php echo esc_url($messages_page_link); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['msg_send_btn']); ?>"><i class="fa fa-envelope-open-o"></i></a>

                                        <?php if($reservation_status != 'booked' && !homey_listing_guest($reservationID)) { ?>
                                        <a href="#" class="mark-as-paid btn-action" data-id="<?php echo esc_attr($reservationID); ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_html__('Mark as Paid', 'homey'); ?>"><i class="fa fa-money"></i></a>
                                        <?php } ?>

                                        <?php if(!homey_listing_guest($reservationID)) { ?>
                                        <a href="#" class="reservation-delete btn-action" data-id="<?php echo esc_attr($reservationID); ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_html__('Delete', 'homey'); ?>"><i class="fa fa-trash"></i></a>
                                        <?php } ?>

                                        <a href="<?php echo esc_url($back_to_list); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['back_btn']); ?>"><i class="fa fa-mail-reply"></i></a>
                                    </div><!-- custom-actions -->
                                </div><!-- block-right -->
                            </div><!-- block-head -->

                            <?php 
                            if($reservation_status == 'booked' && homey_listing_guest($reservationID)) {
                                get_template_part('template-parts/dashboard/reservation/review-form'); 
                            } elseif($reservation_status == 'booked') {
                                get_template_part('template-parts/dashboard/reservation/review-host');
                            }
                            
                            get_template_part('template-parts/dashboard/reservation/add-extra-expenses');
                            get_template_part('template-parts/dashboard/reservation/discount');

                            if($reservation_status == 'declined') {
                                get_template_part('template-parts/dashboard/reservation/declined');

                            } elseif($reservation_status == 'cancelled') {
                                get_template_part('template-parts/dashboard/reservation/cancelled');
                            } else {

                                
                                    get_template_part('template-parts/dashboard/reservation/cancel-form');
                                
                                    if(!homey_listing_guest($reservationID)) {
                                        get_template_part('template-parts/dashboard/reservation/decline-form');
                                    }
                                
                            }

                            if($res_meta['no_of_days'] > 1) {
                                $night_label = homey_option('glc_day_nights_label');
                            } else {
                                $night_label = homey_option('glc_day_night_label');
                            }
                                  
                            ?>
                            
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left child">
                                        <ul class="detail-list">
                                            <li><strong><?php echo esc_attr($homey_local['date_label']); ?>:</strong></li>
                                            <li><?php echo esc_attr( get_the_date( get_option( 'date_format' ), $reservationID ));?>
                                            <br>
                                            <?php echo esc_attr( get_the_date( get_option( 'time_format' ), $reservationID ));?> </li>
                                            
                                        </ul>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <?php if(!empty($renter_info['photo'])) {
                                            echo '<a href="'.esc_url($renter_info['link']).'" target="_blank">'.$renter_info['photo'].'</a>';
                                        }?>
                                        <ul class="detail-list">
                                            <li><strong><?php esc_html_e('From', 'homey'); ?>:</strong> 
                                                <a href="<?php echo esc_url($renter_info['link']); ?>" target="_blank">
                                                    <?php echo esc_attr($renter_info['name']); ?>
                                                </a>
                                            </li>
                                            <li><?php echo get_the_title($listing_id); ?></li>
                                            <li><strong><?php echo __('Notes :','homey'); ?><?php echo __('Les annulations sont possibles 48H avant le début de la réservation.','homey'); ?></strong></li>
                                        </ul>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->

                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <ul class="detail-list detail-list-2-cols">
                                            <li>
                                                <?php echo esc_attr($homey_local['check_In']); ?>: 
                                                <strong><?php echo homey_format_date_simple($check_in); ?></strong>
                                            </li>
                                            <li>
                                                <?php echo esc_attr($homey_local['check_Out']); ?>: 
                                                <strong><?php echo homey_format_date_simple($check_out); ?></strong>
                                            </li>
                                            <li>
                                                <?php echo esc_attr($night_label); ?>: 
                                                <strong><?php echo esc_attr($res_meta['no_of_days']); ?></strong>
                                            </li>
                                            <?php if($booking_hide_fields['guests'] != 1) {?>
                                            <li>
                                                <?php echo esc_attr($guests_label); ?>: 
                                                <strong><?php echo esc_attr($guests); ?></strong>
                                            </li>
                                            <?php } ?>
                                            
                                            <?php if(!empty($res_meta['additional_guests'])) { ?>
                                            <li>
                                                <?php echo esc_attr($homey_local['addinal_guest_text']); ?>: 
                                                <strong><?php echo esc_attr($res_meta['additional_guests']); ?></strong>
                                            </li>
                                            <?php } ?>
                                            
                                        </ul>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->    

                            <?php if(!empty($renter_msg)) { ?>
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Notes', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <p><?php echo esc_attr($renter_msg); ?></p>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section --> 
                            <?php } ?>
                            
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_attr($homey_local['payment_label']); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <?php echo homey_calculate_reservation_cost($reservationID); ?>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->
                        </div><!-- .block -->
                        <div class="payment-buttons visible-sm visible-xs">
                            <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-half-width'); ?>
                        </div>
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
    </div><!-- .dashboard-content-area -->    
    <aside class="dashboard-sidebar">
        <?php get_template_part('template-parts/dashboard/reservation/payment-sidebar'); ?>

        <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-full-width'); ?>

    </aside><!-- .dashboard-sidebar -->
</div><!-- .user-dashboard-right -->
<?php get_template_part('template-parts/dashboard/reservation/message'); ?>
<?php } ?>