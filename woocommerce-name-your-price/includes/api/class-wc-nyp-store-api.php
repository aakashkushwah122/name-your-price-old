<?php
/**
 * WC_NYP_Store_API class
 *
 * @package  WooCommerce Mix and Match Products/REST API
 * @since    3.5.0
 * @version  3.5.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema;

/**
 * Extends the store public API with NYP-related data for each product.
 */
class WC_NYP_Store_API {

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'name_your_price';

	/**
	 * Bootstraps the class and hooks required data.
	 */
	public static function init() {

		// Filter cart item response.
		add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'filter_cart_item_data' ), 10, 3 );

		// Register custom configuration data.
		add_filter( 'woocommerce_store_api_add_to_cart_data', array( __CLASS__, 'add_to_cart_data' ), 10, 2 );

		// Validate add to cart in the Store API and add cart errors.
		add_action( 'woocommerce_store_api_validate_add_to_cart', array( __CLASS__, 'validate_add_to_cart_item' ), 10, 2 );

		// Re-validate product in cart in the Store API and add cart errors.
		add_action( 'woocommerce_store_api_validate_cart_item', array( __CLASS__, 'validate_cart_item' ), 10, 2 );

	}

	/**
	 * Pass price config in Store API context.
	 *
	 * @throws RouteException
	 *
	 * @param  array  $add_to_cart_data
	 * @param array   $request Add to cart request params including id, quantity, and variation attributes.
	 */
	public static function add_to_cart_data( $add_to_cart_data, \WP_REST_Request $request ) {

		if ( isset( $request['nyp'] ) ) {
			$add_to_cart_data['cart_item_data']['nyp'] = $request['nyp'];
		}

		return $add_to_cart_data;
	}


	/*
	|--------------------------------------------------------------------------
	| Callbacks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Filter store API responses to:
	 *
	 * - Add edit price button
	 *
	 * @since 3.5.9
	 *
	 * @param  $response  WP_REST_Response
	 * @param  $server    WP_REST_Server
	 * @param  $request   WP_REST_Request
	 * @return WP_REST_Response
	 */
	public static function filter_cart_item_data( $response, $server, $request ) {

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( strpos( $request->get_route(), 'wc/store' ) === false ) {
			return $response;
		}

		$data = $response->get_data();

		if ( empty( $data['items'] ) ) {
			return $response;
		}

		$cart = WC()->cart->get_cart();

		foreach ( $data['items'] as &$item_data ) {

			$cart_item_key = $item_data['key'];
			$cart_item     = isset( $cart[ $cart_item_key ] ) ? $cart[ $cart_item_key ] : null;

			if ( is_null( $cart_item ) ) {
				continue;
			}

			if ( isset( $cart_item['nyp'] ) ) {
				self::filter_container_cart_item_short_description( $item_data, $cart_item );
			}
		}

		$response->set_data( $data );

		return $response;
	}

	/**
	 * Validate price in Store API context.
	 *
	 * @throws RouteException
	 *
	 * @param  WC_Product  $product
	 * @param array       $request Add to cart request params including id, quantity, and variation attributes.
	 */
	public static function validate_add_to_cart_item( $product, $request ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {

			$price    = isset( $request['cart_item_data'] ) && isset( $request['cart_item_data']['nyp'] ) ? $request['cart_item_data']['nyp'] : '';
			$quantity = isset( $request['quantity'] ) ? intval( $request['quantity'] ) : 1;

			// Validate.
			try {

				WC_Name_Your_Price()->cart->validate_price( $product, $quantity, $price, '', array( 'throw_exception' => true ) );

			} catch ( Exception $e ) {

				if ( $e->getMessage() ) {

					throw new RouteException(
						'woocommerce_store_api_invalid_product_price',
						html_entity_decode( wp_strip_all_tags( $e->getMessage() ) ),
						500
					);
				}
			}
		}
	}


	/**
	 * Validate price in Store API cart context.
	 * Prevent access to checkout if something got messed up.
	 *
	 * @param \WC_Product $product Product object being added to the cart.
	 * @param array       $cart_item Cart item array.
	 */
	public static function validate_cart_item( $product, $cart_item ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {

			$price    = isset( $cart_item['nyp'] ) ? $cart_item['nyp'] : '';
			$quantity = isset( $cart_item['quantity'] ) ? intval( $cart_item['quantity'] ) : 1;

			// Validate.
			try {

				WC_Name_Your_Price()->cart->validate_price(
					$product,
					$quantity,
					$price,
					'',
					array(
						'context'         => 'cart',
						'throw_exception' => true,
					)
				);

			} catch ( Exception $e ) {

				if ( $e->getMessage() ) {

					// translators: %1$s is the product title. %2$s is the reason it cannot be purchased as is.
					$notice = sprintf( wp_kses_post( __( '&quot;%1$s&quot; cannot be purchased. %2$s', 'wc_name_your_price' ) ), $product->get_title(), wp_strip_all_tags( $e->getMessage() ) );

					throw new RouteException(
						'woocommerce_store_api_invalid_product_price',
						html_entity_decode( $notice ),
						500
					);
				}
			}
		}

	}

	/**
	 * Filter cart item short description to support cart editing.
	 *
	 * @since 3.5.9
	 *
	 * @param array  $item_data
	 * @param array  $cart_item
	 */
	private static function filter_container_cart_item_short_description( &$item_data, $cart_item ) {

		if ( apply_filters( 'wc_nyp_show_edit_link_in_cart', true, $cart_item, $cart_item['key'] ) ) {

			$trimmed_short_description = '';

			if ( $item_data['short_description'] ) {
				$trimmed_short_description = '<p class="wc-block-components-product-metadata__description-text">' . wp_trim_words( $item_data['short_description'], 12 ) . '</p>';
			}

			// Get the Edit URL.
			$edit_in_cart_link = WC_Name_Your_Price_Helpers::get_edit_url( $cart_item );

			// Add button to end of short description response.
			$item_data['short_description'] = '<p class="wc-block-cart-item__edit"><a class="components-button wc-block-components-button wp-element-button outlined wc-block-cart-item__edit-link contained" role="button" href="' . esc_url( $edit_in_cart_link ) . '"><span class="wc-block-components-button__text">' . esc_html_x( 'Edit price', 'edit in cart link text', 'wc_name_your_price' ) . '</span></a></p>' . $trimmed_short_description;
		}
	}

}
