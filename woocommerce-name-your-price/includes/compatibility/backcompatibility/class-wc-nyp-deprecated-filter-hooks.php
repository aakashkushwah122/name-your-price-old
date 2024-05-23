<?php
/**
 * Deprecated filter hooks
 *
 * @package WooCommerce Name Your Price/Compatibility
 * @since   3.0.0
 * @version 3.3.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles deprecation notices and triggering of legacy filter hooks
 */
class WC_NYP_Deprecated_Filter_Hooks extends WC_Deprecated_Filter_Hooks {

	/**
	 * Array of deprecated hooks we need to handle.
	 * Format of 'new' => 'old'.
	 *
	 * @var array
	 */
	protected $deprecated_hooks = array(
		'wc_nyp_price_html'                     => 'woocommerce_nyp_html',
		'wc_nyp_variable_price_html'            => 'woocommerce_variable_nyp_html',
		'wc_nyp_variable_subscription_nyp_html' => 'woocommerce_variable_subscription_nyp_html',
		'wc_nyp_is_nyp'                         => 'woocommerce_is_nyp',
		'wc_nyp_raw_suggested_price'            => 'woocommerce_raw_suggested_price',
		'wc_nyp_raw_minimum_price'              => 'woocommerce_raw_minimum_price',
		'wc_nyp_raw_maximum_price'              => 'woocommerce_raw_maximum_price',
		'wc_nyp_raw_minimum_variation_price'    => 'woocommerce_raw_minimum_variation_price',
		'wc_nyp_is_billing_period_variable'     => 'woocommerce_is_billing_period_variable',
		'wc_nyp_suggested_billing_period'       => 'woocommerce_suggested_billing_period',
		'wc_nyp_minimum_billing_period'         => 'woocommerce_minimum_billing_period',
		'wc_nyp_has_nyp_variations'             => 'woocommerce_has_nyp_variations',
		'wc_nyp_is_minimum_hidden'              => 'woocommerce_is_minimum_hidden',
		'wc_nyp_annual_factors'                 => 'woocommerce_nyp_annual_factors',
		'wc_nyp_minimum_price_html'             => 'woocommerce_nyp_minimum_price_html',
		'wc_nyp_suggested_price_html'           => 'woocommerce_nyp_suggested_price_html',
		'wc_nyp_price_input_label_text'         => 'woocommerce_nyp_price_input_label_text',
		'wc_nyp_price_string'                   => 'woocommerce_nyp_price_string',
		'wc_nyp_get_initial_price'              => 'woocommerce_nyp_get_initial_price',
		'wc_nyp_error_message_templates'        => 'woocommerce_nyp_error_message_templates',
		'wc_nyp_error_message'                  => 'woocommerce_nyp_error_message',
		'wc_nyp_settings'                       => 'woocommerce_nyp_settings',
		'wc_nyp_field_suffix'                   => 'nyp_field_prefix',
		'wc_nyp_script_params'                  => 'nyp_script_params',
		'wc_nyp_disable_edit_link_in_cart'      => 'wc_nyp_isset_disable_edit_it_cart',
	);

	/**
	 * Array of versions on each hook has been deprecated.
	 *
	 * @var array
	 */
	protected $deprecated_version = array(
		'woocommerce_nyp_html'                       => '3.0.0',
		'woocommerce_variable_nyp_html'              => '3.0.0',
		'woocommerce_variable_subscription_nyp_html' => '3.0.0',
		'woocommerce_show_variation_price'           => '3.0.0',
		'woocommerce_is_nyp'                         => '3.0.0',
		'woocommerce_raw_suggested_price'            => '3.0.0',
		'woocommerce_raw_minimum_price'              => '3.0.0',
		'woocommerce_raw_maximum_price'              => '3.0.0',
		'woocommerce_raw_minimum_variation_price'    => '3.0.0',
		'woocommerce_is_billing_period_variable'     => '3.0.0',
		'woocommerce_suggested_billing_period'       => '3.0.0',
		'woocommerce_minimum_billing_period'         => '3.0.0',
		'woocommerce_has_nyp_variations'             => '3.0.0',
		'woocommerce_is_minimum_hidden'              => '3.0.0',
		'woocommerce_nyp_annual_factors'             => '3.0.0',
		'woocommerce_nyp_minimum_price_html'         => '3.0.0',
		'woocommerce_nyp_suggested_price_html'       => '3.0.0',
		'woocommerce_nyp_price_input_label_text'     => '3.0.0',
		'woocommerce_nyp_price_string'               => '3.0.0',
		'woocommerce_nyp_get_initial_price'          => '3.0.0',
		'woocommerce_nyp_error_message_templates'    => '3.0.0',
		'woocommerce_nyp_error_message'              => '3.0.0',
		'woocommerce_nyp_settings'                   => '3.0.0',
		'nyp_field_prefix'                           => '3.0.0',
		'nyp_script_params'                          => '3.0.0',
		'wc_nyp_disable_edit_it_cart'                => '3.3.3',
	);

}
