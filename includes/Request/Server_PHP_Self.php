<?php
/**
 * Get, validate, and validate PHP_SELF variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Server PHP_SELF
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Server_PHP_Self
 *
 * @package Connections_Directory\Request
 */
class Server_PHP_Self extends Server {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 'PHP_SELF';

	/**
	 * The input schema.
	 *
	 * @since 10.4.8
	 *
	 * @var array
	 */
	protected $schema = array(
		'default' => '',
		'type'    => 'string',
		'format'  => 'uri',
	);
}
