<?php
/**
 * CoCart Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.1.0
 * @version  3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_CoCart_Compatibility class
 **/
class WC_NYP_CoCart_Compatibility {

	/**
	 * WC_NYP_CoCart_Compatibility Constructor
	 */
	public static function init() {
		add_filter( 'cocart_add_to_cart_validation', array( __CLASS__, 'add_to_cart_validation' ), 10, 6 );
	}

	/**
	 * Validate an NYP product before adding to cart.
	 *
	 * @param  int    $product_id     - Contains the ID of the product.
	 * @param  int    $quantity       - Contains the quantity of the item.
	 * @param  int    $variation_id   - Contains the ID of the variation.
	 * @param  array  $variation      - Attribute values.
	 * @param  array  $item_data - Extra cart item data we want to pass into the item.
	 * @return bool|WP_Error
	 */
	public static function add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '', $item_data = array() ) {

		$nyp_id = $variation_id ? $variation_id : $product_id;

		// Skip if not a NYP product - send original status back.
		if ( ! WC_Name_Your_Price_Helpers::is_nyp( $nyp_id ) ) {
			return $passed;
		}

		// Get the posted price.
		$price = isset( $item_data['nyp'] ) ? WC_Name_Your_Price_Helpers::standardize_number( $item_data['nyp'] ) : '';

		// Get the posted billing period.
		$period = isset( $item_data['nyp_period'] ) ? trim( wc_clean( $item_data['nyp_period'] ) ) : '';

		// Validate.
		try {

			WC_Name_Your_Price()->cart->validate_price( $nyp_id, $quantity, $price, $period, array( 'throw_exception' => true ) );

		} catch ( Exception $e ) {

			if ( $e->getMessage() ) {

				$passed = new WP_Error( 'cocart_cannot_add_product_to_cart', html_entity_decode( wp_strip_all_tags( $e->getMessage() ) ) );

			}
		}

		return $passed;

	}

} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_CoCart_Compatibility::init();
