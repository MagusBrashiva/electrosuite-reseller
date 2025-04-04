<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div id="message" class="updated electrosuite-reseller-message">
	<p><?php echo sprintf( __( '<strong>Welcome to %s</strong> &#8211; You\'re almost ready to start using this plugin :)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->name ); ?></p>
	<p class="submit"><a href="<?php echo add_query_arg('install_electrosuite_reseller_pages', 'true', admin_url('admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-settings') ); ?>" class="button-primary"><?php echo sprintf( __( 'Install %s Pages', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->name ); ?></a> <a class="skip button-primary" href="<?php echo add_query_arg('skip_install_electrosuite_reseller_pages', 'true', admin_url('admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-settings') ); ?>"><?php _e( 'Skip setup', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></a></p>
</div>