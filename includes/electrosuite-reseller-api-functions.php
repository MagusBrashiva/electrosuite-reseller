<?php
/**
 * ElectroSuite Reseller API Functions
 *
 * General API functions available on both the front-end and admin.
 *
 * @author      ElectroSuite
 * @category    Core
 * @package     ElectroSuite Reseller/Functions
 * @version     0.0.1
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Register the REST API route for domain checks.
 * Includes permission checks for SSL, Nonce, and Rate Limiting.
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'domain-search/v1', '/check', array(
        'methods' => 'POST',
        'callback' => 'handle_electrosuite_domain_search_request',
        'permission_callback' => function ( WP_REST_Request $request ) {

            // --- NEW: 1. SSL Check (Add this first) ---
            if ( ! is_ssl() ) {
                error_log("ElectroSuite Reseller REST API Error: Domain search request blocked over non-HTTPS connection.");
                return new WP_Error(
                    'rest_ssl_required',
                    __( 'SSL (HTTPS) is required for domain searches.', 'electrosuite-reseller' ),
                    array( 'status' => 400 ) // 400 Bad Request as the request doesn't meet security requirements
                );
            }
            // --- End NEW SSL Check ---


            // --- Existing: 2. Nonce Check (Was 1) ---
            $nonce = $request->get_header('X-WP-Nonce');
            if ( !$nonce ) {
                // Fallback to checking POST/GET data for _wpnonce if header not present
                 $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( $_REQUEST['_wpnonce'] ) : '';
            }
             if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                 error_log("ElectroSuite Reseller REST API Error: Nonce verification failed.");
                 return new WP_Error(
                     'rest_cookie_invalid', // Keep WP standard code for nonce failure
                     __( 'Nonce check failed. Please refresh the page and try again.', 'electrosuite-reseller' ),
                     array( 'status' => 403 ) // 403 Forbidden is standard for auth/nonce failures
                 );
            }
            // --- End Nonce Check ---


            // --- Existing: 3. Rate Limit Check (Was 2) ---
            // Assuming check_rate_limit function exists and is defined elsewhere
            if ( function_exists('check_rate_limit') ) {
                $limit_check = check_rate_limit('domain_search', 15, 60); // Example: 15 requests per 60 seconds
                if (is_wp_error($limit_check)) {
                    // If rate limit returns an error, return it directly
                    // Ensure the error from check_rate_limit includes a status code (e.g., 429)
                    return $limit_check;
                }
                // If $limit_check was true, proceed
            } else {
                 // Optional: Log if rate limit function is missing but don't block necessarily
                 error_log("ElectroSuite Reseller REST API Warning: Rate limit function 'check_rate_limit' not found.");
            }
            // --- End Rate Limit Check ---


            // --- Existing: 4. Other Checks (Was 3) ---
            // Add any other permission checks if needed (e.g., user capabilities)


            // If all checks pass
            return true;
        }, // End permission_callback
        'args' => array(
            'domain' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($param, $request, $key) {
                    // Keep existing validation logic for the domain parameter
                    $domain_input = trim($param);
                    if ( empty($domain_input) ) {
                        return new WP_Error( 'rest_invalid_param', esc_html__( 'Domain name cannot be empty.', 'electrosuite-reseller' ), array( 'status' => 400 ) );
                    }
                    // Basic check for forbidden characters (anything not letter, digit, hyphen, dot)
                    // Allows for IDNs potentially, detailed check happens in extract_sld_tld
                    if (preg_match('/[^a-z0-9\-\.]/i', $domain_input)) {
                         return new WP_Error( 'rest_invalid_param', esc_html__( 'Domain name contains invalid characters.', 'electrosuite-reseller' ), array( 'status' => 400 ) );
                    }
                    // Check for starting/ending dot or hyphen which is always invalid
                    if (str_starts_with($domain_input, '.') || str_ends_with($domain_input, '.') || str_starts_with($domain_input, '-') || str_ends_with($domain_input, '-')) {
                         return new WP_Error( 'rest_invalid_param', esc_html__( 'Domain name cannot start or end with dot or hyphen.', 'electrosuite-reseller' ), array( 'status' => 400 ) );
                    }
                    // Further specific SLD/TLD validation is handled inside handle_request via extract_sld_tld
                    return true;
                }
            ),
            // Include nonce in args if accepting it via query/body instead of header
            /*
            '_wpnonce' => array(
                'description' => __('WordPress nonce.', 'electrosuite-reseller'),
                'type' => 'string',
                'required' => false, // Nonce might be in header
            ),
            */
        ),

    ) );
} ); // End add_action 'rest_api_init'




/**
 * Fetches the list of TLDs available for registration via the eNom API.
 * Parses the nested XML structure returned by GetTLDList.
 *
 * @param string $username    eNom Username.
 * @param string $api_key     eNom API Key (Live or Test).
 * @param bool   $is_test_mode Whether to use the test API endpoint.
 * @return array|WP_Error Array of TLD strings on success, WP_Error on failure.
 */
