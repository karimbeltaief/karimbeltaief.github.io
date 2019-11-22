<?php
/**
 * Custom Post Type Testimmonials
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 07/01/16
 * Time: 2:45 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Post_Type_Testimonials {
    /**
     * Initialize custom post type
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'definition' ) );
    
    }

    /**
     * Custom post type definition
     *
     * @access public
     * @return void
     */
    public static function definition() {
        $labels = array(
            'name' => __( 'Testimonials','homey-core'),
            'singular_name' => __( 'Testimonial','homey-core' ),
            'add_new' => __('Add New','homey-core'),
            'add_new_item' => __('Add New Testimonial','homey-core'),
            'edit_item' => __('Edit Testimonial','homey-core'),
            'new_item' => __('New Testimonial','homey-core'),
            'view_item' => __('View Testimonial','homey-core'),
            'search_items' => __('Search Agent','homey-core'),
            'not_found' =>  __('No Testimonial found','homey-core'),
            'not_found_in_trash' => __('No Testimonial found in Trash','homey-core'),
            'parent_item_colon' => ''
        );

        $labels = apply_filters( 'homey_post_type_testimonials_labels', $labels );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'hierarchical' => true,
            'can_export' => true,
            'menu_icon' => 'dashicons-businessman',
            'menu_position' => 24,
            'supports' => array('title', 'page-attributes','revisions'),
            'show_in_rest'       => true,
            'rest_base'          => 'homey_testimonials',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rewrite' => array( 'slug' => 'testimonials' )
        );

        register_post_type('homey_testimonials',$args);
    }

        
}