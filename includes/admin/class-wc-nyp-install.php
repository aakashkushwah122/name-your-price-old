<?php
/**
 * NYP install class.
 *
 * Updates custom db data.
 *
 * @package  WooCommerce Name Your Price/Admin
 * @since    3.0.0
 * @version  3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The WC_NYP_Install class.
 */
class WC_NYP_Install {

	/**
	 * Current DB Version
	 *
	 * @var string version.
	 * @since 3.0.0
	 */
	private static $db_version = null;

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'3.0.0' => array(
			'wc_nyp_300_update_text_options', // This will run on install, but leaving this as an example.
		),
	);

	/**
	 * Init.
	 *
	 * @since 3.0.0
	 */
	public static function init() {

		// Test for updates needed.
		add_action( 'admin_init', array( __CLASS__, 'check_version' ) );
		add_action( 'admin_init', array( __CLASS__, 'wc_admin_db_update_notice' ) );

		// Action scheduler hook.
		add_action( 'wc_nyp_run_update_callback', array( __CLASS__, 'run_update_callback' ) );
		add_action( 'wc_nyp_update_db_to_current_version', array( __CLASS__, 'update_db_version' ) );

		// Handle any actions from notices.
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
	}

	/**
	 * Check version and run the installer if necessary.
	 */
	public static function check_version() {

		if ( ! defined( 'IFRAME_REQUEST' ) && self::is_new_install() ) {
			self::install();
		}
	}

	/**
	 * Test if we are using WC Admin Notes or classic notices.
	 *
	 * @since  3.5.0
	 */
	private static function is_wc_admin_active() {
		return WC()->is_wc_admin_active() && false !== get_option( 'woocommerce_admin_install_timestamp' );
	}

	/**
	 * Add WC Admin based db update notice.
	 *
	 * @since 3.5.0
	 */
	public static function wc_admin_db_update_notice() {
		if ( self::is_wc_admin_active() ) {
			new WC_NYP_Notes_Run_Db_Update();
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Handle action scheduler.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Run an update callback when triggered by ActionScheduler.
	 *
	 * @param string $update_callback Callback name.
	 *
	 * @since 3.5.0
	 */
	public static function run_update_callback( $update_callback ) {
		include_once WC_Name_Your_Price()->plugin_path() . '/includes/admin/wc-nyp-update-functions.php';

		if ( is_callable( $update_callback ) ) {
			self::run_update_callback_start( $update_callback );
			$result = (bool) call_user_func( $update_callback );
			self::run_update_callback_end( $update_callback, $result );
		}
	}

	/**
	 * Triggered when a callback will run.
	 *
	 * @since 3.5.0
	 */
	protected static function run_update_callback_start() {
		wc_maybe_define_constant( 'WC_NYP_UPDATING', true );
		wc_maybe_define_constant( 'WC_UPDATING', true );
	}

	/**
	 * Triggered when a callback has ran.
	 *
	 * @since 3.5.0
	 * @param string $callback Callback name.
	 * @param bool   $result Return value from callback. Non-false need to run again.
	 */
	protected static function run_update_callback_end( $callback, $result ) {
		if ( $result ) {
			WC()->queue()->add(
				'wc_nyp_run_update_callback',
				array(
					'update_callback' => $callback,
				),
				'wc_nyp_db_updates'
			);
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 *
	 * @since  3.5.0
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['wc_nyp_do_update'] ) ) {

			$action = wc_clean( $_GET['wc_nyp_do_update'] );

			check_admin_referer( 'wc_nyp_do_update', 'wc_nyp_do_update_nonce' );

			if ( is_callable( array( __CLASS__, $action ) ) ) {
				call_user_func( array( __CLASS__, $action ) );
			} else {
				do_action( 'wc_nyp_do_update_' . $action );
			}
		}
	}

	/**
	 * Install.
	 *
	 * We don't really need to install things per-se, but the 3.0 upgrade may need to run
	 * as the DB version was not stored prior to 3.0.
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'wc_nyp_installing' ) ) {
			return;
		}

		// Running for the first time? Set a transient now.
		set_transient( 'wc_nyp_installing', 'yes', MINUTE_IN_SECONDS * 10 );

		self::remove_admin_notices();

		// Because no DB option existed pre-3.0, we need to *maybe* tweak pre-existing option strings.
		wc_nyp_300_update_text_options();

		// Update plugin version - once set, will not call 'install' again.
		self::update_db_version();

		delete_transient( 'wc_nyp_installing' );

	}


	/*
	|--------------------------------------------------------------------------
	| Handle updates.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 3.5.0
	 */
	private static function remove_admin_notices() {
		include_once WC_Name_Your_Price()->plugin_path() . '/includes/admin/class-wc-nyp-admin-notices.php';
		WC_NYP_Admin_Notices::remove_all_notices();
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  3.5.0
	 */
	public static function is_new_install() {
		return 0 === self::get_current_db_version();
	}

	/**
	 * DB update needed?
	 *
	 * @since  3.5.0
	 *
	 * @return boolean
	 */
	public static function needs_db_update() {
		return self::get_current_db_version() && version_compare( self::get_current_db_version(), self::get_latest_update_version(), '<' );
	}

	/**
	 * Get the most recent updated version.
	 *
	 * @since  3.5.0
	 *
	 * @return string
	 */
	public static function get_latest_update_version() {
		$updates         = self::get_db_update_callbacks();
		$update_versions = array_keys( $updates );
		usort( $update_versions, 'version_compare' );
		return end( $update_versions );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  3.5.0
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 *
	 * @since 3.5.0
	 */
	private static function update() {
		$loop = 0;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {

			if ( version_compare( self::get_current_db_version(), $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					WC()->queue()->schedule_single(
						time() + $loop,
						'wc_nyp_run_update_callback',
						array(
							'update_callback' => $update_callback,
						),
						'wc_nyp_db_updates'
					);
					$loop++;
				}
			}
		}

		// After the callbacks finish, update the db version to the current WC version.
		if ( version_compare( self::get_current_db_version(), self::get_latest_update_version(), '<' ) &&
			! WC()->queue()->get_next( 'wc_nyp_update_db_to_current_version' ) ) {
			WC()->queue()->schedule_single(
				time() + $loop,
				'wc_nyp_update_db_to_current_version',
				array(
					'version' => self::get_latest_update_version(),
				),
				'wc_nyp_db_updates'
			);
		}

	}

	/*
	|--------------------------------------------------------------------------
	| Notice action callbacks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Run the updater if triggered.
	 *
	 * @since  3.5.0
	 */
	public static function update_db() {
		self::update();
	}

	/*
	|--------------------------------------------------------------------------
	| Helper methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Gets the NYP db version of the site
	 *
	 * @since 3.0.0
	 * @return float|int
	 */
	public static function get_current_db_version() {
		if ( is_null( self::$db_version ) ) {
			self::$db_version = get_option( 'woocommerce_nyp_db_version', 0 );
		}
		return self::$db_version;

	}

	/**
	 * Update DB version to current.
	 *
	 * @param  string  $version
	 */
	public static function update_db_version( $version = null ) {
		$version = is_null( $version ) ? self::get_latest_update_version() : $version;
		update_option( 'woocommerce_nyp_db_version', $version );
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/


	/**
	 * Update the suggested and min text options
	 *
	 * @since 3.0.0
	 * @deprecated 3.5.0
	 *
	 * @see wc_nyp_300_update_text_options()
	 */
	public static function update_300_options() {
		wc_deprecated_function( 'WC_NYP_Install::update_300_options()', '3.5.0', 'wc_nyp_300_update_text_options()' );
		return wc_nyp_300_update_text_options();

	}

} // End class: do not remove or there will be no more guacamole for you.
return WC_NYP_Install::init();
