<?php
global $post,
       $edit_link,
       $homey_local,
       $homey_prefix,
       $prop_address,
       $prop_featured,
       $payment_status;

$post_id    = get_the_ID();
$listing_images = get_post_meta( get_the_ID(), $homey_prefix.'listing_images', false );
$address        = get_post_meta( get_the_ID(), $homey_prefix.'listing_address', true );
$bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
$guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
$beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
$baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
$night_price    = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
$featured    = get_post_meta( get_the_ID(), $homey_prefix.'featured', true );

$listing_price = homey_get_price_by_id($post_id);

$dashboard_listings = homey_get_template_link('template/dashboard-listing.php');
$edit_link  = add_query_arg( 'edit_listing', $post_id, $edit_link ) ;
$calendar_link = add_query_arg(array('tab' => 'calendar', 'edit_listing' => $post_id), $edit_link);
$delete_link  = add_query_arg( 'listing_id', $post_id, $dashboard_listings ) ;
$property_status = get_post_status ( $post->ID );
$dashboard = homey_get_template_link('template/dashboard.php');
$price_separator = homey_option('currency_separator');
$make_featured = homey_option('make_featured');

if($property_status == 'publish') {
    $property_status = esc_html__('Published', 'homey');
}

$upgrade_link  = add_query_arg( array(
    'page' => 'upgrade_featured',
    'upgrade_id' => $post_id,
 ), $dashboard );
?>

<tr>
    <td data-label="Thumbnail">
        <a href="<?php the_permalink(); ?>">
        <?php
        if( has_post_thumbnail( $post->ID ) ) {
            the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive dashboard-listing-thumbnail' ) );
        }else{
            homey_image_placeholder( 'homey-listing-thumb' );
        }
        ?>
        </a>
    </td>
    <td data-label="Address">
        <a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong></a>
        <?php if(!empty($address)) { ?>
            <address><?php echo esc_attr($address); ?></address>
        <?php } ?>
    </td>
    <!-- <td data-label="ID">HY01</td> -->
    <td data-label="Type"><?php echo homey_taxonomy_simple('listing_type'); ?></td>
    <td data-label="Price">
        <?php if(!empty($listing_price)) { ?>
        <strong><?php echo homey_formatted_price($listing_price, true); ?><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label_by_id($post_id); ?></strong><br>
        <?php } ?>
    </td>
    <?php /* ?><td data-label="Bedrooms"><?php echo esc_attr($bedrooms); ?></td>
    <td data-label="Baths"><?php echo esc_attr($baths); ?></td><?php */ ?>
    <td data-label="Guests"><?php echo esc_attr($guests); ?></td>
    <td data-label="ID"><?php echo get_the_ID(); ?></td>
    <td data-label="Status">
        <span class="label label-success"><?php echo esc_html($property_status); ?></span>
    </td>
    <td data-label="Actions">
        <div class="custom-actions">
            <button class="btn-action" onclick="location.href='<?php echo esc_url($calendar_link);?>';" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['cal_label']); ?>"><i class="fa fa-calendar"></i></button>
            
            <button class="btn-action" onclick="location.href='<?php echo esc_url($edit_link);?>';" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['edit_btn']); ?>"><i class="fa fa-pencil"></i></button>
            <?php if($featured != 1 && $make_featured != 0) { ?>
            <a href="<?php echo esc_url($upgrade_link); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['upgrade_btn']); ?>"><i class="fa fa-star-o"></i></a>
            <?php } ?>
            
            <button class="btn-action delete-listing" data-id="<?php echo intval($post->ID); ?>" data-nonce="<?php echo wp_create_nonce('delete_listing_nonce') ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['delete_btn']);?>">
                <i class="fa fa-trash"></i>
            </button>
            <a href="<?php the_permalink(); ?>" target="_blank" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['view_btn']); ?>"><i class="fa fa-arrow-right"></i></a>

            
        </div>
    </td>
</tr>