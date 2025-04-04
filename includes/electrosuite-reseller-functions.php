<?php
/**
 * ElectroSuite Reseller Page Functions
 *
 * Functions related to pages and menus.
 *
 * @author 		ElectroSuite
 * @category 	Core
 * @package 	ElectroSuite Reseller/Functions
 * @version 	0.0.1
 */

/**
 * Output generator to aid debugging.
 *
 * @since 1.0.0
 * @return void
 */
function generator() {
	echo "\n\n" . '<!-- ' . ElectroSuite_Reseller()->name . ' Version -->' . "\n" . '<meta name="generator" content="' . esc_attr( ElectroSuite_Reseller()->name ) .' ' . esc_attr( ElectroSuite_Reseller()->version ) . '" />' . "\n\n";
}

?>