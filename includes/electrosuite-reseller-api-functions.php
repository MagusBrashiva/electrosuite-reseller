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
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'domain-search/v1', '/check', array(
        'methods' => 'POST',
        'callback' => 'handle_electrosuite_domain_search_request',
        'permission_callback' => function ( WP_REST_Request $request ) {
            // Verify nonce sent in header or POST data
            $nonce = $request->get_header('X-WP-Nonce');
            if ( !$nonce ) {
                 // Fallback to checking POST data if header not present
                 $nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
            }

            if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                 error_log("ElectroSuite Reseller REST API: Nonce verification failed."); // Log failure
                 return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid nonce.', 'electrosuite-reseller' ), array( 'status' => 403 ) );
            }
            // error_log("ElectroSuite Reseller REST API: Nonce verified successfully."); // Optional success log
            return true;
        },
        'args' => array(
            'domain' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($param, $request, $key) {
                     if ( !is_string($param) || empty(trim($param)) || strpos($param, '.') === false || preg_match('/\s/', $param) ) {
                         return new WP_Error( 'rest_invalid_param', esc_html__( 'Invalid domain format provided.', 'electrosuite-reseller' ), array( 'status' => 400 ) );
                     }
                     // Add more specific validation? (e.g., check character set, length?)
                     return true;
                }
            ),
        ),
    ) );
} );


/**
 * Extracts the SLD and TLD from a domain name.
 * Returns an array ['sld' => ..., 'tld' => ...] or false on failure.
 */
function extract_sld_tld($domain_name) {
    $domain_name = strtolower(trim($domain_name));
    // Basic check for at least one dot
    if (strpos($domain_name, '.') === false) {
        return false;
    }
    $parts = explode('.', $domain_name);
    if (count($parts) < 2) {
        return false; // Should not happen if strpos passed, but safety check
    }
    // Assume TLD is the last part
    $tld = array_pop($parts);
    // SLD is everything else joined back together
    $sld = implode('.', $parts);

    if (empty($sld) || empty($tld)) {
        return false;
    }
    return ['sld' => $sld, 'tld' => $tld];
}



// Replace the entire handle_electrosuite_domain_search_request function

/**
 * Handle the domain search REST API request (Using eNom Check V2).
 */
