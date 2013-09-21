<?php

/**
 * Class registering and creating the metabox for add/edit an entry.
 *
 * @package     Connections
 * @subpackage  Metabox
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnMetabox {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.8
	 * @var (object)
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.8
	 * @see cnMetabox::init()
	 * @see cnMetabox();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class, if it has already been initialized, return the intialized instance.
	 *
	 * @access public
	 * @since 0.8
	 * @see cnMetabox()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;


		}

	}

	/**
	 * Return an instance of the class.
	 *
	 * @access public
	 * @since 0.8
	 * @return (object) cnMetabox
	 */
	public static function getInstance() {

		return self::$instance;
	}

}
