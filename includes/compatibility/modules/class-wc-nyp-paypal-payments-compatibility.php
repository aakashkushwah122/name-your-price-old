<?php
/**
 * PayPal Payments Gateway Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.3.10
 * @version  3.3.10
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_PayPal_Payments_Compatibility class
 **/
class WC_NYP_PayPal_Payments_Compatibility {


	/**
	 * WC_NYP_PayPal_Payments_Compatibility Constructor
	 */
	public static function init() {
		add_action( 'woocommerce_paypal_payments_product_supports_payment_request_button', array( __CLASS__, 'hide_request_buttons' ), 10, 2 );
	}


	/**
	 * Hide PayPal's payment request buttons
	 *
	 * @param   bool $hide
	 * @param   obj WC_Product
	 * @return  bool
	 */
	public static function hide_request_buttons( $hide, $product ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) || WC_Name_Your_Price_Helpers::has_nyp( $product ) ) {
			$hide = false;

		}
		return $hide;
	}


} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_PayPal_Payments_Compatibility::init();
