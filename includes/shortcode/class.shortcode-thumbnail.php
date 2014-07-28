<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * The [cn_thumnb] shortcode.
 *
 * @package     Connections Thumbnail
 * @subpackage  Shortcode API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

class cnThumb extends cnShortcode {

	public static function bfiThumb() {

		$url = bfi_thumb( 'http://sandbox.connections-pro.com/wp-content/uploads/parakeet.jpg', array( 'width' => 200, 'height' => 200, 'crop' => array( 0.2, 0.9 ) ) );

		return '<img src="' . $url . '" width="200" height="200" />';
	}

	public static function shortcode( $atts, $content = '', $tag = 'cn_thumnb' ) {

		$defaults = array(
			'align'         => 'alignnone',
			'id'            => NULL,
			'entry_id'      => NULL,
			'url'           => NULL,
			'height'        => 0,
			'width'         => 0,
			'negate'        => FALSE,
			'grayscale'     => FALSE,
			'brightness'    => 0,
			'colorize'      => NULL,
			'contrast'      => 0,
			'detect_edges'  => FALSE,
			'emboss'        => FALSE,
			'gaussian_blur' => FALSE,
			'blur'          => FALSE,
			'sketchy'       => FALSE,
			'sharpen'       => FALSE,
			'smooth'        => NULL,
			'opacity'       => 100,
			'crop_mode'     => 1,
			'crop_focus'    => array( .5, .5 ),
			'crop_only'     => FALSE,
			'canvas_color'  => '#FFFFFF',
			'quality'       => 90,
		);

		$defaults = apply_filters( 'cn_thumb_shortcode_atts', $defaults );

		$atts = shortcode_atts( $defaults, $atts, $tag ) ;

		/*
		 * Convert some of the $atts values in the array to boolean because the Shortcode API passes all values as strings.
		 */
		cnFormatting::toBoolean( $atts['negate'] );
		cnFormatting::toBoolean( $atts['grayscale'] );
		cnFormatting::toBoolean( $atts['detect_edges'] );
		cnFormatting::toBoolean( $atts['emboss'] );
		cnFormatting::toBoolean( $atts['gaussian_blur'] );
		cnFormatting::toBoolean( $atts['blur'] );
		cnFormatting::toBoolean( $atts['sketchy'] );
		cnFormatting::toBoolean( $atts['sharpen'] );

		// cnFormatting::toBoolean( $atts['crop'] );
		cnFormatting::toBoolean( $atts['crop_only'] );

		$image = cnImage::get( $atts['url'], $atts, FALSE );

		if ( is_wp_error( $image ) ) {

			// Display the error messages.
			return implode( PHP_EOL, $image->get_error_messages() );

		} elseif ( $image === FALSE ) {

			return __( 'An error has occured while creating the thumbnail.', 'connections' );
		}


		$out = img_caption_shortcode(
			array(
				'align' => $atts['align'],
				'width' => $image['width'],
			),
			'<img src="' . $image['url'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '" />' . $image['filename']
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === TRUE ) {

			$out = $out . '<pre>' . $image['log'] . '</pre>';
		}

		return $out;
	}

}
