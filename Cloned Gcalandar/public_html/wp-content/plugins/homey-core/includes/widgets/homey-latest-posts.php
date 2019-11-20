<?php
/*
 * Widget Name: Latest Posts
 * Version: 1.0
 */

class homey_latest_posts extends WP_Widget {
	
	/**
	 * Register widget
	**/
	public function __construct() {
		
		parent::__construct(
	 		'homey_latest_posts', // Base ID
			esc_html__( 'Homey: Latest Posts', 'homey' ), // Name
			array( 'description' => esc_html__( 'Show latest posts by category', 'homey' ), 'classname' => 'widget-latest-posts') // Args
		);
		
	}

	
	/**
	 * Front-end display of widget
	**/
	public function widget( $args, $instance ) {

		global $before_widget, $after_widget, $before_title, $after_title;
		extract( $args );

		$allowed_html_array = array(
			'div' => array(
				'id' => array(),
				'class' => array()
			),
			'h3' => array(
				'class' => array()
			)
		);

		$title = apply_filters('widget_title', $instance['title'] );
		$items_num = $instance['items_num'];
		$category = $instance['category'];
		
		echo wp_kses( $before_widget, $allowed_html_array );
			
			
			if ( $title ) echo wp_kses( $before_title, $allowed_html_array ) . $title . wp_kses( $after_title, $allowed_html_array );
            ?>
            
            <?php
			$qy_latest = new WP_Query(
				array(
					'post_type' => 'post',
					'cat'		=> $category,
					'posts_per_page' => $items_num,
					'ignore_sticky_posts' => 1,
					'post_status' => 'publish'
				)
			);
			?>
            

			<div class="widget-body">

				<?php $i = 0; ?>
				<?php if( $qy_latest->have_posts() ): while( $qy_latest->have_posts() ): $qy_latest->the_post(); $i++; ?>

					<div class="item-list-view">
			            <div class="media property-item">
			                <div class="media-left">
			                    <div class="item-media item-media-thumb">

			                        <a href="<?php the_permalink(); ?>">
			                        	<?php
						                if( has_post_thumbnail( get_the_ID() ) ) {
						                    the_post_thumbnail( 'homey-gallery-thumb2',  array('class' => 'img-responsive' ) );
						                }else{
						                    homey_image_placeholder( 'homey-gallery-thumb2' );
						                }
						                ?>
			                        </a>
			                    </div>
			                </div>
			                <div class="media-body item-body clearfix">
			                    <div class="item-title-head">
			                        <div class="title-head-left">
			                            <h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			                            <i class="fa fa-calendar-o"></i> <?php printf( __( '%s ago', 'homey' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
			                            <span class="post-author">
			                            	<?php esc_html_e('by', 'homey'); ?> 
			                            	<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php the_author(); ?></a>
			                        	</span>
			                        </div>
			                    </div>
			                    <p><?php echo homey_get_excerpt(7); ?> <a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'homey' ); ?></a></p>
			                </div>
			            </div>
			        </div><!-- .item-wrap -->


				<?php endwhile; endif; ?>

				<?php wp_reset_postdata(); ?>
				
			</div>


	    <?php 
		echo wp_kses( $after_widget, $allowed_html_array );
		
	}
	
	
	/**
	 * Sanitize widget form values as they are saved
	**/
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();

		/* Strip tags to remove HTML. For text inputs and textarea. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['items_num'] = strip_tags( $new_instance['items_num'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		
		return $instance;
		
	}
	
	
	/**
	 * Back-end widget form
	**/
	public function form( $instance ) {
		
		/* Default widget settings. */
		$defaults = array(
			'title' => 'Latest Posts',
			'items_num' => '5',
			'category'  => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>"><?php esc_html_e('Maximum posts to show:', 'homey'); ?></label>
			<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'items_num' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'items_num' ) ); ?>" value="<?php echo esc_attr( $instance['items_num'] ); ?>" size="1" />
		</p>
		<?php
		$blog_cats = get_terms('category', array('hide_empty' => false));
		$cats_array = array();
		?>
		<p>
          <label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e('Category:', 'homey'); ?></label>
          <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>">
          
          <option value=""><?php esc_html_e( 'All', 'homey' ); ?></option>
          <?php foreach($blog_cats as $blog_cat) { ?>
				
		  		<option <?php echo ($instance['category'] == $blog_cat->term_id ) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( $blog_cat->term_id ); ?>"><?php echo esc_attr( $blog_cat->name ); ?></option>

		  <?php } ?>
          
          </select>
       </p>
		
	<?php
	}

}

if ( ! function_exists( 'homey_latest_posts_loader' ) ) {
    function homey_latest_posts_loader (){
     register_widget( 'homey_latest_posts' );
    }
     add_action( 'widgets_init', 'homey_latest_posts_loader' );
}