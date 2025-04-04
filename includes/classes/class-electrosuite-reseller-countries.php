<?php
/**
 * ElectroSuite Reseller countries
 *
 * The ElectroSuite Reseller countries class stores country/state data.
 *
 * @class 		ElectroSuite_Reseller_Countries
 * @version		0.0.1
 * @package		ElectroSuite Reseller/Classes
 * @category	Class
 * @author 		ElectroSuite
 */
class ElectroSuite_Reseller_Countries {

	/** @var array Array of countries */
	public $countries;

	/** @var array Array of states */
	public $states;

	/** @var array Array of locales */
	public $locale;

	/** @var array Array of address formats for locales */
	public $address_formats;

	/**
	 * Constructor for the counties class - defines all countries and states.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $electrosuite_reseller, $states;

		$this->countries = apply_filters( 'electrosuite_reseller_countries', array(
			'AF' => __( 'Afghanistan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AX' => __( '&#197;land Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AL' => __( 'Albania', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'DZ' => __( 'Algeria', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AD' => __( 'Andorra', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AO' => __( 'Angola', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AI' => __( 'Anguilla', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AQ' => __( 'Antarctica', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AG' => __( 'Antigua and Barbuda', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AR' => __( 'Argentina', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AM' => __( 'Armenia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AW' => __( 'Aruba', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AU' => __( 'Australia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AT' => __( 'Austria', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AZ' => __( 'Azerbaijan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BS' => __( 'Bahamas', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BH' => __( 'Bahrain', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BD' => __( 'Bangladesh', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BB' => __( 'Barbados', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BY' => __( 'Belarus', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BE' => __( 'Belgium', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PW' => __( 'Belau', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BZ' => __( 'Belize', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BJ' => __( 'Benin', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BM' => __( 'Bermuda', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BT' => __( 'Bhutan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BO' => __( 'Bolivia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BA' => __( 'Bosnia and Herzegovina', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BW' => __( 'Botswana', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BV' => __( 'Bouvet Island', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BR' => __( 'Brazil', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IO' => __( 'British Indian Ocean Territory', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'VG' => __( 'British Virgin Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BN' => __( 'Brunei', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BG' => __( 'Bulgaria', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BF' => __( 'Burkina Faso', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BI' => __( 'Burundi', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KH' => __( 'Cambodia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CM' => __( 'Cameroon', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CA' => __( 'Canada', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CV' => __( 'Cape Verde', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KY' => __( 'Cayman Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CF' => __( 'Central African Republic', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TD' => __( 'Chad', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CL' => __( 'Chile', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CN' => __( 'China', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CX' => __( 'Christmas Island', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CC' => __( 'Cocos (Keeling) Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CO' => __( 'Colombia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KM' => __( 'Comoros', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CG' => __( 'Congo (Brazzaville)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CD' => __( 'Congo (Kinshasa)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CK' => __( 'Cook Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CR' => __( 'Costa Rica', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'HR' => __( 'Croatia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CU' => __( 'Cuba', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CW' => __( 'Cura&Ccedil;ao', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CY' => __( 'Cyprus', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CZ' => __( 'Czech Republic', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'DK' => __( 'Denmark', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'DJ' => __( 'Djibouti', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'DM' => __( 'Dominica', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'DO' => __( 'Dominican Republic', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'EC' => __( 'Ecuador', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'EG' => __( 'Egypt', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SV' => __( 'El Salvador', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GQ' => __( 'Equatorial Guinea', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ER' => __( 'Eritrea', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'EE' => __( 'Estonia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ET' => __( 'Ethiopia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'FK' => __( 'Falkland Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'FO' => __( 'Faroe Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'FJ' => __( 'Fiji', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'FI' => __( 'Finland', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'FR' => __( 'France', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GF' => __( 'French Guiana', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PF' => __( 'French Polynesia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TF' => __( 'French Southern Territories', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GA' => __( 'Gabon', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GM' => __( 'Gambia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GE' => __( 'Georgia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'DE' => __( 'Germany', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GH' => __( 'Ghana', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GI' => __( 'Gibraltar', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GR' => __( 'Greece', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GL' => __( 'Greenland', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GD' => __( 'Grenada', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GP' => __( 'Guadeloupe', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GT' => __( 'Guatemala', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GG' => __( 'Guernsey', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GN' => __( 'Guinea', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GW' => __( 'Guinea-Bissau', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GY' => __( 'Guyana', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'HT' => __( 'Haiti', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'HM' => __( 'Heard Island and McDonald Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'HN' => __( 'Honduras', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'HK' => __( 'Hong Kong', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'HU' => __( 'Hungary', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IS' => __( 'Iceland', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IN' => __( 'India', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ID' => __( 'Indonesia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IR' => __( 'Iran', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IQ' => __( 'Iraq', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IE' => __( 'Republic of Ireland', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IM' => __( 'Isle of Man', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IL' => __( 'Israel', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'IT' => __( 'Italy', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CI' => __( 'Ivory Coast', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'JM' => __( 'Jamaica', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'JP' => __( 'Japan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'JE' => __( 'Jersey', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'JO' => __( 'Jordan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KZ' => __( 'Kazakhstan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KE' => __( 'Kenya', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KI' => __( 'Kiribati', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KW' => __( 'Kuwait', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KG' => __( 'Kyrgyzstan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LA' => __( 'Laos', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LV' => __( 'Latvia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LB' => __( 'Lebanon', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LS' => __( 'Lesotho', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LR' => __( 'Liberia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LY' => __( 'Libya', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LI' => __( 'Liechtenstein', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LT' => __( 'Lithuania', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LU' => __( 'Luxembourg', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MO' => __( 'Macao S.A.R., China', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MK' => __( 'Macedonia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MG' => __( 'Madagascar', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MW' => __( 'Malawi', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MY' => __( 'Malaysia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MV' => __( 'Maldives', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ML' => __( 'Mali', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MT' => __( 'Malta', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MH' => __( 'Marshall Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MQ' => __( 'Martinique', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MR' => __( 'Mauritania', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MU' => __( 'Mauritius', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'YT' => __( 'Mayotte', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MX' => __( 'Mexico', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'FM' => __( 'Micronesia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MD' => __( 'Moldova', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MC' => __( 'Monaco', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MN' => __( 'Mongolia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ME' => __( 'Montenegro', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MS' => __( 'Montserrat', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MA' => __( 'Morocco', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MZ' => __( 'Mozambique', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MM' => __( 'Myanmar', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NA' => __( 'Namibia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NR' => __( 'Nauru', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NP' => __( 'Nepal', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NL' => __( 'Netherlands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AN' => __( 'Netherlands Antilles', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NC' => __( 'New Caledonia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NZ' => __( 'New Zealand', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NI' => __( 'Nicaragua', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NE' => __( 'Niger', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NG' => __( 'Nigeria', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NU' => __( 'Niue', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NF' => __( 'Norfolk Island', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KP' => __( 'North Korea', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'NO' => __( 'Norway', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'OM' => __( 'Oman', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PK' => __( 'Pakistan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PS' => __( 'Palestinian Territory', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PA' => __( 'Panama', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PG' => __( 'Papua New Guinea', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PY' => __( 'Paraguay', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PE' => __( 'Peru', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PH' => __( 'Philippines', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PN' => __( 'Pitcairn', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PL' => __( 'Poland', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PT' => __( 'Portugal', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'QA' => __( 'Qatar', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'RE' => __( 'Reunion', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'RO' => __( 'Romania', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'RU' => __( 'Russia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'RW' => __( 'Rwanda', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'BL' => __( 'Saint Barth&eacute;lemy', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SH' => __( 'Saint Helena', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KN' => __( 'Saint Kitts and Nevis', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LC' => __( 'Saint Lucia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'MF' => __( 'Saint Martin (French part)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SX' => __( 'Saint Martin (Dutch part)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'PM' => __( 'Saint Pierre and Miquelon', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'VC' => __( 'Saint Vincent and the Grenadines', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SM' => __( 'San Marino', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SA' => __( 'Saudi Arabia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SN' => __( 'Senegal', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'RS' => __( 'Serbia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SC' => __( 'Seychelles', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SL' => __( 'Sierra Leone', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SG' => __( 'Singapore', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SK' => __( 'Slovakia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SI' => __( 'Slovenia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SB' => __( 'Solomon Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SO' => __( 'Somalia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ZA' => __( 'South Africa', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GS' => __( 'South Georgia/Sandwich Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'KR' => __( 'South Korea', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SS' => __( 'South Sudan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ES' => __( 'Spain', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'LK' => __( 'Sri Lanka', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SD' => __( 'Sudan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SR' => __( 'Suriname', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SJ' => __( 'Svalbard and Jan Mayen', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SZ' => __( 'Swaziland', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SE' => __( 'Sweden', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'CH' => __( 'Switzerland', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'SY' => __( 'Syria', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TW' => __( 'Taiwan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TJ' => __( 'Tajikistan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TZ' => __( 'Tanzania', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TH' => __( 'Thailand', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TL' => __( 'Timor-Leste', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TG' => __( 'Togo', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TK' => __( 'Tokelau', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TO' => __( 'Tonga', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TT' => __( 'Trinidad and Tobago', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TN' => __( 'Tunisia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TR' => __( 'Turkey', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TM' => __( 'Turkmenistan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TC' => __( 'Turks and Caicos Islands', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'TV' => __( 'Tuvalu', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'UG' => __( 'Uganda', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'UA' => __( 'Ukraine', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'AE' => __( 'United Arab Emirates', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'GB' => __( 'United Kingdom (UK)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'US' => __( 'United States (US)', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'UY' => __( 'Uruguay', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'UZ' => __( 'Uzbekistan', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'VU' => __( 'Vanuatu', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'VA' => __( 'Vatican', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'VE' => __( 'Venezuela', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'VN' => __( 'Vietnam', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'WF' => __( 'Wallis and Futuna', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'EH' => __( 'Western Sahara', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'WS' => __( 'Western Samoa', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'YE' => __( 'Yemen', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ZM' => __( 'Zambia', ELECTROSUITE_RESELLER_TEXT_DOMAIN ),
			'ZW' => __( 'Zimbabwe', ELECTROSUITE_RESELLER_TEXT_DOMAIN )
		));

		// States set to array() are blank i.e. the country has no use for the state field.
		$states = array(
			'AF' => array(),
			'AT' => array(),
			'BE' => array(),
			'BI' => array(),
			'CZ' => array(),
			'DE' => array(),
			'DK' => array(),
			'EE' => array(),
			'FI' => array(),
			'FR' => array(),
			'IS' => array(),
			'IL' => array(),
			'KR' => array(),
			'NL' => array(),
			'NO' => array(),
			'PL' => array(),
			'PT' => array(),
			'SG' => array(),
			'SK' => array(),
			'SI' => array(),
			'LK' => array(),
			'SE' => array(),
			'VN' => array(),
		);

		// Load only the state files the site owner wants/needs
		$allowed = array_merge( $this->get_allowed_countries() );

		if ( $allowed ) {
			foreach ( $allowed as $CC => $country ) {
				if ( ! isset( $states[ $CC ] ) && file_exists( ElectroSuite_Reseller()->plugin_path() . '/includes/states/' . $CC . '.php' ) ) {
					include( ElectroSuite_Reseller()->plugin_path() . '/includes/states/' . $CC . '.php' );
				}
			}
		}

		$this->states = apply_filters( 'electrosuite_reseller_states', $states );
	}

	/**
	 * Get the base country for the store.
	 *
	 * @access public
	 * @return string
	 */
	public function get_base_country() {
		$default = esc_attr( get_option('electrosuite_reseller_default_country') );
		$country = ( ( $pos = strrpos( $default, ':' ) ) === false ) ? $default : substr( $default, 0, $pos );

		return apply_filters( 'electrosuite_reseller_countries_base_country', $country );
	}

