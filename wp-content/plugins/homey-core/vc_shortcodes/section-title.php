<?php
/*-----------------------------------------------------------------------------------*/
/*	Module 1
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_section_title') ) {
	function homey_section_title($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'homey_section_title' => '',
			'homey_section_subtitle' => '',
			'homey_section_title_align' => '',
			'homey_section_title_color' => '',
			'fontsize_title' => '',
			'lineheight_title' => '',
			'fontsize_subtitle' => '',
			'lineheight_subtitle' => '',
		), $atts));

		$h2 = $h3 = '';
		$h2_style = false;
		$h3_style = false;

		if( !empty($fontsize_title) ) {
		    $fontsize_title = 'font-size: ' . ( preg_match( '/(px|em|\%|pt|cm)$/', $fontsize_title ) ? $fontsize_title : $fontsize_title . 'px' ) . ';';
		    $h2_style = true;
		}

		if( !empty($lineheight_title) ) {
		    $lineheight_title = 'line-height: ' . ( preg_match( '/(px|em|\%|pt|cm)$/', $lineheight_title ) ? $lineheight_title : $lineheight_title . 'px' ) . ';';
		    $h2_style = true;
		}

		if( !empty($fontsize_subtitle) ) {
		    $fontsize_subtitle = 'font-size: ' . ( preg_match( '/(px|em|\%|pt|cm)$/', $fontsize_subtitle ) ? $fontsize_subtitle : $fontsize_subtitle . 'px' ) . ';';
		    $h3_style = true;
		}

		if( !empty($lineheight_subtitle) ) {
		    $lineheight_subtitle = 'line-height: ' . ( preg_match( '/(px|em|\%|pt|cm)$/', $lineheight_subtitle ) ? $lineheight_subtitle : $lineheight_subtitle . 'px' ) . ';';
		    $h3_style = true;
		}

		if($h2_style) {
			$h2 = 'style="'.$fontsize_title.' '.$lineheight_title.'"';
		}

		if($h3_style) {
			$h3 = 'style="'.$fontsize_subtitle.' '.$lineheight_subtitle.'"';
		}

		ob_start();
		?>
		<div class="homey-module module-title section-title-module <?php echo esc_attr($homey_section_title_align) . ' ' . esc_attr($homey_section_title_color); ?>">
			<h2 <?php echo $h2; ?>><?php echo esc_attr($homey_section_title); ?></h2>

			<p <?php echo $h3; ?> class="sub-heading"><?php echo esc_attr($homey_section_subtitle); ?></p>
		</div>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;

	}

	add_shortcode('homey-section-title', 'homey_section_title');
}
?>