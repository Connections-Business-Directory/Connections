<?php

/**
 * The default email templates that can be used when sending email with cnEmail.
 *
 * @package     Connections
 * @subpackage  The default email templates.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnEmail_DefaultTemplates {

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
	 * @see cnEmailDefaultTemplate::getInstance()
	 * @see cnEmailDefaultTemplate();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class.
	 *
	 * @access public
	 * @since 0.7.8
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			// Register the default templates.
			add_action( 'plugins_loaded', array( __CLASS__, 'register' ) );
		}

	}

	/**
	 * Return an instance.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return object cnEmailDefaultTemplate
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Register a template for use.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param (array) $atts
	 * @return (void)
	 */
	public static function register() {

		$atts = array(
				'name'        => 'Default',
				'type'        => 'html',
				'version'     => '1.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'The default email template.',
				'parts'       => array( 'body' => array( __CLASS__, 'defaultBody' ) )
				);

		cnEmail_Template::register( $atts );

		$atts = array(
				'name'        => 'Plain Text',
				'slug'        => 'text',
				'type'        => 'text',
				'version'     => '1.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'The plain text email template.',
				);

		cnEmail_Template::register( $atts );
	}

	/**
	 * The callabck for cn_email_message that applies
	 * the formatted styling for the default template.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (string) $content The email content.
	 * @return (string) The styled email content.
	 */
	public static function defaultBody( $content ) {

		$content = str_replace( '<p>', '<p style="font-size: 14px; line-height: 150%">', $content );
		$content = str_replace( '<ul>', '<ul style="margin: 0 0 10px 0; padding: 0;">', $content );
		$content = str_replace( '<li>', '<li style="font-size: 14px; line-height: 150%; display:block; margin: 0 0 4px 0;">', $content );

		return $content;
	}

}
