<?php
/**
 * Dashboard: Upcoming Widget Template.
 *
 * @package     Connections
 * @subpackage  Template : Dashboard: Recently Modified Widget
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
 */

use Connections_Directory\Utility\_nonce;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CN_Dashboard_Upcoming_Template' ) ) {

	/**
	 * Class CN_Dashboard_Upcoming_Template
	 */
	class CN_Dashboard_Upcoming_Template {

		/**
		 * Instance of cnTemplate.
		 *
		 * @since 10.4.40
		 * @var cnTemplate
		 */
		private $template;

		/**
		 * Register the template.
		 */
		public static function register() {

			$atts = array(
				'class'       => 'CN_Dashboard_Upcoming_Template',
				'name'        => 'Dashboard: Upcoming Widget',
				'slug'        => 'dashboard-upcoming',
				'type'        => 'dashboard',
				'version'     => '2.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Dashboard Widget that displays Upcoming Anniversaries and Birthdays.',
				'custom'      => false,
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => '',
				'parts'       => array( 'css' => 'styles.css' ),
			);

			cnTemplateFactory::register( $atts );
		}

		/**
		 * CN_Dashboard_Upcoming_Template constructor.
		 *
		 * @param cnTemplate $template Instance of the cnTemplate object.
		 */
		public function __construct( $template ) {

			$this->template = $template;

			$template->part(
				array(
					'tag'      => 'card',
					'type'     => 'action',
					'callback' => array(
						__CLASS__,
						'card',
					),
				)
			);

			$template->part(
				array(
					'tag'      => 'css',
					'type'     => 'action',
					'callback' => array(
						$template,
						'printCSS',
					),
				)
			);
		}

		/**
		 * Callback to render the template.
		 *
		 * @param cnEntry_HTML $entry    Current instance of the cnEntry object.
		 * @param cnTemplate   $template Instance of the cnTemplate object.
		 * @param array        $atts     The shortcode attributes array.
		 */
		public static function card( $entry, $template, $atts ) {

			if ( is_admin() ) {

				$editTokenURL = _nonce::url( 'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $entry->getId(), 'entry_edit', $entry->getId() );

				if ( current_user_can( 'connections_edit_entry' ) ) {

					echo '<span class="cn-entry-name"><a class="row-title" title="', esc_attr( 'Edit ' . $entry->getName() ), '" href="', esc_url( $editTokenURL ), '"> ', esc_html( $entry->getName() ) . '</a></span> <span class="cn-upcoming-date">', esc_html( $entry->getUpcoming( $atts['list_type'], $atts['date_format'] ) ), '</span>';

				} else {

					echo '<span class="cn-entry-name">', esc_html( $entry->getName() ), '</span> <span class="cn-upcoming-date">', esc_html( $entry->getUpcoming( $atts['list_type'], $atts['date_format'] ) ), '</span>';
				}

			}
		}
	}

	add_action( 'cn_register_template', array( 'CN_Dashboard_Upcoming_Template', 'register' ) );
}
