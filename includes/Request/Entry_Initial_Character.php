<?php
/**
 * Get, validate, and validate the entry initial character variable.
 *
 * @since 10.4.8
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Request\Entry Initial Character
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2021, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Request;

use Connections_Directory\Request;
use WP_Error;

/**
 * Class Entry_Initial_Character
 *
 * @package Connections_Directory\Request
 */
class Entry_Initial_Character extends Input {

	/**
	 * The request variable key.
	 *
	 * @since 10.4.8
	 *
	 * @var string
	 */
	protected $key = 'cn-char';

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
	);

	/**
	 * Get the request variable.
	 *
	 * In the admin this is available as part the request.
	 * On the frontend this is registered as a WordPress query variable and will be accessed
	 * via the `parse_request` query variables. This allows access when using permalinks as
	 * the request variables will not exist otherwise.
	 *
	 * @since 10.4.8
	 *
	 * @return string
	 */
	public function getInput() {

		if ( is_admin() ) {

			$value = parent::getInput();

		} else {

			$value = Request::get()->getVar( $this->key );
		}

		return urldecode( $value );
	}

	/**
	 * Sanitize the search term.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The value to sanitize.
	 *
	 * @return string
	 */
	protected function sanitize( $unsafe ) {

		return sanitize_text_field( $unsafe );
	}

	/**
	 * Validate the search term.
	 *
	 * @since 10.4.8
	 *
	 * @param string $unsafe The raw request value to validate.
	 *
	 * @return true|WP_Error
	 */
	protected function validate( $unsafe ) {

		return 1 === mb_strlen( $unsafe ) ? true : new WP_Error( 'invalid initial character' );
	}
}
