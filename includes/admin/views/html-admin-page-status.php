<div class="wrap electrosuite-reseller electrosuite-reseller-status">
	<div class="icon32 icon32-electrosuite-reseller-status" id="icon-electrosuite-reseller"><br /></div>
		<h2 class="nav-tab-wrapper">
		<?php
			$tabs = array(
				'status' => __( 'System Status', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'tools'  => __( 'Tools', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				//'import'  => __( 'Import', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				//'export'  => __( 'Export', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			);
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-status&tab=' . $name ) . '" class="nav-tab ';
				if ( $current_tab == $name ) echo 'nav-tab-active';
				echo '">' . $label . '</a>';
			}
		?> 
		</h2><br/> 
	<?php
		switch ( $current_tab ) {
			case "import" :
				$this->status_port( 'import' );
				break;
			case "export" :
				$this->status_port( 'export' );
				break;
			case "tools" :
				$this->status_tools();
				break;
			default :
				$this->status_report();
				break;
		}
	?>
</div>