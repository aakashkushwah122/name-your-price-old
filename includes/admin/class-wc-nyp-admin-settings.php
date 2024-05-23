<?php
/**
 * WooCommerce Name Your Price Settings
 *
 * @package     WC_Name_Your_Price/Admin
 * @since       2.0.0
 * @version     3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Settings for API.
 */
if ( class_exists( 'WC_NYP_Admin_Settings', false ) ) {
	return new WC_NYP_Admin_Settings();
}

/**
 * WC_NYP_Admin_Settings
 */
class WC_NYP_Admin_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'nyp';
		$this->label = __( 'Name Your Price', 'wc_name_your_price' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		return apply_filters(
			'wc_' . $this->id . '_settings',
			array(

				array(
					'title' => __( 'Name Your Price Setup', 'wc_name_your_price' ),
					'type'  => 'title',
					'desc'  => __( 'Modify the text strings used by the Name Your Own Price extension.', 'wc_name_your_price' ),
					'id'    => 'woocommerce_nyp_options',
				),

				array(
					'title'    => __( 'Suggested Price Text', 'wc_name_your_price' ),
					'desc'     => __( 'This is the text to display before the suggested price. You can use the placeholder %PRICE% to display the suggested price.', 'wc_name_your_price' ),
					'id'       => 'woocommerce_nyp_suggested_text',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'default'  => _x( 'Suggested price: %PRICE%', 'Name your price default suggested text', 'wc_name_your_price' ),
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Minimum Price Text', 'wc_name_your_price' ),
					'desc'     => __( 'This is the text to display before the minimum accepted price. You can use the placeholder %PRICE% to display the minimum price.', 'wc_name_your_price' ),
					'id'       => 'woocommerce_nyp_minimum_text',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'default'  => _x( 'Minimum price: %PRICE%', 'Name your price default suggested text', 'wc_name_your_price' ),
					'desc_tip' => true,
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Name Your Price Text', 'wc_name_your_price' ),
					'desc'     => __( 'This is the text that appears above the Name Your Price input field.', 'wc_name_your_price' ),
					'id'       => 'woocommerce_nyp_label_text',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'default'  => __( 'Name your price', 'wc_name_your_price' ),
					'desc_tip' => true,
				),

				array(
					'title'       => __( 'Add to Cart Button Text for Shop', 'wc_name_your_price' ),
					'desc'        => __( 'This is the text that appears on the Add to Cart buttons on the Shop Pages.', 'wc_name_your_price' ),
					'id'          => 'woocommerce_nyp_button_text',
					'type'        => 'text',
					'css'         => 'min-width:300px;',
					'default'     => __( 'Choose price', 'wc_name_your_price' ),
					'placeholder' => __( 'Choose price', 'wc_name_your_price' ),
					'desc_tip'    => true,
				),

				array(
					'title'    => __( 'Add to Cart Button Text for Single Product', 'wc_name_your_price' ),
					'desc'     => __( 'This is the text that appears on the Add to Cart buttons on the Single Product Pages. Leave blank to inherit the default add to cart text.', 'wc_name_your_price' ),
					'id'       => 'woocommerce_nyp_button_text_single',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'default'  => '',
					'desc_tip' => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'woocommerce_nyp_options',
				),

				array(
					'title' => __( 'Name Your Price Style', 'wc_name_your_price' ),
					'type'  => 'title',
					'wc_name_your_price',
					'id'    => 'woocommerce_nyp_style_options',
				),

				array(
					'title'   => __( 'Disable Name Your Price Stylesheet', 'wc_name_your_price' ),
					'id'      => 'woocommerce_nyp_disable_css',
					'type'    => 'checkbox',
					'default' => 'no',
				),

				array(
					'type' => 'sectionend',
					'id'   => 'woocommerce_nyp_style_options',
				),

			)
		); // End pages settings.
	}
}
return new WC_NYP_Admin_Settings();
