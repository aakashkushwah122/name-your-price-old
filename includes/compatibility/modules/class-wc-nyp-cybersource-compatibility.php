<?php
/**
 * Cybersource Gateway Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.3.10
 * @version  3.5.13
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_Cybersource_Compatibility class
 **/
class WC_NYP_Cybersource_Compatibility {


	/**
	 * WC_NYP_Cybersource_Compatibility Constructor
	 */
	public static function init() {

		// Hide Google Pay payment request buttons on NYP product.
		add_filter( 'pre_option_sv_wc_google_pay_enabled', array( __CLASS__, 'hide_request_on_nyp' ), 10, 2 );

		// Hide Apple Pay payment request buttons on NYP product.
		add_filter( 'pre_option_sv_wc_apple_pay_enabled', array( __CLASS__, 'hide_request_on_nyp' ), 10, 2 );

	}

	/**
	 * Hide instant pay buttons
	 *
	 * @param false|mixed $value   Pre-option value. Default false.
	 * @return false|mixed (Maybe) filtered pre-option value.
	 */
	public static function hide_request_on_nyp( $pre_option ) {

		if ( is_product() ) {
			global $post;
			$product = wc_get_product( $post );

			if ( class_exists( 'WC_Name_Your_Price_Helpers' ) && ( WC_Name_Your_Price_Helpers::is_nyp( $product ) || WC_Name_Your_Price_Helpers::has_nyp( $product ) ) ) {
				$pre_option = 'no';
			}
		}

		return $pre_option;
	}


} // End class: do not remove or there will be no more guacamole for you.
WC_NYP_Cybersource_Compatibility::init();
