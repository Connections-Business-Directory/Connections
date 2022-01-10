<?php

namespace Connections_Directory\Map\Control\Layer;

use Connections_Directory\Map\Common\Options;
use Connections_Directory\Map\Control\Abstract_Control as Control;
use Connections_Directory\Map\Layer\Abstract_Layer as Layer;
use cnHTML as HTML;

/**
 * Class Layer_Control
 *
 * @package Connections_Directory\Map\Control\Layer
 * @author  Steven A. Zahm
 * @since   8.29
 */
class Layer_Control extends Control {

	use Options;

	/**
	 * Base layers.
	 *
	 * @since 8.29
	 * @var Layer[]
	 */
	private $baseLayers = array();

	/**
	 * Overlay layers.
	 *
	 * @since 8.29
	 * @var Layer[]
	 */
	private $overlays = array();

	/**
	 * Layer_Control constructor.
	 *
	 * @since 8.29
	 *
	 * @param       $id
	 * @param Layer[] $baseLayers
	 * @param Layer[] $overlays
	 */
	public function __construct( $id, $baseLayers = array(), $overlays = array() ) {

		parent::__construct( $id );

		foreach ( $baseLayers as $layer ) {

			$this->addBaseLayer( $layer );
		}

		foreach ( $overlays as $layer ) {

			$this->addOverlay( $layer );
		}
	}

	/**
	 * @since 8.29
	 *
	 * @param string  $id
	 * @param Layer[] $baseLayers
	 * @param Layer[] $overlays
	 *
	 * @return Layer_Control
	 */
	public static function create( $id, $baseLayers = array(), $overlays = array() ) {

		return new static( $id, $baseLayers, $overlays );
	}

	/**
	 * Set initial collapsed state.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-collapsed
	 *
	 * @since 8.29
	 *
	 * @param bool $state The collapsed state.
	 *
	 * @return $this
	 */
	public function setCollapsed( $state ) {

		return $this->store( 'collapsed', (bool) $state );
	}

	/**
	 * Get initial collapsed state.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-collapsed
	 *
	 * @since 8.29
	 *
	 * @return bool
	 */
	public function isCollapsed() {

		return $this->getOption( 'collapsed', true );
	}

	/**
	 * Set initial collapsed state.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-autozindex
	 *
	 * @since 8.29
	 *
	 * @param bool $state The collapsed state.
	 *
	 * @return $this
	 */
	public function setAutoZIndex( $state ) {

		return $this->store( 'autoZIndex', (bool) $state );
	}

	/**
	 * Get initial collapsed state.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-autozindex
	 *
	 * @since 8.29
	 *
	 * @return bool
	 */
	public function isAutoZIndex() {

		return $this->getOption( 'autoZIndex', true );
	}

	/**
	 * Add a base layer.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-addbaselayer
	 *
	 * @since 8.29
	 *
	 * @param Layer $layer A Layer.
	 *
	 * @return $this
	 */
	public function addBaseLayer( Layer $layer ) {

		$this->baseLayers[] = $layer;

		return $this;
	}

	/**
	 * Get all base layers.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-addbaselayer
	 *
	 * @since 8.29
	 *
	 * @return Layer[]
	 */
	public function getBaseLayers() {

		return $this->baseLayers;
	}

	/**
	 * Add an overlay layer.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-addoverlay
	 *
	 * @since 8.29
	 *
	 * @param Layer $layer A Layer.
	 *
	 * @return $this
	 */
	public function addOverlay( Layer $layer ) {

		$this->overlays[] = $layer;

		return $this;
	}

	/**
	 * Get all overlay layers.
	 *
	 * @see https://leafletjs.com/reference.html#control-layers-addoverlay
	 *
	 * @since 8.29
	 *
	 * @return Layer[]
	 */
	public function getOverlays() {

		return $this->overlays;
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

		$this->options['collapsed'] = $this->options['collapsed'] ? 'true' : 'false';

		foreach ( $this->getOptions() as $key => $value ) {

			array_push( $options, array( 'name' => $key, 'value' => (string) $value ) );
		}

		$data = HTML::attribute( 'data-array', $options );

		$html = "<map-control-layers {$data}>" . PHP_EOL;

		foreach ( $this->getBaseLayers() as $layer ) {

			$html .= sprintf(
				'<map-base-layer data-id="%1$s">%2$s</map-base-layer>' . PHP_EOL,
				$layer->getId(),
				$layer->getOption( 'name' )
			);
		}

		foreach ( $this->getOverlays() as $layer ) {

			$html .= sprintf(
				'<map-overlay-layer data-id="%1$s">%2$s</map-overlay-layer>' . PHP_EOL,
				$layer->getId(),
				$layer->getOption( 'name' )
			);
		}

		$html .= '</map-control-layers>' . PHP_EOL;

		return $html;
	}

	/**
	 * Echo the layer control HTML.
	 *
	 * @since 8.29
	 */
	public function render() {

		// HTML is dynamically generated using static text, no user input.
		echo $this->get(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 8.29
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->get();
	}
}
