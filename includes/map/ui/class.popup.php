<?php

namespace Connections_Directory\Map\UI;

use Connections_Directory\Map\Common\Options;
use Connections_Directory\Map\Common\Popup_Trait;
use Connections_Directory\Map\Layer\Abstract_Layer;
use cnHTML as HTML;

/**
 * Class Popup
 *
 * @package Connections_Directory\Map\UI
 * @author  Steven A Zahm
 * @since   8.28
 */
class Popup extends Abstract_Layer {

	use Options;
	use Popup_Trait;

	/**
	 * The popup html content.
	 *
	 * @since 8.28
	 * @var string
	 */
	private $content;

	/**
	 * Popup constructor.
	 *
	 * @since 8.28
	 *
	 * @param string $id
	 * @param string $content
	 * @param array  $options
	 */
	public function __construct( $id, $content, $options = array() ) {

		parent::__construct( $id );

		$this->content = $content;
		$this->setOptions( $options );
	}

	/**
	 * @since 8.28
	 *
	 * @param string $id
	 * @param string $content
	 * @param array  $options
	 *
	 * @return Popup
	 */
	public static function create( $id, $content, $options = array() ) {

		return new static( $id, $content, $options );
	}

	/**
	 * Get max width.
	 *
	 * @link https://leafletjs.com/reference.html#popup-maxwidth
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getMaxWidth() {

		return $this->getOption( 'maxWidth', 300 );
	}

	/**
	 * Set the max width of the popup.
	 *
	 * @link https://leafletjs.com/reference.html#popup-maxwidth
	 *
	 * @since 8.28
	 *
	 * @param int $width
	 *
	 * @return $this
	 */
	public function setMaxWidth( $width ) {

		return $this->store( 'maxWidth', (int) $width );
	}

	/**
	 * Get min width.
	 *
	 * @link https://leafletjs.com/reference.html#popup-minwidth
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getMinWidth() {

		return $this->getOption( 'minWidth', 50 );
	}

	/**
	 * Set the min width.
	 *
	 * @link https://leafletjs.com/reference.html#popup-minwidth
	 *
	 * @since 8.28
	 *
	 * @param int $width
	 *
	 * @return $this
	 */
	public function setMinWidth( $width ) {

		return $this->store( 'minWidth', (int) $width );
	}

	/**
	 * Get max height.
	 *
	 * @link https://leafletjs.com/reference.html#popup-maxheight
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getMaxHeight() {

		return $this->getOption( 'maxHeight' );
	}

	/**
	 * Set the max height.
	 *
	 * @link https://leafletjs.com/reference.html#popup-maxheight
	 *
	 * @since 8.28
	 *
	 * @param int $height
	 *
	 * @return $this
	 */
	public function setMaxHeight( $height ) {

		return $this->store( 'maxHeight', (int) $height );
	}

	/**
	 * Set the auto pan value.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopan
	 *
	 * @since 8.28
	 *
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function setAutoPan( $value ) {

		return $this->store( 'autoPan', (bool) $value );
	}

	/**
	 * Get auto pan value.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopan
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isAutoPan() {

		return $this->getOption( 'autoPan', true );
	}

	/**
	 * Set the keep in view option. If true the popup is kept in view.
	 *
	 * @link https://leafletjs.com/reference.html#popup-keepinview
	 *
	 * @since 8.28
	 *
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function setKeepInView( $value ) {

		return $this->store( 'keepInView', (bool) $value );
	}

	/**
	 * Whether or not the popup can be panned out of view.
	 *
	 * @link https://leafletjs.com/reference.html#popup-keepinview
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isKeepInView() {

		return $this->getOption( 'keepInView', false );
	}

	/**
	 * Set the close button option. If true a close button is shown.
	 *
	 * @link https://leafletjs.com/reference.html#popup-closebutton
	 *
	 * @since 8.28
	 *
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function setCloseButton( $value ) {

		return $this->store( 'closeButton', (bool) $value );
	}

	/**
	 * Whether or not the popup has a close button.
	 *
	 * @link https://leafletjs.com/reference.html#popup-closebutton
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function hasCloseButton() {

		return $this->getOption( 'closeButton', true );
	}

	/**
	 * Get the offset option.
	 *
	 * @link https://leafletjs.com/reference.html#popup-offset
	 *
	 * @since 8.28
	 *
	 * @return array
	 */
	public function getOffset() {

		return $this->getOption( 'offset', array( 0, 6 ) );
	}

