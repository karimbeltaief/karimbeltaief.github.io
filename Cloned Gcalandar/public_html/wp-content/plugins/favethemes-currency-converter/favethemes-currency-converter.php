<?php
/**
 * Plugin Name: Favethemes Currency Converter
 * Plugin URI:  https://favethemes.com
 * Description: 
 * Version:     1.0.1
 * Author:      Favethemes
 * Author URI:  https://favethemes.com
 * License:     GPLv2+
 * Text Domain: favethemes-currency-converter
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FCC_PLUGIN_VERSION', 	'1.0.0' );
define( 'FCC_PLUGIN_URL',     	plugin_dir_url( __FILE__ ) );
define( 'FCC_PLUGIN_PATH',    	dirname( __FILE__ ) . '/' );
define( 'FCC_INC',     			FCC_PLUGIN_PATH . 'inc/' );
define( 'FCC_FUNCTION',     	FCC_PLUGIN_PATH . 'functions/' );
define( 'FCC_CLASSES', 			FCC_PLUGIN_PATH . 'classes/' );
define( 'FCC_TEMPLATES',        FCC_PLUGIN_PATH . '/templates/');
define( 'FCC_DS',               DIRECTORY_SEPARATOR);
define( 'FCC_PLUGIN_BASENAME',  plugin_basename(__FILE__));

if ( version_compare( PHP_VERSION, '5.6.0', '<') ) {
	add_action( 'admin_notices',
		function() {
			echo '<div class="error"><p>'.
			     sprintf( __( "Favethemes Currency Converter requires PHP 5.6 or above to function properly. Detected PHP version on your server is %s. Please upgrade PHP to activate Favethemes Currency Converter or remove the plugin.", 'favethemes-currency-converter' ), phpversion() ? phpversion() : '`undefined`' ) .
			     '</p></div>';
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	);
	return;
}

//Main plugin file
require_once 'classes/class-fcc-init.php';

register_activation_hook( __FILE__, array( 'Favethemes_Currency_Converter', 'FCC_plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Favethemes_Currency_Converter', 'FCC_plugin_deactivate' ) );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function FCC_textdomain() {
    load_plugin_textdomain( 'favethemes-currency-converter', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'FCC_textdomain' );

// Initialize plugin.
Favethemes_Currency_Converter::run();
