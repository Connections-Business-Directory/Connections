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

class cnThumb_Responsive extends cnShortcode {

	public static function shortcode( $atts, $content = '', $tag = 'cn_thumbr' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$log       = array();
		$srcset    = array();
		$permitted = array( 'attachment', 'featured', 'path', 'url', 'logo', 'photo' );
		$defaults  = array(
			'type'          => 'url',
			'source'        => NULL,
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
			'sizes'         => '1024|640|320',
		);

		$defaults = apply_filters( 'cn_thumbr_shortcode_atts', $defaults );

		$atts = shortcode_atts( $defaults, $atts, $tag );

		if ( ! in_array( $atts['type'], $permitted ) ) {

			return __( 'Valid image source type not supplied.', 'connections' );
		}

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

		$atts['sizes'] = explode( '|', $atts['sizes'] );
		array_map( 'trim', $atts['sizes'] );
		array_map( 'absint', $atts['sizes'] );

		if ( empty( $atts['sizes']) ) {

			return __( 'No image sizes were supplied or supplied values were invalid.', 'connections' );
		}

		switch ( $atts['type'] ) {

			case 'attachment':

				$source = wp_get_attachment_url( absint( $atts['source'] ) );
				break;

			case 'featured':

				$source = wp_get_attachment_url( get_post_thumbnail_id() );
				break;

			case 'path':

				$source = $atts['source'];
				break;

			case 'url':

				$source = esc_url( $atts['source'] );
				break;

			case 'logo':

				$result = $instance->retrieve->entry( absint( $atts['source'] ) );

				$entry = new cnEntry( $result );

				$meta = $entry->getImageMeta( array( 'type' => 'logo' ) );

				if ( is_wp_error( $meta ) ) {

					// Display the error messages.
					return implode( PHP_EOL, $meta->get_error_messages() );
				}

				$source = $meta['url'];

				break;

			case 'photo':

				$result = $instance->retrieve->entry( absint( $atts['source'] ) );

				$entry = new cnEntry( $result );

				$meta = $entry->getImageMeta( array( 'type' => 'photo' ) );

				if ( is_wp_error( $meta ) ) {

					// Display the error messages.
					return implode( PHP_EOL, $meta->get_error_messages() );
				}

				$source = $meta['url'];

				break;
		}

		// Unset $atts['source'] because passing that $atts to cnImage::get() extracts and overwrite the $source var.
		unset( $atts['source'] );

		foreach ( $atts['sizes'] as $width ) {

			$atts['width'] = $width;

			$image = cnImage::get( $source, $atts, 'data' );

			if ( is_wp_error( $image ) ) {

				// Display the error messages.
				return implode( PHP_EOL, $image->get_error_messages() );

			} elseif ( $image === FALSE ) {

				return __( 'An error has occured while creating the thumbnail.', 'connections' );
			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG === TRUE ) {

				$log[] = '<pre>' . $image['log'] . '</pre>';
			}

			$srcset[] = $image['url'] . ' ' . $width . 'w';
		}

		$out = sprintf( '<img class="cn-image" srcset="%1$s" sizes="100vw"%2$s />',
			implode( ',', $srcset ),
			empty( $content ) ? '' : ' alt="' . esc_attr( $content ) . '"'
			);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === TRUE ) {

			$out .= implode( '', $log );
		}

		return $out;
	}

}
