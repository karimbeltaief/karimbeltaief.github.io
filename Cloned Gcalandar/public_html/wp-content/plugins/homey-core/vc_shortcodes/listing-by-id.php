<?php
if( !function_exists('homey_listing_by_id') ) {
    function homey_listing_by_id($atts, $content = null)
    {

        extract(shortcode_atts(array(
            'listing_style' => '',
            'listing_id' => ''
        ), $atts));

        ob_start();

        $args = array(
            'post_type' => 'listing',
            'post__in' => array($listing_id),
            'post_status' => 'publish'
        );
        //do the query
        $the_query = New WP_Query($args);
        ?>

        <div class="module-wrap property-module-by-id">
            <div class="listing-wrap item-<?php esc_attr_e($listing_style);?>-view">
                <?php
                if ($the_query->have_posts()) :
                    while ($the_query->have_posts()) : $the_query->the_post();

                        if($listing_style == 'card') {
                            get_template_part('template-parts/listing/listing-card');
                        } else {
                            get_template_part('template-parts/listing/listing-item');
                        }

                    endwhile;
                    Homey_Query::loop_reset_postdata();
                else:
                    get_template_part('template-parts/listing/listing', 'none');
                endif;
                ?>
            </div><!-- grid-listing-page -->
        </div>

        <?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }

    add_shortcode('homey-listing-by-id', 'homey_listing_by_id');
}
?>
