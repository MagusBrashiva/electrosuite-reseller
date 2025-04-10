<?php
/**
 * ElectroSuite Reseller Admin Main Page: System Status Tab
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ensure the base class is loaded
if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {
    include_once( 'class-electrosuite-reseller-admin-tab.php' );
}

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab_Status' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab_Status Class
 */
class ElectroSuite_Reseller_Admin_Tab_Status extends ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = 'status';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = ''; // Defined in main controller

	/**
	 * Output the content for the System Status tab.
     * Includes the status report view.
	 */
	public function output() {
        global $wpdb; // Make globals available if view needs them

        // Define path to the view file relative to the admin directory
        $view_path = dirname( dirname( __FILE__ ) ) . '/views/html-admin-page-status-report.php';

        if ( file_exists( $view_path ) ) {
		    include_once( $view_path );
        } else {
            echo '<p>Error: Status report view file not found.</p>';
        }
	}

} // end class

} // end if class exists

// Instantiate the class so its hooks are registered
new ElectroSuite_Reseller_Admin_Tab_Status();

?>