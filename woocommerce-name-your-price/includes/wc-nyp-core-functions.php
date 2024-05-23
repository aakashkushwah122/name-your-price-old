<?php
/**
 * Core functions.
 *
 * @package  WooCommerce Name Your Price/Functions
 * @since    3.0.0
 */

/**
 * Returns the main instance of WC_Name_Your_Price to prevent the need to use globals.
 *
 * @since  2.0
 * @return WC_Name_Your_Price
 */
function WC_Name_Your_Price() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WC_Name_Your_Price::instance();
}