function get_enom_tld_list( $username, $api_key, $is_test_mode ) {
    error_log("--- get_enom_tld_list: Fetching from API ---"); // Log fetch attempt

    // Define URLs
    $live_url = 'https://reseller.enom.com/interface.asp';
    $test_url = 'https://resellertest.enom.com/interface.asp';
    $base_url = $is_test_mode ? $test_url : $live_url;

    // Construct API URL (Verify Command Name - Assuming GetTLDList)
    $api_url = add_query_arg( array(
        'command' => 'GetTLDList', // Assuming this is correct
        'uid' => $username,
        'pw' => $api_key,
        'responsetype' => 'XML', // We know XML works
    ), $base_url );

    $response = wp_remote_get( $api_url, ['timeout' => 20] );

    // Handle WP HTTP Errors
    if ( is_wp_error( $response ) ) {
        error_log("ElectroSuite Reseller eNom API Error: wp_remote_get failed (GetTLDList). Error: " . $response->get_error_message());
        return new WP_Error('http_error_tldlist', __('Could not connect to registrar to get TLD list.', 'electrosuite-reseller'), ['status' => 503]);
    }

    $http_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    // Handle non-200 HTTP status or empty body
    if ( $http_code !== 200 || empty($body) ) {
        $error_message = __('eNom API communication error (GetTLDList).', 'electrosuite-reseller');
        $log_message = "ElectroSuite Reseller eNom API Error: Critical error processing GetTLDList response.";
        if ($http_code !== 200) { $log_message .= " HTTP status {$http_code}."; }
        if (empty($body)) { $log_message .= " Empty response body.";}
        error_log($log_message . " Raw Body Length: " . strlen($body));
        return new WP_Error( 'enom_api_tldlist_critical_error', $error_message, array( 'status' => 502 ) );
    }

    // Parse XML Response
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body);

    if ($xml === false) {
        // ... handle XML parse error ...
        return new WP_Error('xml_parse_error_tldlist', __('Failed to understand TLD list from registrar.', 'electrosuite-reseller'), ['status' => 502]);
    }

    // Determine root node
    $response_node = isset($xml->{'interface-response'}) ? $xml->{'interface-response'} : $xml;

    // Check for eNom application-level errors
    if (isset($response_node->ErrCount) && intval((string)$response_node->ErrCount) > 0) {
        // ... handle app error, return WP_Error ...
        return new WP_Error('enom_api_tldlist_app_error', /* ... error message ... */ );
    }

    // --- Extract TLD list from the NESTED structure ---
    $tlds = [];
    // Check if the expected path exists
    if (isset($response_node->tldlist->tld)) {
        // Loop through each outer <tld> node
        foreach ($response_node->tldlist->tld as $outer_tld_node) {
            // Access the INNER <tld> node's value
            $tld = strtolower((string)$outer_tld_node->tld); // Access nested tag
            if (!empty($tld)) {
                $tlds[] = $tld;
            }
        }
    } else {
        error_log("eNom GetTLDList Error: Could not find <tldlist><tld> structure in response. Body Length: " . strlen($body));
        // Consider returning error or empty array? Return error for now.
        return new WP_Error('tldlist_structure', __('Could not parse TLD list structure from registrar.', 'electrosuite-reseller'));
    }

    if (empty($tlds)) {
         error_log("eNom GetTLDList Warning: API call successful but parsed an empty TLD list.");
    } else {
         error_log("DEBUG get_enom_tld_list: Successfully parsed " . count($tlds) . " TLDs.");
    }

    return $tlds; // Return array of TLD strings
}


/**
 * Fetches the list of TLDs available for registration via the ResellerClub API.
 * Parses the nested XML structure returned by GetTLDList.
 *
 * @param string $username    ResellerClub Username.
 * @param string $api_key     ResellerClub API Key (Live or Test).
 * @param bool   $is_test_mode Whether to use the test API endpoint.
 * @return array|WP_Error Array of TLD strings on success, WP_Error on failure.
 */
function get_resellerclub_tld_list( $username, $api_key, $is_test_mode ) {
}


/**
 * Fetches the list of TLDs available for registration via the CentralNic API.
 * Parses the nested XML structure returned by GetTLDList.
 *
 * @param string $username    CentralNic Username.
 * @param string $api_key     CentralNic API Key (Live or Test).
 * @param bool   $is_test_mode Whether to use the test API endpoint.
 * @return array|WP_Error Array of TLD strings on success, WP_Error on failure.
 */
function get_centralnic_tld_list( $username, $api_key, $is_test_mode ) {
}


/**
 * Extracts and validates the SLD and TLD from a domain name based on eNom rules.
 * Returns an array ['sld' => ..., 'tld' => ...] or false on failure.
 */
