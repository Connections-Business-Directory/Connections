<?php
/**
 * @package    Connections
 * @subpackage Template : Birthday Light
 * @author     Steven A. Zahm
 * @since      0.7.9
 * @license    GPL-2.0+
 * @link       http://connections-pro.com
 * @copyright  2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Birthday Light - Template
 * Plugin URI:        http://connections-pro.com
 * Description:       Default birthday template with a light background in a table like format.
 * Version:           2.0.1
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
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
				'version'     => '2.0.1',
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

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( __CLASS__, 'card' ) ) );
			$template->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $template, 'printCSS' ) ) );
		}

		/**
		 * @param cnEntry_vCard $entry
		 * @param cnTemplate    $template
		 * @param array         $atts
		 */
		public static function card( $entry, $template, $atts ) {

			$formatted      = '';
			$dates          = $entry->dates;
			$dateCollection = $dates->filterBy( 'type', $atts['list_type'] )->getCollection( 1 );
			$entryDate      = $dateCollection->first();

			switch( $atts['year_type'] ) {

				case 'original':

					if ( $entryDate instanceof cnEntry_Date ) {

						$date = $entryDate->getDate();

						if ( $date instanceof DateTime ) {

							$formatted = $date->format( $atts['date_format'] );
						}
					}

					break;

				case 'since':

					if ( $entryDate instanceof cnEntry_Date ) {

						$date = $entryDate->getDate();

						if ( $date instanceof DateTime ) {

							$today     = new DateTime( current_time( 'mysql' ) );
							$interval  = $today->diff( $date );
							$formatted = $interval->format( $atts['year_format'] );
						}
					}

					break;

				default:

					if ( $entryDate instanceof cnEntry_Date ) {

						$date = $entryDate->getDate();

						if ( $date instanceof DateTime ) {

							$formatted = cnDate::getUpcoming( $date, $atts['date_format'] ); // $date->format( $atts['date_format'] );
						}
					}
			}

			?>

			<span class="cn-entry-name" style=""><?php echo $entry->name; ?></span> <span class="cn-upcoming-date"><?php echo $formatted; ?></span>

			<?php
		}

	}

	// Register the template.
	add_action( 'cn_register_template', array( 'CN_Birthday_Light_Template', 'register' ) );
}
