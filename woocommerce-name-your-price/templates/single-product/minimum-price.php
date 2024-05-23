<?php

/**
 * Minimum Price Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/minimum-price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce Name Your Price/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<p id="nyp-minimum-price-<?php echo esc_attr( $counter ); ?>" class="minimum-price nyp-terms">
	<?php echo wp_kses_post( WC_Name_Your_Price_Helpers::get_minimum_price_html( $nyp_product ) ); ?>
</p>

