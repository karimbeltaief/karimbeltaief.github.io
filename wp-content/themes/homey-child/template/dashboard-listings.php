<?php
/**
 * Template Name: Dashboard Listings
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

get_header(); 

global $current_user, $post;

wp_get_current_user();
$userID         = $current_user->ID;
$user_login     = $current_user->user_login;
$edit_link      = homey_get_template_link('template/dashboard-submission.php');

$no_of_listing   =  '9';
$paged        = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type'        =>  'listing',
    'author'           =>  $userID,
    'paged'             => $paged,
    'posts_per_page'    => $no_of_listing,
    'post_status'      =>  'publish'
);
if( isset ( $_GET['keyword'] ) ) {
    $keyword = trim( $_GET['keyword'] );
    if ( ! empty( $keyword ) ) {
        $args['s'] = $keyword;
    }
}
if( isset ( $_GET['keyword'] ) ) {
    $keyword = trim( $_GET['keyword'] );
    if ( ! empty( $keyword ) ) {
        $args['s'] = $keyword;
    }
}
$args = homey_listing_sort ( $args );
$listing_qry = new WP_Query($args);

?>

<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php the_title(); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div id="listings_module_section" class="dashboard-area">
                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_attr($homey_local['manage_label']); ?></h2>
                                    </div>
                                    <div class="block-right">
                                        <div class="dashboard-form-inline">
                                            <form class="form-inline">
                                                <div class="form-group">
                                                    <input name="keyword" type="text" class="form-control" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : '';?>" placeholder="<?php echo esc_attr__('Search listing', 'homey'); ?>">
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-search-icon"><i class="fa fa-search" aria-hidden="true"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <?php 
                                if($listing_qry->have_posts()): ?>
                                    <div class="table-block dashboard-listing-table dashboard-table">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo esc_attr($homey_local['thumb_label']); ?></th>
                                                    <th><?php echo esc_attr($homey_local['address']); ?></th>
                                                    <th><?php echo homey_option('sn_type_label'); ?></th>
                                                    <th><?php echo esc_attr($homey_local['price_label']); ?></th>
                                                    <?php /* ?><th><?php echo homey_option('glc_bedrooms_label');?></th>
                                                    <th><?php echo homey_option('glc_baths_label');?></th><?php */ ?>
                                                    <th><?php echo homey_option('glc_guests_label');?></th>
                                                    <th><?php echo homey_option('sn_id_label');?></th>
                                                    <th><?php echo esc_attr($homey_local['status_label']); ?></th>
                                                    <th><?php echo esc_attr($homey_local['actions_label']); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="module_listings">
                                                <?php 
                                                while ($listing_qry->have_posts()): $listing_qry->the_post();
                                                    get_template_part('template-parts/dashboard/listing-item');
                                                endwhile;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                                else:
                                    echo '<div class="block-body">';
                                    echo esc_attr($homey_local['listing_dont_have']);  
                                    echo '</div>';      
                                endif; 
                                ?>
                            </div><!-- .block -->

                            <?php homey_pagination( $listing_qry->max_num_pages, $range = 2 ); ?>

                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area --> 
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->


<?php get_footer();?>