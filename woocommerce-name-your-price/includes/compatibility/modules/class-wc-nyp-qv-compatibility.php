<?php
/**
 * Quick View Compatibility
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
 * The Main WC_NYP_QV_Compatibility class
 **/
class WC_NYP_QV_Compatibility {

	/**
	 * WC_NYP_QV_Compatibility Constructor
	 *
	 * @since 3.0.0
	 */
	public static function init() {

		// QuickView support.
		add_action( 'wc_quick_view_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
	}

	/**
	 * Load scripts for use by QV on non-product pages.
	 */
	public static function load_scripts() {

		if ( ! is_product() ) {

			WC_Name_Your_Price()->display->register_scripts();
			WC_Name_Your_Price()->display->nyp_scripts();

		}
	}

} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_QV_Compatibility::init();
