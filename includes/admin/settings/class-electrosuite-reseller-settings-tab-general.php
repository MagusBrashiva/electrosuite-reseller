<?php
/**
 * ElectroSuite Reseller General Tab Settings
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Settings_General_Tab' ) ) {

/**
 * ElectroSuite_Reseller_Settings_General_Tab
 */
class ElectroSuite_Reseller_Settings_General_Tab extends ElectroSuite_Reseller_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id 		= 'tab_general';
		$this->label 	= __( 'General', ELECTROSUITE_RESELLER_TEXT_DOMAIN );

		add_filter( 'electrosuite_reseller_settings_submenu_array', array( &$this, 'add_menu_page' ), 20 );
		add_filter( 'electrosuite_reseller_settings_tabs_array', array( &$this, 'add_settings_page' ), 20 );
		add_action( 'electrosuite_reseller_settings_' . $this->id, array( &$this, 'output' ) );
		add_action( 'electrosuite_reseller_settings_save_' . $this->id, array( &$this, 'save' ) );
	}
	
	/**
	 * Save settings
	 */
	public function save() {
		global $current_tab;

		$settings = $this->get_settings();

		ElectroSuite_Reseller_Admin_Settings::save_fields( $settings, $current_tab );
	}
	
	/**
	 * Get settings array
	 *
	 * @return array
	 */
	 // TODO: Ensure defaults are loading properly
	public function get_settings() {

		return apply_filters( 'electrosuite_reseller_' . $this->id . '_settings', array(

			array(
				'title' 	=> __( 'General Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), 
				'type' 		=> 'title', 
				'desc' 		=> '', 
				'id' 		=> $this->id . '_options'
			),

			// Toggle Test Mode
			array(
				'title' 	=> __( 'Test Mode', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc' 		=> __( 'Send API calls to test server to verify setup before going live.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'id' 		=> 'electrosuite_reseller_test_mode',
				'default'	=> 'yes',
				'type' 		=> 'checkbox',
			),
			
			// Select Registrar
			array(
				'title' 	=> __( 'Select API Server', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc' 		=> __( 'Select domain registrar for client purchases.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'id' 		=> 'electrosuite_reseller_server_api',
				'css' 		=> 'min-width:300px;',
				'class' 	=> 'chosen_select',
				'default'	=> 'enom',
				'type' 		=> 'select',
				'options' 	=> array(
					'enom' => __( 'eNom', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'resellerclub'  => __( 'ResellerClub', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'centralnic'  => __( 'CentralNic', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				),
				'desc_tip'	=>  true
			),

			// Pricing Adjustment Mode			
			array(
				'title' 	=> __( 'Price Adjustment Mode', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc' 		=> __( 'Select whether to adjust prices by a fixed value or percentage.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc_tip' 	=> true,
				'id' 		=> 'electrosuite_reseller_price_mode',
				'default'	=> 'percentage',
				'type' 		=> 'radio',
				'options' => array(
								'fixed' => __( 'Fixed Amount ($)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
								'percentage' => __( 'Percentage (%)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				),
			),

			// Pricing Adjustment Value
			array(
				'title' 	=> __( 'Price Adjustment Value', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc' 		=> __( 'Enter the amount to adjust prices. (Percentages MUST be in non-decimal form! 12.5% -> 12.5)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'id' 		=> 'electrosuite_reseller_price_value',
				'type' 		=> 'number',
				'custom_attributes' => array(
					'min' 	=> -20,
					'step' 	=> 0.1
				),
				'css' 		=> 'width:65px;',
				'default'	=> '03',
				'autoload' 	=> false
			),

			array( 'type' => 'sectionend', 'id' => $this->id . '_options'),

		)); // End general settings
	}

}

} // end if class exists

return new ElectroSuite_Reseller_Settings_General_Tab();

?>