function handle_electrosuite_domain_search_request( WP_REST_Request $request ) {
    $domain_input = $request['domain'];

    // --- Extract SLD ---
    $domain_parts = extract_sld_tld($domain_input);
    if ($domain_parts === false && !empty($domain_input) && strpos($domain_input, '.') === false) {
        $sld = $domain_input;
    } elseif ($domain_parts !== false) {
        $sld = $domain_parts['sld'];
    } else {
         return new WP_Error('invalid_sld', __('Invalid domain or SLD provided.', 'electrosuite-reseller'), ['status' => 400]);
    }

    // --- Determine TLDs to Check ---
    $tlds_to_check = [];
    // Add TLDs from settings (example for eNom - adjust option names)
    if (get_option('electrosuite_reseller_enom_tlds_option_one') === 'yes') $tlds_to_check[] = 'com';
    if (get_option('electrosuite_reseller_enom_tlds_option_two') === 'yes') $tlds_to_check[] = 'org';
    if (get_option('electrosuite_reseller_enom_tlds_option_three') === 'yes') $tlds_to_check[] = 'net';
    // TODO: Add more TLDs? Get from API?

    if (empty($tlds_to_check)) {
         return new WP_Error('no_tlds', __('No TLDs configured for checking.', 'electrosuite-reseller'), ['status' => 500]);
    }
    $tlds_to_check = array_unique($tlds_to_check); // Ensure unique

    // --- Get Credentials & Settings ---
    $active_api_provider = get_option( 'electrosuite_reseller_server_api', 'enom' );
    if ($active_api_provider !== 'enom') {
        return new WP_Error('provider_not_supported', __('Multi-TLD check V2 only implemented for eNom currently.', 'electrosuite-reseller'), ['status' => 501]);
    }
    $is_test_mode = ( get_option( 'electrosuite_reseller_test_mode', 'no' ) === 'yes' );
    $username = get_option( 'electrosuite_reseller_enom_api_username' );
    $option_key_name = $is_test_mode ? 'electrosuite_reseller_enom_test_api_key' : 'electrosuite_reseller_enom_live_api_key';
    $api_key_to_use = get_option( $option_key_name );
    $key_type_for_error = $is_test_mode ? 'Test' : 'Live';

    if ( empty( $username ) || empty( $api_key_to_use ) ) { /* ... credential error handling ... */ return new WP_Error(/*...*/); }

    // --- Get Pricing Adjustment Settings ---
    $price_mode = get_option('electrosuite_reseller_price_mode', 'percentage');
    $price_value_raw = get_option('electrosuite_reseller_price_value', '0');
    $price_value = is_numeric($price_value_raw) ? floatval($price_value_raw) : 0.0;

    // --- Call eNom Check V2 API ---
    $api_results = call_enom_check_v2($username, $api_key_to_use, $sld, $tlds_to_check, $is_test_mode);

    // --- Handle API Errors ---
    if (is_wp_error($api_results)) {
        error_log("ElectroSuite Reseller API Error: Error returned from call_enom_check_v2. Code: " . $api_results->get_error_code() . " Message: " . $api_results->get_error_message());
        // Return the error directly to the frontend
        return $api_results;
    }

    // --- Process Successful Results & Apply Pricing ---
    $results_list = [];
    foreach ($tlds_to_check as $tld) {
        $full_domain = $sld . '.' . $tld;
        $result_item = [
            'domain' => $full_domain,
            'available' => 'error', // Default to error
            'adjusted_price' => null,
            'message' => __('Status not returned by registrar.', 'electrosuite-reseller')
        ];

        // Check if data exists for this TLD in the API response
        if (isset($api_results[$tld])) {
            $tld_data = $api_results[$tld];
            $result_item['available'] = $tld_data['available']; // true or false
            unset($result_item['message']); // Remove default error message

            // Apply pricing if available and cost is valid
            if ($result_item['available'] === true && isset($tld_data['cost']) && is_numeric($tld_data['cost']) && $tld_data['cost'] >= 0) {
                $base_cost = floatval($tld_data['cost']);
                $adjusted_price = $base_cost;

                if ($price_mode === 'fixed') {
                    $adjusted_price += $price_value;
                } elseif ($price_mode === 'percentage') {
                    $markup = $adjusted_price * ($price_value / 100.0);
                    $adjusted_price += $markup;
                }
                $result_item['adjusted_price'] = number_format(max(0, $adjusted_price), 2, '.', '');
            } elseif ($result_item['available'] === true) {
                 // Available but no valid cost price returned by API
                 $result_item['adjusted_price'] = 'N/A';
                 error_log("API Warning: No valid cost price returned for available TLD: {$tld}");
            }
        } else {
             error_log("API Warning: No data returned in V2 response for requested TLD: {$tld}");
        }
        $results_list[] = $result_item;
    }

    // --- Return Final Results ---
    return new WP_REST_Response( $results_list, 200 );
}