	/**
	 * Get the base state for the store.
	 *
	 * @access public
	 * @return string
	 */
	public function get_base_state() {
		$default 	= electrosuite_reseller_clean( get_option( 'electrosuite_reseller_default_country' ) );
		$state 		= ( ( $pos = strrpos( $default, ':' ) ) === false ) ? '' : substr( $default, $pos + 1 );

		return apply_filters( 'electrosuite_reseller_countries_base_state', $state );
	}

	/**
	 * Get the base city for the store.
	 *
	 * @access public
	 * @return string
	 */
	public function get_base_city() {
		return apply_filters( 'electrosuite_reseller_countries_base_city', '' );
	}

	/**
	 * Get the base postcode for the store.
	 *
	 * @access public
	 * @return string
	 */
	public function get_base_postcode() {
		return apply_filters( 'electrosuite_reseller_countries_base_postcode', '' );
	}

	/**
	 * Get the allowed countries for the store.
	 *
	 * @access public
	 * @return array
	 */
	public function get_allowed_countries() {
		if ( apply_filters('electrosuite_reseller_sort_countries', true ) ) {
			asort( $this->countries );
		}

		if ( get_option('electrosuite_reseller_allowed_countries') !== 'specific' ) {
			return $this->countries;
		}

		$countries = array();

		$raw_countries = get_option( 'electrosuite_reseller_specific_allowed_countries' );

		foreach ( $raw_countries as $country ) {
			$countries[ $country ] = $this->countries[ $country ];
		}

		return apply_filters( 'electrosuite_reseller_countries_allowed_countries', $countries );
	}

