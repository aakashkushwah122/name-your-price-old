<?php
/**
 * Grouped Products Compatibility
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
 * The Main WC_NYP_Grouped_Products_Compatibility class
 **/
class WC_NYP_Grouped_Products_Compatibility {


	/**
	 * WC_NYP_Grouped_Products_Compatibility Constructor
	 *
	 * @since 3.0.0
	 */
	public static function init() {

		// Grouped product support.
		add_filter( 'woocommerce_grouped_product_list_column_price', array( __CLASS__, 'display_input' ), 10, 2 );
		add_action( 'woocommerce_grouped_add_to_cart', array( __CLASS__, 'add_filter_for_nyp_attributes' ), 0 );
		add_action( 'woocommerce_grouped_add_to_cart', array( __CLASS__, 'remove_filter_for_nyp_attributes' ), 9999 );
		add_filter( 'wc_nyp_field_suffix', array( __CLASS__, 'grouped_cart_suffix' ), 10, 2 );

	}

	/**
	 * Display the price input with a named suffix to distinguish it from other NYP inputs on the same page.
	 *
	 * @param str            $html
	 * @param obj WC_Product $product
	 * @return str
	 */
	public static function display_input( $html, $product ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {

			$nyp_id = $product->get_id();
			$suffix = '-grouped-' . $nyp_id;

			ob_start();
			WC_Name_Your_Price()->display->display_price_input( $nyp_id, $suffix );
			$input = ob_get_clean();

			$html .= $input;
		}

		return $html;

	}

	/**
	 * Check for the suffix when adding to cart.
	 *
	 * @param string $suffix
	 * @param  int    $nyp_id the product ID or variation ID of the NYP product being displayed
	 * @return string
	 */
	public static function grouped_cart_suffix( $suffix, $nyp_id ) {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! empty( $_REQUEST['quantity'] ) && is_array( $_REQUEST['quantity'] ) && isset( $_REQUEST['quantity'][ $nyp_id ] ) ) {
			$suffix = '-grouped-' . $nyp_id;
		}

		return $suffix;
	}

	/**
	 * Add filter for data attributes.
	 *
	 * @since 3.0.0
	 */
	public static function add_filter_for_nyp_attributes() {
		add_filter( 'wc_nyp_data_attributes', array( __CLASS__, 'optional_nyp_attributes' ), 10, 2 );
	}

	/**
	 * Remove filter for data attributes.
	 *
	 * @since 3.0.0
	 */
	public static function remove_filter_for_nyp_attributes() {
		remove_filter( 'wc_nyp_data_attributes', array( __CLASS__, 'optional_nyp_attributes' ), 10, 2 );
	}


	/**
	 * Mark products as optional.
	 *
	 * @since 3.0.0
	 * @param array      $attributes - The data attributes on the NYP div.
	 * @param  WC_Product $product
	 * @return array
	 */
	public static function optional_nyp_attributes( $attributes, $product ) {
		$attributes['optional'] = 'yes';
		return $attributes;
	}


} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_Grouped_Products_Compatibility::init();
