<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	 * @access public
	 * @since  8.1
	 *
	 * @param  int|null $max_w Image width.
	 * @param  int|null $max_h Image height.
	 * @param  boolean  $crop
	 * @return boolean|WP_Error
	 */
	public function resize_padded( $canvas_w, $canvas_h, $canvas_color, $width, $height, $orig_w, $orig_h, $origin_x, $origin_y ) {

		// if ( ( $this->size['width'] == $max_w ) && ( $this->size['height'] == $max_h ) )
		// 	return true;

		$resized = $this->_resize_padded( $canvas_w, $canvas_h, $canvas_color, $width, $height, $orig_w, $orig_h, $origin_x, $origin_y );

		if ( is_resource( $resized ) ) {
			imagedestroy( $this->image );
			$this->image = $resized;
			return true;

		} elseif ( is_wp_error( $resized ) )
			return $resized;

		return new WP_Error( 'image_resize_error', __( 'Image resize failed.', 'connections' ), $this->file );
	}

	protected function _resize_padded( $canvas_w, $canvas_h, $canvas_color, $width, $height, $orig_w, $orig_h, $origin_x, $origin_y ) {

		$src_x = $src_y = 0;
		$src_w = $orig_w;
		$src_h = $orig_h;

		$cmp_x = $orig_w / $width;
		$cmp_y = $orig_h / $height;

		// calculate x or y coordinate and width or height of source
		if ($cmp_x > $cmp_y) {

			$src_w = round ($orig_w / $cmp_x * $cmp_y);
			$src_x = round (($orig_w - ($orig_w / $cmp_x * $cmp_y)) / 2);

		} else if ($cmp_y > $cmp_x) {

			$src_h = round ($orig_h / $cmp_y * $cmp_x);
			$src_y = round (($orig_h - ($orig_h / $cmp_y * $cmp_x)) / 2);

		}

		$resized = wp_imagecreatetruecolor( $canvas_w, $canvas_h );

		if ( $canvas_color === 'transparent' ) {

			$color = imagecolorallocatealpha( $resized, 255, 255, 255, 127 );

		} else {

			$rgb = cnColor::rgb2hex2rgb( $canvas_color );

			$color = imagecolorallocatealpha( $resized, $rgb['red'], $rgb['green'], $rgb['blue'], 0 );
		}

		// Fill the background of the new image with allocated color.
		imagefill( $resized, 0, 0, $color );

		// Restore transparency.
		imagesavealpha( $resized, TRUE );

		imagecopyresampled( $resized, $this->image, $origin_x, $origin_y, $src_x, $src_y, $width, $height, $src_w, $src_h );

		if ( is_resource( $resized ) ) {
			$this->update_size( $width, $height );
			return $resized;
		}

		return new WP_Error( 'image_resize_error', __( 'Image resize failed.', 'connections' ), $this->file );
	}

	/**
	 * Rotates current image counter-clockwise by $angle.
	 * Ported from image-edit.php
	 * Added presevation of alpha channels
	 *
	 * @access public
	 * @since 8.1
	 *
	 * @param float $angle
	 * @return boolean|WP_Error
	 */
	public function rotate( $angle ) {
		if ( function_exists('imagerotate') ) {
			$rotated = imagerotate( $this->image, $angle, 0 );

			// Add alpha blending
			imagealphablending($rotated, true);
			imagesavealpha($rotated, true);

			if ( is_resource( $rotated ) ) {
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
	 * @access public
	 * @since  8.1
	 *
	 * @param  int   $level 0–100
	 *
	 * @return mixed boolean | object WP_Error
	 */
	public function opacity( $level ) {

		if ( filter_var( $level, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0, 'max_range' => 100 ) ) ) !== FALSE ) {

			$level /= 100;

			$filtered = $this->_opacity( $this->image, $level );

			if ( is_resource( $filtered ) ) {

				// imagedestroy($this->image);
				$this->image = $filtered;

				return TRUE;
			}
		}

		return new WP_Error( 'image_opacity_error', __( 'Image opacity change failed.', 'connections' ), $this->file );
	}

	// from: http://php.net/manual/en/function.imagefilter.php
	// params: image resource id, opacity (eg. 0.0-1.0)
	protected function _opacity($image, $opacity) {
		if (!function_exists('imagealphablending') ||
			!function_exists('imagecolorat') ||
			!function_exists('imagecolorallocatealpha') ||
			!function_exists('imagesetpixel')) return false;

		//get image width and height
		$w = imagesx( $image );
		$h = imagesy( $image );

		//turn alpha blending off
		imagealphablending( $image, false );

		//find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha = 127;
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$alpha = (imagecolorat($image, $x, $y) >> 24 ) & 0xFF;
				if( $alpha < $minalpha ) {
					$minalpha = $alpha;
				}
			}
		}

		//loop through image pixels and modify alpha for each
		for ( $x = 0; $x < $w; $x++ ) {
			for ( $y = 0; $y < $h; $y++ ) {
				//get current alpha value (represents the TANSPARENCY!)
				$colorxy = imagecolorat( $image, $x, $y );
				$alpha = ( $colorxy >> 24 ) & 0xFF;
				//calculate new alpha
				if ( $minalpha !== 127 ) {
					$alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha );
				} else {
					$alpha += 127 * $opacity;
				}
				//get the color index with new alpha
				$alphacolorxy = imagecolorallocatealpha( $image, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
				//set pixel with the new color + opacity
				if(!imagesetpixel($image, $x, $y, $alphacolorxy)) {
					return false;
				}
			}
		}

		imagesavealpha($image, true);

		return $image;
	}

	/**
	 * Tints the image a different color
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @param string $hexColor color e.g. #ff00ff
	 * @return boolean|WP_Error
	 */
	public function colorize( $hexColor ) {

		$hexColor = cnSanitize::hexColor( $hexColor );

		if ( empty( $hexColor ) ) {

			return new WP_Error( 'image_colorize_error', __( 'Value passed to ' . get_class( $this ) . '::colorize() is an invalid hex color.', 'connections' ), $this->file );
		}

		if ( function_exists('imagefilter') &&
			 function_exists('imagesavealpha') &&
			 function_exists('imagealphablending') ) {

			$hexColor = preg_replace( '#^\##', '', $hexColor );

			$r = hexdec ( substr( $hexColor, 0, 2 ) );
			$g = hexdec ( substr( $hexColor, 2, 2 ) );
			$b = hexdec ( substr( $hexColor, 2, 2 ) );

			imagealphablending( $this->image, FALSE );

			if ( imagefilter( $this->image, IMG_FILTER_COLORIZE, $r, $g, $b, 0 ) ) {

				imagesavealpha( $this->image, TRUE );

				return TRUE;
			}
		}

		return new WP_Error( 'image_colorize_error', __( 'Image color change failed.', 'connections' ), $this->file );
	}

	/**
	 * Convert the image to grayscale.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return mixed          bool | object WP_Error
	 */
	public function grayscale() {

		if ( function_exists('imagefilter') ) {

			if ( imagefilter( $this->image, IMG_FILTER_GRAYSCALE ) ) {

				return TRUE;
			}

		}

		return new WP_Error( 'image_grayscale_error', __( 'Image grayscale failed.', 'connections' ), $this->file );
	}

	/**
	 * Negates the image.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return mixed          bool | object WP_Error
	 */
	public function negate() {

		if ( function_exists('imagefilter') ) {

			if ( imagefilter( $this->image, IMG_FILTER_NEGATE ) ) {

				return TRUE;
			}

		}

		return new WP_Error( 'image_negate_error', __( 'Image negate failed.', 'connections' ), $this->file );
	}

	/**
	 * Adjust the image brightness.
	 *
	 * @access public
	 * @since  8.1
	 * @param  integer $level -255 = min brightness, 0 = no change, +255 = max brightness
	 *
	 * @return mixed          bool | object WP_Error
	 */
	public function brightness( $level = 0 ) {

		if ( filter_var( (int) $level, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => -255, 'max_range' => 255 ) ) ) !== FALSE ) {

			if ( function_exists('imagefilter') ) {

				if ( imagefilter( $this->image, IMG_FILTER_BRIGHTNESS, $level ) ) {

					return TRUE;
				}
			}

		}

		return new WP_Error( 'image_brightness_error', __( 'Image brightness failed.', 'connections' ), $this->file );
	}

	/**
	 * Adjust the image contrast.
	 *
	 * @access public
	 * @since  8.1
	 * @param  integer $level -100 = max contrast, 0 = no change, +100 = min contrast (note the direction!)
	 *
	 * @return mixed          bool | object WP_Error
	 */
	public function contrast( $level = 0 ) {

		// Technically, the new range should be -100, -100 because that is what the IMG_FILTER_CONTRAST support,
		// but limiting it to -100, 80 more closely match the output from imagick.
		$level = cnUtility::remapRange( $level, -100, 100, -100, 80 );

		// Ensure we round up rather than down. This is to prevent over/under $level values.
		$level = $level >= 0 ? (int) ceil( $level ) : (int) floor( $level );

		if ( filter_var( $level, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => -100, 'max_range' => 80 ) ) ) !== FALSE ) {

			if ( function_exists('imagefilter') ) {

				if ( imagefilter( $this->image, IMG_FILTER_CONTRAST, $level ) ) {

					return TRUE;
				}
			}

		}

		return new WP_Error( 'image_contrast_error', __( 'Image contrast failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies the edge dection filter.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return mixed             bool | object WP_Error
	 */
	public function detect_edges() {

		if ( function_exists('imagefilter') ) {

			if ( imagefilter( $this->image, IMG_FILTER_EDGEDETECT ) && imagefilter( $this->image, IMG_FILTER_GRAYSCALE ) ) {

				return TRUE;
			}
		}

		return new WP_Error( 'image_edge_detect_error', __( 'Image edge detection failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies the edge dection filter.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return mixed             bool | object WP_Error
	 */
	public function emboss() {

		if ( function_exists('imagefilter') ) {

			if ( imagefilter( $this->image, IMG_FILTER_EMBOSS ) && imagefilter( $this->image, IMG_FILTER_GRAYSCALE ) ) {

				return TRUE;
			}
		}

		return new WP_Error( 'image_emboss_error', __( 'Image emboss failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies the gaussian blur.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return mixed             bool | object WP_Error
	 */
	public function gaussian_blur() {

		if ( function_exists('imagefilter') ) {

			if ( imagefilter( $this->image, IMG_FILTER_GAUSSIAN_BLUR ) ) {

				return TRUE;
			}
		}

		return new WP_Error( 'image_gaussian_blur_error', __( 'Image gaussian blur failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies a blur.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return mixed             bool | object WP_Error
	 */
	public function blur() {

		if ( function_exists('imagefilter') ) {

			if ( imagefilter( $this->image, IMG_FILTER_SELECTIVE_BLUR ) ) {

				return TRUE;
			}
		}

		return new WP_Error( 'image_blur_error', __( 'Image blur failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies a mean removal filter which applies a crappy "sketchy" effect, supposedly.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return mixed             bool | object WP_Error
	 */
	public function sketchy() {

		if ( function_exists('imagefilter') ) {

			if ( imagefilter( $this->image, IMG_FILTER_MEAN_REMOVAL ) ) {

				return TRUE;
			}
		}

		return new WP_Error( 'image_mean_removal_error', __( 'Image mean removal failed.', 'connections' ), $this->file );
	}

	/**
	 * Applies a smooth filter.
	 *
	 * @access public
	 * @since  8.1
	 * @url    http://www.tuxradar.com/practicalphp/11/2/15
	 * @param  integer $level    -100 = max smooth, 100 = min smooth
	 *
	 * @return mixed             bool | object WP_Error
	 */
	public function smooth( $level = 0 ) {

		$level = (float) cnUtility::remapRange( $level, -100, 100, -8, 8 );

		if ( ( $level >= -8 ) && ( $level <= 8 ) && ( filter_var( $level, FILTER_VALIDATE_FLOAT ) !== FALSE ) ) {

			if ( function_exists('imagefilter') ) {

				if ( imagefilter( $this->image, IMG_FILTER_SMOOTH, $level ) ) {

					return TRUE;
				}
			}

		}

		return new WP_Error( 'image_smooth_error', __( 'Image smooth failed.', 'connections' ), $this->file );
	}

	/**
	 * Sharpens an image.
	 *
	 * @access public
	 * @since  8.1
	 * @credit Ben Gillbanks and Mark Maunder authors of TimThumb
	 *
	 * @return mixed             bool | object WP_Error
	 */
	public function sharpen() {

		if ( function_exists('imageconvolution') ) {

			$matrix = array (
				array (-1,-1,-1),
				array (-1,16,-1),
				array (-1,-1,-1),
			);

			$divisor = 8;
			$offset  = 0;

			if ( imageconvolution( $this->image, $matrix, $divisor, $offset ) ) {

				return TRUE;
			}

		}

		return new WP_Error( 'image_sharpen_error', __( 'Image sharpen failed.', 'connections' ), $this->file );
	}

}
