<?php
/**
 * @package   Connections Business Directory
 * @category  Core
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      https://connections-pro.com
 * @copyright 2018 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Directory
 * Plugin URI:        https://connections-pro.com/
 * Description:       A business directory and address book manager.
 * Version:           8.28.3
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include( dirname( __FILE__ ) . '/includes/class.requirements-check.php' );
include( dirname( __FILE__ ) . '/includes/class.text-domain.php' );

/**
 * NOTE: Priority set at -1 to allow extensions to use the `connections` text domain. Since extensions are
 *       generally loaded on the `plugins_loaded` action hook, any strings with the `connections` text
 *       domain will be merged into it. The purpose is to allow the extensions to use strings known to
 *       in the core plugin to reuse those strings and benefit if they are already translated.
 */
cnText_Domain::register( 'connections', plugin_basename( __FILE__ ), -1 );

$check = new cnRequirements_Check(
	array(
		'name'     => 'Connections Business Directory',
		'basename' => plugin_basename( __FILE__ ),
		'file'     => __FILE__,
		'requirements' => array(
			'php' => array(
				'min' => '5.4', //5.4
				'max' => '7.1', //7.1
			),
			'wp'  => array(
				'min' => '4.5.3', //4.5.3
				'max' => '4.9',
			),
		),
	)
);

if ( $check->passes() ) {

	include( dirname( __FILE__ ) . '/includes/class.connections-directory.php' );

	// Start Connections.
	if ( class_exists( 'Connections_Directory' ) ) {

		Connections_Directory::instance( __FILE__ );

		do_action( 'cn_loaded' );
	}
}
