<?php
/**
 * ElectroSuite Reseller Admin Main Page: Credits Tab
 *
 * @package     ElectroSuite Reseller/Admin/Main
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Ensure the base class is loaded
if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab' ) ) {
    include_once( 'class-electrosuite-reseller-admin-tab.php' );
}

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Tab_Credits' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Tab_Credits Class
 */
class ElectroSuite_Reseller_Admin_Tab_Credits extends ElectroSuite_Reseller_Admin_Tab {

	/**
	 * Tab ID.
	 * @var string
	 */
	protected $id = 'credits';

	/**
	 * Tab Label.
	 * @var string
	 */
	protected $label = ''; // Defined in main controller

	/**
	 * Output the content for the Credits tab.
     * Contains logic previously in Welcome::credits_screen().
	 */
	public function output() {
        // Define text domain and constants/variables needed
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';
        $plugin_name = function_exists('ElectroSuite_Reseller') ? ElectroSuite_Reseller()->name : 'ElectroSuite Reseller';
        $repo_url = defined('GITHUB_REPO_URL') ? GITHUB_REPO_URL : '#';
        $plugin_url = function_exists('ElectroSuite_Reseller') ? ElectroSuite_Reseller()->plugin_url() : '';
        $translations_page_url = admin_url( 'admin.php?page=' . rawurlencode( ELECTROSUITE_RESELLER_PAGE ) . '&tab=translations' );

		?>
        <?php // --- START: Content from Welcome::credits_screen --- ?>
			<p class="about-description"><?php printf( esc_html__( 'The %1$s is developed and maintained by "Sébastien Dumont". Are you a passionate individual, would you like to give your support and see your name here? %2$sContribute to %1$s%3$s.', $text_domain ), $plugin_name, '<a href="' . esc_url( $repo_url . '/blob/master/CONTRIBUTING.md' ) . '" target="_blank">', '</a>' ); ?></p>

			<div class="electrosuite-reseller-feature feature-section col two-col">
				<div>
					<h2>Sébastien Dumont</h2>
					<h4 style="font-weight:0; margin-top:0"><?php esc_html_e( 'Project Lead & Developer', $text_domain ); ?></h4>
					<p><img style="float:left; margin: 0 15px 0 0;" src="<?php echo esc_url( $plugin_url . '/assets/images/sebd.jpg' ); ?>" width="100" height="100" /><?php printf( esc_html__( '%s has been developing plugins for WordPress since 2009. He is a freelance Web and WordPress developer.', 'Sébastien'), $text_domain ); ?></p>
					<p><a href="http://www.sebastiendumont.com" target="_blank"><?php printf( esc_html__( 'View %s’s website', 'Sébastien' ), $text_domain ); ?></a></p>
				</div>
				<div class="last-feature">
					<h2>Francois-Xavier Bénard</h2>
					<h4 style="font-weight:0; margin-top:0"><?php esc_html_e( 'Translation Manager, CEO of WP-Translations.org', $text_domain ); ?></h4>
					<p><img style="float:left; margin: 0 15px 0 0;" src="<?php echo esc_url( $plugin_url . '/assets/images/fxbenard.jpg' ); ?>" width="100" height="100" />Translation is my hobby, make it a living is my plan. I translate but also check and code the missing i18n() functions in your plugins or themes. I run a FREE WP Community of translators on Transifex. So if you need someone who cares about quality work, get in touch. Many developers are already trusting me, Seb of course but also Yoast, Pippin and the Mailpoet Team.</p>
					<p><a href="http://wp-translations.org" target="_blank"><?php printf( esc_html__( 'View %s’s website', 'Francois' ), $text_domain ); ?></a></p>
				</div>
			</div>

			<hr class="clear" />

			<h4 class="wp-people-group"><?php esc_html_e( 'Contributors' , $text_domain ); ?></h4><span style="color:#aaa; float:right; position:relative; top:-40px;"><?php esc_html_e( 'These contributors are fetched from the GitHub repository.', $text_domain ); ?></span>

			<?php echo $this->render_contributors(); // Call helper method within this class ?>

			<hr class="clear">

			<h4 class="wp-people-group"><?php esc_html_e( 'Translators' , $text_domain ); ?></h4><span style="color:#aaa; float:right; position:relative; top:-40px;"><?php printf( esc_html__( 'These translators are fetched from the Transifex project for %s.', $text_domain ), $plugin_name ); ?></span>

			<p class="about-description"><?php printf( esc_html__( '<strong>%s</strong> has been kindly translated into several other languages thanks to the WordPress community.', $text_domain ), $plugin_name ); ?></p>
			<?php
			// Display all translators on the project with a link to their profile.
            // Ensure this function is available or move its logic here if needed.
            if ( function_exists( 'transifex_display_translators' ) ) {
			    transifex_display_translators();
            } else {
                 echo '<p><em>' . esc_html__( 'Transifex display function not found.', $text_domain ) . '</em></p>';
            }
			?>
			<p><?php printf( esc_html__( 'Is your name not listed? Then how about taking part in helping with the translation of this plugin. See the list of %slanguages to translate%s.', $text_domain ), '<a href="' . esc_url( $translations_page_url ) . '">', '</a>' ); ?></p>

			<hr class="clear">

			<h4 class="wp-people-group"><?php esc_html_e( 'External Libraries' , $text_domain ); ?></h4>
			<p class="wp-credits-list">
			<a href="http://jquery.com/" target="_blank">jQuery</a>,
			<a href="http://jqueryui.com/" target="_blank">jQuery UI</a>,
			<a href="http://malsup.com/jquery/block/" target="_blank">jQuery Block UI</a>,
			<a href="https://github.com/harvesthq/chosen" target="_blank">jQuery Chosen</a>,
			<a href="https://github.com/carhartl/jquery-cookie" target="_blank">jQuery Cookie</a>,
			<a href="http://code.drewwilson.com/entry/tiptip-jquery-plugin" target="_blank">jQuery TipTip</a> and
			<a href="http://www.no-margin-for-errors.com/projects/prettyPhoto-jquery-lightbox-clone/" target="_blank">prettyPhoto</a>
			</p>
        <?php // --- END: Content from Welcome::credits_screen --- ?>
		<?php
	}


