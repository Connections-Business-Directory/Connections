<?php
/**
 * WordPress Gmagick Image Editor
 *
 * @package WordPress
 * @subpackage Image_Editor
 */

/**
 * WordPress Image Editor Class for Image Manipulation through Gmagick PHP Module
 *
 * @since 3.5.0
 * @package WordPress
 * @subpackage Image_Editor
 * @uses WP_Image_Editor Extends class
 */
class WP_Image_Editor_Gmagick extends WP_Image_Editor {

	protected $image = null; // Gmagick Object

	function __destruct() {
		if ( $this->image instanceof Gmagick ) {
			// we don't need the original in memory anymore
			$this->image->clear();
			$this->image->destroy();
		}
	}

	/**
	 * Checks to see if current environment supports Gmagick.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @return boolean
	 */
	public static function test( $args = array() ) {
		// Test Gmagick's extension and class.
		if ( ! extension_loaded( 'gmagick' ) || ! class_exists( 'Gmagick' ) )
			return false;

		if ( array_diff( array( 'setcompressionquality', 'getimage' ), get_class_methods( 'Gmagick' ) ) )
			return false;

		return true;
	}

	/**
	 * Checks to see if editor supports the mime-type specified.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $mime_type
	 * @return boolean
	 */
	public static function supports_mime_type( $mime_type ) {
		$gmagick_extension = strtoupper( self::get_extension( $mime_type ) );

		if ( ! $gmagick_extension )
			return false;

		// setimageindex is optional unless mime is an animated format.
		// Here, we just say no if you are missing it and aren't loading a jpeg.
		if ( ! method_exists( 'Gmagick', 'setimageindex' ) && $mime_type != 'image/jpeg' )
				return false;

		try {
			$gmagick = new Gmagick;
			return ( (bool) $gmagick->queryformats( $gmagick_extension ) );
		}
		catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Loads image from $this->file into new Gmagick Object.
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @return boolean|WP_Error True if loaded; WP_Error on failure.
	 */
	public function load() {
		if ( $this->image instanceof Gmagick )
			return true;

		if ( ! is_file( $this->file ) && ! preg_match( '|^https?://|', $this->file ) )
			return new WP_Error( 'error_loading_image', __( 'File doesn&#8217;t exist?', 'connections' ), $this->file );

		try {
			$this->image = new Gmagick( $this->file );

			// Select the first frame to handle animated images properly
			if ( is_callable( array( $this->image, 'setimageindex' ) ) )
				$this->image->setimageindex(0);

			$this->mime_type = $this->get_mime_type( $this->image->getimageformat() );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'invalid_image', $e->getMessage(), $this->file );
		}

		$updated_size = $this->update_size();
		if ( is_wp_error( $updated_size ) )
				return $updated_size;

		return $this->set_quality( $this->quality );
	}

	/**
	 * Sets Image Compression quality on a 1-100% scale.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param int $quality Compression Quality. Range: [1,100]
	 * @return boolean|WP_Error
	 */
	public function set_quality( $quality = null ) {
		$quality_result = parent::set_quality( $quality );

		if ( is_wp_error( $quality_result ) ) {
			return $quality_result;
		}
		else {
			$quality = $this->quality;
		}

		try {
			$this->image->setcompressionquality( $quality );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_quality_error', $e->getMessage() );
		}

		return true;
	}

	/**
	 * Sets or updates current image size.
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @param int $width
	 * @param int $height
	 */
	protected function update_size( $width = null, $height = null ) {
		$size = null;

		if ( ! $width || ! $height ) {
			try {
				$size = array(
					'width' => $this->image->getimagewidth(),
					'height' => $this->image->getimageheight()
				);
			}
			catch ( Exception $e ) {
				return new WP_Error( 'invalid_image', __( 'Could not read image size', 'connections' ), $this->file );
			}
		}

		if ( ! $width )
			$width = $size['width'];

		if ( ! $height )
			$height = $size['height'];

		return parent::update_size( $width, $height );
	}

	/**
	 * Resizes current image.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param int $max_w
	 * @param int $max_h
	 * @param boolean $crop
	 * @return boolean|WP_Error
	 */
	public function resize( $max_w, $max_h, $crop = false ) {
		if ( ( $this->size['width'] == $max_w ) && ( $this->size['height'] == $max_h ) )
			return true;

		$dims = image_resize_dimensions( $this->size['width'], $this->size['height'], $max_w, $max_h, $crop );
		if ( ! $dims )
			return new WP_Error( 'error_getting_dimensions', __( 'Could not calculate resized image dimensions', 'connections' ) );
		list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;

		if ( $crop ) {
			return $this->crop( $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h );
		}

		try {
			/**
			 * @TODO: Thumbnail is more efficient for imagick, given a newer version of Imagemagick.
			 * Is this also true for gmagick and what are the rules here
			 * $this->image->thumbnailimage( $dst_w, $dst_h );
			 */
			$this->image->scaleimage( $dst_w, $dst_h );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_resize_error', $e->getMessage() );
		}

		return $this->update_size( $dst_w, $dst_h );
	}

