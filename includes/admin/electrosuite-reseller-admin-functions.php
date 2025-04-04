<?php
/**
 * ElectroSuite Reseller Admin Functions
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin/Functions
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get all Plugin Name screen ids
 *
 * @since 1.0.0
 * @return array
 */
function electrosuite_reseller_get_screen_ids() {
	$menu_name = strtolower( str_replace ( ' ', '-', ElectroSuite_Reseller()->menu_name ) );

	$electrosuite_reseller_screen_id = ELECTROSUITE_RESELLER_SCREEN_ID;

	return apply_filters( 'electrosuite_reseller_screen_ids', array(
		'plugins',
		'toplevel_page_' . $electrosuite_reseller_screen_id,
		'dashboard_page_' . $electrosuite_reseller_screen_id . '-about',
		'dashboard_page_' . $electrosuite_reseller_screen_id . '-changelog',
		'dashboard_page_' . $electrosuite_reseller_screen_id . '-credits',
		'dashboard_page_' . $electrosuite_reseller_screen_id . '-translations',
		'dashboard_page_' . $electrosuite_reseller_screen_id . '-freedoms',
		$electrosuite_reseller_screen_id . '_page_' . $electrosuite_reseller_screen_id . '_settings',
		$electrosuite_reseller_screen_id . '_page_' . $electrosuite_reseller_screen_id . '-settings',
		$electrosuite_reseller_screen_id . '_page_' . $electrosuite_reseller_screen_id . '-status',
		$menu_name . '_page_' . $electrosuite_reseller_screen_id . '_settings',
		$menu_name . '_page_' . $electrosuite_reseller_screen_id . '-settings',
		$menu_name . '_page_' . $electrosuite_reseller_screen_id . '-status',
	) );
}

/**
 * Create a page and store the ID in an option.
 *
 * @access public
 * @since 1.0.0
 * @param mixed $slug Slug for the new page
 * @param mixed $option Option name to store the page's ID
 * @param string $page_title (default: '') Title for the new page
 * @param string $page_content (default: '') Content for the new page
 * @param int $post_parent (default: 0) Parent for the new page
 * @return int page ID
 */
function electrosuite_reseller_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
	global $wpdb;

	$option_value = get_option( $option );

	if ( $option_value > 0 && get_post( $option_value ) )
		return -1;

	$page_found = null;

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	}
	else {
		// Search for an existing page with the specified page slug
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $page_found ) {
		if ( ! $option_value ) {
			update_option( $option, $page_found );
		}

		return $page_found;
	}

	$page_data = array(
		'post_status'       => 'publish',
		'post_type'         => 'page',
		'post_author'       => 1,
		'post_name'         => $slug,
		'post_title'        => $page_title,
		'post_content'      => $page_content,
		'post_parent'       => $post_parent,
		'comment_status'    => 'closed'
	);

	$page_id = wp_insert_post( $page_data );

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}

/**
 * Output admin fields.
 *
 * Loops though the plugin name options array and outputs each field.
 *
 * @since 1.0.0
 * @param array $options Opens array to output
 */
function electrosuite_reseller_admin_fields( $options ) {
	if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Settings' ) ) {
		include 'class-electrosuite-reseller-admin-settings.php';
	}

	ElectroSuite_Reseller_Admin_Settings::output_fields( $options );
}

/**
 * Update all settings which are passed.
 *
 * @access public
 * @since 1.0.0
 * @param array $options
 * @return void
 */
function electrosuite_reseller_update_options( $options ) {
	if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Settings' ) ) {
		include 'class-electrosuite-reseller-admin-settings.php';
	}

	ElectroSuite_Reseller_Admin_Settings::save_fields( $options );
}

/**
 * Get a setting from the settings API.
 *
 * @since 1.0.0
 * @param mixed $option
 * @return string
 */
function electrosuite_reseller_settings_get_option( $option_name, $default = '' ) {
	if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Settings' ) ) {
		include 'class-electrosuite-reseller-admin-settings.php';
	}

	return ElectroSuite_Reseller_Admin_Settings::get_option( $option_name, $default );
}

/**
 * Display Translation progress from Transifex
 *
 * @since 1.0.0
 * @param string $slug Transifex slug
 */
function transifex_display_translation_progress() {
	$stats = new ElectroSuite_Reseller_Transifex_Stats();
	$resource = ElectroSuite_Reseller()->transifex_resources_slug;
	$data_resource = $resource ? " data-resource-slug='{$resource}'" : ''; ?>
	<div class='transifex-stats' data-project-slug='<?php echo ElectroSuite_Reseller()->transifex_project_slug; ?>'<?php echo $data_resource; ?>/>
		<?php $stats->display_translations_progress(); ?>
	</div>
	<?php
}

/**
 * Display Translation Stats from Transifex
 *
 * @since 1.0.0
 * @param string $slug Transifex slug
 */
function transifex_display_translators() {
	$stats = new ElectroSuite_Reseller_Transifex_Stats();
	?>
	<div class='transifex-stats-contributors' data-project-slug='<?php echo ElectroSuite_Reseller()->transifex_project_slug; ?>'/>
		<?php $stats->display_contributors(); ?>
	</div>
	<?php
}

/**
 * Hooks Plugin Name actions, when present in the $_REQUEST superglobal. 
 * Every electrosuite_reseller_action present in $_REQUEST is called using 
 * WordPress's do_action function. These functions are called on init.
 *
 * @since 1.0.0
 * @return void
 */
function electrosuite_reseller_do_actions() {
	if ( isset( $_REQUEST['electrosuite_reseller_action'] ) ) {
		do_action( 'electrosuite_reseller_' . $_REQUEST['electrosuite_reseller_action'], $_REQUEST );
	}
}
add_action( 'admin_init', 'electrosuite_reseller_do_actions' );

?>