<?php
/**
 * Plugin Name: WooCommerce Name Your Price
 * Plugin URI: http://www.woo.com/products/name-your-price/
 * Description: WooCommerce Name Your Price allows customers to set their own price for products or donations.
 * Version: 3.5.13
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com
 * Woo: 18738:31b4e11696cd99a3c0572975a84f1c08
 *
 * GitHub Plugin URI: https://github.com/kathyisawesome/woocommerce-mix-and-match-products/
 * Primary Branch: trunk
 * Release Asset: true
 *
 * Requires at least: 4.4.0
 * Tested up to: 6.3.0
 *
 * WC requires at least: 4.0.0
 * WC tested up to: 8.2.0
 *
 * Requires PHP: 7.2
 *
 * Text Domain: wc_name_your_price
 * Domain Path: /languages/
 *
 * Copyright: © 2012 - 2023 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce Name Your Price
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WC_NYP_PLUGIN_FILE' ) ) {
	define( 'WC_NYP_PLUGIN_FILE', __FILE__ );
}


/**
 * Load plugin class, if dependencies are met.
 *
 * @since 3.0.0
 */
function wc_nyp_init() {

	// Required WooCommerce version.
	$required_woo = '4.0.0';

	// Required PHP version.
	$required_php = '7.2.0';

	// WC version sanity check.
	if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, $required_woo, '<' ) ) {
		$notice = sprintf(
			// Translators: %1$s opening <a> tag for link. %2$s closing </a> tag. %3$s minimum required WooCommerce version number.
			__( '<strong>WooCommerce Name Your Price is inactive.</strong> The %1$sWooCommerce plugin%2$s must be active and at least version %3$s for Name Your Price to function. Please upgrade or activate WooCommerce.', 'wc_name_your_price' ),
			'<a href="http://wordpress.org/extend/plugins/woocommerce/">',
			'</a>',
			$required_woo
		);
		include_once dirname( __FILE__ ) . '/includes/admin/class-wc-nyp-admin-notices.php';
		WC_NYP_Admin_Notices::add_notice( $notice, 'error' );
		return false;
	}

	// PHP version check.
	if ( ! function_exists( 'phpversion' ) || version_compare( phpversion(), $required_php, '<' ) ) {
		$notice = sprintf(
			// Translators: %1$s link to documentation. %2$s minimum required PHP version number.
			__( 'WooCommerce Name Your Price requires at least PHP <strong>%1$s</strong>. Learn <a href="%2$s">how to update PHP</a>.', 'wc_name_your_price' ),
			$required_php,
			'https://woo.com/document/how-to-update-your-php-version/'
		);
		include_once dirname( __FILE__ ) . '/includes/admin/class-wc-nyp-admin-notices.php';
		WC_NYP_Admin_Notices::add_notice( $notice, 'error' );
		return false;
	}

	// Dependencies are met so launch plugin.
	include_once dirname( __FILE__ ) . '/includes/class-wc-name-your-price.php';
	WC_Name_Your_Price::instance();

}
add_action( 'plugins_loaded', 'wc_nyp_init' );
