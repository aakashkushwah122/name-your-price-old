<?php
/**
 * Single Product Price Input
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price-input.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce Name Your Price/Templates
 *
 * @since   1.0.0
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="nyp" <?php echo WC_Name_Your_Price_Helpers::get_data_attributes( $nyp_product, $suffix ); ?> > <?php // phpcs:ignore WordPress.Security.EscapeOutput ?>

	<?php do_action( 'wc_nyp_before_price_input', $nyp_product, $suffix ); ?>

		<?php do_action( 'wc_nyp_before_price_label', $nyp_product, $suffix ); ?>

		<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo wp_kses_post( $input_label ); ?></label>

		<?php do_action( 'wc_nyp_after_price_label', $nyp_product, $suffix ); ?>

		<input
			type="<?php echo esc_attr( $input_type ); ?>"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( implode( ' ', (array) $classes ) ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr( strip_tags( $input_label ) ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"

			<?php
			if ( ! empty( $custom_attributes ) && is_array( $custom_attributes ) ) {
				foreach ( $custom_attributes as $key => $value ) {
					printf( '%s="%s" ', esc_attr( $key ), esc_attr( $value ) );
				}
			}
			?>
		/>

		<input type="hidden" name="update-price" value="<?php echo esc_attr( $updating_cart_key ); ?>" />
		<input type="hidden" name="_nypnonce" value="<?php echo esc_attr( $_nypnonce ); ?>" />	

	<?php do_action( 'wc_nyp_after_price_input', $nyp_product, $suffix ); ?>

</div>

		
