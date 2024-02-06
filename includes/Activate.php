<?php
/**
 * Callback for the activation hook.
 *
 * @since 10.4.63
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Activate
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory;

use cnFileSystem;
use cnSchema;

/**
 * Class Activate
 *
 * @package Connections_Directory
 */
final class Activate {

	/**
	 * Callback for the activation hook.
	 *
	 * @10.4.63
	 */
	public static function plugin() {

		require_once CN_PATH . 'includes/class.schema.php';

		// Create the table structure.
		cnSchema::create();

		// Create the required directories and attempt to make them writable.
		cnFileSystem::mkdirWritable( CN_CACHE_PATH );
		cnFileSystem::mkdirWritable( CN_IMAGE_PATH );
		// cnFileSystem::mkdirWritable( CN_CUSTOM_TEMPLATE_PATH );

		// Add a blank index.php file.
		cnFileSystem::mkIndex( CN_IMAGE_PATH );
		// cnFileSystem::mkIndex( CN_CUSTOM_TEMPLATE_PATH );

		// Add an .htaccess file, create it if one doesn't exist, and add the no indexes option.
		// cnFileSystem::noIndexes( CN_IMAGE_PATH ); // Causes some servers to respond w/ 403 when serving images.
		// cnFileSystem::noIndexes( CN_CUSTOM_TEMPLATE_PATH );

		Connections_Directory()->initOptions();

		/*
		 * Add the page rewrite rules.
		 */
		add_filter( 'root_rewrite_rules', array( 'cnRewrite', 'addRootRewriteRules' ) );
		add_filter( 'page_rewrite_rules', array( 'cnRewrite', 'addPageRewriteRules' ) );

		// Flush so they are rebuilt.
		flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules

		// Force the uninstallation option to off when activating.
		update_option( 'connections_uninstall', array( 'maybe_uninstall' => 0 ), 'no' );
	}
}
