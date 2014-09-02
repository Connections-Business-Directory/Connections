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

				// From: http://stackoverflow.com/questions/3538851/php-imagick-setimageopacity-destroys-transparency-and-does-nothing
				// preserves transparency
				$this->image->setImageOpacity( $level );
				$this->image->evaluateImage( Imagick::EVALUATE_MULTIPLY, $level, Imagick::CHANNEL_ALPHA );

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
	 * @param string hex color e.g. #ff00ff
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
