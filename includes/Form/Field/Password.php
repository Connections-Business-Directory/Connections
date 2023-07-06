<?php
/**
 * The password text form field.
 *
 * @since 10.4.46
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Autocomplete;

/**
 * Class Password
 *
 * @package Connections_Directory\Form\Field
 */
class Password extends Text {

	use Autocomplete;

	/**
	 * The Input field type.
	 *
	 * @since 10.46
	 * @var string
	 */
	protected $type = 'password';
}
