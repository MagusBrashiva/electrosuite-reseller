<?php
/**
 * ElectroSuite Reseller Admin Main Page: Translations Tab
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ensure the base class is loaded
if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {
    include_once( 'class-electrosuite-reseller-admin-tab.php' );
}

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab_Translations' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab_Translations Class
 */
class ElectroSuite_Reseller_Admin_Tab_Translations extends ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = 'translations';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = ''; // Defined in main controller

	/**
	 * Output the content for the Translations tab.
     * Contains logic previously in Welcome::translations_screen().
	 */
	public function output() {
        // Define text domain and constants/variables needed
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';
        $plugin_name = function_exists('ElectroSuite_Reseller') ? ElectroSuite_Reseller()->name : 'ElectroSuite Reseller';
        $transifex_url = defined('TRANSIFEX_PROJECT_URL') ? TRANSIFEX_PROJECT_URL : '#';

		?>
        <?php // --- START: Content from Welcome::translations_screen --- ?>
			<p class="about-description"><?php printf( esc_html__( 'Translations currently in progress and completed for %1$s. %2$sView more on %3$s%4$s.', $text_domain ), $plugin_name, '<a href="' . esc_url( $transifex_url ) . '" target="_blank">', 'Transifex', '</a>' ); ?></p>

			<?php
            // Display translation progress stats.
            // Ensure this function is available or move its logic here if needed.
            if ( function_exists( 'transifex_display_translation_progress' ) ) {
			    transifex_display_translation_progress();
            } else {
                 echo '<p><em>' . esc_html__( 'Transifex display function not found.', $text_domain ) . '</em></p>';
            }
            ?>
        <?php // --- END: Content from Welcome::translations_screen --- ?>
		<?php
	}

} // end class

} // end if class exists

// Instantiate the class so its hooks are registered
new ElectroSuite_Reseller_Admin_Tab_Translations();

?>