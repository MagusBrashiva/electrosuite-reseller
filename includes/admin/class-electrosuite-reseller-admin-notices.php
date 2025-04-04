<?php
/**
 * Display notices in admin.
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Notices' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Notices Class
 */
class ElectroSuite_Reseller_Admin_Notices {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_print_styles', array( &$this, 'add_notices' ) );
	}

	/**
	 * Add notices + styles if needed.
	 */
	public function add_notices() {
		if ( get_option( '_electrosuite_reseller_needs_update' ) == 1 || get_option( '_electrosuite_reseller_needs_pages' ) == 1 ) {
			wp_enqueue_style( 'electrosuite-reseller-activation', ElectroSuite_Reseller()->plugin_url() . '/assets/css/admin/activation.css' );
			add_action( 'admin_notices', array( &$this, 'install_notice' ) );
		}

		$template = get_option( 'template' );

		include( 'electrosuite-reseller-theme-support.php' );

		if ( ! current_theme_supports( 'electrosuite_reseller' ) && ! in_array( $template, $themes_supported ) ) {

			if ( ! empty( $_GET['hide_electrosuite_reseller_theme_support_check'] ) ) {
				update_option( 'electrosuite_reseller_theme_support_check', $template );
				return;
			}

			if ( get_option( 'electrosuite_reseller_theme_support_check' ) !== $template ) {
				wp_enqueue_style( 'electrosuite-reseller-activation', ElectroSuite_Reseller()->plugin_url() . '/assets/css/admin/activation.css' );
				add_action( 'admin_notices', array( $this, 'theme_check_notice' ) );
			}
		}
	}

	/**
	 * Show the install notices
	 */
	function install_notice() {
		// If we need to update, include a message with the update button
		if ( get_option( '_electrosuite_reseller_needs_update' ) == 1 ) {
			include( 'views/html-notice-update.php' );
		}

		// If we have just installed, show a message with the install pages button
		elseif ( get_option( '_electrosuite_reseller_needs_pages' ) == 1 ) {
			include( 'views/html-notice-install.php' );
		}
	}

	/**
	 * Show the Theme Check notice
	 */
	function theme_check_notice() {
		include( 'views/html-notice-theme-support.php' );
	}
}

} // end if class exists.

return new ElectroSuite_Reseller_Admin_Notices();

?>