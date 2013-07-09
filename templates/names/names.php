<?php

/**
 * Template functions.
 *
 * @package     Connections
 * @subpackage  Template Functions
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'cnNames' ) ) {

	class cnNames {

		public static function init() {

			$atts = array(
				'class'       => 'CN_Names',
				'name'        => 'Names',
				'type'        => 'all',
				'version'     => '1.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'A simple responsive template which outputs a list of every name within the directory in a column format if the browser supports it. This template is not recommended for very large directories.',
				'path'        => plugin_dir_path( __FILE__ ),
				'parts'       => array( 'css' => 'style.css' )
				);

			cnTemplateFactory::register( $atts );
		}

	}

	class CN_Names {

		public function __construct( $template ) {

			$this->template = $template;

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( $this, 'card' ) ) );
			$template->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $template, 'printCSS' ) ) );
		}

		public function card( $entry ) {
			$entry->getNameBlock( array( 'link' => TRUE ) );
		}

	}

}