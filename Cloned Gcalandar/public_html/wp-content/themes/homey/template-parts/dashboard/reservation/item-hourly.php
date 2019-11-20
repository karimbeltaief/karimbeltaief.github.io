<?php 
global $homey_prefix, $homey_local;
$listing_author = homey_get_author('40', '40', 'img-circle media-object avatar');

$item_meta = get_post_meta(get_the_ID(), 'reservation_meta', true);

$reservation_guests = get_post_meta(get_the_ID(), 'reservation_guests', true);
$listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);
$listing_address    = get_post_meta( $listing_id, $homey_prefix.'listing_address', true );
$pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
$deposit = get_post_meta(get_the_ID(), 'reservation_upfront', true);
$total_amount = get_post_meta(get_the_ID(), 'reservation_total', true);
$reservation_status = get_post_meta(get_the_ID(), 'reservation_status', true);
$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$detail_link = add_query_arg( 'reservation_detail', get_the_ID(), $reservation_page_link );

$no_upfront = homey_option('reservation_payment');
$booking_hide_fields = homey_option('booking_hide_fields');
if($no_upfront == 'no_upfront') {
    $price = '';
} else {
    $price = $deposit;
}

$is_read = $status_label = '';

if($pets != 1) {
    $pets_allow = $homey_local['text_no'];
} else {
    $pets_allow = $homey_local['text_yes'];
}

if( !homey_is_renter() ) {
    if($reservation_status == 'under_review') {
        $is_read = 'msg-unread';
    }
}

if ( is_page_template( array('template/dashboard.php') ) ) {
    $is_read = '';
}

?>
<tr class="<?php echo esc_attr($is_read); ?>">
    <td data-label="Author">
        <?php if(!empty($listing_author['photo'])) { echo ''.$listing_author['photo']; } ?>
    </td>
    <td data-label="ID">
        <?php echo '#'.get_the_ID(); ?>
    </td>
    <td data-label="Status">
        <?php homey_reservation_label($reservation_status); ?>
    </td>
    <td data-label="Date">
        
        <?php esc_attr( the_time( get_option( 'date_format' ) ));?><br>
        <?php esc_attr( the_time( get_option( 'time_format' ) ));?>        
        
    </td>
    <td data-label="Address">
        <a href="<?php echo get_permalink($listing_id); ?>"><strong><?php echo get_the_title($listing_id); ?></strong></a>
        <?php if(!empty($listing_address)) { ?>
            <address><?php echo esc_attr($listing_address); ?></address>
        <?php } ?>
    </td>
    <td data-label="Check-in">
        <?php echo homey_format_date_simple($item_meta['check_in_date']); ?><br/>
        <?php echo esc_html__('à', 'homey'); ?>
        <?php echo date('H:i',strtotime($item_meta['start_hour'])); ?>
    </td>
    <td data-label="Check-out">
        <?php echo homey_format_date_simple($item_meta['check_in_date']); ?><br/>
        <?php echo esc_html__('à', 'homey'); ?>
        <?php echo date('H:i',strtotime($item_meta['end_hour'])); ?>
    </td>
    <?php if($booking_hide_fields['guests'] != 1) {?>
    <td data-label="Guests">
        <?php echo esc_attr($reservation_guests); ?>
        <!-- 3 Adults<br>
        2 Children -->
    </td>
    <?php } ?>
    
    <td data-label="Pets">
        <?php echo esc_attr($pets_allow); ?>
    </td>
    <td data-label="Subtotal">
        <strong><?php echo homey_formatted_price($price); ?></strong>
    </td>
    <td data-label="Actions">
        <div class="custom-actions">
            <?php 
            if( homey_is_renter() ) {
                if($reservation_status == 'available') {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-success">'.$homey_local['res_paynow_label'].'</a>';
                } else {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-secondary">'.$homey_local['res_details_label'].'</a>';
                }
            } else {
                if($reservation_status == 'under_review') {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-success">'.$homey_local['res_confirm_label'].'</a>';
                } else {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-secondary">'.$homey_local['res_details_label'].'</a>';
                }
            }
            ?>
        </div>
    </td>
</tr>