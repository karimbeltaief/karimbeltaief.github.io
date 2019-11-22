<?php
if( !function_exists('homey_listing_by_ids') ) {
    function homey_listing_by_ids($atts, $content = null)
    {

        extract(shortcode_atts(array(
            'listing_style' => '',
            'listing_ids' => '',
            'columns' => ''
        ), $atts));

        ob_start();

        $ids_array = explode(',', $listing_ids);

        if($columns == '2cols') {
            $column_class = 'col-sm-6';
        } else {
            $column_class = 'col-sm-4';
        }

        $args = array(
            'post_type' => 'listing',
            'post__in' => $ids_array,
            'post_status' => 'publish'
        );
        //do the query
        $the_query = New WP_Query($args);
        ?>

        <div class="module-wrap property-module-by-id property-module-by-id-<?php esc_attr_e($columns);?>">
            <div class="listing-wrap item-<?php esc_attr_e($listing_style);?>-view">
                <div class="row">
                    <?php
                    if ($the_query->have_posts()) :
                        while ($the_query->have_posts()) : $the_query->the_post();

                            echo '<div class="'.$column_class.'">';
                            if($listing_style == 'card') {
                                get_template_part('template-parts/listing/listing-card');
                            } else {
                                get_template_part('template-parts/listing/listing-item');
                            }
                            echo '</div>';

                        endwhile;
                        Homey_Query::loop_reset_postdata();
                    else:
                        get_template_part('template-parts/listing/listing', 'none');
                    endif;
                    ?>
                </div>
            </div><!-- grid-listing-page -->
        </div>

        <?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }

    add_shortcode('homey-listing-by-ids', 'homey_listing_by_ids');
}
?>
