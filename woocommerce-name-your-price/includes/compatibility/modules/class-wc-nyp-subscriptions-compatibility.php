<?php
/**
 * Subscriptions Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    3.0.0
 * @version  3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_NYP_Subscriptions_Compatibility class
 **/
class WC_NYP_Subscriptions_Compatibility {

	/**
	 * WC_NYP_Subscriptions_Compatibility Constructor
	 */
	public static function init() {

		// Extra 'Allow Switching' checkboxes.
		add_filter( 'woocommerce_subscriptions_allow_switching_options', array( __CLASS__, 'allow_switching_options' ) );

		// Add the settings to control whether Switching is enabled and how it will behave
		add_filter( 'woocommerce_subscription_settings', array( __CLASS__, 'add_settings' ), 20 );

		// Handle subscription price switching.
		add_filter( 'wcs_is_product_switchable', array( __CLASS__, 'is_switchable' ), 10, 3 );
		add_filter( 'woocommerce_subscriptions_add_switch_query_args', array( __CLASS__, 'add_switch_query_args' ), 10, 3 );
		add_action( 'woocommerce_variable-subscription_add_to_cart', array( __CLASS__, 'customize_single_variable_product' ) );
		add_filter( 'woocommerce_subscriptions_switch_is_identical_product', array( __CLASS__, 'is_identical_product' ), 10, 6 );
		add_filter( 'woocommerce_subscriptions_switch_error_message', array( __CLASS__, 'switch_validation' ), 10, 6 );

		// My Account switch button text.
		add_filter( 'woocommerce_subscriptions_switch_link_text', array( __CLASS__, 'switch_link_text' ), 10, 4 );

		// Don't show edit link when resubscribing.
		add_filter( 'wc_nyp_show_edit_link_in_cart', array( __CLASS__, 'hide_edit_link_in_cart' ), 10, 2 );
	}

	/**
	 * Add extra 'Allow Switching' options.
	 *
	 * @param  array $data
	 * @return array
	 */
	public static function allow_switching_options( $data ) {
		return array_merge(
			$data,
			array(
				array(
					'id'    => 'nyp_price',
					'label' => __( 'Change Name Your Price subscription amount', 'wc_name_your_price' ),
				),
			)
		);
	}


	/**
	 * Add Switch settings to the Subscription's settings page.
	 *
	 * @since 3.5.0
	 */
	public static function add_settings( $settings ) {

		$switching_settings = array(
			'name'     => esc_html__( 'Name Your Price Switch Button Text', 'wc_name_your_price' ),
			'desc'     => esc_html__( 'Customize the text displayed on the button next to the Name Your Price product subscription on the subscriber\'s account page.', 'wc_name_your_price' ),
			'tip'      => '',
			'id'       => 'woocommerce_nyp_price_switch_button_text',
			'css'      => 'min-width:150px;',
			'default'  => esc_html__( 'Change price', 'wc_name_your_price' ),
			'type'     => 'text',
			'desc_tip' => true,
		);

		// Insert the switch settings in after the switch button text setting otherwise add them to the end.
		if ( ! WC_Subscriptions_Admin::insert_setting_after( $settings, WC_Subscriptions_Admin::$option_prefix . '_switch_button_text', $switching_settings ) ) {
			$settings = array_merge( $settings, array( $switching_settings ) );
		}

		return $settings;
	}

	/**
	 * Ensures that NYP products are allowed to be switched
	 *
	 * @param bool $is_switchable
	 * @param WC_Product
	 * @param mixed null|WC_Product_Variation
	 * @return bool
	 */
	public static function is_switchable( $is_switchable, $product, $variation ) {

		$_nyp_product = $variation instanceof WC_Product_Variation ? $variation : $product;

		if ( self::supports_nyp_switching() && WC_Name_Your_Price_Helpers::is_nyp( $_nyp_product ) ) {
			$is_switchable = true;
		}

		return $is_switchable;
	}