	/**
	 * Processes current image and saves to disk
	 * multiple sizes from single source.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param array $sizes { {'width'=>int, 'height'=>int, 'crop'=>bool}, ... }
	 * @return array
	 */
	public function multi_resize( $sizes ) {
		$metadata = array();
		$orig_size = $this->size;
		$orig_image = $this->image->getimage();

		foreach ( $sizes as $size => $size_data ) {
			if ( ! $this->image )
				$this->image = $orig_image->getimage();

			$resize_result = $this->resize( $size_data['width'], $size_data['height'], $size_data['crop'] );

			if( ! is_wp_error( $resize_result ) ) {
				$resized = $this->_save( $this->image );

				$this->image->clear();
				$this->image->destroy();
				$this->image = null;

				if ( ! is_wp_error( $resized ) ) {
					unset( $resized['path'] );
					$metadata[$size] = $resized;
				}
			}

			$this->size = $orig_size;
		}

		$this->image = $orig_image;

		return $metadata;
	}

	/**
	 * Crops Image.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string|int $src The source file or Attachment ID.
	 * @param int $src_x The start x position to crop from.
	 * @param int $src_y The start y position to crop from.
	 * @param int $src_w The width to crop.
	 * @param int $src_h The height to crop.
	 * @param int $dst_w Optional. The destination width.
	 * @param int $dst_h Optional. The destination height.
	 * @param boolean $src_abs Optional. If the source crop points are absolute.
	 * @return boolean|WP_Error
	 */
	public function crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false ) {
		if ( $src_abs ) {
			$src_w -= $src_x;
			$src_h -= $src_y;
		}

		try {
			$this->image->cropimage( $src_w, $src_h, $src_x, $src_y );

			if ( $dst_w || $dst_h ) {
				// If destination width/height isn't specified, use same as
				// width/height from source.
				if ( ! $dst_w )
					$dst_w = $src_w;
				if ( ! $dst_h )
					$dst_h = $src_h;

				$this->image->scaleimage( $dst_w, $dst_h );
				return $this->update_size();
			}
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_crop_error', $e->getMessage() );
		}

		return $this->update_size();
	}

	/**
	 * Rotates current image counter-clockwise by $angle.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param float $angle
	 * @return boolean|WP_Error
	 */
	public function rotate( $angle ) {
		/**
		 * $angle is 360-$angle because Gmagick rotates clockwise
		 * (GD rotates counter-clockwise)
		 */
		try {
			$this->image->rotateimage( new GmagickPixel('none'), 360 - $angle );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_rotate_error', $e->getMessage() );
		}

		return $this->update_size();
	}

	/**
	 * Flips current image.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param boolean $horz Horizontal Flip
	 * @param boolean $vert Vertical Flip
	 * @returns boolean|WP_Error
	 */
	public function flip( $horz, $vert ) {
		try {
			if ( $horz )
				$this->image->flipimage();

			if ( $vert )
				$this->image->flopimage();
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_flip_error', $e->getMessage() );
		}

		return true;
	}

	/**
	 * Saves current image to file.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $destfilename
	 * @param string $mime_type
	 * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
	 */
	public function save( $destfilename = null, $mime_type = null ) {
		$saved = $this->_save( $this->image, $destfilename, $mime_type );

		if ( ! is_wp_error( $saved ) ) {
			$this->file      = $saved['path'];
			$this->mime_type = $saved['mime-type'];

			try {
				$this->image->setimageformat( strtoupper( $this->get_extension( $this->mime_type ) ) );
			}
			catch ( Exception $e ) {
				return new WP_Error( 'image_save_error', $e->getMessage(), $this->file );
			}
		}

		return $saved;
	}

	protected function _save( $image, $filename = null, $mime_type = null ) {
		list( $filename, $extension, $mime_type ) = $this->get_output_format( $filename, $mime_type );

		if ( ! $filename )
			$filename = $this->generate_filename( null, null, $extension );

		try {
			// Store initial Format
			$orig_format = $this->image->getimageformat();

			$this->image->setimageformat( strtoupper( $this->get_extension( $mime_type ) ) );
			$this->make_image( $filename, array( $image, 'writeImage' ), array( $filename ) );

			// Reset original Format
			$this->image->setimageformat( $orig_format );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_save_error', $e->getMessage(), $filename );
		}

		// Set correct file permissions
		$stat  = stat( dirname( $filename ) );
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@ chmod( $filename, $perms );

		/** This filter is documented in wp-includes/class-wp-image-editor-gd.php */
		return array(
			'path'      => $filename,
			'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
			'width'     => $this->size['width'],
			'height'    => $this->size['height'],
			'mime-type' => $mime_type,
		);
	}

	/**
	 * Streams current image to browser.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $mime_type
	 * @return boolean|WP_Error
	 */
	public function stream( $mime_type = null ) {
		list( $filename, $extension, $mime_type ) = $this->get_output_format( null, $mime_type );

		try {
			// Temporarily change format for stream
			$this->image->setimageformat( strtoupper( $extension ) );

			// Output stream of image content
			header( "Content-Type: $mime_type" );
			print $this->image->getimageblob();

			// Reset Image to original Format
			$this->image->setimageformat( $this->get_extension( $this->mime_type ) );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_stream_error', $e->getMessage() );
		}

		return true;
	}
}