/**
 * Handle the domain search REST API request.
 *
 * @param WP_REST_Request $request The incoming request object.
 * @return WP_REST_Response|WP_Error A response object on success, or WP_Error on failure.
 *
 // commented out for potential update
function handle_electrosuite_domain_search_request( WP_REST_Request $request ) {
    $domain_input = $request['domain']; // e.g., "example.com" or just "example"

    // --- Extract SLD ---
    $domain_parts = extract_sld_tld($domain_input);
    // If user only entered SLD, try common TLDs; otherwise use the SLD from their input
    if ($domain_parts === false && !empty($domain_input) && strpos($domain_input, '.') === false) {
        $sld = $domain_input; // Assume input was just the SLD
        $initial_tld = 'com'; // Maybe default to checking .com first? Or get from settings?
        error_log("API Debug: Input '{$domain_input}' treated as SLD.");
    } elseif ($domain_parts !== false) {
        $sld = $domain_parts['sld'];
        $initial_tld = $domain_parts['tld']; // The TLD the user originally searched
        error_log("API Debug: Input '{$domain_input}' parsed as SLD '{$sld}' and TLD '{$initial_tld}'.");
    } else {
         return new WP_Error('invalid_sld', __('Invalid domain or SLD provided.', 'electrosuite-reseller'), ['status' => 400]);
    }

    // --- Determine TLDs to Check ---
    $tlds_to_check = [];
    // Start with the TLD the user entered, if any
    if (!empty($initial_tld)) {
         $tlds_to_check[] = $initial_tld;
    }
    // Add TLDs from settings (example for eNom - adjust option names)
    if (get_option('electrosuite_reseller_enom_tlds_option_one') === 'yes' && !in_array('com', $tlds_to_check)) $tlds_to_check[] = 'com';
    if (get_option('electrosuite_reseller_enom_tlds_option_two') === 'yes' && !in_array('org', $tlds_to_check)) $tlds_to_check[] = 'org';
    if (get_option('electrosuite_reseller_enom_tlds_option_three') === 'yes' && !in_array('net', $tlds_to_check)) $tlds_to_check[] = 'net';
    // TODO: Get a more comprehensive TLD list, potentially from eNom API (GetTLDList?) or better settings

    if (empty($tlds_to_check)) {
         return new WP_Error('no_tlds', __('No TLDs configured for checking.', 'electrosuite-reseller'), ['status' => 500]);
    }
    $tlds_to_check = array_unique($tlds_to_check); // Remove duplicates
    error_log("API Debug: TLDs to check for SLD '{$sld}': " . implode(', ', $tlds_to_check));


    // --- Get Credentials & Settings (Copied from previous version) ---
    $active_api_provider = get_option( 'electrosuite_reseller_server_api', 'enom' );
    if ($active_api_provider !== 'enom') {
        // For now, only handle eNom logic fully
        return new WP_Error('provider_not_supported', __('Multi-TLD check only implemented for eNom currently.', 'electrosuite-reseller'), ['status' => 501]);
    }
    $is_test_mode = ( get_option( 'electrosuite_reseller_test_mode', 'no' ) === 'yes' );
    $username = get_option( 'electrosuite_reseller_enom_api_username' );
    $option_key_name = $is_test_mode ? 'electrosuite_reseller_enom_test_api_key' : 'electrosuite_reseller_enom_live_api_key';
    $api_key_to_use = get_option( $option_key_name );
    $key_type_for_error = $is_test_mode ? 'Test' : 'Live';

    if ( empty( $username ) || empty( $api_key_to_use ) ) {
        $error_detail = empty($username) ? __('Username missing.', 'electrosuite-reseller') : sprintf(__('%s API key missing.', 'electrosuite-reseller'), $key_type_for_error);
        $error_message = sprintf( __( 'API credentials (%s) for eNom are not configured.', 'electrosuite-reseller' ), $error_detail );
        error_log("ElectroSuite Reseller API Error: " . $error_message);
        return new WP_Error('config_error', $error_message, array( 'status' => 500 ));
    }

    // --- Get Pricing Adjustment Settings ---
    $price_mode = get_option('electrosuite_reseller_price_mode', 'percentage'); // Use NEW ID
    $price_value_raw = get_option('electrosuite_reseller_price_value', '0');    // Use NEW ID
    // Sanitize price value
    $price_value = is_numeric($price_value_raw) ? floatval($price_value_raw) : 0.0;


    // --- Loop Through TLDs & Call API ---
    $results_list = [];
    foreach ($tlds_to_check as $tld) {
        $current_domain = $sld . '.' . $tld;
        error_log("API Debug: Checking domain: {$current_domain}");

        // --- !!! IMPORTANT: Replace this with your actual eNom check & pricing call !!! ---
        // Option A: Modify call_enom_api to also return price
        // $status_and_price = call_enom_api_get_status_and_price($username, $api_key_to_use, $sld, $tld, $is_test_mode);

        // Option B: Make separate calls (less efficient)
        $availability_result = call_enom_api($username, $api_key_to_use, $current_domain, $is_test_mode); // Use existing check first
        $raw_price = null;
        if (!is_wp_error($availability_result) && $availability_result['available']) {
            // If available, make a SECOND call to get pricing (replace with actual function)
            $raw_price = get_enom_tld_price($username, $api_key_to_use, $tld, $is_test_mode); // Needs implementation!
        }
        
        // --- Process Result for this TLD ---
        $result_item = ['domain' => $current_domain];

        if (is_wp_error($availability_result)) {
            // Handle API call error for this specific TLD
            $result_item['available'] = 'error';
            $result_item['message'] = $availability_result->get_error_message();
            error_log("API Error checking {$current_domain}: " . $result_item['message']);
        } elseif (isset($availability_result['available'])) {
             $result_item['available'] = $availability_result['available']; // true or false
             if ($result_item['available'] && is_numeric($raw_price) && $raw_price > 0) {
                 $result_item['raw_price'] = floatval($raw_price); // Store raw price temporarily
             } elseif ($result_item['available']) {
                 error_log("API Warning: {$current_domain} is available but no valid price found.");
             }
        } else {
             // Handle unexpected success structure from API call
             $result_item['available'] = 'error';
             $result_item['message'] = __('Unexpected response from registrar.', 'electrosuite-reseller');
             error_log("API Error checking {$current_domain}: Unexpected response structure.");
        }
        $results_list[] = $result_item;
    } // End TLD loop


    // --- Apply Pricing Adjustments ---
    foreach ($results_list as $key => $item) {
        if ($item['available'] === true && isset($item['raw_price'])) {
            $adjusted_price = $item['raw_price']; // Start with raw price
            if ($price_mode === 'fixed') {
                $adjusted_price += $price_value;
            } elseif ($price_mode === 'percentage') {
                 $markup = $adjusted_price * ($price_value / 100.0);
                 $adjusted_price += $markup;
            }
            // Ensure price isn't negative, format to 2 decimals
            $results_list[$key]['adjusted_price'] = number_format(max(0, $adjusted_price), 2, '.', '');
            unset($results_list[$key]['raw_price']); // Remove raw price before sending to frontend
        } elseif (isset($item['raw_price'])) {
             unset($results_list[$key]['raw_price']); // Remove raw price if not available
        }
    }

    // --- Return Final Results ---
    error_log("API Debug: Returning results list: " . print_r($results_list, true));
    return new WP_REST_Response( $results_list, 200 );
}
*/

