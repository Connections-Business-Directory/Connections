<?php

declare( strict_types=1 );

namespace Connections_Directory\Form;

use Connections_Directory\Form\Field\Attributes;
use Connections_Directory\Form\Field\Attribute\Classnames;
use Connections_Directory\Form\Field\Attribute\Data;
use Connections_Directory\Form\Field\Attribute\Disabled;
use Connections_Directory\Form\Field\Attribute\Label;
use Connections_Directory\Form\Field\Attribute\Name;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Read_Only;
use Connections_Directory\Form\Field\Attribute\Required;
use Connections_Directory\Form\Field\Attribute\Style;
use Connections_Directory\Form\Field\Attribute\Id;
use Connections_Directory\Form\Field\Attribute\Value;

/**
 * Class Field
 *
 * @package Connections_Directory\Form
 */
abstract class Field implements Interfaces\Field {

	use Attributes;
	use Classnames;
	use Data;
	use Disabled;
	use Id;
	use Label;
	use Name;
	use Prefix;
	use Read_Only;
	use Required;
	use Style;
	use Value;

	/**
	 * HTML to insert before the field HTML.
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $prepend = '';

	/**
	 * HTML to insert after the field HTML.
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $append = '';

	/**
	 * Field constructor.
	 *
	 * @param array $attributes The field properties.
	 */
	public function __construct( array $attributes = array() ) {
	}

	/**
	 * Create an instance of the Field.
	 *
	 * @since 10.4
	 *
	 * @param array $properties The field properties.
	 *
	 * @return static
	 */
	public static function create( array $properties = array() ): Field {

		return new static( $properties );
	}

	/**
	 * HTML to insert before the field HTML.
	 *
	 * @since 10.4
	 *
	 * @param string $string
	 *
	 * @return static
	 */
	public function prepend( $string ) {

		$this->prepend = $string;

		return $this;
	}

	/**
	 * HTML to insert after the field HTML.
	 *
	 * @since 10.4
	 *
	 * @param string $string
	 *
	 * @return static
	 */
	public function append( $string ) {

		$this->append = $string;

		return $this;
	}

	/**
	 * Echo the field HTML.
	 *
	 * @since 10.4
	 *
	 * @return static
	 */
	public function fieldHTML() {

		echo $this->getFieldHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return $this;
	}

	/**
	 * Get the field and field label HTML.
	 *
	 * @since 10.4
	 * @since 10.4.39 Add support for the `implicit` label position.
	 *
	 * @return string
	 */
	public function getHTML() {

		$prepend = $this->prepend;
		$label   = $this->getLabelHTML();
		$field   = $this->getFieldHTML();
		$append  = $this->append;
		$html    = "{$label}{$field}";

		switch ( $this->labelPosition ) {

			case 'after':
				$html = "{$field}{$label}";
				break;

			case 'implicit':
			case 'implicit/before':
				$html = str_replace( '</label>', "{$field}</label>", $label );
				break;

			case 'implicit/after':
				$html = preg_replace( '/<label([^>]+?)?[\/ ]*>/', "<label$1>{$field}", $label, 1 );
				break;
		}

		return $prepend . $html . $append;
	}

	/**
	 * Echo field and field label HTML.
	 *
	 * @since 10.4
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 10.4
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