	/**
	 * get_allowed_country_states function.
	 *
	 * @access public
	 * @return array
	 */
	public function get_allowed_country_states() {
		if ( get_option('electrosuite_reseller_allowed_countries') !== 'specific' ) {
			return $this->states;
		}

		$states = array();

		$raw_countries = get_option( 'electrosuite_reseller_specific_allowed_countries' );

		foreach ( $raw_countries as $country ) {
			if ( isset( $this->states[ $country ] ) ) {
				$states[ $country ] = $this->states[ $country ];
			}
		}

		return apply_filters( 'electrosuite_reseller_countries_allowed_country_states', $states );
	}

	/**
	 * Gets an array of countries in the EU.
	 *
	 * @access public
	 * @return array
	 */
	public function get_european_union_countries() {
		return array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' );
	}

	/**
	 * Prefix certain countries with 'the'
	 *
	 * @access public
	 * @return string
	 */
	public function estimated_for_prefix() {
		$return = '';
		if ( in_array( $this->get_base_country(), array( 'GB', 'US', 'AE', 'CZ', 'DO', 'NL', 'PH', 'USAF' ) ) ) $return = __( 'the', 'electrosuite_reseller' ) . ' ';
		return apply_filters('electrosuite_reseller_countries_estimated_for_prefix', $return, $this->get_base_country());
	}

