<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Partners Widget.
 * @since 1.0.1
 */
class Homey_Elementor_Partners extends Widget_Base {

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
        return 'homey_elementor_partners';
    }

    /**
     * Get widget title.
     * @since 1.0.1
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Partners', 'homey-core' );
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
        return 'fa fa-handshake-o';
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
            'orderby',
            [
                'label'     => esc_html__( 'Order By', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                     'none'  => esc_html__( 'None', 'homey-core'),
                     'ID'  => esc_html__( 'ID', 'homey-core'),
                     'title'   => esc_html__( 'Title', 'homey-core'),
                     'date'   => esc_html__( 'Date', 'homey-core'),
                     'rand'   => esc_html__( 'Random', 'homey-core'),
                     'menu_order'   => esc_html__( 'Menu Order', 'homey-core'),
                ],
                'description' => '',
                'default' => '',
            ]
        );

        $this->add_control(
            'order',
            [
                'label'     => esc_html__( 'Order', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'ASC'  => esc_html__( 'ASC', 'homey-core'),
                    'DESC'  => esc_html__( 'DESC', 'homey-core')
                ],
                'default' => 'ASC',
            ]
        );

        $this->add_control(
            'offset',
            [
                'label'     => 'Offset',
                'type'      => Controls_Manager::TEXT,
                'description' => '',
            ]
        );

        $this->add_control(
            'posts_limit',
            [
                'label'     => esc_html__( 'Limit', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => esc_html__( 'Number of testimonials to show.', 'homey-core' ),
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
        
        $args['posts_limit'] =  $settings['posts_limit'];
        $args['orderby'] =  $settings['orderby'];
        $args['order'] =  $settings['order'];
        $args['offset'] =  $settings['offset'];
       
        if( function_exists( 'homey_partners' ) ) {
            echo homey_partners( $args );
        }

        if ( Plugin::$instance->editor->is_edit_mode() ) : 

            $token = wp_generate_password(5, false, false);
            if (is_rtl()) {
                $homey_rtl = "true";
            } else {
                $homey_rtl = "false";
            }
        ?>

            <style>
                .slide-animated {
                    opacity: 1;
                }
            </style>
            <script>    
            jQuery(document).ready(function ($) {

                var sliderdiv = $('.partners-slider');

                sliderdiv.slick({
                    rtl: <?php echo esc_attr( $homey_rtl ); ?>,
                    lazyLoad: 'ondemand',
                    infinite: true,
                    speed: 300,
                    slidesToShow: 4,
                    arrows: true,
                    adaptiveHeight: true,
                    dots: true,
                    appendArrows: '.partners-module-slider',
                    prevArrow: '<button type="button" class="slick-prev"><?php echo $homey_local['prev_text'];?></button>',
                    nextArrow: '<button type="button" class="slick-next"><?php echo $homey_local['next_text'];?></button>',
                    responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 769,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    }]
                });
            });
            </script>
        
        <?php endif; 

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_Partners );