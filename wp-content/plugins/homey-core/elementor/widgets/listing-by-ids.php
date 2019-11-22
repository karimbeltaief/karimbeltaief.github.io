<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Listing by IDs Widget.
 * @since 1.0.1
 */
class Homey_Elementor_listing_By_IDs extends Widget_Base {

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
        return 'homey_elementor_property_by_ids';
    }

    /**
     * Get widget title.
     * @since 1.0.1
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Listings by IDs', 'homey-core' );
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
            'columns',
            [
                'label'     => esc_html__( 'Columns in Row', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '2cols'  => '2 Columns',
                    '3cols'    => '3 Columns'
                ],
                'description' => '',
                'default' => '3cols',
            ]
        );

        $this->add_control(
            'listing_ids',
            [
                'label'     => esc_html__( 'Listing IDs', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => esc_html__( 'Enter Listings ids comma separated. Ex 12,305,34', 'homey-core' ),
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
        $args['columns'] =  $settings['columns'];
        $args['listing_ids']     =  $settings['listing_ids'];
       
        if( function_exists( 'homey_listing_by_ids' ) ) {
            echo homey_listing_by_ids( $args );
        }

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_listing_By_IDs );