<?php
/**
 * @package    Connections
 * @subpackage Template : Carousel
 * @author     Steven A. Zahm
 * @since      1.0
 * @license    GPL-2.0+
 * @link       httsp://connections-pro.com
 * @copyright  2019 Steven A. Zahm
 *
 * @wordpress-plugin
 * _lugin Name:       Connections Business Directory Template : Carousel
 * Plugin URI:        https://connections-pro.com
 * Description:       Carousel template for the block editor.
 * Version:           1.0
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CN_Block_Carousel_Template' ) ) {

	/**
	 * Class CN_Block_Carousel_Template
	 */
	class CN_Block_Carousel_Template {

		/**
		 * Stores an initialized instance of cnTemplate.
		 *
		 * @since 1.0
		 * @var cnTemplate
		 */
		private $template;

		public static function register() {

			$atts = array(
				'class'       => __CLASS__,
				'name'        => 'Block: Carousel',
				'slug'        => 'block-carousel',
				'type'        => 'block',
				'version'     => '1.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'https://connections-pro.com',
				'description' => 'Carousel',
				'custom'      => false,
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => '',
				// 'parts'       => array( 'css' => 'styles.css' ),
			);

			cnTemplateFactory::register( $atts );
		}

		/**
		 * Setup the template.
		 *
		 * @since 1.0
		 *
		 * @param cnTemplate $template An initialized instance of the cnTemplate class.
		 */
		public function __construct( $template ) {

			$this->template = $template;
		}
	}

	add_action( 'cn_register_template', array( 'CN_Block_Carousel_Template', 'register' ) );
}
