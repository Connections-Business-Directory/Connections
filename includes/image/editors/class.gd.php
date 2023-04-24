<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_sanitize;

/**
 * Class CN_Image_Editor_GD
 */
class CN_Image_Editor_GD extends WP_Image_Editor_GD {

	/**
	 * Resizes current image padded.
	 * Wraps _resize_padded, since _resize_padded returns a GD Resource.
	 *
	 * At minimum, either a height or width must be provided.
	 * If one of the two is set to null, the resize will
	 * maintain aspect ratio according to the provided dimension.
	 *
	 * @since 8.1
	 *
	 * @param  int|null $max_w Image width.
	 * @param  int|null $max_h Image height.
	 * @param  boolean  $crop
	 *
	 * @return true|WP_Error
	 */
	public function resize_padded( $canvas_w, $canvas_h, $canvas_color, $width, $height, $orig_w, $orig_h, $origin_x, $origin_y ) {

		// if ( ( $this->size['width'] == $max_w ) && ( $this->size['height'] == $max_h ) )
		// 	return true;

		$resized = $this->_resize_padded( $canvas_w, $canvas_h, $canvas_color, $width, $height, $orig_w, $orig_h, $origin_x, $origin_y );

		if ( is_gd_image( $resized ) ) {
			imagedestroy( $this->image );
			$this->image = $resized;
			return true;

		} elseif ( is_wp_error( $resized ) ) {
			return $resized;
		}

		return new WP_Error( 'image_resize_error', __( 'Image resize failed.', 'connections' ), $this->file );
	}

	protected function _resize_padded( $canvas_w, $canvas_h, $canvas_color, $width, $height, $orig_w, $orig_h, $origin_x, $origin_y ) {

		$src_x = $src_y = 0;
		$src_w = $orig_w;
		$src_h = $orig_h;

		$cmp_x = $orig_w / $width;
		$cmp_y = $orig_h / $height;

		// Calculate x or y coordinate and width or height of source.
		if ( $cmp_x > $cmp_y ) {

			$src_w = round( $orig_w / $cmp_x * $cmp_y );
			$src_x = round( ( $orig_w - ( $orig_w / $cmp_x * $cmp_y ) ) / 2 );

		} elseif ( $cmp_y > $cmp_x ) {

			$src_h = round( $orig_h / $cmp_y * $cmp_x );
			$src_y = round( ( $orig_h - ( $orig_h / $cmp_y * $cmp_x ) ) / 2 );

		}

		$resized = wp_imagecreatetruecolor( $canvas_w, $canvas_h );

		if ( 'transparent' === $canvas_color ) {

			$color = imagecolorallocatealpha( $resized, 255, 255, 255, 127 );

		} else {

			$rgb = cnColor::rgb2hex2rgb( $canvas_color );

			$color = imagecolorallocatealpha( $resized, $rgb['red'], $rgb['green'], $rgb['blue'], 0 );
		}

		// Fill the background of the new image with allocated color.
		imagefill( $resized, 0, 0, $color );

		// Restore transparency.
		imagesavealpha( $resized, true );

		imagecopyresampled( $resized, $this->image, $origin_x, $origin_y, $src_x, $src_y, $width, $height, $src_w, $src_h );

		if ( is_gd_image( $resized ) ) {
			$this->update_size( $width, $height );
			return $resized;
		}

		return new WP_Error( 'image_resize_error', __( '_Image resize failed.', 'connections' ), $this->file );
	}

	/**
	 * Rotates current image counter-clockwise by $angle.
	 * Ported from image-edit.php
	 * Added preservation of alpha channels
	 *
	 * @since 8.1
	 *
	 * @param float $angle
	 *
	 * @return true|WP_Error
	 */
	public function rotate( $angle ) {
		if ( function_exists( 'imagerotate' ) ) {
			$rotated = imagerotate( $this->image, $angle, 0 );

			// Add alpha blending.
			imagealphablending( $rotated, true );
			imagesavealpha( $rotated, true );

			if ( is_gd_image( $rotated ) ) {
				imagedestroy( $this->image );
				$this->image = $rotated;
				$this->update_size();
				return true;
			}
		}
		return new WP_Error( 'image_rotate_error', __( 'Image rotate failed.', 'connections' ), $this->file );
	}