function extract_sld_tld($domain_name) {
    $domain_name = strtolower(trim($domain_name));

    // Basic structure check
    if (strpos($domain_name, '.') === false) return false; // Need at least one dot
    if (preg_match('/\s/', $domain_name)) return false;   // No spaces allowed

    $parts = explode('.', $domain_name);
    if (count($parts) < 2) return false;

    $tld = array_pop($parts);
    $sld = implode('.', $parts); // Handles potential multiple dots in SLD (less common)

    // Validate TLD (basic) - eNom validation focuses on SLD
    if (empty($tld) || strlen($tld) < 2) { // TLDs are usually at least 2 chars
         error_log("Validation Error: Invalid TLD extracted: " . $tld);
         return false;
    }

    // Validate SLD based on eNom Rules
    if (empty($sld)) {
         error_log("Validation Error: Empty SLD extracted.");
         return false;
    }
    // Rule: a-z, 0-9, hyphen ONLY
    if (!preg_match('/^[a-z0-9-]+$/', $sld)) {
         error_log("Validation Error: SLD '{$sld}' contains invalid characters.");
         return false;
    }
    // Rule: Not begin or end with hyphen
    if (str_starts_with($sld, '-') || str_ends_with($sld, '-')) { // PHP 8+ needed for str_starts/ends_with
    // if (substr($sld, 0, 1) === '-' || substr($sld, -1) === '-') { // PHP < 8 compatibility
         error_log("Validation Error: SLD '{$sld}' starts or ends with hyphen.");
         return false;
    }
    // Rule: 3rd and 4th chars not both hyphens (unless IDN - skip IDN check for now)
    if (strlen($sld) >= 4 && substr($sld, 2, 2) === '--') {
        // Basic check - Doesn't account for valid Punycode IDNs starting xn--
        if (!str_starts_with($sld, 'xn--')) {
        // if (substr($sld, 0, 4) !== 'xn--') { // PHP < 8 compatibility
             error_log("Validation Error: SLD '{$sld}' has hyphens at 3rd/4th position (non-IDN).");
             return false;
        }
    }
    // Rule: 2-63 characters (Note: some sources say 1 character is allowed, but eNom says 2)
    if (strlen($sld) < 2 || strlen($sld) > 63) {
         error_log("Validation Error: SLD '{$sld}' length (" . strlen($sld) . ") is outside 2-63 chars.");
         return false;
    }

    // If all checks pass
    return ['sld' => $sld, 'tld' => $tld];
}



/**
 * Checks and enforces basic rate limiting based on IP address using Transients.
 *
 * @param string $limit_key A unique key for this specific limit type (e.g., 'domain_search').
 * @param int    $limit     Maximum number of allowed requests.
 * @param int    $period    Time period in seconds.
 * @return bool|WP_Error True if request is allowed, WP_Error if limit exceeded.
 */
function check_rate_limit($limit_key = 'domain_search', $limit = 10, $period = 60) {
    // Try to get user's IP address reliably
    $ip_address = '';
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    }

    // Sanitize IP just in case, though usually handled by server vars
    $ip_address = preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip_address ); // Allow IPv4, IPv6, commas, spaces (X-Forwarded-For)
    // Take only the first IP if multiple are present
     if (strpos($ip_address, ',') !== false) {
        $ip_address = explode(',', $ip_address)[0];
     }
     $ip_address = trim($ip_address);


    if (empty($ip_address) || $ip_address === 'unknown') {
        // Cannot determine IP, maybe allow request but log warning? Or block? Let's allow for now.
        error_log("Rate Limit Warning: Could not determine IP address for key '{$limit_key}'. Allowing request.");
        return true;
    }

    // Create a unique transient key combining the limit key and the IP address
    $transient_key = 'rate_limit_' . $limit_key . '_' . md5($ip_address);

    // Get the current count for this IP from the transient
    $current_count = get_transient( $transient_key );

    if ( false === $current_count ) {
        // Transient doesn't exist or expired, start count at 1
        set_transient( $transient_key, 1, $period );
        return true; // Allow first request
    } elseif ( intval($current_count) < $limit ) {
        // Count is below limit, increment it
        set_transient( $transient_key, intval($current_count) + 1, $period );
        return true; // Allow request
    } else {
        // Limit exceeded
        // Optionally log the blocked IP/key
        // error_log("Rate Limit Exceeded for key '{$limit_key}', IP: {$ip_address}");
        // Return WP_Error with 429 status code
        return new WP_Error(
            'rest_rate_limit_exceeded',
            __( 'Too many requests. Please wait a minute and try again.', 'electrosuite-reseller' ),
            array( 'status' => 429 ) // HTTP 429 Too Many Requests
        );
    }
}



/**
 * Handle the domain search REST API request (Multi-TLD Avail + Single TLD Price).
 * Retrieves credentials, decrypts Live API key, calls eNom functions, applies markup.
 *
 * @param WP_REST_Request $request The incoming request object.
 * @return WP_REST_Response|WP_Error A response object on success, or WP_Error on failure.
 */
