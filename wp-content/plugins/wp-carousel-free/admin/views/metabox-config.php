<?php if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

//
// Metabox of the uppers section / Upload section.
// Set a unique slug-like ID.
//
$wpcp_carousel_content_source_settings = 'sp_wpcp_upload_options';

//
// Create a metabox.
//
SP_WPCF::createMetabox(
	$wpcp_carousel_content_source_settings,
	array(
		'title'        => __( 'WordPress Carousel Pro', 'wp-carousel-pro' ),
		'post_type'    => 'sp_wp_carousel',
		'show_restore' => false,
		'context'      => 'normal',
	)
);

//
// Create a section.
//
SP_WPCF::createSection(
	$wpcp_carousel_content_source_settings,
	array(
		'fields' => array(
			array(
				'type'  => 'heading',
				'image' => plugin_dir_url( __DIR__ ) . 'img/wpcp-logo.png',
				'after' => '<i class="fa fa-life-ring"></i> Support',
				'link'  => 'https://shapedplugin.com/support/',
				'class' => 'wpcp-admin-header',
			),
			array(
				'id'      => 'wpcp_carousel_type',
				'type'    => 'carousel_type',
				'title'   => __( 'Carousel Type', 'wp-carousel-pro' ),
				'options' => array(
					'image-carousel'   => array(
						'icon' => 'fa fa-image',
						'text' => __( 'Image', 'wp-carousel-pro' ),
					),
					'post-carousel'    => array(
						'icon' => 'dashicons dashicons-admin-post',
						'text' => __( 'Post', 'wp-carousel-pro' ),
					),
					'product-carousel' => array(
						'icon' => 'fa fa-cart-plus',
						'text' => __( 'Product', 'wp-carousel-pro' ),
					),
					'content-carousel' => array(
						'icon'     => 'fa fa-file-text-o',
						'text'     => __( 'Content', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
					'video-carousel'   => array(
						'icon'     => 'fa fa-play-circle-o',
						'text'     => __( 'Video', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
				),
				'default' => 'image-carousel',
			),
			array(
				'id'          => 'wpcp_gallery',
				'type'        => 'gallery',
				'title'       => 'Gallery Images',
				'wrap_class'  => 'wpcp-gallery-filed-wrapper',
				'add_title'   => __( 'ADD IMAGE', 'wp-carousel-pro' ),
				'edit_title'  => __( 'EDIT IMAGE', 'wp-carousel-pro' ),
				'clear_title' => __( 'REMOVE ALL', 'wp-carousel-pro' ),
				'dependency'  => array( 'wpcp_carousel_type', '==', 'image-carousel' ),
			),
			array(
				'id'         => 'wpcp_display_posts_from',
				'type'       => 'selectf',
				'title'      => __( 'Display Posts From', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Select an option to display the posts.', 'wp-carousel-pro' ),
				'options'    => array(
					'latest'        => array(
						'text' => __( 'Latest', 'wp-carousel-free' ),
					),
					'taxonomy'      => array(
						'text'     => __( 'Taxonomy (Pro)', 'wp-carousel-free' ),
						'pro_only' => true,
					),
					'specific_post' => array(
						'text'     => __( 'Specific Posts (Pro)', 'wp-carousel-free' ),
						'pro_only' => true,
					),
				),
				'default'    => 'latest',
				'class'      => 'chosen',
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),

			array(
				'id'         => 'number_of_total_posts',
				'type'       => 'spinner',
				'title'      => __( 'Total Posts', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Number of total posts to show. Default value is 10.', 'wp-carousel-pro' ),
				'default'    => '10',
				'min'        => 1,
				'max'        => 1000,
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),
			// Product Carousel.
			array(
				'id'         => 'wpcp_display_product_from',
				'type'       => 'selectf',
				'title'      => __( 'Display Product From', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Select an option to display the products.', 'wp-carousel-pro' ),
				'options'    => array(
					'latest'            => array(
						'text' => __( 'Latest Product', 'wp-carousel-pro' ),
					),
					'taxonomy'          => array(
						'text'     => __( 'Product Category (Pro)', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
					'specific_products' => array(
						'text'     => __( 'Specific Product (Pro)', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
				),
				'default'    => 'latest',
				'class'      => 'chosen',
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),

			array(
				'id'         => 'wpcp_total_products',
				'type'       => 'spinner',
				'title'      => __( 'Total Products', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Number of total products to display. Default value is 10.', 'wp-carousel-pro' ),
				'default'    => '10',
				'min'        => 1,
				'max'        => 1000,
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),
		), // End of fields array.
	)
);

//
// Metabox for the Carousel Post Type.
// Set a unique slug-like ID.
//
$wpcp_carousel_shortcode_settings = 'sp_wpcp_shortcode_options';

//
// Create a metabox.
//
SP_WPCF::createMetabox(
	$wpcp_carousel_shortcode_settings,
	array(
		'title'        => __( 'Shortcode Section', 'wp-carousel-pro' ),
		'post_type'    => 'sp_wp_carousel',
		'show_restore' => false,
		'theme'        => 'light',
	)
);

//
// Create a section.
//
SP_WPCF::createSection(
	$wpcp_carousel_shortcode_settings,
	array(
		'title'  => __( 'General Settings', 'wp-carousel-pro' ),
		'icon'   => 'fa fa-wrench',
		'fields' => array(
			array(
				'id'         => 'section_title',
				'type'       => 'switcher',
				'title'      => __( 'Carousel Section Title', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide the carousel section title.', 'wp-carousel-pro' ),
				'default'    => false,
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 75,
			),
			array(
				'id'              => 'section_title_margin_bottom',
				'type'            => 'spacing',
				'title'           => __( 'Carousel Title Margin Bottom', 'wp-carousel-pro' ),
				'subtitle'        => __( 'Set margin bottom for the carousel section title. Default value is 30px.', 'wp-carousel-pro' ),
				'all'             => true,
				'all_text'        => '<i class="fa fa-long-arrow-down"></i>',
				'units'           => array(
					'px',
				),
				'all_placeholder' => 'margin',
				'default'         => array(
					'all' => '30',
				),
				'dependency'      => array(
					'section_title',
					'==',
					'true',
					true,
				),
			),
			array(
				'id'       => 'wpcp_number_of_columns',
				'type'     => 'column',
				'title'    => __( 'Carousel Column(s)', 'wp-carousel-pro' ),
				'subtitle' => __( 'Set number of column on devices.', 'wp-carousel-pro' ),
				'default'  => array(
					'lg_desktop' => '5',
					'desktop'    => '4',
					'laptop'     => '3',
					'tablet'     => '2',
					'mobile'     => '1',
				),
				'min'      => '0',
			),
			array(
				'id'         => 'wpcp_image_order_by',
				'type'       => 'select',
				'title'      => __( 'Order by', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set an order by option.', 'wp-carousel-pro' ),
				'options'    => array(
					'menu_order' => __( 'Drag & Drop', 'wp-carousel-pro' ),
					'rand'       => __( 'Random', 'wp-carousel-pro' ),
				),
				'default'    => 'menu_order',
				'dependency' => array( 'wpcp_carousel_type', 'any', 'image-carousel', true ),
			),
			array(
				'id'         => 'wpcp_post_order_by',
				'type'       => 'select',
				'title'      => __( 'Order by', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Select an order by option.', 'wp-carousel-pro' ),
				'options'    => array(
					'ID'         => __( 'ID', 'wp-carousel-pro' ),
					'date'       => __( 'Date', 'wp-carousel-pro' ),
					'rand'       => __( 'Random', 'wp-carousel-pro' ),
					'title'      => __( 'Title', 'wp-carousel-pro' ),
					'modified'   => __( 'Modified', 'wp-carousel-pro' ),
					'menu_order' => __( 'Menu Order', 'wp-carousel-pro' ),
				),
				'default'    => 'menu_order',
				'dependency' => array( 'wpcp_carousel_type', 'any', 'post-carousel,product-carousel', true ),
			),
			array(
				'id'         => 'wpcp_post_order',
				'type'       => 'select',
				'title'      => __( 'Order', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Select an order option.', 'wp-carousel-pro' ),
				'options'    => array(
					'ASC'  => __( 'Ascending', 'wp-carousel-pro' ),
					'DESC' => __( 'Descending', 'wp-carousel-pro' ),
				),
				'default'    => 'rand',
				'dependency' => array( 'wpcp_carousel_type', 'any', 'post-carousel,product-carousel', true ),
			),
			array(
				'id'       => 'wpcp_preloader',
				'type'     => 'switcher',
				'title'    => __( 'Preloader', 'wp-carousel-pro' ),
				'subtitle' => __( 'Carousel will be hidden until page load completed.', 'wp-carousel-pro' ),
				'default'  => true,
			),
		), // Fields array end.
	)
); // End of Upload section.

//
// Carousel settings section begin.
//
SP_WPCF::createSection(
	$wpcp_carousel_shortcode_settings,
	array(
		'title'  => __( 'Carousel Settings', 'wp-carousel-pro' ),
		'icon'   => 'fa fa-sliders',
		'fields' => array(
			array(
				'id'       => 'wpcp_carousel_auto_play',
				'type'     => 'switcher',
				'title'    => __( 'AutoPlay', 'wp-carousel-pro' ),
				'subtitle' => __( 'On/Off auto play.', 'wp-carousel-pro' ),
				'default'  => true,
			),
			array(
				'id'              => 'carousel_auto_play_speed',
				'type'            => 'spacing',
				'title'           => __( 'AutoPlay Speed', 'wp-carousel-pro' ),
				'subtitle'        => __( 'Set auto play speed. Default value is 3000 milliseconds.', 'wp-carousel-pro' ),
				'all'             => true,
				'all_text'        => false,
				'all_placeholder' => 'speed',
				'default'         => array(
					'all' => '3000',
				),
				'units'           => array(
					'ms',
				),
				'attributes'      => array(
					'min' => 0,
				),
				'dependency'      => array(
					'wpcp_carousel_auto_play',
					'==',
					'true',
				),
			),
			array(
				'id'              => 'standard_carousel_scroll_speed',
				'type'            => 'spacing',
				'title'           => __( 'Pagination Speed', 'wp-carousel-pro' ),
				'subtitle'        => __( 'Set pagination/slide scroll speed. Default value is 600 milliseconds.', 'wp-carousel-pro' ),
				'all'             => true,
				'all_text'        => false,
				'all_placeholder' => 'speed',
				'default'         => array(
					'all' => '600',
				),
				'units'           => array(
					'ms',
				),
				'attributes'      => array(
					'min' => 0,
				),
			),

			array(
				'id'       => 'carousel_pause_on_hover',
				'type'     => 'switcher',
				'title'    => __( 'Pause on Hover', 'wp-carousel-pro' ),
				'subtitle' => __( 'On/Off carousel pause on hover.', 'wp-carousel-pro' ),
				'default'  => true,
			),
			array(
				'id'       => 'carousel_infinite',
				'type'     => 'switcher',
				'title'    => __( 'Infinite Loop', 'wp-carousel-pro' ),
				'subtitle' => __( 'On/Off infinite loop mode.', 'wp-carousel-pro' ),
				'default'  => true,
			),
			array(
				'type'    => 'subheading',
				'content' => __( 'Navigation Settings', 'wp-carousel-pro' ),
			),
			// Navigation Settings.
			array(
				'id'         => 'wpcp_navigation',
				'type'       => 'button_set',
				'title'      => __( 'Navigation', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide carousel navigation.', 'wp-carousel-pro' ),
				'options'    => array(
					'show'        => __( 'Show', 'wp-carousel-pro' ),
					'hide'        => __( 'Hide', 'wp-carousel-pro' ),
					'hide_mobile' => __( 'Hide on Mobile', 'wp-carousel-pro' ),
				),
				'radio'      => true,
				'default'    => 'hide_mobile',
				'attributes' => array(
					'data-depend-id' => 'wpcp_navigation',
				),
			),

			array(
				'id'         => 'wpcp_nav_colors',
				'type'       => 'color_group',
				'title'      => __( 'Navigation Color', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set color for the carousel navigation.', 'wp-carousel-pro' ),
				'options'    => array(
					'color1' => __( 'Color', 'wp-carousel-pro' ),
					'color2' => __( 'Hover Color', 'wp-carousel-pro' ),
				),
				'default'    => array(
					'color1' => '#aaa',
					'color2' => '#52b3d9',
				),
				'dependency' => array(
					'wpcp_navigation',
					'!=',
					'hide',
				),
			),
			// Pagination Settings.
			array(
				'type'    => 'subheading',
				'content' => __( 'Pagination Settings', 'wp-carousel-pro' ),
			),
			array(
				'id'         => 'wpcp_pagination',
				'type'       => 'button_set',
				'title'      => __( 'Pagination', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide carousel pagination.', 'wp-carousel-pro' ),
				'options'    => array(
					'show'        => __( 'Show', 'wp-carousel-pro' ),
					'hide'        => __( 'Hide', 'wp-carousel-pro' ),
					'hide_mobile' => __( 'Hide on Mobile', 'wp-carousel-pro' ),
				),
				'radio'      => true,
				'default'    => 'show',
				'attributes' => array(
					'data-depend-id' => 'wpcp_pagination',
				),
			),
			array(
				'id'         => 'wpcp_pagination_color',
				'type'       => 'color_group',
				'title'      => __( 'Pagination Color', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set color for the carousel pagination dots.', 'wp-carousel-pro' ),
				'options'    => array(
					'color1' => __( 'Color', 'wp-carousel-pro' ),
					'color2' => __( 'Active Color', 'wp-carousel-pro' ),
				),
				'default'    => array(
					'color1' => '#cccccc',
					'color2' => '#52b3d9',
				),
				'dependency' => array( 'wpcp_pagination', '!=', 'hide' ),
			),

			// Miscellaneous Settings.
			array(
				'type'    => 'subheading',
				'content' => __( 'Misc. Settings', 'wp-carousel-pro' ),
			),

			array(
				'id'       => 'wpcp_accessibility',
				'type'     => 'switcher',
				'title'    => __( 'Tab and Key Navigation', 'wp-carousel-pro' ),
				'subtitle' => __( 'Enable/Disable carousel scroll with tab and keyboard.', 'wp-carousel-pro' ),
				'default'  => true,
			),
			array(
				'id'       => 'slider_swipe',
				'type'     => 'switcher',
				'title'    => __( 'Touch Swipe', 'wp-carousel-pro' ),
				'subtitle' => __( 'On/Off touch swipe mode.', 'wp-carousel-pro' ),
				'default'  => true,
			),
			array(
				'id'         => 'slider_draggable',
				'type'       => 'switcher',
				'title'      => __( 'Mouse Draggable', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off mouse draggable mode.', 'wp-carousel-pro' ),
				'default'    => true,
				'dependency' => array(
					'slider_swipe',
					'==',
					'true',
				),
			),
		),
	)
); // Carousel settings section end.

//
// Style settings section begin.
//
SP_WPCF::createSection(
	$wpcp_carousel_shortcode_settings,
	array(
		'title'  => __( 'Style Settings', 'wp-carousel-pro' ),
		'icon'   => 'fa fa-paint-brush',
		'fields' => array(

			array(
				'id'         => 'wpcp_post_detail_position',
				'type'       => 'selectf',
				'title'      => __( 'Content Position', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Select a position for the title, content, meta etc.', 'wp-carousel-pro' ),
				'options'    => array(
					'bottom'       => array(
						'text' => __( 'Bottom', 'wp-carousel-free' ),
					),
					'on_right'     => array(
						'text'     => __( 'Right (Pro)', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
					'on_left'      => array(
						'text'     => __( 'Left (Pro)', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
					'with_overlay' => array(
						'text'     => __( 'Overlay (Pro)', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
				),
				'default'    => 'bottom',
				'dependency' => array( 'wpcp_carousel_type', 'any', 'image-carousel,post-carousel,product-carousel', true ),
			),

			array(
				'id'         => 'wpcp_slide_border',
				'type'       => 'border',
				'title'      => __( 'Slide Border', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set border for the slide.', 'wp-carousel-pro' ),
				'all'        => true,
				'default'    => array(
					'all'   => '1',
					'style' => 'solid',
					'color' => '#dddddd',
				),
				'dependency' => array( 'wpcp_carousel_type', '!=', 'product-carousel' ),
			),

			array(
				'id'         => 'wpcp_slide_background',
				'type'       => 'color',
				'title'      => __( 'Slide Background', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set background color for the slide.', 'wp-carousel-pro' ),
				'default'    => '#f9f9f9',
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),

			// Post Settings.
			array(
				'id'         => 'wpcp_post_title',
				'type'       => 'switcher',
				'title'      => __( 'Post Title', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide post title.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),

			array(
				'id'         => 'wpcp_post_content_show',
				'type'       => 'switcher',
				'title'      => __( 'Post Content', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide post content.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),
			array(
				'id'         => 'wpcp_post_content_type',
				'type'       => 'selectf',
				'title'      => __( 'Content Display Type', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Select a content display type.', 'wp-carousel-pro' ),
				'options'    => array(
					'excerpt'            => array(
						'text' => __( 'Excerpt', 'wp-carousel-pro' ),
					),
					'content'            => array(
						'text'     => __( 'Full Content (Pro)', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
					'content_with_limit' => array(
						'text'     => __( 'Content with Limit (Pro)', 'wp-carousel-pro' ),
						'pro_only' => true,
					),
				),
				'default'    => 'excerpt',
				'dependency' => array( 'wpcp_carousel_type|wpcp_post_content_show', '==|==', 'post-carousel|true' ),
			),

			array(
				'type'       => 'subheading',
				'content'    => __( 'Post Meta', 'wp-carousel-pro' ),
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),

			array(
				'id'         => 'wpcp_post_date_show',
				'type'       => 'switcher',
				'title'      => __( 'Post Date', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide post date.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),
			array(
				'id'         => 'wpcp_post_author_show',
				'type'       => 'switcher',
				'title'      => __( 'Post Author', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide post author name.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),

			// Product Settings.
			array(
				'type'       => 'subheading',
				'content'    => __( 'Product Settings', 'wp-carousel-pro' ),
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),
			array(
				'id'         => 'wpcp_product_name',
				'type'       => 'switcher',
				'title'      => __( 'Product Name', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide product name.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),
			array(
				'id'         => 'wpcp_product_price',
				'type'       => 'switcher',
				'title'      => __( 'Product Price', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide product price.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),
			array(
				'id'         => 'wpcp_product_rating',
				'type'       => 'switcher',
				'title'      => __( 'Product Rating', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide product rating.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),
			array(
				'id'         => 'wpcp_product_cart',
				'type'       => 'switcher',
				'title'      => __( 'Add to Cart Button', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide add to cart button.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),
			// Image Settings.
			array(
				'type'    => 'subheading',
				'content' => __( 'Image Settings', 'wp-carousel-pro' ),
			),
			array(
				'id'         => 'show_image',
				'type'       => 'switcher',
				'title'      => __( 'Image', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Show/Hide slide image.', 'wp-carousel-pro' ),
				'text_on'    => __( 'Show', 'wp-carousel-pro' ),
				'text_off'   => __( 'Hide', 'wp-carousel-pro' ),
				'text_width' => 77,
				'default'    => true,
				'dependency' => array( 'wpcp_carousel_type', 'any', 'post-carousel,product-carousel' ),
			),
			array(
				'id'         => 'wpcp_image_sizes',
				'type'       => 'image_sizes',
				'chosen'     => true,
				'title'      => __( 'Image Sizes', 'wp-carousel-pro' ),
				'default'    => 'full',
				'subtitle'   => __( 'Select a image size.', 'wp-carousel-pro' ),
				'dependency' => array( 'wpcp_carousel_type|show_image', 'any|==', 'image-carousel,post-carousel,product-carousel|true' ),
			),
			array(
				'id'         => 'wpcp_product_image_border',
				'type'       => 'border',
				'title'      => __( 'Image Border', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set border for the product image.', 'wp-carousel-pro' ),
				'all'        => true,
				'default'    => array(
					'all'   => '1',
					'style' => 'solid',
					'color' => '#dddddd',
				),
				'dependency' => array( 'wpcp_carousel_type', '==', 'product-carousel' ),
			),
			array(
				'id'         => '_image_title_attr',
				'type'       => 'checkbox',
				'title'      => __( 'Image Title Attribute', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Check to add image title attribute.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array( 'wpcp_carousel_type|show_image', 'any|==', 'image-carousel,post-carousel,product-carousel|true' ),
			),
		), // End of fields array.
	)
); // Style settings section end.


//
// Typography section begin.
//
SP_WPCF::createSection(
	$wpcp_carousel_shortcode_settings,
	array(
		'title'           => __( 'Typography', 'wp-carousel-pro' ),
		'icon'            => 'fa fa-font',
		'enqueue_webfont' => false,
		'fields'          => array(
			array(
				'type'    => 'notice',
				'style'    => 'normal',
				'content' => __( 'The Following Typography (840+ Google Fonts) options are available in the <a href="https://shapedplugin.com/plugin/wordpress-carousel-pro/" target="_blank">Pro Version</a> only.', 'wp-carousel-free' ),
			),
			array(
				'id'       => 'section_title_font_load',
				'type'     => 'switcherf',
				'title'    => __( 'Load Carousel Section Title Font', 'wp-carousel-pro' ),
				'subtitle' => __( 'On/Off google font for the carousel section title.', 'wp-carousel-pro' ),
				'default'  => false,
			),
			array(
				'id'           => 'wpcp_section_title_typography',
				'type'         => 'typography',
				'title'        => __( 'Carousel Section Title Font', 'wp-carousel-pro' ),
				'subtitle'     => __( 'Set Carousel section title font properties.', 'wp-carousel-pro' ),
				'default'      => array(
					'color'          => '#444444',
					'font-family'    => 'Open Sans',
					'font-weight'    => '600',
					'font-size'      => '24',
					'line-height'    => '28',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'text-transform' => 'none',
					'type'           => 'google',
					'unit'           => 'px',
				),
				'preview'      => 'always',
				'preview_text' => 'Carousel Section Title',
			),
			array(
				'id'         => 'wpcp_image_caption_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Load Caption Font', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off google font for the image caption.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array( 'wpcp_carousel_type', '==', 'image-carousel' ),
			),
			array(
				'id'           => 'wpcp_image_caption_typography',
				'type'         => 'typography',
				'title'        => __( 'Caption Font', 'wp-carousel-pro' ),
				'subtitle'     => __( 'Set caption font properties.', 'wp-carousel-pro' ),
				'default'      => array(
					'color'          => '#333',
					'font-family'    => 'Open Sans',
					'font-weight'    => '600',
					'font-size'      => '15',
					'line-height'    => '23',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'text-transform' => 'capitalize',
					'type'           => 'google',
				),
				'preview_text' => 'The image caption',
				'dependency'   => array( 'wpcp_carousel_type', '==', 'image-carousel', true ),
			),
			array(
				'id'         => 'wpcp_image_desc_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Load Description Font', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off google font for the image description.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array( 'wpcp_carousel_type|wpcp_post_title', '==|==', 'image-carousel|true' ),
			),
			array(
				'id'         => 'wpcp_image_desc_typography',
				'type'       => 'typography',
				'title'      => __( 'Description Font', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set description font properties.', 'wp-carousel-pro' ),
				'default'    => array(
					'color'          => '#333',
					'font-family'    => 'Open Sans',
					'font-weight'    => '400',
					'font-style'     => 'normal',
					'font-size'      => '14',
					'line-height'    => '21',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'type'           => 'google',
				),
				'dependency' => array( 'wpcp_carousel_type', '==', 'image-carousel' ),
			),
			// Post Typography.
			array(
				'id'         => 'wpcp_title_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Load Title Font', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off google font for the slide title.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),
			array(
				'id'           => 'wpcp_title_typography',
				'type'         => 'typography',
				'title'        => __( 'Post Title Font', 'wp-carousel-pro' ),
				'subtitle'     => __( 'Set title font properties.', 'wp-carousel-pro' ),
				'default'      => array(
					'color'          => '#444',
					'hover_color'    => '#555',
					'font-family'    => 'Open Sans',
					'font-style'     => '600',
					'font-size'      => '20',
					'line-height'    => '30',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'text-transform' => 'capitalize',
					'type'           => 'google',
				),
				'hover_color'  => true,
				'preview_text' => 'The Post Title',
				'dependency'   => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),

			array(
				'id'         => 'wpcp_post_content_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Post Content Font Load', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off google font for post the content.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array(
					'wpcp_carousel_type',
					'==',
					'post-carousel',
				),
			),
			array(
				'id'         => 'wpcp_post_content_typography',
				'type'       => 'typography',
				'title'      => __( 'Post Content Font', 'wp-carousel-pro' ),
				'subtitle'   => __( 'Set post content font properties.', 'wp-carousel-pro' ),
				'default'    => array(
					'color'          => '#333',
					'font-family'    => 'Open Sans',
					'font-style'     => '400',
					'font-size'      => '16',
					'line-height'    => '26',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'type'           => 'google',
				),
				'dependency' => array( 'wpcp_carousel_type', '==', 'post-carousel' ),
			),
			array(
				'id'         => 'wpcp_post_meta_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Post Meta Font Load', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off google font for the post meta.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array(
					'wpcp_carousel_type',
					'==',
					'post-carousel',
				),
			),
			array(
				'id'           => 'wpcp_post_meta_typography',
				'type'         => 'typography',
				'title'        => __( 'Post Meta Font', 'wp-carousel-pro' ),
				'subtitle'     => __( 'Set post meta font properties.', 'wp-carousel-pro' ),
				'default'      => array(
					'color'          => '#999',
					'font-family'    => 'Open Sans',
					'font-style'     => '400',
					'font-size'      => '14',
					'line-height'    => '24',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'type'           => 'google',
				),
				'preview_text' => 'Post Meta', // Replace preview text with any text you like.
				'dependency'   => array(
					'wpcp_carousel_type',
					'==',
					'post-carousel',
				),
			),

			// // Product Typography.
			array(
				'id'         => 'wpcp_product_name_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Product Name Font Load', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off google font for the product name.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array(
					'wpcp_carousel_type',
					'==',
					'product-carousel',
				),
			),
			array(
				'id'           => 'wpcp_product_name_typography',
				'type'         => 'typography',
				'title'        => __( 'Product Name Font', 'wp-carousel-pro' ),
				'subtitle'     => __( 'Set product name font properties.', 'wp-carousel-pro' ),
				'default'      => array(
					'color'          => '#444',
					'hover_color'    => '#555',
					'font-family'    => 'Open Sans',
					'font-style'     => '400',
					'font-size'      => '15',
					'line-height'    => '23',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'type'           => 'google',
				),
				'hover_color'  => true,
				'preview_text' => 'Product Name', // Replace preview text.
				'dependency'   => array(
					'wpcp_carousel_type',
					'==',
					'product-carousel',
				),
			),
			array(
				'id'         => 'wpcp_product_price_font_load',
				'type'       => 'switcherf',
				'title'      => __( 'Product Price Font Load', 'wp-carousel-pro' ),
				'subtitle'   => __( 'On/Off google font for the product price.', 'wp-carousel-pro' ),
				'default'    => false,
				'dependency' => array(
					'wpcp_carousel_type',
					'==',
					'product-carousel',
				),
			),
			array(
				'id'           => 'wpcp_product_price_typography',
				'type'         => 'typography',
				'title'        => __( 'Product Price Font', 'wp-carousel-pro' ),
				'subtitle'     => __( 'Set product price font properties.', 'wp-carousel-pro' ),
				'default'      => array(
					'color'          => '#222',
					'font-family'    => 'Open Sans',
					'font-style'     => '700',
					'font-size'      => '14',
					'line-height'    => '26',
					'letter-spacing' => '0',
					'text-align'     => 'center',
					'type'           => 'google',
				),
				'preview_text' => '$49.00', // Replace preview text with any text you like.
				'dependency'   => array(
					'wpcp_carousel_type',
					'==',
					'product-carousel',
				),
			),
		), // End of fields array.
	)
); // Style settings section end.


//
// Metabox of the footer section / shortocde section.
// Set a unique slug-like ID.
//
$wpcp_display_shortcode = 'sp_wpcp_display_shortcode';

//
// Create a metabox.
//
SP_WPCF::createMetabox(
	$wpcp_display_shortcode,
	array(
		'title'        => __( 'WordPress Carousel Pro', 'wp-carousel-pro' ),
		'post_type'    => 'sp_wp_carousel',
		'show_restore' => false,
	)
);

//
// Create a section.
//
SP_WPCF::createSection(
	$wpcp_display_shortcode,
	array(
		'fields' => array(
			array(
				'type'  => 'shortcode',
				'class' => 'wpcp-admin-footer',
			),
		),
	)
);
