<?php
/**
 * ElectroSuite Reseller Admin Settings Class.
 *
 * @author 		ElectroSuite
 * @category 	Admin
 * @package 	ElectroSuite Reseller/Admin
 * @version 	0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Admin_Settings' ) ) {

/**
 * ElectroSuite_Reseller_Admin_Settings
 */
class ElectroSuite_Reseller_Admin_Settings {

	private static $settings = array();
	private static $errors   = array();
	private static $messages = array();

	/**
	 * Include the settings page classes
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once( 'settings/class-electrosuite-reseller-settings-page.php' );

			$settings[] = include( 'settings/class-electrosuite-reseller-settings-tab-one.php' );
			$settings[] = include( 'settings/class-electrosuite-reseller-settings-tab-two.php' );
			$settings[] = include( 'settings/class-electrosuite-reseller-settings-tab-api.php' );
			$settings[] = include( 'settings/class-electrosuite-reseller-settings-tab-general.php' );

			self::$settings = apply_filters( 'electrosuite_reseller_get_settings_pages', $settings );
		}
		return self::$settings;
	}

	/**
	 * Save the settings
	 */
	public static function save() {
		global $current_section, $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'electrosuite-reseller-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) );
		}

		// Trigger actions
		do_action( 'electrosuite_reseller_settings_save_' . $current_tab );
		do_action( 'electrosuite_reseller_update_options_' . $current_tab );
		do_action( 'electrosuite_reseller_update_options' );

		self::add_message( __( 'Your settings have been saved.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) );

		do_action( 'electrosuite_reseller_settings_saved' );
	}

	/**
	 * Add a message
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error electrosuite-reseller fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		}
		elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated electrosuite-reseller fade"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main ElectroSuite Reseller settings page in admin.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {
		global $current_section, $current_tab;

		do_action( 'electrosuite_reseller_settings_start' );

		// Changes settings.min.js to settings.js
		wp_enqueue_script( 'electrosuite_reseller_settings', ElectroSuite_Reseller()->plugin_url() . '/assets/js/admin/settings.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris' ), ElectroSuite_Reseller()->version, true );

		wp_localize_script( 'electrosuite_reseller_settings', 'electrosuite_reseller_settings_params', apply_filters( 'electrosuite_reseller_settings_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			)
		) );

		// Include settings pages
		self::get_settings_pages();

		// Get current tab/section
		$current_tab 		= empty( $_GET['tab'] ) ? 'tab_one' : sanitize_text_field( urldecode( $_GET['tab'] ) );
		$current_section 	= empty( $_REQUEST['section'] ) ? '' : sanitize_text_field( urldecode( $_REQUEST['section'] ) );

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			self::save();
		}

		// Add any posted messages
		if ( ! empty( $_GET['electrosuite_reseller_error'] ) ) {
			self::add_error( urldecode( stripslashes( $_GET['electrosuite_reseller_error'] ) ) );
		}

		if ( ! empty( $_GET['electrosuite_reseller_message'] ) ) {
			self::add_message( urldecode( stripslashes( $_GET['electrosuite_reseller_message'] ) ) );
		}

		self::show_messages();

		// Get tabs for the settings page
		$tabs = apply_filters( 'electrosuite_reseller_settings_tabs_array', array() );

		include 'views/html-admin-settings.php';
	}

	/**
	 * Get a setting from the settings API.
	 *
	 * @param mixed $option
	 * @return string
	 */
	public static function get_option( $option_name, $default = '' ) {
		// Array value
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key
			$option_name = current( array_keys( $option_array ) );

			// Get value
			$option_values = get_option( $option_name, '' );

			$key = key( $option_array[ $option_name ] );

			if ( isset( $option_values[ $key ] ) ) {
				$option_value = $option_values[ $key ];
			}
			else {
				$option_value = null;
			}

		// Single value
		} else {
			$option_value = get_option( $option_name, null );
		}

		if ( is_array( $option_value ) ) {
			$option_value = array_map( 'stripslashes', $option_value );
		}
		elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return $option_value === null ? $default : $option_value;
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the plugin name options array and outputs each field.
	 *
	 * @access public
	 * @param array $options Opens array to output
	 */
	public static function output_fields( $options ) {
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) continue;
			if ( ! isset( $value['id'] ) ) $value['id'] = '';
			if ( ! isset( $value['title'] ) ) $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			if ( ! isset( $value['class'] ) ) $value['class'] = '';
			if ( ! isset( $value['css'] ) ) $value['css'] = '';
			if ( ! isset( $value['default'] ) ) $value['default'] = '';
			if ( ! isset( $value['desc'] ) ) $value['desc'] = '';
			if ( ! isset( $value['desc_tip'] ) ) $value['desc_tip'] = false;

			// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling
			if ( $value['desc_tip'] === true ) {
				$description = '';
				$tip = $value['desc'];
			}
			elseif ( ! empty( $value['desc_tip'] ) ) {
				$description = $value['desc'];
				$tip = $value['desc_tip'];
			}
			elseif ( ! empty( $value['desc'] ) ) {
				$description = $value['desc'];
				$tip = '';
			}
			else {
				$description = $tip = '';
			}

			if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
				$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
			}
			elseif ( $description ) {
				$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
			}

