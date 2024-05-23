<?php
/**
 * Stripe Gateway Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.0.0
 * @version  3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_Stripe_Compatibility class
 **/
class WC_NYP_Stripe_Compatibility {


	/**
	 * WC_NYP_Stripe_Compatibility Constructor
	 *
	 * @since 3.0.0
	 */
	public static function init() {

		// Hide Stripe's payment request buttons on NYP product.
		add_filter( 'wc_stripe_hide_payment_request_on_product_page', array( __CLASS__, 'hide_request_on_nyp' ), 10, 2 );

	}

	/**
	 * Hide Stripe's instant pay buttons
	 *
	 * @param   bool        $hide
	 * @param   obj WP_POST
	 * @return  bool
	 */
	public static function hide_request_on_nyp( $hide, $post ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $post->ID ) || WC_Name_Your_Price_Helpers::has_nyp( $post->ID ) ) {
			$hide = true;
		}
		return $hide;
	}


} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_Stripe_Compatibility::init();
