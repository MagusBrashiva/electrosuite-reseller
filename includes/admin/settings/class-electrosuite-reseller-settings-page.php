<?php
/**
 * ElectroSuite Reseller Settings Page/Tab
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Settings_Page' ) ) {

/**
 * ElectroSuite_Reseller_Settings_Page
 */
class ElectroSuite_Reseller_Settings_Page {

	protected $id    = '';
	protected $label = '';

	/**
	 * Add this page to settings
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Add this settings page to plugin menu
	 */
	public function add_menu_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		return array();
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {
		return array();
	}

	/**
	 * Output sections
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) )
			return;

		$output = '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			$output .= '<li><a href="';

			$section_link = admin_url( 'admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) );

			// If this section is default then remove the variable from this link.
			if( $id == '' ) {
				$section_link = remove_query_arg( 'section', $section_link );
			}

			$output .= $section_link;

			$output .= '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		$output .= '</ul><br class="clear" />';
		echo $output;
	}

	/**
	 * Output the settings
	 */
	public function output() {
		$settings = $this->get_settings();

		ElectroSuite_Reseller_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_tab, $current_section;

		$settings = $this->get_settings();

		ElectroSuite_Reseller_Admin_Settings::save_fields( $settings, $current_tab, $current_section );

		if ( $current_section ) {
			do_action( 'electrosuite_reseller_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

} // end if class exists.

?>