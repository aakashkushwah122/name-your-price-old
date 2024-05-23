<?php
/**
 * The Main WC_Name_Your_Price class.
 *
 * @class    WC_Name_Your_Price
 * @package  WooCommerce Name Your Price/Classes
 * @since    1.0.0
 * @version  3.5.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The Main WC_Name_Your_Price class.
 */
class WC_Name_Your_Price {

	/**
	 * The single instance of the class
	 *
	 * @var $instance
	 * @since 2.0
	 */
	protected static $instance = null;

	/**
	 * The plugin version
	 *
	 * @var $version
	 * @since 2.0
	 */
	public $version = '3.5.13';

	/**
	 * The minimum required WooCommerce version
	 *
	 * @var $required_woo
	 * @since 2.1
	 * @deprecated 3.0.0
	 */
	public $required_woo = '4.0.0';

	/**
	 * Array of deprecated hook handlers.
	 *
	 * @var array of WC_NYP_Deprecated_Hooks
	 * @since 3.0
	 */
	public $deprecated_hook_handlers = array();

	/**
	 * Main WC_Name_Your_Price Instance.
	 *
	 * Ensures only one instance of WC_Name_Your_Price is loaded or can be loaded.
	 *
	 * @static
	 * @see WC_Name_Your_Price()
	 * @return WC_Name_Your_Price - Main instance
	 * @since 2.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			// For backcompatibility, still set global.
			$GLOBALS['wc_name_your_price'] = self::$instance;
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning this object is forbidden.', 'wc_name_your_price' ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'wc_name_your_price' ) );
	}

	/**
	 * Auto-load in-accessible properties.
	 *
	 * @param  mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'compatibility', 'cart', 'order', 'display' ) ) ) {
			$classname = 'WC_Name_Your_Price_' . ucfirst( $key );
			return call_user_func( array( $classname, 'instance' ) );
		}
	}

	/**
	 * WC_Name_Your_Price Constructor.
	 *
	 * @return WC_Name_Your_Price
	 * @since 1.0
	 */

