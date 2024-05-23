<?php
/**
 * Handle front-end display
 *
 * @class   WC_Name_Your_Price_Display
 * @package WooCommerce Name Your Price/Classes
 * @since   1.0.0
 * @version 3.5.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Name_Your_Price_Display class.
 */
class WC_Name_Your_Price_Display {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Name_Your_Price_Display
	 *
	 * @since 3.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_Name_Your_Price_Display
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

		// Single Product Display.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 20 );
		add_action( 'woocommerce_before_single_product', array( $this, 'replace_price_template' ) );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_price_input' ), 9 );
		add_action( 'wc_nyp_after_price_input', array( $this, 'display_variable_billing_periods' ), 5, 2 );
		add_action( 'wc_nyp_after_price_input', array( $this, 'display_minimum_price' ) );
		add_action( 'wc_nyp_after_price_input', array( $this, 'display_error_holder' ), 30 );

		// NYP has_options temp fix for product block.
		add_filter( 'woocommerce_product_has_options', array( $this, 'has_options' ), 10, 2 );

		// Edit in cart.
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'edit_quantity' ), 10, 2 );

		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'single_add_to_cart_text' ), 10, 2 );
		add_filter( 'wc_nyp_minimum_price_html', array( $this, 'add_price_terms_html' ), 10, 2 );

		// Display NYP Prices.
		add_filter( 'woocommerce_get_price_html', array( $this, 'nyp_price_html' ), 10, 2 );
		add_filter( 'woocommerce_variable_subscription_price_html', array( $this, 'variable_subscription_nyp_price_html' ), 10, 2 );

		// Loop Display.
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_text' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_to_cart_url' ), 10, 2 );
		// Kill AJAX add to cart WC2.5+.
		add_filter( 'woocommerce_product_supports', array( $this, 'supports_ajax_add_to_cart' ), 10, 3 );

		// Post class.
		add_filter( 'post_class', array( $this, 'add_post_class' ), 30, 3 );

		// Variable products.
		add_action( 'woocommerce_single_variation', array( $this, 'display_variable_price_input' ), 12 );
		add_filter( 'woocommerce_variation_is_visible', array( $this, 'variation_is_visible' ), 10, 4 );
		add_filter( 'woocommerce_available_variation', array( $this, 'available_variation' ), 10, 3 );
		add_filter( 'woocommerce_get_variation_price', array( $this, 'get_variation_price' ), 10, 4 );
		add_filter( 'woocommerce_get_variation_regular_price', array( $this, 'get_variation_price' ), 10, 4 );

		// Cart display.
		add_filter( 'woocommerce_cart_item_price', array( $this, 'add_edit_link_in_cart' ), 10, 3 );

	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Single Product Display Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Register the scripts and styles.
	 *
	 * @since 3.0
	 */
	public function register_scripts() {

		$this->nyp_style();

		$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$script_path = '/assets/js/frontend/name-your-price' . $suffix . '.js';

		wp_register_script( 'woocommerce-nyp', WC_Name_Your_Price()->plugin_url() . $script_path, array( 'jquery' ), WC_Name_Your_Price()->get_file_version( WC_Name_Your_Price()->plugin_path() . $script_path ), true );

		$params = apply_filters(
			'wc_nyp_script_params',
			array(
				'currency_format_num_decimals'       => wc_get_price_decimals(),
				'currency_format_precision_decimals' => absint( wc_get_rounding_precision() ),
				'currency_format_symbol'             => get_woocommerce_currency_symbol(),
				'currency_format_decimal_sep'        => wc_get_price_decimal_separator(),
				'currency_format_thousand_sep'       => wc_get_price_thousand_separator(),
				'currency_format_position'           => get_option( 'woocommerce_currency_pos' ),
				'currency_format_trim_zeros'         => (bool) apply_filters( 'woocommerce_price_trim_zeros', false ),
				'currency_format'                    => str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ), // Deprecated.
				'annual_price_factors'               => WC_Name_Your_Price_Helpers::annual_price_factors(),
				'i18n_subscription_string'           => __( '%price / %period', 'wc_name_your_price' ),
				'trim_zeros'                         => (bool) apply_filters( 'woocommerce_price_trim_zeros', false ),
				'done_typing'                        => (bool) apply_filters( 'wc_nyp_supports_formatting_on_done_typing', true ),
			)
		);

		/**
		 * Filter wc_nyp_supports_formatting_on_done_typing
		 *
		 * @return bool
		 * @deprecated 3.5.12
		 */
		if ( has_filter( 'wc_nyp_supports_formatting_on_done_typing' ) ) {
			wc_deprecated_function( 'The wc_nyp_supports_formatting_on_done_typing filter has been deprecated for accessibility reasons! Because it no longer has a function you can safely remove your callback.', '3.5.12' );
		}

		wp_localize_script( 'woocommerce-nyp', 'woocommerce_nyp_params', $params );
	}

	/**
	 * Load a little stylesheet.
	 *
	 * @return void
	 * @since 1.0
	 */
	public function nyp_style() {
		if ( get_option( 'woocommerce_nyp_disable_css', 'no' ) !== 'yes' ) {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_style( 'woocommerce-nyp', WC_Name_Your_Price()->plugin_url() . '/assets/css/frontend/name-your-price' . $suffix . '.css', false, WC_Name_Your_Price()->version );
			wp_style_add_data( 'woocommerce-nyp', 'rtl', 'replace' );
			if ( $suffix ) {
				wp_style_add_data( 'woocommerce-nyp', 'suffix', '.min' );
			}
		}
	}

	/**
	 * Load price input script.
	 *
	 * @return void
	 */
	public function nyp_scripts() {
		wp_enqueue_script( 'woocommerce-nyp' );
	}

	/**
	 * Remove the default price template.
	 *
	 * @since 3.0.0
	 */
	public function replace_price_template() {
		global $product;
		if ( apply_filters( 'wc_nyp_replace_price_template', true, $product ) && WC_Name_Your_Price_Helpers::is_nyp( $product ) && has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' ) ) {

			// Move price template to before NYP input.
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'display_suggested_price' ) );

			// Restore price template to original.
			add_action( 'woocommerce_after_single_product', array( $this, 'restore_price_template' ) );

		}
	}

	/**
	 * Restore the default price template.
	 *
	 * @since 3.0.0
	 */
	public function restore_price_template() {
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_before_add_to_cart_form', array( $this, 'display_suggested_price' ) );
	}

	/**
	 * Call the Price Input Template.
	 *
	 * @param mixed obj|int $product
	 * @param array $args - specific values for this display
	 *      {
	 *         'suffix' => string key to integration with Bundles, OPC, etc - anywhere more than 1 input may appear
	 *         'force' => bool
	 *       }
	 * @return  void
	 * @since 1.0
	 */
	public function display_price_input( $product = false, $args = array() ) {

		// Backcompat handling of string suffix.
		if ( is_string( $args ) ) {
			$args = array(
				'suffix' => $args,
			);
		}

		$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $product );

		if ( ! $product ) {
			global $product;
		}

		// If not NYP quit right now. Also distinguish if we're a variable product vs simple.
		if ( ! $product ||
			( 'woocommerce_single_variation' === current_action() && ! WC_Name_Your_Price_Helpers::has_nyp( $product ) ) ||
			( 'woocommerce_single_variation' !== current_action() && ! WC_Name_Your_Price_Helpers::is_nyp( $product ) && ! apply_filters( 'wc_nyp_force_display_price_input', isset( $args['force'] ) ? boolval( $args['force'] ) : false, $product ) )
		) {
			return;
		}

		$suffix   = isset( $args['suffix'] ) ? $args['suffix'] : '';
		$price    = WC_Name_Your_Price_Helpers::get_price_value_attr( $product, $suffix );
		$counter  = WC_Name_Your_Price_Helpers::get_counter();
		$input_id = 'nyp-' . $counter;

		$defaults = array(
			'product_id'        => $product->get_id(),
			'nyp_product'       => $product,
			'counter'           => $counter,
			'input_id'          => $input_id,
			'input_type'        => 'text',
			'input_name'        => 'nyp' . $suffix,
			'input_value'       => WC_Name_Your_Price_Helpers::format_price( $price ),
			'input_label'       => WC_Name_Your_Price_Helpers::get_price_input_label_text( $product ),
			'classes'           => array( 'input-text', 'amount', 'nyp-input', 'text' ),
			'aria-describedby'  => array( 'nyp-minimum-price-' . $input_id, 'nyp-error-' . $input_id ),
			'placeholder'       => '',
			'custom_attributes' => array(
				'inputmode' => 'decimal',
			),
			'suffix'            => $suffix,
			'prefix'            => $suffix, // For backcompat.
			'updating_cart_key' => isset( $_GET['update-price'] ) && WC()->cart->find_product_in_cart( sanitize_key( $_GET['update-price'] ) ) ? sanitize_key( $_GET['update-price'] ) : '',
			'_nypnonce'         => isset( $_GET['_nypnonce'] ) ? sanitize_key( $_GET['_nypnonce'] ) : '',
		);

		/**
		 * Filter wc_nyp_price_input_attributes
		 *
		 * @param  array $attributes The array of attributes for the NYP div
		 * @param  obj $product WC_Product The product object
		 * @param  string $suffix - needed for grouped, composites, bundles, etc.
		 * @return string
		 * @since  2.11.0
		 */
		$args = (array) apply_filters( 'wc_nyp_price_input_attributes', wp_parse_args( $args, $defaults ), $product, $suffix );

		// Load up the NYP scripts.
		$this->nyp_scripts();

		// Get the price input template.
		wc_get_template(
			'single-product/price-input.php',
			$args,
			false,
			WC_Name_Your_Price()->plugin_path() . '/templates/'
		);

		/**
		 * Filter woocommerce_get_price_input
		 *
		 * @param  string $html - the resulting input's html.
		 * @param  int    $product_id - the product id.
		 * @param  string $suffix - needed for grouped, composites, bundles, etc.
		 * @return string
		 * @deprecated 3.0.0
		 */
		if ( has_filter( 'woocommerce_get_price_input' ) ) {
			wc_doing_it_wrong( __FUNCTION__, 'woocommerce_get_price_input filter has been removed for security reasons! Please consider using the wc_nyp_price_input_attributes filter to modify attributes or overriding the price-input.php template.', '3.0' );
		}

		WC_Name_Your_Price_Helpers::increase_counter();

	}

	/**
	 * Display the suggested price.
	 *
	 * @param mixed obj|int $product
	 * @return  void
	 * @since 1.0
	 */
	public function display_suggested_price( $product = false ) {

		$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $product );

		if ( ! $product ) {
			global $product;
		}

		$suggested_price_html = WC_Name_Your_Price_Helpers::get_suggested_price_html( $product );

		if ( ! $suggested_price_html && ! WC_Name_Your_Price_Helpers::has_nyp( $product ) ) {
			return;
		}

		echo '<p class="price suggested-price">' . wp_kses_post( $suggested_price_html ) . '</p>';

	}


	/**
	 * Display minimum price plus any subscription terms if applicable.
	 *
	 * @param mixed obj|int $product
	 * @return  void
	 * @since 1.0
	 */
	public function display_minimum_price( $product = false ) {

		$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $product );

		if ( ! $product ) {
			global $product;
		}

		$minimum_price_html = WC_Name_Your_Price_Helpers::get_minimum_price_html( $product );

		if ( ! $minimum_price_html && ! WC_Name_Your_Price_Helpers::has_nyp( $product ) ) {
			return;
		}

		// Get the minimum price template.
		wc_get_template(
			'single-product/minimum-price.php',
			array(
				'product_id'  => $product->get_id(),
				'nyp_product' => $product,
				'counter'     => WC_Name_Your_Price_Helpers::get_counter(),
			),
			false,
			WC_Name_Your_Price()->plugin_path() . '/templates/'
		);

	}


	/**
	 * Show the empty error-holding div.
	 *
	 * @since 3.0.0
	 */
	public function display_error_holder() {
		printf( '<div id="nyp-error-%s" class="woocommerce-nyp-message" aria-live="assertive" style="display: none"></div>', esc_attr( WC_Name_Your_Price_Helpers::get_counter() ) );
	}


	/**
	 * Tell WooCommerce that a NYP product has options, forces All Products blocks to redirect to single page.
	 *
	 * @since 3.2.0
	 *
	 * @param bool $has_options
	 * @param object $product
	 * @return bool
	 */
	public function has_options( $has_options, $product ) {
		return ! $has_options && defined( 'REST_REQUEST' ) && WC_Name_Your_Price_Helpers::is_nyp( $product ) ? true : $has_options;
	}


	/**
	 * Carry cart quantity back to single product page when editing.
	 *
	 * @since 3.2.0
	 *
	 * @param array $args
	 * @param object $product
	 * @return array
	 */
	public function edit_quantity( $args, $product ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {

			if ( isset( $_GET['update-price'] ) && isset( $_GET['quantity'] ) && isset( $_GET['_nypnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_nypnonce'] ), 'nyp-nonce' ) ) {

				$updating_cart_key = wc_clean( wp_unslash( $_GET['update-price'] ) );

				if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
					$qty                 = wc_clean( wp_unslash( $_GET['quantity'] ) );
					$args['input_value'] = $qty;
				}
			}
		}

		return $args;

	}


	/**
	 * If NYP change the single item's add to cart button text.
	 * Don't include on variations as you can't be sure all the variations are NYP.
	 * Variations will be handled via JS.
	 *
	 * @param string $text
	 * @param object $product
	 * @return string
	 * @since 2.0
	 */
	public function single_add_to_cart_text( $text, $product ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			$nyp_text = trim( apply_filters( 'wc_nyp_single_add_to_cart_text', get_option( 'woocommerce_nyp_button_text_single', '' ), $product ) );

			if ( '' !== $nyp_text ) {
				$text = $nyp_text;
			}

			if ( isset( $_GET['update-price'] ) && isset( $_GET['_nypnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_nypnonce'] ), 'nyp-nonce' ) ) {

				$updating_cart_key = wc_clean( wp_unslash( $_GET['update-price'] ) );

				if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
					$text = apply_filters( 'wc_nyp_single_update_cart_text', __( 'Update Cart', 'wc_name_your_price' ), $product );
				}
			}
		}

		return $text;

	}


	/**
	 * Add subscription terms to minimum price html
	 *
	 * @param string     $html
	 * @param WC_Product $product
	 * @return  string
	 * @since 3.0.0
	 */
	public function add_price_terms_html( $html, $product ) {

		$subscription_terms = WC_Name_Your_Price_Helpers::get_subscription_terms_html( $product );

		if ( $html && $subscription_terms ) {
			// Translators: %1$s is minimum price html. %2$s subscription terms.
			$html = sprintf( __( '%1$s %2$s', 'wc_name_your_price' ), $html, $subscription_terms );
		} elseif ( $subscription_terms ) {
			$html = $subscription_terms;
		}

		return $html;
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Display NYP Price HTML
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Filter the Price HTML.
	 *
	 * @param string $html
	 * @param object $product
	 * @return string
	 * @since 2.0
	 */
	public function nyp_price_html( $html, $product ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			$html = apply_filters( 'wc_nyp_price_html', WC_Name_Your_Price_Helpers::get_suggested_price_html( $product ), $product );
		} elseif ( WC_Name_Your_Price_Helpers::has_nyp( $product ) && ! $product->is_type( 'variable-subscription' ) ) {
			$min_variation_string = WC_Name_Your_Price_Helpers::is_variable_price_hidden( $product ) ? '' : WC_Name_Your_Price_Helpers::get_price_string( $product, 'minimum-variation' );
			$html                 = '' !== $min_variation_string ? wc_get_price_html_from_text() . $min_variation_string : '';
			$html                 = apply_filters( 'wc_nyp_variable_price_html', $html, $product );
		}

		return $html;

	}

	/**
	 * Filter the Price HTML for Variable Subscriptions.
	 *
	 * @param string $html
	 * @param object $product
	 * @return string
	 * @since 1.0
	 * @renamed in 2.0
	 */
	public function variable_subscription_nyp_price_html( $html, $product ) {

		if ( WC_Name_Your_Price_Helpers::has_nyp( $product ) && WC_Name_Your_Price_Helpers::is_variable_price_hidden( $product ) && intval( WC_Subscriptions_Product::get_sign_up_fee( $product ) ) === 0 && intval( WC_Subscriptions_Product::get_trial_length( $product ) ) === 0 ) {
			$html = '';
		}

		return apply_filters( 'wc_nyp_variable_subscription_nyp_html', $html, $product );

	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Loop Display Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * If NYP change the loop's add to cart button text.
	 *
	 * @param string $text
	 * @return string
	 * @since 1.0
	 */
	public function add_to_cart_text( $text, $product ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {

			$nyp_text = trim( apply_filters( 'wc_nyp_add_to_cart_text', get_option( 'woocommerce_nyp_button_text', __( 'Choose price', 'wc_name_your_price' ) ), $product ) );

			if ( '' !== $nyp_text ) {
				$text = $nyp_text;
			} else {
				$text = __( 'Choose price', 'wc_name_your_price' );
			}
		}

		return $text;

	}

	/**
	 * If NYP change the loop's add to cart button URL.
	 * Disable ajax add to cart and redirect to product page.
	 * Supported by WC<2.5.
	 *
	 * @param string $url
	 * @return string
	 * @since 1.0
	 */
	public function add_to_cart_url( $url, $product = null ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			$url = get_permalink( $product->get_id() );
		}

		return $url;

	}


	/**
	 * Disable ajax add to cart and redirect to product page.
	 * Supported by WC2.5+
	 *
	 * @param string $url
	 * @return string
	 * @since 1.0
	 */
	public function supports_ajax_add_to_cart( $supports_ajax, $feature, $product ) {

		if ( 'ajax_add_to_cart' === $feature && WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			$supports_ajax = false;
		}

		return $supports_ajax;

	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Post Class
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Add nyp to post class.
	 *
	 * @param  array  $classes - post classes
	 * @param  string $class
	 * @param  int    $post_id
	 * @return array
	 * @since 2.0
	 */
	public function add_post_class( $classes, $class = '', $post_id = '' ) {
		if ( ! $post_id || get_post_type( $post_id ) !== 'product' ) {
			return $classes;
		}

		if ( WC_Name_Your_Price_Helpers::is_nyp( $post_id ) || WC_Name_Your_Price_Helpers::has_nyp( $post_id ) ) {
			$classes[] = 'nyp-product';
		}

		return $classes;

	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Variable Product Display Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Call the Price Input Template for Variable products.
	 *
	 * @param mixed obj|int $product
	 * @param string        $suffix - suffix is key to integration with Bundles
	 * @since 3.0
	 */
	public function display_variable_price_input( $product = false, $suffix = false ) {
		$this->display_price_input( $product, $suffix );
	}

	/**
	 * Make NYP variations visible.
	 *
	 * @param  boolean                  $visible - whether to display this variation or not
	 * @param  int                      $variation_id
	 * @param  int                      $product_id
	 * @param  obj WC_Product_Variation
	 * @return boolean
	 * @since 2.0
	 */
	public function variation_is_visible( $visible, $variation_id, $product_id, $variation ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
			$visible = true;
		}

		return $visible;
	}

	/**
	 * Add nyp data to json encoded variation form.
	 *
	 * @param  array  $data - this is the variation's json data
	 * @param  object $product
	 * @param  object $variation
	 * @return array
	 * @since 2.0
	 */
	public function available_variation( $data, $product, $variation ) {

		if ( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
			$data['is_nyp']                = true;
			$data['minimum_price']         = WC_Name_Your_Price_Helpers::get_minimum_price( $variation );
			$data['maximum_price']         = WC_Name_Your_Price_Helpers::get_maximum_price( $variation );
			$data['initial_price']         = WC_Name_Your_Price_Helpers::get_initial_price( $variation );
			$data['price_label']           = WC_Name_Your_Price_Helpers::get_price_input_label_text( $variation );
			$data['posted_price']          = WC_Name_Your_Price_Helpers::format_price( WC_Name_Your_Price_Helpers::get_posted_price( $variation ) );
			$data['display_price']         = WC_Name_Your_Price_Helpers::format_price( WC_Name_Your_Price_Helpers::get_price_value_attr( $variation ) );
			$data['display_regular_price'] = $data['display_price'];
			$data['price_html']            = apply_filters( 'woocommerce_show_variation_price', true, $product, $variation ) ? '<span class="price">' . WC_Name_Your_Price_Helpers::get_suggested_price_html( $variation ) . '</span>' : '';
			$data['minimum_price_html']    = WC_Name_Your_Price_Helpers::get_minimum_price_html( $variation );
			$data['hide_minimum']          = WC_Name_Your_Price_Helpers::is_minimum_hidden( $variation );
			$data['add_to_cart_text']      = $variation->single_add_to_cart_text();
		} else {
			$data['is_nyp'] = false;
		}

		return $data;

	}

	/**
	 * Get the NYP min price of the lowest-priced variation.
	 *
	 * @param  string  $price
	 * @param  string  $min_or_max - min or max
	 * @param  boolean $display Whether the value is going to be displayed
	 * @return string
	 * @since  2.0
	 */
	public function get_variation_price( $price, $product, $min_or_max, $display ) {

		if ( WC_Name_Your_Price_Helpers::has_nyp( $product ) && 'min' === $min_or_max ) {

			$prices = $product->get_variation_prices();

			if ( is_array( $prices ) && isset( $prices['price'] ) ) {

				// Get the ID of the variation with the minimum price.
				reset( $prices['price'] );
				$min_id = key( $prices['price'] );

				// If the minimum variation is an NYP variation then get the minimum price. This lets you distinguish between 0 and null.
				if ( WC_Name_Your_Price_Helpers::is_nyp( $min_id ) ) {
					$price = WC_Name_Your_Price_Helpers::get_minimum_price( $min_id );
				}
			}
		}

		return $price;
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Variable Billing Period Display Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Display the period options for variable billint periods.
	 *
	 * @param mixed obj|int $product
	 * @param string        $suffix - suffix is key to integration with Bundles
	 * @since 3.0
	 */
	public function display_variable_billing_periods( $product, $suffix ) {

		$product = WC_Name_Your_Price_Helpers::maybe_get_product_instance( $product );

		if ( WC_Name_Your_Price_Helpers::is_billing_period_variable( $product ) ) {

			// Create the dropdown select element.
			$period = WC_Name_Your_Price_Helpers::get_period_value_attr( $product, $suffix );

			// The pre-selected value.
			$selected = $period ? $period : 'month';

			// Get list of available periods from Subscriptions plugin.
			$periods = WC_Name_Your_Price_Helpers::get_subscription_period_strings();

			if ( $periods ) {

				printf( '<span class="nyp-billing-period"><span class="per"> / </span><select id="nyp-period%s" name="%s" class="nyp-period">', intval( WC_Name_Your_Price_Helpers::get_counter() ), esc_attr( 'nyp-period' . $suffix ) );

				foreach ( $periods as $i => $period ) :
					printf( '<option value="%s" %s>%s</option>', esc_attr( $i ), selected( $i, $selected, false ), esc_html( $period ) );
				endforeach;

				echo '</select></span>';

				/**
				 * Filter wc_nyp_subscription_period_input
				 *
				 * @param  string $period_input - the resulting input's html.
				 * @param  obj    $product - the product object.
				 * @param  string $suffix - needed for grouped, composites, bundles, etc.
				 * @return string
				 * @deprecated 3.0.0
				 */
				if ( has_filter( 'wc_nyp_subscription_period_input' ) ) {
					wc_doing_it_wrong( __FUNCTION__, 'woocommerce_get_price_input filter has been removed for security reasons!', '3.0' );
				}
			}
		}
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Cart Display Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Add edit link to cart items.
	 *
	 * @param  string $content
	 * @param  array  $cart_item
	 * @param  string $cart_item_key
	 * @return string
	 */
	public function add_edit_link_in_cart( $content, $cart_item, $cart_item_key ) {

		if ( isset( $cart_item['nyp'] ) && apply_filters( 'wc_nyp_show_edit_link_in_cart', true, $cart_item, $cart_item_key ) ) {

			if ( function_exists( 'is_cart' ) && is_cart() && ! $this->is_cart_widget() ) {

				// Build the Edit link URL.
				$edit_in_cart_link = WC_Name_Your_Price_Helpers::get_edit_url( $cart_item );

				$edit_in_cart_text = _x( 'Edit price', 'edit in cart link text', 'wc_name_your_price' );

				// Translators: %1$s Original cart price string. %2$s URL for edit price link. %3$s text for edit price link.
				$content = sprintf( _x( '%1$s<br/><a class="edit_price_in_cart_text edit_in_cart_text" href="%2$s"><small>%3$s</small></a>', 'edit in cart text', 'wc_name_your_price' ), $content, esc_url( $edit_in_cart_link ), $edit_in_cart_text );

			}
		}

		return $content;

	}

	/**
	 * Rendering cart widget?
	 *
	 * @since  1.4.0
	 * @return boolean
	 */
	public function is_cart_widget() {
		return did_action( 'woocommerce_before_mini_cart' ) > did_action( 'woocommerce_after_mini_cart' );
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Deprecated Functions
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Add a hidden input to facilitate changing the price from cart.
	 *
	 * @since 2.11.1
	 * @deprecated 3.0.0
	 */
	public function display_hidden_update_input() {
		// phpcs:disable WordPress.Security.NonceVerification
		wc_deprecated_function( 'WC_Name_Your_Price_Display::display_hidden_update_input()', '3.0.0', 'Output is auto-generated by WC_Name_Your_Price_Helpers::get_price_input().' );
		if ( isset( $_GET['update-price'] ) ) {
			$updating_cart_key = wc_clean( wp_unslash( $_GET['update-price'] ) );
			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
				echo '<input type="hidden" name="update-price" value="' . esc_attr( $updating_cart_key ) . '" />';
			}
		}
	}

	/**
	 * Change price input position on variable products - This shows them before Product Addons.
	 *
	 * @since 2.8.3
	 * @deprecated 3.0
	 */
	public function move_display_for_variable_product() {
		wc_deprecated_function( 'WC_Name_Your_Price_Display::move_display_for_variable_product()', '3.0.0', 'Function replaced by WC_Name_Your_Price_Display::display_variable_price_input().' );
		remove_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_price_input' ), 9 );
		add_action( 'woocommerce_single_variation', array( $this, 'display_price_input' ), 12 );
	}

	/**
	 * Fix price input position on variable products - This shows them before Product Addons.
	 *
	 * @since 2.9.4
	 * @deprecated 3.0
	 */
	public function revert_display_after_variable_product() {
		wc_deprecated_function( 'WC_Name_Your_Price_Display::revert_display_after_variable_product()', '3.0.0', 'Function replaced by WC_Name_Your_Price_Display::display_variable_price_input().' );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_price_input' ), 9 );
		remove_action( 'woocommerce_single_variation', array( $this, 'display_price_input' ), 12 ); // After WC variation wrap and before Product Add-ons.
	}

	/**
	 * Display the price input with a named prefix to distinguish it from other NYP inputs on the same page.
	 *
	 * @param str            $html
	 * @param obj WC_Product $product
	 * @return str
	 * deprecated 3.0.0
	 */
	public function grouped_add_input( $html, $product ) {
		wc_deprecated_function( 'WC_Name_Your_Price_Display::display_input()', '3.0.0', 'Function relocated to WC_NYP_Grouped_Products_Compatibility class.' );
		return WC_NYP_Grouped_Products_Compatibility::display_input( $html, $product );
	}

	/**
	 * Check for the prefix when adding to cart.
	 *
	 * @param string $prefix
	 * @param  int    $nyp_id the product ID or variation ID of the NYP product being displayed
	 * @return string
	 * @deprecated 3.0.0
	 */
	public function grouped_cart_prefix( $prefix, $nyp_id ) {
		wc_deprecated_function( 'WC_Name_Your_Price_Display::grouped_cart_prefix()', '3.0.0', 'Function relocated to WC_NYP_Grouped_Products_Compatibility class.' );
		return WC_NYP_Grouped_Products_Compatibility::grouped_cart_suffix( $prefix, $nyp_id );
	}

} // End class.
