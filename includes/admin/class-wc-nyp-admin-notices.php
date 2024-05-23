<?php
/**
 * Admin Notices
 *
 * @package  WooCommerce Name Your Price/Admin
 * @since    2.10.0
 * @version  3.5.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_NYP_Admin_Notices Class.
 *
 * Handle the display of admin notices.
 */
class WC_NYP_Admin_Notices {

	/**
	 * Metabox Notices.
	 *
	 * @deprecated 3.5.0
	 *
	 * @var array
	 */
	public static $meta_box_notices = array();

	/**
	 * Admin Notices.
	 *
	 * @deprecated 3.5.0
	 *
	 * @var array
	 */
	public static $admin_notices = array();

	/**
	 * Maintenance Notices.
	 *
	 * @deprecated 3.5.0
	 *
	 * @var array
	 */
	public static $maintenance_notices = array();


	/**
	 * Constructor.
	 */
	public static function init() {

		// Act upon clicking on a 'dismiss notice' link.
		add_action( 'admin_init', array( __CLASS__, 'hide_notices' ), 30 );

		// Show notices.
		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );

	}

	/**
	 * Hide a notice if the GET variable is set.
	 *
	 * @since 3.5.0
	 */
	public static function hide_notices() {

		if ( isset( $_GET['wc-nyp-hide-notice'] ) && isset( $_GET['_wc_nyp_notice_nonce'] ) ) {

			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wc_nyp_notice_nonce'] ) ), 'wc_nyp_hide_notices' ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'wc_name_your_price' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You do not have permission to dismiss this notice.', 'wc_name_your_price' ) );
			}

			$notice_name = sanitize_text_field( $_GET['wc-nyp-hide-notice'] );

			self::hide_notice( $notice_name );
		}
	}

	/**
	 * Hide a single Note notice.
	 *
	 * Used by the Notes classs
	 *
	 * @since 3.5.0
	 *
	 * @param string $name Notice name.
	 */
	private static function hide_notice( $name ) {
		do_action( 'wc_nyp_hide_' . $name . '_notice' );
	}

	/*
	|--------------------------------------------------------------------------
	| Legacy.
	|--------------------------------------------------------------------------
	*/


	/**
	 * Add a notice/error.
	 *
	 * @param  string $text
	 * @param  mixed  $args
	 * @param  bool   $save_notice
	 */
	public static function add_notice( $text, $args, $save_notice = false ) {

		if ( is_array( $args ) ) {
			$type          = $args['type'];
			$dismiss_class = isset( $args['dismiss_class'] ) ? $args['dismiss_class'] : false;
		} else {
			$type          = $args;
			$dismiss_class = false;
		}

		$notice = array(
			'type'          => $type,
			'content'       => $text,
			'dismiss_class' => $dismiss_class,
		);

		self::$admin_notices[] = $notice;

	}

	/**
	 * Show any error messages.
	 */
	public static function output_notices() {

		$notices = self::$admin_notices;

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				echo '<div class="wc-nyp-notice notice-' . esc_attr( $notice['type'] ) . ' notice">';
					echo '<p>' . wp_kses_post( $notice['content'] ) . '</p>';
				echo '</div>';
			}

			// Clear.
			delete_option( 'wc_nyp_meta_box_notices' );
		}
	}

	/**
	 * Remove all notices.
	 *
	 * @since 3.5.1
	 */
	public static function remove_all_notices() {
		self::$admin_notices = array();
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated.
	|--------------------------------------------------------------------------
	*/


	/**
	 * Save errors to an option.
	 *
	 * @deprecated 3.5.0
	 */
	public static function save_notices() {
		wc_deprecated_function( 'WC_NYP_Admin_Notices::save_notices()', '3.5.0', 'Metabox and Maintenance notices are no longer saved. Use WC Admin Notes.' );
		update_option( 'wc_nyp_meta_box_notices', self::$meta_box_notices );
		update_option( 'wc_nyp_maintenance_notices', self::$maintenance_notices );
	}


	/**
	 * Show maintenance notices.
	 *
	 * @deprecated 3.5.0
	 */
	public static function hook_maintenance_notices() {

		wc_deprecated_function( 'WC_NYP_Admin_Notices::hook_maintenance_notices()', '3.5.0', 'Maintenance notices are no longer handled in Name Your Price. Use WC Admin Notes.' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		foreach ( self::$maintenance_notice_types as $type => $callback ) {
			if ( in_array( $type, self::$maintenance_notices ) ) {
				call_user_func( array( __CLASS__, $callback ) );
			}
		}
	}

	/**
	 * Add a maintenance notice to be displayed.
	 *
	 * @deprecated 3.5.0
	 */
	public static function add_maintenance_notice( $notice_name ) {
		wc_deprecated_function( 'WC_NYP_Admin_Notices::hook_maintenance_notices()', '3.5.0', 'Maintenance notices are no longer handled in Name Your Price. Use WC Admin Notes.' );
		self::$maintenance_notices = array_unique( array_merge( self::$maintenance_notices, array( $notice_name ) ) );
	}

	/**
	 * Remove a maintenance notice.
	 *
	 * @deprecated 3.5.0
	 */
	public static function remove_maintenance_notice( $notice_name ) {
		wc_deprecated_function( 'WC_NYP_Admin_Notices::hook_maintenance_notices()', '3.5.0', 'Maintenance notices are no longer handled in Name Your Price. Use WC Admin Notes.' );
		self::$maintenance_notices = array_diff( self::$maintenance_notices, array( $notice_name ) );
	}

	/**
	 * Add 'updating' maintenance notice.
	 *
	 * @deprecated 3.5.0
	 */
	public static function updating_notice() {

		wc_deprecated_function( 'WC_NYP_Admin_Notices::updating_notice()', '3.5.0', 'Maintenance notices are no longer handled in Name Your Price. Use WC Admin Notes.' );

		if ( ! class_exists( 'WC_NYP_Install' ) ) {
			return;
		}

		// Show notice to indicate that an update is in progress.
		if ( WC_NYP_Install::is_update_pending() ) {

			$fallback = '';
			// Do not check within 5 seconds after starting.
			if ( gmdate( 'U' ) - get_option( 'wc_nyp_update_init', 0 ) > 5 ) {
				// Check if the update process is running or not - if not, perhaps it failed to start.
				$fallback_url    = esc_url( wp_nonce_url( add_query_arg( 'force_wc_nyp_db_update', true, admin_url() ), 'wc_nyp_force_db_update_nonce', '_wc_nyp_admin_nonce' ) );
				$fallback_prompt = '<a href="' . $fallback_url . '">' . __( 'run the update process manually', 'wc_name_your_price' ) . '</a>';
				// Translators: %s 'run the update process manually' prompt for updating manually.
				$fallback = '<br/><em>' . sprintf( __( '&hellip;Taking a while? You may need to %s.', 'wc_name_your_price' ), $fallback_prompt ) . '</em>';
				$fallback = WC_NYP_Install::is_update_process_running() ? '' : $fallback;
			}
			$notice = '<strong>' . __( 'WooCommerce Name Your Price Data Update', 'wc_name_your_price' ) . '</strong> &#8211; ' . __( 'Your database is being updated in the background.', 'wc_name_your_price' ) . $fallback;
			self::add_notice( $notice, 'info' );

			// Show persistent notice to indicate that the updating process is complete.
		} else {
			$notice = __( 'WooCommerce Name Your Price data update complete.', 'wc_name_your_price' );
			self::add_notice(
				$notice,
				array(
					'type'          => 'info',
					'dismiss_class' => 'updating',
				)
			);
		}
	}

	/**
	 * Act upon clicking on a 'dismiss notice' link.
	 *
	 * @deprecated 3.5.0
	 */
	public static function dismiss_notice_handler() {

		wc_deprecated_function( 'WC_NYP_Admin_Notices::dismiss_notice_handler()', '3.5.0', 'Maintenance notices are no longer handled in Name Your Price. Use WC Admin Notes.' );

		if ( isset( $_GET['dismiss_wc_nyp_notice'] ) && isset( $_GET['_wc_nyp_admin_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_GET['_wc_nyp_admin_nonce'] ), 'wc_nyp_dismiss_notice_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'wc_name_your_price' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You do not have perission to dismiss this notice.', 'wc_name_your_price' ) );
			}

			$notice = sanitize_text_field( wp_unslash( $_GET['dismiss_wc_nyp_notice'] ) );
			self::remove_maintenance_notice( $notice );
		}
	}
}

WC_NYP_Admin_Notices::init();
