<?php
/**
 * @package    Connections
 * @subpackage Template : Anniversary Dark
 * @author     Steven A. Zahm
 * @since      0.7.9
 * @license    GPL-2.0+
 * @link       https://connections-pro.com
 * @copyright  2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * _lugin Name:       Connections Anniversary Dark - Template
 * Plugin URI:        https://connections-pro.com
 * Description:       Anniversary template with a black background in a table like format.
 * Version:           2.0.1
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CN_Anniversary_Dark_Template' ) ) {

	/**
	 * Class CN_Anniversary_Dark_Template
	 */
	class CN_Anniversary_Dark_Template {

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
				'class'       => 'CN_Anniversary_Dark_Template',
				'name'        => 'Anniversary Dark',
				'slug'        => 'anniversary-dark',
				'type'        => 'anniversary',
				'version'     => '2.0.1',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'Anniversary template with a black background in a table like format.',
				'custom'      => false,
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => 'thumbnail.png',
				'parts'       => array( 'css' => 'styles.css' ),
			);

			cnTemplateFactory::register( $atts );
		}

		/**
		 * CN_Anniversary_Dark_Template constructor.
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

			$formatted      = '';
			$dates          = $entry->dates;
			$dateCollection = $dates->filterBy( 'type', $atts['list_type'] )->getCollection( 1 );
			$entryDate      = $dateCollection->first();

			switch ( $atts['year_type'] ) {

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
			<span class="cn-entry-name" style=""><?php echo esc_html( $entry->getName( array( 'format' => $atts['name_format'] ) ) ); ?></span> <span class="cn-upcoming-date"><?php echo esc_html( $formatted ); ?></span>
			<?php
		}
	}

	// Register the template.
	add_action( 'cn_register_template', array( 'CN_Anniversary_Dark_Template', 'register' ) );
}
