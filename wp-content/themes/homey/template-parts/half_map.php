<?php
global $post, $wp_query, $paged;
$zoom_level = homey_option('halfmap_zoom_level');
$search_layout = homey_option('search_posts_layout');
$search_num_posts = homey_option('search_num_posts');
$search_default_order = homey_option('search_default_order');
?>

<section class="half-map-wrap map-on-left clearfix">
        
    <div class="half-map-right-wrap">
        <div id="homey-halfmap" 
            data-zoom="<?php echo intval($zoom_level); ?>"
            data-layout="<?php echo esc_attr($search_layout); ?>"
            data-num-posts="<?php echo esc_attr($search_num_posts); ?>"
            data-order="<?php echo esc_attr($search_default_order); ?>"
        >
        </div>
        <?php get_template_part('template-parts/map-controls'); ?>
    </div><!-- .half-map-right-wrap -->

    <div class="half-map-left-wrap">
        <?php get_template_part('template-parts/search/search-half-map'); ?>
        <?php get_template_part('template-parts/listing/sort-tool_2'); ?>

        <div id="homey_halfmap_listings_container" class="listing-wrap item-<?php echo esc_attr($search_layout);?>-view">
            
        </div><!-- grid-listing-page -->
    </div><!-- .half-map-left-wrap -->
    
</section><!-- .half-map-wrap -->