<h2><?php esc_html_e('Houzez Theme Settings', 'houzez'); ?></h2>
<?php 
	$active_tab1 = $active_tab2 = $active_tab3 = '';
	if(isset($_GET['page']) && $_GET['page'] == 'houzez_permalinks') {
		$active_tab3 = 'nav-tab-active';
	} else if(isset($_GET['page']) && $_GET['page'] == 'houzez_taxonomies') {
		$active_tab2 = 'nav-tab-active';
	} else if(isset($_GET['page']) && $_GET['page'] == 'houzez_post_types') {
		$active_tab1 = 'nav-tab-active';
	}
?>
<h2 class="nav-tab-wrapper">
    <a href="?page=houzez_post_types" class="nav-tab <?php echo esc_attr($active_tab1);?>"><?php esc_html_e('Custom Post Types', 'houzez-theme-functionality');?></a>
    <!-- <a href="?page=houzez_taxonomies" class="nav-tab <?php echo esc_attr($active_tab2);?>"><?php esc_html_e('Taxonomies', 'houzez-theme-functionality');?></a> -->
    <a href="?page=houzez_permalinks" class="nav-tab <?php echo esc_attr($active_tab3);?>"><?php esc_html_e('Permalinks', 'houzez-theme-functionality');?></a>
</h2>