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
    if (strpos($domain_name, '.') === false) {
        return false;
    }
    $parts = explode('.', $domain_name);
    if (count($parts) < 2) { return false; }
    $tld = array_pop($parts);
    $sld = implode('.', $parts);
    if (empty($sld) || empty($tld)) { return false; }
    return ['sld' => $sld, 'tld' => $tld];
}




/**
 * Handle the domain search REST API request (Multi-TLD Avail + Single TLD Price).
 *
 * @param WP_REST_Request $request The incoming request object.
 * @return WP_REST_Response|WP_Error A response object on success, or WP_Error on failure.
 */
function handle_electrosuite_domain_search_request( WP_REST_Request $request ) {
    $domain_input = $request['domain'];

    // --- Extract SLD ---
    $sld = null; // Initialize $sld
    $domain_parts = extract_sld_tld($domain_input);
    if ($domain_parts === false && !empty($domain_input) && strpos($domain_input, '.') === false) {
        $sld = $domain_input; // Assume input was just the SLD
    } elseif ($domain_parts !== false) {
        $sld = $domain_parts['sld']; // Use SLD from parsed input
    }

    // Check if SLD extraction was successful
    if ( $sld === null ) {
         return new WP_Error('invalid_sld', __('Invalid domain or SLD provided.', 'electrosuite-reseller'), ['status' => 400]);
    }

    // --- Determine TLDs to Check ---
    $tlds_to_check = [];
    // Add TLDs from settings (using eNom options as example)
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
        return new WP_Error('provider_not_supported', __('Domain check only implemented for eNom currently.', 'electrosuite-reseller'), ['status' => 501]);
    }
    $is_test_mode = ( get_option( 'electrosuite_reseller_test_mode', 'no' ) === 'yes' );
    $username = get_option( 'electrosuite_reseller_enom_api_username' );
    $option_key_name = $is_test_mode ? 'electrosuite_reseller_enom_test_api_key' : 'electrosuite_reseller_enom_live_api_key';
    $api_key_to_use = get_option( $option_key_name );
    $key_type_for_error = $is_test_mode ? 'Test' : 'Live';

    // Validate credentials
    if ( empty( $username ) || empty( $api_key_to_use ) ) {
         $error_detail = empty($username) ? __('Username missing.', 'electrosuite-reseller') : sprintf(__('%s API key missing.', 'electrosuite-reseller'), $key_type_for_error);
         $error_message = sprintf( __( 'API credentials (%s) for eNom are not configured.', 'electrosuite-reseller' ), $error_detail );
         error_log("ElectroSuite Reseller API Error: " . $error_message);
         return new WP_Error('config_error', $error_message, array( 'status' => 500 ));
    }

    // --- Get Pricing Adjustment Settings ---
    $price_mode = get_option('electrosuite_reseller_price_mode', 'percentage');
    $price_value_raw = get_option('electrosuite_reseller_price_value', '0');
    $price_value = is_numeric($price_value_raw) ? floatval($price_value_raw) : 0.0;

    // --- STEP A: Call Multi-TLD Availability Check ---
    // Ensure the function name matches the one defined for multi-TLD check
    $availability_results = call_enom_check_v2_availability($username, $api_key_to_use, $sld, $tlds_to_check, $is_test_mode);

    // Handle potential errors from the availability check immediately
    if (is_wp_error($availability_results)) {
        error_log("API Error during multi-TLD availability check: " . $availability_results->get_error_message());
        return $availability_results; // Return the WP_Error object
    }

    // --- STEP B: Loop through requested TLDs & Get Prices for AVAILABLE ones ---
    $results_list = [];
    foreach ($tlds_to_check as $tld) {
        $current_domain = $sld . '.' . $tld;
        $result_item = [
            'domain' => $current_domain,
            'available' => 'error', // Default state
            'adjusted_price' => null,
            'message' => __('Status could not be determined.', 'electrosuite-reseller')
        ];

        // Check availability status from the first API call's results
        if (isset($availability_results[$tld])) {
             $is_available = $availability_results[$tld]['available']; // Should be true or false
             $result_item['available'] = $is_available;
             unset($result_item['message']); // Clear default error message

             // If it's available according to the first check, make a SECOND call to get the price
             if ($is_available === true) {
                  // Ensure the function name matches the one for single TLD price check
                  $price_result = call_enom_check_single_v2_price($username, $api_key_to_use, $sld, $tld, $is_test_mode);

                  if (is_wp_error($price_result)) {
                       // Log error getting price, set price indicator to 'Error'
                       error_log("API Price Warning: Failed to get price for available domain {$current_domain}: " . $price_result->get_error_message());
                       $result_item['adjusted_price'] = 'Error';
                  } elseif (is_array($price_result) && isset($price_result['cost']) && is_numeric($price_result['cost']) && $price_result['cost'] >= 0) {
                       // Successfully got cost price, now apply markup
                       $base_cost = floatval($price_result['cost']);
                       $adjusted_price = $base_cost;
                       if ($price_mode === 'fixed') {
                           $adjusted_price += $price_value;
                       } elseif ($price_mode === 'percentage') {
                           // Ensure calculation is correct: markup based on cost
                           $markup = $base_cost * ($price_value / 100.0);
                           $adjusted_price += $markup;
                       }
                       // Format the final selling price
                       $result_item['adjusted_price'] = number_format(max(0, $adjusted_price), 2, '.', '');
                  } else {
                       // Price call succeeded but didn't return a valid cost value
                       error_log("API Price Warning: No valid cost price returned by single check for available domain {$current_domain}");
                       $result_item['adjusted_price'] = 'N/A';
                  }
             } // End if ($is_available === true)

        } else {
             // TLD wasn't found in the availability results array - this shouldn't happen if the first call worked
             error_log("API Availability Warning: TLD {$tld} missing from multi-check response array.");
             $result_item['message'] = __('Status not returned by registrar.', 'electrosuite-reseller');
        }
        $results_list[] = $result_item;
    } // End TLD loop

    // --- Return Final Results ---
    // error_log("API Debug: Returning results list: " . print_r($results_list, true)); // Optional final check
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
        // IncludePrice=1 doesn't seem to work reliably for multi-TLD test, but leave it for now? Or remove? Let's remove for just availability check.
        // 'IncludePrice' => '1',
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
             error_log("DEBUG eNom Single Price Check: Price found for {$sld}.{$tld}: " . $result_data['cost']); // Log successful price retrieval
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