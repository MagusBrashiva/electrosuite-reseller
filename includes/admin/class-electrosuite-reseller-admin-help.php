<?php
/**
 * Help is provided for this plugin on the plugin pages.
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Help' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Help Class
 */
class ElectroSuite_Reseller_Admin_Help {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'current_screen', array( &$this, 'add_tabs' ), 50 );
	}

	/**
	 * Add help tabs
	 */
	public function add_tabs() {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, electrosuite_reseller_get_screen_ids() ) )
			return;

		$screen->add_help_tab( array(
			'id'	=> 'electrosuite_reseller_docs_tab',
			'title'	=> __( 'Documentation', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'content'	=>

				'<p>' . sprintf( __( 'Thank you for using %s :) Should you need help using or extending %s please read the documentation.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->name, ElectroSuite_Reseller()->name ) . '</p>' .

				'<p><a href="' . ElectroSuite_Reseller()->doc_url . '" class="button button-primary">' . sprintf( __( '%s Documentation', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->name ) . '</a> <!--a href="#" class="button">' . __( 'Restart Tour', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</a--></p>'

		) );

		$screen->add_help_tab( array(
			'id'	=> 'electrosuite_reseller_support_tab',
			'title'	=> __( 'Support', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'content'	=>

				'<p>' . sprintf( __( 'After <a href="%s">reading the documentation</a>, for further assistance you can use the <a href="%s">community forum</a>.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->doc_url, ElectroSuite_Reseller()->wp_plugin_support_url, __( 'Company Name' , ELECTROSUITE_RESELLER_TEXT_DOMAIN ) ) . '</p>' .

				'<p>' . __( 'Before asking for help we recommend checking the status page to identify any problems with your configuration.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</p>' .

				'<p><a href="' . admin_url('admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-status') . '" class="button button-primary">' . __( 'System Status', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</a> <a href="' . ElectroSuite_Reseller()->wp_plugin_support_url . '" class="button">' . __( 'Community Support', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</a>'

		) );

		$screen->add_help_tab( array(
			'id'	=> 'electrosuite_reseller_bugs_tab',
			'title'	=> __( 'Found a bug?', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'content'	=>

				'<p>' . sprintf( __( 'If you find a bug within <strong>%s</strong> you can create a ticket via <a href="%s">Github issues</a>. Ensure you read the <a href="%s">contribution guide</a> prior to submitting your report. Be as descriptive as possible and please include your <a href="%s">system status report</a>.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->name, GITHUB_REPO_URL . 'issues?state=open', GITHUB_REPO_URL . 'blob/master/CONTRIBUTING.md', admin_url( 'admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-status' ) ) . '</p>' .

				'<p><a href="' . GITHUB_REPO_URL . 'issues?state=open" class="button button-primary">' . __( 'Report a bug', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</a> <a href="' . admin_url('admin.php?page=' . ELECTROSUITE_RESELLER_PAGE . '-status') . '" class="button">' . __( 'System Status', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</a></p>'

		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</strong></p>' .
			'<p><a href=" ' . ElectroSuite_Reseller()->web_url . ' " target="_blank">' . sprintf( __( 'About %s', ELECTROSUITE_RESELLER_TEXT_DOMAIN ), ElectroSuite_Reseller()->name ) . '</a></p>' .
			'<p><a href=" ' . ElectroSuite_Reseller()->wp_plugin_url . ' " target="_blank">' . __( 'Project on WordPress.org', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</a></p>' .
			'<p><a href="' . GITHUB_REPO_URL . '" target="_blank">' . __( 'Project on Github', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) . '</a></p>'
		);
	}

} // end class.

} // end if class exists.

return new ElectroSuite_Reseller_Admin_Help();

?>