<?php
/**
 * Debug Plugin Name / Status page - DEPRECATED - Content moved to Tab classes
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin/System Status
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Status' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Status Class - DEPRECATED
 * Most functionality moved to includes/admin/main/class-electrosuite-reseller-admin-tab-*.php classes
 */
class ElectroSuite_Reseller_Admin_Status {

	/**
	 * Handles output of the reports page in admin. - MOVED / NO LONGER USED
	 */
	/*
	public function output() {
		$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'status';
		include_once( 'views/html-admin-page-status.php' );
	}
    */

	/**
	 * Handles output of report - MOVED to Tab_Status class output
	 */
    /*
	public function status_report() {
		global $electrosuite_reseller, $wpdb;
		include_once( 'views/html-admin-page-status-report.php' );
	}
    */

	/**
	 * Handles output of import / export - KEPT FOR POTENTIAL FUTURE USE
     * Would likely be moved to dedicated Import/Export tab classes if implemented.
	 */
	public function status_port( $port ) {
		global $electrosuite_reseller, $wpdb; // $electrosuite_reseller global might be obsolete

		// Define path to the view file relative to the admin directory
        $view_path = dirname( __FILE__ ) . '/views/html-admin-page-status-import-export.php';

        if ( file_exists( $view_path ) ) {
		    include_once( $view_path );
        } else {
             echo '<p>Error: Import/Export view file not found.</p>';
        }
	}

	/**
	 * Handles output of tools - MOVED to Tab_Tools class output/handle_actions
	 */
    /*
	public function status_tools() { ... } // Content removed
    */

	/**
	 * Get tools - MOVED to Tab_Tools class
	 * @return array of tools
	 */
    /*
	public function get_tools() { ... } // Content removed
    */

} // End class

} // End if class exists

// DO NOT instantiate this class anymore
// return new ElectroSuite_Reseller_Admin_Status(); // REMOVED / COMMENTED OUT

?>