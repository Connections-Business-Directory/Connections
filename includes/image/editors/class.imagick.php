<?php

class CN_Image_Editor_Imagick extends WP_Image_Editor_Imagick {

	/**
	 * Gets the currently set image quality value that will be used when saving a file.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return int The image quality value.
	 */
	public function get_quality() {

		return $this->quality;
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

			try {

				/**
				 * NOTES:
				 *
				 * Few hosts seem to have the Imagick PHP library installed and those that do seem to have an
				 * older version which does not have the @see Imagick::setImageOpacity() method. So this causes
				 * a fatal error because the try/catch does not seem to catch calling methods that do not exist.
				 *
				 * Even if the host does have a newer version of Imagick installed it seems that
				 * @see Imagick::setImageOpacity() is not a great solution since it will destroy the transparency of
				 * images that contain it and it does not seem to work on PNG source images (I have not verified the later).
				 *
				 * ref: @link http://stackoverflow.com/questions/3538851/php-imagick-setimageopacity-destroys-transparency-and-does-nothing
				 *
				 * The solution provided in the above link can also not be replied upon because it seems to only work on
				 * image with transparency (I have not verified).
				 *
				 * ref: @link http://stackoverflow.com/questions/24350043/change-opacity-of-image-using-imagick
				 *
				 * Looking for a universal working solution I found this
				 * @link http://catch404.net/2012/07/making-images-transparent-in-imagick-enter-the-pixel-iterator/
				 *
				 * I'm going to change the code to that solution but I can not test because my current host only has
				 * Imagick available via the command line. Crosses fingers :/ ...
				 */

				//if ( method_exists( $this->image, 'setImageOpacity' ) ) $this->image->setImageOpacity( $level );
				//$this->image->evaluateImage( Imagick::EVALUATE_MULTIPLY, $level, Imagick::CHANNEL_ALPHA );

				// Add the alpha channel
				/** @todo Text how this affects an image which already has an alpha channel. */
				$this->image->setImageAlphaChannel( Imagick::ALPHACHANNEL_ACTIVATE );

				/** @var ImagickPixelIterator $pixelIterator */
				$pixelIterator = $this->image->getPixelIterator();

				// Loop trough pixel rows.
				foreach ( $pixelIterator as $row => $pixels ) {

					// Loop through the pixels in the row (columns).
					/** @var ImagickPixel $pixel */
					foreach ( $pixels as $column => $pixel ) {

						// Determine the pixel's current transparency level.
						$current = $pixel->getColorValue( Imagick::COLOR_ALPHA );

						// Only make the pixel more transparent than it may already be
						// if the resulting value is greater than fully transparent.
						$pixel->setColorValue(
							Imagick::COLOR_ALPHA,
							( $current - $level > 0 ? $current - $level : 0 )
						);
					}
					// Sync the change made to the pixel object to the actual image,
					// via the iterator that created the pixel object,
					// this is important to do on each iteration.
					$pixelIterator->syncIterator();
				}

				return TRUE;

			} catch ( Exception $e ) {

				return new WP_Error( 'image_opacity_error', $e->getMessage(), $this->file );
			}
		}

