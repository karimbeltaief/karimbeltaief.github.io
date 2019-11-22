<?php
/*
Plugin Name: Homey Core
Plugin URI:  http://themeforest.net/user/favethemes
Description: Adds functionality to Favethemes Themes
Version:     1.3.1
Author:      Favethemes
Author URI:  http://themeforest.net/user/favethemes
License:     GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HOMEY_PLUGIN_URL',               plugin_dir_url( __FILE__ ));
define( 'HOMEY_PLUGIN_PATH',              dirname( __FILE__ ));
define( 'HOMEY_ADMIN_IMAGES_URL',         HOMEY_PLUGIN_URL  . 'assets/images/');
define( 'HOMEY_TEMPLATES',                HOMEY_PLUGIN_PATH . '/templates/');
define( 'HOMEY_DS',                       DIRECTORY_SEPARATOR);
define( 'HOMEY_PLUGIN_BASENAME',          plugin_basename(__FILE__));

//Main plugin file
require_once 'classes/class-homey-init.php';

register_activation_hook( __FILE__, array( 'Homey', 'homey_plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Homey', 'homey_plugin_deactivate' ) );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function homey_textdomain() {
    load_plugin_textdomain( 'homey-core', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'homey_textdomain' );

// Initialize plugin.
Homey::run();