<?php 
	$active_tab1 = $active_tab2 = $active_tab3 = '';
	if( (isset($_GET['page']) && $_GET['page'] == 'fcc_currencies') && (isset($_GET['tab']) && $_GET['tab'] == 'fcc_rates') ) {
		$active_tab3 = 'nav-tab-active';
	} else if(isset($_GET['page']) && $_GET['page'] == 'fcc_api_settings') {
		$active_tab2 = 'nav-tab-active';
	} else if(isset($_GET['page']) && $_GET['page'] == 'fcc_currencies') {
		$active_tab1 = 'nav-tab-active';
	}
?>
<h2 class="nav-tab-wrapper">
    <a href="?page=fcc_currencies" class="nav-tab <?php echo esc_attr($active_tab1);?>"><?php esc_html_e('Currencies', 'favethemes-currency-converter');?></a>
   
    <a href="?page=fcc_api_settings" class="nav-tab <?php echo esc_attr($active_tab2);?>"><?php esc_html_e('API Settings', 'favethemes-currency-converter');?></a>

    <a href="?page=fcc_currencies&tab=fcc_rates" class="nav-tab <?php echo esc_attr($active_tab3);?>"><?php esc_html_e('Rates', 'favethemes-currency-converter');?></a>
</h2>