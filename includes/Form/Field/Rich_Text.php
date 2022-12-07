<?php
/**
 * Create and render a WP Editor field.
 *
 * @since 10.4.28
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections_Directory\Form\Field\Rich_Text
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Id;
use Connections_Directory\Form\Field\Attribute\Name;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Value;
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_string;

/**
 * Class Rich_Text
 *
 * @package Connections_Directory\Form\Field
 */
class Rich_Text {

	use Id;
	use Name;
	use Prefix;
	use Value;

	/**
	 * The settings parameters passed to @see wp_editor()
	 *
	 * @since 10.4.28
	 * @var array
	 */
	private $settings = array();

	/**
	 * Field constructor.
	 */
	public function __construct() {
		$this->setDefaultValue( '' );
	}

	/**
	 * Create an instance of the Field.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public static function create() {

		return new static();
	}

	/**
	 * The settings parameters passed to {@see wp_editor()}.
	 *
	 * @since 10.4.28
	 *
	 * @param array $settings See {@see _WP_Editors::parse_settings()} for description.
	 */
	public function rteSettings( $settings ) {

		$this->settings = _parse::parameters( $settings, $this->settings, false );

		return $this;
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4
	 *
	 * @return string
	 */
	public function getFieldHTML() {

		$prefix = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

		/** @var string $id */
		$id = _string::applyPrefix( $prefix, $this->getId() );

		$this->rteSettings( array( 'textarea_name' => $this->getName() ) );

		ob_start();

		wp_editor(
			$this->getValue(),
			$id,
			$this->settings
		);

		return ob_get_clean();
	}

	/**
	 * Get the field and field label HTML.
	 *
	 * @since 10.4.28
	 *
	 * @return string
	 */
	public function getHTML() {

		return $this->getFieldHTML();
	}

	/**
	 * Echo field and field label HTML.
	 *
	 * @since 10.4.28
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Return the field HTML.
	 *
	 * @since 10.4.28
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
