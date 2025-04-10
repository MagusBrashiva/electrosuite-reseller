<?php
/**
 * ElectroSuite Reseller Admin Main Page: Freedoms Tab
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ensure the base class is loaded
if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {
    include_once( 'class-electrosuite-reseller-admin-tab.php' );
}

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab_Freedoms' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab_Freedoms Class
 */
class ElectroSuite_Reseller_Admin_Tab_Freedoms extends ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = 'freedoms';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = ''; // Defined in main controller

	/**
	 * Output the content for the Freedoms tab.
     * Contains logic previously in Welcome::freedoms_screen().
	 */
	public function output() {
        // Define text domain for translations
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';
		?>
        <?php // --- START: Content from Welcome::freedoms_screen --- ?>
			<p class="about-description"><?php esc_html_e( 'WordPress Plugin Boilerplate is Free and open source software, built to help speed up plugin development and to be stable and secure for all WordPress versions. Below is a list explaining what you are allowed to do. Enjoy!', $text_domain ); ?></p>

			<ol> <?php // Use ol instead of start="1" ?>
				<li><p><?php esc_html_e( 'You have the freedom to run the program, for any purpose.', $text_domain ); ?></p></li>
				<li><p><?php esc_html_e( 'You have access to the source code, the freedom to study how the program works, and the freedom to change it to make it do what you wish.', $text_domain ); ?></p></li>
				<li><p><?php esc_html_e( 'You have the freedom to redistribute copies of the original program so you can help your neighbor.', $text_domain ); ?></p></li>
				<li><p><?php esc_html_e( 'You have the freedom to distribute copies of your modified versions to others. By doing this you can give the whole community a chance to benefit from your changes.', $text_domain ); ?></p></li>
			</ol>
        <?php // --- END: Content from Welcome::freedoms_screen --- ?>
		<?php
	}

} // end class

} // end if class exists

// Instantiate the class so its hooks are registered
new ElectroSuite_Reseller_Admin_Tab_Freedoms();

?>