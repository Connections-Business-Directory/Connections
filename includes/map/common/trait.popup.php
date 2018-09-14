<?php

namespace Connections_Directory\Map\Common;

use Connections_Directory\Map\UI\Popup;

/**
 * Trait Popup_Trait
 *
 * @package Connections_Directory\Map\Common
 * @author  Steven A. Zahm
 * @since   8.28
 */
trait Popup_Trait {

	/**
	 * @since 8.28
	 * @var Popup
	 */
	private $popup;


	/**
	 * Bind marker to a popup.
	 *
	 * @since 8.28
	 *
	 * @param Popup $popup The popup.
	 *
	 * @return $this
	 */
	public function bindPopup( $popup ) {

		$this->popup = $popup;

		return $this;
	}

	/**
	 * @since 8.28
	 * @return Popup
	 */
	public function getPopup() {

		return $this->popup;
	}
}
