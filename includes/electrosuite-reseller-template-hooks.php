<?php
/**
 * ElectroSuite Reseller Template Hooks
 *
 * Action/filter hooks used for ElectroSuite Reseller functions/templates
 *
 * @author 		ElectroSuite
 * @package 	ElectroSuite Reseller/Templates
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Adds a generator tag in the header.
add_action( 'wp_head', 'generator' );

?>