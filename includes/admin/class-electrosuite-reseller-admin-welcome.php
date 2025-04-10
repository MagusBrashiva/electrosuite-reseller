<?php
/**
 * Welcome Page Class
 *
 * Shows a feature overview of your plugin or a new version including credits.
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists( 'ElectroSuite_Reseller_Admin_Welcome' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Welcome class.
 */
class ElectroSuite_Reseller_Admin_Welcome {

	//private $plugin;

	
	
	
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// add_action( 'admin_menu', array( &$this, 'admin_menus') ); // Should already be removed
		// add_action( 'admin_head', array( $this, 'admin_head' ) ); // REMOVE THIS LINE
		add_action( 'admin_init', array( $this, 'welcome' ) );
        add_action( 'admin_init', array( $this, 'handle_theme_notice_dismissal' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
	}

	
    /**
     * Enqueue styles for the main plugin page (which handles all tabs).
     * Hooked to admin_enqueue_scripts.
     *
     * @param string $hook_suffix The hook suffix for the current admin page.
     */
    public function admin_enqueue_styles( $hook_suffix ) {
        // Define the hook suffix for the main plugin page
        // This is typically 'toplevel_page_{menu_slug}'
        $main_page_slug = defined('ELECTROSUITE_RESELLER_PAGE') ? ELECTROSUITE_RESELLER_PAGE : 'electrosuite-reseller'; // Get slug safely
        $main_page_hook = 'toplevel_page_' . $main_page_slug;

        // Get the actual hook registered by add_menu_page if possible (more robust)
        // $GLOBALS['admin_page_hooks'] might not be populated yet when this hook runs,
        // so relying on the standard 'toplevel_page_{slug}' pattern is often necessary.
        // We can add a check for the global as a secondary measure if needed.
        // if ( isset($GLOBALS['admin_page_hooks'][$main_page_slug]) ) {
        //     $main_page_hook = $GLOBALS['admin_page_hooks'][$main_page_slug];
        // }

        // Check if the current hook suffix matches our main page hook
        if ( $hook_suffix === $main_page_hook ) {
             // Check if main plugin instance exists before calling plugin_url() and version
             if ( function_exists('ElectroSuite_Reseller') ) {
                 $main_plugin = ElectroSuite_Reseller();
                 // Use a more specific handle like 'electrosuite-reseller-main-styles'
                 wp_enqueue_style(
                    'electrosuite-reseller-main-styles',
                    $main_plugin->plugin_url() . '/assets/css/admin/welcome.css', // Still points to welcome.css
                    array(), // Dependencies
                    $main_plugin->version // Version
                 );
             }
        }
    }


	
    /**
     * Handles the dismissal link for the theme compatibility notice.
     * Hooked to admin_init.
     *
     * @since 0.0.1
     * @access public // Changed to public as it's called by WP hook
     */
    public function handle_theme_notice_dismissal() {
        // Check if the dismiss parameter is set and the user has the capability
        // Use 'manage_options' as a common capability for managing notices, adjust if needed
        if ( ! empty( $_GET['hide_electrosuite_reseller_theme_support_check'] ) && current_user_can( 'manage_options' ) ) {
            // Optional: Add nonce check here for better security if desired
            // if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'esr_dismiss_theme_notice' ) ) { ... }

            $template = get_option( 'template' );
            update_option( 'electrosuite_reseller_theme_support_check', $template );

            // Redirect back to the welcome page without the query parameter
            // Ensure constant is defined or use fallback
            $welcome_page_slug = defined('ELECTROSUITE_RESELLER_PAGE') ? (ELECTROSUITE_RESELLER_PAGE . '-about') : 'electrosuite-reseller-about';
            wp_safe_redirect( remove_query_arg( 'hide_electrosuite_reseller_theme_support_check', admin_url( 'index.php?page=' . $welcome_page_slug ) ) );
            exit; // Exit is crucial after a redirect
            // } // End nonce check if added
        }
    }


	

	/**
	 * Sends user to the Welcome page on first activation of Plugin Name as well as each
	 * time Plugin Name is upgraded to a new version.
	 *
	 * @access public
	 * @return void
	 */
	public function welcome() {
		// Bail if no activation redirect transient is set
		if ( ! get_transient( '_electrosuite_reseller_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_electrosuite_reseller_activation_redirect' );

		// Bail if we are waiting to install or update via the interface update/install links
		if ( get_option( '_electrosuite_reseller_needs_update' ) == 1 )
			return;

		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) )
			return;

		if ( ( isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action'] ) && ( isset( $_GET['plugin'] ) && strstr( $_GET['plugin'], 'wordpress-plugin-boilerplate.php' ) ) )
			return;

		wp_redirect( admin_url( 'index.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-about' ) );
		exit;
	}

} // end class.

} // end if class exists.


?>