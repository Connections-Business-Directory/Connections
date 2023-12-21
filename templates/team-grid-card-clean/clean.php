<?php
/**
 * @package    Connections Widget Pack
 * @subpackage Template : Recently Added
 * @author     Steven A. Zahm
 * @since      1.0
 * @license    GPL-2.0+
 * @link       httsp://connections-pro.com
 * @copyright  2019 Steven A. Zahm
 *
 * @wordpress-plugin
 * _lugin Name:       Connections Widget Pack - Template
 * Plugin URI:        https://connections-pro.com
 * Description:       The Recently Added Widget Template.
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

use Connections_Directory\Utility\_sanitize;

if ( ! class_exists( 'CN_Block_Team_Grid_Card_Clean_Template' ) ) {

	/**
	 * Class CN_Block_Team_Grid_Card_Clean_Template
	 */
	class CN_Block_Team_Grid_Card_Clean_Template {

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
				'name'        => 'Block: Team Block Grid Card Clean',
				'slug'        => 'block-team-grid-card-clean',
				'type'        => 'block',
				'version'     => '1.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'https://connections-pro.com',
				'description' => 'Grid layout.',
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

		/**
		 * @since 1.0
		 *
		 * @param array  $attributes
		 * @param string $id
		 *
		 * @return string
		 */
		public function inlineCSS( $attributes, $id ) {

			$columns           = absint( $attributes['columns'] );
			$gutterWidth       = absint( $attributes['gutterWidth'] );
			$borderColor       = _sanitize::hexColor( $attributes['borderColor'] );
			$borderRadius      = absint( $attributes['borderRadius'] );
			$borderWidth       = absint( $attributes['borderWidth'] );
			$imageBorderColor  = _sanitize::hexColor( $attributes['imageBorderColor'] );
			$imageBorderRadius = 'square' === $attributes['imageShape'] ? absint( $attributes['imageBorderRadius'] ) . 'px' : '50%';
			$imageBorderWidth  = absint( $attributes['imageBorderWidth'] );
			// $boxShadow        = $attributes['displayDropShadow'] ? '0px 5px 10px 5px rgba(186,186,186,0.5)' : 'none !important';

			$style = <<<HERE
<style>
#{$id} {
	grid-template-columns: repeat( {$columns}, 1fr );
	gap: {$gutterWidth}px;
}
#{$id} .cn-team-member {
	border: {$borderWidth}px solid {$borderColor};
	border-radius: {$borderRadius}px;
}
#{$id} .cn-team-member .cn-image {
	border: {$imageBorderWidth}px solid {$imageBorderColor} !important;
	border-radius: {$imageBorderRadius} !important;
}
</style>
HERE;
			return $style;
		}
	}

	add_action( 'cn_register_template', array( 'CN_Block_Team_Grid_Card_Clean_Template', 'register' ) );
}
