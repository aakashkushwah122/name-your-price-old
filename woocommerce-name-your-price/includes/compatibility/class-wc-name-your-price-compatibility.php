<?php
/**
 * Plugin Cross-Compatibility
 *
 * @package  WooCommerce Name Your Price/Compatibility
 * @since    2.7.0
 * @version  3.5.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Name_Your_Price_Compatibility Class.
 *
 * Handle loading of modules depending on active pluginss.
 */
class WC_Name_Your_Price_Compatibility {

	/**
	 * Define dependencies
	 *
	 * @var array of minimum versions
	 * @since 2.0.0
	 */
	public $required = array(
		'blocks' => '7.2.0',
	);

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Name_Your_Price_Compatibility
	 *
	 * @since 3.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_Name_Your_Price_Compatibility
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
	 * WC_Name_Your_Price Constructor.
	 *
	 * @return WC_Name_Your_Price
	 * @since 2.7.0
	 */
	public function __construct() {

		// Initialize.
		$this->init();

	}

	/**
	 * Version test for NYP
	 *
	 * @since   3.0.0
	 *
	 * @param   string $version
	 * @return  bool
	 */
	public static function is_nyp_gte( $version = '3.0' ) {
		return version_compare( WC_Name_Your_Price()->version, $version, '>=' );
	}

	/**
	 * Init compatibility classes.
	 *
	 * @since  2.11.0
	 */
	public function init() {

		// Deactivate functionality from mini-extensions.
		add_action( 'plugins_loaded', array( $this, 'unload' ), 11 );

		// Declare Features compatibility.
		add_action( 'before_woocommerce_init', array( $this, 'declare_features_compatibility' ) );

		// Load modules.
		add_action( 'plugins_loaded', array( $this, 'load_modules' ), 100 );

	}


	/**
	 * Unload mini-extensions.
	 *
	 * @since 3.5.0
	 */
	public function unload() {

		// Deactivate functionality added by the Aelia bridge mini-extension.
		if ( class_exists( 'WC_NYP_Aelia_CC' ) ) {
			remove_action( 'plugins_loaded', array( 'WC_NYP_Aelia_CC', 'init' ), 20 );

			// Prompt deactivation.
			add_action(
				'admin_notices',
				function() {
					$notice = __( 'The <strong>WC Name Your Price + Aelia Currency Converter Bridge</strong> mini-extension is now part of <strong>WooCommerce Name Your Price</strong>. Please deactivate and remove the <strong>WC Name Your Price + Aelia Currency Converter Bridge</strong> plugin.', 'wc_name_your_price' );
					echo '<div class="notice notice-error"><p>' . wp_kses_post( $notice ) . '</p></div>';
				}
			);
		}

	}

	/**
	 * Declare WooCommerce Feature compatibility.
	 *
	 * @since 3.5.9
	 */
	public function declare_features_compatibility() {

		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		// HPOS (Custom Order tables) compatibility.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_Name_Your_Price()->plugin_basename(), true );

		// Cart/Checkout Blocks compatibility.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WC_Name_Your_Price()->plugin_basename(), true );
	}

	/**
	 * Prevent deprecated mini-extensions from initializing.
	 *
	 * @since  2.11.0
	 */
	public function load_modules() {

		// Variable products.
		$module_paths['variable_products'] = 'modules/class-wc-nyp-variable-products-compatibility.php';

		// Aelia Currency Converter.
		if ( ! empty( apply_filters( 'wc_aelia_cs_enabled_currencies', array() ) ) ) {
			$module_paths['aelia_currency'] = 'modules/class-wc-nyp-aelia-compatibility.php';
		}

		// WooCommerce Cart/Checkout Blocks support.
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Package' ) && version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), $this->required['blocks'], '>=' ) ) {
			$module_paths['blocks'] = 'modules/class-wc-nyp-blocks-compatibility.php';
		}

		// CoCart support.
		if ( defined( 'COCART_VERSION' ) ) {
			$module_paths['cocart'] = 'modules/class-wc-nyp-cocart-compatibility.php';
		}

