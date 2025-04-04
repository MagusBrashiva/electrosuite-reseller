<?php
/**
 * ElectroSuite_Reseller_Shortcodes class.
 *
 * @class 		ElectroSuite_Reseller_Shortcodes
 * @package		ElectroSuite Reseller/Classes
 * @category	Class
 * @author 		ElectroSuite
 */
class ElectroSuite_Reseller_Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		// Define shortcodes
		$shortcodes = array(
			'sample'               => __CLASS__ . '::sample',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper
	 *
	 * @access public
	 * @param mixed $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public function shortcode_wrapper(
		$function, $atts = array(), $wrapper = array( 'class' => 'electrosuite_reseller', 'before' => null, 'after' => null ) ){
		ob_start();

		$before 	= empty( $wrapper['before'] ) ? '<div class="' . $wrapper['class'] . '">' : $wrapper['before'];
		$after 		= empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	 * Sample shortcode.
	 *
	 * @access public
	 * @param mixed $atts
	 * @return string
	 */
	public static function sample( $atts ) {
		return $this->shortcode_wrapper( array( 'ElectroSuite_Reseller_Shortcode_Sample', 'output' ), $atts );
	}

}
?>