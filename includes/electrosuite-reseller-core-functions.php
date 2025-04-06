<?php
/**
 * ElectroSuite Reseller Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author 		ElectroSuite
 * @category 	Core
 * @package 	ElectroSuite Reseller/Functions
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include core functions
include( 'electrosuite-reseller-conditional-functions.php' );
include( 'electrosuite-reseller-formatting-functions.php' );
include( 'electrosuite-reseller-api-functions.php' );

/**
 * Retrieve page ids. returns -1 if no page is found
 *
 * @access public
 * @param string $page
 * @return int
 */
function electrosuite_reseller_get_page_id( $page ) {

	$page = apply_filters( 'electrosuite_reseller_get_' . $page . '_page_id', get_option('electrosuite_reseller_' . $page . '_page_id' ) );

	return $page ? $page : -1;
}

/**
 * Get template part.
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 * @return void
 */
function electrosuite_reseller_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/electrosuite-reseller/slug-name.php
	if ( $name ) {
		$template = locate_template( array ( "{$slug}-{$name}.php", ElectroSuite_Reseller()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( !$template && $name && file_exists( ElectroSuite_Reseller()->plugin_path() . "/templates/{$slug}-{$name}.php" ) )
		$template = ElectroSuite_Reseller()->plugin_path() . "/templates/{$slug}-{$name}.php";

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/electrosuite-reseller/slug.php
	if ( !$template )
		$template = locate_template( array ( "{$slug}.php", ElectroSuite_Reseller()->template_path() . "{$slug}.php" ) );

	if ( $template )
		load_template( $template, false );
}

/**
 * Get other templates, passing attributes and including the file.
 *
 * @access public
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function electrosuite_reseller_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array($args) )
		extract( $args );

	$located = electrosuite_reseller_locate_template( $template_name, $template_path, $default_path );

	do_action( 'electrosuite_reseller_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'electrosuite_reseller_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @access public
 * @param mixed $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function electrosuite_reseller_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) $template_path = ElectroSuite_Reseller()->template_path();
	if ( ! $default_path ) $default_path = ElectroSuite_Reseller()->plugin_path() . '/templates/';

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters('electrosuite_reseller_locate_template', $template, $template_name, $template_path);
}

/**
 * Get an image size.
 *
 * Variable is filtered by electrosuite_reseller_get_image_size_{image_size}
 *
 * @param string $image_size
 * @return array
 */
function electrosuite_reseller_get_image_size( $image_size ) {
	if ( in_array( $image_size, array( '_thumbnail', '_single' ) ) ) {
		$size 			= get_option( $image_size . '_image_size', array() );
		$size['width'] 	= isset( $size['width'] ) ? $size['width'] : '300';
		$size['height'] = isset( $size['height'] ) ? $size['height'] : '300';
		$size['crop'] 	= isset( $size['crop'] ) ? $size['crop'] : 1;
	}
	else {
		$size = array(
			'width' 	=> '300',
			'height' 	=> '300',
			'crop' 		=> 1
		);
	}
	return apply_filters( 'electrosuite_reseller_get_image_size_' . $image_size, $size );
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */
function electrosuite_reseller_enqueue_js( $code ) {
	global $electrosuite_reseller_queued_js;

	if ( empty( $electrosuite_reseller_queued_js ) )
		$electrosuite_reseller_queued_js = "";

	$electrosuite_reseller_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function electrosuite_reseller_print_js() {
	global $electrosuite_reseller_queued_js;

	if ( ! empty( $electrosuite_reseller_queued_js ) ) {

		echo "<!-- Plugin Name JavaScript-->\n<script type=\"text/javascript\">\njQuery(document).ready(function($) {";

		// Sanitize
		$electrosuite_reseller_queued_js = wp_check_invalid_utf8( $electrosuite_reseller_queued_js );
		$electrosuite_reseller_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $electrosuite_reseller_queued_js );
		$electrosuite_reseller_queued_js = str_replace( "\r", '', $electrosuite_reseller_queued_js );

		echo $electrosuite_reseller_queued_js . "});\n</script>\n";

		unset( $electrosuite_reseller_queued_js );
	}
}

/**
 * Set a cookie - wrapper for setcookie using WP constants
 *
 * @param  string  $name   Name of the cookie being set
 * @param  string  $value  Value of the cookie
 * @param  integer $expire Expiry of the cookie
 */
function electrosuite_reseller_setcookie( $name, $value, $expire = 0 ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, false );
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		trigger_error( "Cookie cannot be set - headers already sent", E_USER_NOTICE );
	}
}

?>