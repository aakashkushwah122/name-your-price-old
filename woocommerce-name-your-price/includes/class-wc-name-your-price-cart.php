<?php
/**
 * Interact with WooCommerce cart
 *
 * @class   WC_Name_Your_Price_Cart
 * @package WooCommerce Name Your Price/Classes
 * @since   1.0.0
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Name_Your_Price_Cart class.
 */
class WC_Name_Your_Price_Cart {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Name_Your_Price_Cart
	 *
	 * @since 3.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_Name_Your_Price_Cart
	 * @since  3.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning this object is forbidden.', 'wc_name_your_price' ), '3.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'wc_name_your_price' ), '3.0.0' );
	}

	/**
	 * __construct function.
	 *
	 * @return void
	 */
	public function __construct() {

		// Functions for cart actions - ensure they have a priority before addons (10).
		add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 5, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 5, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 11, 2 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'set_cart_item' ), 11, 1 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_cart_item' ), 5, 6 );

		// Re-validate prices in cart.
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ) );

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Cart Filters
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Override woo's is_purchasable in cases of nyp products.
	 *
	 * @since 1.0
	 *
	 * @param   boolean     $is_purchasable
	 * @param   WC_Product  $product
	 * @return  boolean
	 */
	public function is_purchasable( $is_purchasable, $product ) {
		if ( ( $product->is_type( WC_Name_Your_Price_Helpers::get_simple_supported_types() ) && WC_Name_Your_Price_Helpers::is_nyp( $product ) )
			|| ( $product->is_type( WC_Name_Your_Price_Helpers::get_variable_supported_types() ) && WC_Name_Your_Price_Helpers::has_nyp( $product ) )
		) {
			$is_purchasable = true;
		}
		return $is_purchasable;
	}

	/**
	 * Redirect to the cart when editing a price "in-cart".
	 *
	 * @since   3.0.0
	 * @param  string $url
	 * @return string
	 */
	public function edit_in_cart_redirect( $url ) {
		return wc_get_cart_url();
	}


	/**
	 * Filter the displayed notice after redirecting to the cart when editing a price "in-cart".
	 *
	 * @since   3.0.0
	 * @param  string $url
	 * @return string
	 */
	public function edit_in_cart_redirect_message( $message ) {
		return esc_html__( 'Cart updated.', 'wc_name_your_price' );
	}

	/**
	 * Add cart session data.
	 *
	 * @param array $cart_item_data extra cart item data we want to pass into the item.
	 * @param int   $product_id contains the id of the product to add to the cart.
	 * @param int   $variation_id ID of the variation being added to the cart.
	 * @since 1.0
	 */
	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

		// phpcs:disable WordPress.Security.NonceVerification

		// An NYP item can either be a product or variation.
		$nyp_id = $variation_id ? $variation_id : $product_id;

		$suffix  = WC_Name_Your_Price_Helpers::get_suffix( $nyp_id );
		$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $nyp_id );

		// get_posted_price() removes the thousands separators.
		$posted_price = WC_Name_Your_Price_Helpers::get_posted_price( $product, $suffix );

		// Is this an NYP item?
		if ( WC_Name_Your_Price_Helpers::is_nyp( $nyp_id ) && $posted_price ) {

			// Updating container in cart?
			if ( isset( $_POST['update-price'] ) && isset( $_POST['_nypnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_nypnonce'] ), 'nyp-nonce' ) ) {

				$updating_cart_key = wc_clean( wp_unslash( $_POST['update-price'] ) );

				if ( WC()->cart->find_product_in_cart( $updating_cart_key ) ) {

					// Remove.
					WC()->cart->remove_cart_item( $updating_cart_key );

					// Redirect to cart.
					add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'edit_in_cart_redirect' ) );

					// Edit notice.
					add_filter( 'wc_add_to_cart_message_html', array( $this, 'edit_in_cart_redirect_message' ) );
				}
			}

			// No need to check is_nyp b/c this has already been validated by validate_add_cart_item().
			$cart_item_data['nyp'] = (float) $posted_price;
		}

		// Add the subscription billing period (the input name is nyp-period).
		$posted_period = WC_Name_Your_Price_Helpers::get_posted_period( $product, $suffix );

		if ( WC_Name_Your_Price_Helpers::is_subscription( $nyp_id ) && WC_Name_Your_Price_Helpers::is_billing_period_variable( $nyp_id ) && $posted_period && array_key_exists( $posted_period, WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ) {
			$cart_item_data['nyp_period'] = $posted_period;
		}

		return $cart_item_data;
	}

	/**
	 * Adjust the product based on cart session data.
	 *
	 * @param  array $cart_item $cart_item['data'] is product object in session
	 * @param  array $values cart item array
	 * @since 1.0
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {

		// No need to check is_nyp b/c this has already been validated by validate_add_cart_item().
		if ( isset( $values['nyp'] ) ) {
			$cart_item['nyp'] = $values['nyp'];

			// Add the subscription billing period.
			if ( WC_Name_Your_Price_Helpers::is_subscription( $cart_item['data'] ) && isset( $values['nyp_period'] ) && array_key_exists( $values['nyp_period'], WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ) {
				$cart_item['nyp_period'] = $values['nyp_period'];
			}

			$cart_item = $this->set_cart_item( $cart_item );
		}

		return $cart_item;
	}

	/**
	 * Change the price of the item in the cart.
	 *
	 * @since 3.0
	 *
	 * @param  array $cart_item
	 * @return  array
	 */
	public function set_cart_item( $cart_item ) {

		// Adjust price in cart if nyp is set.
		if ( isset( $cart_item['nyp'] ) && isset( $cart_item['data'] ) ) {

			$product = $cart_item['data'];
			$price   = $cart_item['nyp'];

			$product->set_price( $price );
			$product->set_sale_price( $price );
			$product->set_regular_price( $price );

			// Subscription-specific price and variable billing period.
			if ( $product->is_type( array( 'subscription', 'subscription_variation' ) ) ) {

				$product->update_meta_data( '_subscription_price', $price );

				if ( WC_Name_Your_Price_Helpers::is_billing_period_variable( $product ) && isset( $cart_item['nyp_period'] ) ) {

					// Length may need to be re-calculated. Hopefully no one is using the length but who knows.
					// v3.1.3 disables the length selector when in variable billing mode.
					$original_period = WC_Subscriptions_Product::get_period( $product );
					$original_length = WC_Subscriptions_Product::get_length( $product );

					if ( $original_length > 0 && $original_period && $cart_item['nyp_period'] !== $original_period ) {
						$factors    = WC_Name_Your_Price_Helpers::annual_price_factors();
						$new_length = $original_length * $factors[ $cart_item['nyp_period'] ] / $factors[ $original_period ];
						$product->update_meta_data( '_subscription_length', floor( $new_length ) );
					}

					// Set period to the chosen period.
					$product->update_meta_data( '_subscription_period', $cart_item['nyp_period'] );

					// Variable billing period is always a "per" interval.
					$product->update_meta_data( '_subscription_period_interval', 1 );

				}
			}
		}

		return $cart_item;
	}

	/**
	 * Validate an NYP product before adding to cart.
	 *
	 * @since 1.0
	 *
	 * @param  int    $product_id     - Contains the ID of the product.
	 * @param  int    $quantity       - Contains the quantity of the item.
	 * @param  int    $variation_id   - Contains the ID of the variation.
	 * @param  array  $variation      - Attribute values.
	 * @param  array  $cart_item_data - Extra cart item data we want to pass into the item.
	 * @return bool
	 */
	public function validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations = '', $cart_item_data = array() ) {

		// Skip legacy validation on Store API requests.
		if ( wc()->is_rest_api_request() ) {
			return $passed;
		}

		$nyp_id  = $variation_id ? $variation_id : $product_id;
		$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $nyp_id );

		// Skip if not a product or NYP product - send original status back.
		if ( ! $product || ! WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			return $passed;
		}

		$suffix = WC_Name_Your_Price_Helpers::get_suffix( $nyp_id );

		// Get_posted_price() runs the price through the standardize_number() helper.
		$price = isset( $cart_item_data['nyp'] ) ? $cart_item_data['nyp'] : WC_Name_Your_Price_Helpers::get_posted_price( $product, $suffix );

		// Get the posted billing period.
		$period = isset( $cart_item_data['nyp_period'] ) ? $cart_item_data['nyp_period'] : WC_Name_Your_Price_Helpers::get_posted_period( $product, $suffix );

		// Validate.
		try {

			$this->validate_price( $product, $quantity, $price, $period, array( 'throw_exception' => true ) );

		} catch ( Exception $e ) {

			if ( $e->getMessage() ) {

				// translators: %1$s is the product title. %2$s is the reason it cannot be added to the cart.
				$notice = sprintf( wp_kses_post( __( '&quot;%1$s&quot; could not be added to the cart. %2$s', 'wc_name_your_price' ) ), $product->get_title(), $e->getMessage() );

				wc_add_notice( $notice, 'error' );
			}

			$passed = false;

		}

		return $passed;

	}

	/**
	 * Re-validate prices on cart load.
	 * Specifically we are looking to prevent smart/quick pay gateway buttons completing an order that is invalid.
	 */
	public function check_cart_items() {

		// Skip legacy validation on Store API requests.
		if ( wc()->is_rest_api_request() ) {
			return;
		}

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

			if ( isset( $cart_item['nyp'] ) ) {
				$period = isset( $cart_item['nyp_period'] ) ? $cart_item['nyp_period'] : '';

				try {

					$this->validate_price( $cart_item['data'], $cart_item['quantity'], $cart_item['nyp'], $period, array( 'throw_exception' => true ) );

				} catch ( Exception $e ) {

					if ( $e->getMessage() ) {

						// translators: %1$s is the product title. %2$s is the reason it cannot be added to the cart.
						$notice = sprintf( wp_kses_post( __( '&quot;%1$s&quot; cannot be purchased. %2$s', 'wc_name_your_price' ) ), $cart_item['data']->get_title(), $e->getMessage() );

						wc_add_notice( $notice, 'error' );

					}
				}
			}
		}
	}

	/**
	 * Validate an NYP product's price is valid.
	 *
	 * @since 3.0
	 *
	 * @param  int|WC_Product   $product
	 * @param  int     $quantity
	 * @param  string  $price
	 * @param  string  $period
	 * @param  array|string  $args {
	 *      Optional. An array of arguments.
	 *      @type string $context           The type of validation. Default is 'add-to-cart'. - Currently unused.
	 *      @type bool   $throw_exception   Throw an exception instead of returning bool.
	 * }
	 *
	 * @param  bool    $deprecated - When true returns the string error message. - Deprecated 3.5.0.
	 * @return boolean
	 */
	public function validate_price( $product, $quantity, $price, $period = '', $args = array(), $deprecated = false ) {

		$passed_validation = true;

		$defaults = array(
			'context'         => is_string( $args ) ? $args : 'add-to-cart', // Back in the day, args was a string and was used to pass context.
			'throw_exception' => false,
		);

		if ( $deprecated ) {
			wc_deprecated_argument( 'return_error', '3.5.0', 'Passing $return_error to the validate_price() method is deprecated. Use $args["throw_exception"] instead to return an Exception.' );
		}

		$args = wp_parse_args( $args, $defaults );

		try {

			// Sanity check.
			$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $product );

			if ( ! ( $product instanceof WC_Product ) ) {
				$reason = esc_html__( 'This is not a valid product.', 'wc_name_your_price' );
				throw new Exception( $reason );
			}

			$product_id    = $product->get_id();
			$product_title = $product->get_title();

			// Get minimum price.
			$minimum = WC_Name_Your_Price_Helpers::get_minimum_price( $product );

			// Get maximum price.
			$maximum = WC_Name_Your_Price_Helpers::get_maximum_price( $product );

			// Minimum error template.
			$min_hidden = WC_Name_Your_Price_Helpers::is_minimum_hidden( $product );

			// Check that it is a positive numeric value.
			if ( ! is_numeric( $price ) || is_infinite( $price ) || floatval( $price ) < 0 ) {

				$reason = esc_html__( 'Please enter a valid, positive number.', 'wc_name_your_price' );

				throw new Exception( $reason );

				// Check that it is greater than minimum price for variable billing subscriptions.
			} elseif ( $minimum && $period && WC_Name_Your_Price_Helpers::is_subscription( $product ) && WC_Name_Your_Price_Helpers::is_billing_period_variable( $product ) ) {

				// Minimum billing period.
				$minimum_period = WC_Name_Your_Price_Helpers::get_minimum_billing_period( $product );

				// Annual minimum.
				$minimum_annual = WC_Name_Your_Price_Helpers::annualize_price( $minimum, $minimum_period );

				// Annual price.
				$annual_price = WC_Name_Your_Price_Helpers::annualize_price( $price, $period );

				// By standardizing the prices over the course of a year we can safely compare them.
				if ( $annual_price < $minimum_annual ) {

					$factors = WC_Name_Your_Price_Helpers::annual_price_factors();

					// If set period is in the $factors array we can calc the min price shown in the error according to entered period.
					if ( isset( $factors[ $period ] ) ) {
						$error_price  = $minimum_annual / $factors[ $period ];
						$error_period = $period;
						// Otherwise, just show the saved minimum price and period.
					} else {
						$error_price  = $minimum;
						$error_period = $minimum_period;
					}

					if ( $min_hidden ) {
						$reason = esc_html__( 'Please enter a higher amount.', 'wc_name_your_price' );
					} else {
						// translators: %s is the minimum price per period.
						$reason = sprintf( esc_html__( 'Please enter at least %s.', 'wc_name_your_price' ), wc_price( $error_price ) . ' / ' . $error_period );
					}

					throw new Exception( $reason );

				}
				// Check that it is greater than minimum price.
			} elseif ( $minimum && floatval( $price ) < floatval( $minimum ) ) {

				if ( $min_hidden ) {
					$reason = esc_html__( 'Please enter a higher amount.', 'wc_name_your_price' );
				} else {
					// translators: %s is the minimum price.
					$reason = sprintf( esc_html__( 'Please enter at least %s.', 'wc_name_your_price' ), wc_price( $minimum ) );
				}

				throw new Exception( $reason );

				// Check that it is less than maximum price.
			} elseif ( $maximum && floatval( $price ) > floatval( $maximum ) ) {

				// translators: %s is the maximum price.
				$reason = sprintf( esc_html__( 'Please enter less than or equal to %s.', 'wc_name_your_price' ), wc_price( $maximum ) );

				throw new Exception( $reason );

			}
		} catch ( Exception $e ) {

			$passed_validation = false;

			if ( $e->getMessage() ) {

				// Return the error message. Formerly used by CoCart integration.
				if ( $deprecated ) {

					return $e->getMessage();

				} elseif ( $args['throw_exception'] ) {

					// Throw the error as an exception.
					throw new Exception( $e->getMessage() );

				}
			}
		}

		return $passed_validation;

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Deprecated Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Change the price of the item in the cart.
	 *
	 * @since 1.0
	 * @deprecated 3.0
	 */
	public function add_cart_item( $cart_item ) {
		wc_deprecated_function( 'WC_Name_Your_Price_Cart::add_cart_item', '3.0', 'Renamed to set_cart_item()' );
		return $this->set_cart_item( $cart_item );
	}

} // End class.
