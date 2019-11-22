<?php $add_currency = Homey_Currencies::currency_add_link(); ?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('Currencies', 'homey-core');?></h1>
    <a href="<?php echo esc_url($add_currency);?>" class="page-title-action"><?php esc_html_e('Add New', 'homey-core');?></a>
    <hr class="wp-header-end">
    <br/>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Name', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Code', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Symbol', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Position', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('N. of Decimal Point', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Decimal Point Separator', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Thousands Separator', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Edit', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc"><span><?php esc_html_e('Delete', 'homey-core');?></span></span></a></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$form_fields = Homey_Currencies::get_form_fields();

			if($form_fields) {
				foreach ( $form_fields as $data ) { 
					$edit_link = Homey_Currencies::currency_edit_link( $data->id );
					$delete_link = Homey_Currencies::currency_delete_link( $data->id );
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
								title="<?php esc_attr_e( 'Edit field', 'homey-core' ); ?>"><i class="dashicons dashicons-edit"></i>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url($delete_link); ?>" class=""
								title="<?php esc_attr_e( 'Delete field', 'homey-core' ); ?>"><i class="dashicons dashicons-trash" ></i>
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