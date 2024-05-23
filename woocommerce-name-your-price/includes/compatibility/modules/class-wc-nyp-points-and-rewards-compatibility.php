<?php
/**
 * Points and Rewards Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.0.0
 * @version  3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_Points_and_Rewards_Compatibility class
 **/
class WC_NYP_Points_and_Rewards_Compatibility {

	/**
	 * Attach hooks and filters
	 */
	public static function init() {
		add_filter( 'option_wc_points_rewards_single_product_message', array( __CLASS__, 'single_product_message_option' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'inline_script' ), 30 );
	}

	/**
	 * Modify the Points message tp wrap points in identifying element.
	 */
	public static function single_product_message_option( $message ) {
		return str_replace( '{points}', '<span class="points">{points}</span>', $message );
	}

	/**
	 * Dyanmically update the Points message for NYP products.
	 */
	public static function inline_script() {

		wp_add_inline_script(
			'woocommerce-nyp',
			'
				
			jQuery(".cart").on("wc-nyp-updated",".nyp", function( e, nypProduct ) {

				var $points = jQuery(e.delegateTarget).find( ".wc-points-rewards-product-message .points" );
				var nyp_points = wc_nyp_format_number( nypProduct.user_price, { num_decimals: 0 } );
				$points.html( wc_nyp_format_number( nypProduct.user_price, { num_decimals: 0 } ) );
		
			} );

		'
		);
	}


} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_Points_and_Rewards_Compatibility::init();
