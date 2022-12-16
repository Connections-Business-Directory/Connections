<?php
/**
 * @package   Connections Business Directory
 * @category  Core
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      https://connections-pro.com
 * @copyright 2022 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Directory
 * Plugin URI:        https://connections-pro.com/
 * Description:       A business directory and address book manager.
 * Version:           10.4.35
 * Requires at least: 5.6
 * Requires PHP:      7.0
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require dirname( __FILE__ ) . '/includes/class.requirements-check.php';
require dirname( __FILE__ ) . '/includes/class.text-domain.php';

/**
 * NOTE: Priority set at -1 to allow extensions to use the `connections` text domain. Since extensions are
 *       generally loaded on the `plugins_loaded` action hook, any strings with the `connections` text
 *       domain will be merged into it. The purpose is to allow the extensions to use strings known to
 *       in the core plugin to reuse those strings and benefit if they are already translated.
 *
 * @since 8.35 Set load priority at 1 to support WP Globus.
 */
cnText_Domain::register( 'connections', plugin_basename( __FILE__ ), 1 );

$check = new cnRequirements_Check(
	array(
		'name'         => 'Connections Business Directory',
		'basename'     => plugin_basename( __FILE__ ),
		'file'         => __FILE__,
		'requirements' => array(
			'php' => array(
				'min' => '7.0', // 5.6.20 -- The minimum PHP version that WordPress 5.2 requires.
				'max' => '8.1', // 7.4
			),
			'wp'  => array(
				'min' => '5.6', // 4.7.12
				'max' => '6.1.1',
			),
		),
	)
);

if ( $check->passes() ) {

	include dirname( __FILE__ ) . '/includes/class.connections-directory.php';

	// Start Connections.
	if ( class_exists( 'Connections_Directory' ) ) {

		Connections_Directory::instance( __FILE__ );

		do_action( 'cn_loaded' );
	}
}
