
<?php $add_new = Homey_Fields_Builder::field_add_link(); ?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('Fields Builder', 'homey-core');?></h1>
	<a href="<?php echo esc_url($add_new);?>" class="page-title-action"><?php esc_html_e('Add New', 'homey-core');?></a>
	<hr class="wp-header-end">
	<br/>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-title column-primary desc field-column-1"><span><?php esc_html_e('Field Name', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc field-column-2"><span><?php esc_html_e('Edit', 'homey-core');?></span></span></a></th>
				<th scope="col" class="manage-column column-title column-primary desc field-column-3"><span><?php esc_html_e('Delete', 'homey-core');?></span></span></a></th>
			</tr>
		</thead>
		<tbody class="row_position">
			<?php
			$form_fields = Homey_Fields_Builder::get_form_fields();

			if($form_fields) {
				foreach ( $form_fields as $data ) { 
					$edit_link = Homey_Fields_Builder::field_edit_link( $data->id );
					$delete_link = Homey_Fields_Builder::field_delete_link( $data->id, $data->field_id );
					$label = stripslashes($data->label);
				
					$label = homey_wpml_translate_single_string($label);	
					?>
					
					<tr id="<?php echo intval($data->id); ?>">
						<td>
							<?php echo esc_attr($label); ?>
						</td>
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