			if ( $tip && in_array( $value['type'], array( 'checkbox' ) ) ) {
				$tip = '<p class="description">' . $tip . '</p>';
			}
			elseif ( $tip ) {
				$tip = '<img class="help_tip" data-tip="' . esc_attr( $tip ) . '" src="' . ElectroSuite_Reseller()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';
			}

			// Switch based on type
			switch( $value['type'] ) {
				// Section Titles
				case 'title':
					if ( ! empty( $value['title'] ) ) {
						echo '<h3>' . esc_html( $value['title'] ) . '</h3>';
					}

					if ( ! empty( $value['desc'] ) ) {
						echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
					}

					echo '<table class="form-table">'. "\n\n";

					if ( ! empty( $value['id'] ) ) {
						do_action( 'electrosuite_reseller_settings_' . sanitize_title( $value['id'] ) );
					}
					break;

				// Section Ends
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {
						do_action( 'electrosuite_reseller_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}
					echo '</table>';
					if ( ! empty( $value['id'] ) ) {
						do_action( 'electrosuite_reseller_settings_' . sanitize_title( $value['id'] ) . '_after' );
					}
					break;

				// Standard text inputs and subtypes like 'number'
				case 'text':

				// Username for API calls
				/*case 'username':
				
					$type 			= $value['type'];
					$class 			= '';
					$option_value 	= self::get_option( $value['id'], $value['default'] );

					if ( $value['type'] == 'color' ) {
						$type = 'text';
						$value['class'] .= 'colorpick';
						$description .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;"></div>';
					}

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $type ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description; ?>
						</td>
					</tr><?php
					break;
					*/
				case 'email':
				case 'number':
				case 'color':
				case 'password':

					$type 			= $value['type'];
					$class 			= '';
					$option_value 	= self::get_option( $value['id'], $value['default'] );

					if ( $value['type'] == 'color' ) {
						$type = 'text';
						$value['class'] .= 'colorpick';
						$description .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>';
					}

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $type ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Textarea
				case 'textarea':

					$option_value = self::get_option( $value['id'], $value['default'] );

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
						<?php echo $description; ?>

						<textarea
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								><?php echo esc_textarea( $option_value );  ?></textarea>
						</td>
					</tr><?php
					break;

				// Select boxes
				case 'select':
				case 'multiselect':

				$option_value = self::get_option( $value['id'], $value['default'] );

				?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
						<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php if ( $value['type'] == 'multiselect' ) echo 'multiple="multiple"'; ?>>

								<?php foreach ( $value['options'] as $key => $val ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php
											if ( is_array( $option_value ) ) {
												selected( in_array( $key, $option_value ), true );
											}
											else {
												selected( $option_value, $key );
											}

										?>><?php echo $val ?></option>
										<?php
									}
								?>
							</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Radio inputs
				case 'radio':

					$option_value = self::get_option( $value['id'], $value['default'] );

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<fieldset>
								<?php echo $description; ?>
							<ul>
							<?php foreach ( $value['options'] as $key => $val ) { ?>
								<li>
									<label><input
												name="<?php echo esc_attr( $value['id'] ); ?>"
												value="<?php echo $key; ?>"
												type="radio"
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="<?php echo esc_attr( $value['class'] ); ?>"
												<?php echo implode( ' ', $custom_attributes ); ?>
												<?php checked( $key, $option_value ); ?>
												/> <?php echo $val ?></label>
								</li>
							<?php } ?>
							</ul>
							</fieldset>
						</td>
					</tr><?php
					break;

				// Checkbox input
				case 'checkbox':

					$option_value = self::get_option( $value['id'], $value['default'] );

					if ( ! isset( $value['hide_if_checked'] ) ) $value['hide_if_checked'] = false;
					if ( ! isset( $value['show_if_checked'] ) ) $value['show_if_checked'] = false;

					if ( ! isset( $value['checkboxgroup'] ) || ( isset( $value['checkboxgroup'] ) && $value['checkboxgroup'] == 'start' ) ) {
					?>
						<tr valign="top" class="<?php
							if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked']=='yes') echo 'hidden_option';
							if ( $value['hide_if_checked'] == 'option' ) echo 'hide_options_if_checked';
							if ( $value['show_if_checked'] == 'option' ) echo 'show_options_if_checked';
						?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
						<td class="forminp forminp-checkbox">
							<fieldset>
						<?php
					}
					else {
						?>
						<fieldset class="<?php
							if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes') echo 'hidden_option';
							if ( $value['hide_if_checked'] == 'option') echo 'hide_options_if_checked';
							if ( $value['show_if_checked'] == 'option') echo 'show_options_if_checked';
						?>">
					<?php
					}

					?>
						<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>

						<label for="<?php echo $value['id'] ?>">
						<input
							name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							type="checkbox"
							value="1"
							<?php checked( $option_value, 'yes'); ?>
							<?php echo implode( ' ', $custom_attributes ); ?>
						/> <?php echo wp_kses_post( $value['desc'] ) ?></label> <?php echo $tip; ?>
					<?php
					if ( ! isset( $value['checkboxgroup'] ) || ( isset( $value['checkboxgroup'] ) && $value['checkboxgroup'] == 'end' ) ) {
						?>
							</fieldset>
						</td>
						</tr>
						<?php
					}
					else {
						?>
						</fieldset>
						<?php
					}
					break;

				// Image width settings
				case 'image_width':

					$width 	= self::get_option( $value['id'] . '[width]', $value['default']['width'] );
					$height = self::get_option( $value['id'] . '[height]', $value['default']['height'] );
					$crop 	= checked( 1, self::get_option( $value['id'] . '[crop]', $value['default']['crop'] ), false );

					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?> <?php echo $tip; ?></th>
						<td class="forminp image_width_settings">

							<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px

						<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" <?php echo $crop; ?> /> <?php _e( 'Hard Crop?', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></label>

						</td>
					</tr><?php
					break;

				// Single page selects
				case 'single_select_page':

					$args = array( 'name'				=> $value['id'],
								   'id'					=> $value['id'],
								   'sort_column' 		=> 'menu_order',
								   'sort_order'			=> 'ASC',
								   'show_option_none' 	=> ' ',
								   'class'				=> $value['class'],
								   'echo' 				=> false,
								   'selected'			=> absint( self::get_option( $value['id'] ) )
							);

					if( isset( $value['args'] ) )
						$args = wp_parse_args( $value['args'], $args );

					?><tr valign="top" class="single_select_page">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?> <?php echo $tip; ?></th>
						<td class="forminp">
							<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', ELECTROSUITE_RESELLER_TEXT_DOMAIN ) .  "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Single country selects
				case 'single_select_country':
					$country_setting = (string) self::get_option( $value['id'] );
					$countries 		 = ElectroSuite_Reseller()->countries->countries;

					if ( strstr( $country_setting, ':' ) ) {
						$country_setting 	= explode( ':', $country_setting );
						$country 			= current( $country_setting );
						$state 				= end( $country_setting );
					}
					else {
						$country 	= $country_setting;
						$state 		= '*';
					}
					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php _e( 'Choose a country&hellip;', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?>" title="Country" class="chosen_select">
							<?php ElectroSuite_Reseller()->countries->country_dropdown_options( $country, $state ); ?>
						</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Country multiselects
				case 'multi_select_countries':

					$selections = (array) self::get_option( $value['id'] );

					if ( ! empty( $value['options'] ) ) {
						$countries = $value['options'];
					}
					else {
						$countries = ElectroSuite_Reseller()->countries->countries;
					}

					asort( $countries );
					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp">
							<select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php _e( 'Choose countries&hellip;', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?>" title="Country" class="chosen_select">
								<?php
								if ( $countries ) {
									foreach ( $countries as $key => $val ) {
										echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selections ), true, false ).'>' . $val . '</option>';
									}
								}
								?>
							</select> <?php if ( $description ) { echo $description; } ?> </br><a class="select_all button" href="#"><?php _e( 'Select all', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></a> <a class="select_none button" href="#"><?php _e( 'Select none', ELECTROSUITE_RESELLER_TEXT_DOMAIN ); ?></a>
						</td>
					</tr><?php
					break;

					case 'checkbox_grid':
                    // Get Title, Description, Tip - reuse logic from start of output_fields
                    // Note: $description and $tip variables are already calculated earlier in the loop.
                    // Use $value['title'] directly for the label in <th>.
                    ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <?php // Output title in TH like other fields ?>
                            <label><?php echo esc_html( $value['title'] ); ?></label>
                            <?php // Placeholder for JS counter ?>
                            <p class="tld-limit-message" style="font-weight: normal; font-size: 0.9em; margin: 5px 0 0 0; padding: 0; color: #666;"></p>
                            <?php // Tooltip icon ?>
                            <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-checkbox_grid <?php echo esc_attr( $value['class'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>">
                            <?php
                            // --- Prepare Reset Link HTML conditionally ---
                            $reset_link_html = '';
                            if ( isset( $value['default_tlds_list'] ) && ! empty( $value['default_tlds_list'] ) && is_array( $value['default_tlds_list'] ) ) {
                                $reset_link_html = '<a href="#" class="reset-tld-defaults" style="white-space: nowrap;">' . esc_html__( 'Reset Default TLDs', 'electrosuite-reseller' ) . '</a>';
                            }
                            // --- End Prepare Reset Link ---
                            ?>

                            <?php // --- Output Description and Reset Link Inline --- ?>
                            <p style="margin-bottom: 10px;"> <?php // Add paragraph wrapper for spacing ?>
                                <?php echo $description; // Echo the pre-formatted description HTML ?>
                                <?php
                                // Append the reset link if it exists, with a separator
                                if ( ! empty( $reset_link_html ) ) {
                                    echo ' | '; // Use regular spaces instead of  
                                    echo $reset_link_html;
                                }
                                ?>
                            </p>
                            <?php // --- End Output --- ?>

                            <?php // Call the rendering method for the grid itself ?>
                            <?php self::output_checkbox_grid( $value ); // Pass the full $value array ?>
                        </td>
                    </tr>
                    <?php
                    break;

				// Default: run an action
				default:
					do_action( 'electrosuite_reseller_admin_field_' . $value['type'], $value );

					break;
			} // end switch
		}
	}

	
	/**
	 * Save admin fields.
	 * Handles saving the settings array and checkbox groups like TLDs.
	 *
	 * @param array $options Array of option definitions for the current view.
	 * @param string $current_tab Current tab ID.
	 * @param string $current_section Current section ID.
	 * @return bool True on success.
	 */
	public static function save_fields( $options, $current_tab, $current_section = '' ) {
		if ( empty( $_POST ) ) {
			return false;
		}

		// Options to update will be stored here, keyed by option name
		$update_options = array();
		// Store all defined option IDs for this view to help process checkbox groups
		$defined_option_ids = [];

		// First pass: Identify all defined options and process non-checkbox-group types
		foreach ( $options as $value ) {
			if ( ! isset( $value['id'] ) || empty( $value['id'] ) || $value['type'] === 'title' || $value['type'] === 'sectionend' || $value['type'] === 'tld_checkbox_title' ) {
				continue; // Skip titles, section ends, dummy types, or fields without ID
			}

            $option_id = $value['id'];
            $defined_option_ids[] = $option_id; // Store the ID

			// Skip processing checkbox array items here, handle them later
			if ( $value['type'] === 'checkbox' && strpos( $option_id, '[' ) !== false ) {
				continue;
			}

			// Process standard types
			$type = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';
			$option_value = null;

			switch ( $type ) {
				case "checkbox": // Single checkbox
					$option_value = isset( $_POST[ $option_id ] ) ? 'yes' : 'no';
					break;

				case "textarea":
					$option_value = isset( $_POST[ $option_id ] ) ? wp_kses_post( trim( stripslashes( $_POST[ $option_id ] ) ) ) : '';
					break;

				case "text":
				case "username":
				case "email":
				case "number":
				case "select":
				case "color":
				case "password":
				case "single_select_page":
				case "single_select_country":
				case "radio":
                    // Assumes electrosuite_reseller_clean exists and sanitizes appropriately
                    if (function_exists('electrosuite_reseller_clean')) {
                         $option_value = isset( $_POST[ $option_id ] ) ? electrosuite_reseller_clean( stripslashes( $_POST[ $option_id ] ) ) : '';
                    } else {
                         // Fallback to basic sanitization if clean function missing
                         $option_value = isset( $_POST[ $option_id ] ) ? sanitize_text_field( stripslashes( $_POST[ $option_id ] ) ) : '';
                    }
					break;

				case "multiselect":
				case "multi_select_countries":
                    $selected_values = [];
					if ( isset( $_POST[ $option_id ] ) && is_array( $_POST[ $option_id ] ) ) {
                        if (function_exists('electrosuite_reseller_clean')) {
                             $selected_values = array_map( 'electrosuite_reseller_clean', array_map( 'stripslashes', (array) $_POST[ $option_id ] ) );
                        } else {
                             // Fallback sanitization
                             $selected_values = array_map( 'sanitize_text_field', array_map( 'stripslashes', (array) $_POST[ $option_id ] ) );
                        }
					}
					$option_value = $selected_values;
					break;

				case "image_width":
                    // This saves as an array under a single option ID like 'setting_id' => ['width'=> W, 'height'=> H, 'crop'=> C]
                    $width = isset( $_POST[$option_id]['width'] ) ? sanitize_text_field( stripslashes( $_POST[$option_id]['width'] ) ) : (isset($value['default']['width']) ? $value['default']['width'] : '');
                    $height = isset( $_POST[$option_id]['height'] ) ? sanitize_text_field( stripslashes( $_POST[$option_id]['height'] ) ) : (isset($value['default']['height']) ? $value['default']['height'] : '');
                    $crop = isset( $_POST[$option_id]['crop'] ) ? 1 : 0;
                    $option_value = ['width' => $width, 'height' => $height, 'crop' => $crop];
					break;

				default:
					// Allow extensions to handle custom types
					do_action( 'electrosuite_reseller_update_option_' . $type, $value );
                    // Retrieve value potentially set by the action
                    $option_value = apply_filters( 'electrosuite_reseller_admin_settings_sanitize_option_' . $option_id, null, $value );
					break;
			}

			if ( ! is_null( $option_value ) ) {
                // This condition might be overly complex if image_width is the only array type saved this way
                // Check if ID contains '[' but IS NOT an image_width type (which already created its array)
				if ( strstr( $option_id, '[' ) && $type !== 'image_width' ) {
					parse_str( $option_id, $option_array );
					$option_name_base = current( array_keys( $option_array ) );
					if ( ! isset( $update_options[ $option_name_base ] ) ) {
						$update_options[ $option_name_base ] = get_option( $option_name_base, array() );
					}
					if ( ! is_array( $update_options[ $option_name_base ] ) ) {
						$update_options[ $option_name_base ] = array();
					}
					$key = key( $option_array[ $option_name_base ] );
					$update_options[ $option_name_base ][ $key ] = $option_value;
				} else {
                    // Assign value for simple options or options handled entirely by type switch (like image_width)
					$update_options[ $option_id ] = $option_value;
				}
			}
            // Allow further modification of the specific option
			do_action( 'electrosuite_reseller_update_option', $value );

		} // End foreach $options

        // --- Process TLD Checkbox Group ---
        // This assumes the ID structure 'base_option_key[tld_value]' was used
        $tld_option_key_enom = 'electrosuite_reseller_enom_checked_tlds';
        // Check if any checkbox fields for this group were defined in the options array
        $enom_tlds_were_present = false;
        foreach($defined_option_ids as $defined_id) {
            if (strpos($defined_id, $tld_option_key_enom . '[') === 0) {
                $enom_tlds_were_present = true;
                break;
            }
        }

        if ($enom_tlds_were_present) {
            $selected_tlds_enom = [];
            // The submitted data for checkboxes with array names comes directly under the base key
            if (isset($_POST[$tld_option_key_enom]) && is_array($_POST[$tld_option_key_enom])) {
                // The keys of the submitted array are the TLDs that were checked
                foreach (array_keys($_POST[$tld_option_key_enom]) as $tld) {
                    // Sanitize the TLD string before saving
                    $selected_tlds_enom[] = sanitize_key($tld); // sanitize_key is good for slugs/keys
                }
            }
            // Update the single option with the array of selected TLDs
            $update_options[$tld_option_key_enom] = $selected_tlds_enom;
             error_log("Debug Save: Saving selected eNom TLDs: " . print_r($selected_tlds_enom, true)); // Optional log
        }
        // --- TODO: Add similar processing block for ResellerClub/CentralNic TLD checkboxes ---
        // $tld_option_key_rc = 'electrosuite_reseller_resellerclub_checked_tlds'; etc.


		// Now save all collected options to the database
		foreach( $update_options as $name => $value ) {
			update_option( $name, $value );
		}

		// This part saving ALL options for a tab/section into another single option seems redundant
        // if the individual options are already being saved by update_option above.
        // Consider removing unless used for export feature.
        /*
		if( empty( $current_section ) ) {
			update_option( 'electrosuite_reseller_options_' . $current_tab, $update_options );
		}
		else{
			update_option( 'electrosuite_reseller_options_' . $current_tab . '_' . $current_section, $update_options );
		}
        */

		return true;
	} // End save_fields

	
	/**
     * Output HTML for the Generic Checkbox Grid content ONLY.
     * Renders the fieldset and the grid div with checkboxes.
     * Title and Description are handled by output_fields().
     * Includes Reset Defaults link and data attribute.
     *
     * @param array $value Field definition array passed from output_fields().
     */
    public static function output_checkbox_grid( $value ) {
        // --- Validation & Data Extraction ---
        if ( empty( $value['id'] ) || !isset( $value['options'] ) || !is_array($value['options']) ) {
             echo '<p style="color:red; font-weight:bold;">Checkbox Grid Render Error: Invalid data passed.</p>';
             return;
        }

        $option_name        = $value['id'];
        $grid_items         = $value['options'];
        // Get currently saved selection (used for initial checked state)
        $selected_items     = get_option($option_name, []);
        if ( ! is_array( $selected_items ) ) $selected_items = [];

        $item_prefix        = isset( $value['item_prefix'] ) ? $value['item_prefix'] : '';
        $grid_container_id  = esc_attr( $option_name . '_grid_container' );
        $grid_classes       = 'checkbox-grid ' . (isset($value['class']) ? esc_attr($value['class']) : '');
        $grid_style         = isset($value['css']) ? esc_attr($value['css']) : '';

        // --- Prepare default TLD data attribute ---
        $defaults_data_attr = '';
        if ( isset( $value['default_tlds_list'] ) && is_array( $value['default_tlds_list'] ) ) {
            $defaults_json = wp_json_encode( $value['default_tlds_list'] ); // Use WordPress JSON function
            if ( $defaults_json ) {
                // Ensure the output is safe for an HTML attribute
                $defaults_data_attr = ' data-defaults="' . esc_attr( $defaults_json ) . '"';
            }
        }
        // --- End Prepare ---


        // --- HTML Output ---
        ?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ?? $option_name ); ?></span></legend>

            <?php // --- Reset Link block (previously moved here) will be DELETED --- ?>
            <?php /*
            <?php if ( !empty($defaults_data_attr) ) : ?>
                <p class="reset-link-container" style="margin-bottom: 10px; margin-top: 5px;">
                    <a href="#" class="reset-tld-defaults"><?php esc_html_e( 'Reset Default TLDs', 'electrosuite-reseller' ); ?></a>
                </p>
            <?php endif; ?>
            */ ?>
            <?php // --- End Deleted Block --- ?>

            <?php // This div holds the grid items (added data attribute) ?>
            <div class="<?php echo esc_attr($grid_classes); ?>" id="<?php echo $grid_container_id; ?>" style="<?php echo esc_attr($grid_style); ?>"<?php echo $defaults_data_attr; ?>>
                <?php if ( empty($grid_items) ) : ?>
                    <p><?php esc_html_e( 'No items available to display.', 'electrosuite-reseller' ); ?></p>
                <?php else : ?>
                    <?php foreach ( $grid_items as $item_key => $item_label ) : ?>
                        <?php
                        // --- Checkbox rendering logic ---
                        if ( is_int( $item_key ) ) { $item_key = $item_label; } // Handle cases where only values are provided
                        $item_key_esc   = esc_attr( $item_key );
                        // Generate unique ID for checkbox
                        $checkbox_id    = esc_attr( $option_name . '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $item_key_esc) );
                        // Name attribute uses array format 'option_name[tld_value]'
                        $checkbox_name  = esc_attr( $option_name . '[' . $item_key_esc . ']' );
                        // Check against the current selection (retrieved via get_option earlier)
                        $is_checked     = in_array( (string) $item_key, array_map('strval', $selected_items), true );
                        ?>
                        <div class="checkbox-grid-item">
                            <label for="<?php echo $checkbox_id; ?>">
                                <input type="checkbox" name="<?php echo $checkbox_name; ?>" id="<?php echo $checkbox_id; ?>" value="yes" <?php checked( $is_checked ); ?> class="checkbox-grid-input">
                                <span class="checkbox-grid-label"><?php echo esc_html( $item_prefix . $item_label ); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; // End empty check ?>
            </div> <?php // End .checkbox-grid div ?>

            <?php // Reset link was removed from here ?>

        </fieldset>
        <?php
        // --- End HTML Output ---
    } // End output_checkbox_grid method
}

} // end if class exists.

?>