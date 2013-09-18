<?php

/**
 * Birthday Light Template.
 *
 * @package     Connections
 * @subpackage  Template : Birthday Light
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Birthday_Light_Template' ) ) {

	class CN_Birthday_Light_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Birthday_Light_Template',
				'name'        => 'Birthday Light',
				'slug'        => 'birthday-light',
				'type'        => 'birthday',
				'version'     => '2.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Default birthday template with a white background in a table like format.',
				'custom'      => FALSE,
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => 'thumbnail.png',
				'parts'       => array( 'css' => 'styles.css' ),
				);

			cnTemplateFactory::register( $atts );
		}

		public function __construct( $template ) {

			$this->template = $template;

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( $this, 'card' ) ) );
			$template->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $template, 'printCSS' ) ) );
		}

		public function card( $entry, $content, $template, $atts, $connections, $vCard ) {

			?>

			<span class="cn-entry-name" style=""><?php echo $entry->name; ?></span> <span class="cn-upcoming-date"><?php echo $entry->getUpcoming( $atts['list_type'], $atts['date_format'] ); ?></span>

			<?php
		}

	}

	add_action( 'cn_register_template', array( 'CN_Birthday_Light_Template', 'register' ) );
}