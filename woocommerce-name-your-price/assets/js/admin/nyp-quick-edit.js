/**
 * Script for editing prices in the quick edit.
 *
 * @package WooCommerce Name Your Price/Admin/Scripts
 */

/*global inlineEditPost */
jQuery(
	function( $ ) {
		$( '#the-list' ).on(
			'click',
			'.editinline',
			function() {

				// Get the post ID.
				var post_id = inlineEditPost.getId( this );

				// Find the hidden NYP data.
				var $nyp_inline_data = $( '#nyp_inline_' + post_id );
				var $wc_inline_data  = $( '#woocommerce_inline_' + post_id );

				// Conditional display.
				var product_type = $wc_inline_data.find( '.product_type' ).text();

				// Quit if unsupported product type.
				if ( false === $.inArray( product_type, wc_nyp_quick_edit_params.supported_types ) ) {
					return false;
				}

				// Get nyp status, suggested and minimum price variables, and whether to display for this product type (only simple and subs).
				var nyp                   = $nyp_inline_data.find( '.nyp' ).text();
				var is_sub                = $nyp_inline_data.find( '.is_sub' ).text();
				var show_variable_billing = $nyp_inline_data.find( '.show_variable_billing' ).text();
				var is_variable_billing   = $nyp_inline_data.find( '.is_variable_billing' ).text();
				var suggested_price       = $nyp_inline_data.find( '.suggested_price' ).text();
				var suggested_period      = $nyp_inline_data.find( '.suggested_period' ).text();
				var min_price             = $nyp_inline_data.find( '.min_price' ).text();
				var min_period            = $nyp_inline_data.find( '.min_period' ).text();
				var max_price             = $nyp_inline_data.find( '.max_price' ).text();
				var is_nyp_allowed        = $nyp_inline_data.find( '.is_nyp_allowed' ).text();
				var hide_minimum          = $nyp_inline_data.find( '.is_minimum_hidden' ).text();

				// Set price inputs.
				$( 'input[name="_suggested_price"]', '.inline-edit-row' ).val( suggested_price );
				$( 'input[name="_min_price"]', '.inline-edit-row' ).val( min_price );
				$( 'input[name="_maximum_price"]', '.inline-edit-row' ).val( max_price );
				$( 'select[name="_suggested_billing_period"] option[value="' + suggested_period + '"]', '.inline-edit-row' ).attr( 'selected', 'selected' );
				$( 'select[name="_minimum_billing_period"] option[value="' + min_period + '"]', '.inline-edit-row' ).attr( 'selected', 'selected' );

				// Set remaining inputs.
				if ( 'yes' === hide_minimum ) {
					$( 'input[name="_hide_nyp_minimum"]', '.inline-edit-row' ).attr( 'checked', 'checked' );
				}

				// Remove NYP Conditional fields when not permitted.
				if ( 'no' === is_nyp_allowed ) {
					$( '.inline-edit-row' ).find( '#nyp-fields' ).remove();
				}

				var $variable_sub_fields = $( '.inline-edit-row' ).find( '.show_if_nyp .show_if_subscription' );
				var $period_fields       = $variable_sub_fields.not( '._variable_billing_field' );

				// Remove Subscriptions conditional fields when not relevant.
				if ( 'no' === is_sub || 'no' === show_variable_billing ) {
					$variable_sub_fields.remove();
				}

				// If NYP show suggested and min inputs.
				if ( 'yes' === nyp ) {
					$( 'input[name="_nyp"]', '.inline-edit-row' ).attr( 'checked', 'checked' );
					$( '.show_if_nyp', '.inline-edit-row' ).show();
					$( '.price_fields input' ).attr( 'disabled', true );
				} else {
					$( '.show_if_nyp', '.inline-edit-row' ).hide();
					$( '.price_fields input' ).attr( 'disabled', false );
				}

				// If subscription and supports variable billing periods show period selects.
				if ( 'yes' === is_variable_billing ) {
					$( 'input[name="_variable_billing"]', '.inline-edit-row' ).attr( 'checked', 'checked' );
					$period_fields.show();
				} else {
					$period_fields.hide();
				}

				// Hide minimum checkbox status.
				if ( 'yes' === hide_minimum ) {
					$( 'input[name="_hide_nyp_minimum"]', '.inline-edit-row' ).attr( 'checked', 'checked' );
				}

				return false;

			}
		);

		// Toggle display of suggested and min prices based on NYP checkbox.
		$( '#the-list' ).on(
			'change',
			'.inline-edit-row input[name="_nyp"]',
			function() {

				if ( $( this ).is( ':checked' ) ) {
					$( '.show_if_nyp', '.inline-edit-row' ).show();
					$( '.price_fields input' ).attr( 'disabled', true );
				} else {
					$( '.show_if_nyp', '.inline-edit-row' ).hide();
					$( '.price_fields input' ).attr( 'disabled', false );
				}

			}
		);

		// Toggle display of suggested and min periods based on variable billing checkbox.
		$( '#the-list' ).on(
			'change',
			'.inline-edit-row input[name="_variable_billing"]',
			function() {

				var $variable_sub_fields = $( '.inline-edit-row' ).find( '.show_if_nyp .show_if_subscription' );
				var $period_fields       = $variable_sub_fields.not( '._variable_billing_field' );

				if ( $( this ).is( ':checked' ) ) {
					$period_fields.show();
				} else {
					$period_fields.hide();
				}

			}
		);

	}
);
