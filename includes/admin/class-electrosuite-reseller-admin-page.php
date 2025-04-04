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
		 * Handles output of the plugin page in admin.
		 */
		public function output() {

			$view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : '';

			if ( false === ( $page_content = get_transient( 'electrosuite_reseller_html_' . $view ) ) ) {

				$page_content = do_action('electrosuite_reseller_html_content_' . $view);

				if ( $page_content ) {
					set_transient( 'electrosuite_reseller_html_' . $view, wp_kses_post( $page_content ), 60*60*24*7 ); // Cached for a week
				}

			}

			include_once( 'views/html-admin-page.php' );

		}

	}

} // end if class exists.

return new ElectroSuite_Reseller_Admin_Page();

?>