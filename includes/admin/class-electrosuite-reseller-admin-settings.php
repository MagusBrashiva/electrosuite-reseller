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
	 * Displays placeholders for Live API keys instead of actual values.
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
			$description = '';
			$tip = '';
			if ( $value['desc_tip'] === true ) {
				$tip = $value['desc'];
			}
			elseif ( ! empty( $value['desc_tip'] ) ) {
				$description = $value['desc'];
				$tip = $value['desc_tip'];
			}
			elseif ( ! empty( $value['desc'] ) ) {
				$description = $value['desc'];
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
				$tip = '<img class="help_tip" data-tip="' . esc_attr( $tip ) . '" src="' . esc_url( ElectroSuite_Reseller()->plugin_url() . '/assets/images/help.png' ) . '" height="16" width="16" />';
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
				case 'email':
				case 'number':
				case 'color':
				// case 'password': // Handled below

					$type 			= $value['type'];
					$option_value 	= self::get_option( $value['id'], $value['default'] );

					if ( $value['type'] == 'color' ) {
						$type = 'text';
						$value['class'] .= ' colorpick'; // Add space before class name
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
								value="<?php echo esc_attr( $option_value ); ?>" <?php // Standard value output for non-password ?>
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Password Field Output Logic
				case 'password':
					$option_value   = self::get_option( $value['id'], $value['default'] );
					$display_value  = ''; // Initialize display value
					$placeholder_text = ''; // Initialize placeholder

					// Define Live API Key Option IDs
					$live_api_key_ids = [
						'electrosuite_reseller_enom_live_api_key',
						'electrosuite_reseller_resellerclub_live_api_key',
						'electrosuite_reseller_centralnic_live_api_key',
					];

					// Check if this is a Live API key
					if ( in_array( $value['id'], $live_api_key_ids ) ) {
						// For Live keys, show placeholder if a value exists (is saved/encrypted)
						if ( ! empty( $option_value ) ) {
							$display_value = '************************************************';
							$placeholder_text = __( 'Saved - Enter new key to change', 'electrosuite-reseller' );
						} else {
							$display_value = '';
							// No specific placeholder needed if empty
						}
					} else {
						// For other password fields (e.g., Test Keys), show the actual saved value (plaintext)
						$display_value = $option_value;
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
								type="password" <?php // Always type="password" for masking ?>
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $display_value ); ?>" <?php // Use controlled display value ?>
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $placeholder_text ); ?>" <?php // Add placeholder hint ?>
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
                                <?php
                                if ( ! empty( $value['options'] ) && is_array( $value['options'] ) ) {
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php
                                            if ( is_array( $option_value ) ) {
                                                selected( in_array( (string) $key, array_map('strval', $option_value), true ), true );
                                            } else {
                                                selected( (string) $option_value, (string) $key );
                                            }
                                        ?>><?php echo esc_html( $val ); ?></option>
                                        <?php
                                    }
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
							<label><?php echo esc_html( $value['title'] ); ?></label> <?php // No 'for' needed for radio group title ?>
							<?php echo $tip; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<fieldset>
								<?php echo $description; ?>
								<ul>
								<?php
								if ( ! empty( $value['options'] ) && is_array( $value['options'] ) ) {
									foreach ( $value['options'] as $key => $val ) {
										$radio_id = esc_attr( $value['id'] . '_' . $key );
										?>
										<li>
											<label for="<?php echo $radio_id; ?>">
												<input
													name="<?php echo esc_attr( $value['id'] ); ?>"
													id="<?php echo $radio_id; ?>"
													value="<?php echo esc_attr( $key ); ?>"
													type="radio"
													style="<?php echo esc_attr( $value['css'] ); ?>"
													class="<?php echo esc_attr( $value['class'] ); ?>"
													<?php echo implode( ' ', $custom_attributes ); ?>
													<?php checked( (string) $key, (string) $option_value ); ?>
												/> <?php echo esc_html( $val ); ?>
											</label>
										</li>
										<?php
									}
								}
								?>
								</ul>
							</fieldset>
						</td>
					</tr><?php
					break;

				// Checkbox input
				case 'checkbox':
					$option_value = self::get_option( $value['id'], $value['default'] );
                    // Ensure default is handled correctly for 'yes'/'no' checkboxes
                    $checked_value = ($option_value === 'yes');

					if ( ! isset( $value['hide_if_checked'] ) ) $value['hide_if_checked'] = false;
					if ( ! isset( $value['show_if_checked'] ) ) $value['show_if_checked'] = false;
                    $tr_class = '';
                    if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked']=='yes') $tr_class .= ' hidden_option';
                    if ( $value['hide_if_checked'] == 'option' ) $tr_class .= ' hide_options_if_checked';
                    if ( $value['show_if_checked'] == 'option' ) $tr_class .= ' show_options_if_checked';

					if ( ! isset( $value['checkboxgroup'] ) || ( isset( $value['checkboxgroup'] ) && $value['checkboxgroup'] == 'start' ) ) {
					?>
						<tr valign="top" class="<?php echo esc_attr(trim($tr_class)); ?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
						<td class="forminp forminp-checkbox">
							<fieldset>
					<?php
					} else { // Middle or end of a group
						?>
						<fieldset class="<?php echo esc_attr(trim($tr_class)); ?>">
					<?php
					}
					?>
						<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
						<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								value="yes" <?php // Value when checked should be 'yes' for consistency with save logic ?>
								<?php checked( $checked_value ); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo wp_kses_post( $value['desc'] ); // Use 'desc' for label text if provided ?>
						</label> <?php echo $tip; ?>
					<?php
					if ( ! isset( $value['checkboxgroup'] ) || ( isset( $value['checkboxgroup'] ) && $value['checkboxgroup'] == 'end' ) ) {
						?>
							</fieldset>
						</td>
						</tr>
						<?php
					} else {
						?>
						</fieldset>
						<?php
					}
					break;

				// Image width settings
				case 'image_width':
					// Retrieve array value, then access keys with defaults
                    $image_size = self::get_option( $value['id'], $value['default'] );
                    $width  = isset( $image_size['width'] ) ? $image_size['width'] : ( isset($value['default']['width']) ? $value['default']['width'] : '' );
                    $height = isset( $image_size['height'] ) ? $image_size['height'] : ( isset($value['default']['height']) ? $value['default']['height'] : '' );
                    $crop   = isset( $image_size['crop'] ) ? $image_size['crop'] : ( isset($value['default']['crop']) ? $value['default']['crop'] : 0 );

					?><tr valign="top">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?> <?php echo $tip; ?></th>
						<td class="forminp image_width_settings">
							<label for="<?php echo esc_attr( $value['id'] ); ?>-width"><?php esc_html_e( 'Width', 'electrosuite-reseller' ); ?></label>
							<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo esc_attr( $width ); ?>" />
							<label for="<?php echo esc_attr( $value['id'] ); ?>-height"><?php esc_html_e( 'Height', 'electrosuite-reseller' ); ?></label>
							<input name="<?php echo esc_attr( $value['id'] ); ?>[height]" id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo esc_attr( $height ); ?>" />px
                            <span class="description">
							    <label for="<?php echo esc_attr( $value['id'] ); ?>-crop"><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" value="1" <?php checked( 1, $crop ); ?> /> <?php esc_html_e( 'Hard Crop?', 'electrosuite-reseller' ); ?></label>
                            </span>
						</td>
					</tr><?php
					break;

				// Single page selects
				case 'single_select_page':
					$page_id = self::get_option( $value['id'], $value['default'] );
					$args = array(
                        'name'				=> $value['id'],
                        'id'				=> $value['id'],
                        'sort_column' 		=> 'menu_order',
                        'sort_order'		=> 'ASC',
                        'show_option_none' 	=> ' ', // Allows placeholder to work
                        'class'				=> $value['class'],
                        'echo' 				=> false,
                        'selected'			=> absint( $page_id )
					);

					if( isset( $value['args'] ) ) {
						$args = wp_parse_args( $value['args'], $args );
                    }

					?><tr valign="top" class="single_select_page">
						<th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tip; ?>
                        </th>
						<td class="forminp">
							<?php echo str_replace(
                                ' id=',
                                " data-placeholder='" . esc_attr__( 'Select a page…', 'electrosuite-reseller' ) .  "' style='" . esc_attr($value['css']) . "' class='" . esc_attr($value['class']) . "' id=",
                                wp_dropdown_pages( $args )
                            ); ?> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Single country selects (assuming ElectroSuite_Reseller()->countries exists)
				case 'single_select_country':
                    $country_setting = (string) self::get_option( $value['id'], $value['default'] );
                    $countries = is_object(ElectroSuite_Reseller()->countries) ? ElectroSuite_Reseller()->countries->countries : array();
                    $country = $country_setting; // Assuming format doesn't include state here
					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
						<td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" class="<?php echo esc_attr($value['class']); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country…', 'electrosuite-reseller' ); ?>" title="<?php esc_attr_e( 'Country', 'electrosuite-reseller' ); ?>">
							<option value=""><?php esc_html_e( 'Choose a country…', 'electrosuite-reseller' ); ?></option>
							<?php
                            if (is_object(ElectroSuite_Reseller()->countries)) {
                                // Assuming a method exists like country_dropdown_options
                                // Let's simplify if the structure is just Key => Value
                                foreach ($countries as $ckey => $cvalue) {
                                    echo '<option value="' . esc_attr($ckey) . '" ' . selected($country, $ckey, false) . '>' . esc_html($cvalue) . '</option>';
                                }
                            }
                            ?>
						</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Country multiselects
				case 'multi_select_countries':
                    $selections = (array) self::get_option( $value['id'], $value['default'] );
                    if (!is_array($selections)) $selections = []; // Ensure it's an array

                    if ( ! empty( $value['options'] ) && is_array( $value['options'] ) ) {
                        $countries = $value['options'];
                    } else {
                        $countries = is_object(ElectroSuite_Reseller()->countries) ? ElectroSuite_Reseller()->countries->countries : array();
                    }

                    asort( $countries );
                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tip; ?>
                        </th>
                        <td class="forminp">
                            <select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" id="<?php echo esc_attr( $value['id'] ); ?>" style="width:350px; <?php echo esc_attr($value['css']); ?>" data-placeholder="<?php esc_attr_e( 'Choose countries…', 'electrosuite-reseller' ); ?>" title="<?php esc_attr_e( 'Country', 'electrosuite-reseller' ); ?>" class="<?php echo esc_attr($value['class']); ?>">
                                <?php
                                if ( !empty($countries) ) {
                                    foreach ( $countries as $key => $val ) {
                                        echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( (string) $key, array_map('strval', $selections), true ), true, false ).'>' . esc_html( $val ) . '</option>';
                                    }
                                }
                                ?>
                            </select> <?php echo $description; ?>
                            <br/><a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'electrosuite-reseller' ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'electrosuite-reseller' ); ?></a>
                        </td>
                    </tr><?php
                    break;

                // Generic Checkbox Grid Field
                case 'checkbox_grid':
                    ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php echo esc_html( $value['title'] ); ?></label>
                            <?php // --- Re-inserted TLD Counter Placeholder --- ?>
                            <p class="tld-limit-message" style="font-weight: normal; font-size: 0.9em; margin: 5px 0 0 0; padding: 0; color: #666;"></p>
                            <?php // --- End Re-inserted Element --- ?>
                            <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-checkbox_grid <?php echo esc_attr( $value['class'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>">
                            <?php
                            // Prepare Reset Link HTML conditionally
                            $reset_link_html = '';
                            if ( isset( $value['default_tlds_list'] ) && ! empty( $value['default_tlds_list'] ) && is_array( $value['default_tlds_list'] ) ) {
                                $reset_link_html = '<a href="#" class="reset-tld-defaults" style="white-space: nowrap;">' . esc_html__( 'Reset Default TLDs', 'electrosuite-reseller' ) . '</a>';
                            }
                            ?>
                            <p style="margin-bottom: 10px;"> <?php // Description and Reset link ?>
                                <?php echo $description; // Echo the pre-formatted description HTML ?>
                                <?php
                                if ( ! empty( $reset_link_html ) ) {
                                    echo ' | '; // Separator
                                    echo $reset_link_html;
                                }
                                ?>
                            </p>
                            <?php self::output_checkbox_grid( $value ); // Call the rendering method ?>
                        </td>
                    </tr>
                    <?php
                    break;


				// Default: run an action for custom field types
				default:
					do_action( 'electrosuite_reseller_admin_field_' . $value['type'], $value );
					break;
			} // end switch
		} // end foreach
	} // end output_fields

	
	/**
	 * Save admin fields.
	 * Handles saving the settings array and checkbox groups like TLDs.
	 * Encrypts Live API keys before saving.
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

        // Define Live API Key Option IDs
        $live_api_key_ids = [
            'electrosuite_reseller_enom_live_api_key',
            'electrosuite_reseller_resellerclub_live_api_key',
            'electrosuite_reseller_centralnic_live_api_key',
        ];

		// First pass: Identify all defined options and process non-checkbox-group types
		foreach ( $options as $value ) {
			if ( ! isset( $value['id'] ) || empty( $value['id'] ) || !isset($value['type']) || $value['type'] === 'title' || $value['type'] === 'sectionend' || $value['type'] === 'checkbox_grid' ) { // Skip checkbox_grid fields here, handled later
				continue; // Skip titles, section ends, grid containers, or fields without ID/type
			}

            $option_id = $value['id'];
            $defined_option_ids[] = $option_id; // Store the ID

			// Skip processing checkbox array items here, handle them later in checkbox_grid specific logic if needed
			if ( $value['type'] === 'checkbox' && strpos( $option_id, '[' ) !== false ) {
				continue;
			}

			// Process standard types
			$type = sanitize_title( $value['type'] );
			$option_value = null; // Initialize
            $raw_post_value = isset( $_POST[ $option_id ] ) ? stripslashes( $_POST[ $option_id ] ) : null; // Get raw value once

			switch ( $type ) {
				case "checkbox": // Single checkbox
					$option_value = isset( $_POST[ $option_id ] ) ? 'yes' : 'no'; // Uses $_POST directly
					break;

				case "textarea":
					$option_value = !is_null( $raw_post_value ) ? wp_kses_post( trim( $raw_post_value ) ) : '';
					break;

				case "text":
				case "username": // Usernames are plaintext
				case "email":
				case "number":
				case "select":
				case "color":
				// case "password": // Password handling moved below switch
				case "single_select_page":
				case "single_select_country":
				case "radio":
                    // Use default sanitization or electrosuite_reseller_clean if available
                    if (function_exists('electrosuite_reseller_clean')) {
                         $option_value = !is_null( $raw_post_value ) ? electrosuite_reseller_clean( $raw_post_value ) : '';
                    } else {
                         $option_value = !is_null( $raw_post_value ) ? sanitize_text_field( $raw_post_value ) : '';
                    }
					break;

                case "password":
                    // Check if this is a Live API Key
                    if ( in_array( $option_id, $live_api_key_ids ) ) {
                        if ( !is_null( $raw_post_value ) && $raw_post_value !== '' ) {
                            // Only encrypt if the value is not the placeholder and Security class exists
                            if ( $raw_post_value !== '********' ) { // Don't re-encrypt the placeholder
                                if ( class_exists('ElectroSuite_Reseller_Security') ) {
                                    $encrypted_value = ElectroSuite_Reseller_Security::encrypt( $raw_post_value );
                                    if ( $encrypted_value !== false ) {
                                        $option_value = $encrypted_value;
                                    } else {
                                        self::add_error( sprintf( __( 'Failed to encrypt API key for %s. Setting was not saved.', 'electrosuite-reseller' ), $value['title'] ) );
                                        $option_value = null; // Prevent update if encryption fails
                                    }
                                } else {
                                    self::add_error( __( 'Security class not found. API key was not saved.', 'electrosuite-reseller' ) );
                                    $option_value = null; // Prevent update if class missing
                                }
                            } else {
                                // User saved without changing the placeholder, so keep the existing encrypted value.
                                // We achieve this by setting $option_value to null here, so it doesn't get added
                                // to $update_options unless explicitly handled below.
                                // However, it's simpler to just *not* update the option if the placeholder is submitted.
                                // So, we just don't assign anything to $option_value here if it's the placeholder.
                                // We need to ensure $option_value remains null or is handled appropriately outside the switch.
                                continue 2; // Skip to next option in the main foreach loop
                            }
                        } else {
                            // User submitted an empty value (cleared the field), save empty string
                            $option_value = '';
                        }
                    } else {
                        // It's a password field, but NOT a Live API Key (e.g., Test Key)
                        // Save as plaintext using appropriate sanitization
                        if (function_exists('electrosuite_reseller_clean')) {
                             $option_value = !is_null( $raw_post_value ) ? electrosuite_reseller_clean( $raw_post_value ) : '';
                        } else {
                             $option_value = !is_null( $raw_post_value ) ? sanitize_text_field( $raw_post_value ) : '';
                        }
                    }
                    break;


				case "multiselect":
				case "multi_select_countries":
                    $selected_values = [];
                    // Use $raw_post_value which is already available and unslashed
					if ( !is_null( $raw_post_value ) && is_array( $raw_post_value ) ) {
                        // Sanitize each value in the array
                        if (function_exists('electrosuite_reseller_clean')) {
                             $selected_values = array_map( 'electrosuite_reseller_clean', $raw_post_value );
                        } else {
                             $selected_values = array_map( 'sanitize_text_field', $raw_post_value );
                        }
					}
					$option_value = $selected_values;
					break;

				case "image_width":
                    // image_width saves as an array under a single option ID
                    $image_width_value = isset( $_POST[ $option_id ] ) && is_array( $_POST[ $option_id ] ) ? $_POST[ $option_id ] : [];
                    $width = isset( $image_width_value['width'] ) ? sanitize_text_field( stripslashes( $image_width_value['width'] ) ) : (isset($value['default']['width']) ? $value['default']['width'] : '');
                    $height = isset( $image_width_value['height'] ) ? sanitize_text_field( stripslashes( $image_width_value['height'] ) ) : (isset($value['default']['height']) ? $value['default']['height'] : '');
                    $crop = isset( $image_width_value['crop'] ) ? 1 : 0; // Checkbox value '1' or not set
                    $option_value = ['width' => $width, 'height' => $height, 'crop' => $crop];
					break;

				default:
					// Allow extensions to handle custom types via action hooks
                    // Use apply_filters to allow modification/sanitization of the value based on type or id
                    $option_value = apply_filters( 'electrosuite_reseller_admin_settings_sanitize_option', $raw_post_value, $option_id, $value );
                    // Also apply type-specific filter if needed
                    $option_value = apply_filters( 'electrosuite_reseller_admin_settings_sanitize_option_' . $type, $option_value, $value );
					break;
			}

			// If $option_value was set (i.e., not null from encryption failure or handled 'continue'), add it to the update array.
            if ( ! is_null( $option_value ) ) {
                // Handle complex IDs like arrays (e.g., image_width, although it's handled above now)
				if ( strstr( $option_id, '[' ) && $type !== 'image_width' && $type !== 'password' /* Password handled above */ ) {
					// This logic might need review if other complex fields exist
                    parse_str( $option_id, $option_array );
                    $option_name_base = current( array_keys( $option_array ) );
                    if ( ! isset( $update_options[ $option_name_base ] ) ) {
                        // Initialize with existing value if merging into an array option
                        $update_options[ $option_name_base ] = get_option( $option_name_base, array() );
                    }
                    if ( ! is_array( $update_options[ $option_name_base ] ) ) {
                        // Ensure it's an array if we're treating it like one
                        $update_options[ $option_name_base ] = array();
                    }
                    $key = key( $option_array[ $option_name_base ] );
                    $update_options[ $option_name_base ][ $key ] = $option_value;
				} else {
                    // Assign value for simple options or options whose value was fully prepared in the switch case
					$update_options[ $option_id ] = $option_value;
				}
			}
            // Allow final modification via action hook if needed (less common for saving)
			do_action( 'electrosuite_reseller_update_option', $value, $option_value );

		} // End foreach $options

        // --- Process TLD Checkbox Group Separately ---
        // Find the checkbox_grid definitions in the original $options array
        foreach ($options as $value) {
            if (isset($value['type']) && $value['type'] === 'checkbox_grid' && isset($value['id'])) {
                $grid_option_key = $value['id'];
                $selected_items = [];
                // Checkbox grids submit data as option_name[item_key] = 'yes' (or similar value)
                if (isset($_POST[$grid_option_key]) && is_array($_POST[$grid_option_key])) {
                    // The keys of the submitted array are the items that were checked
                    foreach (array_keys($_POST[$grid_option_key]) as $item_key) {
                        // Sanitize the item key (e.g., TLD string) before saving
                        $selected_items[] = sanitize_key($item_key); // sanitize_key is good for slugs/keys like TLDs
                    }
                }
                // Add the array of selected items to the update list
                $update_options[$grid_option_key] = $selected_items;
                // error_log("Debug Save: Saving selected items for grid {$grid_option_key}: " . print_r($selected_items, true));
            }
        }
        // --- End TLD Checkbox Group Processing ---


		// Now save all collected options to the database
		foreach( $update_options as $name => $value ) {
			update_option( $name, $value );
		}

		// Deprecated grouped option saving removed

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