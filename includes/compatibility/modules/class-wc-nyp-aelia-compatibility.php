<?php
/**
 * WC_NYP_Aelia_Compatibility class
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.5.0
 * @version  3.5.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Blocks Compatibility.
 */
class WC_NYP_Aelia_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {

		// Store additional cart data.
		add_action( 'woocommerce_add_cart_item', array( __CLASS__, 'add_initial_currency' ) );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'convert_cart_currency' ), 20, 3 );

		// Convert cart editing price.
		add_filter( 'wc_nyp_edit_in_cart_args', array( __CLASS__, 'edit_in_cart_args' ), 10, 2 );
		add_filter( 'wc_nyp_get_initial_price', array( __CLASS__, 'get_posted_price' ), 10, 3 );

		// Convert NYP prices.
		add_filter( 'wc_nyp_raw_suggested_price', array( __CLASS__, 'convert_nyp_prices' ), 10, 3 );
		add_filter( 'wc_nyp_raw_minimum_price', array( __CLASS__, 'convert_nyp_prices' ), 10, 3 );
		add_filter( 'wc_nyp_raw_maximum_price', array( __CLASS__, 'convert_nyp_prices' ), 10, 3 );

		// Admin metabox fields.
		add_action( 'wc_nyp_options_pricing', array( __CLASS__, 'pricing_options' ), 100, 2 );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_meta' ) );

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Cart callbacks.
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Store the inintial currency when item is added.
	 *
	 * @static
	 * @param array $cart_item
	 * @return array
	 */
	public static function add_initial_currency( $cart_item ) {

		$nyp_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];

		if ( WC_Name_Your_Price_Helpers::is_nyp( $nyp_id ) && isset( $cart_item['nyp'] ) ) {
			$cart_item['nyp_currency'] = get_woocommerce_currency();
			$cart_item['nyp_original'] = $cart_item['nyp'];
		}

		return $cart_item;
	}

	/**
	 * Switch the cart price when currency changes.
	 *
	 * @param bool $remove_cart_item_from_session If true, the item will not be added to the cart. Default: false.
	 * @param array $values Cart item values e.g. quantity and product_id.
	 * @param string $key Cart item key.
	 * @return array
	 */
	public static function convert_cart_currency( $cart_item, $values, $key ) {

		if ( isset( $cart_item['nyp_original'] ) && isset( $cart_item['nyp_currency'] ) ) {

			// If the currency changed, convert the price entered by the customer into the active currency.
			if ( $cart_item['nyp_currency'] !== get_woocommerce_currency() ) {
				$new_price = self::convert_price( $cart_item['nyp_original'], $cart_item['nyp_currency'] );
				// Otherwise, put it back to the original amount.
			} else {
				$new_price = $cart_item['nyp_original'];
			}

			$cart_item['nyp'] = $new_price;
			$cart_item['data']->set_price( $new_price );
			$cart_item['data']->set_regular_price( $new_price );
			$cart_item['data']->set_sale_price( $new_price );

		}

		return $cart_item;
	}

	/**
	 * Add currency to cart edit link.
	 *
	 * @param bool $remove_cart_item_from_session If true, the item will not be added to the cart. Default: false.
	 * @param array $values Cart item values e.g. quantity and product_id.
	 * @param string $key Cart item key.
	 * @return array
	 */
	public static function edit_in_cart_args( $args, $cart_item ) {
		$args['nyp_currency'] = get_woocommerce_currency();
		return $args;
	}


	/**
	 * Maybe convert any prices being edited from the cart
	 *
	 * @param string $posted_price
	 * @param mixed WC_Product $product
	 * @param string $suffix
	 * @return string
	 */
	public static function get_posted_price( $posted_price, $product, $suffix ) {

		if ( isset( $_REQUEST[ 'nyp_raw' . $suffix ] ) && isset( $_REQUEST['nyp_currency'] ) ) {
			$from_currency = wc_clean( $_REQUEST['nyp_currency'] );
			$raw_price     = wc_clean( $_REQUEST[ 'nyp_raw' . $suffix ] );

			if ( $from_currency !== get_woocommerce_currency() ) {
				$posted_price = self::convert_price( $raw_price, $from_currency );
			}
		}

		return $posted_price;
	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Conversion callbacks.
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Convert the suggested, min, and max prices.
	 *
	 * @static
	 * @param string $price
	 * @param  int $produce_id
	 * @param  obj WC_Product $product
	 * @return string
	 */
	public static function convert_nyp_prices( $price, $product_id, $product ) {

		$active_currency = get_woocommerce_currency();

		$current_filter = current_filter();

		// Get the possible meta value.
		$meta_key = str_replace( 'wc_nyp_raw', '', current_filter() );

		// Patch for actual minimum meta key.
		if ( '_minimum_price' === $meta_key ) {
			$meta_key = '_min_price';
		}

		$meta_value = $product->get_meta( $meta_key . '_' . $active_currency, true );

		// If there's no meta value, automatically convert it.
		if ( ! $meta_value ) {
			$meta_value = self::convert_price( $price );
		}

		return $meta_value;

	}

	/**
	 * Wrapper function to convert a price from one currency to another.
	 *
	 * @static
	 * @param string $price
	 * @param string $from_currency
	 * @param string $to_currency
	 *
	 * @return string|float - Aelia will return a float/number.
	 */
	public static function convert_price( $price, $from_currency = false, $to_currency = false ) {

		// Source currency.
		if ( ! $from_currency ) {
			$from_currency = get_option( 'woocommerce_currency' );
		}

		// Destination currency.
		if ( ! $to_currency ) {
			$to_currency = get_woocommerce_currency();
		}

		/*
		 This filter allows to call a conversion while still maintaining a loose coupling. It accepts a minimum of three arguments:
		 * - Value to convert
		 * - source currency
		 * - destination currency
		 * It returns the original converted to the destination currency
		 */
		return apply_filters( 'wc_aelia_cs_convert', $price, $from_currency, $to_currency );
	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Extra metabox settings.
	 * ---------------------------------------------------------------------------------
	 */


	/**
	 * Add maximum inputs to product metabox
	 *
	 * @param  object WC_Product $product_object
	 * @param  bool $show_billing_period_options
	 * @param  mixed int|false $loop - for use in variations
	 */
	public static function pricing_options( $product_object, $show_billing_period_options = false, $loop = false ) {

		$currencies = apply_filters( 'wc_aelia_cs_enabled_currencies', array() );

		$default_currency = get_option( 'woocommerce_currency' );

		if ( count( $currencies ) > 1 ) { ?>

			<div class="wc_aelia_cs_product_prices clearfix hide_if_subscription">

				<div class="product_currency_prices_header">
					<h3><?php esc_html_e( 'Price in specific currencies', 'wc_name_your_price' ); ?></h3>

					<?php

					foreach ( $currencies as $currency ) {

						if ( $currency === $default_currency ) {
							continue;
						}

						// Suggested Price.
						woocommerce_wp_text_input(
							array(
								'id'            => is_int( $loop ) ? "variation_suggested_price_currency[$currency][$loop]" : "_suggested_price_currency[$currency]",
								'class'         => 'wc_input_price short',
								'wrapper_class' => is_int( $loop ) ? 'form-row form-row-first' : '',
								'placeholder'   => esc_html__( 'Auto', 'wc_name_your_price' ),
								'label'         => esc_html__( 'Suggested Price', 'wc_name_your_price' ) . ' (' . $currency . ')',
								'desc_tip'      => 'true',
								'description'   => esc_html__( 'Price to replace the default price string.  Leave blank to not suggest a price.', 'wc_name_your_price' ),
								'data_type'     => 'price',
								'value'         => $product_object->get_meta( '_suggested_price_' . $currency, true ),
							)
						);

						// Minimum Price.
						woocommerce_wp_text_input(
							array(
								'id'            => is_int( $loop ) ? "variation_min_price_currency[$loop][$currency]" : "_min_price_currency[$currency]",
								'class'         => 'wc_input_price short',
								'wrapper_class' => is_int( $loop ) ? 'form-row form-row-last' : '',
								'placeholder'   => esc_html__( 'Auto', 'wc_name_your_price' ),
								'label'         => esc_html__( 'Minimum Price', 'wc_name_your_price' ) . ' (' . $currency . ')',
								'desc_tip'      => 'true',
								'description'   => esc_html__( 'Lowest acceptable price for product. Leave blank to not enforce a minimum. Must be less than or equal to the set suggested price.', 'wc_name_your_price' ),
								'data_type'     => 'price',
								'value'         => $product_object->get_meta( '_min_price_' . $currency, true ),
							)
						);

						// Maximum Price.
						woocommerce_wp_text_input(
							array(
								'id'            => is_int( $loop ) ? "variation_maximum_price_currency[$loop][$currency]" : "_maximum_price_currency[$currency]",
								'class'         => 'wc_input_price short',
								'wrapper_class' => is_int( $loop ) ? 'form-row form-row-first' : '',
								'placeholder'   => esc_html__( 'Auto', 'wc_name_your_price' ),
								'label'         => esc_html__( 'Maximum Price', 'wc_name_your_price' ) . ' (' . $currency . ')',
								'desc_tip'      => 'true',
								'description'   => esc_html__( 'Highest acceptable price for product. Leave blank to not enforce a maximum.', 'wc_name_your_price' ),
								'data_type'     => 'price',
								'value'         => $product_object->get_meta( '_maximum_price_' . $currency, true ),
							)
						);

						echo '<hr>';

					}

					?>
				</div>
		</div>

			<?php
		}
	}

	/**
	 * Save extra meta info
	 *
	 * @param WC_Product $product
	 */
	public static function save_product_meta( $product ) {

		// Text Field - Amounts in each currency
		$suggested_prices = wp_unslash( sanitize_text_field( $_POST['_suggested_price_currency'] ) );

		if ( ! empty( $suggested_prices ) ) {

			// Save the fee amount for each currency
			foreach ( $suggested_prices as $currency => $amount ) {
				$product->update_meta_data( '_suggested_price_' . $currency, wc_format_decimal( $amount ) );
			}
		}

		// Text Field - Amounts in each currency
		$minimum_prices = wp_unslash( sanitize_text_field( $_POST['_min_price_currency'] ) );

		if ( ! empty( $minimum_prices ) ) {

			// Save the fee amount for each currency
			foreach ( $minimum_prices as $currency => $amount ) {
				$product->update_meta_data( '_min_price_' . $currency, wc_format_decimal( $amount ) );
			}
		}

		// Text Field - Amounts in each currency
		$maximum_prices = wp_unslash( sanitize_text_field( $_POST['_maximum_price_currency'] ) );

		if ( ! empty( $maximum_prices ) ) {

			// Save the fee amount for each currency
			foreach ( $maximum_prices as $currency => $amount ) {
				$product->update_meta_data( '_maximum_price_' . $currency, wc_format_decimal( $amount ) );
			}
		}

	}

}

WC_NYP_Aelia_Compatibility::init();