	public function __construct() {

		// Include required files.
		$this->includes();

		// Always load translation files.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Filter wc_get_products to accept "name-your-price"=>true.
		add_filter( 'woocommerce_get_wp_query_args', array( $this, 'query_args' ), 10, 2 );

		// Prepare handling of deprecated filters/actions.
		$this->deprecated_hook_handlers['actions'] = new WC_NYP_Deprecated_Action_Hooks();
		$this->deprecated_hook_handlers['filters'] = new WC_NYP_Deprecated_Filter_Hooks();

		// Launch sub-classes.
		WC_Name_Your_Price_Display::instance();
		WC_Name_Your_Price_Cart::instance();
		WC_Name_Your_Price_Order::instance();
		WC_Name_Your_Price_Compatibility::instance();

		do_action( 'wc_nyp_loaded' );

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Helper Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 * @since  2.0
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WC_NYP_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 * @since  2.0
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WC_NYP_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin base path name.
	 *
	 * @since  3.3.10
	 *
	 * @return string
	 */
	public function plugin_basename() {
		return plugin_basename( WC_NYP_PLUGIN_FILE );
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @since 3.5.5
	 *
	 * @param  string  $file
	 * @param  string  $version - A version number, handy for mini-extensions to make use of this method.
	 * @return string
	 */
	public function get_file_version( $file, $version = '' ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return $version ? $version : $this->version;
	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Required Files
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @return void
	 * @since  1.0
	 */
	public function includes() {

		// Class autoloader.
		include_once $this->plugin_path() . '/includes/class-wc-nyp-autoloader.php';

		// Include core functions.
		include_once $this->plugin_path() . '/includes/wc-nyp-core-functions.php';

		// Include WC compatibility functions.
		include_once $this->plugin_path() . '/includes/compatibility/core/class-wc-name-your-price-core-compatibility.php';

		// Include all helper functions.
		include_once $this->plugin_path() . '/includes/class-wc-name-your-price-helpers.php';

		// Include the front-end functions.
		include_once $this->plugin_path() . '/includes/class-wc-name-your-price-display.php';
		include_once $this->plugin_path() . '/includes/class-wc-name-your-price-cart.php';
		include_once $this->plugin_path() . '/includes/class-wc-name-your-price-order.php';

		// Include compatibility modules.
		include_once $this->plugin_path() . '/includes/compatibility/class-wc-name-your-price-compatibility.php';

		// Include deprecated functions.
		include_once $this->plugin_path() . '/includes/wc-nyp-deprecated-functions.php';

		// Support deprecated filter hooks and actions.
		include_once $this->plugin_path() . '/includes/compatibility/backcompatibility/class-wc-nyp-deprecated-action-hooks.php';
		include_once $this->plugin_path() . '/includes/compatibility/backcompatibility/class-wc-nyp-deprecated-filter-hooks.php';

		// Store API
		include_once $this->plugin_path() . '/includes/api/class-wc-nyp-store-api.php';

		// Include admin class to handle all backend functions.
		if ( is_admin() ) {

			// Admin includes.
			$this->admin_includes();

		}

	}

	/**
	 * Load the admin files.
	 *
	 * @return void
	 * @since  2.2
	 */
	public function admin_includes() {
		include_once $this->plugin_path() . '/includes/admin/class-wc-name-your-price-admin.php';
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Localization
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/wc_name_your_price/wc_name_your_price-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/wc_name_your_price-LOCALE.mo
	 *      - WP_CONTENT_DIR/plugins/woocommerce-name-your-price/languages/wc_name_your_price-LOCALE.mo
	 *
	 * @return void
	 * @since  1.0
	 */
	public function load_plugin_textdomain() {
		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wc_name_your_price' );

		load_textdomain( 'wc_name_your_price', WP_LANG_DIR . '/wc_name_your_price/wc_name_your_price-' . $locale . '.mo' );
		load_plugin_textdomain( 'wc_name_your_price', false, plugin_basename( dirname( WC_NYP_PLUGIN_FILE ) ) . '/languages' );

	}

	/**
	 * ---------------------------------------------------------------------------------
	 * Products Query
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Handle querying by product_type = "name-your-price"
	 *
	 * @param array $query - Args for WP_Query.
	 * @param array $query_vars - Query vars from WC_Product_Query.
	 * @return array modified $query
	 * @since 1.0
	 */
	public function query_args( $query, $query_vars ) {

		if ( ! empty( $query_vars['name-your-price'] ) ) {

			$query['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_nyp',
					'value'   => 'yes',
					'compare' => '=',
				),
				array(
					'key'     => '_has_nyp',
					'value'   => 'yes',
					'compare' => '=',
				),
			);

		}

		return $query;
	}



	/*
	-----------------------------------------------------------------------------------*/
	/*
	  Resources                                                                        */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Returns URL to a doc or support resource.
	 *
	 * @since  3.5.0
	 *
	 * @param  string  $handle
	 * @return string
	 */
	public function get_resource_url( $handle ) {

		switch ( $handle ) {
			case 'ticket-form':
				$resource = 'https://woo.com/my-account/create-a-ticket/?select=18738';
				break;
			case 'docs':
				$resource = 'https://woo.com/document/name-your-price/';
				break;
			case 'updating':
				$resource = 'https://woo.com/document/how-to-update-woocommerce/';
				break;
			case 'outdated-templates':
				$resource = 'https://woo.com/document/fix-outdated-templates-woocommerce/';
				break;
			default:
				$resource = false;
		}

		return $resource;
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Deprecated Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Displays a warning message if version check fails.
	 *
	 * @return string
	 * @since  2.1
	 * @deprecated 1.6.0
	 */
	public function admin_notice() {
		wc_deprecated_function( 'WC_NYP_Admin_Notices::admin_notice()', '1.6.0', 'Function is no longer used.' );
		if ( current_user_can( 'activate_plugins' ) ) {
			// Translators: %s minimum required WooCommerce version number.
			echo '<div class="error"><p>' . esc_html( sprintf( __( 'WooCommerce Name Your Price requires at least WooCommerce %s in order to function. Please activate or upgrade WooCommerce.', 'wc_name_your_price' ), $this->required_woo ) ) . '</p></div>';
		}
	}

	/**
	 * Test environement meets min requirements.
	 *
	 * @since  2.10.0
	 * @deprecated 3.0.0
	 */
	public function has_min_environment() {

		wc_deprecated_function( 'WC_Name_Your_Price::has_min_environment', '3.0.0', 'Environment checks now happen before class is loaded.' );

		// WC version sanity check.
		if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, $this->required_woo, '<' ) ) {
			// Translators: %1$s opening <a> tag for link. %2$s closing </a> tag. %3$s minimum required WooCommerce version number.
			$notice = sprintf( __( '<strong>WooCommerce Name Your Price is inactive.</strong> The %1$sWooCommerce plugin%2$s must be active and at least version %3$s for Name Your Price to function. Please upgrade or activate WooCommerce.', 'wc_name_your_price' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', $this->required_woo );
			require_once 'includes/admin/class-wc-nyp-admin-notices.php';
			WC_NYP_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		// PHP version check.
		if ( ! function_exists( 'phpversion' ) || version_compare( phpversion(), '5.6.20', '<' ) ) {
			// Translators: %1$s link to documentation. %2$s minimum required PHP version number.
			$notice = sprintf( __( 'WooCommerce Name Your Price requires at least PHP <strong>%1$s</strong>. Learn <a href="%2$s">how to update PHP</a>.', 'wc_name_your_price' ), '5.6.20', 'https://woo.com/document/how-to-update-your-php-version/' );
			require_once 'includes/admin/class-wc-nyp-admin-notices.php';
			WC_NYP_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		return true;

	}

	/**
	 * 'Settings' link on plugin page
	 *
	 * @deprecated 3.5.10 - Moved to WC_Name_Your_Price_Admin class.
	 *
	 * @param array $links
	 * @return array
	 * @since 1.0
	 */
	public function add_action_link( $links ) {

		wc_deprecated_function( __METHOD__, '3.5.10', 'Method relocated to WC_Name_Your_Price_Admin class.' );

		$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=nyp' ) . '" title="' . esc_html__( 'Go to the settings page', 'wc_name_your_price' ) . '">' . esc_html__( 'Settings', 'wc_name_your_price' ) . '</a>';
		return array_merge( (array) $settings_link, $links );

	}

} // End class: do not remove or there will be no more guacamole for you.
