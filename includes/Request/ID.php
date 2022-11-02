<?php
/**
 * Get, validate, and validate an ID request variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\ID
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class ID
 *
 * @package Connections_Directory\Request
 */
class ID extends Integer {

	/**
	 * ID constructor.
	 *
	 * @since 10.4.32
	 */
	public function __construct() {

		parent::__construct( 'id' );
	}
}
