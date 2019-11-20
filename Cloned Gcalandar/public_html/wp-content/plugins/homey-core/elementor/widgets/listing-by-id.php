<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Listing by ID Widget.
 * @since 1.0.1
 */
class Homey_Elementor_listing_By_ID extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve widget name.
     *
     * @since 1.0.1
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'homey_elementor_property_by_id';
    }

    /**
     * Get widget title.
     * @since 1.0.1
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Listing by ID', 'homey-core' );
    }

    /**
     * Get widget icon.
     *
     * @since 1.0.1
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'fa fa-building-o';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the widget belongs to.
     *
     * @since 1.0.1
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'homey-elements' ];
    }

    /**
     * Register widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.1
     * @access protected
     */
    protected function _register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label'     => esc_html__( 'Content', 'homey-core' ),
                'tab'       => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'listing_style',
            [
                'label'     => esc_html__( 'Listing style', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'grid'  => 'Grid View',
                    'card'    => 'Card View'
                ],
                'description' => esc_html__('Select grid/card style, the default style will be list view', 'homey'),
                'default' => 'grid',
            ]
        );

        $this->add_control(
            'listing_id',
            [
                'label'     => esc_html__( 'Listing ID', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => esc_html__( 'Enter the listing ID. Example 305', 'homey-core' ),
            ]
        );
        
        $this->end_controls_section();

    }

    /**
     * Render widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.1
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();
                
        $args['listing_style'] =  $settings['listing_style'];
        $args['listing_id']     =  $settings['listing_id'];
       
        if( function_exists( 'homey_listing_by_id' ) ) {
            echo homey_listing_by_id( $args );
        }

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_listing_By_ID );