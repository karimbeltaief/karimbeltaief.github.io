<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Search Widget.
 * @since 1.3.0
 */
class Homey_Elementor_Search extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve widget name.
     *
     * @since 1.3.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'homey_elementor_search';
    }

    /**
     * Get widget title.
     * @since 1.3.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Banner Search', 'homey-core' );
    }

    /**
     * Get widget icon.
     *
     * @since 1.3.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-post-title';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the widget belongs to.
     *
     * @since 1.3.0
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
     * @since 1.3.0
     * @access protected
     */
    protected function _register_controls() {


    }

    /**
     * Render widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.3.0
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();
        
        global $post, $homey_local, $homey_prefix;
        $homey_search_type = homey_search_type();

        if($homey_search_type == "per_hour") {
            get_template_part('template-parts/search/banner-horizontal', 'hourly');
        } else {
            get_template_part('template-parts/search/banner-horizontal', 'daily');
        }

        if ( Plugin::$instance->editor->is_edit_mode() ) : ?>

            <script>
                jQuery('.search-banner select').selectpicker('refresh');
            </script>
        
        <?php endif;

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_Search );