<?php
/**
 * Deprecated action hooks
 *
 * @package WooCommerce Name Your Price/Compatibility
 * @since   3.0.0
 * @version 3.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles deprecation notices and triggering of legacy action hooks.
 */
class WC_NYP_Deprecated_Action_Hooks extends WC_Deprecated_Action_Hooks {

	/**
	 * Array of deprecated hooks we need to handle. Format of 'new' => 'old'.
	 *
	 * @var array
	 */
	protected $deprecated_hooks = array(
		'wc_nyp_loaded'                    => 'wc_name_your_price_loaded',
		'wc_nyp_options_pricing'           => 'woocommerce_name_your_price_options_pricing',
		'wc_nyp_options_variation_pricing' => 'woocommerce_name_your_price_options_variation_pricing',
		'wc_nyp_before_price_input'        => 'woocommerce_nyp_before_price_input',
		'wc_nyp_price_input_label_hint'    => 'woocommerce_nyp_price_input_label_hint',
		'wc_nyp_after_price_input'         => 'woocommerce_nyp_after_price_input',
	);

	/**
	 * Array of versions on each hook has been deprecated.
	 *
	 * @var array
	 */
	protected $deprecated_version = array(
		'wc_name_your_price_loaded'                   => '3.0.0',
		'woocommerce_name_your_price_options_pricing' => '3.0.0',
		'woocommerce_name_your_price_options_variation_pricing' => '3.0.0',
		'woocommerce_nyp_before_price_input'          => '3.0.0',
		'woocommerce_nyp_price_input_label_hint'      => '3.0.0',
		'woocommerce_nyp_after_price_input'           => '3.0.0',
	);

}