// --- Helper Functions for Each API Provider ---


// Add this new function, remove the old call_enom_api / get_enom_tld_price

/**
 * Checks domain availability and pricing for multiple TLDs using eNom Check API V2.
 *
 * @param string $username    eNom Username.
 * @param string $api_key     eNom API Key (Live or Test).
 * @param string $sld         The Second-Level Domain to check.
 * @param array  $tld_array   An array of TLDs to check (e.g., ['com', 'net', 'org']).
 * @param bool   $is_test_mode Whether to use the test API endpoint.
 * @return array|WP_Error An associative array keyed by TLD with availability and cost, or WP_Error on failure.
 *                        Example Success: ['com' => ['available' => true, 'cost' => 10.99], 'net' => ['available' => false, 'cost' => null]]
 *                        Example Error: WP_Error object
 */
function call_enom_check_v2( $username, $api_key, $sld, $tld_array, $is_test_mode ) {
    if (empty($sld) || empty($tld_array) || !is_array($tld_array)) {
        return new WP_Error('invalid_input', __('Invalid SLD or TLD list provided to eNom check function.', 'electrosuite-reseller'));
    }

    // Define eNom URLs
    $live_url = 'https://reseller.enom.com/interface.asp';
    $test_url = 'https://resellertest.enom.com/interface.asp';
    $base_url = $is_test_mode ? $test_url : $live_url;

    // Construct API URL with V2 parameters
    $tld_list_string = implode(',', $tld_array); // Create comma-separated string
    $api_url = add_query_arg( array(
        'command' => 'Check',        // Command name
        'Version' => '2',            // Use Version 2
        'IncludePrice' => '1',       // Request pricing
        'uid' => $username,          // Credentials
        'pw' => $api_key,
        'SLD' => $sld,               // Second-Level Domain
        'TLDList' => $tld_list_string, // Comma-separated TLD list
        'responsetype' => 'json',    // Request JSON format
    ), $base_url );

    // Make the external API request
    // error_log("DEBUG eNom V2 Request URL: " . $api_url); // Keep commented out unless debugging credentials again
    $response = wp_remote_get( $api_url, ['timeout' => 20] ); // Increased timeout slightly for multi-check

    // Check for WordPress HTTP API errors
    if ( is_wp_error( $response ) ) {
         error_log("ElectroSuite Reseller eNom API Error: wp_remote_get failed (V2 Check). Error: " . $response->get_error_message());
         return new WP_Error('http_error', __('Could not connect to the domain registrar (V2). Please try again later.', 'electrosuite-reseller'), ['status' => 503]);
    }

    // Process the response
    $http_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    // Check 1: Basic HTTP or JSON failure
    // Note: eNom V2 might return slightly different base structure, adjust if needed based on actual responses
    if ( $http_code !== 200 || !$data ) {
         $error_message = __('eNom API V2 communication error.', 'electrosuite-reseller');
         $log_message = 'ElectroSuite Reseller eNom API Error: Critical error processing V2 response.';
         if (!$data && !empty($body)) { $log_message .= ' Invalid JSON received.'; }
         elseif ($http_code !== 200) { $log_message .= " HTTP status {$http_code}."; }
         error_log($log_message . " Raw Body: " . $body);
         return new WP_Error( 'enom_api_v2_critical_error', $error_message, array( 'status' => 502 ) );
    }

    // Check 2: eNom application-level errors (ErrCount at top level for V2?)
    // CONSULT ACTUAL V2 RESPONSE: The documentation isn't explicit if ErrCount is still top-level or nested. Assume top-level for now.
    if ( isset($data['ErrCount']) && $data['ErrCount'] > 0 ) {
         $error_message = __('eNom API Error', 'electrosuite-reseller');
         $log_message = 'ElectroSuite Reseller eNom API Error: Application error reported by eNom (V2).';
         $specific_error = isset($data['errors']['Err1']) ? $data['errors']['Err1'] : (isset($data['ResponseString']) ? $data['ResponseString'] : '');

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
         error_log("DEBUG eNom V2 App Error Raw Body: " . $body);
         return new WP_Error( 'enom_api_v2_app_error', $error_message, array( 'status' => 400 ) );
    }

        // --- Process Successful V2 Response (Revised for Parallel Arrays) ---
    $results = []; // Initialize results array keyed by TLD

    // Access the nested interface-response data
    if (!isset($data['interface-response'])) {
         error_log("eNom V2 API Error: 'interface-response' key missing from successful response.");
         return new WP_Error('enom_api_v2_structure_error', __('Unexpected response structure from eNom (missing interface-response).', 'electrosuite-reseller'));
    }
    $eNomResponse = $data['interface-response'];

    // Check if the required parallel arrays exist and have the same count
    $domain_count = isset($eNomResponse['DomainCount']) ? intval($eNomResponse['DomainCount']) : 0;
    if (
        $domain_count > 0 &&
        isset($eNomResponse['Domain']) && is_array($eNomResponse['Domain']) &&
        isset($eNomResponse['RRPCode']) && is_array($eNomResponse['RRPCode']) &&
        isset($eNomResponse['RRPText']) && is_array($eNomResponse['RRPText']) &&
        count($eNomResponse['Domain']) === $domain_count &&
        count($eNomResponse['RRPCode']) === $domain_count &&
        count($eNomResponse['RRPText']) === $domain_count
    ) {
        // Loop through the results using the index
        for ($i = 0; $i < $domain_count; $i++) {
            $domain_name = $eNomResponse['Domain'][$i];
            $rrp_code = $eNomResponse['RRPCode'][$i];
            $rrp_text = $eNomResponse['RRPText'][$i];

            // Extract TLD from the domain name
            $domain_parts = explode('.', $domain_name, 2);
            $tld = isset($domain_parts[1]) ? strtolower($domain_parts[1]) : null;

            if ($tld) {
                $results[$tld] = [
                    'available'   => ($rrp_code == '210'), // RRPCode 210 means available
                    'cost'        => null, // Pricing not included in this response format
                    'status_code' => $rrp_code,
                    'status_desc' => $rrp_text,
                ];
            } else {
                 error_log("eNom V2 API Warning: Could not extract TLD from returned domain: " . $domain_name);
            }
        }
    } elseif ($domain_count > 0) {
        // Data structure is unexpected (arrays missing or counts don't match)
        error_log("eNom V2 API Error: Parallel array structure mismatch or missing arrays in response. DomainCount reported: {$domain_count}. Raw Body: " . $body);
        return new WP_Error('enom_api_v2_structure_error', __('Unexpected response structure from eNom (array mismatch).', 'electrosuite-reseller'));
    } else {
         // DomainCount was 0 or missing, even though ErrCount was 0.
         error_log("eNom V2 API Warning: ErrCount is 0 but DomainCount is also 0. Raw Body: " . $body);
         // Return empty results, calling function will handle this.
    }

    return $results; // Return the associative array keyed by TLD
}


