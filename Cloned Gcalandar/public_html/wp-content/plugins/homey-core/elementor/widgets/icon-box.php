<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Text with icon Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.1
 */
class Homey_Elementor_Icon_Box extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve Features Block widget name.
     *
     * @since 1.0.1
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'homey_elementor_icon_box';
    }

    /**
     * Get widget title.
     *
     * Retrieve Features Block widget title.
     *
     * @since 1.0.1
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Icon Box', 'homey-core' );
    }

    /**
     * Get widget icon.
     *
     * Retrieve Features Block widget icon.
     *
     * @since 1.0.1
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'fa fa-plug';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the Features Section widget belongs to.
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
     * Register Features Block widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.1
     * @access protected
     */
    protected function _register_controls() {

        //Content
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'homey-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'icon_boxes',
            [
                'label'  => esc_html__( 'Icon Box', 'homey-core' ),
                'type'   => Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name'  => 'icon_type',
                        'label' => esc_html__( 'Icon Type', 'homey-core' ),
                        'type'      => Controls_Manager::SELECT,
                        'options'   => [
                            'fontawesome_icon'  => 'FontAwesome',
                            'custom_icon'    => 'Custom Icon'
                        ],
                        'default' => 'fontawesome_icon'
                    ],
                    [
                        'name'  => 'icon',
                        'label' => esc_html__( 'Fontawesome Icon', 'homey-core' ),
                        'type'  => Controls_Manager::ICON,
                    ],
                    [
                        'name'  => 'custom_icon',
                        'label' => esc_html__( 'Custom Icon', 'homey-core' ),
                        'type'  => Controls_Manager::MEDIA,
                    ],
                    [
                        'name'  => 'title',
                        'label' => esc_html__( 'Title', 'homey-core' ),
                        'type'  => Controls_Manager::TEXT,
                    ],
                    [
                        'name'  => 'text',
                        'label' => esc_html__( 'Text', 'homey-core' ),
                        'type'  => Controls_Manager::TEXTAREA,
                    ],
                    [
                        'name'  => 'read_more_text',
                        'label' => esc_html__( 'Read More Text', 'homey-core' ),
                        'type'  => Controls_Manager::TEXT,
                    ],
                    [
                        'name'  => 'read_more_link',
                        'label' => esc_html__( 'Read More Link', 'homey-core' ),
                        'type'  => Controls_Manager::URL,
                    ],
                ],
                'default' => [],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'content_section_settings',
            [
                'label' => esc_html__( 'Icons Boxes Settings', 'homey-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'icon_boxes_style',
            [
                'label'     => 'Style',
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'style_one'  => 'Style One',
                    'style3'    => 'Stype Two'
                ],
                'description' => '',
                'default' => 'style_one',
            ]
        );
        $this->add_control(
            'icon_boxes_columns',
            [
                'label'     => 'Columns',
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'three_columns'  => 'Three Columns',
                    'four_columns'    => 'Four Columns'
                ],
                'description' => '',
                'default' => 'three_columns',
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Render Features Block widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.1
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();

        $icon_boxes_style = $settings['icon_boxes_style'];
        $icon_boxes_columns = $settings['icon_boxes_columns'];

        if( $icon_boxes_style == 'style3' ) { $no_margin = ''; } else { $no_margin = 'no-margin'; }
        ?>
        <div class="homey-module service-blocks-main services-module <?php echo esc_attr( $icon_boxes_columns ).' '.esc_attr( $icon_boxes_style ); ?>">
            <div class="row <?php echo esc_attr( $no_margin ); ?>">
            <?php
            foreach (  $settings['icon_boxes'] as $icon_box ) { 

                $read_more_link = $icon_box['read_more_link']['url'];
                $is_external = $icon_box['read_more_link']['is_external'];

                ?>

                <div class="module-item">
                    <div class="service-block">
                        <div class="block-icon">
                            <?php
                            if( $icon_box['icon_type'] == "fontawesome_icon" ) { ?>
                                <i class="<?php echo esc_attr($icon_box['icon']); ?>"></i>
                            <?php } else {
                                echo wp_get_attachment_image( $icon_box['custom_icon']['id'] );
                            }
                            ?>
                        </div>
                        <div class="block-content">
                        <h3> <?php echo esc_attr($icon_box['title']); ?></h3>
                            <p><?php echo wp_kses_post($icon_box['text']); ?></p>
                        <?php if( $read_more_link != '' ) { ?>
                            <a href="<?php echo esc_url($read_more_link); ?>"  <?php if($is_external == 'on') { echo 'target="_blank"'; } ?> class="read-more"><?php echo esc_attr( $icon_box['read_more_text'] ); ?></a>
                        <?php } ?>
                        
                        </div>
                    </div>
                </div>

            <?php
            }
            ?>
            </div>
        </div>
    <?php

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_Icon_Box); 