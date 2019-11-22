<?php
/**
 * Testimonials
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 07/01/16
 * Time: 4:00 PM
 */
if( !function_exists('homey_testimonials') ) {
    function homey_testimonials($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'testimonials_type' => '',
            'testi_cols' => '',
            'posts_limit' => '',
            'offset' => '',
            'orderby' => '',
            'order' => ''
        ), $atts));

        ob_start();
        $homey_local = homey_get_localization();

        $args = array(
            'post_type' => 'homey_testimonials',
            'posts_per_page' => $posts_limit,
            'orderby' => $orderby,
            'order' => $order,
            'offset' => $offset
        );

        $testimonials_data = '';

        $testi_qry = new WP_Query($args);
        if ($testi_qry->have_posts()): 
            while ($testi_qry->have_posts()): $testi_qry->the_post();

                $text = get_post_meta(get_the_ID(), 'homey_testi_text', true);
                $name = get_post_meta(get_the_ID(), 'homey_testi_name', true);
                $position = get_post_meta(get_the_ID(), 'homey_testi_position', true);
                $company = get_post_meta(get_the_ID(), 'homey_testi_company', true);
                $photo_id = get_post_meta(get_the_ID(), 'homey_testi_photo', true);

                $comma = '';
                if(!empty($position) && !empty($company)) {
                    $comma = ', ';
                }

                if ($testimonials_type == 'grid') {
                    $testimonials_data .= '<div class="col-xs-12 '.esc_attr($testi_cols).'">';
                }
                
                $testimonials_data .= '<div class="testimonial-item text-center">';

                    if(!empty($text)) {
                        $testimonials_data .= '<p class="description">'.esc_html($text).'</p>';
                    }
                    $testimonials_data .= '<div class="testimonial-thumb">';
                        $testimonials_data .= wp_get_attachment_image($photo_id, array('120', '120'), false, array('class' => 'img-circle img-responsive'));
                    $testimonials_data .= '</div>';

                    if(!empty($name) || !empty($position) || !empty($company)) {
                        $testimonials_data .= '<p class="auther-info">';

                            if(!empty($name)) {
                                $testimonials_data .= '<strong>'.esc_attr($name).'</strong><br>';
                            }
                            if(!empty($position) || !empty($company)) {
                                $testimonials_data .= '<em>'.esc_attr($position).$comma.esc_attr($company).'</em>';
                            }
                        $testimonials_data .= '</p>';
                    }

                $testimonials_data .= '</div>';

                if ($testimonials_type == 'grid') {
                    $testimonials_data .= '</div>';
                }

            endwhile; 
        endif;
        wp_reset_postdata();
        ?>

        <!--start testimonials module-->
        <?php if ($testimonials_type == 'grid') { ?>

            <div class="module-wrap testimonials-module">
                <div class="row">
                    
                    <?php echo $testimonials_data; ?>
                    
                </div>
            </div>

        <?php } elseif ($testimonials_type == 'slides') { ?>

            <?php
            $token = wp_generate_password(5, false, false);
            if (is_rtl()) {
                $homey_rtl = "true";
            } else {
                $homey_rtl = "false";
            }
            ?>
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

            <div class="module-wrap testimonials-module testimonials-module-slider">
                <div class="testimonials-slider-wrap">
                    <div class="row">
                        <div class="testimonials-slider">
                            <?php echo $testimonials_data; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php } ?>
        <!--end post testimonials module-->


        <?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }

    add_shortcode('homey-testimonials', 'homey_testimonials');
}
?>