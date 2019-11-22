<?php
global $post;
$size = 'homey-gallery-thumb';
$listing_images = rwmb_meta( 'homey_listing_images', 'type=plupload_image&size='.$size, $post->ID );
$i = 0;

if(!empty($listing_images)) { 
?>
<div id="gallery-section" class="gallery-section">
    <div class="block">
        <div class="featured-image-wrap featured-slide-gallery-wrap clearfix">
            <?php foreach( $listing_images as $image ) { $i++; ?>
            <a href="<?php echo esc_url($image['full_url']);?>" class="swipebox <?php if($i == 8){ echo 'more-images'; } elseif($i > 8) {echo 'gallery-hidden'; } ?>">
            	<?php if($i ==8){ echo '<span>'.count($listing_images).'+</span>'; } ?>
                <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
            </a>
            <?php } ?>
        </div>
    </div><!-- block -->
</div>
<?php } ?>