<?php
$mobile_logo = homey_option( 'mobile_logo', false, 'url' );
$splash_logo = homey_option( 'custom_logo_mobile_splash', false, 'url' );
if(homey_is_transparent_logo()) {
    $mobile_logo = $splash_logo;
}
?>
<header class="header-nav header-mobile hidden-md hidden-lg">
    <div class="header-mobile-wrap">
        <div class="container">
            <div class="row">
                <div class="col-xs-3">
                    <button type="button" class="btn btn-mobile-nav mobile-main-nav" data-toggle="collapse" data-target="#mobile-nav" aria-expanded="false">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button><!-- btn-mobile-nav -->
                </div>
                <div class="col-xs-6">
                    <div class="mobile-logo text-center">
                        <h1>
                            <a href="<?php echo esc_url(site_url('/')); ?>">
                                <?php if( !empty( $mobile_logo ) ) { ?>
                                    <img src="<?php echo esc_url( $mobile_logo ); ?>" alt="<?php bloginfo( 'name' );?>" title="<?php bloginfo( 'name' ); ?> - <?php bloginfo( 'description' ); ?>">
                                <?php } else {
                                        bloginfo( 'name' );
                                    } ?>
                            </a>
                        </h1>
                    </div><!-- mobile-logo -->
                </div>
                <div class="col-xs-3">
                    <?php if(homey_is_login_register()) { ?>
                    <div class="user-menu text-right">
                        <button type="button" class="btn btn-mobile-nav user-mobile-nav" data-toggle="collapse" data-target="#user-nav" aria-expanded="false">
                            <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                        </button>
                    </div><!-- user-menu -->
                    <?php } ?>
                </div>
            </div><!-- row -->
        </div><!-- container -->
    </div><!-- header-mobile-wrap -->
    
    <div class="container">
        <div class="row">
            <div class="mobile-nav-wrap">
                <?php get_template_part ('template-parts/header/mobile-menu'); ?>
            </div><!-- mobile-nav-wrap -->    
        </div>        
    </div><!-- container -->
    <div class="container">
        <div class="row">
            <div class="user-nav-wrap">
                <?php if( class_exists('Homey_login_register') ): ?>
            
                    <?php 
                    if( is_user_logged_in() ) { 
                        get_template_part ('template-parts/header/mobile-user-menu');
                    } else {
                        get_template_part ('template-parts/header/mobile-user-menu-not-logged-in');
                    }
                    ?>
                
                <?php endif; ?>
            </div><!-- mobile-nav-wrap -->
        </div>
    </div><!-- container -->
</header><!-- header-nav header-mobile hidden-md hidden-lg -->