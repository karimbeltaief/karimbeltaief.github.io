<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 07/01/18
 * Time: 6:41 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Post_Type_Partner {
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
            'name' => __( 'Partners','homey-core'),
            'singular_name' => __( 'Partner','homey-core' ),
            'add_new' => __('Add New','homey-core'),
            'add_new_item' => __('Add New Partner','homey-core'),
            'edit_item' => __('Edit Partner','homey-core'),
            'new_item' => __('New Partner','homey-core'),
            'view_item' => __('View Partner','homey-core'),
            'search_items' => __('Search Partner','homey-core'),
            'not_found' =>  __('No Partner found','homey-core'),
            'not_found_in_trash' => __('No Partner found in Trash','homey-core'),
            'parent_item_colon' => ''
        );

        $labels = apply_filters( 'homey_post_type_partners_labels', $labels );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_icon' => 'dashicons-awards',
            'menu_position' => 24,
            'supports' => array('title','page-attributes','thumbnail','revisions'),
            'rewrite' => array( 'slug' => 'partner' )
        );

        register_post_type('homey_partner',$args);
    }

}