<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 23/01/16
 * Time: 11:33 PM
 */
if( !function_exists('homey_blog_posts_carousel') ) {
    function homey_blog_posts_carousel($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'category_id' => '',
            'posts_limit' => '',
            'offset' => '',
        ), $atts));

        ob_start();

        $wp_query_args = array(
            'ignore_sticky_posts' => 1,
            'post_type' => 'post'
        );
        if (!empty($category_id)) {
            $wp_query_args['cat'] = $category_id;
        }
        if (!empty($offset)) {
            $wp_query_args['offset'] = $offset;
        }
        $wp_query_args['post_status'] = 'publish';

        if (empty($posts_limit)) {
            $posts_limit = get_option('posts_per_page');
        }
        $wp_query_args['posts_per_page'] = $posts_limit;

        $the_query = New WP_Query($wp_query_args);

        $token = wp_generate_password(5, false, false);
        if (is_rtl()) {
            $homey_rtl = "true";
        } else {
            $homey_rtl = "false";
        }
        ?>
        <script>
            jQuery(document).ready(function ($) {

                var post_card = $('#blog-carousel-<?php echo esc_attr( $token ); ?>');

                post_card.slick({
                    rtl: <?php echo esc_attr( $homey_rtl ); ?>,
                    lazyLoad: 'ondemand',
                    infinite: true,
                    speed: 300,
                    slidesToShow: 3,
                    arrows: true,
                    adaptiveHeight: true,
                    dots: true,
                    appendArrows: '.blog-module-slider',
                    prevArrow: '<button type="button" class="slick-prev">Prev</button>',
                    nextArrow: '<button type="button" class="slick-next">Next</button>',
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

        <div class="module-wrap blog-module blog-module-slider">
            <div class="blog-module-wrap">
                <div id="blog-carousel-<?php echo esc_attr( $token ); ?>" class="blog-module-slider-view">
                    <?php 
                    if ($the_query->have_posts()): 
                        while ($the_query->have_posts()): $the_query->the_post(); 
                    
                        get_template_part('content-grid');
                    
                        endwhile; 
                    endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
        </div>


        <?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }

    add_shortcode('homey-blog-posts-carousel', 'homey_blog_posts_carousel');
}
?>