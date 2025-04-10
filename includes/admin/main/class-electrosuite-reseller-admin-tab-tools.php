<?php
/**
 * ElectroSuite Reseller Admin Main Page: Tools Tab
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ensure the base class is loaded
if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {
    include_once( 'class-electrosuite-reseller-admin-tab.php' );
}

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab_Tools' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab_Tools Class
 */
class ElectroSuite_Reseller_Admin_Tab_Tools extends ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = 'tools';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = ''; // Defined in main controller

    /**
     * Handles actions submitted from the Tools tab.
     * Hooked to admin_init via the base class constructor.
     */
    public function handle_actions() {
        // Check if we are on the correct page and an action is specified
        if ( isset( $_GET['page'], $_GET['tab'], $_GET['action'] ) &&
             $_GET['page'] === ELECTROSUITE_RESELLER_PAGE &&
             $_GET['tab'] === $this->id &&
             isset( $_REQUEST['_wpnonce'] ) &&
             wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'debug_action' ) &&
             current_user_can( 'manage_options' ) // Add capability check
           )
        {
            $action = sanitize_key( $_GET['action'] );
            $tools = $this->get_tools(); // Get available tools

            // Include installer class only when needed
            $installer_file = dirname( dirname( __FILE__ ) ) . '/class-electrosuite-reseller-install.php';
            if ( file_exists( $installer_file ) ) {
                include_once( $installer_file );
            } else {
                 // Handle error - installer file missing
                 add_action( 'admin_notices', function() {
                     echo '<div class="notice notice-error"><p>Error: Installer class file not found. Tools may not function.</p></div>';
                 });
                 return;
            }

            // Check if installer class exists
            if ( ! class_exists( 'ElectroSuite_Reseller_Install' ) ) {
                 add_action( 'admin_notices', function() {
                     echo '<div class="notice notice-error"><p>Error: Installer class not found. Tools may not function.</p></div>';
                 });
                 return;
            }

			$installer = new ElectroSuite_Reseller_Install();
            $message = ''; // Store feedback message

			switch ( $action ) {
				case "install_pages" :
                    // Ensure create_pages is static or instantiate if not
                    // Assuming it's static based on previous code
                    if ( method_exists('ElectroSuite_Reseller_Install', 'create_pages') ) {
					    ElectroSuite_Reseller_Install::create_pages();
					    $message = sprintf( __( 'All missing %s pages were installed successfully.', 'electrosuite-reseller' ), ElectroSuite_Reseller()->name );
                    } else {
                         $message = 'Error: create_pages method not found.'; // Basic error feedback
                    }
				    break;

				case "reset_roles" :
					// Remove then re-add caps and roles
					$installer->remove_roles();
					$installer->create_roles();
					$message = __( 'Roles successfully reset.', 'electrosuite-reseller' );
				    break;

				case "restart" :
                    // Standard Keys for Options and Transients to Delete
                    $option_prefix = 'electrosuite_reseller_';
                    $transient_tld_prefixes = [
                        'enom_tld_list_cache',
                        // Add ResellerClub/CentralNic prefixes here when known
                    ];
                    $transient_other_prefixes = [
                         'electrosuite_reseller_contributors', // Example from Welcome class
                         '_esr_needs_setup', // Setup notice transient
                         // Add other plugin-specific transients if they exist
                    ];

					$installer->remove_roles();
                    // More robust deletion using direct DB queries
                    global $wpdb;
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $option_prefix . '%' ) );

                    foreach ($transient_tld_prefixes as $prefix) {
                        delete_transient($prefix);
                        error_log("Admin Status Tool: Deleted TLD transient: " . $prefix);
                    }
                    foreach ($transient_other_prefixes as $prefix) {
                        // Handle potential wildcard matching for transients if needed, direct delete for known keys
                        delete_transient($prefix);
                         error_log("Admin Status Tool: Attempted delete of transient: " . $prefix);
                        // Example wildcard delete (use carefully):
                        // $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", '_transient_' . $prefix . '%', '_transient_timeout_' . $prefix . '%' ) );
                    }

                    // TODO: Place your own functions here if needed for plugin-specific cleanup.

					$installer->create_options();
					$installer->create_roles();

					$message = __( 'All previous data has been removed and defaults re-installed.', 'electrosuite-reseller' );
				    break;

				default:
					// Handle custom tools defined by filter
					if( isset( $tools[ $action ]['callback'] ) ) {
						$callback = $tools[ $action ]['callback'];
						if ( is_callable( $callback ) ) {
                            $return = call_user_func( $callback );
                            if( $return === false ) {
                                if( is_array( $callback ) ) {
                                    $class_name = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
                                    $method_name = $callback[1];
                                    $message = sprintf( __( 'Error calling %s::%s', 'electrosuite-reseller' ), esc_html($class_name), esc_html($method_name) );
                                } else {
                                    $message = sprintf( __( 'Error calling %s', 'electrosuite-reseller' ), esc_html( print_r( $callback, true ) ) );
                                }
                            } elseif ( is_string($return) ) {
                                // Assume callback returned a success message
                                $message = $return;
                            }
                        } else {
                            $message = sprintf( __( 'Error: Tool callback for action "%s" is not callable.', 'electrosuite-reseller' ), esc_html($action) );
                        }
					} else {
                         $message = sprintf( __( 'Error: Unknown tool action "%s".', 'electrosuite-reseller' ), esc_html($action) );
                    }
				    break;
			} // End switch

            // Redirect back to the tools tab after action, adding a message parameter
            $redirect_url = admin_url( 'admin.php?page=' . rawurlencode( ELECTROSUITE_RESELLER_PAGE ) . '&tab=' . $this->id );
            if ( $message ) {
                // Basic message encoding, consider using transients for more robust messaging
                $redirect_url = add_query_arg( 'esr_message', urlencode( $message ), $redirect_url );
            }
            wp_safe_redirect( $redirect_url );
            exit;

        } // End nonce/capability check
    }


	/**
	 * Output the content for the Tools tab.
     * Includes the tools view.
	 */
	public function output() {
        global $wpdb; // Make globals available if view needs them

        // Display messages passed via query arg from handle_actions redirect
        if ( isset( $_GET['esr_message'] ) ) {
            $message_type = strpos( strtolower( urldecode( $_GET['esr_message'] ) ), 'error' ) !== false ? 'error' : 'updated';
            echo '<div class="' . esc_attr( $message_type ) . ' notice is-dismissible"><p>' . esc_html( urldecode( $_GET['esr_message'] ) ) . '</p></div>';
        }

        // Display message if settings have been saved (seems unrelated but was in original code)
		if ( isset( $_REQUEST['settings-updated'] ) ) {
			echo '<div class="updated notice is-dismissible"><p>' . esc_html__( 'Your changes have been saved.', 'electrosuite-reseller' ) . '</p></div>';
		}

        // Get tools data to pass to the view
		$tools = $this->get_tools();

        // Define path to the view file relative to the admin directory
        $view_path = dirname( dirname( __FILE__ ) ) . '/views/html-admin-page-status-tools.php';

        if ( file_exists( $view_path ) ) {
		    include_once( $view_path ); // View file will use the $tools variable
        } else {
            echo '<p>Error: Tools view file not found.</p>';
        }
	}

	/**
	 * Get tools definition array.
     * Moved from Status class.
	 *
	 * @return array of tools
	 */
	public function get_tools() {
        // Ensure main plugin object is available
        $plugin_name = function_exists('ElectroSuite_Reseller') ? ElectroSuite_Reseller()->name : 'ElectroSuite Reseller';
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';

		return apply_filters( 'electrosuite_reseller_debug_tools', array(

			'install_pages' => array(
				'name'		=> sprintf( __( 'Install %s Pages', $text_domain ), $plugin_name ),
				'button' 	=> __( 'Install Pages', $text_domain ),
				'desc' 		=> sprintf( __( '<strong class="red">Note:</strong> This tool will install all the missing %s pages. Pages already defined and set up will not be replaced.', $text_domain ), $plugin_name ),
                // Optional: Add callback if needed, otherwise handled by switch in handle_actions
			),

			'reset_roles' => array(
				'name'		=> __( 'Capabilities', $text_domain ),
				'button'	=> __( 'Reset Capabilities', $text_domain ),
				'desc'		=> sprintf( __( 'This tool will reset capabilities. Use this if users cannot access all %s admin pages.', $text_domain ), $plugin_name ),
			),

			'restart' => array(
				'name'		=> __( 'Reset Plugin', $text_domain ),
				'button'	=> __( 'Reset All Data', $text_domain ),
				'desc'		=> sprintf( __( 'This tool will erase all %1$s settings and data, then re-install defaults. Use this with caution. <strong>All current %1$s data will be lost and cannot be recovered.</strong>', $text_domain ), $plugin_name ),
			),

            // Example of a tool with a custom callback
            /*
            'custom_tool' => array(
                'name'     => __( 'My Custom Tool', $text_domain ),
                'button'   => __( 'Run Custom Tool', $text_domain ),
                'desc'     => __( 'Description of what the custom tool does.', $text_domain ),
                'callback' => array( $this, 'run_custom_tool' ) // Method within this class
            ),
            */

		) );
	}

    /* Example callback method for a custom tool */
    /*
    public function run_custom_tool() {
        // Do something...
        // Return a success message string or false on failure
        return __( 'Custom tool executed successfully!', 'electrosuite-reseller' );
    }
    */


} // end class

} // end if class exists

// Instantiate the class so its hooks are registered
new ElectroSuite_Reseller_Admin_Tab_Tools();

?>