<?php
/**
 * Deprecated file, still required as a couple addons presently call the methods.
 *
 * @deprecated 10.4.42
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shortcode\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

use Connections_Directory\Shortcode;
use function Connections_Directory\Utility\_deprecated\_file as _deprecated_file;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

_deprecated_file( basename( __FILE__ ), '10.4.42' );

/**
 * The core [connections] shortcode.
 *
 * @package     Connections
 * @subpackage  Shortcode API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */
class cnShortcode_Connections {

	/**
	 * Callback for the `[connections]` shortcode.
	 *
	 * Display results based on query var `cn-view`.
	 *
	 * @internal
	 * @since 0.7.3
	 * @since 10.4.40 Moved from {@file class.shortcode.php}.
	 * @deprecated 10.4.41
	 *
	 * @param array|string $atts    Shortcode attributes array or empty string.
	 * @param string|null  $content The content of a shortcode when it wraps some content.
	 * @param string       $tag     Shortcode name.
	 *
	 * @return string
	 */
	public static function view( $atts, $content = '', $tag = 'connections' ): string {

		_deprecated_function( __METHOD__, '10.4.41', 'Directory_View::instance()' );

		return Shortcode\Directory_View::instance( $atts, $content, $tag )->getHTML();
	}

	/**
	 * Renders the entry directory.
	 *
	 * @since 0.8
	 * @deprecated 10.4.41
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content = '', $tag = 'connections' ): string {

		_deprecated_function( __METHOD__, '10.4.41', 'Entry_Directory::instance()' );

		return Shortcode\Entry_Directory::instance( $atts, $content, $tag )->getHTML();
	}
}