    /**
	 * Render Contributors List - MOVED from Welcome class
	 *
     * @access private
	 * @return string HTML formatted list of contributors.
	 */
	private function render_contributors() {
		$contributors = $this->get_contributors();
        $text_domain = defined('ELECTROSUITE_RESELLER_TEXT_DOMAIN') ? ELECTROSUITE_RESELLER_TEXT_DOMAIN : 'electrosuite-reseller';

		if ( empty( $contributors ) ) {
            return '<p><em>' . esc_html__( 'Could not retrieve contributors list from GitHub.', $text_domain ) . '</em></p>';
        }

		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
            if ( ! is_object($contributor) || ! isset($contributor->login) ) continue; // Basic validation

			// Get details about this contributor.
			$contributor_details = $this->get_individual_contributor( $contributor->login );

            $name_display = isset( $contributor_details->name ) && $contributor_details->name ? esc_html( $contributor_details->name ) : esc_html( $contributor->login );
            $profile_url = esc_url( 'https://github.com/' . $contributor->login );
            $avatar_url = isset($contributor->avatar_url) ? esc_url($contributor->avatar_url) : '';
            $blog_url = isset( $contributor_details->blog ) && $contributor_details->blog ? esc_url( $contributor_details->blog ) : '';


			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf( '<a href="%s" target="_blank" title="%s">',
				$profile_url,
				sprintf( esc_attr__( 'View %s\'s GitHub Profile', $text_domain ), $name_display )
			);
            if ( $avatar_url ) {
			    $contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', $avatar_url, esc_attr( $name_display ) );
            } else {
                 $contributor_list .= '<span class="wp-person-placeholder-image"></span>'; // Placeholder if no avatar
            }
			$contributor_list .= '</a>';


			$contributor_list .= '<span class="wp-person-name">' . sprintf( '<strong><a href="%s" target="_blank">%s</a></strong>', $profile_url, $name_display ) . '</span>';

            // Removed separate Name/Username display, combined above.

			if( $blog_url ) {
				$contributor_list .= '<span class="wp-person-website">' . sprintf( '<a href="%s" target="_blank">%s</a>', $blog_url, esc_html__( 'Website', $text_domain ) ) . '</span>';
			}

			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}


	/**
	 * Retrieve list of contributors from GitHub. - MOVED from Welcome class
	 *
	 * @access private
	 * @return mixed Array of contributor objects or empty array on failure.
	 */
	private function get_contributors() {
        // Use a more specific transient name
		$transient_name = 'esr_contributors_list';
        $contributors = get_transient( $transient_name );

		if ( false !== $contributors ) {
			return $contributors;
		}

        // TODO: Update repo URL if needed, currently hardcoded from original boilerplate
		$response = wp_remote_get( 'https://api.github.com/repos/seb86/WordPress-Plugin-Boilerplate/contributors', array( 'sslverify' => false ) ); // Consider enabling sslverify if possible

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) ) {
			return array();
		}

        // Cache for 1 hour
		set_transient( $transient_name, $contributors, HOUR_IN_SECONDS );

		return $contributors;
	}


	/**
	 * Retrieve details about the single contributor from GitHub. - MOVED from Welcome class
	 *
     * @param string $username GitHub username.
	 * @access private
	 * @return mixed Object with contributor details or empty array on failure.
	 */
	private function get_individual_contributor( $username ) {
        // Use a more specific transient name
		$transient_name = 'esr_contributor_' . sanitize_key($username);
        $contributor = get_transient( $transient_name );

		if ( false !== $contributor ) {
			return $contributor;
		}

		$response = wp_remote_get( 'https://api.github.com/users/' . urlencode($username), array( 'sslverify' => false ) ); // Consider enabling sslverify

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return array(); // Return empty array on failure
		}

		$contributor = json_decode( wp_remote_retrieve_body( $response ) );

        // Basic validation
        if ( ! is_object($contributor) ) {
            return array();
        }

        // Cache for 1 hour
		set_transient( $transient_name, $contributor, HOUR_IN_SECONDS );

		return $contributor;
	}


} // end class

} // end if class exists

// Instantiate the class so its hooks are registered
new ElectroSuite_Reseller_Admin_Tab_Credits();

?>