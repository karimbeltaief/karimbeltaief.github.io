<?php
global $post, $homey_prefix;
$header_title = get_post_meta( $post->ID, $homey_prefix. 'header_title', true );
$header_subtitle = get_post_meta( $post->ID, $homey_prefix. 'header_subtitle', true );
$banner_search = get_post_meta( $post->ID, 'homey_head_search_style', true);

if(homey_is_splash()) {
	$header_title = homey_option( 'splash_welcome_text' );
	$header_subtitle = homey_option( 'splash_welcome_sub' );
}

if(!empty($header_title)) {
	echo '<h2 class="banner-title">'.esc_attr($header_title).'</h2>';
}

if(!empty($header_subtitle)) {
	echo '<p class="banner-subtitle">'.esc_attr($header_subtitle).'</p>';
} 
?>