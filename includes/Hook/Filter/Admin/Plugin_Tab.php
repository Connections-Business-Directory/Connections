<?php
/**
 * Add the Connections plugin group on the Plugins admin page.
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

use WP_Plugin_Install_List_Table;

/**
 * Class Plugin_Tab
 *
 * @package Connections_Directory\Hook\Filter\Admin
 */
final class Plugin_Tab {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.31
	 */
	public static function register() {

		// Add the Connections Tab to the Add Plugins admin page.
		add_filter( 'install_plugins_tabs', array( __CLASS__, 'installTab' ) );

		// Set up the plugins_api() arguments.
		add_filter( 'install_plugins_table_api_args_connections', array( __CLASS__, 'installArgs' ) );
	}

	/**
	 * Callback for the `install_plugins_tabs` filter.
	 *
	 * @see WP_Plugin_Install_List_Table::prepare_items()
	 *
	 * @internal
	 * @since 8.6.8
	 *
	 * @param array $tabs The tabs shown on the Plugin Install screen.
	 *
	 * @return array
	 */
	public static function installTab( $tabs ) {

		$tabs['connections'] = 'Connections';

		return $tabs;
	}

	/**
	 * Callback for the `install_plugins_table_api_args_connections` filter.
	 *
	 * @see WP_Plugin_Install_List_Table::prepare_items()
	 *
	 * @internal
	 * @since 8.6.8
	 *
	 * @param array $args Plugin Install API arguments.
	 *
	 * @return array
	 */
	public static function installArgs( $args ) {

		global $tabs, $tab, $paged, $type, $term;

		$per_page = 30;

		$args = array(
			'page'     => $paged,
			'per_page' => $per_page,
			'fields'   => array(
				'last_updated'    => true,
				'icons'           => true,
				'active_installs' => true,
			),
			// Send the locale and installed plugin slugs to the API, so it can provide context-sensitive results.
			'locale'   => get_user_locale(),
			// 'installed_plugins' => $this->get_installed_plugin_slugs(),
		);

		$args['installed_plugins'] = array( 'connections' );
		$args['author']            = 'shazahm1hotmailcom';
		// $args['search'] = 'Connections Business Directory';

		add_action( 'install_plugins_connections', array( __CLASS__, 'installResults' ), 9, 1 );

		return $args;
	}

	/**
	 * Callback for the `install_plugins_connections` action.
	 *
	 * @see wp-admin/plugin-install.php
	 *
	 * @internal
	 * @since 8.6.8
	 *
	 * @param int $page The current page number of the plugins list table.
	 */
	public static function installResults( $page ) {

		/** @var WP_Plugin_Install_List_Table $wp_list_table */
		global $wp_list_table;

		foreach ( $wp_list_table->items as $key => &$item ) {

			if ( is_array( $item ) ) {

				// Remove the core plugin.
				if ( 'connections' === $item['slug'] ) {

					unset( $wp_list_table->items[ $key ] );
				}

				// Remove any items which do not have Connections in its name.
				if ( false === strpos( $item['name'], 'Connections' ) ) {

					unset( $wp_list_table->items[ $key ] );
				}

			} elseif ( is_object( $item ) ) {

				if ( 'connections' === $item->slug ) {

					unset( $wp_list_table->items[ $key ] );
				}

				if ( false === strpos( $item->name, 'Connections' ) ) {

					unset( $wp_list_table->items[ $key ] );
				}
			}
		}

		// Save the items from the original query.
		$core = $wp_list_table->items;

		// Affiliate URL and preg replace pattern.
		$tslAffiliateURL = 'https://tinyscreenlabs.com/?tslref=connections';
		$pattern         = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";

		// phpcs:disable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar
		//$mam = plugins_api(
		//	'plugin_information',
		//	array(
		//		'slug'              => 'mobile-app-manager-for-connections',
		//		'fields'            => array(
		//			'last_updated'    => TRUE,
		//			'icons'           => TRUE,
		//			'active_installs' => TRUE,
		//		),
		//		'locale'            => get_user_locale(),
		//		'installed_plugins' => array( 'connections' ),
		//	)
		//);
		// phpcs:enable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar

		$offers = plugins_api(
			'plugin_information',
			array(
				'slug'              => 'connections-business-directory-offers',
				'fields'            => array(
					'last_updated'    => true,
					'icons'           => true,
					'active_installs' => true,
				),
				'locale'            => get_user_locale(),
				'installed_plugins' => array( 'connections' ),
			)
		);

		// phpcs:disable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar
		//$tsl = plugins_api(
		//	'query_plugins',
		//	array(
		//		'author'            => 'tinyscreenlabs',
		//		'fields'            => array(
		//			'last_updated'    => TRUE,
		//			'icons'           => TRUE,
		//			'active_installs' => TRUE,
		//		),
		//		'locale'            => get_user_locale(),
		//		'installed_plugins' => array( 'connections' ),
		//	)
		//);

		//if ( ! is_wp_error( $tsl ) ) {
		//
		//	foreach ( $tsl->plugins as $plugin ) {
		//
		//		switch ( $plugin->slug ) {
		//
		//			// Add TSL MAM to the top of the plugin items array.
		//			case 'mobile-app-manager-for-connections':
		//
		//				$wp_list_table->items = cnArray::prepend( $wp_list_table->items, $plugin );
		//				break;
		//
		//			// Add TSL Offers to the bottom of the plugin items array.
		//			case 'connections-business-directory-offers':
		//
		//				$wp_list_table->items[] = $plugin;
		//				break;
		//		}
		//	}
		//}
		// phpcs:enable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar

		?>
		<form id="plugin-filter" method="post">
			<?php
			// $wp_list_table->display();
			$wp_list_table->_pagination_args = array();

			if ( 0 < count( $core ) ) {
				$wp_list_table->items = $core;
				self::installDisplayGroup( 'Free' );
			}

			// phpcs:disable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar
			//if ( ! is_wp_error( $mam ) ) {
			//
			//	// Update the links to TSL to use the affiliate URL.
			//	$mam->homepage = $tslAffiliateURL;
			//	$mam->author = preg_replace( $pattern, $tslAffiliateURL, $mam->author );
			//
			//	$wp_list_table->items = array( $mam );
			//	self::installDisplayGroup( 'Mobile App' );
			//}
			// phpcs:enable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar

			if ( ! is_wp_error( $offers ) ) {

				// Update the links to TSL to use the affiliate URL.
				$offers->homepage = $tslAffiliateURL;
				$offers->author   = preg_replace( $pattern, $tslAffiliateURL, $offers->author );

				$wp_list_table->items = array( $offers );
				self::installDisplayGroup( 'Third Party' );
			}
			?>
		</form>
		<?php

		// Restore original items.
		$wp_list_table->items = $core;
	}

	/**
	 * Display the plugin info cards.
	 *
	 * @access private
	 * @since  8.6.8
	 *
	 * @param string $name The section heading text.
	 */
	private static function installDisplayGroup( $name ) {

		/** @var WP_Plugin_Install_List_Table $wp_list_table */
		global $wp_list_table;

		// Needs an extra wrapping div for nth-child selectors to work.
		?>
		<div class="plugin-group"><h3> <?php echo esc_html( $name ); ?></h3>
			<div class="plugin-items">

				<?php $wp_list_table->display(); ?>
			</div>
		</div>
		<?php
	}
}
