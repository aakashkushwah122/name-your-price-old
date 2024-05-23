<?php
/**
 * WooCommerce Name Your Price - WooCommerce Admin Notices.
 *
 * Adds relevant information via the WooCommerce Inbox.
 *
 * @since   3.5.0
 * @version 3.5.0
 *
 * @package WooCommerce Name Your Price/Admin/Notes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_NYP_Admin_Notes {

	/**
	 * Attach hooks and filters
	 */
	public static function init() {
		add_action( 'wc_admin_daily', array( __CLASS__, 'possibly_add_notes' ), 15 );
	}

	/**
	 * Include the notes to create.
	 */
	public static function possibly_add_notes() {

		// Start adding our notes/messages.
		WC_NYP_Notes_Get_Support::possibly_add_note();
		WC_NYP_Notes_Help_Improve::possibly_add_note();

	}


} // END class

return WC_NYP_Admin_Notes::init();
