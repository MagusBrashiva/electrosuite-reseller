<?php
/**
 * ElectroSuite Reseller API Tab Settings
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Settings_APIs_Tab' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Settings_APIs_Tab
 */
class ElectroSuite_Reseller_Settings_APIs_Tab extends ElectroSuite_Reseller_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id 		= 'tab_api';
        $this->label 	= __( 'APIs', ELECTROSUITE_RESELLER_TEXT_DOMAIN );

        add_filter( 'electrosuite_reseller_settings_submenu_array', array( &$this, 'add_menu_page' ), 20 );
        add_filter( 'electrosuite_reseller_settings_tabs_array', array( &$this, 'add_settings_page' ), 20 );
        add_action( 'electrosuite_reseller_settings_' . $this->id, array( &$this, 'output' ) );
        add_action( 'electrosuite_reseller_settings_save_' . $this->id, array( &$this, 'save' ) );
        add_action( 'electrosuite_reseller_sections_' . $this->id, array( &$this, 'output_sections' ) );
    }
    
    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            '' 		        => __( 'eNom', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), // Default section to show first
            'resellerclub' 	=> __( 'ResellerClub', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
            'centralnic'    => __( 'CentralNic', ELECTROSUITE_RESELLER_TEXT_DOMAIN )
        );

        return $sections;
    }
    
    /**
     * Output the settings
     */
    public function output() {
        global $current_section;

        $settings = $this->get_settings( $current_section );

        ElectroSuite_Reseller_Admin_Settings::output_fields( $settings );
    }

        /**
     * Save settings for the API tab.
     * Prevents saving if the connection is not secure (HTTPS).
     */
    public function save() {
        global $current_tab, $current_section;

        // --- NEW: SSL Check ---
        if ( ! is_ssl() ) {
            // Add an error message to be displayed by the settings framework
            // Use the static method from the main settings class
            if ( class_exists('ElectroSuite_Reseller_Admin_Settings') ) {
                ElectroSuite_Reseller_Admin_Settings::add_error(
                __( 'Settings cannot be saved over an insecure HTTP connection. Please use HTTPS.', 'electrosuite-reseller' )
                );
            }
            error_log("ElectroSuite Reseller Security: Attempted to save API settings over HTTP. Save prevented.");
            // Prevent further processing by returning early
            return;
        }
        // --- End SSL Check ---


        // --- Existing Save Logic (only runs if SSL check passes) ---
        // Get the settings definition for the current section
        $settings = $this->get_settings( $current_section );

        // Call the central save function
         if ( class_exists('ElectroSuite_Reseller_Admin_Settings') ) {
             ElectroSuite_Reseller_Admin_Settings::save_fields( $settings, $current_tab, $current_section );
         } else {
             // Handle case where main settings class isn't available (shouldn't happen)
             error_log("ElectroSuite Reseller Error: ElectroSuite_Reseller_Admin_Settings class not found during API settings save.");
             // Maybe add a generic error? For now, just log it.
             // We already returned if not SSL, so this path implies another issue.
         }

         // Note: The 'Your settings have been saved.' message comes from ElectroSuite_Reseller_Admin_Settings::save() if called directly,
         // or might need to be added here or within save_fields depending on the exact structure.
         // Let's assume save_fields handles adding the success message upon successful update_option calls.
    }


    // --- NEW HELPER METHODS ---

         /**
     * Get provider-specific configuration keys and details.
     * All providers defined here MUST have the same array structure.
     *
     * @param string $provider_key 'enom', 'resellerclub', 'centralnic'
     * @return array Configuration array or empty array if invalid provider.
     */
    protected function get_provider_config( $provider_key ) {
        // Define the standard structure based on eNom
        $base_config_keys = array(
            'provider_name'       => '', // Placeholder for provider's display name
            'username_option_key' => '', // Placeholder for the WP option key for the username/ID
            'test_key_option_key' => '', // Placeholder for the WP option key for the test API key
            'live_key_option_key' => '', // Placeholder for the WP option key for the live API key
            'tld_list_transient'  => '', // Placeholder for the transient name for caching available TLDs
            'selected_tlds_key'   => '', // Placeholder for the WP option key storing the selected TLDs array
            'fetch_function'      => '', // Placeholder for the name of the function used to fetch available TLDs
            'default_tlds'        => [],
        );

        $default_fallback_tlds = ['com', 'net', 'org', 'info', 'biz']; // Use the same list for both purposes

        $configs = array(
            'enom' => array_merge($base_config_keys, array(
                'provider_name'       => __('eNom', 'electrosuite-reseller'),
                'username_option_key' => 'electrosuite_reseller_enom_api_username',
                'test_key_option_key' => 'electrosuite_reseller_enom_test_api_key',
                'live_key_option_key' => 'electrosuite_reseller_enom_live_api_key',
                'tld_list_transient'  => 'enom_tld_list_cache',
                'selected_tlds_key'   => 'electrosuite_reseller_enom_checked_tlds',
                'fetch_function'      => 'get_enom_tld_list', // Assumes this global function exists
                'default_tlds'        => $default_fallback_tlds, // Use the defined list
            )),
            'resellerclub' => array_merge($base_config_keys, array(
                'provider_name'       => __('ResellerClub', 'electrosuite-reseller'),
                'username_option_key' => 'electrosuite_reseller_resellerclub_api_username', // Value changes
                'test_key_option_key' => 'electrosuite_reseller_resellerclub_test_api_key', // Value changes
                'live_key_option_key' => 'electrosuite_reseller_resellerclub_live_api_key', // Value changes
                'tld_list_transient'  => 'resellerclub_tld_list_cache',                   // Value changes
                'selected_tlds_key'   => 'electrosuite_reseller_resellerclub_checked_tlds', // Value changes
                'fetch_function'      => 'get_resellerclub_tld_list',                     // Value changes (Placeholder)
                'default_tlds'        => $default_fallback_tlds, // Use the defined list
            )),
            'centralnic' => array_merge($base_config_keys, array(
                'provider_name'       => __('CentralNic', 'electrosuite-reseller'),
                'username_option_key' => 'electrosuite_reseller_centralnic_api_username', // Value changes
                'test_key_option_key' => 'electrosuite_reseller_centralnic_test_api_key', // Value changes
                'live_key_option_key' => 'electrosuite_reseller_centralnic_live_api_key', // Value changes
                'tld_list_transient'  => 'centralnic_tld_list_cache',                   // Value changes
                'selected_tlds_key'   => 'electrosuite_reseller_centralnic_checked_tlds', // Value changes
                'fetch_function'      => 'get_centralnic_tld_list',                     // Value changes (Placeholder)
                'default_tlds'        => $default_fallback_tlds, // Use the defined list
            )),
        );

        return isset( $configs[ $provider_key ] ) ? $configs[ $provider_key ] : array();
    }


        /**
     * Get the standard credential settings fields for a provider.
     * Assumes structure from get_provider_config.
     * Sets Username and Test Key type to 'text', Live Key to 'password'.
     * Adds 'electrosuite-api-credential' class for sizing via CSS.
     *
     * @param array $config Provider configuration from get_provider_config().
     * @return array Array of settings field definitions.
     */
    protected function get_credential_fields( $config ) {
        // Check if essential config keys are present
        if ( empty( $config ) || empty($config['username_option_key']) || empty($config['test_key_option_key']) || empty($config['live_key_option_key']) ) {
             error_log("ElectroSuite API Settings: Missing essential credential keys in config for provider.");
             return array(); // Return empty if essential config keys are missing
        }

        $provider_name = isset($config['provider_name']) ? $config['provider_name'] : __('Provider', 'electrosuite-reseller');

        // Define labels and descriptions using provider name
        $username_label = sprintf( __( '%s Username/ID', 'electrosuite-reseller' ), $provider_name );
        $username_desc  = sprintf( __( 'Enter your %s account Username or identifier.', 'electrosuite-reseller' ), $provider_name );
        $test_key_label = sprintf( __( '%s Test API Key', 'electrosuite-reseller' ), $provider_name );
        $test_key_desc  = sprintf( __( 'Enter your API Key from the %s Test environment. Used when Test Mode is enabled.', 'electrosuite-reseller' ), $provider_name );
        $live_key_label = sprintf( __( '%s Live API Key', 'electrosuite-reseller' ), $provider_name );
        $live_key_desc  = sprintf( __( 'Enter your API Key from the %s Live environment. Used when Test Mode is disabled.', 'electrosuite-reseller' ), $provider_name );

        return array(
            // Username / ID Field - Type 'text'
            array(
                'title'    => $username_label,
                'desc'     => $username_desc,
                'id'       => $config['username_option_key'],
                'type'     => 'text', // Type is text
                'class'    => 'regular-text electrosuite-api-credential', // Added class
                'default'  => '', 'autoload' => false, 'desc_tip' => true,
            ),
            // Test API Key Field - Type 'text'
            array(
                'title'    => $test_key_label,
                'desc'     => $test_key_desc,
                'id'       => $config['test_key_option_key'],
                'type'     => 'text', // Type is text
                'class'    => 'regular-text electrosuite-api-credential', // Added class
                'default'  => '', 'autoload' => false, 'desc_tip' => true,
            ),
            // Live API Key Field - Type 'password'
            array(
                'title'    => $live_key_label,
                'desc'     => $live_key_desc,
                'id'       => $config['live_key_option_key'],
                'type'     => 'password', // Type is password
                'class'    => 'regular-text electrosuite-api-credential', // Added class
                'default'  => '', 'autoload' => false, 'desc_tip' => true,
            ),
        );
    }


    /**
    * Fetch the available TLDs list for a provider.
    * Handles transient caching, decrypts Live API key, and calls the provider-specific fetch function.
    * Assumes fetch function signature: func( $username, $api_key, $is_test_mode )
    * Returns default TLDs on credential/decryption/API errors, adding admin notices.
    *
    * @param array $config Provider configuration.
    * @return array Array of available TLD strings (fetched or default). WP_Error is no longer returned directly.
    */
    protected function fetch_available_tlds( $config ) {
        // --- Basic config check ---
        if ( empty( $config ) || empty( $config['tld_list_transient'] ) || empty( $config['fetch_function'] ) || !isset($config['default_tlds']) || empty($config['provider_name']) ) {
             error_log("ElectroSuite API Settings Error: Provider configuration for TLD fetching is incomplete.");
             // Add admin error for user feedback
             self::add_error( __('TLD list cannot be loaded due to incomplete plugin configuration.', 'electrosuite-reseller') );
             // Return an empty array or a predefined absolute fallback if config['default_tlds'] might also be missing
             return isset($config['default_tlds']) && is_array($config['default_tlds']) ? $config['default_tlds'] : ['com', 'net', 'org'];
        }

        $transient_key  = $config['tld_list_transient'];
        $fetch_function = $config['fetch_function'];
        $default_tlds   = $config['default_tlds'];
        $provider_name  = $config['provider_name'];

        // --- Try cache first ---
        $cached_list = get_transient( $transient_key );
        if ( false !== $cached_list && is_array( $cached_list ) ) {
            // Optional: Could add a visual indicator on settings page if list is cached?
            // error_log("Debug Settings: Using cached TLD list for {$provider_name}."); // Optional Debug Log
            return $cached_list; // Return cached list (could be fetched or fallback)
        }

        // --- Check function exists ---
        if ( ! function_exists( $fetch_function ) ) {
                error_log( "ElectroSuite API Settings Error: TLD fetch function '{$fetch_function}' not found for provider '{$provider_name}'." );
                self::add_error( sprintf( __( 'Required function %s() is missing. Using default TLD list for %s.', 'electrosuite-reseller'), esc_html($fetch_function), esc_html($provider_name) ) );
                // Cache the default list briefly since the function is missing
                set_transient( $transient_key, $default_tlds, MINUTE_IN_SECONDS * 5 );
                return $default_tlds;
        }

        // --- Get Credentials ---
        $is_test_mode = ( get_option( 'electrosuite_reseller_test_mode', 'no' ) === 'yes' );
        $username = get_option( $config['username_option_key'] );
        $api_key_option = $is_test_mode ? $config['test_key_option_key'] : $config['live_key_option_key'];
        $retrieved_api_key = get_option( $api_key_option ); // Plaintext Test or Encrypted Live

        // Flag to determine if we should proceed with API call
        $can_call_api = true;

        // Validate base credentials exist
        if ( empty( $username ) || empty( $retrieved_api_key ) ) {
            $error_detail = empty($username) ? __('Username missing.', 'electrosuite-reseller') : sprintf(__('%s API key missing.', 'electrosuite-reseller'), ($is_test_mode ? 'Test' : 'Live'));
            self::add_error( sprintf( __( 'API credentials (%1$s) missing for %2$s. Cannot fetch live TLD list. Using default list.', 'electrosuite-reseller'), $error_detail, esc_html($provider_name)) );
            $can_call_api = false; // Prevent API call
            // No need to return yet, will fall through to return $default_tlds
        }

        // --- Decrypt Live API Key If Necessary ---
        $api_key_for_calls = $retrieved_api_key; // Initialize

        if ( $can_call_api && false === $is_test_mode ) { // Only decrypt if in Live mode AND base credentials exist
            if ( class_exists('ElectroSuite_Reseller_Security') ) {
                $decrypted_key = ElectroSuite_Reseller_Security::decrypt( $retrieved_api_key );

                if ( false === $decrypted_key ) {
                    // Decryption failed
                    $error_message = sprintf(__('Could not use the Live API key for %s (TLD List Fetch). Decryption failed. Check WP salts and saved key. Using default TLD list.', 'electrosuite-reseller'), esc_html($provider_name));
                    error_log("ElectroSuite Reseller Settings Error: " . $error_message);
                    self::add_error( $error_message ); // Show error to admin
                    $can_call_api = false; // Prevent API call with bad key
                } else {
                    // Use the decrypted key for API calls
                    $api_key_for_calls = $decrypted_key;
                    unset($decrypted_key); // Clear sensitive data from memory
                }
            } else {
                // Security class is missing - critical failure
                $error_message = sprintf(__('Security component is missing. Cannot fetch Live TLD list for %s. Using default list.', 'electrosuite-reseller'), esc_html($provider_name));
                error_log("ElectroSuite Reseller Settings Error: " . $error_message);
                self::add_error( $error_message ); // Show error to admin
                $can_call_api = false; // Prevent API call
            }
        }
        // If in test mode, or decryption failed, or credentials missing, $api_key_for_calls might be original/irrelevant, but $can_call_api controls flow.
        // --- End Decryption ---


        // --- Call the provider-specific function only if credentials and decryption (if needed) were successful ---
        $final_tld_list = $default_tlds; // Start with default as fallback
        $used_fallback = true; // Assume fallback unless API call succeeds

        if ( $can_call_api ) {
             error_log( "Debug Settings: Calling {$fetch_function} for {$provider_name} to fetch TLDs..." );
             // Pass the ready-to-use plaintext key
             $fetched_tlds = call_user_func( $fetch_function, $username, $api_key_for_calls, $is_test_mode );

             // --- Process result ---
             if ( is_wp_error( $fetched_tlds ) ) {
                 error_log( "Error fetching {$provider_name} TLD list via API: " . $fetched_tlds->get_error_message() . ". Using default TLDs." );
                 self::add_error( sprintf( __( 'Error fetching TLDs from %1$s: %2$s Using default list.', 'electrosuite-reseller' ), esc_html($provider_name), esc_html($fetched_tlds->get_error_message()) ) );
                 // Keep $final_tld_list as $default_tlds
             } elseif ( is_array( $fetched_tlds ) ) {
                 if ( empty( $fetched_tlds ) ) {
                     error_log( "Warning: {$provider_name} TLD list API returned empty array. Using default TLDs." );
                     self::add_error( sprintf( __( '%s API returned no TLDs. Using default list.', 'electrosuite-reseller' ), esc_html($provider_name) ) );
                     // Keep $final_tld_list as $default_tlds
                 } else {
                     $final_tld_list = $fetched_tlds; // Use the successfully fetched list
                     $used_fallback = false; // Mark that we used the live list
                     error_log( "Debug Settings: Successfully fetched " . count($final_tld_list) . " TLDs via API for {$provider_name}." );
                 }
             } else {
                 // Unexpected return type from fetch function
                 error_log( "Error: {$fetch_function} returned unexpected type: " . gettype($fetched_tlds) . ". Using default TLDs." );
                 self::add_error( sprintf(__( 'Received an unexpected result from the %s API. Using default list.', 'electrosuite-reseller'), esc_html($provider_name)) );
                 // Keep $final_tld_list as $default_tlds
             }
        } else {
             // API call was prevented due to missing credentials or decryption failure
             error_log("ElectroSuite Reseller Settings Info: API call to {$fetch_function} skipped due to prior error (credentials/decryption). Using default TLD list.");
             // Keep $final_tld_list as $default_tlds and $used_fallback as true
        }

        // --- Cache the result (fetched or fallback) ---
        // Cache fallback results for a shorter duration
        $cache_duration = $used_fallback ? ( MINUTE_IN_SECONDS * 5 ) : DAY_IN_SECONDS;
        set_transient( $transient_key, $final_tld_list, $cache_duration );

        return $final_tld_list; // Return either fetched list or default/fallback list
    }


    /**
     * Get the checkbox grid field definition for TLDs.
     * Corrected to use $config['provider_name'] instead of undefined $provider_key.
     *
     * @param array $config Provider configuration.
     * @param array|WP_Error $available_tlds Array of available TLDs or WP_Error if fetch failed.
     * @return array The settings field definition for the grid or an error message definition.
     */
    protected function get_tld_grid_field( $config, $available_tlds ) {
    // --- Basic config check (as before) ---
    if ( empty( $config ) || empty($config['selected_tlds_key']) || !isset($config['default_tlds']) ) { // Added check for default_tlds
        return array(
            'type' => 'title',
            'desc' => '<p style="color:red;">TLD configuration key missing.</p>',
            'id'   => 'tld_config_error_' . uniqid()
        );
    }

    // --- Get provider name, keys, classes (as before) ---
    $provider_name = isset($config['provider_name']) ? $config['provider_name'] : __('Provider', 'electrosuite-reseller');
    $selected_tlds_key = $config['selected_tlds_key'];
    $grid_class = sanitize_title($provider_name) . '-tld-grid';
    $default_tlds = $config['default_tlds']; // <<< Get the default TLD list

    // --- Title and Description text (as before) ---
    $field_title_text = sprintf( __( '%s TLDs to Sell', 'electrosuite-reseller' ), esc_html($provider_name) );
    $field_desc_text = sprintf( __( 'Select the TLDs you want searchable on the frontend. Remember to click Save at the bottom of the page.', 'electrosuite-reseller' ), esc_html($provider_name) ); // Updated desc slightly

    // --- Handle potential error or empty list from fetch_available_tlds (as before) ---
    // The fetch function now returns defaults on error/empty, so we might not see WP_Error as often here,
    // but the check remains good practice. The $available_tlds received here *should* now always be an array (possibly the default one).
    if ( is_wp_error( $available_tlds ) ) {
            // This path is less likely now fetch_available_tlds returns defaults, but keep for robustness
            $error_message = sprintf( __( 'Could not load available TLDs from %1$s. Error: %2$s', 'electrosuite-reseller' ), esc_html($provider_name), esc_html($available_tlds->get_error_message()) );
            return array( /* ... error display ... */ );
    }
        if ( !is_array($available_tlds) ) {
            // Should not happen if fetch_available_tlds works correctly
            error_log("get_tld_grid_field: Unexpected non-array for available_tlds after fetch. Type: ".gettype($available_tlds));
            return array( /* ... maybe fallback error display ... */);
        }
        // Note: The check for an empty array might now only occur if the default/fallback list itself is empty.
        if ( empty($available_tlds) ) {
            $error_message = sprintf( __( 'No TLDs (including defaults) are available for %s.', 'electrosuite-reseller' ), esc_html($provider_name) );
            return array( /* ... info/error display ... */ );
        }


        // --- Proceed with grid definition (as before) ---
        sort( $available_tlds );
        $selected_tlds_array = get_option( $selected_tlds_key, [] );
        if ( ! is_array( $selected_tlds_array ) ) $selected_tlds_array = [];
        $tld_options = array_combine( $available_tlds, $available_tlds );


        // --- Return the field definition (MODIFIED) ---
        return array(
             'title'             => $field_title_text,
             'desc'              => $field_desc_text,
             'id'                => $selected_tlds_key,
             'type'              => 'checkbox_grid',
             'class'             => $grid_class,
             'item_prefix'       => '.',
             'options'           => $tld_options,
             'default'           => $selected_tlds_array,
             'default_tlds_list' => $default_tlds, // <<< ADDED: Pass the default list
         );
    }

    // --- END HELPER METHODS ---


    /**
     * Get settings array for the API Tab. (Simplified)
     * Determines section key (defaulting to 'enom'), fetches config, and calls helpers.
     * Avoids hardcoded string comparisons for sections within this method.
     *
     * @param string $current_section The section identifier passed by the framework ('', 'resellerclub', 'centralnic').
     * @return array Array of settings fields.
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();

        // --- Determine the configuration key to use ---
        // Map the default empty section identifier '' to the actual config key 'enom'.
        // Assumes 'enom', 'resellerclub', 'centralnic' are the keys used in get_provider_config.
        $config_key = ( $current_section === '' ) ? 'enom' : $current_section;
        // --- End Determination ---


        // --- Fetch Configuration ---
        $config = $this->get_provider_config( $config_key );
        if ( empty( $config ) ) {
             // Handle invalid/unknown section key after default mapping
             return array( array(
                 'type' => 'title',
                 'desc' => sprintf(__('Settings are not available for the requested API provider section: %s', 'electrosuite-reseller'), esc_html($config_key))
             ));
        }
        // --- End Fetch Configuration ---


        $provider_name = $config['provider_name'];

        // --- Assemble Settings Array using Helpers ---
        // 1. Section Title
        $settings[] = array(
            'title' => sprintf( __( '%s API Settings', 'electrosuite-reseller' ), esc_html($provider_name) ),
            'type'  => 'title',
            'id'    => $config_key . '_api_options' // Use config key for unique ID
        );

        // 2. Credential Fields (Helper uses $config)
        $settings = array_merge( $settings, $this->get_credential_fields( $config ) );
        /*
        // 3. TLD Selection Title
        $settings[] = array(
            'title' => sprintf( __( 'TLDs to Check (%s)', 'electrosuite-reseller' ), esc_html($provider_name) ),
            'desc'  => sprintf( __( 'Select the TLDs you want the domain availability check to query via the %s API. List loaded from the provider.', 'electrosuite-reseller' ), esc_html($provider_name) ),
            'id'    => $config_key . '_tld_selection_title', // Use config key for unique ID
            'type'  => 'title'
        );
        */
        // 4. Fetch TLDs (Helper uses $config)
        $available_tlds = $this->fetch_available_tlds( $config );

        
        // 5. Add Grid Field or Error Message (Helper uses $config and $available_tlds)
        $settings[] = $this->get_tld_grid_field( $config, $available_tlds ); // Pass only necessary args

        // 6. Section End
        $settings[] = array(
            'type' => 'sectionend',
            'id'   => $config_key . '_api_options' // Use config key for unique ID
        );
        // --- End Assembly ---

        // Filter uses the determined config key ('enom', 'resellerclub', etc.)
        return apply_filters( 'electrosuite_reseller_api_tab_settings_section_' . $config_key, $settings );
    } // End get_settings method


} // End class

} // end if class exists

return new ElectroSuite_Reseller_Settings_APIs_Tab();

?>