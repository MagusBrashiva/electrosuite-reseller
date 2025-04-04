<?php
/**
 * ElectroSuite Reseller Second Tab Settings
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Settings_Second_Tab' ) ) {

/**
 * ElectroSuite_Reseller_Settings_Second_Tab
 */
class ElectroSuite_Reseller_Settings_Second_Tab extends ElectroSuite_Reseller_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id 		= 'tab_two';
		$this->label 	= __( 'Second Tab', ELECTROSUITE_RESELLER_TEXT_DOMAIN );

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
			'' 		=> __( 'Section One', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'two' 	=> __( 'Section Two', ELECTROSUITE_RESELLER_TEXT_DOMAIN )
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
	public function get_settings( $current_section = '' ) {

		if ( $current_section == 'two' ) {

			return apply_filters('electrosuite_reseller_second_tab_settings_section_'.$current_section, array(

				array(
					'title' 	=> __( 'Section Two Options', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), 
					'type' 		=> 'title', 
					'desc' 		=> '', 
					'id' 		=> 'section_two_options'
				),

				array(
					'title' => __( 'Select Page', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> '<br/>' . sprintf( __( 'You can set a description here also.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), admin_url( 'options-permalink.php' ) ),
					'id' 		=> 'electrosuite_reseller_select_single_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'css' 		=> 'min-width:300px;',
					'desc_tip'	=> __( 'You can select or search for a page.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				),

				array(
					'title' => __( 'Select', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'This example shows you options from an array().', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_select_array',
					'class'		=> 'chosen_select',
					'css' 		=> 'min-width:300px;',
					'default'	=> '',
					'type' 		=> 'select',
					'options' => array(
									'yes' => __( 'Yes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
									'no' => __( 'No', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					),
					'desc_tip'	=>  true,
				),

				array(
					'title' => __( 'MultiSelect', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'This example shows you the ability to select multi options from an array().', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_multiselect_array',
					'class'		=> 'chosen_select',
					'css' 		=> 'min-width:300px;',
					'default'	=> '',
					'type' 		=> 'multiselect',
					'options' => array(
									'yes' => __( 'Yes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
									'no' => __( 'No', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					),
					'desc_tip'	=>  true,
				),

				array(
					'title' 	=> __( 'Checkbox', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Checkbox option', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_checkbox',
					'default'	=> 'no',
					'type' 		=> 'checkbox'
				),

				array(
					'title' 	=> __( 'Radio', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Radio option', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc_tip' 	=> true,
					'id' 		=> 'electrosuite_reseller_radio',
					'default'	=> '',
					'type' 		=> 'radio',
					'options' => array(
									'yes' => __( 'Yes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
									'no' => __( 'No', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					),
				),

				array(
					'title' 	=> __( 'Number', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Use this field for numbered options.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_number_option',
					'type' 		=> 'number',
					'custom_attributes' => array(
						'min' 	=> 0,
						'step' 	=> 1
					),
					'css' 		=> 'width:50px;',
					'default'	=> '05',
					'autoload' 	=> false
				),

				array(
					'title' 	=> __( 'Color', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Use this field for color picking.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_color_option',
					'type' 		=> 'color',
					'css' 		=> 'width:70px;',
					'default'	=> '#ffffff',
					'autoload' 	=> false
				),

				array(
					'title' 		=> __( 'Group Checkboxes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Checkbox One', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_group_checkbox_option_one',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'start',
					'autoload' 		=> false
				),

				array(
					'desc' 			=> __( 'Checkbox Two', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_group_checkbox_option_two',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'end',
					'autoload' 		=> false
				),

				array(
					'title' 		=> __( 'Email', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> 'Use this field option to be used for entering an email address only. (HTML 5 Field)',
					'id' 			=> 'electrosuite_reseller_email_option',
					'type' 			=> 'email',
					'default'		=> get_option( 'admin_email' ),
					'autoload' 		=> false
				),

				array(
					'title' 		=> __( 'Password', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> 'Use this field option to be used for entering a password.',
					'id' 			=> 'electrosuite_reseller_password_option',
					'type' 			=> 'password',
					'default'		=> '',
					'autoload' 		=> false
				),

				array(
					'title' 		=> __( 'Image Size', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Use this field option to save multiple settings for an image size', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_image_size_option',
					'css' 			=> '',
					'type' 			=> 'image_width',
					'default'		=> array(
						'width' 		=> '150',
						'height'		=> '150',
						'crop'			=> false
					),
					'desc_tip' 		=> true,
				),

				array( 'type' => 'sectionend', 'id' => 'section_two_options'),

			));

		} else {

			return apply_filters( 'electrosuite_reseller_second_tab_settings', array(

				array(
					'title' 	=> __( 'Section One Options', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), 
					'type' 		=> 'title', 
					'desc' 		=> '', 
					'id' 		=> 'section_one_options'
				),

				array(
					'title' => __( 'Select Page', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> '<br/>' . sprintf( __( 'You can set a description here also.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), admin_url( 'options-permalink.php' ) ),
					'id' 		=> 'electrosuite_reseller_select_single_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'css' 		=> 'min-width:300px;',
					'desc_tip'	=> __( 'You can select or search for a page.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
				),

				array(
					'title' => __( 'Select', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'This example shows you options from an array().', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_select_array',
					'class'		=> 'chosen_select',
					'css' 		=> 'min-width:300px;',
					'default'	=> '',
					'type' 		=> 'select',
					'options' => array(
									'yes' => __( 'Yes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
									'no' => __( 'No', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					),
					'desc_tip'	=>  true,
				),

				array(
					'title' => __( 'MultiSelect', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'This example shows you the ability to select multi options from an array().', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_multiselect_array',
					'class'		=> 'chosen_select',
					'css' 		=> 'min-width:300px;',
					'default'	=> '',
					'type' 		=> 'multiselect',
					'options' => array(
									'yes' => __( 'Yes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
									'no' => __( 'No', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					),
					'desc_tip'	=>  true,
				),

				array(
					'title' 	=> __( 'Checkbox', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Checkbox option', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_checkbox',
					'default'	=> 'no',
					'type' 		=> 'checkbox'
				),

				array(
					'title' 	=> __( 'Radio', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Radio option', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc_tip' 	=> true,
					'id' 		=> 'electrosuite_reseller_radio',
					'default'	=> '',
					'type' 		=> 'radio',
					'options' => array(
									'yes' => __( 'Yes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
									'no' => __( 'No', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					),
				),

				array(
					'title' 	=> __( 'Number', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Use this field for numbered options.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_number_option',
					'type' 		=> 'number',
					'custom_attributes' => array(
						'min' 	=> 0,
						'step' 	=> 1
					),
					'css' 		=> 'width:50px;',
					'default'	=> '05',
					'autoload' 	=> false
				),

				array(
					'title' 	=> __( 'Color', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 		=> __( 'Use this field for color picking.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 		=> 'electrosuite_reseller_color_option',
					'type' 		=> 'color',
					'css' 		=> 'width:70px;',
					'default'	=> '#ffffff',
					'autoload' 	=> false
				),

				array(
					'title' 		=> __( 'Group Checkboxes', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Checkbox One', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_group_checkbox_option_one',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'start',
					'autoload' 		=> false
				),

				array(
					'desc' 			=> __( 'Checkbox Two', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_group_checkbox_option_two',
					'default'		=> 'yes',
					'type' 			=> 'checkbox',
					'checkboxgroup' => 'end',
					'autoload' 		=> false
				),

				array(
					'title' 		=> __( 'Email', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> 'Use this field option to be used for entering an email address only. (HTML 5 Field)',
					'id' 			=> 'electrosuite_reseller_email_option',
					'type' 			=> 'email',
					'default'		=> get_option( 'admin_email' ),
					'autoload' 		=> false
				),

				array(
					'title' 		=> __( 'Password', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> 'Use this field option to be used for entering a password.',
					'id' 			=> 'electrosuite_reseller_password_option',
					'type' 			=> 'password',
					'default'		=> '',
					'autoload' 		=> false
				),

				array(
					'title' 		=> __( 'Image Size', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'desc' 			=> __( 'Use this field option to save multiple settings for an image size', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'id' 			=> 'electrosuite_reseller_image_size_option',
					'css' 			=> '',
					'type' 			=> 'image_width',
					'default'		=> array(
						'width' 		=> '150',
						'height'		=> '150',
						'crop'			=> false
					),
					'desc_tip' 		=> true,
				),

				array( 'type' => 'sectionend', 'id' => 'section_one_options'),

			));
		}
	}
}

} // end if class exists

return new ElectroSuite_Reseller_Settings_Second_Tab();

?>