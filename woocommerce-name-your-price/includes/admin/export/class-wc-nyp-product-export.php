<?php
/**
 * Product Export Class
 *
 * @package  WooCommerce Name Your Price/Admin/Export
 * @since    3.5.0
 * @version  3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_NYP_Product_Export Class.
 *
 * Add support for MNM products to WooCommerce product export.
 */
class WC_NYP_Product_Export {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Add CSV columns for exporting container data.
		add_filter( 'woocommerce_product_export_column_names', array( __CLASS__, 'add_columns' ) );
		add_filter( 'woocommerce_product_export_product_default_columns', array( __CLASS__, 'add_columns' ) );

		// "NYP" column data.
		add_filter( 'woocommerce_product_export_product_column_wc_nyp_is_nyp', array( __CLASS__, 'export_is_nyp' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_wc_nyp_has_nyp', array( __CLASS__, 'export_has_nyp' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_wc_nyp_suggested_price', array( __CLASS__, 'export_suggested_price' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_wc_nyp_minimum_price', array( __CLASS__, 'export_minimum_price' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_wc_nyp_maximum_price', array( __CLASS__, 'export_maximum_price' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_wc_nyp_hide_min', array( __CLASS__, 'export_hide_min' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_wc_nyp_hide_variable_price', array( __CLASS__, 'export_hide_variable_price' ), 10, 2 );

	}


	/**
	 * Add CSV columns for exporting container data.
	 *
	 * @param  array  $columns
	 * @return array  $columns
	 */
	public static function add_columns( $columns ) {

		$columns['wc_nyp_is_nyp']              = __( 'Is NYP?', 'wc_name_your_price' );
		$columns['wc_nyp_has_nyp']             = __( 'Has NYP?', 'wc_name_your_price' );
		$columns['wc_nyp_suggested_price']     = __( 'Suggested price', 'wc_name_your_price' );
		$columns['wc_nyp_minimum_price']       = __( 'Minimum price', 'wc_name_your_price' );
		$columns['wc_nyp_maximum_price']       = __( 'Maximum price', 'wc_name_your_price' );
		$columns['wc_nyp_hide_min']            = __( 'Hide NYP min?', 'wc_name_your_price' );
		$columns['wc_nyp_hide_variable_price'] = __( 'Hide NYP variable price?', 'wc_name_your_price' );

		/**
		 * Name Your Price Export columns.
		 *
		 * @param  array $columns
		 */
		return apply_filters( 'wc_nyp_export_column_names', $columns );
	}

	/**
	 * "Is NYP" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_is_nyp( $value, $product ) {
		return wc_string_to_bool( $product->get_meta( '_nyp', true, 'edit' ) ) ? 1 : 0;
	}

	/**
	 * "Has NYP" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_has_nyp( $value, $product ) {
		return wc_string_to_bool( $product->get_meta( '_has_nyp', true, 'edit' ) ) ? 1 : 0;
	}


	/**
	 * Suggested price column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_suggested_price( $value, $product ) {
		$price = $product->get_meta( '_suggested_price', true, 'edit' );
		return $price ? wc_format_localized_price( $price ) : '';
	}

	/**
	 * "Min Price" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_minimum_price( $value, $product ) {
		$price = $product->get_meta( '_min_price', true, 'edit' );
		return $price ? wc_format_localized_price( $price ) : '';
	}

	/**
	 * "Max Price" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_maximum_price( $value, $product ) {
		$price = $product->get_meta( '_maximum_price', true, 'edit' );
		return $price ? wc_format_localized_price( $price ) : '';
	}

	/**
	 * "Hide Min" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_hide_min( $value, $product ) {
		return wc_string_to_bool( $product->get_meta( '_hide_nyp_minimum', true, 'edit' ) ) ? 1 : 0;
	}

	/**
	 * "Hide Variable" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_hide_variable_price( $value, $product ) {
		return wc_string_to_bool( $product->get_meta( '_nyp_hide_variable_price', true, 'edit' ) ) ? 1 : 0;
	}

}

WC_NYP_Product_Export::init();