function handle_electrosuite_domain_search_request( WP_REST_Request $request ) {
    $domain_input = $request['domain']; // Already sanitized by 'sanitize_callback' and validated by 'validate_callback' in register_rest_route

    // --- Extract SLD ---
    $sld = null; // Initialize $sld
    // Use helper function to extract SLD and TLD if possible
    $domain_parts = extract_sld_tld($domain_input);

    // Handle cases:
    // 1. Input is 'example.com' -> extract_sld_tld returns ['sld'=>'example', 'tld'=>'com']
    // 2. Input is just 'example' -> extract_sld_tld returns false, but we can use the input as SLD
    // 3. Input is invalid (e.g., '.example', 'example.') -> extract_sld_tld returns false, handled below
    if ($domain_parts !== false) {
        $sld = $domain_parts['sld']; // Use SLD from parsed input
    } elseif (strpos($domain_input, '.') === false && !empty($domain_input)) {
        // If no dot and not empty, assume input was just the SLD.
        // Perform basic SLD validation similar to extract_sld_tld's checks
        $temp_sld = strtolower(trim($domain_input));
        if (
            !preg_match('/^[a-z0-9-]+$/', $temp_sld) || // Rule: a-z, 0-9, hyphen ONLY
            str_starts_with($temp_sld, '-') || str_ends_with($temp_sld, '-') || // Rule: Not begin or end with hyphen
            (strlen($temp_sld) >= 4 && substr($temp_sld, 2, 2) === '--' && !str_starts_with($temp_sld, 'xn--')) || // Rule: 3rd/4th not '--' (basic non-IDN check)
            strlen($temp_sld) < 2 || strlen($temp_sld) > 63 // Rule: 2-63 characters
         ) {
             // Invalid SLD format even if no TLD was provided
             // Keep $sld as null, will trigger error below
             error_log("Domain Search Handler: Invalid SLD format provided directly: " . $domain_input);
         } else {
             $sld = $temp_sld; // Assume input was a valid SLD
         }
    }

    // Check if SLD extraction/validation was successful
    if ( $sld === null ) {
         // Return error based on more specific validation failure if possible (from validate_callback or above checks)
         // For simplicity here, return a general error.
         return new WP_Error('invalid_sld_or_domain', __('Invalid domain name or SLD provided.', 'electrosuite-reseller'), ['status' => 400]);
    }

    // --- Determine TLDs to Check ---
    // Get selected TLDs based on the *active* provider. Default to eNom if provider setting missing.
    $active_api_provider = get_option( 'electrosuite_reseller_server_api', 'enom' );
    $tld_option_key = 'electrosuite_reseller_' . $active_api_provider . '_checked_tlds';
    // Provide a sensible default TLD list if the option is empty or not set
    $default_tlds = ['com', 'net', 'org'];
    $selected_tlds = get_option($tld_option_key, $default_tlds);
    // Ensure $selected_tlds is a non-empty array
    $tlds_to_check = (is_array($selected_tlds) && !empty($selected_tlds)) ? $selected_tlds : $default_tlds;


    // --- Get Credentials & Settings for the Active Provider ---
    // For now, we only handle eNom completely. Add logic for other providers later.
    if ($active_api_provider !== 'enom') {
        // TODO: Implement logic for ResellerClub, CentralNic
        return new WP_Error('provider_not_supported', __('Domain check only implemented for eNom currently.', 'electrosuite-reseller'), ['status' => 501]);
    }

    $is_test_mode = ( get_option( 'electrosuite_reseller_test_mode', 'no' ) === 'yes' );
    $username = get_option( 'electrosuite_reseller_enom_api_username' );
    $option_key_name = $is_test_mode ? 'electrosuite_reseller_enom_test_api_key' : 'electrosuite_reseller_enom_live_api_key';
    $retrieved_api_key = get_option( $option_key_name ); // This is plaintext test key OR encrypted live key
    $key_type_for_error = $is_test_mode ? 'Test' : 'Live';


    // --- Validate Base Credentials Exist ---
    if ( empty( $username ) || empty( $retrieved_api_key ) ) {
         $error_detail = empty($username) ? __('Username missing.', 'electrosuite-reseller') : sprintf(__('%s API key missing.', 'electrosuite-reseller'), $key_type_for_error);
         $error_message = sprintf( __( 'API credentials (%1$s) for %2$s are not configured.', 'electrosuite-reseller' ), $error_detail, 'eNom' );
         error_log("ElectroSuite Reseller API Error: " . $error_message);
         return new WP_Error('config_error_credentials', $error_message, array( 'status' => 500 ));
    }


    // --- Decrypt Live API Key If Necessary ---
    $api_key_for_calls = $retrieved_api_key; // Initialize with the retrieved value

    if ( false === $is_test_mode ) { // Only decrypt if in Live mode
        if ( class_exists('ElectroSuite_Reseller_Security') ) {
            $decrypted_key = ElectroSuite_Reseller_Security::decrypt( $retrieved_api_key );

            if ( false === $decrypted_key ) {
                // Decryption failed
                $error_message = __('Could not use the Live API key for eNom. Decryption failed. Check WP salts and saved key.', 'electrosuite-reseller');
                error_log("ElectroSuite Reseller API Error: " . $error_message);
                // Return 500 Internal Server Error as it's a server config issue
                return new WP_Error('enom_live_key_decrypt_failed_handler', $error_message, ['status' => 500]);
            }
            // Use the decrypted key for API calls
            $api_key_for_calls = $decrypted_key;
            unset($decrypted_key); // Clear sensitive data from memory

        } else {
            // Security class is missing - critical failure
            $error_message = __('Security component is missing. Cannot process Live API request for eNom.', 'electrosuite-reseller');
            error_log("ElectroSuite Reseller API Error: " . $error_message);
            return new WP_Error('enom_security_class_missing_handler', $error_message, ['status' => 500]);
        }
    }
    // If in test mode, $api_key_for_calls remains the plaintext test key.
    // --- End Decryption ---


    // --- Get Pricing Adjustment Settings ---
    $price_mode = get_option('electrosuite_reseller_price_mode', 'percentage');
    $price_value_raw = get_option('electrosuite_reseller_price_value', '0'); // Default to 0
    $price_value = is_numeric($price_value_raw) ? floatval($price_value_raw) : 0.0;


    // --- STEP A: Call Multi-TLD Availability Check ---
    // Pass the ready-to-use key ($api_key_for_calls)
    $availability_results = call_enom_check_v2_availability($username, $api_key_for_calls, $sld, $tlds_to_check, $is_test_mode);

    // Handle potential errors from the availability check immediately
    if (is_wp_error($availability_results)) {
        error_log("API Error during multi-TLD availability check: " . $availability_results->get_error_message());
        // Ensure the error has a status code before returning
        $status = $availability_results->get_error_data();
        if ( !is_array($status) || !isset($status['status']) ) {
             $availability_results->add_data(['status' => 502]); // Default to 502 Bad Gateway if status missing
        }
        return $availability_results; // Return the WP_Error object
    }


    // --- STEP B: Loop through requested TLDs & Get Prices for AVAILABLE ones ---
    $results_list = [];
    foreach ($tlds_to_check as $tld) {
        $current_domain = $sld . '.' . $tld;
        $result_item = [
            'domain' => $current_domain,
            'available' => 'error', // Default state if not found in results
            'adjusted_price' => null,
            'message' => __('Status could not be determined.', 'electrosuite-reseller')
        ];

        // Check availability status from the first API call's results array
        if (isset($availability_results[$tld])) {
             // Check if the result for this TLD indicates an error from the availability call itself
             if ( $availability_results[$tld]['available'] === 'error' ) {
                  $result_item['available'] = 'error';
                  $result_item['message'] = isset($availability_results[$tld]['message']) ? $availability_results[$tld]['message'] : __('Status not returned by registrar.', 'electrosuite-reseller');
                  error_log("API Availability Error for {$current_domain}: Status was 'error'. Message: " . $result_item['message']);
             } else {
                 // Availability is either true or false
                 $is_available = $availability_results[$tld]['available']; // Should be bool true or false
                 $result_item['available'] = $is_available;
                 unset($result_item['message']); // Clear default error message

                 // If it's available, attempt to get the price and apply markup
                 if ($is_available === true) {
                      // Pass the ready-to-use key ($api_key_for_calls)
                      $price_result = call_enom_check_single_v2_price($username, $api_key_for_calls, $sld, $tld, $is_test_mode);

                      if (is_wp_error($price_result)) {
                           // Log the error from the price check, but still return the TLD as available
                           error_log("API Price Warning: Failed to get price for available domain {$current_domain}: " . $price_result->get_error_message());
                           $result_item['adjusted_price'] = 'Error'; // Indicate price retrieval error specifically
                           $result_item['message'] = __('Could not retrieve price.', 'electrosuite-reseller');
                      } elseif (is_array($price_result) && isset($price_result['cost']) && is_numeric($price_result['cost']) && $price_result['cost'] >= 0) {
                           // Got a valid base cost, apply markup/adjustment
                           $base_cost = floatval($price_result['cost']);
                           $adjusted_price = $base_cost; // Start with base cost

                           if ($price_mode === 'fixed') {
                               $adjusted_price += $price_value; // Add fixed amount
                           } elseif ($price_mode === 'percentage' && $price_value != 0) {
                               $markup_amount = $base_cost * ($price_value / 100.0); // Calculate percentage markup based on cost
                               $adjusted_price += $markup_amount; // Add markup
                           }
                           // Ensure price is not negative, format to 2 decimal places
                           $result_item['adjusted_price'] = number_format(max(0, $adjusted_price), 2, '.', '');
                      } else {
                           // Price call succeeded (not WP_Error) but didn't return a valid numeric cost
                           error_log("API Price Warning: No valid cost price returned by single check for available domain {$current_domain}. Price Result: " . print_r($price_result, true));
                           $result_item['adjusted_price'] = 'N/A'; // Indicate price not available from registrar
                           $result_item['message'] = __('Price not available.', 'electrosuite-reseller');
                      }
                 } // End if ($is_available === true) - No price needed if not available
             }

        } else {
             // TLD wasn't found in the availability results array (should not happen if checks in call_enom_check_v2_availability work)
             error_log("API Availability Warning: TLD {$tld} missing from multi-check response array for SLD {$sld}.");
             // Keep default error state from initialization
             $result_item['message'] = __('Status not returned by registrar.', 'electrosuite-reseller');
        }
        $results_list[] = $result_item;
    } // End TLD loop

    // --- Return Final Results ---
    return new WP_REST_Response( $results_list, 200 );
}




