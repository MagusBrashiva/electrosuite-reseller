<?php
/**
 * Setup menus in WP admin.
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Menus' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Menus Class
 */
class ElectroSuite_Reseller_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		global $wp_version;

		// Add menu seperator
		add_action( 'admin_init', array( &$this, 'add_admin_menu_separator' ) );

		/**
		 * If WordPress is version 3.7.1 or less then 
		 * set menu separator the old fashioned way.
		 * If WordPress is version 3.8 or newer then 
		 * filter the menu order and include the menu serparator.
		 */
		if( $wp_version <= '3.7.1' ) {
			add_action( 'admin_menu', array( &$this, 'set_admin_menu_separator' ) );
		}
		else{
			add_action( 'admin_head', array( &$this, 'menu_highlight' ) );
			add_filter( 'menu_order', array( &$this, 'menu_order' ) );
			//add_filter( 'parent_file', array( &$this, 'menu_parent_file' ) );
			add_filter( 'custom_menu_order', array( &$this, 'custom_menu_order' ) );
		}

		// Add menus
		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 9 );
	}

	/**
	 * Add menu seperator
	 */
	public function add_admin_menu_separator( $position ) {
		global $menu;

		if ( current_user_can( ElectroSuite_Reseller()->manage_plugin ) ) {

			$menu[ $position ] = array(
				0	=>	'',
				1	=>	'read',
				2	=>	'separator' . $position,
				3	=>	'',
				4	=>	'wp-menu-separator electrosuite-reseller'
			);

		}
	}

	/**
	 * Set menu seperator
	 */
	public function set_admin_menu_separator() {
		do_action( 'admin_init', 55.6 );
	} // end set_admin_menu_separator

	/**
	 * Add menu items
	 */
	public function admin_menu() {
		global $menu, $electrosuite_reseller, $wp_version;

		if ( current_user_can( ElectroSuite_Reseller()->manage_plugin ) && $wp_version >= '3.8' ) {
			$menu[] = array( '', 'read', 'separator-electrosuite-reseller', '', 'wp-menu-separator electrosuite-reseller' );
		}

		$main_page = add_menu_page( ElectroSuite_Reseller()->title_name, ElectroSuite_Reseller()->menu_name, ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE, array( &$this, 'electrosuite_reseller_page' ), null, '55.6' );

		$settings_menu = isset( ElectroSuite_Reseller()->full_settings_menu ) ? ElectroSuite_Reseller()->full_settings_menu : '';
		if( $settings_menu == '' || $settings_menu == 'no' ) {
			$settings_page = add_submenu_page( ELECTROSUITE_RESELLER_PAGE, sprintf( __( '%s Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), __( 'Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) , ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE . '-settings', array( &$this, 'settings_page' ) );
		}
		else{
			// Load the main settings page.
			$settings_page = add_submenu_page( ELECTROSUITE_RESELLER_PAGE, sprintf( __( '%s Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), __( 'Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) , ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE . '-settings', array( &$this, 'settings_page' ) );

			// List the menu name and slug for each tab to have it's own settings shortcut.
			$settings_submenus = apply_filters( 'electrosuite_reseller_settings_submenu_array', array(
				array(
					'menu_name' => __( 'First Tab', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'menu_slug' => 'tab_one',
				),
				array(
					'menu_name' => __( 'Second Tab', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
					'menu_slug' => 'tab_two',
				)
			) );

			// Each settings tab will create a submenu under the plugin menu.
			foreach( $settings_submenus as $tab ) {
				$settings_page .= add_submenu_page( ELECTROSUITE_RESELLER_PAGE, sprintf( __( '%s Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), $tab['menu_name'], ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE . '-settings&tab=' . $tab['menu_slug'], array( &$this, 'settings_page' ) );
			}
		}

		add_submenu_page( ELECTROSUITE_RESELLER_PAGE, sprintf( __( '%s Status', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), __( 'System Status', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE . '-status', array( &$this, 'status_page' ) );

		add_submenu_page( ELECTROSUITE_RESELLER_PAGE, sprintf( __( '%s Tools', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), __( 'Tools', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE . '-status&tab=tools', array( &$this, 'status_page' ) );

		//add_submenu_page( ELECTROSUITE_RESELLER_PAGE, sprintf( __( '%s Import', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), __( 'Import', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE . '-status&tab=import', array( &$this, 'port_page' ) );

		//add_submenu_page( ELECTROSUITE_RESELLER_PAGE, sprintf( __( '%s Export', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), __( 'Export', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->manage_plugin, ELECTROSUITE_RESELLER_PAGE . '-status&tab=export', array( &$this, 'port_page' ) );

		register_setting( 'electrosuite_reseller_status_settings_fields', 'electrosuite_reseller_status_options' );
	}

	/**
	 * Highlights the correct top level admin menu item.
	 *
	 * @access public
	 * @return void
	 */
	public function menu_highlight() {
		global $menu, $submenu, $parent_file, $submenu_file, $self;

		$to_highlight_types = array( 'tools', 'import', 'export' );

		if ( isset( $_GET['tab'] ) ) {
			if ( in_array( $_GET['tab'], $to_highlight_types ) ) {
				$submenu_file = 'admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '&tab=' . esc_attr( $_GET['tab'] );
				$parent_file  = ELECTROSUITE_RESELLER_PAGE;
			}
		}

		if ( isset( $submenu['electrosuite-reseller'] ) && isset( $submenu['electrosuite-reseller'][1] ) ) {
			$submenu['electrosuite-reseller'][0] = $submenu['electrosuite-reseller'][1];
			unset( $submenu['electrosuite-reseller'][1] );
		}

	}

	/**
	 * Reorder the plugin menu items in admin.
	 *
	 * @param mixed $menu_order
	 * @return array
	 */
	public function menu_order( $menu_order ) {
		// Initialize our custom order array
		$electrosuite_reseller_menu_order = array();

		// Get the index of our custom separator
		$electrosuite_reseller_separator = array_search( 'separator-electrosuite-reseller', $menu_order );

		// Loop through menu order and do some rearranging
		foreach ( $menu_order as $index => $item ) {

			if ( ( ( str_replace( '_', '-', ELECTROSUITE_RESELLER_SLUG ) ) == $item ) ) {
				$electrosuite_reseller_menu_order[] = 'separator-' . str_replace( '_', '-', ELECTROSUITE_RESELLER_SLUG );
				$electrosuite_reseller_menu_order[] = $item;
				$electrosuite_reseller_menu_order[] = 'admin.php?page=' . ELECTROSUITE_RESELLER_PAGE;
				unset( $menu_order[$electrosuite_reseller_separator] );
			}
			elseif ( !in_array( $item, array( 'separator-' . str_replace( '_', '-', ELECTROSUITE_RESELLER_SLUG ) ) ) ) {
				$electrosuite_reseller_menu_order[] = $item;
			}

		}

		// Return menu order
		return $electrosuite_reseller_menu_order;
	}

	public function menu_parent_file( $parent_file ) {
		global $current_screen, $pagenow, $submenu_file;

		switch( $pagenow ) {
			case 'admin.php':
				if( isset( $_GET['tab'] ) ) {
					if( $_GET['tab'] == 'tools' ) {
						$parent_file = 'admin.php?page=' . ELECTROSUITE_RESELLER_SLUG . '&tab=tools';
					}
				}
				break;
			default:
				$parent_file = $parent_file;
				break;
		}

		return $parent_file;
	}

	/**
	 * custom_menu_order
	 * @return bool
	 */
	public function custom_menu_order() {
		if ( ! current_user_can( ElectroSuite_Reseller()->manage_plugin ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Init the Plugin Name page
	 */
	public function electrosuite_reseller_page() {
		$page = include_once( 'class-electrosuite-reseller-admin-page.php' );
		$page->output();
	}

	/**
	 * Init the settings page
	 */
	public function settings_page() {
		include_once( 'class-electrosuite-reseller-admin-settings.php' );
		ElectroSuite_Reseller_Admin_Settings::output();
	}

	/**
	 * Init the status page
	 */
	public function status_page() {
		$page = include( 'class-electrosuite-reseller-admin-status.php' );
		$page->output();
	}

	/**
	 * Init the import and export page
	 */
	public function port_page() {
		$page = include( 'class-electrosuite-reseller-admin-import-export.php' );
		$page->output();
	}

}

} // end if class exists.

return new ElectroSuite_Reseller_Admin_Menus();

?>