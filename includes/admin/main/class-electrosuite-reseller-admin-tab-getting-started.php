<?php
/**
 * ElectroSuite Reseller Admin Main Page: Getting Started Tab
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ensure the base class is loaded if not using an autoloader
if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {
    include_once( 'class-electrosuite-reseller-admin-tab.php' );
}

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab_GettingStarted' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab_GettingStarted Class
 */
class ElectroSuite_Reseller_Admin_Tab_GettingStarted extends ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = 'getting-started';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = ''; // Label is defined in the main page controller's $tabs array

    /**
     * Constructor. Hooks output and action handler.
     */
    public function __construct() {
        parent::__construct(); // Call parent constructor to hook output

        // Hook handler for button action
        add_action( 'admin_init', array( $this, 'handle_actions' ) );
    }



	/**
	 * Output the content for the Getting Started tab.
     * Contains logic previously in Welcome::about_screen() and Welcome::maybe_display_theme_check_notice().
	 */
	public function output() {

        // Define text domain for translations
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';

        // Display feedback message if redirected after button click
        if ( isset( $_GET['message'] ) && $_GET['message'] === 'setup_complete' ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Setup marked as complete. The default tab is now System Status.', $text_domain ) . '</p></div>';
        }

        // Call the theme check notice display first
        $this->maybe_display_theme_check_notice();

        // Define text domain for translations
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';
		?>
        <?php // --- START: Content from Welcome::about_screen (excluding intro) --- ?>
			<p class="about-description"><?php esc_html_e( 'Use this page to show what the plugin does or what features you have added since your first release. Replace the placeholder images with screenshots of your plugin. You can even make the screenshots linkable to show a larger screenshot with or without caption or play an embedded video. It\'s all up to you.', $text_domain ); ?></p>

			<div>
				<h3><?php esc_html_e( 'Three Columns with Screenshots', $text_domain ); ?></h3>
				<div class="electrosuite-reseller-feature feature-section col three-col">
					<div>
						<a href="http://placekitten.com/720/480" data-rel="prettyPhoto[gallery]"><img src="http://placekitten.com/300/250" alt="<?php esc_attr_e( 'Screenshot Title', $text_domain ); ?>" style="width: 99%; margin: 0 0 1em;"></a>
						<h4><?php esc_html_e( 'Title of Feature or New Changes', $text_domain ); ?></h4>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed aliquet diam a facilisis eleifend. Cras ac justo felis. Mauris faucibus, orci eu blandit fermentum, lorem nibh sollicitudin mi, sit amet interdum metus urna ut lacus.</p>
					</div>
					<div>
						<a href="http://placekitten.com/980/640" data-rel="prettyPhoto[gallery]" title="<?php esc_attr_e( 'You can add captions to your screenshots.', $text_domain ); ?>"><img src="http://placekitten.com/300/250" alt="" style="width: 99%; margin: 0 0 1em;"></a>
						<h4><?php esc_html_e( 'Title of Feature or New Changes', $text_domain ); ?></h4>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed aliquet diam a facilisis eleifend. Cras ac justo felis. Mauris faucibus, orci eu blandit fermentum, lorem nibh sollicitudin mi, sit amet interdum metus urna ut lacus.</p>
					</div>
					<div class="last-feature">
						<a href="http://vimeo.com/88671403" data-rel="prettyPhoto" title="<?php esc_attr_e( 'Or add captions on your videos.', $text_domain ); ?>"><img src="http://placekitten.com/300/250" alt="<?php esc_attr_e( 'Video Title', $text_domain ); ?>" style="width: 99%; margin: 0 0 1em;"></a>
						<h4><?php esc_html_e( 'Title of Feature or New Changes', $text_domain ); ?></h4>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed aliquet diam a facilisis eleifend. Cras ac justo felis. Mauris faucibus, orci eu blandit fermentum, lorem nibh sollicitudin mi, sit amet interdum metus urna ut lacus.</p>
					</div>
				</div>
			</div>

            <hr>

            <h3><?php esc_html_e( 'Manual Setup Completion', $text_domain ); ?></h3>
            <p><?php esc_html_e( 'If you have configured the necessary settings (e.g., API keys), you can manually mark the setup as complete. This will change the default tab shown when accessing the plugin page.', $text_domain ); ?></p>
            <?php if ( get_option( 'electrosuite_reseller_setup_complete', false ) ) : ?>
                <p><strong><?php esc_html_e( 'Setup is currently marked as complete.', $text_domain ); ?></strong></p>
                <?php // Optionally add a button here later to UNSET the flag for testing, e.g.: ?>
                <?php /*
                <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . rawurlencode( ELECTROSUITE_RESELLER_PAGE ) . '&tab=' . $this->id . '&action=mark_setup_incomplete' ) ); // Need to handle this action in handle_actions() ?>">
                    <?php wp_nonce_field( 'esr_mark_setup_incomplete_nonce' ); ?>
                    <button type="submit" class="button"><?php esc_html_e( 'Mark Setup as Incomplete (for testing)', $text_domain ); ?></button>
                </form>
                */ ?>
            <?php else : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . rawurlencode( ELECTROSUITE_RESELLER_PAGE ) . '&tab=' . $this->id . '&action=mark_setup_complete' ) ); ?>">
                    <?php wp_nonce_field( 'esr_mark_setup_complete_nonce' ); ?>
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Mark Setup as Complete', $text_domain ); ?></button>
                </form>
            <?php endif; ?>


			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => ELECTROSUITE_RESELLER_PAGE . '-settings' ), 'admin.php' ) ) ); ?>"><?php printf( esc_html__( 'Go to %s Settings', $text_domain ), esc_html( ElectroSuite_Reseller()->name ) ); ?></a>
			</div>
        <?php // --- END: Content from Welcome::about_screen --- ?>
		<?php
	}


    /**
     * Checks theme compatibility and displays a notice if needed.
     * Moved from Welcome class.
     *
     * @since 0.0.1
     * @access private
     */
    private function maybe_display_theme_check_notice() {
        // Check dependencies before executing theme check
        if ( ! function_exists('ElectroSuite_Reseller') ) {
            return; // Cannot proceed without main plugin functions
        }

        $template = get_option( 'template' );
        // Define paths relative to the main plugin file for robustness
        $base_path = dirname( ELECTROSUITE_RESELLER_FILE );
        $theme_support_file = $base_path . '/admin/electrosuite-reseller-theme-support.php';
        $notice_view_file = $base_path . '/admin/views/html-notice-theme-support.php'; // Assuming view exists

        if ( file_exists( $theme_support_file ) ) {
            // Define $themes_supported before including the file to avoid notices if it's empty/malformed
            $themes_supported = array();
            include( $theme_support_file ); // This should define/populate $themes_supported

            // Ensure $themes_supported is an array after include
            if ( ! is_array($themes_supported) ) {
                $themes_supported = array();
            }

            // Check for theme support declaration OR if theme is in the known supported list
            if ( ! current_theme_supports( 'electrosuite_reseller' ) && ! in_array( $template, $themes_supported ) ) {

                // Check if user dismissed check for this theme previously
                if ( get_option( 'electrosuite_reseller_theme_support_check' ) !== $template ) {
                    // Display the notice inline if the view file exists
                    if ( file_exists( $notice_view_file ) ) {
                        // The notice view file likely contains the necessary HTML structure and dismiss link
                        echo '<div class="electrosuite-reseller-theme-notice-inline notice notice-warning" style="margin: 15px 0;">'; // Wrap in standard WP notice classes
                        include( $notice_view_file ); // Include the notice HTML structure
                        echo '</div>';
                    } else {
                        // Fallback basic notice if view file is missing
                        ?>
                        <div class="notice notice-warning" style="margin: 15px 0;">
                            <p><?php printf( esc_html__( 'Your current theme does not declare %1$s support. Issues may occur. Please see the %2$sdocumentation%3$s.', 'electrosuite-reseller' ), '<code>electrosuite_reseller</code>', '<a href="' . esc_url( ElectroSuite_Reseller()->doc_url ) . '" target="_blank">', '</a>' ); ?></p>
                        </div>
                        <?php
                    }
                }
            }
        } // end file_exists theme_support_file check
    } // End maybe_display_theme_check_notice

    
    /**
     * Handle actions specific to the Getting Started tab, like marking setup complete.
     * Hooked to admin_init.
     */
    public function handle_actions() {
        // Check if the specific action was submitted from our page/tab
        if ( isset( $_GET['page'], $_GET['tab'], $_GET['action'] ) &&
             $_GET['page'] === ELECTROSUITE_RESELLER_PAGE &&
             $_GET['tab'] === $this->id &&
             $_GET['action'] === 'mark_setup_complete' &&
             isset( $_POST['_wpnonce'] ) && // Check nonce was sent
             wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'esr_mark_setup_complete_nonce' ) &&
             current_user_can( 'manage_options' ) // Check capability
           )
        {
            update_option( 'electrosuite_reseller_setup_complete', true );

            // Redirect back to prevent re-submission and show updated state
            $redirect_url = admin_url( 'admin.php?page=' . rawurlencode( ELECTROSUITE_RESELLER_PAGE ) . '&tab=' . $this->id . '&message=setup_complete' );
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }


} // end class

} // end if class exists

// Instantiate the class so its hooks are registered
new ElectroSuite_Reseller_Admin_Tab_GettingStarted();
?>