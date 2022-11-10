<?php
/**
 * Get, validate, and sanitize an array of ID request variables.
 *
 * @since 10.4.17
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Int Array
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

/**
 * Class Int_Array
 *
 * @package Connections_Directory\Request
 */
class ID_Array extends Integer_Array {

	/**
	 * ID constructor.
	 *
	 * @since 10.4.32
	 */
	public function __construct() {

		parent::__construct( 'id' );
	}
}