	/**
	 * Add the existing price/period to switch link to pre-populate values
	 *
	 * @param str                      $permalink
	 * @param int                      $subscription_id
	 * @param $item_id (the order item)
	 * @return str
	 */
	public static function add_switch_query_args( $permalink, $subscription_id, $item_id ) {
		$subscription  = wcs_get_subscription( $subscription_id );
		$existing_item = wcs_get_order_item( $item_id, $subscription );
		$args          = array();

		$nyp_product = $existing_item->get_product();

		if ( $nyp_product && WC_Name_Your_Price_Helpers::is_nyp( $nyp_product ) ) {

			$suffix                      = WC_Name_Your_Price_Helpers::get_suffix( $nyp_product->get_id() );
			$args[ 'nyp_raw' . $suffix ] = $subscription->get_item_subtotal( $existing_item, $subscription->get_prices_include_tax() );

			if ( WC_Name_Your_Price_Helpers::is_billing_period_variable( $nyp_product ) ) {
				$args[ 'nyp-period' . $suffix ] = WC_Name_Your_Price_Core_Compatibility::get_prop( $subscription, 'billing_period' );
			}

			$permalink = add_query_arg( $args, $permalink );

		}

		return $permalink;
	}


	/**
	 * Disable the attribute select if switching is not allowed
	 */
	public static function customize_single_variable_product() {

		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['switch-subscription'] ) && isset( $_GET['item'] ) ) {

			$subscription  = wcs_get_subscription( absint( $_GET['switch-subscription'] ) );
			$existing_item = wcs_get_order_item( absint( $_GET['item'] ), $subscription );

			// Get the product/variation ID of this item.
			$nyp_product = $existing_item->get_product();

			if ( WC_Name_Your_Price_Helpers::is_nyp( $nyp_product ) ) {

				if ( ! self::supports_variable_switching() ) {
					add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( __CLASS__, 'disable_attributes' ) );
					add_filter( 'woocommerce_reset_variations_link', '__return_null' );
				}

				if ( ! self::supports_nyp_switching() ) {
					add_filter( 'wc_nyp_price_input_attributes', array( __CLASS__, 'disable_input' ) );
				}
			}
		}

	}

	/**
	 * Disable the print input select if switching is not allowed
	 *
	 * @param array $attributes The input attributes
	 * @return array
	 */
	public static function disable_input( $attributes ) {
		$attributes['disabled'] = 'disabled';
		return $attributes;
	}

	/**
	 * Disable the attribute select if switching is not allowed
	 *
	 * @param str $html
	 * @return str
	 */
	public static function disable_attributes( $html ) {
		return str_replace( '<select', '<select disabled="disabled"', $html );
	}


	/**
	 * Test if is identical product
	 *
	 * @param bool $is_identical
	 * @param obj $product
	 * @throws Exception when the subscription is the same as the current subscription
	 * @return bool
	 */
	public static function is_identical_product( $is_identical, $product_id, $quantity, $variation_id, $subscription, $item ) {

		if ( $is_identical && self::supports_nyp_switching() ) {

			$nyp_id = $variation_id ? $variation_id : $product_id;

			if ( WC_Name_Your_Price_Helpers::is_nyp( $nyp_id ) ) {

				$prefix = WC_Name_Your_Price_Helpers::get_suffix( $nyp_id );

				$nyp_product = wc_get_product( $nyp_id );

				$initial_subscription_price  = floatval( $subscription->get_item_subtotal( $item, $subscription->get_prices_include_tax() ) );
				$new_subscription_price      = floatval( WC_Name_Your_Price_Helpers::get_posted_price( $nyp_id, $prefix ) );
				$initial_subscription_period = WC_Name_Your_Price_Core_Compatibility::get_prop( $subscription, 'billing_period' );
				$new_subscription_period     = WC_Name_Your_Price_Helpers::get_posted_period( $nyp_id, $prefix );

				// If variable billing period check both price and billing period.
				if ( WC_Name_Your_Price_Helpers::is_billing_period_variable( $nyp_id ) && $new_subscription_price === $initial_subscription_price && $new_subscription_period === $initial_subscription_period ) {
					throw new Exception( __( 'Please modify the price or billing period so that it is not the same as your existing subscription.', 'wc_name_your_price' ) );

					// Check price only.
				} elseif ( $new_subscription_price === $initial_subscription_price ) {
					throw new Exception( __( 'Please modify the price so that it is not the same as your existing subscription.', 'wc_name_your_price' ) );

					// If the price/period is different then this is NOT and identical product. Do not remove!
				} else {
					$is_identical = false;
				}
			}
		}

		return $is_identical;
	}

	/**
	 * Test if the switching subscription is valid
	 * if already valid (ie: changing variation), then skip
	 * if not already valid, check that price or period is changed
	 *
	 * @param str $error_message
	 * @param int $product_id
	 * @param int $quantity
	 * @param int $variation_id - is a null '' if not a variation.
	 * @param WC_Subscription $subscription
	 * @param WC_Order_Item_Product $sub_order_item
	 * @return str
	 */
	public static function switch_validation( $error_message, $product_id, $quantity, $variation_id, $subscription, $sub_order_item ) {

		if ( empty( $error_message ) ) {

			// If NYP-only switching, ensure product/variation IDs are the same.
			if ( self::supports_nyp_switching() ) {

				if ( $variation_id && ! self::supports_variable_switching() && $variation_id !== $sub_order_item->get_variation_id() ) {
					$error_message = __( 'You are only allowed to change this subscription\'s price.', 'wc_name_your_price' );
				}
			} else {

				$nyp_id = $variation_id ? $variation_id : $product_id;

				if ( WC_Name_Your_Price_Helpers::is_nyp( $nyp_id ) ) {
					$error_message = __( 'You do not have permission to modify the price of this subscription.', 'wc_name_your_price' );
				}
			}
		}

		return $error_message;
	}

	/*
	|--------------------------------------------------------------------------
	| Hooks - Cart Templates
	|--------------------------------------------------------------------------
	*/

	/**
	 * Change the switch button text for Mix and Match subscriptions.
	 *
	 * @since 3.5.0
	 *
	 * @param string $switch_link_text The switch link html.
	 * @param int $item_id The order item ID of a subscription line item
	 * @param array $item An order line item
	 * @param object $subscription A WC_Subscription object
	 * @return string
	 */
	public static function switch_link_text( $switch_link_text, $item_id, $item, $subscription ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $item->get_product() ) && self::supports_nyp_switching() ) {
			$option_text      = get_option( 'woocommerce_nyp_price_switch_button_text', esc_html__( 'Change price', 'wc_name_your_price' ) );
			$switch_link_text = '' !== $option_text ? $option_text : $switch_link_text;
		}

		return $switch_link_text;
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	*/

	/**
	 * Is NYP switching enabled
	 *
	 * @param bool $is_product_switchable
	 * @param obj  $product
	 * @return bool
	 */
	public static function supports_nyp_switching() {
		return wc_string_to_bool( get_option( WC_Subscriptions_Admin::$option_prefix . '_allow_switching_nyp_price', 'no' ) );
	}

	/**
	 * Is variable switching enabled
	 *
	 * @param bool $is_product_switchable
	 * @param obj  $product
	 * @return bool
	 */
	public static function supports_variable_switching() {
		$allow_switching = get_option( WC_Subscriptions_Admin::$option_prefix . '_allow_switching', 'no' );
		return strpos( $allow_switching, 'variable' ) !== false;
	}

	/**
	 * Don't show edit link when resubscribing
	 *
	 * @param bool $show
	 * @param array  $cart_item
	 * @return bool
	 */
	public static function hide_edit_link_in_cart( $show, $cart_item ) {
		if ( isset( $cart_item['subscription_resubscribe'] ) ) {
			$show = false;
		}
		return $show;
	}

} // End class: do not remove or there will be no more guacamole for you.

WC_NYP_Subscriptions_Compatibility::init();
