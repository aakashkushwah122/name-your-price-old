<?php
/**
 * WC_NYP_Blocks_Compatibility class
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.5.0
 * @version  3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Blocks Compatibility.
 */
class WC_NYP_Blocks_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {

		if ( ! did_action( 'woocommerce_blocks_loaded' ) ) {
			return;
		}

		require_once WC_Name_Your_Price()->plugin_path() . '/includes/api/class-wc-nyp-store-api.php';
		WC_NYP_Store_API::init();

	}
}

WC_NYP_Blocks_Compatibility::init();
