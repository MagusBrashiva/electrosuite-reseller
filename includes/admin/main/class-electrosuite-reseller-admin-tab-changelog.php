<?php
/**
 * ElectroSuite Reseller Admin Main Page: Changelog Tab
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ensure the base class is loaded
if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {
    include_once( 'class-electrosuite-reseller-admin-tab.php' );
}

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab_Changelog' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab_Changelog Class
 */
class ElectroSuite_Reseller_Admin_Tab_Changelog extends ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = 'changelog';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = ''; // Defined in main controller

	/**
	 * Output the content for the Changelog tab.
     * Contains logic previously in Welcome::changelog_screen().
	 */
	public function output() {
        // Define text domain for translations
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';
		?>
        <?php // --- START: Content from Welcome::changelog_screen --- ?>
			<p><?php esc_html_e( 'Bulletpoint your changelog like so.', $text_domain ); ?></p>

			<div class="changelog point-releases">
				<h3><?php printf( esc_html__( 'Version %s', $text_domain ), '1.0.0' ); // Example version ?></h3>
				<p><strong><?php printf( esc_html__( 'First version of the %s.', $text_domain ), esc_html( ElectroSuite_Reseller()->name ) ); ?></strong></p>
                <?php // TODO: Add actual changelog items here, perhaps dynamically ?>
			</div>
        <?php // --- END: Content from Welcome::changelog_screen --- ?>
		<?php
	}

} // end class

} // end if class exists

// Instantiate the class so its hooks are registered
new ElectroSuite_Reseller_Admin_Tab_Changelog();

?>