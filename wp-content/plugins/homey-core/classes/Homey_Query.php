<?php
/**
 * Class Homey_Query
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Query {
    
    static $fake_loop_offset = 0; 

    public static function shortcode_to_args($atts = '', $paged = '') {
        extract(shortcode_atts(
                array(
                    'listing_style' => '',
                    //'module_columns' => '',
                    'listing_type' => '',
                    'room_type' => '',
                    'listing_country' => '',
                    'listing_state' => '',
                    'listing_city' => '',
                    'listing_area' => '',
                    'listing_ids' => '',
                    'featured_listing' => '',
                    'posts_limit' => '',
                    'sort_by' => '',
                    'offset' => ''
                ),
                $atts
            )
        );

        $tax_query = array();
        
        $wp_query_args = array(
            'ignore_sticky_posts' => 1
        );


        if (!empty($listing_type)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_type',
                'field' => 'slug',
                'terms' => self::traverse_comma_string($listing_type)
            );
        }

        if (!empty($room_type)) {
            $tax_query[] = array(
                'taxonomy' => 'room_type',
                'field' => 'slug',
                'terms' => self::traverse_comma_string($room_type)
            );
        }

        if (!empty($listing_country)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_country',
                'field' => 'slug',
                'terms' => self::traverse_comma_string($listing_country)
            );
        }

        if (!empty($listing_state)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_state',
                'field' => 'slug',
                'terms' => self::traverse_comma_string($listing_state)
            );
        }

        if (!empty($listing_city)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_city',
                'field' => 'slug',
                'terms' => self::traverse_comma_string($listing_city)
            );
        }

        if (!empty($listing_area)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_area',
                'field' => 'slug',
                'terms' => self::traverse_comma_string($listing_area)
            );
        }
        

        $listing_ids_array = explode(',', $listing_ids);

        if (!empty($listing_ids)) {
            $wp_query_args['post__in'] = $listing_ids_array;
        }

        $tax_count = count( $tax_query );

        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ){
            $wp_query_args['tax_query'] = $tax_query;
        }

        if ( $sort_by == 'a_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'homey_night_price';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'homey_night_price';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured' ) {
            $wp_query_args['meta_key'] = 'homey_featured';
            $wp_query_args['meta_value'] = '1';
        } else if ( $sort_by == 'a_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $wp_query_args['orderby'] = 'meta_value';
            $wp_query_args['meta_key'] = 'homey_featured';
            $wp_query_args['order'] = 'DESC';
        }


        if (!empty($featured_listing)) {
            
            if( $featured_listing == "yes" ) {
                $wp_query_args['meta_key'] = 'homey_featured';
                $wp_query_args['meta_value'] = '1';
            } else {
                $wp_query_args['meta_key'] = 'homey_featured';
                $wp_query_args['meta_value'] = '0';
            }
        }

        $wp_query_args['post_status'] = 'publish';

        if (empty($posts_limit)) {
            $posts_limit = get_option('posts_per_page');
        }
        $wp_query_args['posts_per_page'] = $posts_limit;

        if (!empty($paged)) {
            $wp_query_args['paged'] = $paged;
        } else {
            $wp_query_args['paged'] = 1;
        }

        if (!empty($offset) and $paged > 1) {
            $wp_query_args['offset'] = $offset + ( ($paged - 1) * $posts_limit) ;
        } else {
            $wp_query_args['offset'] = $offset ;
        }

        self::$fake_loop_offset = $offset;

        $wp_query_args['post_type'] = 'listing';

        return $wp_query_args;
    }

    public static function metabox_to_args($homepage_loop_filter, $paged = '') {


        $wp_query_args = self::shortcode_to_args($homepage_loop_filter, $paged);


        $wp_query_args['ignore_sticky_posts'] = 0;

        if (isset($wp_query_args['offset']) and $wp_query_args['offset'] > 0) {
            add_filter('found_posts', array(__CLASS__, 'hook_fix_offset_pagination'), 1, 2 );
        }

        return $wp_query_args;
    }


    public static function hook_fix_offset_pagination($found_posts, $query) {
        remove_filter('found_posts','hook_fix_offset_pagination');
        return $found_posts - houzez_data_source::$fake_loop_offset;
    }


    public static function &get_wp_query ($atts = '', $paged = '') {
        $args = self::shortcode_to_args($atts, $paged);
        $fave_query = new WP_Query($args);
        return $fave_query;
    }

    public static function traverse_comma_string($string) {
        $string_array = explode(',', $string);
        
        if(!empty($string_array[0])) {
            return $string_array;
        }
        return '';
    }

    /**
     * Resets current query
     *
     * @access public
     * @return void
     */
    public static function loop_reset() {
        wp_reset_query();
    }

    /**
     * Resets current query postdata
     *
     * @access public
     * @return void
     */
    public static function loop_reset_postdata() {
        wp_reset_postdata();
    }

    /**
     * Checks if there is another post in query
     *
     * @access public
     * @return bool
     */
    public static function loop_has_next() {
        global $wp_query;

        if ( $wp_query->current_post + 1 < $wp_query->post_count ) {
            return true;
        }

        return false;
    }
}