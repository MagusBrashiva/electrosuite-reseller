<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div id="message" class="updated electrosuite-reseller-message">
	<p><?php echo sprintf( __( '<strong>%s Update Required</strong> &#8211; We just need to update your install to the latest version', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->name ); ?></p>
	<p class="submit"><a href="<?php echo add_query_arg( 'do_update_electrosuite_reseller', 'true', admin_url('admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-settings') ); ?>" class="electrosuite-reseller-update-now button-primary"><?php _e( 'Run the updater', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery('.electrosuite-reseller-update-now').click('click', function(){
		var answer = confirm( '<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?>' );
		return answer;
	});
</script>