<?php

class CN_Image_Editor_Gmagick extends WP_Image_Editor_Gmagick {

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

}
