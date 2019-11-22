<?php $add_currency = FCC_Currencies::currency_add_link(); ?>
<div class="wrap">
	<br/>
    <a href="<?php echo esc_url($add_currency);?>" class="page-title-action"><?php esc_html_e('Add New Currency', 'favethemes-currency-converter');?></a>
    <hr class="wp-header-end">
    <br/>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Name', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Code', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Symbol', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Position', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('N. of Decimal Point', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Decimal Point Separator', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Thousands Separator', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Edit', 'favethemes-currency-converter');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Delete', 'favethemes-currency-converter');?></span></span></a></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$form_fields = FCC_Currencies::get_form_fields();

			if($form_fields) {
				foreach ( $form_fields as $data ) { 
					$edit_link = FCC_Currencies::currency_edit_link( $data->id );
					$delete_link = FCC_Currencies::currency_delete_link( $data->id );
					?>

					<tr>
						<td><?php echo $data->currency_name; ?></td>
						<td><?php echo $data->currency_code; ?></td>
						<td><?php echo $data->currency_symbol; ?></td>
						<td><?php echo $data->currency_position; ?></td>
						<td><?php echo $data->currency_decimal; ?></td>
						<td><?php echo $data->currency_decimal_separator; ?></td>
						<td><?php echo $data->currency_thousand_separator; ?></td>
						<td>
							<a href="<?php echo esc_url($edit_link); ?>" class=""
								title="<?php esc_html_e( 'Edit field', 'favethemes-currency-converter' ); ?>"><i class="dashicons dashicons-edit"></i>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url($delete_link); ?>" class=""
								title="<?php esc_html_e( 'Delete field', 'favethemes-currency-converter' ); ?>"><i class="dashicons dashicons-trash" ></i>
							</a>
						</td>
					</tr>
					<?php		
				}
			}
			?>

		</tbody>
	</table>
</div>