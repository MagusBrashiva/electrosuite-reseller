<?php
/**
 * ElectroSuite Reseller Admin.
 *
 * @author      ElectroSuite
 * @category    Admin
 * @package     ElectroSuite Reseller/Admin
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin' ) ) {

class ElectroSuite_Reseller_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Actions
		add_action( 'init', array( $this, 'includes' ) ); // Use $this for non-static methods within the class
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ) );
		add_action( 'current_screen', array( $this, 'tour' ) );
		add_action( 'current_screen', array( $this, 'conditonal_includes' ) );
		add_action( 'admin_footer', 'electrosuite_reseller_print_js', 25 );
        add_action( 'admin_notices', array( $this, 'display_ssl_api_admin_notice' ) ); // Added Action Hook for SSL Notice

		// Filters
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
		add_filter( 'update_footer', array( $this, 'update_footer' ), 15 );
	}

	
	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		// Functions - Check if file exists before including
        $functions_file = plugin_dir_path( __FILE__ ) . 'electrosuite-reseller-admin-functions.php';
        if ( file_exists( $functions_file ) ) {
		    include_once( $functions_file );
        }

		// Use this action to register custom post types, user roles and anything else
		do_action( 'electrosuite_reseller_admin_include' );

		// Classes we only need if the ajax is not-ajax
		if ( ! ( defined('DOING_AJAX') && DOING_AJAX ) ) {

            // Helper function to include class files safely
            $include_class = function( $filename ) {
                $filepath = plugin_dir_path( __FILE__ ) . $filename;
                if ( file_exists( $filepath ) ) {
                    include_once( $filepath );
                    return true; // Indicate success
                } else {
                    // Optional: Log error if a critical class file is missing
                    error_log("ElectroSuite Reseller Warning: Admin class file not found: " . $filepath);
                    return false; // Indicate failure
                }
            };

			// Transifex Stats (Keep if used)
			$include_class( 'class-electrosuite-reseller-transifex-api.php' );
			$include_class( 'class-electrosuite-reseller-transifex-stats.php' );

			// Main Plugin Admin Classes
			$include_class( 'class-electrosuite-reseller-admin-menus.php' );
            $include_class( 'class-electrosuite-reseller-admin-page.php' ); // Include the main page controller
			$include_class( 'class-electrosuite-reseller-admin-settings.php' );
            // $include_class( 'class-electrosuite-reseller-admin-status.php' ); // REMOVE or keep commented if file is kept but deprecated
			$include_class( 'class-electrosuite-reseller-admin-welcome.php' ); // Keep for activation redirect, notices hook, CSS enqueue
			$include_class( 'class-electrosuite-reseller-admin-notices.php' ); // Keep for install/update notices

            // --- START: Include Main Page Tab Classes ---
            $main_tab_files = array(
                'main/class-electrosuite-reseller-admin-tab.php', // Base class
                'main/class-electrosuite-reseller-admin-tab-getting-started.php',
                'main/class-electrosuite-reseller-admin-tab-status.php',
                'main/class-electrosuite-reseller-admin-tab-tools.php',
                'main/class-electrosuite-reseller-admin-tab-changelog.php',
                'main/class-electrosuite-reseller-admin-tab-credits.php',
                'main/class-electrosuite-reseller-admin-tab-translations.php',
                'main/class-electrosuite-reseller-admin-tab-freedoms.php',
            );
            foreach ( $main_tab_files as $tab_file ) {
                $include_class( $tab_file );
            }
            // --- END: Include Main Page Tab Classes ---


			// Plugin Help (Keep if used)
			if ( apply_filters( 'electrosuite_reseller_enable_admin_help_tab', true ) ) {
				$include_class( 'class-electrosuite-reseller-admin-help.php' );
			}
		}
	}

	/**
	 * This includes the plugin tour.
	 */
	public function tour() {
		// Plugin Tour - Placeholder logic
		$ignore_tour = get_option('electrosuite_reseller_ignore_tour');

		if ( !isset( $ignore_tour ) || !$ignore_tour ) {
			//include( 'class-electrosuite-reseller-admin-pointers.php' );
		}
	}

	/**
	 * Include admin files conditionally based on screen ID.
	 */
	public function conditonal_includes() {
		$screen = get_current_screen();
        // Ensure screen object exists
        if ( ! $screen ) {
            return;
        }

		switch ( $screen->id ) {
			case 'dashboard' :
				// Include a file to load only for the dashboard.
			break;
			// Add other cases as needed
			// case 'users' :
			// case 'user' :
			// case 'profile' :
			// case 'user-edit' :
				// Include a file to load only for the user pages.
			// break;
		}
	}

	/**
	 * Prevent any user who cannot 'edit_posts' (subscribers etc) from accessing admin.
	 */
	public function prevent_admin_access() {
		$prevent_access = false;

        // Check if the main plugin class function exists before calling it
        if ( function_exists('ElectroSuite_Reseller') ) {
            // Correct capability check needed if 'manage_plugin' isn't standard
            $manage_capability = isset(ElectroSuite_Reseller()->manage_plugin) ? ElectroSuite_Reseller()->manage_plugin : 'manage_options'; // Default to manage_options

            if ( 'yes' == get_option( 'electrosuite_reseller_lock_down_admin' ) && ! ( defined('DOING_AJAX') && DOING_AJAX ) && ! ( current_user_can( 'edit_posts' ) || current_user_can( $manage_capability ) ) && basename( $_SERVER["SCRIPT_FILENAME"] ) !== 'admin-post.php' ) {
                $prevent_access = true;
            }
        } else {
            // Log error if main plugin function not found
             error_log("ElectroSuite Reseller Error: Main plugin function ElectroSuite_Reseller() not found in prevent_admin_access.");
        }


		$prevent_access = apply_filters( 'electrosuite_reseller_prevent_admin_access', $prevent_access );

		if ( $prevent_access ) {
            // Ensure electrosuite_reseller_get_page_id exists and returns a valid ID
            $redirect_page_id = 0;
            if ( function_exists('electrosuite_reseller_get_page_id') ) {
                // Replace 'page-slug' with the actual page slug you want to redirect to.
                // This function needs to be defined elsewhere. Example: 'dashboard' or 'home'
                $redirect_page_id = electrosuite_reseller_get_page_id( 'home' ); // Example redirection target slug
            }

            $redirect_url = $redirect_page_id ? get_permalink( $redirect_page_id ) : home_url(); // Redirect to homepage if function fails

			wp_safe_redirect( $redirect_url );
			exit;
		}
	}


    /**
     * Display an admin notice on the API settings page if not using HTTPS.
     * Added for SSL check.
     */
    public function display_ssl_api_admin_notice() {
        // Check if we are on the admin side
        if ( ! is_admin() ) {
            return;
        }

        // Get current screen information
        $screen = get_current_screen();
        if ( ! $screen ) { // Ensure screen object is valid
            return;
        }

        // Construct the base screen ID for the settings page
        // Check if constants are defined; use fallback if not.
        $toplevel_slug = defined('ELECTROSUITE_RESELLER_PAGE') ? ELECTROSUITE_RESELLER_PAGE : 'electrosuite-reseller'; // Default if constant not set yet
        $settings_slug = defined('ELECTROSUITE_RESELLER_SETTINGS_SLUG') ? ELECTROSUITE_RESELLER_SETTINGS_SLUG : 'electrosuite-reseller-settings'; // Need to define this or use the actual slug from add_submenu_page

        // The screen ID format is often: toplevel_page_{menu_slug} or {hook_prefix}_page_{menu_slug}
        // Let's check based on common patterns and the specific tab GET parameter
        $is_plugin_settings_page = ( strpos( $screen->id, $toplevel_slug ) !== false || strpos( $screen->id, 'es-reseller' ) !== false ) && isset($_GET['page']) && $_GET['page'] === $settings_slug;


        // Check specifically if the 'tab' GET parameter is 'tab_api'
        $is_on_api_settings_tab = ( isset( $_GET['tab'] ) && $_GET['tab'] === 'tab_api' );

        // Display error notice if on the API settings tab AND connection is NOT secure
        if ( $is_plugin_settings_page && $is_on_api_settings_tab && ! is_ssl() ) {
            ?>
            <div class="notice notice-error"> <?php // Non-dismissible error ?>
                <p>
                    <strong><?php esc_html_e( 'Security Warning:', 'electrosuite-reseller' ); ?></strong>
                    <?php esc_html_e( 'You are accessing API settings over an insecure HTTP connection. Saving credentials is disabled. Please switch to HTTPS to manage API settings.', 'electrosuite-reseller' ); ?>
                    <a href="https://wordpress.org/support/article/administration-over-ssl/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more about HTTPS.', 'electrosuite-reseller' ); ?></a>
                </p>
            </div>
            <?php
        }
    }


	/**
	 * Filters the admin footer text by placing links for the plugin.
	 *
	 * @access public
	 */
	function admin_footer_text($text) {
		$screen = get_current_screen();
        if ( ! $screen ) return $text; // Add check for screen object

        // Check if helper function exists before calling
        $screen_ids = function_exists('electrosuite_reseller_get_screen_ids') ? electrosuite_reseller_get_screen_ids() : array();

		if ( in_array( $screen->id, $screen_ids ) ) {
            // Check if main plugin class function exists
            if ( ! function_exists('ElectroSuite_Reseller') ) {
                return $text; // Return original text if main class unavailable
            }

			$links = apply_filters( 'electrosuite_reseller_admin_footer_text_links', array(
				esc_url(ElectroSuite_Reseller()->web_url . '?utm_source=wpadmin&utm_campaign=footer') => __( 'Website', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				esc_url(ElectroSuite_Reseller()->doc_url . '?utm_source=wpadmin&utm_campaign=footer') => __( 'Documentation', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			) );

			$output_text = ''; // Use different variable name
			$counter = 1; // Use integer for counter

			foreach( $links as $key => $value ) {
				$output_text .= '<a target="_blank" href="' . esc_url($key) . '">' . esc_html($value) . '</a>';

				if( count( $links ) > 1 && count( $links ) != $counter ) {
					$output_text .= ' | ';
				}
                $counter++; // Increment counter correctly
			}

			return $output_text; // Return the constructed text
		}

		return $text; // Return original text if not on plugin screen
	}

	/**
	 * Filters the update footer by placing details of the plugin and links.
	 *
	 * @access public
	 */
	function update_footer( $text ) {
		$screen = get_current_screen();
        if ( ! $screen ) return $text; // Add check for screen object

        // Check if helper function exists
        $screen_ids = function_exists('electrosuite_reseller_get_screen_ids') ? electrosuite_reseller_get_screen_ids() : array();

		if ( in_array( $screen->id, $screen_ids ) ) {
            // Check if main plugin class function exists
            if ( ! function_exists('ElectroSuite_Reseller') ) {
                return $text;
            }

            // Ensure constants are defined or provide fallbacks
            $repo_url = defined('GITHUB_REPO_URL') ? GITHUB_REPO_URL : '#'; // Fallback URL
            $page_constant = defined('ELECTROSUITE_RESELLER_PAGE') ? ELECTROSUITE_RESELLER_PAGE : 'electrosuite-reseller'; // Fallback slug
            $about_page_url = esc_url( admin_url('index.php?page=' . $page_constant . '-about') );

			$version_link = $about_page_url; // Use the calculated URL

            $output_text = '<span class="wrap">'; // Use different variable name

			$links = apply_filters( 'electrosuite_reseller_update_footer_links', array(
				esc_url( $repo_url . '/blob/master/CONTRIBUTING.md?utm_source=wpadmin&utm_campaign=footer') => __( 'Contribute', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				esc_url( $repo_url . '/issues?state=open&utm_source=wpadmin&utm_campaign=footer') => __( 'Report Bugs', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			) );

			foreach( $links as $key => $value ) {
				$output_text .= '<a target="_blank" class="add-new-h2" href="' . esc_url($key) . '">' . esc_html($value) . '</a>';
			}

			$output_text .= '</span>' .
			'<p class="alignright">'.
			sprintf( esc_html__('%s Version', ELECTROSUITE_RESELLER_TEXT_DOMAIN), esc_html(ElectroSuite_Reseller()->name) ).
			' : <a href="' . $version_link . '">'.
			esc_attr( ElectroSuite_Reseller()->version ).
			'</a></p>'; // Removed closing </p> here, looks like it should be after version

			return $output_text; // Return constructed text
		}

		return $text; // Return original text
	}

} // End class ElectroSuite_Reseller_Admin

} // end if class exists

// Instantiate the class
return new ElectroSuite_Reseller_Admin();
?>