	/**
	 * Get the states for a country.
	 *
	 * @access public
	 * @param string $cc country code
	 * @return array of states
	 */
	public function get_states( $cc ) {
		return ( isset( $this->states[ $cc ] ) ) ? $this->states[ $cc ] : array();
	}

	/**
	 * Outputs the list of countries and states for use in dropdown boxes.
	 *
	 * @access public
	 * @param string $selected_country (default: '')
	 * @param string $selected_state (default: '')
	 * @param bool $escape (default: false)
	 * @return void
	 */
	public function country_dropdown_options( $selected_country = '', $selected_state = '', $escape = false ) {
		if ( apply_filters('electrosuite_reseller_sort_countries', true ) ) {
			asort( $this->countries );
		}

		if ( $this->countries ) foreach ( $this->countries as $key => $value) {
			if ( $states =  $this->get_states($key) ) {
				echo '<optgroup label="' . esc_attr( $value ) . '">';
					foreach ($states as $state_key=>$state_value) {
						echo '<option value="' . esc_attr( $key ) . ':'.$state_key.'"';

						if ($selected_country==$key && $selected_state==$state_key) echo ' selected="selected"';

						echo '>'.$value.' &mdash; '. ($escape ? esc_js($state_value) : $state_value) .'</option>';
					}
				echo '</optgroup>';
			}
			else {
				echo '<option';
				if ( $selected_country == $key && $selected_state == '*' ) echo ' selected="selected"';
				echo ' value="' . esc_attr( $key ) . '">'. ($escape ? esc_js( $value ) : $value) .'</option>';
			}
		}
	}

}

?>