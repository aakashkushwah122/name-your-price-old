<?php
/**
 * WooCommerce Payments Gateway Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.3.7
 * @version  3.3.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_WCPay_Compatibility class
 **/
class WC_NYP_WCPay_Compatibility {


	/**
	 * WC_NYP_WCPay_Compatibility Constructor
	 */
	public static function init() {
		add_filter( 'wcpay_payment_request_is_product_supported', array( __CLASS__, 'hide_request_buttons' ), 10, 2 );
	}


	/**
	 * Hide payment request pay buttons
	 *
	 * @param   bool        $supported
	 * @param   WC_Product  $product
	 * @return  bool
	 */
	public static function hide_request_buttons( $supported, $product ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) || WC_Name_Your_Price_Helpers::has_nyp( $product ) ) {
			$supported = false;
		}
		return $supported;
	}


} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_WCPay_Compatibility::init();
