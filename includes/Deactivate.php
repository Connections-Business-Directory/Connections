<?php
/**
 * Callback for the deactivation hook.
 *
 * @since 10.4.63
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Deactivate
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory;

use cnRole;
use cnSettingsAPI;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_format;

/**
 * Class Deactivate
 */
final class Deactivate {

	/**
	 * Callback for the deactivation hook.
	 *
	 * Flush rewrite rules and maybe save the uninstallation data.
	 *
	 * @since 10.4.63
	 */
	public static function plugin() {

		/*
		 * Since we're adding the rewrite rules using a filter, make sure to remove the filter
		 * before flushing, otherwise the rules will not be removed.
		 */
		remove_filter( 'root_rewrite_rules', array( 'cnRewrite', 'addRootRewriteRules' ) );
		remove_filter( 'page_rewrite_rules', array( 'cnRewrite', 'addPageRewriteRules' ) );

		// Flush so they are rebuilt.
		flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules

		self::maybeSaveUninstallData();
	}

	/**
	 * Maybe save the uninstallation data that will be used when deleting the plugin.
	 *
	 * @since 10.4.63
	 */
	protected static function maybeSaveUninstallData() {

		$uninstall      = get_option( 'connections_uninstall', array() );
		$maybeUninstall = _array::get( $uninstall, 'maybe_uninstall', false );

		_format::toBoolean( $maybeUninstall );

		if ( $maybeUninstall ) {

			$capabilities = cnRole::capabilities();
			$options      = array( 'connections_options', 'connections_uninstall' );
			$settings     = cnSettingsAPI::getAll();
			$tables       = array(
				CN_ENTRY_TABLE,
				CN_ENTRY_ADDRESS_TABLE,
				CN_ENTRY_PHONE_TABLE,
				CN_ENTRY_EMAIL_TABLE,
				CN_ENTRY_MESSENGER_TABLE,
				CN_ENTRY_SOCIAL_TABLE,
				CN_ENTRY_LINK_TABLE,
				CN_ENTRY_DATE_TABLE,
				CN_ENTRY_TABLE_META,
				CN_TERMS_TABLE,
				CN_TERM_TAXONOMY_TABLE,
				CN_TERM_RELATIONSHIP_TABLE,
				CN_TERM_META_TABLE,
			);

			foreach ( $settings as $setting ) {

				foreach ( $setting as $optionName => $value ) {

					$options[] = $optionName;
				}
			}

			_array::set( $uninstall, 'capabilities', array_keys( $capabilities ) );
			_array::set( $uninstall, 'options', $options );
			_array::set( $uninstall, 'tables', $tables );

			update_option( 'connections_uninstall', $uninstall, 'no' );
		}
	}
}
