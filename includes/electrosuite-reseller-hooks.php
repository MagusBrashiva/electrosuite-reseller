<?php
/**
 * ElectroSuite Reseller Hooks
 *
 * Action and filter hooks.
 *
 * @author      ElectroSuite
 * @category    Core
 * @package     ElectroSuite Reseller/Functions
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Pass data to the Domain Search block's view script.
 *
 * This function makes the REST API URL and a nonce available
 * to the front-end JavaScript for the domain search block.
 */
function electrosuite_reseller_enqueue_domain_search_data() {

    // The script handle is derived from the block name in block.json
    // Block name: 'create-block/domain-search' -> Handle: 'create-block-domain-search-view-script'
    $script_handle = 'create-block-domain-search-view-script';

    // Check if the view script is actually enqueued on the current page.
    // This ensures we only add data if the block is present and its script is loaded.
    if ( wp_script_is( $script_handle, 'enqueued' ) ) {

        // Data to pass to the script
        $data_to_pass = array(
            // Get the URL for our custom REST endpoint
            'apiUrl' => esc_url_raw( get_rest_url( null, 'domain-search/v1/check' ) ),
            // Create a nonce for security verification in the REST API callback
            // 'wp_rest' is the default action used by the REST API permission callback.
            'nonce'  => wp_create_nonce( 'wp_rest' )
        );

        // Make the data available to the script under the global JS object 'domainSearchData'
        wp_localize_script( $script_handle, 'domainSearchData', $data_to_pass );

    }
}
// Hook into the action that runs when front-end scripts are enqueued.
// Use a priority > 10 if needed to ensure it runs after the block script is enqueued by WP.
add_action( 'wp_enqueue_scripts', 'electrosuite_reseller_enqueue_domain_search_data', 20 );


// --- Add other action/filter hooks below as needed ---

?>