// Function for multi-TLD availability check ONLY

/**
 * Checks domain availability for multiple TLDs using eNom Check API V2 (XML).
 * Does NOT reliably return pricing from test server.
 *
 * @param string $username    eNom Username.
 * @param string $api_key     eNom API Key (Live or Test).
 * @param string $sld         The Second-Level Domain to check.
 * @param array  $tld_array   An array of TLDs to check.
 * @param bool   $is_test_mode Whether to use the test API endpoint.
 * @return array|WP_Error Associative array keyed by TLD ['tld' => ['available' => bool]] or WP_Error.
 */
function call_enom_check_v2_availability( $username, $api_key, $sld, $tld_array, $is_test_mode ) {
    // Input validation
    if (empty($sld) || empty($tld_array) || !is_array($tld_array)) {
        return new WP_Error('invalid_input', __('Invalid SLD or TLD list provided to availability check.', 'electrosuite-reseller'));
    }

    // Define eNom URLs
    $live_url = 'https://reseller.enom.com/interface.asp';
    $test_url = 'https://resellertest.enom.com/interface.asp';
    $base_url = $is_test_mode ? $test_url : $live_url;

    // Construct API URL with V2, TLDList, and XML ResponseType
    $tld_list_string = implode(',', $tld_array);
    $api_url = add_query_arg( array(
        'command' => 'Check',
        'Version' => '2',
        'uid' => $username,
        'pw' => $api_key,
        'SLD' => $sld,
        'TLDList' => $tld_list_string,
        'responsetype' => 'XML',
    ), $base_url );

    // Make request
    $response = wp_remote_get( $api_url, ['timeout' => 20] );

    // Check for WordPress HTTP API errors
    if ( is_wp_error( $response ) ) {
         error_log("ElectroSuite Reseller eNom API Error: wp_remote_get failed (Avail Check). Error: " . $response->get_error_message());
         return new WP_Error('http_error', __('Could not connect to the domain registrar (availability check).', 'electrosuite-reseller'), ['status' => 503]);
    }

    $http_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    // Basic HTTP error check
    if ( $http_code !== 200 || empty($body) ) {
        $error_message = __('eNom API communication error (Avail Check).', 'electrosuite-reseller');
        $log_message = "ElectroSuite Reseller eNom API Error: Critical error processing Avail Check response.";
        if ($http_code !== 200) { $log_message .= " HTTP status {$http_code}."; }
        if (empty($body)) { $log_message .= " Empty response body.";}
        error_log($log_message . " Raw Body Length: " . strlen($body));
        return new WP_Error( 'enom_api_avail_critical_error', $error_message, array( 'status' => 502 ) );
    }

    // --- Parse XML Response ---
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body);

    if ($xml === false) {
         $xml_errors = libxml_get_errors();
         $error_detail = !empty($xml_errors) ? $xml_errors[0]->message : 'Unknown XML parse error.';
         libxml_clear_errors();
         error_log("ElectroSuite Reseller eNom API Error: Failed to parse XML (Avail Check). Error: {$error_detail}. Raw Body Length: " . strlen($body));
         return new WP_Error('xml_parse_error', __('Failed to understand availability response from registrar.', 'electrosuite-reseller'), ['status' => 502]);
    }

    // Determine root node
    $response_node = isset($xml->{'interface-response'}) ? $xml->{'interface-response'} : $xml;

    // Check for eNom application-level errors
    if (isset($response_node->ErrCount) && intval((string)$response_node->ErrCount) > 0) {
        $error_message = __('eNom API Error', 'electrosuite-reseller');
        $log_message = "ElectroSuite Reseller eNom API Error: Application error reported by eNom (Avail Check).";
        $specific_error = isset($response_node->errors->Err1) ? (string)$response_node->errors->Err1 : (isset($response_node->responses->response->ResponseString) ? (string)$response_node->responses->response->ResponseString : '');

        if (!empty($specific_error)) {
            $log_message .= " Message: {$specific_error}";
            if (stripos($specific_error, 'password') !== false || stripos($specific_error, 'loginid') !== false) {
                $error_message = __('Authentication failed with domain registrar.', 'electrosuite-reseller');
            } else {
                $error_message = $specific_error;
            }
        } else {
             $error_message = __('An unspecified error occurred with the domain registrar.', 'electrosuite-reseller');
        }
        error_log($log_message);
        return new WP_Error('enom_api_avail_app_error', $error_message, array('status' => 400));
    }

    // --- Extract Availability Only ---
    $results = [];
    $domain_count = isset($response_node->DomainCount) ? intval((string)$response_node->DomainCount) : 0;

    // Check if the required parallel arrays exist and have the same count
    if (
        $domain_count > 0 &&
        isset($response_node->Domain) && is_iterable($response_node->Domain) && // Use is_iterable for SimpleXML nodes
        isset($response_node->RRPCode) && is_iterable($response_node->RRPCode) &&
        isset($response_node->RRPText) && is_iterable($response_node->RRPText) &&
        count($response_node->Domain) === $domain_count &&
        count($response_node->RRPCode) === $domain_count &&
        count($response_node->RRPText) === $domain_count
    ) {
        // Loop through the results using the index
        for ($i = 0; $i < $domain_count; $i++) {
            $domain_name = (string)$response_node->Domain[$i];
            $rrp_code = (string)$response_node->RRPCode[$i];
            // $rrp_text = (string)$response_node->RRPText[$i]; // Not currently used

            // Extract TLD from the domain name
            $domain_parts = explode('.', $domain_name, 2);
            $tld = isset($domain_parts[1]) ? strtolower($domain_parts[1]) : null;

            if ($tld) {
                $results[$tld] = [
                    'available'   => ($rrp_code == '210'), // RRPCode 210 means available
                    // No 'cost' key needed in this function's return
                ];
            } else {
                 error_log("eNom V2 API Warning: Could not extract TLD from returned domain (Avail Check): " . $domain_name);
            }
        } // End For loop
    } elseif (isset($response_node->ErrCount) && intval((string)$response_node->ErrCount) == 0) {
         // Handle case where ErrCount is 0 but expected data arrays are missing or DomainCount is 0
         error_log("eNom V2 Avail Check Warning: ErrCount is 0 but DomainCount is 0 or Domain arrays missing. Raw Body Length: " . strlen($body));
         // Currently does nothing else - returns empty $results implicitly
    } else {
        // Handle case where ErrCount is missing but DomainCount might be 0 or other structure issues
         error_log("eNom V2 Avail Check Warning: Unexpected XML structure or DomainCount=0. Raw Body Length: " . strlen($body));
         // Return error because we couldn't parse availability
         return new WP_Error('enom_xml_structure_avail', __('Unexpected availability response structure from eNom.', 'electrosuite-reseller'));
    }

    return $results; // Return ['tld' => ['available' => bool], ...]
}




