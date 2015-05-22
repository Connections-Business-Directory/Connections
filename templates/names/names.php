<?php
/**
 * @package    Connections
 * @subpackage Template : Names
 * @author     Steven A. Zahm
 * @since      0.7.9
 * @license    GPL-2.0+
 * @link       http://connections-pro.com
 * @copyright  2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Names - Template
 * Plugin URI:        http://connections-pro.com
 * Description:       A simple responsive template which outputs a list of every name within the directory in a column format if the browser supports it. This template is not recommended for very large directories.
 * Version:           1.0.1
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Names_Template' ) ) {

	/**
	 * Class CN_Names_Template
	 */
	class CN_Names_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Names_Template',
				'name'        => 'Names',
				'slug'        => 'names',
				'type'        => 'all',
				'version'     => '1.0.1',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'A simple responsive template which outputs a list of every name within the directory in a column format if the browser supports it. This template is not recommended for very large directories.',
				'custom'      => FALSE,
				'path'        => plugin_dir_path( __FILE__ ),
				'parts'       => array( 'css' => 'style.css' ),
				);

			cnTemplateFactory::register( $atts );
		}

		/**
		 * @access public
		 * @since  unknown
		 *
		 * @param cnTemplate $template
		 */
		public function __construct( $template ) {

			$this->template = $template;

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( __CLASS__, 'card' ) ) );
			$template->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $template, 'printCSS' ) ) );
		}

		/**
		 * @access public
		 * @since  unknown
		 *
		 * @param cnOutput $entry
		 */
		public static function card( $entry ) {

			$entry->getNameBlock( array( 'link' => TRUE ) );
		}

	}

	// Register the template.
	add_action( 'cn_register_template', array( 'CN_Names_Template', 'register' ) );
}
