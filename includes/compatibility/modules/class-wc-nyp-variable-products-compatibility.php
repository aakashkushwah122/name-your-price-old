<?php
/**
 * Variable Products Compatibility
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
 * The Main WC_NYP_Variable_Products_Compatibility class
 **/
class WC_NYP_Variable_Products_Compatibility {


	/**
	 * WC_NYP_Variable_Products_Compatibility Constructor
	 *
	 * @since 3.0.0
	 */
	public static function init() {

		// Variable products- sync has_nyp status of parent.
		add_action( 'woocommerce_variable_product_sync_data', array( __CLASS__, 'variable_sync_has_nyp_status' ) );

		if ( ! WC_Name_Your_Price_Core_Compatibility::is_wc_version_gte( '4.4.0' ) ) {
			// Trigger whenever product is saved. Solves issue #25552" where "WC_Product_Variable::sync() does not run when a variation is removed in WC<4.4.
			add_action( 'wc_nyp_variable_product_sync_data', array( __CLASS__, 'variable_sync_has_nyp_status' ) );
		}

	}

	/**
	 * Sync variable product has_nyp status.
	 *
	 * @param   WC_Product $product
	 * @return  void
	 * @since   3.0.0
	 */
	public static function variable_sync_has_nyp_status( $product ) {
		$product->delete_meta_data( '_has_nyp' );
		$product->delete_meta_data( '_nyp_hide_variable_price' );

		// Only run on supported types.
		if ( $product->is_type( WC_Name_Your_Price_Helpers::get_variable_supported_types() ) ) {

			global $wpdb;

			$variation_ids = $product ? $product->get_children() : array();

			if ( empty( $variation_ids ) ) {
				return;
			}

			$variation_id_placeholders = implode( ', ', array_fill( 0, count( $variation_ids ), '%d' ) );

			$nyp_variation_count = $wpdb->get_var(
				$wpdb->prepare( "SELECT count(post_id) FROM $wpdb->postmeta WHERE post_id IN ( $variation_id_placeholders ) AND meta_key = '_nyp' AND meta_value = 'yes' LIMIT 1", $variation_ids ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);

			// Has NYP variations.
			if ( 0 < $nyp_variation_count ) {

				$product->add_meta_data( '_has_nyp', 'yes', true );

				// Check if minimum priced-variation has the minimum hidden or a null minimum.
				$variation_prices = $product->get_variation_prices();

				$min_variation_id    = key( $variation_prices['price'] );
				$min_variation_price = $variation_prices['price'][ $min_variation_id ];
				$min_variation       = wc_get_product( $min_variation_id );

				// If the cheapest variation is NYP and has no price (or min is hidden... save a meta flag on the parent).
				if ( $min_variation && WC_Name_Your_Price_Helpers::is_nyp( $min_variation ) ) {
					if ( false === WC_Name_Your_Price_Helpers::get_minimum_price( $min_variation ) || WC_Name_Your_Price_Helpers::is_minimum_hidden( $min_variation ) ) {
						$product->add_meta_data( '_nyp_hide_variable_price', 'yes', true );
					}
				}
			}
		}
	}


} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_Variable_Products_Compatibility::init();
