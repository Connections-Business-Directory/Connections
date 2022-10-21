<?php
/**
 * Add plugin information to the plugin row on the Plugins admin page.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Hook\Filter\Admin
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Filter\Admin;

/**
 * Class Plugin_Row
 *
 * @package Connections_Directory\Hook\Filter\Admin
 */
final class Plugin_Row {

	/**
	 * Callback for the `load-plugins.php` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.31
	 */
	public static function register() {

		// Add Settings link to the plugin actions.
		add_filter( 'plugin_action_links_' . CN_BASE_NAME, array( __CLASS__, 'actionLinks' ) );

		// Add FAQ, Support and Donate links.
		add_filter( 'plugin_row_meta', array( __CLASS__, 'metaLinks' ), 10, 2 );

		// Add Changelog table row in the manage Plugins admin page.
		add_action( 'in_plugin_update_message-' . CN_BASE_NAME, array( __CLASS__, 'displayUpgradeNotice' ), 20, 2 );
	}

	/**
	 * Callback for the `plugin_action_links_{$plugin_file}` filter.
	 *
	 * Add the Settings link to the plugin admin page.
	 *
	 * @see WP_Plugins_List_Table::single_row()
	 *
	 * @internal
	 * @since unknown
	 *
	 * @param array $links An array of plugin action links.
	 *
	 * @return array
	 */
	public static function actionLinks( $links ) {

		$new_links = array();

		$new_links[] = '<a href="admin.php?page=connections_settings">' . __( 'Settings', 'connections' ) . '</a>';

		return array_merge( $new_links, $links );
	}

	/**
	 * Callback for the `plugin_row_meta` filter.
	 *
	 * Add the links for premium templates, extensions and support info.
	 *
	 * @internal
	 * @since unknown
	 *
	 * @param array  $links An array of the plugin's metadata, including
	 *                      the version, author, author URI, and plugin URI.
	 * @param string $file  Path to the plugin file relative to the plugin directory.
	 *
	 * @return array
	 */
	public static function metaLinks( $links, $file ) {

		if ( CN_BASE_NAME === $file ) {

			$permalink = apply_filters(
				'Connections_Directory/Admin/Menu/Submenu/Support/Permalink',
				'https://wordpress.org/support/plugin/connections/'
			);

			$title = apply_filters(
				'Connections_Directory/Admin/Menu/Submenu/Support/Title',
				esc_html__( 'Support', 'connections' )
			);

			$title     = esc_html( $title );
			$permalink = esc_url( $permalink );

			$links[] = '<a href="https://connections-pro.com/?page_id=29" target="_blank">' . esc_html__( 'Extensions', 'connections' ) . '</a>';
			$links[] = '<a href="https://connections-pro.com/?page_id=419" target="_blank">' . esc_html__( 'Templates', 'connections' ) . '</a>';
			$links[] = '<a href="https://connections-pro.com/documentation/contents/" target="_blank">' . esc_html__( 'Documentation', 'connections' ) . '</a>';
			$links[] = '<a href="' . $permalink . '" target="_blank">' . $title . '</a>';
		}

		return $links;
	}

	/**
	 * Callback for the `in_plugin_update_message_{$file}` action.
	 *
	 * Display the upgrade notice and changelog on the Manage Plugin admin screen.
	 *
	 * Inspired by Changelogger. Code based on W3 Total Cache.
	 *
	 * @see wp_plugin_update_row()
	 *
	 * @internal
	 * @since unknown
	 *
	 * @param array  $plugin_data An Array of the plugin metadata.
	 * @param object $r           An array of metadata about the available plugin update.
	 */
	public static function displayUpgradeNotice( $plugin_data, $r ) {

		echo '</p>'; // Required to close the open <p> tag that exists when this action is run.

		// Show the upgrade notice if it exists.
		if ( isset( $r->upgrade_notice ) ) {
			/* translators: Plugin version number. */
			echo '<div class="cn-update-message-p-clear-before"><strong>' . sprintf( esc_html__( 'Upgrade notice for version: %s', 'connections' ), esc_html( $r->new_version ) ) . '</strong></div>';
			echo '<ul><li>' . esc_html( wp_strip_all_tags( $r->upgrade_notice ) ) . '</li></ul>';
		}

		// Grab the plugin info using the WordPress.org Plugins API.
		// First, check to see if the function exists, if it doesn't, include the file which contains it.
		if ( ! function_exists( 'plugins_api' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		$plugin = plugins_api(
			'plugin_information',
			array(
				'slug'   => 'connections',
				'fields' => array(
					'tested'       => true,
					'requires'     => false,
					'rating'       => false,
					'downloaded'   => false,
					'downloadlink' => false,
					'last_updated' => false,
					'homepage'     => false,
					'tags'         => false,
					'sections'     => true,
				),
			)
		);

		// Create the regex that'll parse the changelog for the latest version.
		$regex = '~<h([1-6])>' . preg_quote( $r->new_version ) . '.+?</h\1>(.+?)<h[1-6]>~is';

		preg_match( $regex, $plugin->sections['changelog'], $matches );

		// If no changelog is found for the current version, return.
		if ( isset( $matches[2] ) && ! empty( $matches[2] ) ) {

			preg_match_all( '~<li>(.+?)</li>~', $matches[2], $matches );

			// Make sure the change items were found and not empty before proceeding.
			if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {

				$ul = false;

				// Finally, lets render the changelog list.
				foreach ( $matches[1] as $key => $line ) {

					if ( ! $ul ) {

						echo '<div class="cn-update-message-p-clear-before"><strong>' . esc_html__( 'Take a minute to update, here\'s why:', 'connections' ) . '</strong></div>';
						echo '<ul class="cn-changelog">';
						$ul = true;
					}

					echo '<li style="' . ( 0 === $key % 2 ? ' clear: left;' : '' ) . '">' . wp_kses_post( $line ) . '</li>';
				}

				if ( $ul ) {

					echo '</ul><div style="clear: left;"></div>';
				}
			}
		}

		echo '<p class="cn-update-message-p-clear-before">'; // Required to open a </p> tag that exists when this action is run.
	}
}