/**
 * Makes an API call to eNom to check domain availability. (Cleaned Logs)
 *
 * @param string $username    eNom Username.
 * @param string $api_key     eNom API Key (Live or Test, already selected).
 * @param string $domain      The full domain name to check (e.g., example.com).
 * @param bool   $is_test_mode Whether to use the test API endpoint.
 * @return array|WP_Error Array with results on success, WP_Error on failure.
 // commented out for potential update
function call_enom_api( $username, $api_key, $domain, $is_test_mode ) {
    // Define eNom URLs
    $live_url = 'https://reseller.enom.com/interface.asp';
    $test_url = 'https://resellertest.enom.com/interface.asp';
    $base_url = $is_test_mode ? $test_url : $live_url;

    // Prepare domain parts
    $domain_parts = explode('.', $domain, 2);
    $sld = isset($domain_parts[0]) ? $domain_parts[0] : '';
    $tld = isset($domain_parts[1]) ? $domain_parts[1] : '';
    if (empty($sld) || empty($tld)) {
        error_log("ElectroSuite Reseller eNom API: Invalid domain format provided.");
        return new WP_Error('invalid_domain', __('Invalid domain format.', 'electrosuite-reseller'), ['status' => 400]);
    }

    // Construct the full API URL
    $api_url = add_query_arg( array(
        'command' => 'check',
        'uid' => $username,
        'pw' => $api_key,
        'sld' => $sld,
        'tld' => $tld,
        'responsetype' => 'json',
    ), $base_url );

    // Make the external API request
    $response = wp_remote_get( $api_url, ['timeout' => 15] );

    // Check for WordPress HTTP API errors
    if ( is_wp_error( $response ) ) {
         error_log("ElectroSuite Reseller eNom API Error: wp_remote_get failed. Error: " . $response->get_error_message());
         return new WP_Error('http_error', __('Could not connect to the domain registrar. Please try again later.', 'electrosuite-reseller'), ['status' => 503]);
    }

    // Process the response
    $http_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    // Check 1: Basic HTTP or JSON failure
    if ( $http_code !== 200 || !$data || !isset($data['interface-response']) ) {
         $error_message = __('eNom API communication error.', 'electrosuite-reseller');
         $log_message = 'ElectroSuite Reseller eNom API Error: Critical error processing response.';
         if (!$data && !empty($body)) { $log_message .= ' Invalid JSON received.'; }
         elseif ($http_code !== 200) { $log_message .= " HTTP status {$http_code}."; }
         elseif (!isset($data['interface-response'])) { $log_message .= ' Unexpected response structure.'; }
         error_log($log_message . " Raw Body Length: " . strlen($body));
         return new WP_Error( 'enom_api_critical_error', $error_message, array( 'status' => 502 ) );
    }

    $eNomResponse = $data['interface-response'];

    // Check 2: eNom application-level errors
    if ( isset($eNomResponse['ErrCount']) && $eNomResponse['ErrCount'] > 0 ) {
         $error_message = __('eNom API Error', 'electrosuite-reseller');
         $log_message = 'ElectroSuite Reseller eNom API Error: Application error reported by eNom.';
         $specific_error = '';

         if (isset($eNomResponse['errors']['Err1'])) {
             $specific_error = $eNomResponse['errors']['Err1'];
         } elseif (isset($eNomResponse['responses']['response']['ResponseString'])) {
             $specific_error = $eNomResponse['responses']['response']['ResponseString'];
         }

         // Sanitize before setting user message or logging
         if (!empty($specific_error)) {
             $log_message .= " Message: {$specific_error}";
             if (stripos($specific_error, 'password') !== false || stripos($specific_error, 'loginid') !== false) {
                 $error_message = __('Authentication failed with domain registrar.', 'electrosuite-reseller');
             } else {
                 $error_message = $specific_error;
             }
         } else {
              $error_message = __('An unspecified error occurred with the domain registrar.', 'electrosuite-reseller'); // Fallback if no specific message found
         }

         error_log($log_message);
         return new WP_Error( 'enom_api_app_error', $error_message, array( 'status' => 400 ) );
    }

    // --- Process Successful Response ---
    $processed_results = [
        'provider' => 'enom',
        'available' => isset($eNomResponse['RRPCode']) && $eNomResponse['RRPCode'] == '210',
        'domain' => $domain,
        'status_code' => isset($eNomResponse['RRPCode']) ? $eNomResponse['RRPCode'] : null,
        'status_desc' => isset($eNomResponse['RRPText']) ? $eNomResponse['RRPText'] : null,
        'suggestions' => [],
    ];

    return $processed_results; // Return the results array
}
*/

/**
 * Placeholder for ResellerClub API call.
 */
function call_resellerclub_api( $user_id, $api_key, $domain, $is_test_mode ) {
    error_log("ElectroSuite Reseller API: ResellerClub API call initiated for domain {$domain}. (Not Implemented)");
    // TODO: Implement ResellerClub API logic here
    return new WP_Error( 'not_implemented', __('ResellerClub integration is not yet available.', 'electrosuite-reseller'), array( 'status' => 501 ) );
}

/**
 * Placeholder for CentralNic API call.
 */
function call_centralnic_api( $username, $api_key, $domain, $is_test_mode ) {
    error_log("ElectroSuite Reseller API: CentralNic API call initiated for domain {$domain}. (Not Implemented)");
    // TODO: Implement CentralNic API logic here
     return new WP_Error( 'not_implemented', __('CentralNic integration is not yet available.', 'electrosuite-reseller'), array( 'status' => 501 ) );
}
?>