		return new WP_Error( 'image_opacity_error', __( 'Image opacity change failed.', 'connections' ), $this->file );
	}

	/**
	 * Tints the image a different color
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @param string $hexColor hex color e.g. #ff00ff
	 * @return boolean|WP_Error
	 */
	public function colorize( $hexColor ) {

		$hexColor = cnSanitize::hexColor( $hexColor );

		if ( empty( $hexColor ) ) {

			return new WP_Error( 'image_colorize_error', __( 'Value passed to ' . get_class( $this ) . '::colorize() is an invalid hex color.', 'connections' ), $this->file );
		}

		try {

			// When you're using an image with an alpha channel (for example a transparent png), a value of 1.0 will return a completely transparent image,
			// but a value of 1 works just fine.
			// ref: http://php.net/manual/en/imagick.colorizeimage.php#107012

			return $this->image->colorizeImage( $hexColor, 1 );

		} catch ( Exception $e ) {

			return new WP_Error( 'image_colorize_error', $e->getMessage(), $this->file );
		}

	}

	/**
	 * Convert the image to grayscale.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return object Imagick | WP_Error
	 */
	public function grayscale() {

		try {

			return $this->image->modulateImage( 100, 0,100 );

		} catch ( Exception $e ) {

			return new WP_Error( 'image_grayscale_error', $e->getMessage(), $this->file );
		}
	}

	/**
	 * Negates the image.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return object Imagick | WP_Error
	 */
	public function negate() {

		try {

			return $this->image->negateImage(FALSE);

		} catch ( Exception $e ) {

			return new WP_Error( 'image_negate_error', $e->getMessage(), $this->file );
		}
	}

	/**
	 * Adjust the image brightness.
	 *
	 * @access public
	 * @since  8.1
	 * @param  integer $level -255 = min brightness, 0 = no change, +255 = max brightness
	 *
	 * @return object         Imagick | WP_Error
	 */
	public function brightness( $level ) {

		$level = (float) cnUtility::remapRange( $level, -255, 255, 0, 200 );

		if ( ( $level >= 0 ) && ( $level <= 200 ) && ( filter_var( $level, FILTER_VALIDATE_FLOAT ) !== FALSE ) ) {

			try {

				return $this->image->modulateImage( $level, 100, 100 );

			} catch ( Exception $e ) {

				return new WP_Error( 'image_brightness_error', $e->getMessage(), $this->file );
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
	 * @return object         Imagick | WP_Error
	 */
	public function contrast( $level ) {

		if ( ( $level >= -100 ) && ( $level <= 100 ) && ( filter_var( $level, FILTER_VALIDATE_FLOAT ) !== FALSE ) ) {

			try {

				$sharpen  = $level <= 0 ? TRUE : FALSE;
				$midpoint = cnUtility::remapRange( $level, -100, 100, -20, 20 );
				$quanta   = $this->image->getQuantumRange();

				return $this->image->sigmoidalContrastImage( $sharpen, abs( $midpoint ), .5 * $quanta["quantumRangeLong"] );

			} catch ( Exception $e ) {

				return new WP_Error( 'image_contrast_error', $e->getMessage(), $this->file );
			}

		}

		return new WP_Error( 'image_contrast_error', __( 'Image contrast failed.', 'connections' ), $this->file );
	}

	/**
	 * Apply the edge detection filter.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return object             Imagick | WP_Error
	 */
	public function detect_edges() {

		try {

			// The imagick edgeImage() function outputs a different result than GD,
			// so process the image further for a "close enough" match.
			$this->image->edgeImage( 1 );
			$this->image->negateImage( FALSE );
			$this->image->modulateImage( 100, 0, 100 );
			$this->image->colorizeImage( '#141414', 1 );
			$this->image->negateImage( TRUE );
			$this->image->gammaImage( 3.5 );

			return $this->image->adaptiveSharpenImage( 0, 1 );

		} catch ( Exception $e ) {

			return new WP_Error( 'image_edge_detect_error', $e->getMessage(), $this->file );
		}
	}

	/**
	 * Apply the emboss filter.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return object             Imagick | WP_Error
	 */
	public function emboss() {

		try {

			// The imagick embossImage() function outputs a different result than GD,
			// so process the image further for a "close enough" match.
			$this->image->embossImage( 0, 1 );
			$this->image->negateImage( FALSE );
			$this->image->modulateImage( 100, 0, 100 );
			$this->image->colorizeImage( '#969696', 1 );
			$this->image->negateImage( TRUE );
			// $this->image->gammaImage( 3.5 );

			return $this->image->adaptiveSharpenImage( 0, 1 );

		} catch ( Exception $e ) {

			return new WP_Error( 'image_emboss_error', $e->getMessage(), $this->file );
		}
	}

	/**
	 * Apply a gaussian blur.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return object             Imagick | WP_Error
	 */
	public function gaussian_blur() {

		try {

			return $this->image->gaussianBlurImage( 0, 1 );

		} catch ( Exception $e ) {

			return new WP_Error( 'image_gaussian_blur_error', $e->getMessage(), $this->file );
		}
	}

	/**
	 * Apply a blur.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return object             Imagick | WP_Error
	 */
	public function blur() {

		try {

			return $this->image->blurImage( 0, 1 );

		} catch (Exception $e) {

			return new WP_Error( 'image_gaussian_blur_error', $e->getMessage(), $this->file );
		}
	}

	/**
	 * Sharpens an image.
	 *
	 * @access public
	 * @since  8.1
	 *
	 * @return object             Imagick | WP_Error
	 */
	public function sharpen() {

		try {

			return $this->image->sharpenImage( 0, 1 );

		} catch ( Exception $e ) {

			return new WP_Error( 'image_sharpen_error', $e->getMessage(), $this->file );
		}
	}

}
