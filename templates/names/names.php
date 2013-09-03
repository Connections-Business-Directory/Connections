<?php

/**
 * Names Template.
 *
 * @package     Connections
 * @subpackage  Template : Names
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Names_Template' ) ) {

	class CN_Names_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Names_Template',
				'name'        => 'Names',
				'slug'        => 'names',
				'type'        => 'all',
				'version'     => '1.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'A simple responsive template which outputs a list of every name within the directory in a column format if the browser supports it. This template is not recommended for very large directories.',
				'custom'      => FALSE,
				'path'        => plugin_dir_path( __FILE__ ),
				'parts'       => array( 'css' => 'style.css' ),
				);

			cnTemplateFactory::register( $atts );
		}

		public function __construct( $template ) {

			$this->template = $template;

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( $this, 'card' ) ) );
			$template->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $template, 'printCSS' ) ) );
		}

		public function card( $entry ) {

			$entry->getNameBlock( array( 'link' => TRUE ) );
		}

	}

	add_action( 'cn_register_template', array( 'CN_Names_Template', 'register' ) );
}