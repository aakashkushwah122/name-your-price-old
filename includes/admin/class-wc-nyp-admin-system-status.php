<?php
/**
 * Name Your Price System Status Class
 *
 * Adds additional information to the WooCommerce System Status.
 *
 * @class    WC_NYP_Admin_System_Status
 * @package  WooCommerce Name Your Price/Admin
 * @since    3.0.0
 * @version  3.5.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC_NYP_Admin_System_Status class.
 */
class WC_NYP_Admin_System_Status {

	/**
	 * Attach callbacks
	 */
	public static function init() {
		// Template override scan path.
		add_filter( 'woocommerce_template_overrides_scan_paths', array( __CLASS__, 'template_scan_path' ) );

		// Show outdated templates in the system status.
		add_action( 'woocommerce_system_status_report', array( __CLASS__, 'render_system_status_items' ) );
	}

	/**
	 * Support scanning for template overrides in extension.
	 *
	 * @param  array $paths
	 * @return array
	 */
	public static function template_scan_path( $paths ) {
		$paths['WooCommerce Name Your Price'] = WC_Name_Your_Price()->plugin_path() . '/templates/';
		return $paths;
	}

	/**
	 * Add NYP debug data in the system status.
	 *
	 * @since  3.0.0
	 */
	public static function render_system_status_items() {

		$debug_data = array(
			'version'    => array(
				'name' => _x( 'Version', 'label for the system staus page', 'wc_name_your_price' ),
				'note' => get_option( 'wc_mix_and_match_version', null ),
			),
			'db_version' => array(
				'name' => _x( 'Database Version', 'label for the system staus page', 'wc_name_your_price' ),
				'note' => get_option( 'wc_mix_and_match_db_version', null ),
			),
		);

		$theme_overrides = self::get_template_overrides();

		$debug_data['nyp_theme_overrides'] = array(
			'name'      => _x( 'Template Overrides', 'label for the system status page', 'wc_name_your_price' ),
			'mark'      => '',
			'mark_icon' => $theme_overrides['has_outdated_templates'] ? 'warning' : 'yes',
			'data'      => $theme_overrides,
		);

		if ( $theme_overrides['has_outdated_templates'] ) {
			$debug_data['nyp_outdated_templates'] = array(
				'name'      => _x( 'Outdated Templates', 'label for the system status page', 'wc_name_your_price' ),
				'mark'      => 'error',
				'mark_icon' => 'warning',
				'note'      => '<a href="' . esc_url( WC_Name_Your_Price()->get_resource_url( 'outdated-templates' ) ) . '" target="_blank">' . __( 'Learn how to update', 'wc_name_your_price' ) . '</a>',
			);
		}

		include 'views/html-admin-page-status-report.php';
	}

	/**
	 * Determine which of our files have been overridden by the theme.
	 *
	 * @return array
	 */
	private static function get_template_overrides() {

		$template_path    = WC_Name_Your_Price()->plugin_path() . '/templates/';
		$wc_template_path = trailingslashit( wc()->template_path() );
		$theme_root       = trailingslashit( get_theme_root() );
		$overridden       = array();
		$outdated         = false;
		$templates        = WC_Admin_Status::scan_template_files( $template_path );

		foreach ( $templates as $file ) {
			$theme_file  = false;
			$is_outdated = false;
			$locations   = array(
				get_stylesheet_directory() . "/{$file}",
				get_stylesheet_directory() . "/{$wc_template_path}{$file}",
				get_template_directory() . "/{$file}",
				get_template_directory() . "/{$wc_template_path}{$file}",
			);

			foreach ( $locations as $location ) {
				if ( is_readable( $location ) ) {
					$theme_file = $location;
					break;
				}
			}

			if ( ! empty( $theme_file ) ) {
				$core_version  = WC_Admin_Status::get_file_version( $template_path . $file );
				$theme_version = WC_Admin_Status::get_file_version( $theme_file );
				if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
					$outdated    = true;
					$is_outdated = true;
				}
				$overridden[] = array(
					'file'         => str_replace( $theme_root, '', $theme_file ),
					'version'      => $theme_version,
					'core_version' => $core_version,
					'is_outdated'  => $is_outdated,
				);
			}
		}

		return array(
			'has_outdated_templates' => $outdated,
			'overridden_templates'   => $overridden,
		);
	}

}
WC_NYP_Admin_System_Status::init();
