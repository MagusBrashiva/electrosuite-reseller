<?php
/**
 * ElectroSuite Reseller Admin Main Page Tab Base Class
 *
 * Provides a structure for individual tab content classes.
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab
 */
abstract class ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = '';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = '';

	/**
	 * Constructor. Hooks the output method.
	 */
	public function __construct() {
        if ( method_exists( $this, 'output' ) ) {
		    add_action( 'electrosuite_reseller_main_page_tab_' . $this->id, array( $this, 'output' ) );
        }
        // Hook for handling actions if the method exists in the child class
        if ( method_exists( $this, 'handle_actions' ) ) {
            add_action( 'admin_init', array( $this, 'handle_actions' ) );
        }
	}

	/**
	 * Get the tab ID.
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the tab label.
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

    /**
     * Output the content for the tab.
     * Child classes must implement this method.
     */
    abstract public function output();

}

} // end if class exists.

?>