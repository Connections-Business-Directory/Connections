<?php
/**
 * @package    Connections
 * @subpackage Template : Birthday Dark
 * @author     Steven A. Zahm
 * @since      0.7.9
 * @license    GPL-2.0+
 * @link       http://connections-pro.com
 * @copyright  2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Birthday Dark - Template
 * Plugin URI:        http://connections-pro.com
 * Description:       Default birthday template with a black background in a table like format.
 * Version:           2.0.1
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Birthday_Dark_Template' ) ) {

	class CN_Birthday_Dark_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Birthday_Dark_Template',
				'name'        => 'Birthday Dark',
				'slug'        => 'birthday-dark',
				'type'        => 'birthday',
				'version'     => '2.0.1',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Default birthday template with a black background in a table like format.',
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

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( __CLASS__, 'card' ) ) );
			$template->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $template, 'printCSS' ) ) );
		}

		public static function card( $entry, $template, $atts ) {

			?>

			<span class="cn-entry-name" style=""><?php echo $entry->name; ?></span> <span class="cn-upcoming-date"><?php echo $entry->getUpcoming( $atts['list_type'], $atts['date_format'] ); ?></span>

			<?php
		}

	}

	// Register the template.
	add_action( 'cn_register_template', array( 'CN_Birthday_Dark_Template', 'register' ) );
}
