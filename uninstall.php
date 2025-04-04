<?php
/**
 * ElectroSuite Reseller Uninstall
 *
 * Uninstalling ElectroSuite Reseller deletes e.g. user roles, options and pages.
 *
 * @author 		Your Name / Your Company Name
 * @category 	Core
 * @package 	ElectroSuite Reseller/Uninstaller
 * @version 	1.0.0
 */
if( !defined('WP_UNINSTALL_PLUGIN') ) exit();

global $wpdb, $wp_roles;

// For Single site
if ( !is_multisite() ) {

	$status_options = get_option( 'electrosuite_reseller_status_options', array() );

	if ( ! empty( $status_options['uninstall_data'] ) ) {

		// Delete options
		$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'electrosuite_reseller_%';");

		// Roles + caps
		$installer = include( 'includes/admin/class-electrosuite-reseller-install.php' );
		$installer->remove_roles();

		// Pages
		$get_pages = $installer->electrosuite_reseller_pages();
		foreach( $get_pages as $key => $page ) {
			wp_trash_post( get_option( 'electrosuite_reseller_' . $key . '_page_id' ) );
		}

	}

} 
// For Multisite
else {
	global $wpdb, $wp_roles;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );

		// Your uninstall code goes here.
		// List each option to delete here.
		delete_site_option( 'option_name' );
	}

	switch_to_blog( $original_blog_id );
}
?>