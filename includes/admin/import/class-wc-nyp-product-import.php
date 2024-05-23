<?php
/**
 * Product Import Class
 *
 * @package  WooCommerce Mix and Match Products/Admin/Import
 * @since    3.5.0
 * @version  3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_NYP_Product_Import Class.
 *
 * Add support for MNM products to WooCommerce product import.
 */
class WC_NYP_Product_Import {

	/**
	 * var WC_Product_CSV_Importer Class.
	 */
	private $importer = false;

	/**
	 * Hook in.
	 */
	public static function init() {

		// Map custom column titles.
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'map_columns' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( __CLASS__, 'add_columns_to_mapping_screen' ) );

		// Set meta. // Is this necessary for meta data?
		add_filter( 'woocommerce_product_import_pre_insert_product_object', array( __CLASS__, 'set_meta' ), 10, 2 );
	}

	/**
	 * Register the 'Custom' columns in the importer.
	 *
	 * @param  array  $columns
	 * @return array  $columns
	 */
	public static function map_columns( $columns ) {

		$columns['name-your-price'] = array(
			'name'    => __( 'Name Your Price', 'wc_name_your_price' ),
			'options' => array(
				'wc_nyp_is_nyp'              => __( 'Is NYP?', 'wc_name_your_price' ),
				'wc_nyp_has_nyp'             => __( 'Has NYP?', 'wc_name_your_price' ),
				'wc_nyp_suggested_price'     => __( 'Suggested price', 'wc_name_your_price' ),
				'wc_nyp_minimum_price'       => __( 'Minimum price', 'wc_name_your_price' ),
				'wc_nyp_maximum_price'       => __( 'Maximum price', 'wc_name_your_price' ),
				'wc_nyp_hide_min_price'      => __( 'Hide min?', 'wc_name_your_price' ),
				'wc_nyp_hide_variable_price' => __( 'Hide NYP variable price?', 'wc_name_your_price' ),
			),
		);

		return apply_filters( 'wc_nyp_csv_product_import_mapping_options', $columns );

	}

	/**
	 * Add automatic mapping support for custom columns.
	 *
	 * @param  array  $columns
	 * @return array  $columns
	 */
	public static function add_columns_to_mapping_screen( $columns ) {

		$columns[ __( 'Is NYP?', 'wc_name_your_price' ) ]              = 'wc_nyp_is_nyp';
		$columns[ __( 'Has NYP?', 'wc_name_your_price' ) ]             = 'wc_nyp_has_nyp';
		$columns[ __( 'Suggested price', 'wc_name_your_price' ) ]      = 'wc_nyp_suggested_price';
		$columns[ __( 'Minimum price', 'wc_name_your_price' ) ]        = 'wc_nyp_minimum_price';
		$columns[ __( 'Maximum price', 'wc_name_your_price' ) ]        = 'wc_nyp_maximum_price';
		$columns[ __( 'Hide min?', 'wc_name_your_price' ) ]            = 'wc_nyp_hide_min_price';
		$columns[ __( 'Hide variable price?', 'wc_name_your_price' ) ] = 'wc_nyp_hide_variable_price';

		// Always add English mappings.
		$columns['Is NYP?']              = 'wc_nyp_is_nyp';
		$columns['Has NYP?']             = 'wc_nyp_has_nyp';
		$columns['Suggested price']      = 'wc_nyp_suggested_price';
		$columns['Minimum price']        = 'wc_nyp_minimum_price';
		$columns['Maximum price']        = 'wc_nyp_maximum_price';
		$columns['Hide min?']            = 'wc_nyp_hide_min_price';
		$columns['Hide variable price?'] = 'wc_nyp_hide_variable_price';

		return apply_filters( 'wc_nyp_csv_product_import_mapping_default_columns', $columns );
	}

	/**
	 * Set meta.
	 *
	 * @param  array  $parsed_data
	 * @return array
	 */
	public static function set_meta( $product, $data ) {

		if ( $product instanceof WC_Product ) {

			if ( $product->is_type( WC_Name_Your_Price_Helpers::get_simple_supported_types() ) ) {

				// Booleans.
				if ( isset( $data['wc_nyp_is_nyp'] ) ) {
					$product->add_meta_data( '_nyp', wc_bool_to_string( $data['wc_nyp_is_nyp'] ), true );
				}

				if ( isset( $data['wc_nyp_hide_min_price'] ) ) {
					$product->add_meta_data( '_hide_nyp_minimum', wc_bool_to_string( $data['wc_nyp_hide_min_price'] ), true );
				}

				// Prices.
				if ( ! empty( $data['wc_nyp_suggested_price'] ) ) {
					$product->add_meta_data( '_suggested_price', wc_format_decimal( $data['wc_nyp_suggested_price'] ), true );
				}

				if ( ! empty( $data['wc_nyp_minimum_price'] ) ) {
					$product->add_meta_data( '_min_price', wc_format_decimal( $data['wc_nyp_minimum_price'] ), true );
				}

				if ( ! empty( $data['wc_nyp_maximum_price'] ) ) {
					$product->add_meta_data( '_maximum_price', wc_format_decimal( $data['wc_nyp_maximum_price'] ), true );
				}
			} elseif ( $product->is_type( WC_Name_Your_Price_Helpers::get_variable_supported_types() ) ) {

				// Booleans.
				if ( isset( $data['wc_nyp_has_nyp'] ) ) {
					$product->add_meta_data( '_has_nyp', wc_bool_to_string( $data['wc_nyp_has_nyp'] ), true );
				}

				if ( isset( $data['wc_nyp_hide_variable_price'] ) ) {
					$product->add_meta_data( '_nyp_hide_variable_price', wc_bool_to_string( $data['wc_nyp_hide_variable_price'] ), true );
				}
			}
		}

		return $product;
	}

}
WC_NYP_Product_Import::init();
