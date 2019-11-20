<?php
global $post, $homey_local;
$period_array = get_post_meta($post->ID, 'homey_custom_period', true);
$hide_fields = homey_option('add_hide_fields');
$custom_weekend_price = isset($hide_fields['custom_weekend_price']) ? $hide_fields['custom_weekend_price'] : 0;

if(!empty($period_array)) {
?>
<div id="custom-price-section" class="custom-price-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <h3 class="sub-title"><?php echo esc_attr($homey_local['custom_period_prices']); ?></h3>
                <div class="table-block dashboard-reservation-table dashboard-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo esc_attr($homey_local['start_date']); ?></th>
                                <th><?php echo esc_attr($homey_local['end_date']); ?></th>
                                <th><?php echo esc_attr($homey_local['nightly_label']); ?></th>
                                <?php if($custom_weekend_price != 1) { ?>
                                <th><?php echo esc_attr($homey_local['weekends_label']); ?></th>
                                <?php } ?>
                                <th><?php echo esc_attr($homey_local['addinal_guests_label']); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo homey_get_custom_period($post->ID, false); ?>

                        </tbody>
                    </table>
                </div>
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>
<?php } ?>