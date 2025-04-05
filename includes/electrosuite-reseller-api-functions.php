<?php
/**
 * ElectroSuite Reseller API Functions
 *
 * General API functions available on both the front-end and admin.
 *
 * @author 		ElectroSuite
 * @category 	Core
 * @package 	ElectroSuite Reseller/Functions
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'rest_api_init', function () {
    register_rest_route( 'domain-search/v1', '/check', array(
        'methods' => 'POST',
        'callback' => 'handle_electrosuite_domain_search_request',
        'permission_callback' => function () {
            $nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
            if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                 return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid nonce.', 'electrosuite-reseller' ), array( 'status' => 403 ) );
            }
            return true;
        },
        'args' => array(
            'domain' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($param) {
                     return is_string($param) && ! empty($param) && strpos($param, '.') !== false;
                }
            ),
        ),
    ) );
} );

function handle_electrosuite_domain_search_request( WP_REST_Request $request ) {
    // --- 1. Determine Active API Provider ---
    $active_api_provider = get_option( 'electrosuite_reseller_server_api', 'enom' );

    // --- 2. Check Test Mode Status ---
    // Use 'no' as default if option is missing; checkbox value is 'yes' when checked.
    $is_test_mode = ( get_option( 'electrosuite_reseller_checkbox', 'no' ) === 'yes' );

    $domain_to_check = $request['domain'];
    $username = null;
    $api_key = null;

    // --- 3. Get Credentials Based on Active Provider ---
    switch ( $active_api_provider ) {
        case 'resellerclub':
            $username = get_option( 'electrosuite_reseller_resellerclub_api_username_option' );
            $api_key = get_option( 'electrosuite_reseller_resellerclub_api_key_option' );
            break;
        case 'centralnic':
            $username = get_option( 'electrosuite_reseller_centralnic_api_username_option' );
            $api_key = get_option( 'electrosuite_reseller_centralnic_api_key_option' );
            break;
        case 'enom':
        default:
            $username = get_option( 'electrosuite_reseller_enom_api_username_option' );
            $api_key = get_option( 'electrosuite_reseller_enom_api_key_option' );
            break;
    }

    // --- 4. Validate Credentials ---
    if ( empty( $username ) || empty( $api_key ) ) {
         error_log("Domain Search Block Error: Missing API credentials for provider: " . $active_api_provider);
        return new WP_Error( 'config_error', __( 'API credentials for the active provider are not configured.', 'electrosuite-reseller' ), array( 'status' => 500 ) );
    }

    // --- 5. Prepare and Make External API Call (Provider Specific) ---
    $results = null;
    switch ( $active_api_provider ) {
        case 'resellerclub':
            // Pass the test mode flag to the helper function
            $results = call_resellerclub_api( $username, $api_key, $domain_to_check, $is_test_mode );
            break;
        case 'centralnic':
             // Pass the test mode flag to the helper function
            $results = call_centralnic_api( $username, $api_key, $domain_to_check, $is_test_mode );
            break;
        case 'enom':
        default:
             // Pass the test mode flag to the helper function
            $results = call_enom_api( $username, $api_key, $domain_to_check, $is_test_mode );
            break;
    }

    // --- 6. Process Results ---
    if ( is_wp_error( $results ) ) {
        return $results;
    } elseif ( $results === null) {
         return new WP_Error( 'internal_error', __( 'Failed to get results from API.', 'electrosuite-reseller' ), array( 'status' => 500 ) );
    } else {
        return new WP_REST_Response( $results, 200 );
    }
}

// --- Helper Functions for Each API Provider ---

// IMPORTANT: Update function definitions to accept $is_test_mode
// IMPORTANT: You MUST find the correct Test/Sandbox URLs from the API documentation!

function call_enom_api( $username, $api_key, $domain, $is_test_mode ) {
    // Define eNom URLs - REPLACE WITH ACTUAL URLs FROM ENOM DOCS
    $live_url = 'https://reseller.enom.com/interface.asp';
    $test_url = 'https://resellertest.enom.com/interface.asp'; // Or whatever their test URL is

    // Select URL based on test mode
    $base_url = $is_test_mode ? $test_url : $live_url;

    // Prepare domain parts (assuming SLD/TLD separation needed by eNom)
    $domain_parts = explode('.', $domain, 2);
    $sld = isset($domain_parts[0]) ? $domain_parts[0] : '';
    $tld = isset($domain_parts[1]) ? $domain_parts[1] : '';

    if (empty($sld) || empty($tld)) {
        return new WP_Error('invalid_domain', __('Invalid domain format.', 'electrosuite-reseller'), ['status' => 400]);
    }

    // Construct the full API URL with parameters (adjust based on eNom docs)
    $api_url = add_query_arg( array(
        'command' => 'check',
        'uid' => $username,
        'pw' => $api_key,
        'sld' => $sld,
        'tld' => $tld,
        'responsetype' => 'json', // Request JSON if possible
    ), $base_url );

    $response = wp_remote_get( $api_url, ['timeout' => 15] );

    if ( is_wp_error( $response ) ) return $response;

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    $http_code = wp_remote_retrieve_response_code($response);

    // Add more robust error checking based on eNom's response codes/structure
    if ($http_code !== 200 || !$data || isset($data['ErrCount']) && $data['ErrCount'] > 0 ) {
         $error_message = isset($data['errors'][0]) ? $data['errors'][0] : __('eNom API Error', 'electrosuite-reseller');
         return new WP_Error( 'enom_api_error', $error_message, array( 'status' => $http_code, 'response' => $data ) );
    }

    // Process $data from eNom - this depends entirely on their response format
    $processed_results = [
        'provider' => 'enom',
         // Example - ADJUST BASED ON ACTUAL ENOM RESPONSE FIELD FOR AVAILABILITY
        'available' => isset($data['RRPCode']) && $data['RRPCode'] == '210',
        'domain' => $domain,
        'status_code' => isset($data['RRPCode']) ? $data['RRPCode'] : null, // Example extra info
        'status_desc' => isset($data['RRPText']) ? $data['RRPText'] : null, // Example extra info
        'suggestions' => [], // Populate if eNom provides suggestions
        // 'raw' => $data // Optional: Include raw response for debugging
    ];
    return $processed_results;
}

function call_resellerclub_api( $user_id, $api_key, $domain, $is_test_mode ) { // Often uses User ID not username
    // Define ResellerClub URLs - REPLACE WITH ACTUAL URLs FROM RESELLERCLUB DOCS
    $live_url = 'https://httpapi.com/api/domains/available.json'; // Example structure
    $test_url = 'https://test.httpapi.com/api/domains/available.json'; // Example structure

    $base_url = $is_test_mode ? $test_url : $live_url;

    // ResellerClub often needs domain and TLDs separated, and auth info in query string
    $domain_parts = explode('.', $domain, 2);
    $sld = isset($domain_parts[0]) ? $domain_parts[0] : '';
    $tld = isset($domain_parts[1]) ? $domain_parts[1] : '';

    if (empty($sld) || empty($tld)) {
         return new WP_Error('invalid_domain', __('Invalid domain format.', 'electrosuite-reseller'), ['status' => 400]);
    }

    $api_url = add_query_arg( array(
        'auth-userid' => $user_id,       // Check param names in docs!
        'api-key' => $api_key,         // Check param names in docs!
        'domain-name' => $sld,
        'tlds' => $tld,
    ), $base_url );

    // TODO: Implement ResellerClub wp_remote_get/post call, response parsing, error handling
    error_log("ResellerClub API call to: " . $api_url); // Log for debugging
    return new WP_Error( 'not_implemented', __('ResellerClub API call not implemented.', 'electrosuite-reseller'), array( 'status' => 501 ) );

    // Example structure after successful call and parsing $data
    /*
    $processed_results = [
        'provider' => 'resellerclub',
        'available' => isset($data[$domain]['status']) && $data[$domain]['status'] == 'available', // Example check
        'domain' => $domain,
        'suggestions' => [], // Populate if available
    ];
    return $processed_results;
    */
}

function call_centralnic_api( $username, $api_key, $domain, $is_test_mode ) {
    // Define CentralNic URLs - REPLACE WITH ACTUAL URLs FROM CENTRALNIC DOCS
    $live_url = 'https://api.centralnic.com/v1/domain/check'; // Example structure - likely needs versioning, etc.
    $test_url = 'https://test.api.centralnic.com/v1/domain/check'; // Example structure

    $base_url = $is_test_mode ? $test_url : $live_url;

    // CentralNic might use Basic Auth (headers) or other methods. Check docs.
    // Might need domain in request body or query params.

    // TODO: Implement CentralNic wp_remote_get/post call, authentication (maybe in headers), response parsing, error handling
    error_log("CentralNic API call to: " . $base_url . " for domain " . $domain); // Log for debugging
    return new WP_Error( 'not_implemented', __('CentralNic API call not implemented.', 'electrosuite-reseller'), array( 'status' => 501 ) );

     // Example structure after successful call and parsing $data
    /*
    $processed_results = [
        'provider' => 'centralnic',
        'available' => isset($data['status']) && in_array('available', $data['status']), // Example check
        'domain' => $domain,
        'suggestions' => [], // Populate if available
    ];
    return $processed_results;
    */
}
?>