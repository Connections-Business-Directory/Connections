<?php

namespace Connections_Directory\Utility;

use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

/**
 * Class _color
 *
 * @package Connections_Directory\Utility
 */
final class _color {

	/**
	 * An array of named colors as the key with the value being the RGB values of said color.
	 *
	 * @access private
	 * @since  8.1
	 * @static
	 *
	 * @link   http://psoug.org/snippet/CSS_Colornames_to_RGB_values_415.htm
	 * @var    array
	 */
	private static $colors = array(

		// Colors as they are defined in HTML 3.2
		'black'                => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0x00 ),
		'maroon'               => array( 'red' => 0x80, 'green' => 0x00, 'blue' => 0x00 ),
		'green'                => array( 'red' => 0x00, 'green' => 0x80, 'blue' => 0x00 ),
		'olive'                => array( 'red' => 0x80, 'green' => 0x80, 'blue' => 0x00 ),
		'navy'                 => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0x80 ),
		'purple'               => array( 'red' => 0x80, 'green' => 0x00, 'blue' => 0x80 ),
		'teal'                 => array( 'red' => 0x00, 'green' => 0x80, 'blue' => 0x80 ),
		'gray'                 => array( 'red' => 0x80, 'green' => 0x80, 'blue' => 0x80 ),
		'silver'               => array( 'red' => 0xC0, 'green' => 0xC0, 'blue' => 0xC0 ),
		'red'                  => array( 'red' => 0xFF, 'green' => 0x00, 'blue' => 0x00 ),
		'lime'                 => array( 'red' => 0x00, 'green' => 0xFF, 'blue' => 0x00 ),
		'yellow'               => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0x00 ),
		'blue'                 => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0xFF ),
		'fuchsia'              => array( 'red' => 0xFF, 'green' => 0x00, 'blue' => 0xFF ),
		'aqua'                 => array( 'red' => 0x00, 'green' => 0xFF, 'blue' => 0xFF ),
		'white'                => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0xFF ),

		// Additional colors as they are used by Netscape and IE
		'aliceblue'            => array( 'red' => 0xF0, 'green' => 0xF8, 'blue' => 0xFF ),
		'antiquewhite'         => array( 'red' => 0xFA, 'green' => 0xEB, 'blue' => 0xD7 ),
		'aquamarine'           => array( 'red' => 0x7F, 'green' => 0xFF, 'blue' => 0xD4 ),
		'azure'                => array( 'red' => 0xF0, 'green' => 0xFF, 'blue' => 0xFF ),
		'beige'                => array( 'red' => 0xF5, 'green' => 0xF5, 'blue' => 0xDC ),
		'blueviolet'           => array( 'red' => 0x8A, 'green' => 0x2B, 'blue' => 0xE2 ),
		'brown'                => array( 'red' => 0xA5, 'green' => 0x2A, 'blue' => 0x2A ),
		'burlywood'            => array( 'red' => 0xDE, 'green' => 0xB8, 'blue' => 0x87 ),
		'cadetblue'            => array( 'red' => 0x5F, 'green' => 0x9E, 'blue' => 0xA0 ),
		'chartreuse'           => array( 'red' => 0x7F, 'green' => 0xFF, 'blue' => 0x00 ),
		'chocolate'            => array( 'red' => 0xD2, 'green' => 0x69, 'blue' => 0x1E ),
		'coral'                => array( 'red' => 0xFF, 'green' => 0x7F, 'blue' => 0x50 ),
		'cornflowerblue'       => array( 'red' => 0x64, 'green' => 0x95, 'blue' => 0xED ),
		'cornsilk'             => array( 'red' => 0xFF, 'green' => 0xF8, 'blue' => 0xDC ),
		'crimson'              => array( 'red' => 0xDC, 'green' => 0x14, 'blue' => 0x3C ),
		'darkblue'             => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0x8B ),
		'darkcyan'             => array( 'red' => 0x00, 'green' => 0x8B, 'blue' => 0x8B ),
		'darkgoldenrod'        => array( 'red' => 0xB8, 'green' => 0x86, 'blue' => 0x0B ),
		'darkgray'             => array( 'red' => 0xA9, 'green' => 0xA9, 'blue' => 0xA9 ),
		'darkgreen'            => array( 'red' => 0x00, 'green' => 0x64, 'blue' => 0x00 ),
		'darkkhaki'            => array( 'red' => 0xBD, 'green' => 0xB7, 'blue' => 0x6B ),
		'darkmagenta'          => array( 'red' => 0x8B, 'green' => 0x00, 'blue' => 0x8B ),
		'darkolivegreen'       => array( 'red' => 0x55, 'green' => 0x6B, 'blue' => 0x2F ),
		'darkorange'           => array( 'red' => 0xFF, 'green' => 0x8C, 'blue' => 0x00 ),
		'darkorchid'           => array( 'red' => 0x99, 'green' => 0x32, 'blue' => 0xCC ),
		'darkred'              => array( 'red' => 0x8B, 'green' => 0x00, 'blue' => 0x00 ),
		'darksalmon'           => array( 'red' => 0xE9, 'green' => 0x96, 'blue' => 0x7A ),
		'darkseagreen'         => array( 'red' => 0x8F, 'green' => 0xBC, 'blue' => 0x8F ),
		'darkslateblue'        => array( 'red' => 0x48, 'green' => 0x3D, 'blue' => 0x8B ),
		'darkslategray'        => array( 'red' => 0x2F, 'green' => 0x4F, 'blue' => 0x4F ),
		'darkturquoise'        => array( 'red' => 0x00, 'green' => 0xCE, 'blue' => 0xD1 ),
		'darkviolet'           => array( 'red' => 0x94, 'green' => 0x00, 'blue' => 0xD3 ),
		'deeppink'             => array( 'red' => 0xFF, 'green' => 0x14, 'blue' => 0x93 ),
		'deepskyblue'          => array( 'red' => 0x00, 'green' => 0xBF, 'blue' => 0xFF ),
		'dimgray'              => array( 'red' => 0x69, 'green' => 0x69, 'blue' => 0x69 ),
		'dodgerblue'           => array( 'red' => 0x1E, 'green' => 0x90, 'blue' => 0xFF ),
		'firebrick'            => array( 'red' => 0xB2, 'green' => 0x22, 'blue' => 0x22 ),
		'floralwhite'          => array( 'red' => 0xFF, 'green' => 0xFA, 'blue' => 0xF0 ),
		'forestgreen'          => array( 'red' => 0x22, 'green' => 0x8B, 'blue' => 0x22 ),
		'gainsboro'            => array( 'red' => 0xDC, 'green' => 0xDC, 'blue' => 0xDC ),
		'ghostwhite'           => array( 'red' => 0xF8, 'green' => 0xF8, 'blue' => 0xFF ),
		'gold'                 => array( 'red' => 0xFF, 'green' => 0xD7, 'blue' => 0x00 ),
		'goldenrod'            => array( 'red' => 0xDA, 'green' => 0xA5, 'blue' => 0x20 ),
		'greenyellow'          => array( 'red' => 0xAD, 'green' => 0xFF, 'blue' => 0x2F ),
		'honeydew'             => array( 'red' => 0xF0, 'green' => 0xFF, 'blue' => 0xF0 ),
		'hotpink'              => array( 'red' => 0xFF, 'green' => 0x69, 'blue' => 0xB4 ),
		'indianred'            => array( 'red' => 0xCD, 'green' => 0x5C, 'blue' => 0x5C ),
		'indigo'               => array( 'red' => 0x4B, 'green' => 0x00, 'blue' => 0x82 ),
		'ivory'                => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0xF0 ),
		'khaki'                => array( 'red' => 0xF0, 'green' => 0xE6, 'blue' => 0x8C ),
		'lavender'             => array( 'red' => 0xE6, 'green' => 0xE6, 'blue' => 0xFA ),
		'lavenderblush'        => array( 'red' => 0xFF, 'green' => 0xF0, 'blue' => 0xF5 ),
		'lawngreen'            => array( 'red' => 0x7C, 'green' => 0xFC, 'blue' => 0x00 ),
		'lemonchiffon'         => array( 'red' => 0xFF, 'green' => 0xFA, 'blue' => 0xCD ),
		'lightblue'            => array( 'red' => 0xAD, 'green' => 0xD8, 'blue' => 0xE6 ),
		'lightcoral'           => array( 'red' => 0xF0, 'green' => 0x80, 'blue' => 0x80 ),
		'lightcyan'            => array( 'red' => 0xE0, 'green' => 0xFF, 'blue' => 0xFF ),
		'lightgoldenrodyellow' => array( 'red' => 0xFA, 'green' => 0xFA, 'blue' => 0xD2 ),
		'lightgreen'           => array( 'red' => 0x90, 'green' => 0xEE, 'blue' => 0x90 ),
		'lightgrey'            => array( 'red' => 0xD3, 'green' => 0xD3, 'blue' => 0xD3 ),
		'lightpink'            => array( 'red' => 0xFF, 'green' => 0xB6, 'blue' => 0xC1 ),
		'lightsalmon'          => array( 'red' => 0xFF, 'green' => 0xA0, 'blue' => 0x7A ),
		'lightseagreen'        => array( 'red' => 0x20, 'green' => 0xB2, 'blue' => 0xAA ),
		'lightskyblue'         => array( 'red' => 0x87, 'green' => 0xCE, 'blue' => 0xFA ),
		'lightslategray'       => array( 'red' => 0x77, 'green' => 0x88, 'blue' => 0x99 ),
		'lightsteelblue'       => array( 'red' => 0xB0, 'green' => 0xC4, 'blue' => 0xDE ),
		'lightyellow'          => array( 'red' => 0xFF, 'green' => 0xFF, 'blue' => 0xE0 ),
		'limegreen'            => array( 'red' => 0x32, 'green' => 0xCD, 'blue' => 0x32 ),
		'linen'                => array( 'red' => 0xFA, 'green' => 0xF0, 'blue' => 0xE6 ),
		'mediumaquamarine'     => array( 'red' => 0x66, 'green' => 0xCD, 'blue' => 0xAA ),
		'mediumblue'           => array( 'red' => 0x00, 'green' => 0x00, 'blue' => 0xCD ),
		'mediumorchid'         => array( 'red' => 0xBA, 'green' => 0x55, 'blue' => 0xD3 ),
		'mediumpurple'         => array( 'red' => 0x93, 'green' => 0x70, 'blue' => 0xD0 ),
		'mediumseagreen'       => array( 'red' => 0x3C, 'green' => 0xB3, 'blue' => 0x71 ),
		'mediumslateblue'      => array( 'red' => 0x7B, 'green' => 0x68, 'blue' => 0xEE ),
		'mediumspringgreen'    => array( 'red' => 0x00, 'green' => 0xFA, 'blue' => 0x9A ),
		'mediumturquoise'      => array( 'red' => 0x48, 'green' => 0xD1, 'blue' => 0xCC ),
		'mediumvioletred'      => array( 'red' => 0xC7, 'green' => 0x15, 'blue' => 0x85 ),
		'midnightblue'         => array( 'red' => 0x19, 'green' => 0x19, 'blue' => 0x70 ),
		'mintcream'            => array( 'red' => 0xF5, 'green' => 0xFF, 'blue' => 0xFA ),
		'mistyrose'            => array( 'red' => 0xFF, 'green' => 0xE4, 'blue' => 0xE1 ),
		'moccasin'             => array( 'red' => 0xFF, 'green' => 0xE4, 'blue' => 0xB5 ),
		'navajowhite'          => array( 'red' => 0xFF, 'green' => 0xDE, 'blue' => 0xAD ),
		'oldlace'              => array( 'red' => 0xFD, 'green' => 0xF5, 'blue' => 0xE6 ),
		'olivedrab'            => array( 'red' => 0x6B, 'green' => 0x8E, 'blue' => 0x23 ),
		'orange'               => array( 'red' => 0xFF, 'green' => 0xA5, 'blue' => 0x00 ),
		'orangered'            => array( 'red' => 0xFF, 'green' => 0x45, 'blue' => 0x00 ),
		'orchid'               => array( 'red' => 0xDA, 'green' => 0x70, 'blue' => 0xD6 ),
		'palegoldenrod'        => array( 'red' => 0xEE, 'green' => 0xE8, 'blue' => 0xAA ),
		'palegreen'            => array( 'red' => 0x98, 'green' => 0xFB, 'blue' => 0x98 ),
		'paleturquoise'        => array( 'red' => 0xAF, 'green' => 0xEE, 'blue' => 0xEE ),
		'palevioletred'        => array( 'red' => 0xDB, 'green' => 0x70, 'blue' => 0x93 ),
		'papayawhip'           => array( 'red' => 0xFF, 'green' => 0xEF, 'blue' => 0xD5 ),
		'peachpuff'            => array( 'red' => 0xFF, 'green' => 0xDA, 'blue' => 0xB9 ),
		'peru'                 => array( 'red' => 0xCD, 'green' => 0x85, 'blue' => 0x3F ),
		'pink'                 => array( 'red' => 0xFF, 'green' => 0xC0, 'blue' => 0xCB ),
		'plum'                 => array( 'red' => 0xDD, 'green' => 0xA0, 'blue' => 0xDD ),
		'powderblue'           => array( 'red' => 0xB0, 'green' => 0xE0, 'blue' => 0xE6 ),
		'rosybrown'            => array( 'red' => 0xBC, 'green' => 0x8F, 'blue' => 0x8F ),
		'royalblue'            => array( 'red' => 0x41, 'green' => 0x69, 'blue' => 0xE1 ),
		'saddlebrown'          => array( 'red' => 0x8B, 'green' => 0x45, 'blue' => 0x13 ),
		'salmon'               => array( 'red' => 0xFA, 'green' => 0x80, 'blue' => 0x72 ),
		'sandybrown'           => array( 'red' => 0xF4, 'green' => 0xA4, 'blue' => 0x60 ),
		'seagreen'             => array( 'red' => 0x2E, 'green' => 0x8B, 'blue' => 0x57 ),
		'seashell'             => array( 'red' => 0xFF, 'green' => 0xF5, 'blue' => 0xEE ),
		'sienna'               => array( 'red' => 0xA0, 'green' => 0x52, 'blue' => 0x2D ),
		'skyblue'              => array( 'red' => 0x87, 'green' => 0xCE, 'blue' => 0xEB ),
		'slateblue'            => array( 'red' => 0x6A, 'green' => 0x5A, 'blue' => 0xCD ),
		'slategray'            => array( 'red' => 0x70, 'green' => 0x80, 'blue' => 0x90 ),
		'snow'                 => array( 'red' => 0xFF, 'green' => 0xFA, 'blue' => 0xFA ),
		'springgreen'          => array( 'red' => 0x00, 'green' => 0xFF, 'blue' => 0x7F ),
		'steelblue'            => array( 'red' => 0x46, 'green' => 0x82, 'blue' => 0xB4 ),
		'tan'                  => array( 'red' => 0xD2, 'green' => 0xB4, 'blue' => 0x8C ),
		'thistle'              => array( 'red' => 0xD8, 'green' => 0xBF, 'blue' => 0xD8 ),
		'tomato'               => array( 'red' => 0xFF, 'green' => 0x63, 'blue' => 0x47 ),
		'turquoise'            => array( 'red' => 0x40, 'green' => 0xE0, 'blue' => 0xD0 ),
		'violet'               => array( 'red' => 0xEE, 'green' => 0x82, 'blue' => 0xEE ),
		'wheat'                => array( 'red' => 0xF5, 'green' => 0xDE, 'blue' => 0xB3 ),
		'whitesmoke'           => array( 'red' => 0xF5, 'green' => 0xF5, 'blue' => 0xF5 ),
		'yellowgreen'          => array( 'red' => 0x9A, 'green' => 0xCD, 'blue' => 0x32 ),
	);

	/**
	 * Ensures that any hex color is properly hashed.
	 * Otherwise, returns value unaltered.
	 *
	 * This function is borrowed from the class_wp_customize_manager.php
	 * file in WordPress core.
	 *
	 * @since 8.1
	 * @deprecated 10.4.19
	 * @see maybe_hash_hex_color()
	 *
	 * @param string $color
	 *
	 * @return string
	 */
	public static function maybeHashHEXColor( $color ) {

		_deprecated_function( __METHOD__, '10.4.19', 'maybe_hash_hex_color()' );

		return maybe_hash_hex_color( $color );
	}

	/**
	 * Convert named color to RGB.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $name Named color.
	 * @param  bool   $returnAsString Whether or not to return the RGB as an array or string, default is array.
	 * @param  string $separator      Used to separate RGB values. Applicable only if second parameter is TRUE.
	 *
	 * @return mixed  array|string  RGB will be turned as an array unless $returnAsString is TRUE.
	 */
	public static function name2rgb( $name, $returnAsString = false, $separator = ',' ) {

		$name = strtolower( $name );

		$rgb = isset( self::$colors[ $name ] ) ? self::$colors[ $name ] : false;

		if ( $rgb ) {

			// Returns the RGB string or associative array.
			return $returnAsString ? implode( $separator, $rgb ) : $rgb;

		} else {

			return false;
		}
	}

	/**
	 * Convert named color to HEX.
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $name Named color.
	 *
	 * @return mixed bool|string False if named color does not have a defined color separation.
	 */
	public static function name2hex( $name ) {

		$name = strtolower( $name );
		$rgb  = self::name2rgb( $name, true );

		if ( $rgb ) {

			return self::rgb2hex2rgb( $rgb );

		} else {

			return false;
		}
	}

	/**
	 * Convert HEX to RGB or RGB to HEX.
	 *
	 * @link   http://php.net/manual/en/function.hexdec.php#93835
	 * @link   http://php.net/manual/en/function.hexdec.php#99478
	 *
	 * @access public
	 * @since  8.1
	 * @static
	 *
	 * @param  string $color          HEX or RGB string. HEX can be long or short with/without the has. RGB can be separated by space, comma or period.
	 * @param  bool   $returnAsString Whether or not to return the HEX to RGB as an array or string, default is array.
	 * @param  string $separator      Used to separate RGB values. Applicable only if second parameter is TRUE.
	 *
	 * @return mixed  array|bool|string HEX to RGB will be turned as an array unless $returnAsString is TRUE. RGB to HEX will be returned as a string. False on failure.
	 */
	public static function rgb2hex2rgb( $color, $returnAsString = false, $separator = ',' ) {

		if ( ! $color ) {
			return false;
		}

		$color = trim( $color );

		if ( preg_match( '/^[0-9ABCDEFabcdef\#]+$/i', $color ) ) {

			$color = preg_replace( '/[^0-9A-Fa-f]/', '', $color ); // Gets a proper hex string
			$rgb   = array();

			// If a proper hex code, convert using bitwise operation. No overhead... faster
			if ( 6 == strlen( $color ) ) {

				$colorVal     = hexdec( $color );
				$rgb['red']   = 0xFF & ( $colorVal >> 0x10 );
				$rgb['green'] = 0xFF & ( $colorVal >> 0x8 );
				$rgb['blue']  = 0xFF & $colorVal;

				// Returns the RGB string or associative array.
				$out = $returnAsString ? implode( $separator, $rgb ) : $rgb;

				// If shorthand notation, need some string manipulations.
			} elseif ( 3 == strlen( $color ) ) {

				$rgb['red']   = hexdec( str_repeat( substr( $color, 0, 1 ), 2 ) );
				$rgb['green'] = hexdec( str_repeat( substr( $color, 1, 1 ), 2 ) );
				$rgb['blue']  = hexdec( str_repeat( substr( $color, 2, 1 ), 2 ) );

				// Returns the RGB string or associative array.
				$out = $returnAsString ? implode( $separator, $rgb ) : $rgb;

				// Invalid hex color code.
			} else {

				$out = false;
			}

		} elseif ( preg_match( '/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i', $color ) ) {

			$spr = str_replace( array( ',', ' ', '.' ), ':', $color );
			$e   = explode( ':', $spr );

			if ( 3 != count( $e ) ) {

				return false;
			}

			$out = '#';

			for ( $i = 0; $i < 3; $i++ ) {

				$e[ $i ] = dechex( ( $e[ $i ] <= 0 ) ? 0 : ( ( $e[ $i ] >= 255 ) ? 255 : $e[ $i ] ) );
			}

			for ( $i = 0; $i < 3; $i++ ) {

				$out .= ( ( strlen( $e[ $i ] ) < 2 ) ? '0' : '' ) . $e[ $i ];
			}

			$out = strtoupper( $out );

		} else {

			$out = false;
		}

		return $out;
	}
}
