<?php
/**
 * Template Name: Dashboard Add Listing
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

global $current_user, $hide_fields, $homey_local;
wp_get_current_user();
$userID = $current_user->ID;

$user_email = $current_user->user_email;
$admin_email =  get_bloginfo('admin_email');
$panel_class = '';

$invalid_nonce = false;
$submitted_successfully = false;
$updated_successfully = false;
$dashboard_listings = homey_get_template_link('template/dashboard-listings.php');
$dashboard_submission = homey_get_template_link('template/dashboard-submission.php');
$submitted_page_url = homey_get_template_link('template/dashboard-listing-submitted.php');
$hide_fields = homey_option('add_hide_fields');
$required_fields = homey_option('add_listing_required_fields');
$prefix = 'homey_';

if( isset( $_POST['action'] ) ) {
    $submission_action = $_POST['action'];
    $tab = isset($_POST['current_tab']) ? $_POST['current_tab'] : '';

    $new_listing = array(
        'post_type' => 'listing'
    );

    $listing_id = apply_filters('listing_submission_filter', $new_listing);

    $listing_id = intval($listing_id);

    $args = array(
        'listing_title'  =>  get_the_title($listing_id),
        'listing_id'     =>  $listing_id
    );

    if( isset( $_POST['cancellation_policy_in_48H'] ) ) {
        update_post_meta( $listing_id, $prefix.'cancellation_policy_in_48H', sanitize_text_field( $_POST['cancellation_policy_in_48H'] ) );
    }

    if( $submission_action == 'update_listing' ) {
        $return_url  = add_query_arg( 
            array(
                'edit_listing' => $listing_id,
                'tab' => $tab,
                'message' => true,
            ),
            $dashboard_submission );
        

        if( isset( $_POST['cancellation_policy_in_48H'] ) ) {
            update_post_meta( $listing_id, $prefix.'cancellation_policy_in_48H', sanitize_text_field( $_POST['cancellation_policy_in_48H'] ) );
        }

    } else {
        $return_url  = add_query_arg( 'listing_id', $listing_id, $submitted_page_url );
    }

    wp_redirect($return_url);

}
get_header();
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
                        <div class="dashboard-area">

                            <?php if(isset($_GET['edit_listing']) && (isset($_GET['message']) && $_GET['message'] == 1)) { ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    <?php echo esc_attr($homey_local['list_updated']); ?>
                            </div>
                            <?php } ?>

                            <?php if(isset($_GET['edit_listing']) && (isset($_GET['featured']) && $_GET['featured'] == 1)) { ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                                    <?php echo esc_attr($homey_local['list_upgrade_featured']); ?>
                            </div>
                            <?php } ?>

                            <div class="validate-errors alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <?php echo wp_kses(__( '<strong>Error!</strong> Please fill out the required fields.', 'homey' ), homey_allowed_html() ); ?>
                            </div>
                            <div class="validate-errors-gal alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <?php echo wp_kses(__( '<strong>Error!</strong> Upload at least one image.', 'homey' ), homey_allowed_html() ); ?>
                            </div>

                            <?php
                            if (isset($_GET['edit_listing']) && !empty($_GET['edit_listing'])) {

                                get_template_part('template-parts/dashboard/edit-listing/submit-form');

                            } else {

                                get_template_part('template-parts/dashboard/submit-listing/submit-form');

                            }
                            ?>
                            
                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->    
        
        <aside class="dashboard-sidebar">
            <?php get_template_part('template-parts/dashboard/sidebar-listing');?>
        </aside><!-- .dashboard-sidebar -->
        
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->

<?php get_footer();?>