	/**
	 * Changes the opacity of the image
	 *
	 * @since 8.1
	 *
	 * @param int $level 0–100
	 *
	 * @return true|WP_Error
	 */
	public function opacity( $level ) {

		if ( filter_var( $level, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0, 'max_range' => 100 ) ) ) !== false ) {

			$level /= 100;

			$filtered = $this->_opacity( $this->image, $level );

			if ( is_gd_image( $filtered ) ) {

				// imagedestroy($this->image);
				$this->image = $filtered;

				return true;
			}
		}

		return new WP_Error( 'image_opacity_error', __( 'Image opacity change failed.', 'connections' ), $this->file );
	}

	/**
	 * Apply image opacity.
	 *
	 * @link http://php.net/manual/en/function.imagefilter.php
	 *
	 * @param GdImage $image   Instance of GdImage resource object.
	 * @param int     $opacity Opacity value between 0.0–1.0.
	 *
	 * @return false
	 */
	protected function _opacity( $image, $opacity ) {
		if ( ! function_exists( 'imagealphablending' ) ||
			 ! function_exists( 'imagecolorat' ) ||
			 ! function_exists( 'imagecolorallocatealpha' ) ||
			 ! function_exists( 'imagesetpixel' ) ) {
			return false;
		}

		// Get image width and height.
		$w = imagesx( $image );
		$h = imagesy( $image );

		// Turn alpha blending off.
		imagealphablending( $image, false );

		// Find the most opaque pixel in the image (the one with the smallest alpha value).
		$minalpha = 127;
		for ( $x = 0; $x < $w; $x++ ) {
			for ( $y = 0; $y < $h; $y++ ) {
				$alpha = ( imagecolorat( $image, $x, $y ) >> 24 ) & 0xFF;
				if ( $alpha < $minalpha ) {
					$minalpha = $alpha;
				}
			}
		}

		// Loop through image pixels and modify alpha for each.
		for ( $x = 0; $x < $w; $x++ ) {
			for ( $y = 0; $y < $h; $y++ ) {
				// Get current alpha value (represents the TRANSPARENCY!).
				$colorxy = imagecolorat( $image, $x, $y );
				$alpha   = ( $colorxy >> 24 ) & 0xFF;
				// Calculate new alpha.
				if ( 127 !== $minalpha ) {
					$alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha );
				} else {
					$alpha += 127 * $opacity;
				}
				// Get the color index with new alpha.
				$alphacolorxy = imagecolorallocatealpha( $image, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
				// Set pixel with the new color + opacity.
				if ( ! imagesetpixel( $image, $x, $y, $alphacolorxy ) ) {
					return false;
				}
			}
		}

		imagesavealpha( $image, true );

		return $image;
	}

	/**
	 * Tints the image a different color
	 *
	 * @since 8.1
	 *
	 * @param string $hexColor color e.g. #ff00ff
	 *
	 * @return true|WP_Error
	 */
	public function colorize( $hexColor ) {

		$hexColor = _sanitize::hexColor( $hexColor );

		if ( empty( $hexColor ) ) {

			return new WP_Error(
				'image_colorize_error',
				/* translators: Class method name. */
				sprintf( __( 'Value passed to %s::colorize() is an invalid hex color.', 'connections' ), get_class( $this ) ),
				$this->file
			);
		}

		if ( function_exists( 'imagefilter' ) &&
			 function_exists( 'imagesavealpha' ) &&
			 function_exists( 'imagealphablending' ) ) {

			$hexColor = preg_replace( '#^\##', '', $hexColor );

			$r = hexdec( substr( $hexColor, 0, 2 ) );
			$g = hexdec( substr( $hexColor, 2, 2 ) );
			$b = hexdec( substr( $hexColor, 2, 2 ) );

			imagealphablending( $this->image, false );

			if ( imagefilter( $this->image, IMG_FILTER_COLORIZE, $r, $g, $b, 0 ) ) {

				imagesavealpha( $this->image, true );

				return true;
			}
		}

		return new WP_Error( 'image_colorize_error', __( 'Image color change failed.', 'connections' ), $this->file );
	}

	/**
	 * Convert the image to grayscale.
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function grayscale() {

		if ( function_exists( 'imagefilter' ) ) {

			if ( imagefilter( $this->image, IMG_FILTER_GRAYSCALE ) ) {

				return true;
			}

		}

		return new WP_Error( 'image_grayscale_error', __( 'Image grayscale failed.', 'connections' ), $this->file );
	}

	/**
	 * Negates the image.
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function negate() {

		if ( function_exists( 'imagefilter' ) ) {

			if ( imagefilter( $this->image, IMG_FILTER_NEGATE ) ) {

				return true;
			}

		}

		return new WP_Error( 'image_negate_error', __( 'Image negate failed.', 'connections' ), $this->file );
	}

	/**
	 * Adjust the image brightness.
	 *
	 * @since 8.1
	 *
	 * @param integer $level -255 = min brightness, 0 = no change, +255 = max brightness
	 *
	 * @return true|WP_Error
	 */
	public function brightness( $level = 0 ) {

		if ( filter_var( (int) $level, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => -255, 'max_range' => 255 ) ) ) !== false ) {

			if ( function_exists( 'imagefilter' ) ) {

				if ( imagefilter( $this->image, IMG_FILTER_BRIGHTNESS, $level ) ) {

					return true;
				}
			}

		}

		return new WP_Error( 'image_brightness_error', __( 'Image brightness failed.', 'connections' ), $this->file );
	}

	/**
	 * Adjust the image contrast.
	 *
	 * @since 8.1
	 *
	 * @param integer $level -100 = max contrast, 0 = no change, +100 = min contrast (note the direction!).
	 *
	 * @return true|WP_Error
	 */
	public function contrast( $level = 0 ) {

		// Technically, the new range should be -100, -100 because that is what the IMG_FILTER_CONTRAST support,
		// but limiting it to -100, 80 more closely match the output from imagick.
		$level = _::remapRange( $level, -100, 100, -100, 80 );

		// Ensure we round up rather than down. This is to prevent over/under $level values.
		$level = $level >= 0 ? (int) ceil( $level ) : (int) floor( $level );

		if ( filter_var( $level, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => -100, 'max_range' => 80 ) ) ) !== false ) {

			if ( function_exists( 'imagefilter' ) ) {

				if ( imagefilter( $this->image, IMG_FILTER_CONTRAST, $level ) ) {

					return true;
				}
			}

		}

		return new WP_Error( 'image_contrast_error', __( 'Image contrast failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies the edge detection filter.
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function detect_edges() {

		if ( function_exists( 'imagefilter' ) ) {

			if ( imagefilter( $this->image, IMG_FILTER_EDGEDETECT ) && imagefilter( $this->image, IMG_FILTER_GRAYSCALE ) ) {

				return true;
			}
		}

		return new WP_Error( 'image_edge_detect_error', __( 'Image edge detection failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies the edge detection filter.
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function emboss() {

		if ( function_exists( 'imagefilter' ) ) {

			if ( imagefilter( $this->image, IMG_FILTER_EMBOSS ) && imagefilter( $this->image, IMG_FILTER_GRAYSCALE ) ) {

				return true;
			}
		}

		return new WP_Error( 'image_emboss_error', __( 'Image emboss failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies the gaussian blur.
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function gaussian_blur() {

		if ( function_exists( 'imagefilter' ) ) {

			if ( imagefilter( $this->image, IMG_FILTER_GAUSSIAN_BLUR ) ) {

				return true;
			}
		}

		return new WP_Error( 'image_gaussian_blur_error', __( 'Image gaussian blur failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies a blur.
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function blur() {

		if ( function_exists( 'imagefilter' ) ) {

			if ( imagefilter( $this->image, IMG_FILTER_SELECTIVE_BLUR ) ) {

				return true;
			}
		}

		return new WP_Error( 'image_blur_error', __( 'Image blur failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies a mean removal filter which applies a crappy "sketchy" effect, supposedly.
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function sketchy() {

		if ( function_exists( 'imagefilter' ) ) {

			if ( imagefilter( $this->image, IMG_FILTER_MEAN_REMOVAL ) ) {

				return true;
			}
		}

		return new WP_Error( 'image_mean_removal_error', __( 'Image mean removal failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies a smooth filter.
	 *
	 * @link http://www.tuxradar.com/practicalphp/11/2/15
	 *
	 * @since 8.1
	 *
	 * @param integer $level -100 = max smooth, 100 = min smooth
	 *
	 * @return true|WP_Error
	 */
	public function smooth( $level = 0 ) {

		$level = (float) _::remapRange( $level, -100, 100, -8, 8 );

		if ( ( $level >= -8 ) && ( $level <= 8 ) && ( filter_var( $level, FILTER_VALIDATE_FLOAT ) !== false ) ) {

			if ( function_exists( 'imagefilter' ) ) {

				if ( imagefilter( $this->image, IMG_FILTER_SMOOTH, $level ) ) {

					return true;
				}
			}

		}

		return new WP_Error( 'image_smooth_error', __( 'Image smooth failed.', 'connections' ), $this->file );
	}

	/**
	 * Sharpens an image.
	 *
	 * @credit Ben Gillbanks and Mark Maunder authors of TimThumb
	 *
	 * @since 8.1
	 *
	 * @return true|WP_Error
	 */
	public function sharpen() {

		if ( function_exists( 'imageconvolution' ) ) {

			$matrix = array(
				array( -1, -1, -1 ),
				array( -1, 16, -1 ),
				array( -1, -1, -1 ),
			);

			$divisor = 8;
			$offset  = 0;

			if ( imageconvolution( $this->image, $matrix, $divisor, $offset ) ) {

				return true;
			}

		}

		return new WP_Error( 'image_sharpen_error', __( 'Image sharpen failed.', 'connections' ), $this->file );
	}
}
