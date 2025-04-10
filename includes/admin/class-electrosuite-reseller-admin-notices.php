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
		add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
        // --- START: Removed for Setup Notice ---
        // Remove auto-dismissal hook: add_action( 'admin_init', array( $this, 'maybe_dismiss_setup_notice' ) );
        // --- END: Removed for Setup Notice ---
	}

	
	/**
	 * Add notices + styles if needed.
	 */
	public function add_notices() {
        // --- START: Added for Setup Notice ---
        // Check if setup is needed and user can perform setup
        if ( get_transient( '_esr_needs_setup' ) ) {
             if ( function_exists('ElectroSuite_Reseller') && isset( ElectroSuite_Reseller()->manage_plugin ) && current_user_can( ElectroSuite_Reseller()->manage_plugin ) ) {
                // Enqueue activation styles if needed for this notice
                // Check if main plugin instance exists before calling plugin_url()
                if ( function_exists('ElectroSuite_Reseller') ) {
                    wp_enqueue_style( 'electrosuite-reseller-activation', ElectroSuite_Reseller()->plugin_url() . '/assets/css/admin/activation.css' );
                }
                // Hook the setup notice display
                add_action( 'admin_notices', array( $this, 'setup_notice' ) );
             }
        }
        // --- END: Added for Setup Notice ---

		// Original checks for update/pages needed
		if ( get_option( '_electrosuite_reseller_needs_update' ) == 1 || get_option( '_electrosuite_reseller_needs_pages' ) == 1 ) {
			// Ensure styles are enqueued only once if setup notice also triggered it
            if ( ! wp_style_is( 'electrosuite-reseller-activation', 'enqueued' ) ) {
                // Check if main plugin instance exists before calling plugin_url()
                if ( function_exists('ElectroSuite_Reseller') ) {
			        wp_enqueue_style( 'electrosuite-reseller-activation', ElectroSuite_Reseller()->plugin_url() . '/assets/css/admin/activation.css' );
                }
            }
			add_action( 'admin_notices', array( $this, 'install_notice' ) );
		}

        // --- START: Theme Check Logic Fully Removed ---
        // The theme check logic previously here (including the commented-out block)
        // has been moved to class-electrosuite-reseller-admin-welcome.php
        // --- END: Theme Check Logic Fully Removed ---
	}

    
	/**
	 * Show the setup notice.
     * @since 0.0.1
	 */
	public function setup_notice() {
        // --- Correct the URL to point to the main page + getting started tab ---
        $main_page_slug = defined('ELECTROSUITE_RESELLER_PAGE') ? ELECTROSUITE_RESELLER_PAGE : 'electrosuite-reseller';
        $setup_url = admin_url( 'admin.php?page=' . $main_page_slug . '&tab=getting-started' );
        // --- End URL Correction ---

        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';
		?>
		<div id="message" class="notice notice-info is-dismissible electrosuite-reseller-notice electrosuite-reseller-setup-notice">
			<p>
                <strong><?php esc_html_e( 'Welcome to ElectroSuite Reseller!', $text_domain ); ?></strong><br>
                <?php esc_html_e( 'Please complete the setup to start using the plugin.', $text_domain ); ?>
            </p>
			<p>
				<a href="<?php echo esc_url( $setup_url ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Start Setup', $text_domain ); ?>
                </a>
			</p>
		</div>
		<?php
	}

    /**
	 * Check if the user is visiting the settings page and dismiss the setup notice transient.
     * @since 0.0.1
	 */
    public function maybe_dismiss_setup_notice() {
        // Define slugs or retrieve from constants/options if available
        $settings_page_slug = defined('ELECTROSUITE_RESELLER_PAGE') ? (ELECTROSUITE_RESELLER_PAGE . '-settings') : 'electrosuite-reseller-settings';

        // Check if we are on the settings page
        if ( isset( $_GET['page'] ) && $_GET['page'] === $settings_page_slug ) {
            // Check if the transient exists before trying to delete it
            if ( get_transient( '_esr_needs_setup' ) ) {
                delete_transient( '_esr_needs_setup' );
            }
        }
    }
    // --- END: Added for Setup Notice ---

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

	// --- START: Function Removed ---
	/**
	 * Show the Theme Check notice - THIS FUNCTION SHOULD BE DELETED
	 */
	/* function theme_check_notice() {
		include( 'views/html-notice-theme-support.php' );
	} */
    // --- END: Function Removed ---
}

} // end if class exists.

return new ElectroSuite_Reseller_Admin_Notices();

?>