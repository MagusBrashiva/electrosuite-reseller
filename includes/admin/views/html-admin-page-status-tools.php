<form method="post" action="options.php">

	<?php settings_fields( 'electrosuite_reseller_status_settings_fields' ); ?>

	<?php $options = wp_parse_args( get_option( 'electrosuite_reseller_status_options', array() ), array( 'uninstall_data' => 0 ) ); ?>

	<table class="electrosuite_reseller_status_table widefat" cellspacing="0">
		<thead class="tools">
			<tr>
				<th colspan="2"><?php _e( 'Tools', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></th>
			</tr>
		</thead>
		
		<tbody class="tools">
			<?php foreach( $tools as $action => $tool ) { ?>
				<tr>
					<td><?php echo esc_html( $tool['name'] ); ?></td>
					<td>
						<p>
							<?php
                            // Construct the correct base URL for the tools tab
                            $base_url = admin_url('admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '&tab=tools&action=' . $action );
                            // Generate the nonce URL using the correct base URL
                            $nonce_url = wp_nonce_url( $base_url, 'debug_action' );
                            ?>
							<a href="<?php echo esc_url( $nonce_url ); ?>" class="button"><?php echo esc_html( $tool['button'] ); ?></a>
							<span class="description"><?php echo wp_kses_post( $tool['desc'] ); ?></span>
						</p>
					</td>
				</tr>
			<?php } ?>
	 		<tr>
				<td><?php _e( 'Remove all data on uninstall', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></td>
	 			<td>
	 				<p>
						<label><input type="checkbox" class="checkbox" name="electrosuite_reseller_status_options[uninstall_data]" value="1" <?php checked( '1', $options['uninstall_data'] ); ?> /> <?php _e( 'Enabled', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></label>
					</p>
					<p>
						<span class="description"><?php _e( 'This tool will delete all data when uninstalling via Plugins > Delete.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></span>
	 				</p>
	 			</td>
	 		</tr>
		</tbody>
	</table>

	<p class="submit">
		<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) ?>" />
	</p>

</form>