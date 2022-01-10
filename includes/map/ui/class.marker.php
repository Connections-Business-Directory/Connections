<?php

namespace Connections_Directory\Map\UI;

use cnCoordinates as Coordinates;
use Connections_Directory\Map\Common\Options;
use Connections_Directory\Map\Common\Popup_Trait;
use Connections_Directory\Map\Layer\Abstract_Layer;
use cnHTML as HTML;

/**
 * Class Marker
 *
 * @package Connections_Directory\Map\UI
 * @author  Steven A. Zahm
 * @since   8.28
 */
class Marker extends Abstract_Layer {

	use Options;
	use Popup_Trait;

	/**
	 * @since 8.28
	 * @var Coordinates
	 */
	private $coordinates;

	/**
	 * Marker constructor.
	 *
	 * @since 8.28
	 *
	 * @param string      $id
	 * @param Coordinates $coordinates
	 * @param array       $options
	 */
	public function __construct( $id, $coordinates, $options = array() ) {

		parent::__construct( $id );

		$this->coordinates = $coordinates;
		$this->setOptions( $options );
	}

	/**
	 * @since 8.28
	 *
	 * @param string      $id
	 * @param Coordinates $coordinates
	 * @param array       $options
	 *
	 * @return Marker
	 */
	public static function create( $id, $coordinates, $options = array() ) {

		return new static( $id, $coordinates, $options );
	}

	/**
	 * Get the marker coordinates.
	 *
	 * @since 8.28
	 *
	 * @return Coordinates
	 */
	public function getCoordinates() {

		return $this->coordinates;
	}

	/**
	 * Set the marker coordinates.
	 *
	 * @since 8.28
	 *
	 * @param Coordinates $coordinates
	 */
	public function setCoordinates( $coordinates ) {

		$this->coordinates = $coordinates;
	}

	/**
	 * Get the marker latitude.
	 *
	 * @since 8.28
	 *
	 * @return null|string
	 */
	public function getLatitude() {

		return $this->coordinates->getLatitude();
	}

	/**
	 * Get the marker longitude.
	 *
	 * @since 8.28
	 *
	 * @return null|string
	 */
	public function getLongitude() {

		return $this->coordinates->getLongitude();
	}

	/**
	 * Get the marker icon.
	 *
	 * @since 8.28
	 *
	 * @return Icon|null
	 */
	public function getIcon() {

		return $this->getOption( 'icon' );
	}

	/**
	 * Set the marker icon.
	 *
	 * @link https://leafletjs.com/reference.html#marker-icon
	 *
	 * @since 8.28
	 *
	 * @param Icon $icon Custom icon.
	 *
	 * @return $this
	 */
	public function setIcon( Icon $icon ) {

		return $this->store( 'icon', $icon );
	}

	/**
	 * Set marker as interactive.
	 *
	 * @link https://leafletjs.com/reference.html#marker-interactive
	 *
	 * @since 8.28
	 *
	 * @param bool $value Interactive value.
	 *
	 * @return $this
	 */
	public function setInteractive( $value ) {

		return $this->store( 'interactive', (bool) $value );
	}

	/**
	 * Whether or not if marker is interactive.
	 *
	 * @link https://leafletjs.com/reference.html#marker-interactive
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isInteractive() {

		return $this->getOption( 'interactive', true );
	}

	/**
	 * Set marker as draggable.
	 *
	 * @link https://leafletjs.com/reference.html#marker-draggable
	 *
	 * @since 8.28
	 *
	 * @param bool $value Draggable value.
	 *
	 * @return $this
	 */
	public function setDraggable( $value ) {

		return $this->store( 'draggable', (bool) $value );
	}

	/**
	 * Whether or not if marker is draggable.
	 *
	 * @link https://leafletjs.com/reference.html#marker-draggable
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isDraggable() {

		return $this->getOption( 'draggable', true );
	}

	/**
	 * Set if marker responds to keyboard events.
	 *
	 * @link https://leafletjs.com/reference.html#marker-keyboard
	 *
	 * @since 8.28
	 *
	 * @param bool $value Keyboard value.
	 *
	 * @return $this
	 */
	public function setKeyboard( $value ) {

		return $this->store( 'keyboard', (bool) $value );
	}

