<?php
global $post, $homey_local, $homey_prefix, $hide_labels;

$night_price = homey_get_listing_data('night_price');
$weekends_price = homey_get_listing_data('weekends_price');
$weekends_days = homey_get_listing_data('weekends_days');
$priceWeekly = homey_get_listing_data('priceWeek');
$priceMonthly = homey_get_listing_data('priceMonthly');
$min_stay_days = homey_get_listing_data('min_book_days');
$max_stay_days = homey_get_listing_data('max_book_days');
$security_deposit = homey_get_listing_data('security_deposit');
$cleaning_fee = homey_get_listing_data('cleaning_fee');
$cleaning_fee_type = homey_get_listing_data('cleaning_fee_type');
$city_fee = homey_get_listing_data('city_fee');
$city_fee_type = homey_get_listing_data('city_fee_type');
$additional_guests_price = homey_get_listing_data('additional_guests_price');
$allow_additional_guests = homey_get_listing_data('allow_additional_guests');

if($allow_additional_guests == 'yes') {
    $allow_additional_guests = esc_html__('Yes', 'homey');
} else {
    $allow_additional_guests = esc_html__('No', 'homey');
}

$cleaning_fee_period = $city_fee_period = '';

if($cleaning_fee_type == 'per_stay') {
    $cleaning_fee_period = esc_html__('Per Stay', 'homey');
} elseif($cleaning_fee_type == 'daily') {
    $cleaning_fee_period = esc_html__('Daily', 'homey');
}

if($city_fee_type == 'per_stay') {
    $city_fee_period = esc_html__('Per Stay', 'homey');
} elseif($city_fee_type == 'daily') {
    $city_fee_period = esc_html__('Daily', 'homey');
}

if($weekends_days == 'sat_sun') {
    $weekendDays = esc_html__('Sat & Sun', 'homey');

} elseif($weekends_days == 'fri_sat') {
    $weekendDays = esc_html__('Fri & Sat', 'homey');

} elseif($weekends_days == 'fri_sat_sun') {
    $weekendDays = esc_html__('Fri, Sat & Sun', 'homey');
}

?>
<div id="price-section" class="price-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_prices_heading')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <ul class="detail-list detail-list-2-cols">
                        <?php if(!empty($night_price) && $hide_labels['sn_nightly_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_nightly_label'));?>: 
                            <strong><?php echo homey_formatted_price($night_price, true); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($weekends_price) && $hide_labels['sn_weekends_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_weekends_label'));?> (<?php echo $weekendDays; ?>): 
                            <strong><?php echo homey_formatted_price($weekends_price, true); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($priceWeekly) && $hide_labels['sn_weekly7d_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_weekly7d_label'));?>: 
                            <strong><?php echo homey_formatted_price($priceWeekly, true); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($priceMonthly) && $hide_labels['sn_monthly30d_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_monthly30d_label'));?>: 
                            <strong><?php echo homey_formatted_price($priceMonthly, true); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($security_deposit) && $hide_labels['sn_security_deposit_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_security_deposit_label'));?>: 
                            <strong><?php echo homey_formatted_price($security_deposit, true); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($additional_guests_price) && $hide_labels['sn_addinal_guests_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_addinal_guests_label'));?>: 
                            <strong><?php echo homey_formatted_price($additional_guests_price, true); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($allow_additional_guests) && $hide_labels['sn_allow_additional_guests'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_allow_additional_guests'));?>: 
                            <strong><?php echo esc_attr($allow_additional_guests); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($cleaning_fee) && $hide_labels['sn_cleaning_fee'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_cleaning_fee'));?>: 
                            <strong><?php echo homey_formatted_price($cleaning_fee, true); ?></strong> <?php echo esc_attr($cleaning_fee_period); ?>
                        </li>
                        <?php } ?>

                        <?php if(!empty($city_fee) && $hide_labels['sn_city_fee'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_city_fee'));?>: 
                            <strong><?php echo homey_formatted_price($city_fee, true); ?></strong> <?php echo esc_attr($city_fee_period); ?>
                        </li>
                        <?php } ?>

                        <?php if(!empty($min_stay_days) && $hide_labels['sn_min_no_of_days'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_min_no_of_days'));?>: 
                            <strong><?php echo esc_attr($min_stay_days); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($max_stay_days) && $hide_labels['sn_max_no_of_days'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_max_no_of_days'));?>: 
                            <strong><?php echo esc_attr($max_stay_days); ?></strong>
                        </li>
                        <?php } ?>

                    </ul>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>