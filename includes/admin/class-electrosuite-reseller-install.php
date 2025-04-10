<?php
/**
 * Installation related functions and actions.
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Classes
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Install' ) ) {

/**
 * ElectroSuite_Reseller_Install Class
 */
class ElectroSuite_Reseller_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( ELECTROSUITE_RESELLER_FILE, array( &$this, 'install' ) );

		add_action( 'admin_init', array( &$this, 'install_actions' ) );
		add_action( 'admin_init', array( &$this, 'check_version' ), 5 );
		add_action( 'in_plugin_update_message-' . plugin_basename( ELECTROSUITE_RESELLER_FILE ), array( &$this, 'in_plugin_update_message' ) );
	}

	/**
	 * check_version function.
	 *
	 * @access public
	 * @return void
	 */
	public function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && ( get_option( 'electrosuite_reseller_version' ) != ElectroSuite_Reseller()->version || get_option( 'electrosuite_reseller_db_version' ) != ElectroSuite_Reseller()->version ) )
			$this->install();

			do_action( 'electrosuite_reseller_updated' );
	}

	/**
	 * Install actions such as installing pages when a button is clicked.
	 *
	 * @access public
	 */
	public function install_actions() {
		// Install - Add pages button
		if ( ! empty( $_GET['install_electrosuite_reseller_pages'] ) ) {

			$this->create_pages();

			// We no longer need to install pages
			delete_option( '_electrosuite_reseller_needs_pages' );
			delete_transient( 'electrosuite_reseller_activation_redirect' );

			// What's new redirect
			wp_redirect( admin_url( 'index.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-about&electrosuite-reseller-installed=true' ) );
			exit;

		// Skip button
		} elseif ( ! empty( $_GET['skip_install_electrosuite_reseller_pages'] ) ) {

			// We no longer need to install pages
			delete_option( '_electrosuite_reseller_needs_pages' );
			delete_transient( 'electrosuite_reseller_activation_redirect' );

			// What's new redirect
			wp_redirect( admin_url( 'index.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-about' ) );
			exit;

		// Update button
		} elseif ( ! empty( $_GET['do_update_electrosuite_reseller'] ) ) {

			$this->update();

			// Update complete
			delete_option( '_electrosuite_reseller_needs_pages' );
			delete_option( '_electrosuite_reseller_needs_update' );
			delete_transient( 'electrosuite_reseller_activation_redirect' );

			// What's new redirect
			wp_redirect( admin_url( 'index.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-about&electrosuite-reseller-updated=true' ) );
			exit;
		}
	}

	
	public function install() {
        // Check if the main plugin function exists before using it
        if ( ! function_exists('ElectroSuite_Reseller') ) {
             error_log("ElectroSuite Reseller Error: Main plugin instance not available during install.");
             return; // Exit if main plugin is unavailable
        }
        $main_plugin = ElectroSuite_Reseller();

		$this->create_options();
		$this->create_roles();

		// Queue upgrades
		$current_version = get_option( 'electrosuite_reseller_version', null );
		$current_db_version = get_option( 'electrosuite_reseller_db_version', null );

		// Use main plugin instance for version comparison
		if ( version_compare( $current_db_version, '1.0.1', '<' ) && ! is_null( $current_db_version ) ) {
			update_option( '_electrosuite_reseller_needs_update', 1 );
		} else {
			update_option( 'electrosuite_reseller_db_version', $main_plugin->version );
		}

		// Update version using main plugin instance
		update_option( 'electrosuite_reseller_version', $main_plugin->version );

		// Check if pages are needed - Replace 'page-slug' with your actual required page slug if applicable
        // This check might be redundant if the setup notice covers all initial configuration.
		if ( function_exists('electrosuite_reseller_get_page_id') && electrosuite_reseller_get_page_id( 'page-slug' ) < 1 ) {
			update_option( '_electrosuite_reseller_needs_pages', 1 );
		}

		// Flush rewrite rules
		flush_rewrite_rules();

        // --- START: Added for Setup Notice ---
        // Set a transient to indicate setup is needed. Set to non-expiring (0).
        set_transient( '_esr_needs_setup', 1, 0 );
        // --- END: Added for Setup Notice ---

		// Redirect to welcome screen (existing logic)
		set_transient( 'electrosuite_reseller_activation_redirect', 1, 60 * 60 );
	}


	/**
	 * Handle updates
	 *
	 * @access public
	 */
	public function update() {
		// Do updates
		$current_db_version = get_option( 'electrosuite_reseller_db_version' );

		if ( version_compare( $current_db_version, '1.0.1', '<' ) || ELECTROSUITE_RESELLER_VERSION == '1.0.1' ) {
			include( 'updates/electrosuite-reseller-update-1.0.1.php' );
			update_option( 'electrosuite_reseller_db_version', '1.0.1' );
		}

		update_option( 'electrosuite_reseller_db_version', ElectroSuite_Reseller()->version );
	}

	/**
	 * List the pages that the plugin relies on, 
	 * fetching the page id's in variables.
	 *
	 * @access public
	 * @return void
	 */
	public function electrosuite_reseller_pages() {
		return apply_filters( 'electrosuite_reseller_pages', array(

			'example' => array(
				'name' 		=> _x( 'example', 'page_slug', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'title' 	=> __( 'Example Page', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'content' 	=> __( 'This page was created as an example to show you the ability to creating pages automatically when the plugin is installed. You may if you wish create a page just to insert a single shortcode. You should find this page already set in the plugin settings. This save the user time to setup the pages the plugin requires.', ELECTROSUITE_RESELLER_TEXT_DOMAIN )
			),

			'shortcode' => array(
				'name' 		=> _x( 'shortcode', 'page_slug', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'title' 	=> __( 'Shortcode Example Page', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'content' 	=> __( '[caption align="alignright" width="300"]<img src="http://placekitten.com/300/205" alt="Cat" title="Cute Cat" width="300" height="205" /> Cute Cat[/caption] This page was created to show shortcode detection in the page.', ELECTROSUITE_RESELLER_TEXT_DOMAIN )
			),

		) );
	}

	/**
	 * Create the pages the plugin relies on, 
	 * storing page id's in variables.
	 *
	 * @access public
	 * @return void
	 */
	public static function create_pages() {
		$pages = self::electrosuite_reseller_pages(); // Get the pages.

		foreach ( $pages as $key => $page ) {
			electrosuite_reseller_create_page( esc_sql( $page['name'] ), 'electrosuite_reseller_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? electrosuite_reseller_get_page_id( $page['parent'] ) : '' );
		}
	}

	/**
	 * Default options
	 *
	 * Sets up the default options used on the settings page
	 *
	 * @access public
	 */
	public function create_options() {
		// Include settings so that we can run through defaults
		include_once( 'class-electrosuite-reseller-admin-settings.php' );

		$settings = ElectroSuite_Reseller_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			foreach ( $section->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}
	}

	
	/**
	 * Create roles and capabilities.
	 * Only adds CPT capabilities if defined in get_core_capabilities.
	 * No longer adds the core 'manage_electrosuite_reseller' capability.
	 * Removes example 'custom_role'. Add back ONLY if needed for frontend functionality.
	 *
	 * @access public
	 */
	public function create_roles() {
		global $wp_roles;

		// Make sure WP_Roles class is available and instantiated.
		if ( ! class_exists('WP_Roles') ) {
			return;
		}
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

        // Add CPT capabilities (if any are defined in get_core_capabilities) to Administrator
        $cpt_capabilities = self::get_core_capabilities(); // Get only CPT capabilities now

        if ( ! empty( $cpt_capabilities ) && $wp_roles->is_role( 'administrator' ) ) {
            foreach( $cpt_capabilities as $cap_group ) { // $cap_group is the array for a specific CPT
                if ( is_array( $cap_group ) ) {
                    foreach( $cap_group as $cap ) {
                        $wp_roles->add_cap( 'administrator', $cap );
                    }
                }
            }
        }

        // Note: The example 'custom_role' registration has been removed.
        // Add it back here ONLY if you have a specific reason for that role.

	}

	
	/**
	 * Get capabilities for ElectroSuite Reseller - specific to CPTs if any.
	 * The core 'manage_electrosuite_reseller' capability is no longer needed as we use 'manage_options'.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function get_core_capabilities() {
		$capabilities = array();

		// List the capability types you want to apply. Often related to custom post types.
        // Remove or modify 'your_cpt_slug' if you don't have CPTs needing specific capabilities.
		$capability_types = apply_filters( 'electrosuite_reseller_capability_types', array( /* 'your_cpt_slug' */ ) );

		foreach( $capability_types as $capability_type ) {
            // Generate standard CPT capabilities
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms (if using custom taxonomies associated with the CPT)
				// "manage_{$capability_type}_terms",
				// "edit_{$capability_type}_terms",
				// "delete_{$capability_type}_terms",
				// "assign_{$capability_type}_terms"
			);
		}

		return $capabilities; // Returns only CPT caps, or empty array if no CPTs defined
	}

	/**
	 * electrosuite_reseller_remove_roles function.
	 *
	 * @access public
	 * @return void
	 */
	public function remove_roles() {
		global $wp_roles;

		if ( class_exists('WP_Roles') ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {

			$capabilities = self::get_core_capabilities();

			foreach( $capabilities as $cap_group ) {
				foreach( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'administrator', $cap );
				}
			}

			remove_role( 'custom_role' );
		}
	}

	/**
	 * Delete all plugin options.
	 *
	 * @access public
	 * @return void
	 */
	public function delete_options() {
		global $wpdb;

		// Delete options
		$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'electrosuite_reseller_%';");
	}

	/**
	 * Active plugins pre update option filter
	 *
	 * @param string $new_value
	 * @return string
	 */
	function pre_update_option_active_plugins($new_value) {
		$old_value = (array) get_option('active_plugins');

		if ($new_value !== $old_value && in_array(W3TC_FILE, (array) $new_value) && in_array(W3TC_FILE, (array) $old_value)) {
			$this->_config->set('notes.plugins_updated', true);

			try {
				$this->_config->save();
			}

			catch(Exception $ex) {}
		}

		return $new_value;
	}

	/**
	 * Show details of plugin changes on Installed Plugin Screen.
	 *
	 * @return void
	 */
	function in_plugin_update_message() {
		$response = wp_remote_get( ELECTROSUITE_RESELLER_README_FILE );

		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {

			// Output Upgrade Notice
			$matches = null;
			$regexp = '~==\s*Upgrade Notice\s*==\s*=\s*[0-9.]+\s*=(.*)(=\s*' . preg_quote( ELECTROSUITE_RESELLER_VERSION ) . '\s*=|$)~Uis';

			if ( preg_match( $regexp, $response['body'], $matches ) ) {
				$notices = (array) preg_split('~[\r\n]+~', trim( $matches[1] ) );

				echo '<div class="electrosuite_reseller_upgrade_notice" style="padding: 8px; margin: 6px 0;">';

				foreach ( $notices as $index => $line ) {
					echo '<p style="margin: 0; font-size: 1.1em; text-shadow: 0 1px 1px #3563e8;">' . preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) . '</p>';
				}

				echo '</div>';
			}

			// Output Changelog
			$matches = null;
			$regexp = '~==\s*Changelog\s*==\s*=\s*[0-9.]+\s*-(.*)=(.*)(=\s*' . preg_quote( ELECTROSUITE_RESELLER_VERSION ) . '\s*-(.*)=|$)~Uis';

			if ( preg_match( $regexp, $response['body'], $matches ) ) {
				$changelog = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );

				echo ' ' . __( 'What\'s new:', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '<div style="font-weight: normal;">';

				$ul = false;

				foreach ( $changelog as $index => $line ) {
					if ( preg_match('~^\s*\*\s*~', $line ) ) {
						if ( ! $ul ) {
							echo '<ul style="list-style: disc inside; margin: 9px 0 9px 20px; overflow:hidden; zoom: 1;">';
							$ul = true;
						}
						$line = preg_replace( '~^\s*\*\s*~', '', htmlspecialchars( $line ) );
						echo '<li style="width: 50%; margin: 0; float: left; ' . ( $index % 2 == 0 ? 'clear: left;' : '' ) . '">' . $line . '</li>';
					}
					else {
						if ( $ul ) {
							echo '</ul>';
							$ul = false;
						}
						echo '<p style="margin: 9px 0;">' . htmlspecialchars( $line ) . '</p>';
					}
				}

				if ($ul) {
					echo '</ul>';
				}

				echo '</div>';
			}
		}
	}

} // end if class.

} // end if class exists.

return new ElectroSuite_Reseller_Install();

?>