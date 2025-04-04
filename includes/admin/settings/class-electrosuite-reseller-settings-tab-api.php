<?php
/**
 * ElectroSuite Reseller API Tab Settings
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Settings_APIs_Tab' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Settings_APIs_Tab
 */
class ElectroSuite_Reseller_Settings_APIs_Tab extends ElectroSuite_Reseller_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id 		= 'tab_api';
		$this->label 	= __( 'APIs', ELECTROSUITE_RESELLER_TEXT_DOMAIN );

		add_filter( 'electrosuite_reseller_settings_submenu_array', array( &$this, 'add_menu_page' ), 20 );
		add_filter( 'electrosuite_reseller_settings_tabs_array', array( &$this, 'add_settings_page' ), 20 );
		add_action( 'electrosuite_reseller_settings_' . $this->id, array( &$this, 'output' ) );
		add_action( 'electrosuite_reseller_settings_save_' . $this->id, array( &$this, 'save' ) );
		add_action( 'electrosuite_reseller_sections_' . $this->id, array( &$this, 'output_sections' ) );
	}
	
	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' 		=> __( 'eNom', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'two' 	=> __( 'ResellerClub', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'three' => __( 'CentralNic', ELECTROSUITE_RESELLER_TEXT_DOMAIN )
		);

		return $sections;
	}
	
	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		ElectroSuite_Reseller_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_tab, $current_section;

		$settings = $this->get_settings( $current_section );

		ElectroSuite_Reseller_Admin_Settings::save_fields( $settings, $current_tab, $current_section );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	 // TODO: Ensure defaults are loading properly
	 // TODO: Update to load TLDs from API or list
	public function get_settings( $current_section = '' ) {

		if ( $current_section == 'two' ) {

			return apply_filters('electrosuite_reseller_api_tab_settings_section_'.$current_section, array(

				array(
					'title' 	=> __( 'ResellerClub API Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), 
					'type' 		=> 'title', 
					'desc' 		=> '', 
					'id' 		=> 'section_two_options'
				),
				
				array(
					'title' 		=> __( 'ResellerClub Username', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Enter your ResellerClub account Username.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_resellerclub_api_username_option',
					'css' 			=> 'min-width:300px;',
					'type' 			=> 'username',
					'default'		=> '',
					'autoload' 		=> true,
					'desc_tip'	=>  true
				),

				array(
					'title' 		=> __( 'ResellerClub API Key', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Enter your ResellerClub API Key.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_resellerclub_api_key_option',
					'css' 			=> 'min-width:450px;',
					'type' 			=> 'password',
					'default'		=> '',
					'autoload' 		=> false,
					'hidden' 		=> true,
					'desc_tip'	=>  true
				),
				
				// Preferred TLDs
				array(
					'title' 		=> __( 'Available TLDs', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( '.com', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_resellerclub_tlds_option_one',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'start',
					'autoload' 		=> false
				),				
				array(
					'desc' 			=> __( '.org', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_resellerclub_tlds_option_two',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => '',
					'autoload' 		=> false
				),
				array(
					'desc' 			=> __( '.net', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_resellerclub_tlds_option_three',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'end',
					'autoload' 		=> false
				),

				array( 'type' => 'sectionend', 'id' => 'section_two_options'),

			));

		} elseif ( $current_section == 'three' ) {
		
			return apply_filters('electrosuite_reseller_api_tab_settings_section_'.$current_section, array(

				array(
					'title' 	=> __( 'CentralNic API Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), 
					'type' 		=> 'title', 
					'desc' 		=> '', 
					'id' 		=> 'section_three_options'
				),
				
				array(
					'title' 		=> __( 'CentralNic Username', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Enter your CentralNic account Username.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_centralnic_api_username_option',
					'css' 			=> 'min-width:300px;',
					'type' 			=> 'username',
					'default'		=> '',
					'autoload' 		=> true,
					'desc_tip'	=>  true
				),

				array(
					'title' 		=> __( 'CentralNic API Key', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Enter your CentralNic API Key.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_centralnic_api_key_option',
					'css' 			=> 'min-width:450px;',
					'type' 			=> 'password',
					'default'		=> '',
					'autoload' 		=> false,
					'hidden' 		=> true,
					'desc_tip'	=>  true
				),
				
				// Preferred TLDs
				array(
					'title' 		=> __( 'Available TLDs', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( '.com', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_centralnic_tlds_option_one',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'start',
					'autoload' 		=> false
				),				
				array(
					'desc' 			=> __( '.org', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_centralnic_tlds_option_two',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => '',
					'autoload' 		=> false
				),
				array(
					'desc' 			=> __( '.net', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_centralnic_tlds_option_three',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'end',
					'autoload' 		=> false
				),

				array( 'type' => 'sectionend', 'id' => 'section_three_options'),

			));

		} else {

			return apply_filters( 'electrosuite_reseller_api_tab_settings', array(

				array(
					'title' 	=> __( 'eNom API Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), 
					'type' 		=> 'title', 
					'desc' 		=> '', 
					'id' 		=> 'section_one_options'
				),

				array(
					'title' 		=> __( 'eNom Username', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Enter your eNom account Username.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_enom_api_username_option',
					'css' 			=> 'min-width:300px;',
					'type' 			=> 'username',
					'default'		=> '',
					'autoload' 		=> true,
					'desc_tip'	=>  true
				),

				array(
					'title' 		=> __( 'eNom API Key', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Enter your eNom API Key.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_enom_api_key_option',
					'css' 			=> 'min-width:450px;',
					'type' 			=> 'password',
					'default'		=> '',
					'autoload' 		=> false,
					'hidden' 		=> true,
					'desc_tip'	=>  true
				),
				
				// Preferred TLDs
				array(
					'title' 		=> __( 'Available TLDs', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( '.com', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_enom_tlds_option_one',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'start',
					'autoload' 		=> false
				),				
				array(
					'desc' 			=> __( '.org', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_enom_tlds_option_two',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => '',
					'autoload' 		=> false
				),
				array(
					'desc' 			=> __( '.net', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_enom_tlds_option_three',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'end',
					'autoload' 		=> false
				),

				array( 'type' => 'sectionend', 'id' => 'section_one_options'),

			));
		}
	}




	/*
	public function get_settings() {

		return apply_filters( 'electrosuite_reseller_' . $this->id . '_settings', array(

			array(
				'title' 	=> __( 'API Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), 
				'type' 		=> 'title', 
				'desc' 		=> '', 
				'id' 		=> $this->id . '_options'
			),


			// Select server API
			array(
				'title' 	=> __( 'Select API Server', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc' 		=> __( 'Select domain registrar to use.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'id' 		=> 'electrosuite_reseller_server_api',
				'css' 		=> 'min-width:300px;',
				'class' 	=> 'chosen_select',
				'default'	=> 'enom',
				'type' 		=> 'select',
				'options' 	=> array(
					'enom' => __( 'eNom', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'resellerclub'  => __( 'ResellerClub', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				),
				'desc_tip'	=>  true
			),
			
			
			// enter registrar Username
			array(
				'title' 		=> __( 'Username', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc' 			=> __( 'Enter your reseller account Username for the selected registrar.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'id' 			=> 'electrosuite_reseller_api_username_option',
				'css' 			=> 'min-width:300px;',
				'type' 			=> 'username',
				'default'		=> '',
				'autoload' 		=> true,
				'desc_tip'	=>  true
			),


			// Switch API key based on API server
			switch(	get_option( 'electrosuite_reseller_server_api' ) ) {
				case 'enom':
					array(
						'title' 		=> __( 'eNom API Key', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
						'desc' 			=> __( 'Enter your reseller account API key for the selected registrar.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
						'id' 			=> 'electrosuite_reseller_enom_api_key_option',
						'css' 			=> 'min-width:450px;',
						'type' 			=> 'password',
						'default'		=> '',
						'autoload' 		=> false,
						'hidden' 		=> true,
						'desc_tip'	=>  true
					);
					break;
				case 'resellerclub':
					array(
						'title' 		=> __( 'ResellerClub API Key', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
						'desc' 			=> __( 'Enter your reseller account API key for the selected registrar.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
						'id' 			=> 'electrosuite_reseller_resellerclub_api_key_option',
						'css' 			=> 'min-width:450px;',
						'type' 			=> 'password',
						'default'		=> '',
						'autoload' 		=> false,
						'hidden' 		=> true,
						'desc_tip'	=>  true
					);
					break;
				default:
					// Add default case if needed
					break;
			},
			

			// enter registrar API key
			array(
				'title' 		=> __( 'API Key', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'desc' 			=> __( 'Enter your reseller account API key for the selected registrar.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				'id' 			=> 'electrosuite_reseller_api_key_option',
				'css' 			=> 'min-width:450px;',
				'type' 			=> 'password',
				'default'		=> '',
				'autoload' 		=> false,
				'hidden' 		=> true,
				'desc_tip'	=>  true
			),

			array( 'type' => 'sectionend', 'id' => $this->id . '_options'),

		)); // End general settings
	}
	*/


}

} // end if class exists

return new ElectroSuite_Reseller_Settings_APIs_Tab();

?>