<?php

/**
 * The email template API.
 *
 * @package     Connections
 * @subpackage  Email Template API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnEmail_Template {

	/**
	 * The template registry.
	 *
	 * @access private
	 * @since 0.7.8
	 * @var (array)
	 */
	public static $templates;

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.7.8
	 * @var (object)
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.7.8
	 * @see cnEmailTemplate::getInstance()
	 * @see cnEmailTemplate();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return (void)
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;
			self::$templates = new stdClass();
		}

	}

	/**
	 * Return an instance.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return (object) cnEmailTemplate
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Register a template for use.
	 *
	 * Accepted options for the $atts property are:
	 *  name (string) [required] The template name.
	 *  slug (string) [optional] The template slug.
	 *  type (string) [required] The template type.
	 *  version (string) [required] The template version.
	 *  author (string) [required] The authors name.
	 *  authorURL (string) [optional] The author's website.
	 *  description (string) [optional] Template description.
	 *  path (string) [required] The base path to the template's folder.
	 *  url (string) [required] The base URL to the templates's folder.
	 *  thumbnail (string) [optional] The template's thumnail file name.
	 *  parts (array) [optional] The template parts.
	 *  	Accepted values for parts:
	 *  		head (string) [optional] The action callback that should be run for the email template head. The output will come right after the </head> tag.
	 *  		body-before (string) The action callback that should be run before the email content.
	 *  		body (string) [required] The file name of the PHP file used to render the entry content.
	 *  		body-after (string) [optional] The action callback that should be run after the email content.
	 *  		foot (string) [optional] The action callback that should be run for the email template foot. The output will come right before the </body> tag.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (array) $atts
	 * @return (void)
	 */
	public static function register( $atts ) {

		$defaults = array(
			'name'        => '',
			'slug'        => '',
			'type'        => 'html',
			'version'     => '',
			'author'      => '',
			'authorURL'   => '',
			'description' => '',
			'path'        => '',
			'url'         => '',
			'thumbnail'   => '',
			'parts'       => array()
			);

		$atts = wp_parse_args( $atts, $defaults );

		extract( $atts );

		// Since the template slug is optional, but required, we'll create the slug from the template's name.
		if ( empty( $slug ) ) $slug = $atts['slug'] = sanitize_title_with_dashes( $name, '', 'save' );

		// PHP 5.4 warning fix.
		if ( ! isset( self::$templates->{ $slug } ) ) self::$templates->{ $slug } = new stdClass();

		self::$templates->{ $slug } = (object) $atts;
	}

	/**
	 * Return the requested template.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (string) $slug The template slug.
	 * @return (object) | (bool)
	 */
	public static function get( $slug ) {

		return isset( self::$templates->{ $slug } ) ? self::$templates->{ $slug } : FALSE;

	}

	/**
	 * The temlpate to use when sending an email.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (string) $slug The template slug.
	 * @return (void)
	 */
	public static function template( $slug ) {

		// Get the template attributes object.
		$template = self::get( $slug );

		// Register the template parts actions.
		self::parts( $template );

		 /*
		  * Hook into the cn_email_message filter.
		  * This filter only is required to be added if the template type is HTML.
		  * If the template is not, run a filer to ensure any html tags are stripped.
		  */
		$template->type == 'html' ? add_filter( 'cn_email_message', array( __CLASS__, 'content' ) ) : add_filter( 'cn_email_message', array( __CLASS__, 'stripTags' ) );
	}

	/**
	 * Add the actions/filters of the template.
	 *
	 * @access private
	 * @since 0.7.8
	 * @param  (object) $slug The template attributes object.
	 * @return (void)
	 */
	private static function parts( $template ) {

		foreach ( $template->parts as $part => $callback ) {

			switch ( $part ) {

				case 'head':
					add_action( 'cn_email_head', $callback, 20 );
					break;

				case 'body-before':
					add_action( 'cn_email_body-before', $callback, 20 );
					break;

				case 'body':
					add_filter( 'cn_email_message', $callback, 20 );
					break;

				case 'body-after':
					add_action( 'cn_email_body-after', $callback, 20 );
					break;

				case 'foot':
					add_action( 'cn_email_foot', $callback, 20 );
					break;
			}
		}

	}

	/**
	 * Build the template structure.
	 *
	 * @access private
	 * @since 0.7.8
	 * @param  (string) $content The email message.
	 * @return (string) The email message, before it is styled by a template.
	 */
	public static function content( $content ) {

		$head       = self::head();

		$bodyBefore = self::beforeBody();

		$body       = self::body( $content );

		$bodyAfter  = self::afterBody();

		$foot       = self::foot();

		return $head . $bodyBefore . $body . $bodyAfter . $foot;
	}

	/**
	 * Strip HTML tags while attempting to preserver structure.
	 *
	 * This could be vastly improved. Look into these:
	 *     Textify: https://github.com/jonathandavis/textify
	 *     Markdownify: http://milianw.de/projects/markdownify/index.php
	 *
	 * @access private
	 * @since 0.7.8
	 * @param  (string) $content The email message.
	 * @return (string)
	 */
	public static function stripTags( $content ) {

		$tags = array('</p>', '<br />', '<br/>', '<br>', '<hr />', '<hr>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '</tr>');

		$content = wpautop( $content, TRUE );

		$content = str_ireplace( $tags, "\r\n", $content );

		return strip_tags( $content );
	}

	/**
	 * The email head.
	 *
	 * @access private
	 * @since 0.7.8
	 * @return (string)
	 */
	private static function head() {

		ob_start();
		?>

		<html>
		<head>
			<style type="text/css">#outlook a { padding: 0; }</style>
		</head>
			<body>

		<?php

		do_action( 'cn_email_head' );

		return ob_get_clean();
	}

	/**
	 * The email before body message content.
	 *
	 * @access private
	 * @since 0.7.8
	 * @return (string)
	 */
	private static function beforeBody() {

		ob_start();

		do_action( 'cn_email_body-before' );

		return ob_get_clean();
	}

	/**
	 * The email body.
	 *
	 * @access private
	 * @since 0.7.8
	 * @return (string)
	 */
	private static function body( $content ) {

		return wpautop( $content );
	}

	/**
	 * The email after body message content.
	 *
	 * @access private
	 * @since 0.7.8
	 * @return (string)
	 */
	private static function afterBody() {

		ob_start();

		do_action( 'cn_email_body-after' );

		return ob_get_clean();
	}

	/**
	 * The email foot.
	 *
	 * @access private
	 * @since 0.7.8
	 * @return (string)
	 */
	private static function foot() {

		ob_start();

		do_action( 'cn_email_foot' );
		?>

			</body>
		</html>

		<?php
		return ob_get_clean();
	}

}

// Init the email template API.
cnEmail_Template::init();