	/**
	 * Whether or not marker triggers keyboard events.
	 *
	 * @link https://leafletjs.com/reference.html#marker-keyboard
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isKeyboard() {

		return $this->getOption( 'keyboard', true );
	}

	/**
	 * Get marker tooltip.
	 *
	 * @link https://leafletjs.com/reference.html#marker-title
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getTitle() {

		return $this->getOption( 'title', '' );
	}

	/**
	 * Set marker tooltip.
	 *
	 * @link https://leafletjs.com/reference.html#marker-title
	 *
	 * @since 8.28
	 *
	 * @param string $title The tooltip title.
	 *
	 * @return $this
	 */
	public function setTitle( $title ) {

		return $this->store( 'title', $title );
	}

	/**
	 * Get the alt attribute of the icon.
	 *
	 * @link https://leafletjs.com/reference.html#marker-alt
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getAlt() {

		return $this->getOption( 'alt', '' );
	}

	/**
	 * Set alt attribute of icon.
	 *
	 * @link https://leafletjs.com/reference.html#marker-alt
	 *
	 * @since 8.28
	 *
	 * @param string $alt The tooltip alt.
	 *
	 * @return $this
	 */
	public function setAlt( $alt ) {

		return $this->store( 'alt', $alt );
	}

	/**
	 * Get the zIndex offset.
	 *
	 * @link https://leafletjs.com/reference.html#marker-zindexoffset
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getZIndexOffset() {

		return $this->getOption( 'zIndexOffset', 0 );
	}

	/**
	 * Set zIndex offset attribute.
	 *
	 * @link https://leafletjs.com/reference.html#marker-zindexoffset
	 *
	 * @since 8.28
	 *
	 * @param int $zIndexOffset The tooltip zIndex offset.
	 *
	 * @return $this
	 */
	public function setZIndexOffset( $zIndexOffset ) {

		return $this->store( 'zIndexOffset', (int) $zIndexOffset );
	}

	/**
	 * Get marker opacity.
	 *
	 * @link https://leafletjs.com/reference.html#marker-opacity
	 *
	 * @since 8.28
	 *
	 * @return float
	 */
	public function getOpacity() {

		return $this->getOption( 'fillOpacity', 1.0 );
	}

	/**
	 * Set the marker opacity.
	 *
	 * @link https://leafletjs.com/reference.html#marker-opacity
	 *
	 * @since 8.28
	 *
	 * @param float $value Marker opacity.
	 *
	 * @return $this
	 */
	public function setOpacity( $value ) {

		return $this->store( 'fillOpacity', (float) $value );
	}

	/**
	 * Set rise on hover.
	 *
	 * @link https://leafletjs.com/reference.html#marker-riseonhover
	 *
	 * @since 8.28
	 *
	 * @param bool $value Rise on hover option value.
	 *
	 * @return $this
	 */
	public function setRiseOnHover( $value ) {

		return $this->store( 'riseOnHover', (bool) $value );
	}

	/**
	 * Whether or not if rise on hover option is set.
	 *
	 * @link https://leafletjs.com/reference.html#marker-riseonhover
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isRiseOnHover() {

		return $this->getOption( 'riseOnHover', false );
	}

	/**
	 * Get the rise on hover offset.
	 *
	 * @link https://leafletjs.com/reference.html#marker-riseoffset
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getRiseOffset() {

		return $this->getOption( 'riseOffset', 250 );
	}

	/**
	 * Set rise offset.
	 *
	 * @link https://leafletjs.com/reference.html#marker-riseoffset
	 *
	 * @since 8.28
	 *
	 * @param int $value Rise on hover option value.
	 *
	 * @return $this
	 */
	public function setRiseOffset( $value ) {

		return $this->store( 'riseOffset', (int) $value );
	}

	/**
	 * Return the map marker HTML.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function get() {

		$options = array( array( 'name' => 'id', 'value' => $this->getId() ) );

		$options[] = array( 'name' => 'latitude', 'value' => $this->getLatitude() );
		$options[] = array( 'name' => 'longitude', 'value' => $this->getLongitude() );

		foreach ( $this->getOptions() as $key => $value ) {

			array_push( $options, array( 'name' => $key, 'value' => (string) $value ) );
		}

		$data = HTML::attribute( 'data-array', $options );

		$html = "<map-marker {$data}>";

		$popup = $this->getPopup();

		if ( $popup instanceof Popup ) {

			$html .= $popup;
		}

		$html .= '</map-marker>' . PHP_EOL;

		return $html;
	}

	/**
	 * Echo the map marker HTML.
	 *
	 * @since 8.28
	 */
	public function render() {

		// HTML is dynamically generated using static text, no user input.
		echo $this->get(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 8.28
	 * @return string
	 */
	public function __toString() {

		return $this->get();
	}
}
