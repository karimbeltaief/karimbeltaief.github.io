<?php

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Elementor Listings Carousel Widget.
 * @since 1.0.1
 */
class Homey_Elementor_Listings_Carousels extends Widget_Base {

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
        return 'homey_elementor_listings_carousel';
    }

    /**
     * Get widget title.
     * @since 1.0.1
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Listings Carousel', 'homey-core' );
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


        $sort_by = array( 
            '' => esc_html__('Default', 'homey'), 
            'a_price' => esc_html__('Price (Low to High)', 'homey'), 
            'd_price' => esc_html__('Price (High to Low)', 'homey'),
            'a_date' => esc_html__('Date (Old to New)', 'homey'),
            'd_date' => esc_html__('Date (New to Old)', 'homey'),
            'featured_top' => esc_html__('Featured on Top', 'homey'),
            'random' => esc_html__('Random', 'homey'),
        );

        $listing_type = array();
        $room_type = array();
        $listing_country = array();
        $listing_state = array();
        $listing_city = array();
        $listing_area = array();
        homey_get_terms_array_elementor( 'listing_type', $listing_type );
        homey_get_terms_array_elementor( 'room_type', $room_type );
        homey_get_terms_array_elementor( 'listing_country', $listing_country );
        homey_get_terms_array_elementor( 'listing_state', $listing_state );
        homey_get_terms_array_elementor( 'listing_city', $listing_city );
        homey_get_terms_array_elementor( 'listing_area', $listing_area );

        $sort_by = array( 
            '' => esc_html__('Default', 'homey-core'), 
            'a_price' => esc_html__('Price (Low to High)', 'homey-core'), 
            'd_price' => esc_html__('Price (High to Low)', 'homey-core'),
            'a_date' => esc_html__('Date Old to New', 'homey-core'),
            'd_date' => esc_html__('Date New to Old', 'homey-core'),
            'featured_top' => esc_html__('Featured on Top', 'homey-core'),
            'random' => esc_html__('Random', 'homey-core')
        );

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
                'description' => esc_html__("Select grid/card style, the default style will be list view", "homey"),
                'default' => 'grid',
            ]
        );

        $this->add_control(
            'listing_type',
            [
                'label'     => esc_html__( 'Listing Type', 'homey-core' ),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $listing_type,
                'description' => '',
                'multiple' => true,
                'default' => '',
            ]
        );

        $this->add_control(
            'room_type',
            [
                'label'     => esc_html__( 'Room Type', 'homey-core' ),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $room_type,
                'description' => '',
                'multiple' => true,
                'default' => '',
            ]
        );

        $this->add_control(
            'listing_country',
            [
                'label'     => esc_html__( 'Listing Country', 'homey-core' ),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $listing_country,
                'description' => '',
                'multiple' => true,
                'default' => '',
            ]
        );

        $this->add_control(
            'listing_state',
            [
                'label'     => esc_html__( 'Listing State', 'homey-core' ),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $listing_state,
                'description' => '',
                'multiple' => true,
                'default' => '',
            ]
        );

        $this->add_control(
            'listing_city',
            [
                'label'     => esc_html__( 'Listing City', 'homey-core' ),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $listing_city,
                'description' => '',
                'multiple' => true,
                'default' => '',
            ]
        );

        $this->add_control(
            'listing_area',
            [
                'label'     => esc_html__( 'Listing Area', 'homey-core' ),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $listing_area,
                'description' => '',
                'multiple' => true,
                'default' => '',
            ]
        );

        $this->add_control(
            'listing_ids',
            [
                'label'     => esc_html__( 'Listing IDs', 'homey-core' ),
                'type'      => Controls_Manager::TEXT,
                'description'   => esc_html__( 'Enter listings ids comma separated. Ex 12,305,34', 'homey-core' ),
            ]
        );

        $this->add_control(
            'featured_listing',
            [
                'label'     => esc_html__( 'Featured listings', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    ''  => esc_html__( '- Any -', 'homey-core'),
                    'no'    => esc_html__('Without Featured', 'houzez'),
                    'yes'  => esc_html__('Only Featured', 'houzez')
                ],
                "description" => esc_html__("You can make a post featured by clicking the featured listings checkbox while add/edit post", "homey-core"),
                'default' => '',
            ]
        );

        $this->add_control(
            'sort_by',
            [
                'label'     => esc_html__( 'Sort By', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => $sort_by,
                'description' => '',
                'default' => '',
            ]
        );

        $this->add_control(
            'posts_limit',
            [
                'label'     => esc_html__('Number of properties', 'homey-core'),
                'type'      => Controls_Manager::TEXT,
                'description' => '',
                'default' => '9',
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
        
        $this->end_controls_section();

        //Carousel Settings
        $this->start_controls_section(
            'filters_section',
            [
                'label'     => esc_html__( 'Carousel Settings', 'homey-core' ),
                'tab'       => Controls_Manager::TAB_CONTENT,
            ]
        );


        $this->add_control(
            'slides_to_show',
            [
                'label'     => esc_html__('Slides To Show', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '3' => '3 Columns',
                    '4' => '4 Columns'
                ],
                "description" => '',
                'default' => '3',
            ]
        );
        $this->add_control(
            'slides_to_scroll',
            [
                'label'     => esc_html__('Slides To Scroll', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4'
                ],
                "description" => '',
                'default' => '1',
            ]
        );
        $this->add_control(
            'slide_infinite',
            [
                'label'     => esc_html__('Infinite Scroll', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'true' => esc_html__('Yes', 'homey-core' ),
                    'false' => esc_html__('No', 'homey-core' )
                ],
                "description" => '',
                'default' => 'true',
            ]
        );
        $this->add_control(
            'slide_auto',
            [
                'label'     => esc_html__('Auto Play', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'false' => esc_html__('No', 'homey-core' ),
                    'true' => esc_html__('Yes', 'homey-core' )
                    
                ],
                "description" => '',
                'default' => 'false',
            ]
        );
        $this->add_control(
            'auto_speed',
            [
                'label'     => 'Auto Play Speed',
                'type'      => Controls_Manager::TEXT,
                'description' => esc_html__("Autoplay Speed in milliseconds. Default 3000", 'homey-core'),
                'default' => '3000'
            ]
        );
        $this->add_control(
            'navigation',
            [
                'label'     => esc_html__('Next/Prev Navigation', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'false' => esc_html__('No', 'homey-core' ),
                    'true' => esc_html__('Yes', 'homey-core' )
                    
                ],
                "description" => '',
                'default' => 'true',
            ]
        );
        $this->add_control(
            'slide_dots',
            [
                'label'     => esc_html__('Dots Nav', 'homey-core' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'false' => esc_html__('No', 'homey-core' ),
                    'true' => esc_html__('Yes', 'homey-core' )
                    
                ],
                "description" => '',
                'default' => 'true',
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

        $listing_type = $room_type = $listing_country = $listing_state = $listing_city = $listing_area = array();

        if(!empty($settings['listing_type'])) {
            $listing_type = implode (",", $settings['listing_type']);
        }

        if(!empty($settings['room_type'])) {
            $room_type = implode (",", $settings['room_type']);
        }

        if(!empty($settings['listing_country'])) {
            $listing_country = implode (",", $settings['listing_country']);
        }

        if(!empty($settings['listing_state'])) {
            $listing_state = implode (",", $settings['listing_state']);
        }

        if(!empty($settings['listing_city'])) {
            $listing_city = implode (",", $settings['listing_city']);
        }

        if(!empty($settings['listing_area'])) {
            $listing_area = implode (",", $settings['listing_area']);
        }

        $args['listing_type']    =  $listing_type;
        $args['room_type']       =  $room_type;
        $args['listing_country'] =  $listing_country;
        $args['listing_state']   =  $listing_state;
        $args['listing_city']    =  $listing_city;
        $args['listing_area']    =  $listing_area;

        $args['listing_style'] =  $settings['listing_style'];
        $args['featured_listing'] =  $settings['featured_listing'];
        $args['listing_ids'] =  $settings['listing_ids'];
        $args['posts_limit'] =  $settings['posts_limit'];
        $args['sort_by'] =  $settings['sort_by'];
        $args['offset'] =  $settings['offset'];

        $args['slides_to_show'] = $settings['slides_to_show'];
        $args['slides_to_scroll'] = $settings['slides_to_scroll'];
        $args['slide_infinite'] = $settings['slide_infinite'];
        $args['slide_auto'] = $settings['slide_auto'];
        $args['auto_speed'] = $settings['auto_speed'];
        $args['navigation'] = $settings['navigation'];
        $args['slide_dots'] = $settings['slide_dots'];

        
       
        if( function_exists( 'homey_listing_carousel' ) ) {
            echo homey_listing_carousel( $args );
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
                jQuery('.homey-carousel[id^="homey-listing-carousel-"]').each(function(){
                    var $div = jQuery(this);
                    var token = $div.data('token');
                    var obj = window['listing_caoursel_' + token];
                    

                    var columns = <?php echo esc_attr($settings['slides_to_show']); ?>,
                        slidesToShow = <?php echo esc_attr($settings['slides_to_show']); ?>,
                        slidesToScroll = <?php echo esc_attr($settings['slides_to_scroll']); ?>,
                        autoplay = <?php echo esc_attr($settings['slide_auto']); ?>,
                        autoplaySpeed = <?php echo esc_attr($settings['auto_speed']); ?>,
                        dots = <?php echo esc_attr($settings['slide_dots']); ?>,
                        navigation = <?php echo esc_attr($settings['navigation']); ?>,
                        slide_infinite =  <?php echo esc_attr($settings['slide_infinite']); ?>;

                        var listing_style = "<?php echo esc_attr($settings['listing_style']); ?>";
                        var next_text = HOMEY_ajax_vars.next_text;
                        var prev_text = HOMEY_ajax_vars.prev_text;

                        var homey_carousel = jQuery('#homey-listing-carousel-'+token);
                       
                        function parseBool(str) {
                            if( str == 'true' ) { return true; } else { return false; }
                        }

                        homey_carousel.slick({
                            rtl: <?php echo esc_attr($homey_rtl); ?>,
                            lazyLoad: 'ondemand',
                            infinite: slide_infinite,
                            autoplay: autoplay,
                            autoplaySpeed: autoplaySpeed,
                            speed: 300,
                            slidesToShow: columns,
                            slidesToScroll: slidesToScroll,
                            arrows: navigation,
                            adaptiveHeight: true,
                            dots: dots,
                            appendArrows: '.property-module-'+listing_style+'-slider-'+slidesToShow,
                            prevArrow: '<button type="button" class="slick-prev">'+prev_text+'</button>',
                            nextArrow: '<button type="button" class="slick-next">'+next_text+'</button>',
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

Plugin::instance()->widgets_manager->register_widget_type( new Homey_Elementor_Listings_Carousels );