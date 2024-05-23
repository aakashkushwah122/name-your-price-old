<?php
/**
 * Deprecated functions.
 *
 * These were needed to bridge the gap between WC 2.x and WC 3.0.
 *
 * @package  WooCommerce Name Your Price/Deprecated
 * @since    2.5.0
 */

/**
 * Returns the product object.
 *
 * @deprecated 2.5.0
 * @return wc_get_product
 */
function wc_nyp_get_product( $the_product = false, $args = array() ) {
	wc_deprecated_function( 'wc_nyp_get_product', '2.5.0', 'wc_get_product' );
	return wc_get_product( $the_product, $args );
}

/**
 * Returns the number of price decimals from settings.
 *
 * @deprecated 2.5.0
 * @return wc_get_price_decimals
 */
function wc_nyp_get_price_decimals() {
	wc_deprecated_function( 'wc_nyp_get_price_decimals', '2.5.0', 'wc_get_price_decimals' );
	return wc_get_price_decimals();
}

/**
 * Returns the decimal separator from settings.
 *
 * @deprecated 2.5.0
 * @return wc_get_price_decimal_separator
 */
function wc_nyp_get_price_decimal_separator() {
	wc_deprecated_function( 'wc_nyp_get_price_decimal_separator', '2.5.0', 'wc_get_price_decimal_separator' );
	return wc_get_price_decimal_separator();
}

/**
 * Returns the thousands separator from settings.
 *
 * @deprecated 2.5.0
 * @return wc_get_price_thousand_separator()
 */
function wc_nyp_get_price_thousand_separator() {
	wc_deprecated_function( 'wc_nyp_get_price_thousand_separator', '2.5.0', 'wc_get_price_thousand_separator' );
	return wc_get_price_thousand_separator();
}
