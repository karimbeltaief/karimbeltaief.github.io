<?php
if( !function_exists('homey_child_scripts') ) {
    function homey_child_scripts()
    {
    	global $paged, $post, $current_user;
        wp_get_current_user();
        $userID = $current_user->ID;
        wp_enqueue_script('homey-child-maps', get_stylesheet_directory_uri() . '/js/homey-maps.js', array('jquery'), '', true);
    	echo "alpesh";

    	
      
    }
}

add_action( 'wp_enqueue_scripts', 'homey_child_scripts' ); 