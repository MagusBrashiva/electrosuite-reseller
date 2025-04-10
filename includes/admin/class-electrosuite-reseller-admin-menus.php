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
			// add_filter( 'parent_file', array( &$this, 'menu_parent_file' ) ); // Keep commented if not used			
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
	 * Add menu items following standard WordPress practice.
	 */
	public function admin_menu() {
		global $menu, $wp_version;

		// Add separator
		if ( current_user_can( 'manage_options' ) && version_compare( $wp_version, '3.8', '>=' ) ) {
			// Ensure separator doesn't already exist at this specific position
            // Using a slightly different position ensures it's distinct if other plugins use 55.6/55.7
			if ( ! isset($menu['55.7']) || $menu['55.7'][2] !== 'separator-electrosuite-reseller' ) {
                $menu['55.7'] = array( '', 'read', 'separator-electrosuite-reseller', '', 'wp-menu-separator electrosuite-reseller' );
            }
		}

        // 1. Add the top-level menu page.
        // The callback here is less critical now, but keeping it consistent.
		$main_hook = add_menu_page(
            ElectroSuite_Reseller()->title_name,
            ElectroSuite_Reseller()->menu_name,
            'manage_options', // Capability
            ELECTROSUITE_RESELLER_PAGE, // Main slug
            array( $this, 'electrosuite_reseller_page' ), // Callback
            null,
            '55.6' // Position
        );

        // 2. Add "Getting Started" as the FIRST submenu item.
        // Its slug matches the parent, making it the default landing page.
        add_submenu_page(
            ELECTROSUITE_RESELLER_PAGE,                                     // Parent slug
            ElectroSuite_Reseller()->title_name,                          // Page title
            __( 'Getting Started', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),     // VISIBLE Menu title
            'manage_options',                                             // Capability
            ELECTROSUITE_RESELLER_PAGE,                                     // Menu slug *matches* parent slug
            array( $this, 'electrosuite_reseller_page' )                    // Callback (main page controller)
        );

        // 3. Add Settings submenu (appears second)
		$settings_menu_option = isset( ElectroSuite_Reseller()->full_settings_menu ) ? ElectroSuite_Reseller()->full_settings_menu : 'no';
		if( $settings_menu_option == 'no' ) {
			$settings_page = add_submenu_page(
                ELECTROSUITE_RESELLER_PAGE, // Parent slug
                sprintf( __( '%s Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->title_name ), // Page title
                __( 'Settings', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), // Visible Menu title
                'manage_options', // Capability
                ELECTROSUITE_RESELLER_PAGE . '-settings', // Unique slug
                array( $this, 'settings_page' ) // Callback for settings
            );
		}
		else{
			// Keeping original complex logic for multiple settings submenus if enabled
			$settings_page = add_submenu_page( /* ... args ... capability ... */ );
			$settings_submenus = apply_filters( 'electrosuite_reseller_settings_submenu_array', array( /* ... */ ) );
			foreach( $settings_submenus as $tab ) {
                if ( isset($tab['menu_name'], $tab['menu_slug']) ) {
				    $settings_page .= add_submenu_page( /* ... args ... capability ... */ );
                }
			}
		}

        // NOTE: Menu order filters in __construct should remain commented out for this test.
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
	 * Init the Main Plugin page - Calls the static output method of the controller class.
	 */
	public function electrosuite_reseller_page() {
        // Ensure the controller class is loaded if not already (e.g., by the main Admin class includes)
        if ( ! class_exists('ElectroSuite_Reseller_Admin_Page') ) {
             $page_class_file = dirname(__FILE__) . '/class-electrosuite-reseller-admin-page.php';
             if ( file_exists( $page_class_file ) ) {
                 include_once( $page_class_file );
             } else {
                 echo '<div class="wrap"><h2>Error</h2><p>Main page controller class not found.</p></div>';
                 return;
             }
        }

        // Check if the class and static method exist before calling
        if ( class_exists('ElectroSuite_Reseller_Admin_Page') && method_exists('ElectroSuite_Reseller_Admin_Page', 'output') ) {
		    // Call the static output method directly
		    ElectroSuite_Reseller_Admin_Page::output();
        } else {
             // Provide more specific error messages
             if ( class_exists('ElectroSuite_Reseller_Admin_Page') && !method_exists('ElectroSuite_Reseller_Admin_Page', 'output') ) {
                echo '<div class="wrap"><h2>Error</h2><p>Main page output method not found in ElectroSuite_Reseller_Admin_Page class.</p></div>';
             } elseif (!class_exists('ElectroSuite_Reseller_Admin_Page')) {
                echo '<div class="wrap"><h2>Error</h2><p>ElectroSuite_Reseller_Admin_Page class does not exist or was not loaded.</p></div>';
             } else {
                echo '<div class="wrap"><h2>Error</h2><p>Could not display main plugin page.</p></div>';
             }
        }
	}

	/**
	 * Init the settings page
	 */
	public function settings_page() {
		include_once( 'class-electrosuite-reseller-admin-settings.php' );
		ElectroSuite_Reseller_Admin_Settings::output();
	}
	
}

} // end if class exists.

return new ElectroSuite_Reseller_Admin_Menus();

?>