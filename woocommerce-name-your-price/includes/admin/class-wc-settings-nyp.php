<?php
/**
 * WooCommerce Name Your Price Settings
 *
 * @package     WC_Name_Your_Price/Admin
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Settings_NYP' ) ) :

	/**
	 * WC_Settings_NYP
	 */
	class WC_Settings_NYP extends WC_Settings_Page {

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
				'woocommerce_' . $this->id . '_settings',
				array(

					array(
						'title' => __( 'Name Your Price Setup', 'wc_name_your_price' ),
						'type'  => 'title',
						'desc'  => __( 'Modify the text strings used by the Name Your Own Price extension.', 'wc_name_your_price' ),
						'id'    => 'woocommerce_nyp_options',
					),

					array(
						'title'    => __( 'Suggested Price Text', 'wc_name_your_price' ),
						'desc'     => __( 'This is the text to display before the suggested price.', 'wc_name_your_price' ),
						'id'       => 'woocommerce_nyp_suggested_text',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'  => __( 'Suggested price:', 'wc_name_your_price' ),
						'desc_tip' => true,
					),

					array(
						'title'    => __( 'Minimum Price Text', 'wc_name_your_price' ),
						'desc'     => __( 'This is the text to display before the minimum accepted price.', 'wc_name_your_price' ),
						'id'       => 'woocommerce_nyp_minimum_text',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'  => __( 'Minimum price:', 'wc_name_your_price' ),
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
						'title'    => __( 'Add to Cart Button Text for Shop', 'wc_name_your_price' ),
						'desc'     => __( 'This is the text that appears on the Add to Cart buttons on the Shop Pages.', 'wc_name_your_price' ),
						'id'       => 'woocommerce_nyp_button_text',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'  => __( 'Set price', 'wc_name_your_price' ),
						'desc_tip' => true,
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

endif;

return new WC_Settings_NYP();
