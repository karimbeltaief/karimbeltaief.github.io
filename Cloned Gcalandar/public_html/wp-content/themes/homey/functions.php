<?php
/**
 * Homey functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Homey
 * @since Homey 1.0.0
 * @author Waqas Riaz
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
global $wp_version;
/**
*	---------------------------------------------------------------
*	Define constants
*	---------------------------------------------------------------
*/
define( 'HOMEY_THEME_NAME', 'Homey' );
define( 'HOMEY_THEME_SLUG', 'homey' );
define( 'HOMEY_THEME_VERSION', '1.3.0' );
/**
*	----------------------------------------------------------------------------------
*	Set up theme default and register various supported features.
*	----------------------------------------------------------------------------------
*/
if ( ! function_exists( 'homey_setup' ) ) {
	function homey_setup() {

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		//Let WordPress manage the document title.
		add_theme_support( 'title-tag' );

		//Add support for post thumbnails.
		add_theme_support( 'post-thumbnails' );

		add_image_size( 'homey-listing-thumb', 450, 300, true );
		add_image_size( 'homey-gallery-thumb', 250, 250, true );
		add_image_size( 'homey-gallery', 1140, 760, true );
		add_image_size( 'homey-gallery-thumb2', 120,80, true );
		add_image_size( 'homey-variable-slider', 0, 500, true );

		add_image_size( 'homey_thumb_555_360', 555, 360, true );
		add_image_size( 'homey_thumb_555_262', 555, 262, true );
		add_image_size( 'homey_thumb_360_360', 360, 360, true );
		add_image_size( 'homey_thumb_360_120', 360, 120, true );
		

		/**
		*	Register nav menus. 
		*/
		register_nav_menus(
			array(
				'main-menu' => esc_html__( 'Main Menu', 'homey' ),
				'top-menu' => esc_html__( 'Top Menu', 'homey' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		/*
		 * Enable support for Post Formats.
		 * See https://developer.wordpress.org/themes/functionality/post-formats/
		 */
		add_theme_support( 'post-formats', array(

		) );

		//remove gallery style css
		add_filter( 'use_default_gallery_style', '__return_false' );
		
	}

	add_action( 'after_setup_theme', 'homey_setup' );
}

/**
 *	-----------------------------------------------------------------
 *	Make the theme available for translation.
 *	-----------------------------------------------------------------
 */
load_theme_textdomain( 'homey', get_template_directory() . '/languages' );

/**
 *	-------------------------------------------------------------------------
 *	Set up the content width value based on the theme's design.
 *	-------------------------------------------------------------------------
 */
if( !function_exists('homey_content_width') ) {
	function homey_content_width()
	{
		$GLOBALS['content_width'] = apply_filters('homey_content_width', 1170);
	}

	add_action('after_setup_theme', 'homey_content_width', 0);
}


/**
 *	-------------------------------------------------------------------
 *	Visual Composer
 *	-------------------------------------------------------------------
 */
if (class_exists('Vc_Manager') && class_exists('Homey') ) {

	if( !function_exists('homey_include_composer') ) {
		function homey_include_composer()
		{
			require_once(get_template_directory() . '/framework/vc_extend.php');
		}

		add_action('init', 'homey_include_composer', 9999);
	}

}

if(!function_exists('homey_or_custom_posts')) {
	function homey_or_custom_posts($query) {
	  if($query->is_admin) {
	  	$post_type = $query->get('post_type');

	    if ( $post_type == 'homey_reservation' || $post_type == 'homey_review' || $post_type == 'homey_invoice') {

	    	$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';

	    	if(empty($orderby)) {
		      	$query->set('orderby', 'date');
		      	$query->set('order', 'DESC');
		      }
	    }
	  }
	  return $query;
	}
	add_filter('pre_get_posts', 'homey_or_custom_posts');
}


/**
 *	-----------------------------------------------------------------------------------------
 *	Enqueue scripts and styles.
 *	-----------------------------------------------------------------------------------------
 */
require_once( get_template_directory() . '/inc/register-scripts.php' );


/**
 *	-----------------------------------------------------------------------------------------
 *	Include files
 *	-----------------------------------------------------------------------------------------
 */
require_once( get_template_directory() . '/framework/functions/helper.php' );
require_once( get_template_directory() . '/framework/functions/wallet.php' );
require_once( get_template_directory() . '/framework/functions/profile.php' );
require_once( get_template_directory() . '/framework/functions/price.php' );
require_once( get_template_directory() . '/framework/functions/listings.php' );
require_once( get_template_directory() . '/framework/functions/reservation.php' );
require_once( get_template_directory() . '/framework/functions/reservation-hourly.php' );
require_once( get_template_directory() . '/framework/functions/calendar.php' );
require_once( get_template_directory() . '/framework/functions/calendar-hourly.php' );
require_once( get_template_directory() . '/framework/functions/review.php' );
require_once( get_template_directory() . '/framework/functions/search.php' );
require_once( get_template_directory() . '/framework/functions/messages.php' );
require_once( get_template_directory() . '/framework/functions/cron.php' );
require_once( get_template_directory() . '/framework/functions/icalendar.php' );
require_once( get_template_directory() . '/framework/functions/v13-db.php' );
require_once( get_template_directory() . '/framework/ics-parser/class.iCalReader.php' );
require_once( get_template_directory() . '/template-parts/header/favicons.php' );

require_once( get_template_directory() . '/framework/thumbnails/better-jpgs.php');


/**
 *	-----------------------------------------------------------------------------------------
 *	Localizations
 *	-----------------------------------------------------------------------------------------
 */
require_once(get_theme_file_path('localization.php'));

/**
 *	-----------------------------------------------------------------------------------------
 *	Include hooks and filters
 *	-----------------------------------------------------------------------------------------
 */
require_once( get_template_directory() . '/framework/homey-hooks.php' );


/**
 *	-----------------------------------------------------------------------------------------
 *	Styling
 *	-----------------------------------------------------------------------------------------
 */
if ( class_exists( 'ReduxFramework' ) ) {
	require_once( get_template_directory() . '/inc/styling-options.php' );
}
require_once( get_template_directory() . '/framework/functions/demo-importer.php' );


/**
 *	-----------------------------------------------------------------------------------------
 *	TMG plugin activation
 *	-----------------------------------------------------------------------------------------
 */
	require_once( get_template_directory() . '/framework/class-tgm-plugin-activation.php' );
	require_once( get_template_directory() . '/framework/register-plugins.php' );


/**
 *	---------------------------------------------------------------------------------------
 *	Meta Boxes
 *	---------------------------------------------------------------------------------------
 */
require_once(get_template_directory() . '/framework/metaboxes/homey-meta-boxes.php');
require_once(get_template_directory() . '/framework/metaboxes/listing-state-meta.php');
require_once(get_template_directory() . '/framework/metaboxes/listing-cities-meta.php');
require_once(get_template_directory() . '/framework/metaboxes/listing-area-meta.php');
require_once( get_template_directory() . '/framework/metaboxes/listing-type-meta.php' );


/**
 *	---------------------------------------------------------------------------------------
 *	Options Admin Panel
 *	---------------------------------------------------------------------------------------
 */
require_once( get_template_directory() . '/framework/options/remove-tracking-class.php' ); // Remove tracking
require_once( get_template_directory() . '/framework/options/homey-options.php' );
require_once( get_template_directory() . '/framework/options/homey-option.php' );


/*-----------------------------------------------------------------------------------*/
/*	Register blog sidebar, footer and custom sidebar
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_widgets_init') ) {
	add_action('widgets_init', 'homey_widgets_init');
	function homey_widgets_init()
	{
		register_sidebar(array(
			'name' => esc_html__('Default Sidebar', 'homey'),
			'id' => 'default-sidebar',
			'description' => esc_html__('Widgets in this area will be shown in the default sidebar.', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));
		register_sidebar(array(
			'name' => esc_html__('Page Sidebar', 'homey'),
			'id' => 'page-sidebar',
			'description' => esc_html__('Widgets in this area will be shown in the page sidebar.', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));
		register_sidebar(array(
			'name' => esc_html__('Listings Sidebar', 'homey'),
			'id' => 'listing-sidebar',
			'description' => esc_html__('Widgets in this area will be shown in listings sidebar.', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));

		register_sidebar(array(
			'name' => esc_html__('Blog Sidebar', 'homey'),
			'id' => 'blog-sidebar',
			'description' => esc_html__('Widgets in this area will be shown in the blog sidebar.', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));

		register_sidebar(array(
			'name' => esc_html__('Custom Sidebar 1', 'homey'),
			'id' => 'custom-sidebar-1',
			'description' => esc_html__('This sidebar can be assigned to any page when add/edit page.', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));

		register_sidebar(array(
			'name' => esc_html__('Custom Sidebar 2', 'homey'),
			'id' => 'custom-sidebar-2',
			'description' => esc_html__('This sidebar can be assigned to any page when add/edit page.', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));

		register_sidebar(array(
			'name' => esc_html__('Custom Sidebar 3', 'homey'),
			'id' => 'custom-sidebar-3',
			'description' => esc_html__('This sidebar can be assigned to any page when add/edit page.', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));

		register_sidebar(array(
			'name' => esc_html__('Footer Area 1', 'homey'),
			'id' => 'footer-sidebar-1',
			'description' => esc_html__('Widgets in this area will be show in footer column one', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));
		register_sidebar(array(
			'name' => esc_html__('Footer Area 2', 'homey'),
			'id' => 'footer-sidebar-2',
			'description' => esc_html__('Widgets in this area will be show in footer column two', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));
		register_sidebar(array(
			'name' => esc_html__('Footer Area 3', 'homey'),
			'id' => 'footer-sidebar-3',
			'description' => esc_html__('Widgets in this area will be show in footer column three', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));
		register_sidebar(array(
			'name' => esc_html__('Footer Area 4', 'homey'),
			'id' => 'footer-sidebar-4',
			'description' => esc_html__('Widgets in this area will be show in footer column four', 'homey'),
			'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-top"><h3 class="widget-title">',
			'after_title' => '</h3></div>',
		));
		
	}
}

if ( !current_user_can('administrator') && !is_admin() ) {
	add_filter('show_admin_bar', '__return_false');
}

if ( !function_exists( 'homey_block_users' ) ) :

	add_action( 'init', 'homey_block_users' );

	function homey_block_users() {
		$users_admin_access = homey_option('users_admin_access');

		if( is_user_logged_in() ) {
			if ($users_admin_access != 0) {
				if (is_admin() && !current_user_can('administrator') && isset( $_GET['action'] ) != 'delete' && !(defined('DOING_AJAX') && DOING_AJAX)) {
					wp_die(esc_html("You don't have permission to access this page.", "homey"));
					exit;
				}
			}
		}
	}

endif;


function homey_pre_get_posts($query) {

    if( is_admin() ) 
        return;

    if( is_search() && $query->is_main_query() ) {
        $query->set('post_type', 'post');
    } 

}

add_action( 'pre_get_posts', 'homey_pre_get_posts' );
?>