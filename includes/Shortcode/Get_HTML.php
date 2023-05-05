<?php
/**
 * Common shortcode code to get the shortcode HTML markup.
 *
 * @since      10.4.42
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shorcode
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );
namespace Connections_Directory\Shortcode;

/**
 * Trait Get_HTML
 *
 * @package Connections_Directory\Shortcode
 */
trait Get_HTML {

	/**
	 * The shortcode output HTML.
	 *
	 * @since 10.4.42
	 * @var string
	 */
	private $html;

	/**
	 * Get the generated shortcode HTML.
	 *
	 * @since 10.4.42
	 *
	 * @return string
	 */
	public function getHTML(): string {

		return $this->html;
	}

	/**
	 * Render the shortcode HTML.
	 *
	 * @since 10.4.42
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaping is done in the template.
	}

	/**
	 * Return the generated shortcode HTML.
	 *
	 * @since 10.4.42
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
