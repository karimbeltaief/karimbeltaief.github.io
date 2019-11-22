<?php
/**
 * Template Name: Reservation Payment
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

get_header();
global $current_user;

wp_get_current_user();
$userID = $current_user->ID;

$reservation_id = $reservation_status = '';
if(isset($_GET['reservation_id']) && !empty($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
} 

$enable_paypal = homey_option('enable_paypal');
$enable_stripe = homey_option('enable_stripe');
$stripe_processor_link = homey_get_template_link('template/template-stripe-charge.php');
$is_hourly = get_post_meta($reservation_id, 'is_hourly', true);
?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php the_title(); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-with-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <form name="homey_checkout" method="post" class="homey_payment_form" action="<?php echo esc_url($stripe_processor_link); ?>">
                            <div class="dashboard-area">

                                <div class="block">
                                    <div class="block-head">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Select the payment method', 'homey'); ?></h2>
                                        </div><!-- block-left -->
                                    </div><!-- block-head -->
                                
                                    <div class="block-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="payment-method">
                                                    <?php if($enable_paypal != 0) { ?>
                                                    <div class="payment-method-block paypal-method">
                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input name="payment_gateway" value="paypal" type="radio">
                                                                <span class="control-text"><?php esc_html_e('Paypal', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php } ?>

                                                    <?php if($enable_stripe != 0) { ?>
                                                    <div class="payment-method-block stripe-method">
                                                        <div class="form-group">
                                                            <label class="control control--radio radio-tab">
                                                                <input name="payment_gateway" value="stripe" type="radio">
                                                                <span class="control-text"><?php esc_html_e('Stripe', 'homey'); ?></span>
                                                                <span class="control__indicator"></span>
                                                                <span class="radio-tab-inner"></span>
                                                            </label>
                                                            <?php 
                                                            if($is_hourly == 'yes') {
                                                                homey_hourly_stripe_payment($reservation_id);
                                                            } else {
                                                                homey_stripe_payment($reservation_id);
                                                            }
                                                            ?>
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
                                                <h2 class="title"><?php esc_html_e('Payment', 'homey'); ?></h2>
                                            </div><!-- block-left -->
                                            <div class="block-right">
                                                <?php 
                                                if($is_hourly == 'yes') {
                                                    echo homey_calculate_hourly_reservation_cost($reservation_id);
                                                } else {
                                                    echo homey_calculate_reservation_cost($reservation_id);
                                                }
                                                ?>
                                            </div><!-- block-right -->
                                        </div><!-- block-body -->
                                    </div><!-- block-section -->
                                </div><!-- .block -->

                                <?php if($reservation_status == 'available') { ?>
                                <div class="payment-buttons">
                                    <div id="homey_notify"></div>
                                    <input type="hidden" name="reservation_id" id="reservation_id" value="<?php echo intval($reservation_id); ?>">
                                    <input type="hidden" name="checkout-security" id="checkout-security" value="<?php echo wp_create_nonce('checkout-security-nonce'); ?>"/>

                                    <?php
                                    if($is_hourly == 'yes') { ?>
                                        <button id="make_hourly_booking_payment" class="btn btn-success btn-full-width"><?php esc_html_e('Process Payment', 'homey'); ?></button>
                                    <?php    
                                    } else { ?>
                                        <button id="make_booking_payment" class="btn btn-success btn-full-width"><?php esc_html_e('Process Payment', 'homey'); ?></button>
                                    <?php
                                    }
                                    ?>
                                    

                                </div>
                                <?php } ?>
                            </div><!-- .dashboard-area -->
                        </form>
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->    
        
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->

<?php get_footer();?>