<?php
global $post;
$size = 'homey-variable-slider';
$listing_images = rwmb_meta( 'homey_listing_images', 'type=plupload_image&size='.$size, $post->ID );

if(!empty($listing_images)) { 
?>
<div class="top-gallery-section top-gallery-variable-width-section">
    <div class="listing-slider-variable-width">
        
        <?php foreach( $listing_images as $image ) { ?>
        <div>
        	<a href="<?php echo esc_url($image['full_url']);?>" class="swipebox">
        	<img class="img-responsive" data-lazy="<?php echo esc_url($image['url']); ?>" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
        	</a>
        </div>
        <?php } ?>
        
    </div>
</div><!-- top-gallery-section -->
<?php } ?>