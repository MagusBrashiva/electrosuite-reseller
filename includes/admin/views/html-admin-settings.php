<div class="wrap electrosuite_reseller">
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<h2><?php esc_html_e( 'ElectroSuite Reseller Settings', 'electrosuite-reseller' ); ?></h2>

		<div class="icon32 icon32-electrosuite-reseller-settings" id="icon-electrosuite-reseller"><br /></div><h2 class="nav-tab-wrapper">
			<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . admin_url( 'admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
				}

				do_action( 'electrosuite_reseller_settings_tabs' );
			?>
		</h2>
				
		<?php
		do_action( 'electrosuite_reseller_sections_' . $current_tab );
		?>

		<?php // --- ADDED SAVE BUTTON CONTAINER --- ?>
		<div style="float: right; margin: -15px 15px 10px 0;">
			<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save_top" class="button-primary" type="submit" value="<?php _e( 'Save Changes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?>" />
			<?php endif; ?>
		</div>
		<div style="clear: both;"></div> <?php // Clear the float ?>
		<?php // --- END ADDED SAVE BUTTON CONTAINER --- ?>

		<?php
		do_action( 'electrosuite_reseller_settings_' . $current_tab );
		?>

		<p class="submit">
			<?php if ( ! isset( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save Changes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?>" />
			<?php endif; ?>
			<input type="hidden" name="subtab" id="last_tab" />
			<?php wp_nonce_field( 'electrosuite-reseller-settings' ); ?>
		</p>
	</form>
</div>