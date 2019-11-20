<?php
    /*
     * Plugin Name: Latest Comments
     * Plugin URI: http://favethemes.com/
     * Description: A widget that shows latest posts slider or list
     * Version: 1.0
     * Author: Waqas Riaz
     * Author URI: http://favethemes.com/
     */
    
    class homey_Latest_Comments extends WP_Widget {
        
        
        /**
         * Register widget
         **/
        public function __construct() {
            
            parent::__construct(
                'homey_latest_comments', // Base ID
                __( 'Homey: Latest Comments', 'homey' ), // Name
                array( 'description' => __( 'Display the most latest comments ', 'homey' ), 'classname' => 'widget-latest-comments' ) // Args
            );
            
        }
        
        
        /**
         * Front-end display of widget
         **/
        public function widget( $args, $instance ) {
            
            extract( $args );
            
            $title = apply_filters('widget_title', $instance['title'] );
            $comments_show = isset( $instance['comments_show'] ) ? $instance['comments_show']: 5;
            
            echo $before_widget;
            
            
            if ( $title ) echo $before_title . $title . $after_title;
            
            // Get the comments
            $recent_comments = get_comments( array(
               'number' => $comments_show,
               'status' => 'approve',
               'type' => 'comment',
               'post_type' => 'post'
            ) );
            ?>

            <div class="widget-body">
            <?php 
                $commentnum = 1;
                foreach ($recent_comments as $comment){ ?>

                    <div class="comment-block">
                        <div class="media">
                            <div class="media-left">
                                <a href="#" class="media-object">
                                    <?php 
                                    $user_id = $comment->user_id;
                                    $author = homey_get_author_by_id('60', '60', 'avatar avatar-60 photo', $user_id);
                                    echo $author['photo'];
                                    ?>
                                </a>
                            </div>
                            <div class="media-body media-middle">
                                <div class="msg-user-info">
                                    <div class="msg-user-left">
                                        <h2 class="title"><span><?php echo( $comment->comment_author ); ?> <?php esc_html_e('on', 'homey');?></span> 
                                            <a href="<?php echo get_permalink( $comment->comment_post_ID );?>#comment-<?php echo $comment->comment_ID; ?>">
                                                <?php echo get_the_title( $comment->comment_post_ID ); ?>        
                                            </a>
                                        </h2>
                                        <div class="message-date">
                                            <i class="fa fa-calendar-o"></i> 
                                                <?php echo get_comment_date( 'M j, Y', $comment->comment_ID ); ?>
                                            
                                        </div>
                                    </div>
                                </div>
                                <p> <?php echo wp_trim_words( $comment->comment_content, 14 ); ?></p>
                            </div>
                        </div>
                    </div>

            <?php } ?>
            </div>


<?php 
    echo $after_widget;
    
    }
    
    
    /**
     * Sanitize widget form values as they are saved
     **/
    public function update( $new_instance, $old_instance ) {
        
        $instance = array();
        
        /* Strip tags to remove HTML. For text inputs and textarea. */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['comments_show'] = strip_tags( $new_instance['comments_show'] );
        
        return $instance;
        
    }
    
    
    /**
     * Back-end widget form
     **/
    public function form( $instance ) {
        
        /* Default widget settings. */
        $defaults = array(
                          'title' => 'Latest Comments',
                          'comments_show' => '5',
                          );
        $instance = wp_parse_args( (array) $instance, $defaults );
        
        ?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'homey'); ?></label>
<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
</p>
<p>
<label for="<?php echo $this->get_field_id( 'comments_show' ); ?>"><?php _e('Comments to show:', 'homey'); ?></label>
<input type="text" id="<?php echo $this->get_field_id( 'comments_show' ); ?>" name="<?php echo $this->get_field_name( 'comments_show' ); ?>" value="<?php echo $instance['comments_show']; ?>" size="1" />
</p>
<p>
<?php
    }
    
    }
    if ( ! function_exists( 'homey_Latest_Comments_loader' ) ) {
        function homey_Latest_Comments_loader (){
            register_widget( 'homey_Latest_Comments' );
        }
        add_action( 'widgets_init', 'homey_Latest_Comments_loader' );
    }
