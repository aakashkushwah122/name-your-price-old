<?php
/**
 * WooCommerce Mix and Match: Support note.
 *
 * Adds a note to ask users to contact support if they haven't made any mix and match products yet.
 *
 * @since 3.5.0
 *
 * @package WooCommerce Mix and Match/Admin/Notes
 */
defined( 'ABSPATH' ) || exit;

use \Automattic\WooCommerce\Admin\Notes\Note;
use \Automattic\WooCommerce\Admin\Notes\NoteTraits;

/**
 * Support note.
 */
class WC_NYP_Notes_Get_Support {
	/**
	 * Note traits.
	 */
	use NoteTraits;

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'wc-nyp-admin-get-support-note';

	/**
	 * Get the note.
	 *
	 * @return Note
	 */
	public static function get_note() {

		if ( ! WC_Name_Your_Price_Helpers::is_plugin_active_for( 3 * DAY_IN_SECONDS ) || WC_Name_Your_Price_Helpers::is_plugin_active_for( 7 * DAY_IN_SECONDS ) ) {
			return;
		}

		// Show if there is any nyp product.
		$query    = new \WC_Product_Query(
			array(
				'limit'           => 1,
				'return'          => 'ids',
				'status'          => array( 'publish' ),
				'name-your-price' => true,
			)
		);
		$products = $query->get_products();

		if ( 0 !== count( $products ) ) {
			return;
		}

		// If you're updating the following please use sprintf to separate HTML tags.
		// https://github.com/woocommerce/woocommerce-admin/pull/6617#discussion_r596889685.
		$content_lines = array(
			esc_html__( 'It looks like you haven\'t created a Name Your Price product yet. Please let us know if there is anything we can assist you with.', 'wc_name_your_price' ),

		);

		$additional_data = array(
			'role' => 'administrator',
		);

		// Unused for now, but maybe in the future.
		$campaign_args = array(
			'utm_campaign' => 'wc-admin',
			'utm_content'  => 'wc-inbox',
		);

		$support_url = add_query_arg( $campaign_args, esc_url( WC_Name_Your_Price()->get_resource_url( 'ticket-form' ) ) );

		$note = new Note();
		$note->set_title( esc_html__( 'Need help with Name Your Price?', 'wc_name_your_price' ) );
		$note->set_content( implode( '', $content_lines ) );
		$note->set_content_data( (object) $additional_data );
		$note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
		$note->set_name( self::NOTE_NAME );
		$note->set_source( 'woocommerce-name-your-price' );
		$note->add_action( 'wc-nyp-get-support', esc_html__( 'Ask a question', 'wc_name_your_price' ), $support_url );

		return $note;
	}
}