		// Grouped products.
		if ( WC_Name_Your_Price_Core_Compatibility::is_wc_version_gte( '3.3.0' ) ) {
			$module_paths['grouped_products'] = 'modules/class-wc-nyp-grouped-products-compatibility.php';
		}

		// Subscriptions switching.
		if ( class_exists( 'WC_Subscriptions' ) && version_compare( WC_Subscriptions::$version, '1.4.0', '>' ) ) {
			$module_paths['subscriptions'] = 'modules/class-wc-nyp-subscriptions-compatibility.php';
		}

		// Stripe fixes.
		if ( class_exists( 'WC_Stripe' ) ) {
			$module_paths['stripe'] = 'modules/class-wc-nyp-stripe-compatibility.php';
		}

		// Braintree Paypal fixes.
		if ( class_exists( 'WC_Gateway_Braintree_PayPal' ) ) {
			$module_paths['braintree_paypal'] = 'modules/class-wc-nyp-braintree-paypal-compatibility.php';
		}

		// Cybersource fixes.
		if ( function_exists( 'wc_cybersource' ) ) {
			$module_paths['cybersource'] = 'modules/class-wc-nyp-cybersource-compatibility.php';
		}

		// QuickView support.
		if ( class_exists( 'WC_Quick_View' ) ) {
			$module_paths['quickview'] = 'modules/class-wc-nyp-qv-compatibility.php';
		}

		// WooCommerce Payments request buttons.
		if ( class_exists( 'WC_Payments' ) ) {
			$module_paths['wcpay'] = 'modules/class-wc-nyp-wcpay-compatibility.php';
		}

		// WooCommerce PayPal Payments request buttons.
		if ( class_exists( 'WooCommerce\PayPalCommerce\Plugin' ) ) {
			$module_paths['wcpaypal'] = 'modules/class-wc-nyp-paypal-payments-compatibility.php';
		}

		// WooCommerce Points and Rewards.
		if ( class_exists( 'WC_Points_Rewards' ) ) {
			$module_paths['pointsandrewards'] = 'modules/class-wc-nyp-points-and-rewards-compatibility.php';
		}

		/**
		 * 'wc_nyp_compatibility_modules' filter.
		 *
		 * Use this to filter the required compatibility modules.
		 *
		 * @since  2.11.0
		 * @param  array $module_paths
		 */
		$module_paths = apply_filters( 'wc_nyp_compatibility_modules', $module_paths );
		foreach ( $module_paths as $name => $path ) {
			require_once $path;
		}

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Deprecated Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Sync variable product has_nyp status.
	 *
	 * @param   WC_Product $product
	 * @return  void
	 * @since   2.5.1
	 * @deprecated 2.11.0
	 */
	public function variable_sync_has_nyp_status( $product ) {
		wc_deprecated_function( 'WC_Name_Your_Price_Compatibility::variable_sync_has_nyp_status', '3.0.0', 'WC_NYP_Variable_Products_Compatibility::variable_sync_has_nyp_status()' );
		return WC_NYP_Variable_Products_Compatibility::variable_sync_has_nyp_status( $product );
	}

