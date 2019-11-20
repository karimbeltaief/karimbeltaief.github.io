<?php
/**
 * Role Management
 *
 * User: waqasriaz
 */

add_role(
    'homey_renter',
    esc_html__( 'Renter', 'homey-login-register' ),
    array(
        'read'         => true,  // true allows this capability
        'edit_posts'   => false,
        'delete_posts' => false, // Use false to explicitly deny
    )
);

add_role(
    'homey_host',
    esc_html__( 'Host', 'homey-login-register' ),
    array(
        'read'                      => false,  // true allows this capability
        'edit_posts'                => false,
        'delete_posts'              => false, // Use false to explicitly deny
        'read_listing'             => true,
        'publish_posts'             => false,
        'edit_listing'             => true,
        'create_listings'         => true,
        'edit_listings'           => true,
        'delete_listings'         => true,
        'edit_published_listings' => true,
        'publish_listings'        => true,
        'delete_published_listings'=> true,
        'delete_private_listings' => true
    )
);