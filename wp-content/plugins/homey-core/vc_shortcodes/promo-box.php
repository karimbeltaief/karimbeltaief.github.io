<?php
/*-----------------------------------------------------------------------------------*/
/*	Module 1
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_promobox') ) {
	function homey_promobox($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'promo_image' => '',
			'promo_title' => '',
			'promo_link' => '',
			'promo_content' => '',
			'promo_link_text' => ''
		), $atts));

		ob_start();
		?>
		<div class="item-promo">
			<div class="media">
				<?php if(!empty($promo_image)) { ?>
				<div class="item-promo-image">
					<div class="item-media item-media-thumb">
						<a href="<?php echo esc_url($promo_link); ?>">
							<?php echo wp_get_attachment_image( $promo_image, 'full', false, array('class' => 'img-responsive') ); ?>
						</a>
					</div>
				</div>
				<?php } ?>
				<div class="media-body item-body">
					<div class="item-title-head">
						<div class="text-left">
							<h2 class="title">
								<a href="<?php echo esc_url($promo_link); ?>"><?php echo $promo_title; ?></a>
							</h2>
							<?php 
							if(!empty($promo_content)) {
								echo $promo_content;
							} else {
								echo $content;
							}
							?>
						</div>
					</div>
					<?php if(!empty($promo_link) && !empty($promo_link_text)) { ?>
					<div class="item-promo-footer">
						<a href="<?php echo esc_url($promo_link); ?>"><?php echo $promo_link_text; ?></a>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;

	}

	add_shortcode('homey-promobox', 'homey_promobox');
}
?>