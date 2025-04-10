<?php
/**
 * Plugin Name Admin Page Output
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Page' ) ) {

	/**
	 * ElectroSuite_Reseller_Admin_Page Class
	 */
	class ElectroSuite_Reseller_Admin_Page {

		/**
		 * Handles output of the main plugin page in admin.
		 * Uses a tabbed interface.
         * Made static to be called directly as the menu page callback handler.
		 */
		public static function output() { // Ensure 'static' keyword is present
            global $wpdb;

            // --- Determine Current Tab ---
            $default_tab = 'getting-started'; // Initial default before checking setup
            $setup_complete = (bool) get_option( 'electrosuite_reseller_setup_complete', false );

            if ( $setup_complete ) {
                $default_tab = 'status'; // Default to Status tab after setup
            }

            $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : $default_tab;

            // --- Define Tabs ---
            $tabs = apply_filters( 'electrosuite_reseller_main_page_tabs', array(
                'getting-started' => __( 'Getting Started', 'electrosuite-reseller' ),
                'status'          => __( 'System Status', 'electrosuite-reseller' ),
                'tools'           => __( 'Tools', 'electrosuite-reseller' ),
                'changelog'       => __( 'Changelog', 'electrosuite-reseller' ),
                'credits'         => __( 'Credits', 'electrosuite-reseller' ),
                'translations'    => __( 'Translations', 'electrosuite-reseller' ),
                'freedoms'        => __( 'Freedoms', 'electrosuite-reseller' )
            ) );

            // Ensure the determined $current_tab is valid
            if ( ! array_key_exists( $current_tab, $tabs ) ) {
                 $current_tab = $default_tab;
                 if ( ! array_key_exists( $current_tab, $tabs ) && ! empty( $tabs ) ) {
                     reset($tabs);
                     $current_tab = key($tabs);
                 }
            }

            // --- Include the Main View File ---
            // Define view path relative to this file's directory
            $view_path = dirname( __FILE__ ) . '/views/html-admin-page.php';
            if ( file_exists( $view_path ) ) {
                // Pass variables to the view's scope
			    include_once( $view_path );
            } else {
                 echo '<div class="wrap"><h2>Error</h2><p>Main page view file not found.</p></div>';
            }
		} // End static output()

	}

} // end if class exists.

//return new ElectroSuite_Reseller_Admin_Page();

?>