<?php
/**
 * Status Report data for NYP.
 *
 * @package  WooCommerce Name Your Price
 * @since    3.3.10
 * @version  3.5.10
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="Name Your Price">
			<h2><?php esc_html_e( 'Name Your Price', 'wc_name_your_price' ); ?></h2>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $debug_data as $section => $data ) {
		// Use mark key if available, otherwise default back to the success key.
		if ( isset( $data['mark'] ) ) {
			$mark = $data['mark'];
		} elseif ( isset( $data['success'] ) && $data['success'] ) {
			$mark = 'yes';
		} elseif ( isset( $data['error'] ) && $data['error'] ) {
			$mark = 'error';
		} else {
			$mark = '';
		}

		// Use mark_icon key if available, otherwise set based on $mark.
		if ( isset( $data['mark_icon'] ) ) {
			$mark_icon = $data['mark_icon'];
		} elseif ( 'yes' === $mark ) {
			$mark_icon = 'yes';
		} else {
			$mark_icon = 'no-alt';
		}

		?>
		<tr>
			<td data-export-label="<?php echo esc_attr( $data['name'] ); ?>"><?php echo esc_html( $data['name'] ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td>
				<?php

				// If this isn't theme overrides, keep it simple.
				if ( 'nyp_theme_overrides' !== $section ) {

					if ( isset( $data['note'] ) ) {
						if ( empty( $mark ) ) {
							echo wp_kses_post( $data['note'] );
						} else {
							?>
							<mark class="<?php echo esc_html( $mark ); ?>">
													<?php
													if ( $mark_icon ) {
														echo '<span class="dashicons dashicons-' . esc_attr( $mark_icon ) . '"></span> ';
													}
													echo wp_kses_post( $data['note'] );
													?>
							</mark>
							<?php
						}
					}
					continue;
				}

				// If we don't have template data, continue early.
				if ( empty( $data['data']['overridden_templates'] ) ) {
					echo '&ndash;';
					continue;
				}

				foreach ( $data['data']['overridden_templates'] as $override ) {
					printf( '<code>%s</code> ', esc_html( $override['file'] ) );
					if ( $override['is_outdated'] ) {
						printf(
							// translators: %1$s is the file version, %2$s is the core version.
							esc_html__( 'version %1$s is out of date. The core version is %2$s', 'wc_name_your_price' ),
							'<strong style="color:red">' . esc_html( $override['version'] ) . '</strong>',
							'<strong>' . esc_html( $override['core_version'] ) . '</strong>'
						);
					}
					echo '<br />';
				}
				?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
