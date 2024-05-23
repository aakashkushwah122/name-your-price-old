<?php
/**
 * Stripe Gateway Compatibility
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
 * The Main WC_NYP_Braintree_PayPal_Compatibility class
 **/
class WC_NYP_Braintree_PayPal_Compatibility {


	/**
	 * WC_NYP_Braintree_PayPal_Compatibility Constructor
	 */
	public static function init() {

		// Hide payment request buttons on NYP product.
		add_filter( 'wc_braintree_paypal_product_buy_now_enabled', array( __CLASS__, 'hide_request_on_nyp' ), 10, 2 );

	}

	/**
	 * Hide instant pay buttons
	 *
	 * @param bool $enabled whether product buy now buttons are enabled in the settings
	 * @param \WC_Gateway_Braintree_PayPal $gateway gateway object
	 * @return  bool
	 */
	public static function hide_request_on_nyp( $show, $gateway ) {

		if ( is_product() ) {
			global $post;
			$product = wc_get_product( $post );

			if ( class_exists( 'WC_Name_Your_Price_Helpers' ) && ( WC_Name_Your_Price_Helpers::is_nyp( $product ) || WC_Name_Your_Price_Helpers::has_nyp( $product ) ) ) {
				$show = false;
			}
		}

		return $show;
	}


} // End class: do not remove or there will be no more guacamole for you.
WC_NYP_Braintree_PayPal_Compatibility::init();
