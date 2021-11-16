<?php

namespace Connections_Directory\Map\Layer\Group;

use cnHTML as HTML;
use Connections_Directory\Map\Layer\Abstract_Layer as Layer;

/**
 * Class Layer_Group
 *
 * @package Connections_Directory\Map\Group
 * @author  Steven A. Zahm
 * @since   8.28
 */
class Layer_Group extends Layer {

	/**
	 * @since 8.28
	 * @var Layer[]
	 */
	private $layers = array();

	/**
	 * @since  8.28
	 *
	 * @param string $id
	 *
	 * @return Layer_Group
	 */
	public static function create( $id ) {

		return new static( $id );
	}

	/**
	 * Add a layer to the group.
	 *
	 * @since 8.28
	 *
	 * @param Layer $layer Layer being added.
	 *
	 * @return $this
	 */
	public function addLayer( $layer ) {

		$this->layers[] = $layer;

		return $this;
	}

	/**
	 * Get all layers.
	 *
	 * @since 8.28
	 *
	 * @return Layer[]
	 */
	public function getLayers() {

		return $this->layers;
	}

	/**
	 * Returns the layer group HTML.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function get() {

		$options = array( array( 'name' => 'id', 'value' => $this->getId() ) );

		foreach ( $this->getOptions() as $key => $value ) {

			array_push( $options, array( 'name' => $key, 'value' => (string) $value ) );
		}

		$data = HTML::attribute( 'data-array', $options );

		$html = "<map-layergroup {$data}>" . PHP_EOL;

		foreach ( $this->getLayers() as $layer ) {

			$html .= $layer;
		}

		$html .= '</map-layergroup>' . PHP_EOL;

		return $html;
	}

	/**
	 * Echo the layer group HTML.
	 *
	 * @since 8.28
	 */
	public function render() {

		// HTML is dynamically generated using static text, no user input.
		echo $this->get(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 8.28
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->get();
	}
}
