<?php
/**
 * Interact with WooCommerce orders
 *
 * @class   WC_Name_Your_Price_Order
 * @package WooCommerce Name Your Price/Classes
 * @since   2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Name_Your_Price_Order class.
 */
class WC_Name_Your_Price_Order {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Name_Your_Price_Order
	 *
	 * @since 3.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_Name_Your_Price_Order
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
		add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again_cart_item_data' ), 5, 3 );

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Order Filters
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Add cart session data from existing order.
	 *
	 * @param array                                                              $cart_item_data
	 * @param WC_Order_Item_Product (supports array notation due to ArrayAccess)
	 * @param WC_Order
	 * @return array
	 *
	 * @todo Switch from array to object notation.
	 *
	 * @since 2.4.0
	 */
	public function order_again_cart_item_data( $cart_item_data, $line_item, $order ) {

		// Get the product/variation product object of this item.
		$nyp_product = $line_item->get_product();

		if ( WC_Name_Your_Price_Helpers::is_nyp( $nyp_product ) ) {

			$line_price = $line_item->get_subtotal();

			/**
			 * If the order prices include tax then we need to add back the tax on this product.
			 * Otherwise, the subtotal is the original price entered *minus* tax.
			 * See: https://github.com/helgatheviking/woocommerce-name-your-price/issues/91
			 */
			if ( $nyp_product->is_taxable() && $order->get_prices_include_tax() ) {

				/**
				 * If not rounding taxes at the subtotal we need to round the item's subtotal.
				 * We can assume that the original NYP price was entered with the number of price decimals from settings.
				 *
				 * Hopefully, we can remove this when https://github.com/woocommerce/woocommerce/issues/24184 is resolved.
				 *
				 * A workaround for rounding issues is to enable tax rounding at the subtotal level via settings
				 * see: https://share.getcloudapp.com/4gux6lve
				 */
				if ( 'yes' !== get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
					$line_price = wc_format_decimal( $line_price, wc_get_price_decimals() );
				}

				// Use the taxes array items here as they contain taxes to a more accurate number of decimals.
				$taxes       = $line_item->get_taxes();
				$line_price += array_sum( $taxes['subtotal'] );

			}

			$line_price = $line_price / $line_item->get_quantity();

			$cart_item_data['nyp'] = (float) wc_format_decimal( $line_price );
		}

		if ( WC_Name_Your_Price_Helpers::is_subscription( $nyp_product ) && WC_Name_Your_Price_Helpers::is_billing_period_variable( $nyp_product ) ) {

			$subscription = $this->find_subscription( $line_item, $order );

			if ( is_callable( array( $subscription, 'get_billing_period' ) ) ) {
				$cart_item_data['nyp_period'] = $subscription->get_billing_period();
			}
		}

		return $cart_item_data;
	}

	/**
	 * Find the order item's related subscription.
	 * Slightly hacky, matches product ID against product ID of subscription.
	 * Will fail if multiple variable billing period subs exist in subscription.
	 *
	 * @since 2.4.0
	 */
	public function find_subscription( $order_item, $order ) {

		$order_items_product_id = wcs_get_canonical_product_id( $order_item );

		$subscription_for_item = null;

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {
			foreach ( $subscription->get_items() as $line_item ) {
				if ( wcs_get_canonical_product_id( $line_item ) === $order_items_product_id ) {
					$subscription_for_item = $subscription;
					break 2;
				}
			}
		}

		return $subscription_for_item;
	}

}//end class
