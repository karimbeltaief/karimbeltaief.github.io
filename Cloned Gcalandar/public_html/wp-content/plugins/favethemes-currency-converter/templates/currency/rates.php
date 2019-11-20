<div class="wrap">
    <br/>
    <a style="float: right;" href="?page=fcc_currencies&tab=fcc_rates&fcc-update=1" class="button button-primary"><?php esc_html_e('Update Exchange Rates', 'favethemes-currency-converter');?></a>
    <hr class="wp-header-end">
    <br/>
    <br/>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Currency Name', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Code', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Exchange Rate', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Last Updated', 'favethemes-currency-converter');?></span></span></a></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$exchange_rates = FCC_Rates::get_exchange_rate_data();

			$i = 0;
			if($exchange_rates) {
				foreach ( $exchange_rates as $data ) { $i++; 

					$c_data = json_decode($data->currency_data);
					?>
					<tr>
						<td><?php echo $c_data->name;?></td>
						<td><?php echo $data->currency_code; ?></td>
						<td><?php echo $data->currency_rate; ?></td>
						<td><?php echo $data->timestamp; ?></td>
					</tr>
					<?php		
				}
			}
			?>

		</tbody>
	</table>
</div>