<?php
/**
 * Name Your Price Upgrade Functions
 *
 * @package  WooCommerce Name Your Price/Admin/Functions
 * @since    3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Update the suggested and min text options
 *
 * @since 3.5.0
 */
function wc_nyp_300_update_text_options() {

	$_placeholder = '%PRICE%';

	// Get options.
	$min_text       = get_option( 'woocommerce_nyp_minimum_text' );
	$suggested_text = get_option( 'woocommerce_nyp_suggested_text' );

	// Set or update minimum price text.
	if ( false !== $min_text ) {
		$min_text = is_rtl() ? $_placeholder . ' ' . $min_text : $min_text . ' ' . $_placeholder;
		update_option( 'woocommerce_nyp_minimum_text', $min_text );
	}

	// Set or update suggested price text.
	if ( false !== $suggested_text ) {
		$suggested_text = is_rtl() ? $_placeholder . ' ' . $suggested_text : $suggested_text . ' ' . $_placeholder;
		update_option( 'woocommerce_nyp_suggested_text', $suggested_text );
	}

	return false;

}
