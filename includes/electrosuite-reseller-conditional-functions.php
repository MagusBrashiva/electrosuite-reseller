<?php
/**
 * ElectroSuite Reseller Conditional Functions
 *
 * Functions for determining the current query/page.
 *
 * @author 		ElectroSuite
 * @category 	Core
 * @package 	ElectroSuite Reseller/Functions
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'is_ajax' ) ) {

	/**
	 * is_ajax - Returns true when the page is loaded via ajax.
	 *
	 * @access public
	 * @return bool
	 */
	function is_ajax() {
		if ( defined('DOING_AJAX') ) {
			return true;
		}

		return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) ? true : false;
	}
}

?>