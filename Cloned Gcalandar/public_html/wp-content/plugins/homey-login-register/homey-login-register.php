<?php
/*
Plugin Name: Homey Login Register
Plugin URI:  http://themeforest.net/user/favethemes
Description: Adds login register functionality for houzez theme
Version:     1.2.0
Author:      Waqas Riaz
Author URI:  http://themeforest.net/user/favethemes
License:     GPL2
*/

class Homey_login_register {

	/**
     * Constructor
     *
     * @since 1.0
     *
    */
    public function __construct() {
        $this->homey_login_constants();
    	$this->homey_login_inc_files();
        $this->setup_actions();
    }

    /**
     * Define constants
     *
     * @since 1.0
     *
    */
    protected function homey_login_constants() {

        /**
         * Plugin Path
         */
        define( 'HOMEY_LOGIN_FUNC_PATH', plugin_dir_path( __FILE__ ) );

    }

    /**
     * include files
     *
     * @since 1.0
     *
    */
    function homey_login_inc_files() {

        //Login Register
        require_once( HOMEY_LOGIN_FUNC_PATH . 'functions/login_register.php');
        require_once( HOMEY_LOGIN_FUNC_PATH . 'functions/social_login.php');
        require_once( HOMEY_LOGIN_FUNC_PATH . 'functions/roles.php');
        //require_once( HOMEY_LOGIN_FUNC_PATH . 'functions/roles-functions.php');

    }

    /**
     * Sets up initial actions.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function setup_actions() {

        // Internationalize the text strings used.
        add_action( 'plugins_loaded', array( $this, 'homey_i18n' ), 2 );
    }


    /**
     * Callback function WP plugin_loaded action hook. Loads lang
     *
     * @since  1.0
     * @access public
     */
    public function homey_i18n() {
        load_plugin_textdomain( 'homey-login-register', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  1.0.0
     * @access public
     * @global $wpdb
     * @return void
     */
    /*public function role_delete() {
        remove_role( 'homey_agent' );
        remove_role( 'homey_buyer' );
    }*/

}

/**
 * Instantiate the Class
 *
 * @since     1.0
 * @global    object
 */
$Homey_login_register = new homey_login_register();
?>