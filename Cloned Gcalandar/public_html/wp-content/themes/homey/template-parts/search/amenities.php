<?php
global $homey_local, $homey_prefix;

$get_amenities = array();
$get_amenities = isset ( $_GET['amenity'] ) ? $_GET['amenity'] : $get_amenities;

if( taxonomy_exists('listing_amenity') ) {
    $amenities = get_terms(
        array(
            "listing_amenity"
        ),
        array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        )
    );
    $amenities_count = count($get_amenities);
    $checked_amenity = '';
    $count = 0;
    if (!empty($amenities)) { ?>

        <div class="filters-wrap">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                    <div class="filters">
                        <strong><?php echo esc_attr(homey_option('srh_amenities')); ?></strong>
                    </div>
                </div>
                <div class="amenities-list col-xs-12 col-sm-12 col-md-9 col-lg-9">

                    <?php
                    $total_amenities = count($amenities);
                    foreach ($amenities as $amenity):
                        $count++;

                        if (in_array($amenity->slug, $get_amenities)) {
                            $checked_amenity = $amenity->slug;
                        }

                        if($count == 1) {
                            echo '<div class="filters">';
                        }

                        if($count == 7) {
                            echo '<div class="collapse" id="collapseAmenities">
                                    <div class="filters">';
                        }
                            echo '<label class="control control--checkbox">';
                                echo '<input name="amenity[]" type="checkbox" '.checked( $checked_amenity, $amenity->slug, false ).' value="' . esc_attr( $amenity->slug ) . '">';
                                echo '<span class="contro-text">'.esc_attr( $amenity->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';

                        if( ($count == 6) || ($count < 6 && $count == $total_amenities) ) {    
                            echo '</div>';
                        }

                        if( ($count > 6) && ($count == $total_amenities) ) {
                            echo '</div></div>';
                        }

                    endforeach;
                    ?>
                </div>

                <?php if($total_amenities > 6 ) { ?>
                <div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
                    <div class="filters">
                        <a role="button" data-toggle="collapse" data-target="#collapseAmenities" aria-expanded="false" aria-controls="collapseAmenities">
                            <span class="filter-more-link"><?php echo esc_attr($homey_local['search_more']); ?></span> 
                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i> 
                        </a>
                    </div>
                </div>
                <?php } ?>

            </div><!-- featues row -->
        </div><!-- .filters-wrap -->

    <?php    
    }
}
?>