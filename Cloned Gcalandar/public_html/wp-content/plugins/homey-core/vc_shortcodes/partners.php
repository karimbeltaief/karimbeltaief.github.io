<?php
/**
 * Partners
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 07/01/16
 * Time: 7:00 PM
 */
if( !function_exists('homey_partners') ) {
    function homey_partners($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'posts_limit' => '',
            'offset' => '',
            'orderby' => '',
            'order' => ''
        ), $atts));

        ob_start();
        $homey_local = homey_get_localization();

        $args = array(
            'post_type' => 'homey_partner',
            'posts_per_page' => $posts_limit,
            'orderby' => $orderby,
            'order' => $order,
            'offset' => $offset
        );
        $wp_qry = new WP_Query($args);
        $token = wp_generate_password(5, false, false);
        if (is_rtl()) {
            $homey_rtl = "true";
        } else {
            $homey_rtl = "false";
        }
        ?>
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

        <div class="module-wrap partners-module partners-module-slider">
            <div class="partners-slider-wrap">
                <div class="row">
                    <div class="partners-slider">

                        <?php
                        if ($wp_qry->have_posts()): while ($wp_qry->have_posts()): $wp_qry->the_post();
                            $website = get_post_meta(get_the_ID(), 'homey_partner_website', true); ?>
                            <div class="partner-item text-center">
                                <div class="partner-thumb">
                                    <!-- <a href="<?php echo esc_url($website); ?>"> -->
                                        <?php
                                        if( has_post_thumbnail( get_the_ID() ) ) {
                                            the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
                                        }else{
                                            homey_image_placeholder( 'homey-listing-thumb' );
                                        }
                                        ?>
                                    <!-- </a> -->
                                </div>
                            </div>
                        <?php endwhile; endif; ?>
                        <?php wp_reset_postdata(); ?>

                    </div>
                </div>
            </div>
        </div>

        <?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }

    add_shortcode('homey-partners', 'homey_partners');
}