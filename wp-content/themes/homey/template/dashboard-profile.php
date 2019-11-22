<?php
/**
 * Template Name: Dashboard User Profile
 */
/*-----------------------------------------------------------------------------------*/
// Social Logins
/*-----------------------------------------------------------------------------------*/
if( ( isset($_GET['code']) && isset($_GET['state']) ) ){
    homey_facebook_login($_GET);

} else if( isset( $_GET['openid_mode']) && $_GET['openid_mode'] == 'id_res' ) {
    homey_openid_login($_GET);

} else if (isset($_GET['code'])){
    homey_google_oauth_login($_GET);

} else {
    if ( !is_user_logged_in() ) {
        wp_redirect(  home_url('/') );
    }
}

get_header();
global $current_user, $author_info;

wp_get_current_user();
$userID = $current_user->ID;
$user_email = $current_user->user_email;
$admin_email =  get_bloginfo('admin_email');
$author_info = homey_get_author_by_id('100', '100', 'img-circle', $userID);
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

                            <div id="profile_message"></div>

                            <?php 
                            if(isset($_GET['page']) && $_GET['page'] == 'password-reset') {
                                get_template_part('template-parts/dashboard/profile/password');

                            } elseif(isset($_GET['page']) && $_GET['page'] == 'verification') {
                                get_template_part('template-parts/dashboard/profile/verification');

                            } else {

                                get_template_part('template-parts/dashboard/profile/progress'); 
                                get_template_part('template-parts/dashboard/profile/photo'); 
                                get_template_part('template-parts/dashboard/profile/information');
                                get_template_part('template-parts/dashboard/profile/address'); 
                                get_template_part('template-parts/dashboard/profile/contact');

                                if(!homey_is_renter()) {
                                    get_template_part('template-parts/dashboard/profile/social');
                                }

                            }
                            
                            wp_nonce_field( 'homey_profile_nonce', 'homey_profile_security' );   
                            ?>

                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->    
        
        <aside class="dashboard-sidebar">
            <?php get_template_part('template-parts/dashboard/profile/status');?>
        </aside><!-- .dashboard-sidebar -->
        
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->


<?php get_footer();?>