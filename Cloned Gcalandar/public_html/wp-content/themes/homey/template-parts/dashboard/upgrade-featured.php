<?php
global $homey_prefix, $homey_local;

$price_featured_listing = homey_option('price_featured_listing');
$upgrade_id = isset( $_GET['upgrade_id'] ) ? $_GET['upgrade_id'] : '';

$terms_conditions = homey_option('payment_terms_condition');
$allowed_html_array = array(
    'a' => array(
        'href' => array(),
        'title' => array(),
        'target' => array()
    )
);
$enable_paypal = homey_option('enable_paypal');
$enable_stripe = homey_option('enable_stripe');
$is_upgrade = 0; $listing_id = '';
if( !empty( $upgrade_id ) ) {
    $is_upgrade = 1;
    $listing_id = $upgrade_id;
}

$checked_paypal = $checked_stripe = $checked_bank = '';
if($enable_paypal != 0 ) {
    $checked_paypal = 'checked';
} elseif( $enable_paypal != 1 && $enable_stripe != 0 ) {
    $checked_stripe = 'checked';
} elseif( $enable_paypal != 1 && $enable_stripe != 1 && $enable_wireTransfer != 0 ) {
    $checked_bank = 'checked';
} else {

}
$stripe_processor_link = homey_get_template_link('template/template-stripe-charge.php');
?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <form name="homey_checkout" method="post" class="homey_payment_form" action="<?php echo esc_url($stripe_processor_link); ?>">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">

                        <div class="block">
                            <div class="block-head">
                                <div class="block-left">
                                    <h2 class="title"><?php echo esc_html__('Select the payment method', 'homey'); ?></h2>
                                </div><!-- block-left -->
                            </div><!-- block-head -->

                            <div class="block-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="payment-method">
                                            <?php if( $enable_paypal != 0 ) { ?>
                                            <div class="payment-method-block paypal-method">
                                                <div class="form-group">
                                                    <label class="control control--radio radio-tab">
                                                        <input type="radio" class="payment-paypal" name="homey_payment_type" value="paypal" <?php echo esc_html($checked_paypal);?>>
                                                        <span class="control-text"><?php echo esc_html__('Paypal', 'homey'); ?></span>
                                                        <span class="control__indicator"></span>
                                                        <span class="radio-tab-inner"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php } ?>

                                            <?php if( $enable_stripe != 0 ) { ?>
                                            <div class="payment-method-block stripe-method">
                                                <div class="form-group">
                                                    <label class="control control--radio radio-tab">
                                                        <input type="radio" class="payment-stripe" name="homey_payment_type" value="stripe" <?php echo esc_html($checked_stripe);?>>
                                                        <span class="control-text"><?php echo esc_html__('Stripe', 'homey'); ?></span>
                                                        <span class="control__indicator"></span>
                                                        <span class="radio-tab-inner"></span>
                                                    </label>
                                                    <?php homey_stripe_payment_for_featured(); ?>
                                                </div>
                                            </div>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_html__('Payment', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <div class="payment-list">
                                            <ul>
                                                <li>
                                                    <?php esc_html_e('Upgrade to featured', 'homey'); ?> 
                                                    <span><?php echo homey_formatted_price($price_featured_listing); ?></span>
                                                </li>
                                                
                                                <li class="total">
                                                    <div class="payment-list-price-detail clearfix">
                                                        <div class="pull-left">
                                                            <div class="payment-list-price-detail-total-price"><?php esc_html_e('Total', 'homey'); ?></div>
                                                        </div>
                                                        <div class="pull-right text-right">
                                                            <div class="payment-list-price-detail-total-price"><?php echo homey_formatted_price($price_featured_listing); ?></div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div><!-- payment-list --> 
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->
                        </div><!-- .block -->

                        
                        <div class="payment-buttons">
                            <input type="hidden" id="listing_id" name="listing_id" value="<?php echo intval( $listing_id ); ?>">
                            <input type="hidden" id="is_upgrade" name="is_upgrade" value="<?php echo intval($is_upgrade); ?>">
                            <button id="homey_complete_order" class="btn btn-success btn-full-width"><?php echo esc_html__('Process Payment', 'homey'); ?></button>
                        </div>
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
        </form>
    </div><!-- .dashboard-content-area -->    

    <aside class="dashboard-sidebar">
        <div class="item-grid-view">

            <?php get_template_part('template-parts/dashboard/sidebar-listing'); ?>

        </div>
    </aside><!-- .dashboard-sidebar -->
    
</div><!-- .user-dashboard-right -->