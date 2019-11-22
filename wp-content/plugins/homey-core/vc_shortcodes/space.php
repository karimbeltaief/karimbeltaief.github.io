<?php
/*-----------------------------------------------------------------------------------*/
/*	Space
/*-----------------------------------------------------------------------------------*/

function homey_space_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'height' => '50'
    ), $atts ) );
   return '<div style="clear:both; width:100%; height:'.$height.'px"></div>';
}
add_shortcode( 'homey-space', 'homey_space_shortcode' );
?>