<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Testimonials Widget.
 * @since 1.0.1
 */
class Homey_Elementor_Testimonials extends Widget_Base {

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
        return 'homey_elementor_testimonials';
    }

    /**
     * Get widget title.
     * @since 1.0.1
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Testimonials', 'homey-core' );
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
        return 'fa fa-quote-right';
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
            'testimonials_type',
            [
                'label'     => esc_html__( 'Testimonials Type', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'grid'  => esc_html__( 'Grid', 'homey-core'),
                    'slides'    => esc_html__( 'Slides', 'homey-core')
                ],
                'default' => 'grid',
            ]
        );

        $this->add_control(
            'testi_cols',
            [
                'label'     => esc_html__( 'Columns', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'col-sm-4'  => esc_html__( '3 Columns', 'homey-core'),
                    'col-sm-6 col-md-3'    => esc_html__( '4 Columns', 'homey-core')
                ],
                'default' => 'col-sm-4',
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

        $this->add_control(
            'offset',
            [
                'label'     => esc_html__( 'Offset posts', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => '',
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
                'default' => 'none',
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
                
        $args['testimonials_type']        =  $settings['testimonials_type'];
        $args['testi_cols']        =  $settings['testi_cols'];
        $args['posts_limit']     =  $settings['posts_limit'];
        $args['offset']  =  $settings['offset'];
        $args['orderby']  =  $settings['orderby'];
        $args['order']  =  $settings['order'];
       
        if( function_exists( 'homey_testimonials' ) ) {
            echo homey_testimonials( $args );
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

                    $('.testimonials-slider').slick({
                        rtl: <?php echo esc_attr( $homey_rtl ); ?>,
                        lazyLoad: 'ondemand',
                        infinite: true,
                        speed: 300,
                        slidesToShow: 4,
                        arrows: true,
                        adaptiveHeight: true,
                        dots: true,
                        appendArrows: '.testimonials-module-slider',
                        prevArrow: '<button type="button" class="slick-prev"><?php echo $homey_local['prev_text'];?></button>',
                        nextArrow: '<button type="button" class="slick-next"><?php echo $homey_local['next_text'];?></button>',
                        responsive: [
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }]
                    });

                });
            
            </script>
        
        <?php endif;

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_Testimonials );