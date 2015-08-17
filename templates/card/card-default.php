<?php
/**
 * @package    Connections
 * @subpackage Template : Card
 * @author     Steven A. Zahm
 * @since      0.7.9
 * @license    GPL-2.0+
 * @link       http://connections-pro.com
 * @copyright  2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Card - Template
 * Plugin URI:        http://connections-pro.com
 * Description:       The default template.
 * Version:           3.0
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Card_Template' ) ) {

	/**
	 * Class CN_Card_Template
	 */
	class CN_Card_Template {

		/**
		 * @var string
		 */
		const SLUG = 'card';

		public static function register() {

			$atts = array(
				'class'       => 'CN_Card_Template',
				'name'        => 'Default Entry Card',
				'slug'        => self::SLUG,
				'type'        => 'all',
				'version'     => '3.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'This is the default template.',
				'custom'      => FALSE,
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => 'thumbnail.png',
				'parts'       => array(),
				'supports'    => array(
					'customizer' => array(
						'view' => array(
							'card'   => array(
								'display' => array(
									'title',
									'org',
									'dept',
									'contact_name',
									'family',
									'addresses',
									'phone_numbers',
									'email',
									'im',
									'social_media',
									'links',
									'dates',
									'bio',
									'notes',
									'categories',
									'last_updated',
									'return_to_top',
								),
								'image' => array(
									'type',
									'width',
									'height',
									'crop_mode',
									'fallback',
								),
								'advanced' => array(
									'name_format',
									'contact_name_format',
									'address_format',
									'address_types',
									'email_format',
									'email_types',
									'phone_format',
									'phone_types',
									'link_format',
									'link_types',
									'date_format',
									'date_types'
								),
							),
							'single' => array(
								'display' => array(
									'title',
									'org',
									'dept',
									'contact_name',
									'family',
									'addresses',
									'phone_numbers',
									'email',
									'im',
									'social_media',
									'links',
									'dates',
									'bio',
									'notes',
									'categories',
									'last_updated',
								),
								'image' => array(
									'type',
									'width',
									'height',
									'crop_mode',
									'fallback',
								),
								'advanced' => array(
									'name_format',
									'contact_name_format',
									'address_format',
									'address_types',
									'email_format',
									'email_types',
									'phone_format',
									'phone_types',
									'link_format',
									'link_types',
									'date_format',
									'date_types'
								),
							),
						),
					),
					'single',
				),
			);

			cnTemplateFactory::register( $atts );

			add_filter( 'cn_register_settings_fields', array( __CLASS__, 'registerSettingsDefaults' ) );
		}

		/**
		 * @param cnTemplate $template
		 */
		public function __construct( $template ) {

			$this->template = $template;

			add_filter( 'cn_list_atts-' . $template->getSlug() , array( __CLASS__, 'initOptions') );
		}

		/**
		 * Save the template settings defaults using @see cnSettingsAPI::registerFields().
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		public static function registerSettingsDefaults( $fields ) {

			$fields[] = array(
				'plugin_id' => 'connections_template',
				'section'   => self::SLUG,
				'id'        => 'card',
				'type'      => 'customizer',
				'default'   => array(
					'show_title'            => TRUE,
					'show_org'              => TRUE,
					'show_dept'             => TRUE,
					'show_contact_name'     => TRUE,
					'show_family'           => TRUE,
					'show_addresses'        => TRUE,
					'show_phone_numbers'    => TRUE,
					'show_email'            => TRUE,
					'show_im'               => TRUE,
					'show_social_media'     => TRUE,
					'show_links'            => TRUE,
					'show_dates'            => TRUE,
					'show_bio'              => FALSE,
					'show_notes'            => FALSE,
					'show_categories'       => TRUE,
					'show_last_updated'     => TRUE,
					'show_return_to_top'    => TRUE,
					'image_type'            => 'photo',
					'image_width'           => NULL,
					'image_height'          => NULL,
					'image_crop_mode'       => '1',
					'image_fallback'        => FALSE,
					'image_fallback_string' => __( 'No Image Available', 'connections' ),
					'name_format'           => '',
					'contact_name_format'   => '',
					'address_format'        => '',
					'address_types'         => '',
					'phone_format'          => '',
					'phone_types'           => '',
					'email_format'          => '',
					'email_types'           => '',
					'date_format'           => '',
					'date_types'            => '',
					'link_format'           => '',
					'link_types'            => '',
					'border_width'          => 1,
					'border_color'          => '#E3E3E3',
					'border_radius'         => 4,
				)
			);

			$fields[] = array(
				'plugin_id' => 'connections_template',
				'section'   => self::SLUG,
				'id'        => 'single',
				'type'      => 'customizer',
				'default'   => array(
					'show_title'         => TRUE,
					'show_org'           => TRUE,
					'show_dept'          => TRUE,
					'show_contact_name'  => TRUE,
					'show_family'        => TRUE,
					'show_addresses'     => TRUE,
					'show_phone_numbers' => TRUE,
					'show_email'         => TRUE,
					'show_im'            => TRUE,
					'show_social_media'  => TRUE,
					'show_links'         => TRUE,
					'show_dates'         => TRUE,
					'show_bio'           => TRUE,
					'show_notes'         => FALSE,
					'show_categories'    => TRUE,
					'show_last_updated'  => TRUE,
					'show_return_to_top' => FALSE,
					'image_type'         => 'photo',
					'image_width'        => NULL,
					'image_height'       => NULL,
					'image_crop_mode'    => '1',
					'image_fallback'     => FALSE,
					'image_fallback_string' => __( 'No Image Available', 'connections' ),
					'name_format'           => '',
					'contact_name_format'   => '',
					'address_format'        => '',
					'address_types'         => '',
					'phone_format'          => '',
					'phone_types'           => '',
					'email_format'          => '',
					'email_types'           => '',
					'date_format'           => '',
					'date_types'            => '',
					'link_format'           => '',
					'link_types'            => '',
				)
			);

			return $fields;
		}

		/**
		 * Initiate the template options using the option values.
		 *
		 * @access private
		 * @since  3.0
		 *
		 * @param  array $atts The shortcode $atts array.
		 *
		 * @return array
		 */
		public static function initOptions( $atts ) {

			if ( get_query_var( 'cn-entry-slug' ) ) {

				/**
				 * @var cnOutput $entry
				 * @var array $option
				 */
				$options = cnSettingsAPI::get( 'connections_template', 'card', 'single' );

			} else {

				/**
				 * @var cnOutput $entry
				 * @var array $option
				 */
				$options = cnSettingsAPI::get( 'connections_template', 'card', 'card' );
				$style   = array(
					'background-color' => '#FFF',
					'border'           => $options['border_width'] . 'px solid ' . $options['border_color'],
					'border-radius'    => $options['border_radius'] . 'px',
					'color'            => '#000',
					'margin'           => '8px 0',
					'padding'          => '10px',
					'position'         => 'relative',
				);

				if ( is_array( $style ) ) {

					$atts = wp_parse_args( $style, $atts );
				}
			}

			if ( is_array( $options ) ) {

				$atts = wp_parse_args( $options, $atts );
			}

			return $atts;
		}

		/**
		 * Include the Template Customizer support file if the template is being customized.
		 *
		 * @access private
		 * @since  3.0
		 */
		public static function includeCustomizer() {

			if ( isset( $_REQUEST['cn-template'] ) && self::SLUG == $_REQUEST['cn-template'] ) {

				require_once CN_PATH . 'templates/card/class.customizer.php';
			}
		}
	}

	// Register the template.
	add_action( 'cn_register_template', array( 'CN_Card_Template', 'register' ) );

	// Include the Customizer configuration file for the template.
	add_action( 'cn_template_customizer_include', array( 'CN_Card_Template', 'includeCustomizer' ) );
}