	/**
	 * Set the offset option as a point.
	 *
	 * @link https://leafletjs.com/reference.html#popup-offset
	 *
	 * @since 8.28
	 *
	 * @param array $value
	 *
	 * @return $this
	 */
	public function setOffset( $value ) {

		return $this->store( 'offset', $value );
	}

	/**
	 * Get the autoPanPaddingTopLeft option.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopanpaddingtopleft
	 *
	 * @since 8.28
	 *
	 * @return int
	 */
	public function getAutoPanPaddingTopLeft() {

		return $this->getOption( 'autoPanPaddingTopLeft' );
	}

	/**
	 * Set the autoPanPaddingTopLeft option as a point.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopanpaddingtopleft
	 *
	 * @since 8.28
	 *
	 * @param array $value
	 *
	 * @return $this
	 */
	public function setAutoPanPaddingTopLeft( $value ) {

		return $this->store( 'autoPanPaddingTopLeft', $value );
	}

	/**
	 * Get the autoPanPaddingBottomRight option.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopanpaddingbottomright
	 *
	 * @since 8.28
	 *
	 * @return array
	 */
	public function getAutoPanPaddingBottomRight() {

		return $this->getOption( 'autoPanPaddingBottomRight' );
	}

	/**
	 * Set the autoPanPaddingBottomRight option as a point.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopanpaddingbottomright
	 *
	 * @since 8.28
	 *
	 * @param array $value
	 *
	 * @return $this
	 */
	public function setAutoPanPaddingBottomRight( $value ) {

		return $this->store( 'autoPanPaddingBottomRight', $value );
	}

	/**
	 * Get the autoPanPadding option.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopanpadding
	 *
	 * @since 8.28
	 *
	 * @return array
	 */
	public function getAutoPanPadding() {

		return $this->getOption( 'autoPanPadding', array( 5, 5 ) );
	}

	/**
	 * Set the autoPanPadding option as a point.
	 *
	 * @link https://leafletjs.com/reference.html#popup-autopanpadding
	 *
	 * @since 8.28
	 *
	 * @param array $value
	 *
	 * @return $this
	 */
	public function setAutoPanPadding( $value ) {

		return $this->store( 'autoPanPadding', $value );
	}

	/**
	 * Set the closeOnClick option.
	 *
	 * @link https://leafletjs.com/reference.html#popup-closeonclick
	 *
	 * @since 8.28
	 *
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function setCloseOnClick( $value ) {

		return $this->store( 'closeOnClick', (bool) $value );
	}

	/**
	 * Whether or not the popup closes when map is clicked.
	 *
	 * @link https://leafletjs.com/reference.html#popup-closeonclick
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isCloseOnClick() {

		return $this->getOption( 'closeOnClick' );
	}

	/**
	 * Get the class name.
	 *
	 * @link https://leafletjs.com/reference.html#popup-classname
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getClassName() {

		return $this->getOption( 'className', '' );
	}

	/**
	 * Set a custom class name to assign to the popup.
	 *
	 * @link https://leafletjs.com/reference.html#popup-classname
	 *
	 * @since 8.28
	 *
	 * @param string $className
	 *
	 * @return $this
	 */
	public function setClassName( $className ) {

		return $this->store( 'className', $className );
	}

	/**
	 * Set auto close option.
	 *
	 * @link https://leafletjs.com/reference-1.3.4.html#popup-autoclose
	 *
	 * @param bool $autoClose.
	 *
	 * @return $this
	 */
	public function setAutoClose( $autoClose ) {

		return $this->store( 'autoClose', (bool) $autoClose );
	}

	/**
	 * Whether or not to auto close the popup.
	 *
	 * @link https://leafletjs.com/reference-1.3.4.html#popup-autoclose
	 *
	 * @since 8.28
	 *
	 * @return bool
	 */
	public function isAutoClose() {

		return $this->getOption( 'autoClose', true );
	}

	/**
	 * Get the HTML content of the popup.
	 *
	 * @link https://leafletjs.com/reference.html#popup-setcontent
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function getContent() {

		return $this->content;
	}

	/**
	 * Set HTML content of the popup.
	 *
	 * @link https://leafletjs.com/reference.html#popup-setcontent
	 *
	 * @since 8.28
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function setContent( $content ) {

		$this->content = $content;

		return $this;
	}

	/**
	 * Return the HTML popup HTML.
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

		$html  = "<map-marker-popup {$data}>";
		$html .= $this->getContent();
		$html .= '</map-marker-popup>';

		return $html;
	}

	/**
	 * Echo the popup HTML.
	 *
	 * @since 8.28
	 *
	 * @return string
	 */
	public function render() {

		echo wp_kses_post( $this->get() );
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