/**
 * Checks domain availability and pricing for a SINGLE TLD using eNom Check API V2 (XML).
 *
 * @param string $username    eNom Username.
 * @param string $api_key     eNom API Key (Live or Test).
 * @param string $sld         The Second-Level Domain to check.
 * @param string $tld         The Top-Level Domain to check (e.g., 'com').
 * @param bool   $is_test_mode Whether to use the test API endpoint.
 * @return array|WP_Error ['available' => bool, 'cost' => float|null] on success, WP_Error on failure.
 */
function call_enom_check_single_v2_price( $username, $api_key, $sld, $tld, $is_test_mode ) {
    if (empty($sld) || empty($tld)) {
        return new WP_Error('invalid_input', __('Invalid SLD or TLD provided to eNom single check function.', 'electrosuite-reseller'));
    }

    // Define eNom URLs
    $live_url = 'https://reseller.enom.com/interface.asp';
    $test_url = 'https://resellertest.enom.com/interface.asp';
    $base_url = $is_test_mode ? $test_url : $live_url;

    // Construct API URL for single TLD check, V2, IncludePrice, XML response
    $api_url = add_query_arg( array(
        'command' => 'Check',
        'Version' => '2',
        'IncludePrice' => '1',
        'uid' => $username,
        'pw' => $api_key,
        'SLD' => $sld,
        'TLD' => $tld, // Single TLD
        'responsetype' => 'XML', // Request XML
    ), $base_url );

    // Make the request
    $response = wp_remote_get( $api_url, ['timeout' => 15] );

    // WP HTTP Error check
    if ( is_wp_error( $response ) ) {
         error_log("ElectroSuite Reseller eNom API Error: wp_remote_get failed (Single Price Check) for {$sld}.{$tld}. Error: " . $response->get_error_message());
         return new WP_Error('http_error', __('Could not connect to the domain registrar (price check).', 'electrosuite-reseller'), ['status' => 503]);
    }

    $http_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    // Basic HTTP error check
    if ( $http_code !== 200 || empty($body) ) {
        $error_message = __('eNom API communication error (Single Price Check).', 'electrosuite-reseller');
        $log_message = "ElectroSuite Reseller eNom API Error: Critical error processing single price response for {$sld}.{$tld}.";
        if ($http_code !== 200) { $log_message .= " HTTP status {$http_code}."; }
        if (empty($body)) { $log_message .= " Empty response body.";}
        error_log($log_message . " Raw Body Length: " . strlen($body));
        return new WP_Error( 'enom_api_v2_critical_error', $error_message, array( 'status' => 502 ) );
    }

    // --- Parse XML Response ---
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body);

    if ($xml === false) {
        $xml_errors = libxml_get_errors();
        $error_detail = !empty($xml_errors) ? $xml_errors[0]->message : 'Unknown XML parse error.';
        libxml_clear_errors();
        error_log("ElectroSuite Reseller eNom API Error: Failed to parse XML (Single Price Check) for {$sld}.{$tld}. Error: {$error_detail}. Raw Body Length: " . strlen($body));
        return new WP_Error('xml_parse_error', __('Failed to understand price response from registrar.', 'electrosuite-reseller'), ['status' => 502]);
    }

    // Determine root node for response data
    $response_node = isset($xml->{'interface-response'}) ? $xml->{'interface-response'} : $xml;

    // Check for eNom application-level errors
    if (isset($response_node->ErrCount) && intval((string)$response_node->ErrCount) > 0) {
        $error_message = __('eNom API Error', 'electrosuite-reseller');
        $log_message = "ElectroSuite Reseller eNom API Error: Application error reported by eNom (Single Price Check) for {$sld}.{$tld}.";
        $specific_error = isset($response_node->errors->Err1) ? (string)$response_node->errors->Err1 : (isset($response_node->responses->response->ResponseString) ? (string)$response_node->responses->response->ResponseString : '');

        if (!empty($specific_error)) {
            $log_message .= " Message: {$specific_error}";
            if (stripos($specific_error, 'password') !== false || stripos($specific_error, 'loginid') !== false) {
                $error_message = __('Authentication failed with domain registrar.', 'electrosuite-reseller');
            } else {
                $error_message = $specific_error;
            }
        } else {
             $error_message = __('An unspecified error occurred with the domain registrar.', 'electrosuite-reseller');
        }
        error_log($log_message);
        return new WP_Error('enom_api_app_error', $error_message, array('status' => 400));
    }

    // --- Extract Data from Successful XML ---
    $result_data = ['available' => false, 'cost' => null]; // Initialize default return structure

    // Check if the expected Domain structure exists
    if (isset($response_node->Domains->Domain)) {
         $domain_node = $response_node->Domains->Domain;

         // Check Availability (RRPCode 210)
         if (isset($domain_node->RRPCode) && (string)$domain_node->RRPCode == '210') {
             $result_data['available'] = true;
         }

         // Extract Registration Price from the specific structure for single domain responses
         if (isset($domain_node->Prices->Registration) && is_numeric((string)$domain_node->Prices->Registration)) {
             $result_data['cost'] = floatval((string)$domain_node->Prices->Registration);
         } elseif ($result_data['available']) {
              error_log("ElectroSuite Reseller eNom API Warning: Registration price missing or invalid in single check XML for available domain {$sld}.{$tld}");
         }
    } else {
         error_log("ElectroSuite Reseller eNom API Error: Unexpected XML structure - Domains->Domain missing in single check for {$sld}.{$tld}. Raw Body Length: " . strlen($body));
         // Return error as we couldn't parse the expected structure
         return new WP_Error('enom_xml_structure_single', __('Unexpected response structure from eNom (single price check).', 'electrosuite-reseller'));
    }

    return $result_data; // Return ['available' => bool, 'cost' => float|null]
}




/**
 * Placeholder for ResellerClub API call.
 */
function call_resellerclub_api( $user_id, $api_key, $domain, $is_test_mode ) {
    error_log("ElectroSuite Reseller API: ResellerClub API call initiated for domain {$domain}. (Not Implemented)");
    return new WP_Error( 'not_implemented', __('ResellerClub integration is not yet available.', 'electrosuite-reseller'), array( 'status' => 501 ) );
}

/**
 * Placeholder for CentralNic API call.
 */
function call_centralnic_api( $username, $api_key, $domain, $is_test_mode ) {
    error_log("ElectroSuite Reseller API: CentralNic API call initiated for domain {$domain}. (Not Implemented)");
     return new WP_Error( 'not_implemented', __('CentralNic integration is not yet available.', 'electrosuite-reseller'), array( 'status' => 501 ) );
}
?>