	/**
	 * Sync variable product prices against NYP minimum prices.
	 *
	 * @param   string $product_id
	 * @param   array  $children - the ids of the variations
	 * @return  void
	 * @since   2.0
	 * @deprecated 2.11.0
	 */
	public function variable_product_sync( $product_id, $children ) {
		wc_deprecated_function( 'WC_Name_Your_Price_Compatibility::variable_product_sync', '2.7.0', 'No longer need to sync prices as that happens automatically in WooCommerce core.' );

		$has_nyp = 'no';

		if ( $children ) {

			$min_price    = null;
			$max_price    = null;
			$min_price_id = null;
			$max_price_id = null;

			// Main active prices.
			$min_price    = null;
			$max_price    = null;
			$min_price_id = null;
			$max_price_id = null;

			// Regular prices.
			$min_regular_price    = null;
			$max_regular_price    = null;
			$min_regular_price_id = null;
			$max_regular_price_id = null;

			// Sale prices.
			$min_sale_price    = null;
			$max_sale_price    = null;
			$min_sale_price_id = null;
			$max_sale_price_id = null;

			foreach ( array( 'price', 'regular_price', 'sale_price' ) as $price_type ) {
				foreach ( $children as $child_id ) {

					// if NYP.
					if ( WC_Name_Your_Price_Helpers::is_nyp( $child_id ) ) {

						$has_nyp = 'yes';

						// Skip hidden variations.
						if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
							$stock = get_post_meta( $child_id, '_stock', true );
							if ( '' !== $stock && $stock <= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
								continue;
							}
						}

						// Get the nyp min price for this variation.
						$child_price = get_post_meta( $child_id, '_min_price', true );

						// if there is no set minimum, technically the min is 0.
						$child_price = $child_price ? $child_price : 0;

						// Find min price.
						if ( is_null( ${"min_{$price_type}"} ) || $child_price < ${"min_{$price_type}"} ) {
							${"min_{$price_type}"}    = $child_price;
							${"min_{$price_type}_id"} = $child_id;
						}

						// Find max price.
						if ( is_null( ${"max_{$price_type}"} ) || $child_price > ${"max_{$price_type}"} ) {
							${"max_{$price_type}"}    = $child_price;
							${"max_{$price_type}_id"} = $child_id;
						}
					} else {

						$child_price = get_post_meta( $child_id, '_' . $price_type, true );

						// Skip non-priced variations.
						if ( '' === $child_price ) {
							continue;
						}

						// Skip hidden variations.
						if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
							$stock = get_post_meta( $child_id, '_stock', true );
							if ( '' !== $stock && $stock <= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
								continue;
							}
						}

						// Find min price.
						if ( is_null( ${"min_{$price_type}"} ) || $child_price < ${"min_{$price_type}"} ) {
							${"min_{$price_type}"}    = $child_price;
							${"min_{$price_type}_id"} = $child_id;
						}

						// Find max price.
						if ( $child_price > ${"max_{$price_type}"} ) {
							${"max_{$price_type}"}    = $child_price;
							${"max_{$price_type}_id"} = $child_id;
						}
					}
				}

				// Store prices.
				update_post_meta( $product_id, '_min_variation_' . $price_type, ${"min_{$price_type}"} );
				update_post_meta( $product_id, '_max_variation_' . $price_type, ${"max_{$price_type}"} );

				// Store ids.
				update_post_meta( $product_id, '_min_' . $price_type . '_variation_id', ${"min_{$price_type}_id"} );
				update_post_meta( $product_id, '_max_' . $price_type . '_variation_id', ${"max_{$price_type}_id"} );
			}

			// The VARIABLE PRODUCT price should equal the min price of any type.
			update_post_meta( $product_id, '_price', $min_price );

			// set status for variable product.
			update_post_meta( $product_id, '_has_nyp', $has_nyp );

			wc_delete_product_transients( $product_id );

		}

	}

	/**
	 * Resolves the string to array notice for variable period subs by providing the billing period if one does not exist.
	 *
	 * @param string $period
	 * @param obj    $product
	 * @return string
	 * @since 2.2.0
	 */
	public function product_period( $period, $product ) {

		wc_deprecated_function( 'WC_Name_Your_Price_Compatibility::product_period', '2.7.0', 'No longer need to filter the period as the period is modified at runtime.' );

		if ( WC_Name_Your_Price_Helpers::is_billing_period_variable( $product ) && empty( $period ) ) {
			$period = is_admin() ? WC_Name_Your_Price_Helpers::get_minimum_billing_period( $product ) : WC_Name_Your_Price_Helpers::get_posted_period( $product );
		}

		return $period;
	}

	/**
	 * Declare HPOS (Custom Order tables) compatibility.
	 *
	 * @since 3.3.10
	 * @deprecated 3.5.9
	 */
	public function declare_hpos_compatibility() {

		wc_deprecated_function( 'WC_Name_Your_Price_Compatibility::declare_hpos_compatibility', '3.5.9', 'Method renamed to declare_features_compatibility.' );

		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_Name_Your_Price()->plugin_basename(), true );
	}

} // end class
