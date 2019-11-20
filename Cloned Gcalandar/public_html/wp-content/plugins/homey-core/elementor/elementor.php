<?php
/**
 * Name         : Elementor Addons For Homey
 * Description  : Provides additional Elementor Elements for the Homey theme
 * Author : Waqas Riaz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Homey_Elementor_Extensions' ) ) {
    final class Homey_Elementor_Extensions {

        /**
         * Homey_Extensions The single instance of Homey_Extensions.
         * @var     object
         * @access  private
         * @since   1.1.0
         */
        private static $_instance = null;

        /**
         * Constructor function.
         * @access  public
         * @since   1.1.0
         * @return  void
         */
        public function __construct() {
            add_action( 'elementor/elements/categories_registered', array( $this, 'add_widget_categories' ) );
            add_action( 'init', array( $this, 'elementor_widgets' ),  20 );
        }

        /**
         * Homey_Elementor_Extensions Instance
         *
         * Ensures only one instance of Homey_Elementor_Extensions is loaded or can be loaded.
         *
         * @since 1.1.0
         * @static
         * @return Homey_Elementor_Extensions instance
         */
        public static function instance () {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }


        /**
         * Widget Category Register
         *
         * @since  1.1.0
         * @access public
         */
        public function add_widget_categories( $elements_manager ) {
            $elements_manager->add_category(
                'homey-elements',
                [
                    'title' => esc_html__( 'Homey Elements', 'homey-core' ),
                    'icon' => 'fa fa-plug',
                ]
            );
        }

        /**
         * Widgets
         *
         * @since  1.0.0
         * @access public
         */
        public function elementor_widgets() {
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/section-title.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/space.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/search.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/listings.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/listing-carousel.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/listing-by-id.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/listing-by-ids.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/grids.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/icon-box.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/promo-boxes.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/partners.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/testimonials.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/register.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/blog-posts.php';
            require_once HOMEY_PLUGIN_PATH . '/elementor/widgets/blog-posts-carousel.php';
        }
    }
}

if ( did_action( 'elementor/loaded' ) ) {
    // Finally initialize code
    Homey_Elementor_Extensions::instance();
}