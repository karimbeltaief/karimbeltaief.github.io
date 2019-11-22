<?php
/**
 * Widget Name: Taxonomies
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 12/01/16
 * Time: 11:58 PM
 */
class HOMEY_taxonomies extends WP_Widget {

    /**
     * Register widget
     **/
    public function __construct() {

        parent::__construct(
            'homey_taxonomies', // Base ID
            esc_html__( 'Homey: Taxonomies List', 'homey' ), // Name
            array( 'classname' => 'widget-categories', 'description' => esc_html__( 'Show listing type, room type, countries, cities, areas', 'homey' ), ) // Args
        );

    }


    /**
     * Front-end display of widget
     **/
    public function widget( $args, $instance ) {

        global $before_widget, $after_widget, $before_title, $after_title, $post;
        extract( $args );

        $allowed_html_array = array(
            'div' => array(
                'id' => array(),
                'class' => array()
            ),
            'h3' => array(
                'class' => array()
            )
        );

        $title = apply_filters('widget_title', $instance['title'] );
        $listing_taxonomy = $instance['taxonomy'];
        $tax_count = $instance['tax_count'];
        $tax_child = $instance['tax_child'];
        $items_num = $instance['items_num'];

        if( $tax_count == 'yes' ) { $show_count = true; } else { $show_count = false; }
        if( $tax_child == 'yes' ) { $show_child = true; } else { $show_child = false; }

        echo wp_kses( $before_widget, $allowed_html_array );

        if ( $title ) echo wp_kses( $before_title, $allowed_html_array ) . $title . wp_kses( $after_title, $allowed_html_array );

        echo '<div class="widget-body">';
            homey_listing_taxonomies( $listing_taxonomy, $show_count, $show_child, $items_num );
        echo '</div>';

        echo wp_kses( $after_widget, $allowed_html_array );
    }


    /**
     * Sanitize widget form values as they are saved
     **/
    public function update( $new_instance, $old_instance ) {

        $instance = array();

        /* Strip tags to remove HTML. For text inputs and textarea. */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['taxonomy'] = $new_instance['taxonomy'];
        $instance['tax_count'] = $new_instance['tax_count'];
        $instance['tax_child'] = $new_instance['tax_child'];
        $instance['items_num'] = $new_instance['items_num'];

        return $instance;

    }


    /**
     * Back-end widget form
     **/
    public function form( $instance ) {

        /* Default widget settings. */
        $defaults = array(
            'title' => '',
            'taxonomy' => 'listing_type',
            'tax_count' => 'yes',
            'tax_child' => 'no',
            'items_num' => '3'
        );
        $instance = wp_parse_args( (array) $instance, $defaults );

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'homey'); ?></label>
            <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php esc_html_e( 'Taxonomy', 'homey' ); ?>
                <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>">
                    
                    <option value="listing_type" <?php echo ($instance['taxonomy'] == 'listing_type') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Type', 'homey' ); ?></option>
                    
                    <option value="room_type" <?php echo ($instance['taxonomy'] == 'room_type') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Room Type', 'homey' ); ?></option>
                    
                    <option value="listing_country" <?php echo ($instance['taxonomy'] == 'listing_country') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Country', 'homey' ); ?></option>

                    <option value="listing_city" <?php echo ($instance['taxonomy'] == 'listing_city') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'City', 'homey' ); ?></option>
                    
                    <option value="listing_area" <?php echo ($instance['taxonomy'] == 'listing_area') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Area', 'homey' ); ?></option>
                    

                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'tax_count' ) ); ?>"><?php esc_html_e( 'Count', 'homey' ); ?>
                <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'tax_count' ) ); ?>">
                    <option value="yes" <?php echo ($instance['tax_count'] == 'yes') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Show Count', 'homey' ); ?></option>
                    <option value="no" <?php echo ($instance['tax_count'] == 'no') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Hide Count', 'homey' ); ?></option>
                </select>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'tax_child' ) ); ?>"><?php esc_html_e( 'Child', 'homey' ); ?>
                <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'tax_child' ) ); ?>">
                    <option value="no" <?php echo ($instance['tax_child'] == 'no') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Hide Child', 'homey' ); ?></option>
                    <option value="yes" <?php echo ($instance['tax_child'] == 'yes') ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Show Child', 'homey' ); ?></option>
                </select>
            </label>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>"><?php esc_html_e('Maximum posts to show:', 'homey'); ?></label>
            <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'items_num' ) ); ?>" value="<?php echo esc_attr( $instance['items_num'] ); ?>" size="1" />
        </p>

        <?php
    }

}

if ( ! function_exists( 'HOMEY_taxonomies_loader' ) ) {
    function HOMEY_taxonomies_loader (){
        register_widget( 'HOMEY_taxonomies' );
    }
    add_action( 'widgets_init', 'HOMEY_taxonomies_loader' );
}

if(!function_exists('homey_listing_taxonomies')) {
    function homey_listing_taxonomies($tax, $show_count, $show_child, $items_num) {
        $terms = get_terms( $tax , array( 'parent'=> 0 ));
        if( !is_wp_error($terms) ) {
            $count = count($terms);
            if ( $count > 0 ){
                show_hierarchical_taxonomy( $terms, $tax, $show_child, $show_count, $items_num );
            }
        }
    }
}

function show_hierarchical_taxonomy ( $terms, $taxonomy, $show_child, $show_count, $items_num ) {
    $count = count( $terms );
    if ( $count > 0 ){

        $total = 0;

        echo '<ul class="list-unstyled">';
        foreach ($terms as $term){

            if($total == $items_num) {
                break;
            }

            echo '<li>
            <a href="' . esc_url( get_term_link( $term->slug, $term->taxonomy ) ). '">' . esc_attr( $term->name ) . '</a>';
            
            if( $show_count ) {
                echo '<span class="cat-count">(' . esc_attr( $term->count ) . ')</span>';
            }

            if( $show_child ) {
                $child_terms = get_terms( $taxonomy, array('parent' => $term->term_id));
                if ($child_terms) {
                    show_hierarchical_taxonomy( $child_terms, $taxonomy, false, $show_count, $items_num );
                }
            }

            echo '</li>';

            $total++;
        }
        echo '</ul>';
    }
}
