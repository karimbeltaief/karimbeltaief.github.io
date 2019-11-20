<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Promo Boxes Widget.
 * @since 1.0.1
 */
class Homey_Elementor_Promo_Boxes extends Widget_Base {

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
        return 'homey_elementor_promo_boxes';
    }

    /**
     * Get widget title.
     * @since 1.0.1
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Promo Box', 'homey-core' );
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
        return 'fa fa-users';
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
            'promo_image',
            [
                'label'     => esc_html__( 'Image', 'homey-core' ),
                'type'  => Controls_Manager::MEDIA,
            ]
        );

        $this->add_control(
            'promo_title',
            [
                'label'     => esc_html__( 'Title', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => '',
            ]
        );

        $this->add_control(
            'content',
            [
                'label'     => esc_html__( 'Content', 'homey-core' ),
                'type'      => Controls_Manager::WYSIWYG,
                'description'   => '',
            ]
        );
        $this->add_control(
            'promo_link',
            [
                'label'     => esc_html__( 'URL', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => '',
            ]
        );
        $this->add_control(
            'promo_link_text',
            [
                'label'     => esc_html__( 'URL Text', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => '',
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

        $args['promo_image']    =  $settings['promo_image']['id'];
        $args['promo_title']     =  $settings['promo_title'];
        $args['promo_link']  =  $settings['promo_link'];
        $args['promo_link_text']  =  $settings['promo_link_text'];
        $args['promo_content']  =  $settings['content'];
       
        if( function_exists( 'homey_promobox' ) ) {
            echo homey_promobox( $args );
        }

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_Promo_Boxes );