<?php

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field;

/**
 * Class Group
 *
 * @package Connections_Directory\Form\Field
 */
abstract class Group extends Field {

	/**
	 * The HTML element tag that contains the group of Input fields.
	 *
	 * `block` is alias for `div`
	 * `inline` is alias for `span`
	 * `list` is alias for `ul`
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $container = 'list';

	/**
	 * Position of the Input field label. Default: `after`
	 *
	 * @since 10.4
	 * @var string
	 */
	protected $labelPosition = 'after';

	/**
	 * Get the HTML element tags based on the group container type.
	 *
	 * @since 10.4
	 *
	 * @return array{child: string, parent: string}
	 */
	protected function getContainerTags() {

		switch ( $this->container ) {

			case 'block':
			case 'div':
				$tags = array( 'child' => 'div', 'parent' => 'div' );
				break;

			case 'inline':
			case 'span':
				$tags = array( 'child' => 'span', 'parent' => 'span' );
				break;

			case 'list':
			case 'ul':
			default:
				$tags = array( 'child' => 'li', 'parent' => 'ul' );
		}

		return $tags;
	}

	/**
	 * Set HTML the element tag that contains the Input group.
	 *
	 * @since 10.4
	 *
	 * @param string $type
	 *
	 * @return static
	 */
	public function setContainer( $type ) {

		$this->container = in_array( $type, array( 'block', 'div', 'list', 'inline', 'span', 'ul' ) ) ? $type : 'list';

		return $this;
	}
}
