<?php

/**
 * Class containing all necessary methods to output structured HTML output of an entry object.
 *
 * @package     Connections
 * @subpackage  Entry HTML
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnOutput
 */
class cnOutput extends cnEntry {

	/**
	 * Echo or return the supplied string.
	 *
	 * @access private
	 * @since  8.2.6
	 *
	 * @param bool   $return
	 * @param string $html
	 *
	 * @return string
	 */
	private function echoOrReturn( $return, $html ) {

		if ( $return ) {

			return $html;

		} else {

			echo $html;
			return '';
		}
	}

	/**
	 * Echos the 'Entry Sized' image.
	 *
	 * @deprecated since 0.7.2.0
	 */
	public function getCardImage() {
		$this->getImage();
	}

	/**
	 * Echos the 'Profile Sized' image.
	 *
	 * @deprecated since 0.7.2.0
	 */
	public function getProfileImage() {
		$this->getImage( array( 'image' => 'photo' , 'preset' => 'profile' ) );
	}

	/**
	 * Echos the 'Thumbnail Sized' image.
	 *
	 * @deprecated since 0.7.2.0
	 */
	public function getThumbnailImage() {
		$this->getImage( array( 'image' => 'photo' , 'preset' => 'thumbnail' ) );
	}

	/**
	 * Echos the logo image.
	 *
	 * @deprecated since 0.7.2.0
	 */
	public function getLogoImage() {

		$this->getImage( array( 'image' => 'logo' ) );
	}

	/**
	 * Echo or return the image/logo if associated in a HTML hCard compliant string.
	 *
	 * Accepted option for the $atts property are:
	 *  image (string) Select the image to display. Valid values are photo || logo
	 *  preset (string) Select one of the predefined image sizes Must be used in conjunction with the 'image' option. Valid values are thumbnail || entry || profile
	 *  fallback (array) Object to be shown when there is no image or logo.
	 *   type (string) Fallback type. Valid values are; none || default || block
	 *   string (string) The string used with the block fallback
	 *   height (int) Block height. [Required if a image custom size was set.]
	 *   width (int) Block width.
	 *  height (int) Override the values saved in the settings. [Required if providing custom size.]
	 *  width (int) Override the values saved in the settings.
	 *  zc (int) Crop format
	 *   0 Resize to Fit specified dimensions (no cropping)
	 *   1 Crop and resize to best fit the dimensions (default behaviour)
	 *   2 Resize proportionally to fit entire image into specified dimensions, and add borders if required
	 *   3 Resize proportionally adjusting size of scaled image so there are no borders gaps
	 *  before (string) HTML to output before the image
	 *  after (string) HTML to after before the image
	 *  style (array) Customize an inline stlye tag for the image or the placeholder block. Array format key == attribute; value == value.
	 *  return (bool) Return or echo the string. Default is to echo.
	 *
	 * NOTE: If only the height or width was set for a custom image size, the opposite image dimension must be set for
	 * the fallback block. This does not apply if the fallback is the default image.
	 *
	 * Filters:
	 *  cn_output_default_atts_image => (array) Register the methods default attributes.
	 *  cn_output_image => (string) The output string, (array) The $atts.
	 *
	 * @todo Enable support for a default image to be set.
	 *
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param array   $atts [optional]
	 * @return string
	 */
	public function getImage( $atts = array() ) {

		$displayImage  = FALSE;
		//$cropModes     = array( 0 => 'none', 1 => 'crop', 2 => 'fill', 3 => 'fit' );
		$targetOptions = array( 'new' => '_blank', 'same' => '_self' );
		$tag           = array();
		$srcset        = array();
		$out           = '';

		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array(
			'image'     => 'photo',
			'preset'    => 'entry',
			'fallback'  => array(
				'type'   => 'none',
				'string' => '',
				'height' => 0,
				'width'  => 0,
			),
			'width'     => 0,
			'height'    => 0,
			'zc'        => 1,
			'quality'   => 80,
			'before'    => '',
			'after'     => '',
			'sizes'     => array( '100vw' ),
			'style'     => array(),
			'action'    => 'display',
			'permalink' => FALSE, // Defaulting this to false for now. Default this to true in future update.
			'return'    => FALSE,
		);

		$defaults = apply_filters( 'cn_output_default_atts_image' , $defaults );

		$atts = cnSanitize::args( $atts, $defaults );

		if ( isset( $atts['fallback'] ) && is_array( $atts['fallback'] ) ) $atts['fallback'] = cnSanitize::args( $atts['fallback'], $defaults['fallback'] );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		/*
		 * The $atts key that are not image tag attributes.
		 */
		$nonAtts = array( 'action', 'image', 'preset', 'fallback', 'image_size', 'zc', 'quality', 'before', 'after', 'style', 'return' );

		$customSize = ( ! empty( $atts['height'] ) || ! empty( $atts['width'] ) ) ? TRUE : FALSE;

		switch ( $atts['image'] ) {

			case 'photo':

				if ( $this->getImageLinked() && ( $this->getImageDisplay() || 'edit' == $atts['action'] ) ) {

					$displayImage  = TRUE;
					$atts['class'] = 'cn-image photo';
					$atts['alt']   = sprintf( __( 'Photo of %s', 'connections' ), $this->getName() );
					$atts['title'] = sprintf( __( 'Photo of %s', 'connections' ), $this->getName() );

					$atts['alt']   = apply_filters( 'cn_photo_alt', $atts['alt'], $this );
					$atts['title'] = apply_filters( 'cn_photo_title', $atts['title'], $this );

					if ( $customSize ) {

						/** @var array|WP_Error $image */
						$image = $this->getImageMeta(
							array(
								'type'      => 'photo',
								'size'      => 'custom',
								'crop_mode' => $atts['zc'],
								'width'     => $atts['width'],
								'height'    => $atts['height'],
								'quality'   => $atts['quality'],
							)
						);

						if ( is_wp_error( $image ) ) {

							if ( is_admin() ) cnMessage::render( 'error', implode( '<br />', $image->get_error_messages() ) );
							$displayImage = FALSE;

						} else {

							// Since this is a custom size of an image we can not know which crop mode to use.
							// Set the crop mode the the value set in $atts['zc'].
							//$cropMode = $atts['zc'];

							// Add the image to the scrset.
							$srcset['image_custom'] = array( 'src' => $image['url'], 'width' => '1x' );

							$atts['width']  = $image['width'];
							$atts['height'] = $image['height'];
						}

					} else {

						$preset = array( 'thumbnail' => 'thumbnail', 'medium' => 'entry', 'large' => 'profile' );

						if ( $size = array_search( $atts['preset'], $preset ) ) {

							/** @var array|WP_Error $image */
							$image = $this->getImageMeta(
								array(
									'type' => 'photo',
									'size' => $size,
								)
							);

							if ( is_wp_error( $image ) ) {

								if ( is_admin() ) cnMessage::render( 'error', implode( '<br />', $image->get_error_messages() ) );
								$displayImage = FALSE;

							} else {

								// Set the crop mode to the value saved in the settings.
								//$cropMode = ( $key = array_search( cnSettingsAPI::get( 'connections', "image_{$size}", 'ratio' ), $cropModes ) ) || $key === 0 ? $key : 2;

								// Add the image to the scrset.
								$srcset[ 'image_' . $size ] = array( 'src' => $image['url'], 'width' => '1x' );

								$atts['width']  = $image['width'];
								$atts['height'] = $image['height'];
							}

						} else {

							$displayImage = FALSE;

							$atts['fallback']['type']   = 'block';
							$atts['fallback']['string'] = sprintf( __( 'Photo present %s is not valid.', 'connections' ), $size );
						}
					}
				}

				/*
				 * Create the link for the image if one was assigned.
				 */
				$links = $this->getLinks( array( 'image' => TRUE ) );

				if ( ! empty( $links ) ) {

					$link = $links[0];
				}

				break;

			case 'logo':

				if ( $this->getLogoLinked() && ( $this->getLogoDisplay() || 'edit' == $atts['action'] ) ) {

					$displayImage  = TRUE;
					$atts['class'] = 'cn-image logo';
					$atts['alt']   = sprintf( __( 'Logo for %s', 'connections' ), $this->getName() );
					$atts['title'] = sprintf( __( 'Logo for %s', 'connections' ), $this->getName() );
					//$cropMode      = ( $key = array_search( cnSettingsAPI::get( 'connections', 'image_logo', 'ratio' ), $cropModes ) ) || $key === 0 ? $key : 2;

					$atts['alt']   = apply_filters( 'cn_logo_alt', $atts['alt'], $this );
					$atts['title'] = apply_filters( 'cn_logo_title', $atts['title'], $this );

					if ( $customSize ) {

						$image = $this->getImageMeta(
							array(
								'type'      => 'logo',
								'size'      => 'custom',
								'crop_mode' => $atts['zc'],
								'width'     => $atts['width'],
								'height'    => $atts['height'],
								'quality'   => $atts['quality'],
							)
						);

						if ( is_wp_error( $image ) ) {

							if ( is_admin() ) cnMessage::render( 'error', implode( '<br />', $image->get_error_messages() ) );
							$displayImage = FALSE;

						} else {

							// Add the image to the scrset.
							$srcset['logo_custom'] = array( 'src' => esc_url( $image['url'] ), 'width' => '1x' );

							$atts['width']  = $image['width'];
							$atts['height'] = $image['height'];
						}

					} else {

						$image = $this->getImageMeta(
							array(
								'type' => 'logo',
								'size' => 'scaled',
							)
						);

						if ( is_wp_error( $image ) ) {

							if ( is_admin() ) cnMessage::render( 'error', implode( '<br />', $image->get_error_messages() ) );
							$displayImage = FALSE;

						} else {

							// Add the image to the scrset.
							$srcset['logo'] = array( 'src' => esc_url( $image['url'] ), 'width' => '1x' );

							$atts['width']  = $image['width'];
							$atts['height'] = $image['height'];
						}

					}
				}

				/*
				 * Create the link for the image if one was assigned.
				 */
				$links = $this->getLinks( array( 'logo' => TRUE ) );

				if ( ! empty( $links ) ) {

					$link = $links[0];
				}

				break;
		}

		if ( $displayImage ) {

			// Allow extension to filter the img class.
			$atts['class'] = apply_filters( 'cn_image_class', $atts['class'] );

			// Add the 2x (retina) image to the srcset.
			/*$srcset['2x'] = array(
				'src' => add_query_arg(
					array(
						CN_IMAGE_ENDPOINT => $wp_rewrite->using_permalinks() ? FALSE : TRUE,
						'src'             => $this->getOriginalImageURL( $atts['image'] ),
						'cn-entry-slug'   => $this->getSlug(),
						'w'               => $image['width'] * 2,
						'h'               => $atts['height'] * 2,
						'zc'              => $cropMode,
					),
					( $wp_rewrite->using_permalinks() ? home_url( CN_IMAGE_ENDPOINT ) : home_url() ) ),
				'width' => '2x'
				);*/

			// Allow extensions to add/remove images to the srcset.
			$srcset = apply_filters( 'cn_image_srcset', $srcset );

			foreach ( $srcset as $src ) {

				$atts['srcset'][] = implode( ' ', cnURL::makeProtocolRelative( $src ) );
			}

			$atts['srcset'] = implode( ', ', $atts['srcset'] );

			// Allow extensions to add/remove sizes media queries.
			$atts['sizes'] = apply_filters( 'cn_image_sizes', $atts['sizes'] );

			$atts['sizes'] = implode( ', ', $atts['sizes'] );

			// Remove any values in the $atts array that not not img attributes and add those that are to the $tag array.
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) && ! in_array( $attr , $nonAtts ) ) $tag[] = "$attr=\"$value\"";
			}

			// All extensions to apply/remove inline styles.
			$atts['style'] = apply_filters( 'cn_image_styles', $atts['style'] );

			if ( is_array( $atts['style'] ) && ! empty( $atts['style'] ) ) array_walk( $atts['style'], create_function( '&$i, $property', '$i = "$property: $i";' ) );

			/*
			 * If a link has not been attached to the photo/logo AND the permalink option is enabled
			 * initiate a new link object and set it's properties.
			 */
			if ( ! isset( $link ) && TRUE === $atts['permalink'] ) {

				$link = (object) array( 'url' => $this->getPermalink(), 'target' => '', 'followString' => '' );
			}

			/*
			 * If the image has a link/permalink attached, create the HTML anchor.
			 */
			if ( isset( $link ) ) {

				$link   = apply_filters( "cn_image_link-{$atts['image']}", apply_filters( 'cn_image_link', $link, $this ), $this );
				$target = array_key_exists( $link->target, $targetOptions ) ? $targetOptions[ $link->target ] : '';

				$anchor = sprintf(
					'<a href="%1$s"%2$s%3$s>',
					esc_url( $link->url ),
					empty( $target ) ? '' : ' target="' . $target . '"',
					empty( $link->followString ) ? '' : ' rel="' . $link->followString . '"'
				);
			}

			// The inner <span> is required for responsive image support. This markup also makes it IE8 compatible.
			$out = sprintf( '<span class="cn-image-style"><span style="display: block; max-width: 100%%; width: %2$spx">%3$s<img %4$s%1$s/>%5$s</span></span>',
				empty( $atts['style'] ) ? '' : ' style="' . implode( '; ', $atts['style'] ) . ';"',
				absint( $image['width'] ),
				isset( $anchor ) ? $anchor : '',
				implode( ' ', $tag ),
				isset( $anchor ) ? '</a>' : ''
			);

		} else {

			if ( $customSize ) {

				/*
				 * Set the size to the supplied custom. The fallback custom size would take priority if it has been supplied.
				 */
				$atts['style']['width']  = empty( $atts['fallback']['width'] ) ? $atts['width'] . 'px' : $atts['fallback']['width'] . 'px';
				$atts['style']['height'] = empty( $atts['fallback']['height'] ) ? $atts['height'] . 'px' : $atts['fallback']['height'] . 'px';

			} else {
				/*
				 * If a custom size was not set, use the dimensions saved in the settings.
				 */
				switch ( $atts['image'] ) {
					case 'photo':

						switch ( $atts['preset'] ) {

							case 'entry':
								$atts['style']['width']  = cnSettingsAPI::get( 'connections', 'image_medium', 'width' ) . 'px';
								$atts['style']['height'] = cnSettingsAPI::get( 'connections', 'image_medium', 'height' ) . 'px';
								break;

							case 'profile':
								$atts['style']['width']  = cnSettingsAPI::get( 'connections', 'image_large', 'width' ) . 'px';
								$atts['style']['height'] = cnSettingsAPI::get( 'connections', 'image_large', 'height' ) . 'px';
								break;

							case 'thumbnail':
								$atts['style']['width']  = cnSettingsAPI::get( 'connections', 'image_thumbnail', 'width' ) . 'px';
								$atts['style']['height'] = cnSettingsAPI::get( 'connections', 'image_thumbnail', 'height' ) . 'px';
								break;

							default:
								$atts['style']['width']  = cnSettingsAPI::get( 'connections', 'image_medium', 'width' ) . 'px';
								$atts['style']['height'] = cnSettingsAPI::get( 'connections', 'image_medium', 'height' ) . 'px';
								break;
						}

						break;

					case 'logo':

						$atts['style']['width']  = cnSettingsAPI::get( 'connections', 'image_logo', 'width' ) . 'px';
						$atts['style']['height'] = cnSettingsAPI::get( 'connections', 'image_logo', 'height' ) . 'px';
						break;
				}
			}

			switch ( $atts['fallback']['type'] ) {

				case 'block':

					$atts['style']['display'] = 'inline-block';

					if ( is_array( $atts['style'] ) && ! empty( $atts['style'] ) ) array_walk( $atts['style'], create_function( '&$i, $property', '$i = "$property: $i";' ) );

					$string = empty( $atts['fallback']['string'] ) ? '' : '<span>' . $atts['fallback']['string'] . '</span>';

					$out = sprintf( '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image-%1$s cn-image-none"%2$s>%3$s</span></span>',
						esc_attr( $atts['image'] ),
						empty( $atts['style'] ) ? '' : ' style="' . implode( '; ', $atts['style'] ) . ';"',
						$string
					);

					break;

				case 'default':
					/*
					 * @TODO Enable support for a default image to be set.
					 * NOTE: Use switch for image type to allow a default image for both the image and logo.
					 */
					break;
			}
		}

		$out = apply_filters( 'cn_output_image', $out, $atts, $this );

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Returns the permalink for the entry.
	 *
	 * @access public
	 * @since  8.1.6
	 *
	 * @uses   cnURL::permalink()
	 */
	public function permalink() {

		cnURL::permalink(
			array(
				'type'       => 'name',
				'slug'       => $this->getSlug(),
				'home_id'    => $this->directoryHome['page_id'],
				'force_home' => $this->directoryHome['force_home'],
				'data'       => 'url',
				'return'     => FALSE,
			)
		);
	}

	/**
	 * Echo or return the entry name in a HTML hCard compliant string.
	 *
	 * @example
	 * If an entry is an individual this would return their name as Last Name, First Name
	 *
	 * $this->getName( array( 'format' => '%last%, %first% %middle%' ) );
	 *
	 * NOTE: If an entry is a organization/family, this will return the organization/family name instead
	 *       ignoring the format attribute because it does not apply.
	 *
	 * @access  public
	 * @since   unknown
	 *
	 * @param array $atts {
	 *     Optional.
	 *
	 *     @type string $format How the name should be displayed using Tokens for the parts of the name.
	 *                          Default, '%prefix% %first% %middle% %last% %suffix%'.
	 *                          Accepts any combination of the following tokens:
	 *                          '%prefix%', '%first%', '%middle%', '%last%', '%suffix%', '%first_initial%', '%middle_initial%','%last_initial%'
	 *     @type string $before HTML to be displayed before the relations container. Default, empty string.
	 *     @type string $after  HTML to be displayed after the relations container. Default, empty string.
	 *     @type bool   $return Whether or not to return the HTML. Default, FALSE.
	 * }
	 *
	 * @return string
	 */
	public function getNameBlock( $atts = array() ) {

		$defaults = array(
			'format' => '%prefix% %first% %middle% %last% %suffix%',
			'link'   => cnSettingsAPI::get( 'connections', 'connections_link', 'name' ),
			'target' => 'name',
			'before' => '',
			'after'  => '',
			'return' => FALSE
		);

		/**
		 * Filter the arguments.
		 *
		 * @since unknown
		 *
		 * @param array $atts An array of arguments.
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_name_atts', $atts ),
			apply_filters( 'cn_output_name_default_atts', $defaults )
		);

		$search = array(
			'%prefix%',
			'%first%',
			'%middle%',
			'%last%',
			'%suffix%',
			'%first_initial%',
			'%middle_initial%',
			'%last_initial%',
		);
		$replace         = array();
		$honorificPrefix = $this->getHonorificPrefix();
		$first           = $this->getFirstName();
		$middle          = $this->getMiddleName();
		$last            = $this->getLastName();
		$honorificSuffix = $this->getHonorificSuffix();

		switch ( $this->getEntryType() ) {

			case 'organization':

				// The `notranslate` class is added to prevent Google Translate from translating the text.
				$html = '<span class="org fn notranslate">' . $this->getOrganization() . '</span>';

				break;

			case 'family':

				$html = '<span class="fn n notranslate"><span class="family-name">' . $this->getFamilyName() . '</span></span>';

				break;

			default:

				$replace[] = 0 == strlen( $honorificPrefix ) ? '' : '<span class="honorific-prefix">' . $honorificPrefix . '</span>';

				$replace[] = 0 == strlen( $first ) ? '' : '<span class="given-name">' . $first . '</span>';

				$replace[] = 0 == strlen( $middle ) ? '' : '<span class="additional-name">' . $middle . '</span>';

				$replace[] = 0 == strlen( $last ) ? '' : '<span class="family-name">' . $last . '</span>';

				$replace[] = 0 == strlen( $honorificSuffix ) ? '' : '<span class="honorific-suffix">' . $honorificSuffix . '</span>';

				$replace[] = 0 == strlen( $first ) ? '' : '<span class="given-name-initial">' . $first[0] . '</span>';

				$replace[] = 0 == strlen( $middle ) ? '' : '<span class="additional-name-initial">' . $middle[0] . '</span>';

				$replace[] = 0 == strlen( $last ) ? '' : '<span class="family-name-initial">' . $last[0] . '</span>';

				$html = str_ireplace(
					$search,
					$replace,
					'<span class="fn n notranslate">' . ( empty( $atts['format'] ) ? $defaults['format'] : $atts['format'] ) . '</span>'
				);

				break;
		}

		$html = cnString::replaceWhatWith( $html, ' ' );

		if ( $atts['link'] ) {

			$html = cnURL::permalink(
				array(
					'type'       => $atts['target'],
					'slug'       => $this->getSlug(),
					'title'      => $this->getName( $atts ),
					'text'       => $html,
					'home_id'    => $this->directoryHome['page_id'],
					'force_home' => $this->directoryHome['force_home'],
					'return'     => TRUE,
				)
			);
		}

		$html = $atts['before'] . $html . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Returns the Entry's full first and last name.
	 *
	 * NOTE: If an entry is a organization/family, this will return the organization/family name instead
	 *    ignoring the format attribute because it does not apply.
	 *
	 * @deprecated since 0.7.2.0
	 * @return string
	 */
	public function getFullFirstLastNameBlock() {
		return $this->getNameBlock( array( 'format' => '%prefix% %first% %middle% %last% %suffix%', 'return' => TRUE ) );
	}

	/**
	 * Returns the Entry's full first and last name with the last name first.
	 *
	 * NOTE: If an entry is a organization/family, this will return the organization/family name instead
	 *    ignoring the format attribute because it does not apply.
	 *
	 * @deprecated since 0.7.2.0
	 * @return string
	 */
	public function getFullLastFirstNameBlock() {
		return $this->getNameBlock( array( 'format' => '%last%, %first% %middle%', 'return' => TRUE ) );
	}

	/**
	 * Echos the family members of the family entry type.
	 *
	 * @deprecated since 0.7.1.0
	 */
	public function getConnectionGroupBlock() {
		$this->getFamilyMemberBlock();
	}

	/**
	 * Echos the family members of the family entry type.
	 *
	 * @access  public
	 * @since   unknown
	 *
	 * @param array $atts {
	 *     Optional.
	 *
	 *     @type string $container_tag The relationship container tag. Default `ul`. Accepts HTML tag.
	 *     @type string $item_tag      The relationship row tag. Default `li`. Accepts HTML tag.
	 *     @type string $item_format   The relationship row HTML markup.
	 *     @type string $name_format   How the relationship name should be displayed @see cnEntry::getName().
	 *     @type string $separator     The string used to separate the relation label from the relation name. Default ':'.
	 *     @type string $before        HTML to be displayed before the relations container. Default, empty string.
	 *     @type string $after         HTML to be displayed after the relations container. Default, empty string.
	 *     @type string $before_item   HTML to be displayed before a relation row. Default, empty string.
	 *     @type string $after_item    HTML to be displayed after a relation row. Default, empty string.
	 * }
	 *
	 * @return string
	 */
	public function getFamilyMemberBlock( $atts = array() ) {

		$defaults = array(
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'item_format'   => '<%1$s class="cn-relation"><span class="cn-relation-label">%relation%</span>%separator% <span class="cn-relation-name notranslate">%name%</span></%1$s>', // The `notranslate` class is added to prevent Google Translate from translating the text.
			'name_format'   => '',
			'separator'     => ':',
			'before'        => '',
			'after'         => '',
			'before_item'   => '',
			'after_item'    => '',
			'return'        => FALSE,
		);

		/**
		 * Filter the arguments.
		 *
		 * @since unknown
		 *
		 * @param array $atts An array of arguments.
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_family_atts', $atts ),
			apply_filters( 'cn_output_family_default_atts', $defaults )
		);

		$html   = '';
		$search = array( '%relation%', '%name%', '%separator%' );

		if ( $relations = $this->getFamilyMembers() ) {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			foreach ( $relations as $relationData ) {

				$relation = new cnEntry();
				$replace  = array();

				if ( $relation->set( $relationData['entry_id'] ) ) {

					// Ensure the use can only see relationships based on relations status and visibility.
					if ( 'approved' != $relation->getStatus() || ! cnValidate::userPermitted( $relation->getVisibility() ) ) {

						continue;
					}

					$replace[] = $instance->options->getFamilyRelation( $relationData['relation'] );

					$replace[] = cnURL::permalink(
						array(
							'type'       => 'name',
							'slug'       => $relation->getSlug(),
							'title'      => $relation->getName( array( 'format' => $atts['name_format'] ) ),
							'text'       => $relation->getName( array( 'format' => $atts['name_format'] ) ),
							'home_id'    => $this->directoryHome['page_id'],
							'force_home' => $this->directoryHome['force_home'],
							'return'     => TRUE,
						)
					);

					$replace[] = empty( $atts['separator'] ) ? '' : '<span class="cn-separator">' . $atts['separator'] . '</span>';

					$row = str_ireplace(
						$search,
						$replace,
						empty( $atts['item_format'] ) ? $defaults['item_format'] : $atts['item_format']
					);

					$html .= "\t" . sprintf( $row, $atts['item_tag'] ) . PHP_EOL;
				}
			}

			$html = sprintf(
				'<%1$s class="cn-relations">' . PHP_EOL . '%2$s</%1$s>',
				$atts['container_tag'],
				$html
			);

			$html = $atts['before'] . $html . $atts['after'] . PHP_EOL;
		}

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Echo or return the entry's title in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  before (string) HTML to output before an address.
	 *  after (string) HTML to after before an address.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_title => (array) Register the methods default attributes.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $atts Accepted values as noted above.
	 *
	 * @return string
	 */
	public function getTitleBlock( $atts = array() ) {

		$defaults = array(
			'tag'    => 'span',
			'before' => '',
			'after'  => '',
			'return' => FALSE
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_title', $atts ),
			apply_filters( 'cn_output_default_atts_title', $defaults )
		);

		$title = $this->getTitle();

		if ( ! empty( $title ) ) {

			// The `notranslate` class is added to prevent Google Translate from translating the text.
			$out = sprintf( '<%1$s class="title notranslate">%2$s</%1$s>', $atts['tag'], $title );

		} else {

			return '';
		}

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Echo or return the entry's organization and/or department in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  before (string) HTML to output before an address.
	 *  after (string) HTML to after before an address.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_orgunit => (array) Register the methods default attributes.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $atts Accepted values as noted above.
	 *
	 * @return string
	 */
	public function getOrgUnitBlock( $atts = array() ) {

		$out = '';

		$defaults = array(
			'before'    => '',
			'after'     => '',
			'show_org'  => TRUE,
			'show_dept' => TRUE,
			'link'      => array(
				'organization' => cnSettingsAPI::get( 'connections', 'connections_link', 'organization' ),
				'department'   => cnSettingsAPI::get( 'connections', 'connections_link', 'department' )
			),
			'return'    => FALSE
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_orgunit', $atts ),
			apply_filters( 'cn_output_default_atts_orgunit', $defaults )
		);

		$org  = $atts['show_org'] ? $this->getOrganization() : '';
		$dept = $atts['show_dept'] ? $this->getDepartment() : '';

		if ( ! empty( $org ) || ! empty( $dept ) ) {

			$out .= '<span class="org">';

			if ( ! empty( $org ) ) {

				if ( $atts['link']['organization'] ) {

					$organization = cnURL::permalink( array(
							'type'       => 'organization',
							'slug'       => $org,
							'title'      => $org,
							'text'       => $org,
							'home_id'    => $this->directoryHome['page_id'],
							'force_home' => $this->directoryHome['force_home'],
							'return'     => TRUE
						)
					);

				} else {

					$organization = $org;
				}

				// The `notranslate` class is added to prevent Google Translate from translating the text.
				$out .= '<span class="organization-name notranslate"' . ( $this->getEntryType() == 'organization' ? ' style="display: none;"' : '' ) . '>' . $organization . '</span>';
			}

			if ( ! empty( $dept ) ) {

				if ( $atts['link']['department'] ) {

					$department = cnURL::permalink( array(
							'type'       => 'department',
							'slug'       => $dept,
							'title'      => $dept,
							'text'       => $dept,
							'home_id'    => $this->directoryHome['page_id'],
							'force_home' => $this->directoryHome['force_home'],
							'return'     => TRUE
						)
					);

				} else {

					$department = $dept;
				}

				// The `notranslate` class is added to prevent Google Translate from translating the text.
				$out .= '<span class="organization-unit notranslate">' . $department . '</span>';
			}

			$out .= '</span>';
		}

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Return the entry's organization and/or department in a HTML hCard compliant string.
	 *
	 * @deprecated since 0.7.2.0
	 */
	public function getOrganizationBlock() {
		return $this->getOrgUnitBlock( array( 'return' => TRUE ) );
	}

	/**
	 * Return the entry's organization and/or department in a HTML hCard compliant string.
	 *
	 * @deprecated since 0.7.2.0
	 */
	public function getDepartmentBlock() {
		return $this->getOrgUnitBlock( array( 'return' => TRUE ) );
	}

	/**
	 * Echo or return the entry's contact name in a HTML string.
	 *
	 * @access  public
	 * @since   unknown
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type string $format    The format the contact name should be returned as.
	 *                             Default: %label%%separator% %first% %last%
	 *                             Accepts any combination of the following tokens: '%label%', '%first%', '%last%', '%separator%'
	 *     @type string $label     The label shown for the contact name.
	 *                             Default: Contact
	 *     @type string $separator The separator to use between the label and contact name.
	 *     @type string $before    The content to render before the contact name block.
	 *     @type string $after     The content to render after the contact name block.
	 *     @type bool   $return    Whether or not to echo or return the HTML.
	 *                             Default: FALSE, which is to echo the result.
	 * }
	 *
	 * @return string
	 */
	public function getContactNameBlock( $atts = array() ) {

		$defaults = array(
			'format'    => '',
			'label'     => __( 'Contact', 'connections' ),
			'separator' => ':',
			'before'    => '',
			'after'     => '',
			'return'    => FALSE
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_contact_name', $atts ),
			apply_filters( 'cn_output_default_atts_contact_name', $defaults )
		);

		$search  = array( '%label%', '%first%', '%last%', '%separator%' );
		$replace = array();
		$first   = $this->getContactFirstName();
		$last    = $this->getContactLastName();

		if ( empty( $first ) && empty( $last ) ) {

			return '';
		}

		$replace[] = 0 == strlen( $first ) && 0 == strlen( $last ) ? '' : '<span class="contact-label">' . $atts['label'] . '</span>';

		// The `notranslate` class is added to prevent Google Translate from translating the text.
		$replace[] = 0 == strlen( $first ) ? '' : '<span class="contact-given-name notranslate">' . $first . '</span>';

		// The `notranslate` class is added to prevent Google Translate from translating the text.
		$replace[] = 0 == strlen( $last ) ? '' : '<span class="contact-family-name notranslate">' . $last . '</span>';

		$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

		$out = str_ireplace(
			$search,
			$replace,
			empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %first% %last%' : $defaults['format'] ) : $atts['format']
		);

		$out = cnString::replaceWhatWith( $out, ' ' );

		$block = '<span class="cn-contact-block">' .  $out . '</span>';

		$html = $atts['before'] . $block . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Echo or return the entry's addresses in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry address.
	 *  type (array) || (string) Retrieve specific address types.
	 *   Permitted Types:
	 *    home
	 *    work
	 *    school
	 *    other
	 *  district (array) || (string) Retrieve addresses in a specific district.
	 *  county (array) || (string) Retrieve addresses in a specific county.
	 *  city (array) || (string) Retrieve addresses in a specific city.
	 *  state (array) || (string) Retrieve addresses in a specific state..
	 *  zipcode (array) || (string) Retrieve addresses in a specific zipcode.
	 *  country (array) || (string) Retrieve addresses in a specific country.
	 *  coordinates (array) Retrieve addresses in with specific coordinates. Both latitude and longitude must be supplied.
	 *  format (string) The tokens to use to display the address block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %line1%
	 *    %line2%
	 *    %line3%
	 *    %city%
	 *    %state%
	 *    %zipcode%
	 *    %country%
	 *    %geo%
	 *    %separator%
	 *  separator (string) The separator to use.
	 *  before (string) HTML to output before the addresses.
	 *  after (string) HTML to after before the addresses.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_address => (array) Register the methods default attributes.
	 *
	 * @access public
	 * @since unknown
	 *
	 * @param array $atts Accepted values as noted above.
	 * @param bool  $cached Returns the cached address rather than querying the db.
	 *
	 * @return string
	 */
	public function getAddressBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred'   => NULL,
			'type'        => NULL,
			'limit'       => NULL,
			'district'    => NULL,
			'county'      => NULL,
			'city'        => NULL,
			'state'       => NULL,
			'zipcode'     => NULL,
			'country'     => NULL,
			'coordinates' => array(),
			'format'      => '',
			'link'        => array(
				'district'    => cnSettingsAPI::get( 'connections', 'link', 'district' ),
				'county'      => cnSettingsAPI::get( 'connections', 'link', 'county' ),
				'locality'    => cnSettingsAPI::get( 'connections', 'link', 'locality' ),
				'region'      => cnSettingsAPI::get( 'connections', 'link', 'region' ),
				'postal_code' => cnSettingsAPI::get( 'connections', 'link', 'postal_code' ),
				'country'     => cnSettingsAPI::get( 'connections', 'link', 'country' ),
			),
			'separator'   => ':',
			'before'      => '',
			'after'       => '',
			'return'      => FALSE,
		);

		/**
		 * Allow extensions to filter the default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_address', $atts ),
			apply_filters( 'cn_output_default_atts_address', $defaults )
		);

		$atts['link'] = cnSanitize::args( $atts['link'], $defaults['link'] );
		$atts['id']   = $this->getId();

		$out = $this->addresses->filterBy( 'type', $atts['type'] )
		                       ->filterBy( 'district', $atts['district'] )
		                       ->filterBy( 'county', $atts['county'] )
		                       ->filterBy( 'city', $atts['city'] )
		                       ->filterBy( 'state', $atts['state'] )
		                       ->filterBy( 'zipcode', $atts['zipcode'] )
		                       ->filterBy( 'country', $atts['country'] )
		                       ->filterBy( 'preferred', $atts['preferred'] )
		                       ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
		                       ->take( $atts['limit'] )
		                       ->escapeFor( 'display' )
		                       ->render( 'hcard', array( 'atts' => $atts, 'entry' => $this ), TRUE, TRUE );

		// The filters need to be reset so additional calls to get addresses with different params return expected results.
		$this->addresses->resetFilters();

		$out = cnString::replaceWhatWith( $out, ' ' );

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Echo or return a <div> with the entry's address within the HTML5 data- attribute. To be used for
	 * placing a Google Map in with the jQuery goMap plugin.
	 *
	 * NOTE: wp_enqueue_script('jquery-gomap-min') must called before use, otherwise just an empty div will be displayed.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry address.
	 *  type (array) || (string) Retrieve specific address types.
	 *   Permitted Types:
	 *    home
	 *    work
	 *    school
	 *    other
	 *  static (bool) Query map via the Google Static Maps API
	 *  maptype (string) Valid types are: HYBRID, ROADMAP, SATELLITE, TERRAIN
	 *  zoom (int) Sets the zoom level.
	 *  height (int) Specifiy the div height in px.
	 *  width (int) Specifiy the div widdth in px.
	 *  coordinates (array) Retrieve addresses in with specific coordinates. Both latitude and longitude must be supplied.
	 *  before (string) HTML to output before the addresses.
	 *  after (string) HTML to after before the addresses.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * @TODO Add support for the Google Maps API Premier client id.
	 *
	 * Filters:
	 *  cn_output_default_atts_contact_name => (array) Register the methods default attributes.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $atts Accepted values as noted above.
	 * @param bool  $cached Returns the cached address rather than querying the db.
	 *
	 * @return string
	 */
	public function getMapBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred' => NULL,
			'type'      => NULL,
			'static'    => FALSE,
			'maptype'   => 'ROADMAP',
			'zoom'      => 13,
			'height'    => 400,
			'width'     => 400,
			'before'    => '',
			'after'     => '',
			'return'    => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_map', $atts ),
			apply_filters( 'cn_output_default_atts_map', $defaults )
		);

		$atts['id'] = $this->getId();

		$out = '';
		$attr = array();
		$addr = array();
		$geo = array();

		// Limit the map type to one of the valid types to prevent user error.
		$permittedMapTypes = array( 'HYBRID', 'ROADMAP', 'SATELLITE', 'TERRAIN' );
		$atts['maptype'] = strtoupper( $atts['maptype'] );
		if ( ! in_array( $atts['maptype'] , $permittedMapTypes ) ) $atts['maptype'] = 'ROADMAP';

		// Limit the user specified zoom level to between 0 and 21
		if ( ! in_array( $atts['zoom'] , range( 0, 21 ) ) ) $atts['zoom'] = 13;

		// Ensure the requested map size does not exceed the permitted sizes permitted by the Google Static Maps API
		if ( $atts['static'] ) $atts['width'] = ( $atts['width'] <= 640 ) ? $atts['width'] : 640;
		if ( $atts['static'] ) $atts['height'] = ( $atts['height'] <= 640 ) ? $atts['height'] : 640;

		$addresses = $this->getAddresses( $atts , $cached );

		if ( empty( $addresses ) ) return '';

		if ( ! empty( $addresses[0]->line_one ) ) $addr[] = $addresses[0]->line_one;
		if ( ! empty( $addresses[0]->line_two ) ) $addr[] = $addresses[0]->line_two;
		if ( ! empty( $addresses[0]->city ) ) $addr[] = $addresses[0]->city;
		if ( ! empty( $addresses[0]->state ) ) $addr[] = $addresses[0]->state;
		if ( ! empty( $addresses[0]->zipcode ) ) $addr[] = $addresses[0]->zipcode;

		if ( ! empty( $addresses[0]->latitude ) && ! empty( $addresses[0]->longitude ) ) {
			$geo['latitude'] = $addresses[0]->latitude;
			$geo['longitude'] = $addresses[0]->longitude;
		}

		if ( empty( $addr ) && empty( $geo ) ) return '';

		if ( $atts['static'] ) {
			$attr['center'] = ( empty( $geo ) ) ? implode( ', ' , $addr ) : implode( ',' , $geo );
			$attr['markers'] = $attr['center'];
			$attr['size'] = $atts['width'] . 'x' . $atts['height'];
			$attr['maptype'] = $atts['maptype'];
			$attr['zoom'] = $atts['zoom'];
			//$attr['scale'] = 2;
			$attr['format'] = 'png';
			$attr['sensor'] = 'false';

			$out .= '<span class="cn-image-style" style="display: inline-block;"><span class="cn-image" style="height: ' . $atts['height'] . '; width: ' . $atts['width'] . '">';
			$out .= '<img class="map" title="' . $attr['center'] . '" alt="' . $attr['center'] . '" width="' . $atts['width'] . '" height="' . $atts['height'] . '" src="http://maps.googleapis.com/maps/api/staticmap?' . http_build_query( $attr , '' , '&amp;' ) . '"/>';
			$out .= '</span></span>';
		}
		else {
			$attr[] = 'class="cn-gmap" id="map-' . $this->getRuid() . '"';
			if ( ! empty( $addr ) ) $attr[] = 'data-address="' . implode( ', ', $addr ) .'"';
			if ( ! empty( $geo['latitude'] ) ) $attr[] = 'data-latitude="' . $geo['latitude'] .'"';
			if ( ! empty( $geo['longitude'] ) ) $attr[] = 'data-longitude="' . $geo['longitude'] .'"';
			$attr[] = 'style="' . ( ! empty( $atts['width'] ) ? 'width: ' . $atts['width'] . 'px; ' : '' ) . 'height: ' . $atts['height'] . 'px"';
			$attr[] = 'data-maptype="' . $atts['maptype'] .  '"';
			$attr[] = 'data-zoom="' . $atts['zoom'] .  '"';

			$out = '<div ' . implode( ' ', $attr ) . '></div>';
		}

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Echo or return the entry's phone numbers in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry phone number.
	 *  type (array) || (string) Retrieve specific phone number types.
	 *   Permitted Types:
	 *    homephone
	 *    homefax
	 *    cellphone
	 *    workphone
	 *    workfax
	 *  format (string) The tokens to use to display the phone number block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %number%
	 *    %separator%
	 *  separator (string) The separator to use.
	 *  before (string) HTML to output before the phone numbers.
	 *  after (string) HTML to after before the phone numbers.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_phone => (array) Register the methods default attributes.
	 *
	 * @access public
	 * @since unknown
	 *
	 * @param array $atts   Accepted values as noted above.
	 * @param bool  $cached Returns the cached data rather than querying the db.
	 *
	 * @return string
	 */
	public function getPhoneNumberBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred' => NULL,
			'type'      => NULL,
			'limit'     => NULL,
			'format'    => '',
			'separator' => ':',
			'before'    => '',
			'after'     => '',
			'return'    => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_phone', $atts ),
			apply_filters( 'cn_output_default_atts_phone', $defaults )
		);

		$atts['id'] = $this->getId();

		$out = $this->phoneNumbers->filterBy( 'type', $atts['type'] )
		                          ->filterBy( 'preferred', $atts['preferred'] )
		                          ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
		                          ->take( $atts['limit'] )
		                          ->escapeFor( 'display' )
		                          ->render( 'hcard', array( 'atts' => $atts, 'entry' => $this ), TRUE, TRUE );

		// The filters need to be reset so additional calls to get phone numbers with different params return expected results.
		$this->phoneNumbers->resetFilters();

		$block = cnString::replaceWhatWith( $out, ' ' );

		$html = $atts['before'] . $block . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Returns the entry's telephone type in a HTML hCard compliant string.
	 *
	 * @link  http://microformats.org/wiki/hcard-cheatsheet
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public function gethCardTelType( $data ) {

		$type = '';

		switch ( $data ) {

			case 'home':
				$type = '<span class="type" style="display: none;">home</span>';
				break;
			case 'homephone':
				$type = '<span class="type" style="display: none;">home</span>';
				break;
			case 'homefax':
				$type = '<span class="type" style="display: none;">home</span><span class="type" style="display: none;">fax</span>';
				break;
			case 'cell':
				$type = '<span class="type" style="display: none;">cell</span>';
				break;
			case 'cellphone':
				$type = '<span class="type" style="display: none;">cell</span>';
				break;
			case 'work':
				$type = '<span class="type" style="display: none;">work</span>';
				break;
			case 'workphone':
				$type = '<span class="type" style="display: none;">work</span>';
				break;
			case 'workfax':
				$type = '<span class="type" style="display: none;">work</span><span class="type" style="display: none;">fax</span>';
				break;
			case 'fax':
				$type = '<span class="type" style="display: none;">work</span><span class="type" style="display: none;">fax</span>';
				break;
		}

		return $type;
	}

	/**
	 * Returns the entry's address type in a HTML hCard compliant string.
	 *
	 * @link http://microformats.org/wiki/adr-cheatsheet#Properties_.28Class_Names.29
	 *
	 * @access private
	 * @since  unknown
	 *
	 * @param string $adrType
	 *
	 * @return string
	 */
	public function gethCardAdrType( $adrType ) {

		switch ( $adrType ) {

			case 'home':
				$type = '<span class="type" style="display: none;">home</span>';
				break;
			case 'work':
				$type = '<span class="type" style="display: none;">work</span>';
				break;
			case 'school':
				$type = '<span class="type" style="display: none;">postal</span>';
				break;
			case 'other':
				$type = '<span class="type" style="display: none;">postal</span>';
				break;

			default:
				$type = '<span class="type" style="display: none;">postal</span>';
				break;
		}

		return $type;
	}

	/**
	 * Echo or return the entry's email addresses in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry email address.
	 *  type (array) || (string) Retrieve specific email address types.
	 *   Permitted Types:
	 *    personal
	 *    work
	 *  format (string) The tokens to use to display the email address block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %address%
	 *    %icon%
	 *    %separator%
	 *  title (string) The link title attribute. Accepts tokens.
	 *   Permitted Tokens:
	 *    Name tokens:
	 *     %prefix%
	 *     %first%
	 *     %middle%
	 *     %last%
	 *     %suffix%
	 *    Email tokens:
	 *     %type%
	 *     %name%
	 *  size (int) the icon size. Permitted sizes are 16, 24, 32, 48.
	 *  separator (string) The separator to use.
	 *  before (string) HTML to output before the email addresses.
	 *  after (string) HTML to after before the email addresses.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_email => (array) Register the methods default attributes.
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $atts   Accepted values as noted above.
	 * @param bool  $cached Returns the cached data rather than querying the db.
	 *
	 * @return string
	 */
	public function getEmailAddressBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred' => NULL,
			'type'      => NULL,
			'limit'     => NULL,
			'format'    => '',
			'title'     => '',
			'size'      => 32,
			'separator' => ':',
			'before'    => '',
			'after'     => '',
			'return'    => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_email', $atts ),
			apply_filters( 'cn_output_default_atts_email', $defaults )
		);

		$atts['id'] = $this->getId();

		$out = $this->emailAddresses->filterBy( 'type', $atts['type'] )
		                            ->filterBy( 'preferred', $atts['preferred'] )
		                            ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
		                            ->take( $atts['limit'] )
		                            ->escapeFor( 'display' )
		                            ->render( 'hcard', array( 'atts' => $atts, 'entry' => $this ), TRUE, TRUE );

		// The filters need to be reset so additional calls to get email addresses with different params return expected results.
		$this->emailAddresses->resetFilters();

		$block = cnString::replaceWhatWith( $out, ' ' );

		$html = $atts['before'] . $block . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Echo or return the entry's IM network IDs in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry IM network.
	 *  type (array) || (string) Retrieve specific IM network types.
	 *   Permitted Types:
	 *    aim
	 *    yahoo
	 *    jabber
	 *    messenger
	 *    skype
	 *  format (string) The tokens to use to display the IM network block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %id%
	 *    %separator%
	 *  separator (string) The separator to use.
	 *  before (string) HTML to output before the IM networks.
	 *  after (string) HTML to after before the IM networks.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_im => (array) Register the methods default attributes.
	 *
	 * @url http://microformats.org/wiki/hcard-examples#New_Types_of_Contact_Info
	 * @access public
	 * @since unknown
	 *
	 * @param array $atts   Accepted values as noted above.
	 * @param bool  $cached Returns the cached data rather than querying the db.
	 *
	 * @return string
	 */
	public function getImBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred' => NULL,
			'type'      => NULL,
			'limit'     => NULL,
			'format'    => '',
			'separator' => ':',
			'before'    => '',
			'after'     => '',
			'return'    => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_im', $atts ),
			apply_filters( 'cn_output_default_atts_im', $defaults )
		);

		$atts['id'] = $this->getId();

		$out = $this->im->filterBy( 'type', $atts['type'] )
		                ->filterBy( 'preferred', $atts['preferred'] )
		                ->filterBy( 'visibility', Connections_Directory()->currentUser->canView() )
		                ->take( $atts['limit'] )
		                ->escapeFor( 'display' )
		                ->render( 'hcard', array( 'atts' => $atts, 'entry' => $this ), TRUE, TRUE );

		// The filters need to be reset so additional calls to get messenger IDs with different params return expected results.
		$this->im->resetFilters();

		$block = cnString::replaceWhatWith( $out, ' ' );

		$html = $atts['before'] . $block . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Echo or return the entry's social media network IDs in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry social media network.
	 *  type (array) || (string) Retrieve specific social media network types.
	 *   Permitted Types:
	 *    delicious
	 *    cdbaby
	 *    facebook
	 *    flickr
	 *    itunes
	 *    linked-in
	 *    mixcloud
	 *    myspace
	 *    podcast
	 *    reverbnation
	 *    rss
	 *    technorati
	 *    twitter
	 *    soundcloud
	 *    vimeo
	 *    youtube
	 *  format (string) The tokens to use to display the social media block parts.
	 *   Permitted Tokens:
	 *    %title%
	 *    %url%
	 *    %icon%
	 *    %separator%
	 *  style (string) The icon style to be used.
	 *   Permitted Styles:
	 *    wpzoom
	 *   Permitted Sizes:
	 *    16
	 *    24
	 *    32
	 *    48
	 *    64
	 *  size (int) The icon size to be used.
	 *  separator (string) The separator to use.
	 *  before (string) HTML to output before the social media networks.
	 *  after (string) HTML to after before the social media networks.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_socialmedia => (array) Register the methods default attributes.
	 *
	 * @link http://microformats.org/wiki/hcard-examples#Site_profiles
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $atts   Accepted values as noted above.
	 * @param bool  $cached Returns the cached data rather than querying the db.
	 *
	 * @return string
	 */
	public function getSocialMediaBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred' => NULL,
			'type'      => NULL,
			'format'    => '',
			'style'     => 'wpzoom',
			'size'      => 32,
			'separator' => ':',
			'before'    => '',
			'after'     => '',
			'return'    => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_social_media', $atts ),
			apply_filters( 'cn_output_default_atts_social_media', $defaults )
		);

		$atts['id'] = $this->getId();

		$rows     = array();
		$networks = $this->getSocialMedia( $atts, $cached );
		$search   = array( '%label%', '%url%', '%icon%', '%separator%' );

		$iconStyles = array( 'wpzoom' );
		$iconSizes = array( 16, 24, 32, 48, 64 );

		/*
		 * Ensure the supplied icon style and size are valid, if not reset to the default values.
		 */
		$iconStyle = ( in_array( $atts['style'], $iconStyles ) ) ? $atts['style'] : 'wpzoom';
		$iconSize  = ( in_array( $atts['size'], $iconSizes ) ) ? $atts['size'] : 32;

		if ( empty( $networks ) ) return '';

		//$out = '<span class="social-media-block">' . PHP_EOL;

		foreach ( $networks as $network ) {
			$replace   = array();
			$iconClass = array();

			/*
			 * Create the icon image class. This array will implode to a string.
			 */
			$iconClass[] = $network->type;
			$iconClass[] = $iconStyle;
			$iconClass[] = 'sz-' . $iconSize;

			$row = "\t" . '<span class="social-media-network cn-social-media-network' . ( $network->preferred ? ' cn-preferred cn-social-media-network-preferred' : '' ) . '">';

			$replace[] = '<a class="url ' . $network->type . '" href="' . $network->url . '" target="_blank" title="' . $network->name . '">' . $network->name . '</a>';

			$replace[] = '<a class="url ' . $network->type . '" href="' . $network->url . '" target="_blank" title="' . $network->name . '">' . $network->url . '</a>';

			$replace[] = '<a class="url ' . $network->type . '" href="' . $network->url . '" target="_blank" title="' . $network->name . '"><img class="' . implode( ' ', $iconClass ) . '" src="' . CN_URL . 'assets/images/icons/' . $iconStyle . '/' . $iconSize . '/' . $network->type . '.png" height="' . $iconSize . '" width="' . $iconSize . '" style="width: ' . $iconSize . 'px; height: ' . $iconSize . 'px;" alt="' . $network->name . ' Icon"/></a>';

			$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

			$row .= str_ireplace(
				$search,
				$replace,
				empty( $atts['format'] ) ? '%icon%' : $atts['format']
			);

			$row .= '</span>' . PHP_EOL;

			$rows[] = apply_filters( 'cn_output_social_media_link', cnString::replaceWhatWith( $row, ' ' ), $network, $this, $atts );
		}

		//$out .= '</span>' . PHP_EOL;

		//$out = cnString::replaceWhatWith( $out, ' ' );

		//$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		$block = '<span class="social-media-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL .'</span>';

		$block = apply_filters( 'cn_output_social_media_links', $block, $networks, $this, $atts );

		$html = $atts['before'] . $block . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Return the entry's websites in a HTML hCard compliant string.
	 *
	 * @deprecated since 0.7.2.0
	 */
	public function getWebsiteBlock() {

		/*
		 * Set some defaults so the result resembles how the previous rendered.
		 */
		return $this->getLinkBlock( array( 'format' => '%label%%separator% %url%' , 'type' => array( 'personal', 'website' ) , 'return' => TRUE ) );
	}

	/**
	 * Echo or return the entry's links in a HTML hCard compliant string.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry link.
	 *  type (array) || (string) Retrieve specific link types.
	 *   Permitted Types:
	 *    website
	 *    blog
	 *  format (string) The tokens to use to display the phone number block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %title%
	 *    %url%
	 *    %image%
	 *    %separator%
	 *  label (string) The label to be displayed for the links.
	 *  size (string) The valid image sizes. Valid values are: mcr || tny || vsm || sm || lg || xlg
	 *  separator (string) The separator to use.
	 *  before (string) HTML to output before the social media networks.
	 *  after (string) HTML to after before the social media networks.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_link => (array) Register the methods default attributes.
	 *
	 * @link  http://microformats.org/wiki/hcard-examples#Site_profiles
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param  array $atts   Accepted values as noted above.
	 * @param  bool  $cached Returns the cached data rather than querying the db.
	 *
	 * @return string
	 */
	public function getLinkBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred' => NULL,
			'type'      => NULL,
			'format'    => '',
			'label'     => NULL,
			'size'      => 'lg',
			'icon_size' => 32,
			'separator' => ':',
			'before'    => '',
			'after'     => '',
			'return'    => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_link', $atts ),
			apply_filters( 'cn_output_default_atts_link', $defaults )
		);

		$atts['id'] = $this->getId();

		$rows          = array();
		$links         = $this->getLinks( $atts, $cached );
		$search        = array( '%label%', '%title%', '%url%', '%image%', '%icon%', '%separator%' );
		$iconSizes     = array( 16, 24, 32, 48, 64 );
		$targetOptions = array( 'new' => '_blank', 'same' => '_self' );

		if ( empty( $links ) ) return '';

		/*
		 * Ensure the supplied size is valid, if not reset to the default value.
		 */

		$icon = array();

		$icon['width']  = in_array( $atts['icon_size'], $iconSizes ) ? $atts['icon_size'] : 32;
		$icon['height'] = $icon['width'];
		$icon['src']    = CN_URL . 'assets/images/icons/link/link_' . $icon['width'] . '.png';

		foreach ( $links as $link ) {

			$icon = apply_filters( 'cn_output_link_icon', $icon, $link->type );

			$replace = array();

			if ( empty( $atts['label'] ) ) {

				$name = empty( $link->name ) ? '' : $link->name;

			} else {

				$name = $atts['label'];
			}

			$url    = cnSanitize::field( 'url', $link->url );
			$target = array_key_exists( $link->target, $targetOptions ) ? $targetOptions[ $link->target ] : '_self';
			$follow = $link->follow ? '' : 'rel="nofollow"';

			$replace[] = '<span class="link-name">' . $name . '</span>';

			// The `notranslate` class is added to prevent Google Translate from translating the text.
			$replace[] = empty( $link->title ) ? '' : '<a class="url" href="' . $url . '"' . ' target="' . $target . '" ' . $follow . '>' . $link->title . '</a>';
			$replace[] = '<a class="url notranslate" href="' . $url . '"' . ' target="' . $target . '" ' . $follow . '>' . $url . '</a>';

			if ( FALSE !== filter_var( $link->url, FILTER_VALIDATE_URL ) &&
			     FALSE !== strpos( $atts['format'], '%image%' ) ) {

				$screenshot = new cnSiteShot(
					array(
						'url'    => $link->url,
						'alt'    => $url,
						'title'  => $name,
						'target' => $target,
						'follow' => $link->follow,
						'return' => TRUE,
					)
				);

				$size = $screenshot->setSize( $atts['size'] );

				/** @noinspection CssInvalidPropertyValue */
				$screenshot->setBefore( '<span class="cn-image-style" style="display: inline-block;"><span style="display: block; max-width: 100%; width: ' . $size['width'] . 'px">' );
				$screenshot->setAfter( '</span></span>' );

				$replace[] = $screenshot->render();

			} else {

				$replace[] = '';
			}

			$replace[] = '<span class="link-icon"><a class="url" title="' . $link->title . '" href="' . $url . '" target="' . $target . '" ' . $follow . '><img src="' . $icon['src'] . '" height="' . $icon['height'] . '" width="' . $icon['width'] . '"/></a></span>';

			$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

			$row = "\t" . '<span class="link ' . $link->type . ' cn-link' . ( $link->preferred ? ' cn-preferred cn-link-preferred' : '' ) . '">';

			$row .= str_ireplace(
				$search,
				$replace,
				empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %title%' : $defaults['format'] ) : $atts['format']
			);

			$row .= '</span>';

			$rows[] = apply_filters( 'cn_output_link', cnString::replaceWhatWith( $row, ' ' ), $link, $this, $atts );
		}

		$block = '<span class="link-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL .'</span>';

		$block = apply_filters( 'cn_output_links', $block, $links, $this, $atts );

		$html = $atts['before'] . $block . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}


	/**
	 * Echo or return the entry's dates in a HTML string.
	 *
	 * Accepted options for the $atts property are:
	 *  preferred (bool) Retrieve the preferred entry date.
	 *  type (array) || (string) Retrieve specific date types.
	 *   Permitted Types:
	 *    baptism
	 *    certification
	 *    employment
	 *    membership
	 *    graduate_high_school
	 *    graduate_college
	 *    ordination
	 *
	 *  format (string) The tokens to use to display the date block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %date%
	 *    %separator%
	 *  name_format (string) Tokens for the parts of the name. See cnOutput::getNameBlock
	 *  date_format (string) See http://php.net/manual/en/function.date.php
	 *  separator (string) The separator to use between the label and date.
	 *  before (string) HTML to output before the dates.
	 *  after (string) HTML to after before the dates.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * Filters:
	 *  cn_output_default_atts_date => (array) Register the methods default attributes.
	 *
	 * @access public
	 * @since 0.7.3
	 *
	 * @param array $atts   Accepted values as noted above.
	 * @param bool  $cached Returns the cached data rather than querying the db.
	 *
	 * @return string
	 */
	public function getDateBlock( $atts = array(), $cached = TRUE ) {

		$defaults = array(
			'preferred'   => NULL,
			'type'        => NULL,
			'format'      => '',
			'name_format' => '',
			'date_format' => cnSettingsAPI::get( 'connections', 'display_general', 'date_format' ),
			'separator'   => ':',
			'before'      => '',
			'after'       => '',
			'return'      => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_date', $atts ),
			apply_filters( 'cn_output_default_atts_date', $defaults )
		);

		$atts['id'] = $this->getId();

		$dates = $this->getDates( $atts , $cached );
		$search = array( '%label%' , '%date%' , '%separator%' );

		if ( empty( $dates ) ) return '';

		$out = '<span class="date-block">' . PHP_EOL;

		foreach ( $dates as $date ) {

			try {

				// Go thru the formatting acrobats to make sure DateTime is feed a valid date format
				// just in case a user manages to input an incorrect date or date format.
				$dateObject = new DateTime( date( 'm/d/Y', strtotime( $date->date ) ) );

			} catch ( Exception $e ) {

				continue;
			}

			$replace = array();

			$out .= "\t" . '<span class="vevent cn-date' . ( $date->preferred ? ' cn-preferred cn-date-preferred' : '' ) . '">';

			// Hidden elements are to maintain hCalendar spec compatibility
			$replace[] = ( empty( $date->name ) ) ? '' : '<span class="date-name">' . $date->name . '</span>';
			//$replace[] = ( empty($date->date) ) ? '' : '<span class="dtstart"><span class="value" style="display: none;">' . $dateObject->format( 'Y-m-d' ) . '</span><span class="date-displayed">' . $dateObject->format( $atts['date_format'] ) . '</span></span>';
			$replace[] = ( empty( $date->date ) ) ? '' : '<abbr class="dtstart" title="' . $dateObject->format( 'Ymd' ) .'">' . date_i18n( $atts['date_format'] , strtotime( $date->date ) , FALSE ) /*$dateObject->format( $atts['date_format'] )*/ . '</abbr><span class="summary" style="display:none">' . $date->name . ' - ' . $this->getName( array( 'format' => $atts['name_format'] ) ) . '</span><span class="uid" style="display:none">' . $dateObject->format( 'YmdHis' ) . '</span>';
			$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

			$out .= str_ireplace(
				$search,
				$replace,
				empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %date%' : $defaults['format'] ) : $atts['format']
			);

			$out .= '</span>' . PHP_EOL;
		}

		$out .= '</span>' . PHP_EOL;

		$out = cnString::replaceWhatWith( $out, ' ' );

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Echo or return the entry's birthday in a HTML string.
	 *
	 * Accepted options for the $atts property are:
	 *  format (string) The tokens to use to display the date block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %date%
	 *    %separator%
	 *  name_format (string) Tokens for the parts of the name. See cnOutput::getNameBlock
	 *  date_format (string) See http://php.net/manual/en/function.date.php
	 *  separator (string) The separator to use between the label and date.
	 *  before (string) HTML to output before the dates.
	 *  after (string) HTML to after before the dates.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param string $format deprecated since 0.7.3
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function getBirthdayBlock( $format = '', $atts = array() ) {
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array();
		$defaults['format'] = '';
		$defaults['name_format'] = '';

		// The $format option has been deprecated since 0.7.3. If it has been supplied override the $defaults['date_format] value.
		$defaults['date_format'] = empty( $format ) ? 'F jS' : $format;

		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;

		$atts = cnSanitize::args( $atts, $defaults );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		$out = '';
		$search = array( '%label%' , '%date%' , '%separator%' );
		$replace = array();

		if ( ! $this->getBirthday() ) return '';

		/*
		 * NOTE: The vevent span is for hCalendar compatibility.
		 * NOTE: The second birthday span [hidden] is for hCard compatibility.
		 * NOTE: The third span series [hidden] is for hCalendar compatibility.
		 */
		$out .= '<div class="vevent"><span class="birthday">';

		$replace[] = '<span class="date-name">' . __( 'Birthday', 'connections' ) . '</span>';
		$replace[] = '<abbr class="dtstart" title="' . $this->getBirthday( 'Ymd' ) .'">' . date_i18n( $atts['date_format'] , strtotime( $this->getBirthday( 'Y-m-d' ) ) , FALSE ) . '</abbr>';
		$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

		$out .= str_ireplace(
			$search,
			$replace,
			empty( $atts['format'] ) ? '%label%%separator% %date%' : $atts['format']
		);

		$out .= '<span class="bday" style="display:none">' . $this->getBirthday( 'Y-m-d' ) . '</span>';
		$out .= '<span class="summary" style="display:none">' . __( 'Birthday', 'connections' ) . ' - ' . $this->getName( array( 'format' => $atts['name_format'] ) ) . '</span><span class="uid" style="display:none">' . $this->getBirthday( 'YmdHis' ) . '</span>';

		$out .= '</div>';

		$out = cnString::replaceWhatWith( $out, ' ' );

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Echo or return the entry's anniversary in a HTML string.
	 *
	 * Accepted options for the $atts property are:
	 *  format (string) The tokens to use to display the date block parts.
	 *   Permitted Tokens:
	 *    %label%
	 *    %date%
	 *    %separator%
	 *  name_format (string) Tokens for the parts of the name. See cnOutput::getNameBlock
	 *  date_format (string) See http://php.net/manual/en/function.date.php
	 *  separator (string) The separator to use between the label and date.
	 *  before (string) HTML to output before the dates.
	 *  after (string) HTML to after before the dates.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * @access public
	 * @since  0.7.3
	 *
	 * @param string $format deprecated since 0.7.3
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function getAnniversaryBlock( $format = '', $atts = array() ) {
		/*
		 * // START -- Set the default attributes array. \\
		 */
		$defaults = array();
		$defaults['format'] = '';
		$defaults['name_format'] = '';

		// The $format option has been deprecated since 0.7.3. If it has been supplied override the $defaults['date_format] value.
		$defaults['date_format'] = empty( $format ) ? 'F jS' : $format;
		$defaults['separator'] = ':';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['return'] = FALSE;

		$atts = cnSanitize::args( $atts, $defaults );
		/*
		 * // END -- Set the default attributes array if not supplied. \\
		 */

		$out = '';
		$search = array( '%label%' , '%date%' , '%separator%' );
		$replace = array();

		if ( ! $this->getAnniversary() ) return '';

		/*
		 * NOTE: The vevent span is for hCalendar compatibility.
		 * NOTE: The second birthday span [hidden] is for hCard compatibility.
		 * NOTE: The third span series [hidden] is for hCalendar compatibility.
		 */
		$out .= '<div class="vevent"><span class="anniversary">';

		$replace[] = '<span class="date-name">' . __( 'Anniversary', 'connections' ) . '</span>';
		$replace[] = '<abbr class="dtstart" title="' . $this->getAnniversary( 'Ymd' ) .'">' . date_i18n( $atts['date_format'] , strtotime( $this->getAnniversary( 'Y-m-d' ) ) , FALSE ) . '</abbr>';
		$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

		$out .= str_ireplace(
			$search,
			$replace,
			empty( $atts['format'] ) ? '%label%%separator% %date%' : $atts['format']
		);

		$out = cnString::replaceWhatWith( $out, ' ' );

		$out .= '<span class="bday" style="display:none">' . $this->getAnniversary( 'Y-m-d' ) . '</span>';
		$out .= '<span class="summary" style="display:none">' . __( 'Anniversary', 'connections' ) . ' - ' . $this->getName( array( 'format' => $atts['name_format'] ) ) . '</span><span class="uid" style="display:none">' . $this->getAnniversary( 'YmdHis' ) . '</span>';

		$out .= '</div>';

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Echo or returns the entry Notes.
	 *
	 * Registers the global $wp_embed because the run_shortcode method needs
	 * to run before the do_shortcode function for the [embed] shortcode to fire
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array
	 *
	 * @return string
	 */
	public function getNotesBlock( $atts = array() ) {

		$defaults = array(
			'before'    => '',
			'after'     => '',
			'return'    => FALSE
		);

		/**
		 * Allow extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_notes', $atts ),
			apply_filters( 'cn_output_default_atts_notes', $defaults )
		);

		$out = apply_filters( 'cn_output_notes', $this->getNotes() );

		$out = '<div class="cn-notes">' . $atts['before'] . $out .  $atts['after'] . '</div>' . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Echo or returns the entry Bio.
	 *
	 * Registers the global $wp_embed because the run_shortcode method needs
	 * to run before the do_shortcode function for the [embed] shortcode to fire
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array
	 *
	 * @return string
	 */
	public function getBioBlock( $atts = array() ) {

		$defaults = array(
			'before'    => '',
			'after'     => '',
			'return'    => FALSE
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_bio', $atts ),
			apply_filters( 'cn_output_default_atts_bio', $defaults )
		);

		$out = apply_filters( 'cn_output_bio', $this->getBio() );

		$out = '<div class="cn-biography">' . $atts['before'] . $out . $atts['after'] . '</div>' . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Renders an excerpt of the bio or supplied string.
	 *
	 * @access public
	 * @since  8.5.19
	 *
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function excerpt( $atts = array() ) {

		$defaults = array(
			'before'    => '',
			'after'     => '',
			'length'    => apply_filters( 'cn_excerpt_length', 55 ),
			'more'      => apply_filters( 'cn_excerpt_more', __( '&hellip;', 'connections' ) ),
			'return'    => FALSE
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.19
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_excerpt', $atts ),
			apply_filters( 'cn_output_default_atts_excerpt', $defaults )
		);

		$excerpt = $this->getExcerpt( $atts, 'display' );

		/**
		 * Apply the default filters.
		 *
		 * @since 8.5.19
		 */
		$excerpt = apply_filters( 'cn_output_excerpt', $excerpt );
		$html = '<div class="cn-excerpt">' . $atts['before'] . $excerpt . $atts['after'] . '</div>' . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Displays the category list in a HTML list or custom format.
	 *
	 * NOTE: This is the Connections equivalent of @see get_the_category_list() in WordPress core ../wp-includes/category-template.php
	 *
	 * @access public
	 * @since  unknown
	 *
	 * @param array $atts {
	 * Optional. An array of arguments.
	 *
	 *     @type string $container_tag    The HTML tag to be used for the container element.
	 *                                    Default: div
	 *     @type string $label_tag        The HTML tag to be used for the category label element.
	 *                                    Default: span
	 *     @type string $item_tag         The HTML tag to be used for the category element.
	 *                                    Default: span
	 *     @type string $type             The display type to be used to display the categories.
	 *                                    Accepts: block|list
	 *                                    Default: block
	 *     @type string $list             If the $type is list, which type?
	 *                                    Accepts: ordered|unordered
	 *                                    Default: unordered
	 *     @type string $label            The label to be displayed before the categories.
	 *                                    Default: Categories:
	 *     @type string $separator        The category separator used when separating categories when $type == list
	 *                                    Default: ', '
	 *     @type string $parent_separator The separator to be used when displaying the category's hierarchy.
	 *                                    Default: ' &raquo; '
	 *     @type string $before           String content to display before the categories.
	 *     @type string $after            String content to display after the categories.
	 *     @type bool   $link             Whether or not render the categories as permalinks.
	 *                                    Default: false
	 *     @type bool   $parents          Whether or not to display the category hierarchy.
	 *                                    Default: false
	 *     @type bool   $return           Whether or not to echo or return the HTML.
	 *                                    Default: false
	 * }
	 *
	 * @return string
	 */
	public function getCategoryBlock( $atts = array() ) {

		global $wp_rewrite;

		$defaults = array(
			'container_tag'    => 'div',
			'label_tag'        => 'span',
			'item_tag'         => 'span',
			'type'             => 'block',
			'list'             => 'unordered',
			'label'            => __( 'Categories:', 'connections' ) . ' ',
			'separator'        => ', ',
			'parent_separator' => ' &raquo; ',
			'before'           => '',
			'after'            => '',
			'link'             => FALSE,
			'parents'          => FALSE,
			'child_of'         => 0,
			'return'           => FALSE,
		);

		/**
		 * All extensions to filter the method default and supplied args.
		 *
		 * @since 8.5.18
		 */
		$atts = cnSanitize::args(
			apply_filters( 'cn_output_atts_category', $atts ),
			apply_filters( 'cn_output_default_atts_category', $defaults )
		);

		$categories = $this->getCategory( array( 'child_of' => $atts['child_of'] ) );
		$count      = count( $categories );
		$html       = '';
		$label      = '';
		$items      = array();

		if ( empty( $categories ) ) {

			return $html;
		}

		if ( 'list' == $atts['type'] ) {

			$atts['item_tag'] = 'li';
		}

		if ( 0 < strlen( $atts['label'] ) ) {

			$label = sprintf(
				'<%1$s class="cn_category_label">%2$s</%1$s> ',
				$atts['label_tag'],
				esc_html( $atts['label'] )
			);
		}

		$i = 1;

		foreach ( $categories as $category ) {

			$text = '';

			if ( $atts['parents'] ) {

				// If the term is a root parent, skip.
				if ( 0 !== $category->parent ) {

					$text .= cnTemplatePart::getCategoryParents(
						$category->parent,
						array(
							'link'       => $atts['link'],
							'separator'  => $atts['parent_separator'],
							'force_home' => $this->directoryHome['force_home'],
							'home_id'    => $this->directoryHome['page_id'],
						)
					);
				}
			}

			if ( $atts['link'] ) {

				$rel = is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ? 'rel="category tag"' : 'rel="category"';

				$url = cnTerm::permalink(
					$category,
					'category',
					array(
						'force_home' => $this->directoryHome['force_home'],
						'home_id'    => $this->directoryHome['page_id'],
					)
				);

				$text .= '<a href="' . $url . '" ' . $rel . '>' . esc_html( $category->name ) . '</a>';

			} else {

				$text .= esc_html( $category->name );
			}

			$items[] = apply_filters(
				'cn_entry_output_category_item',
				sprintf(
					'<%1$s class="cn-category-name cn_category cn-category-%2$d">%3$s%4$s</%1$s>', // The `cn_category` class is named with an underscore for backward compatibility.
					$atts['item_tag'],
					$category->term_id,
					$text,
					$count > $i && 'list' !== $atts['type'] ? esc_html( $atts['separator'] ) : ''
				),
				$category,
				$count,
				$i,
				$atts,
				$this
			);

			$i++; // Increment here so the correct value is passed to the filter.
		}

		/*
		 * Remove NULL, FALSE and empty strings (""), but leave values of 0 (zero).
		 * Filter our these in case someone hooks into the `cn_entry_output_category_item` filter and removes a category
		 * by returning an empty value.
		 */
		$items = array_filter( $items, 'strlen' );

		/**
		 * @since 8.6.12
		 */
		$items = apply_filters( 'cn_entry_output_category_items', $items );

		if ( 'list' == $atts['type'] ) {

			$html .= sprintf(
				'<%1$s class="cn-category-list">%2$s</%1$s>',
				'unordered' === $atts['list'] ? 'ul' : 'ol',
				implode( '', $items )
			);

		} else {

			$html .= implode( '', $items );
		}

		$html = apply_filters(
			'cn_entry_output_category_container',
			sprintf(
				'<%1$s class="cn-categories">%2$s</%1$s>' . PHP_EOL,
				$atts['container_tag'],
				$atts['before'] . $label . $html . $atts['after']
			),
			$atts
		);

		return $this->echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Renders the custom meta data fields assigned to the entry.
	 *
	 * This will also run any actions registered for a custom metaboxes
	 * and its fields. The actions should hook into `cn_output_meta_field-{key}`
	 * to be rendered.
	 *
	 * Accepted option for the $atts property are:
	 * 	key (string) The meta key to retrieve.
	 * 	single (bool) Whether or not to return a single value
	 * 		if multiple values exists for the supplied `key`.
	 * 			NOTE: The `key` attribute must be supplied.
	 * 			NOTE: If multiple values exist for a given `key` only first found will be returned.
	 *  display_custom (bool) Whether or not to display any custom content meta blocks via their registered callbacks.
	 *      If any are registered the callback output will be rendered before the custom fields meta block @see cnOutput::renderMetaBlock().
	 *      For better control @see cnOutput::getContentBlock() can be used.
	 *
	 * @access public
	 * @since 0.8
	 * @uses wp_parse_args()
	 * @uses apply_filters()
	 * @uses has_action()
	 * @uses do_action()
	 * @param  array  $atts The attributes array.
	 * @param  array  $shortcode_atts If this is used within the shortcode template loop, the shortcode atts
	 * 		should be passed so the shortcode atts can be passed by do_action() to allow access to the action callback.
	 * @param  cnTemplate|null $template If this is used within the shortcode template loop, the template object
	 * 		should be passed so the template object can be passed by do_action() to allow access to the action callback.
	 */
	public function getMetaBlock( $atts, $shortcode_atts, $template ) {

		// @todo Implement 'merge_keys'.
		//
		// Whether or not to merge duplicate keys and their respective value.
		// Expected result of a merge would be would be an indexed array:
		//
		// array(
		// 		array(
		// 			'meta_key' => 'the-duplicate-key',
		// 			'meta_value' => array(
		// 				'meta_id' => 'value 1',
		// 				'meta_id' => 'value 2',
		// 				'meta_id' => 'and so on ...',
		// 			)
		// 		)
		// )
		//
		// $this->renderMetablock() would have to be updated to account for the
		// 'meta_value' array, I think.
		//
		// NOTE: This should actually be done in cnEntry::getMeta and not here.
		$defaults = array(
			'key'             => '',
			'single'          => FALSE,
			'merge_keys'      => FALSE,
			'display_custom'  => FALSE,
			);

		$atts = wp_parse_args( apply_filters( 'cn_output_meta_block_atts', $atts ), $defaults );

		$results = $this->getMeta( $atts );

		if ( ! empty( $results ) ) {

			if ( empty( $atts['key'] ) ) {

				$metadata = $results;

			} else {

				// Rebuild the results array for consistency in for the output methods/actions.

				$metadata = array();

				foreach ( $results as $key => $value ) {

					$metadata[] = array( 'meta_key' => $key, 'meta_value' => $value );
				}
			}

			foreach ( $metadata as $key => $value ) {

				if ( $atts['display_custom'] && has_action( 'cn_output_meta_field-' . $key ) ) {

					do_action( 'cn_output_meta_field-' . $key, $key, $value, $this, $shortcode_atts, $template );

					unset( $metadata[ $key ] );
				}
			}

			$this->renderMetaBlock( $metadata );
		}
	}

	/**
	 * Outputs the data saved in the "Custom Fields" entry metabox.
	 * This should not be confused with the fields registered with
	 * cnMetaboxAPI. Those fields should be output using a registered
	 * action which runs in $this->getMetaBlock().
	 *
	 * @access private
	 * @since 0.8
	 * @uses wp_parse_args()
	 * @uses apply_filters()
	 * @param  array  $metadata The metadata array passed from $this->getMetaBlock(). @see self::getMetaBlock().
	 *
	 * @return string
	 */
	private function renderMetaBlock( $metadata ) {

		$out = '';

		$defaults = array(
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'key_tag'       => 'span',
			'value_tag'     => 'span',
			'separator'     => ': ',
			'before'        => '',
			'after'         => '',
		);

		$atts = wp_parse_args( apply_filters( 'cn_output_meta_atts', $defaults ), $defaults );

		foreach ( (array) $metadata as $key => $value ) {

			// Do not render any private keys; ie. ones that begin with an underscore
			// or any fields registered as part of a custom metabox.
			if ( cnMeta::isPrivate( $key, 'entry' ) ) continue;

			$out .= apply_filters(
				'cn_entry_output_meta_key',
				sprintf(
					'<%1$s><%2$s class="cn-entry-meta-key">%3$s%4$s</%2$s><%5$s class="cn-entry-meta-value">%6$s</%5$s></%1$s>' . PHP_EOL,
					$atts['item_tag'],
					$atts['key_tag'],
					trim( $key ),
					$atts['separator'],
					$atts['value_tag'],
					implode( ', ', (array) $value )
				),
				$atts,
				$key,
				$value
			);
		}

		if ( empty( $out ) ) return '';

		$out = apply_filters(
			'cn_entry_output_meta_container',
			sprintf(
				'<%1$s class="cn-entry-meta">%2$s</%1$s>' . PHP_EOL,
				$atts['container_tag'],
				$out
			),
			$atts,
			$metadata
		);

		echo $atts['before'] . $out . $atts['after'] . PHP_EOL;
	}

	/**
	 * Run the actions registered to custom content blocks.
	 *
	 * Render any custom content blocks registered to the `cn_entry_output_content-{id}` action hook.
	 *
	 * This will also run any actions registered for a custom metaboxes and its fields.
	 * The actions should hook into `cn_output_meta_field-{key}` to be rendered.
	 *
	 * Accepted option for the $atts property are:
	 * 	id (string) The custom block ID to render.
	 * 	order (mixed) array | string  An indexed array of custom content block IDs that should be rendered in the order in the array.
	 * 		If a string is provided, it should be a comma delimited string containing the content block IDs. It will be converted to an array.
	 * 	exclude (array) An indexed array of custom content block IDs that should be excluded from being rendered.
	 * 	include (array) An indexed array of custom content block IDs that should be rendered.
	 * 		NOTE: Custom content block IDs in `exclude` outweigh custom content block IDs in include. Meaning if the
	 * 		same custom content block ID exists in both, the custom content block will be excluded.
	 *
	 * @access public
	 * @since 0.8
	 * @uses do_action()
	 * @uses wp_parse_args()
	 * @uses apply_filters()
	 * @uses has_action()
	 * @param  mixed  $atts array | string [optional] The custom content block(s) to render.
	 * @param  array  $shortcode_atts [optional] If this is used within the shortcode template loop, the shortcode atts
	 * 		should be passed so the shortcode atts can be passed by do_action() to allow access to the action callback.
	 * @param  cnTemplate|null $template [optional] If this is used within the shortcode template loop, the template object
	 * 		should be passed so the template object can be passed by do_action() to allow access to the action callback.
	 *
	 * @return string The HTML output of the custom content blocks.
	 */
	public function getContentBlock( $atts = array(), $shortcode_atts = array(), $template = NULL ) {

		$blockContainerContent = '';

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			$settings = cnSettingsAPI::get( 'connections', 'connections_display_single', 'content_block' );

		} else {

			$settings = cnSettingsAPI::get( 'connections', 'connections_display_list', 'content_block' );
		}

		$order   = isset( $settings['order'] ) ? $settings['order'] : array();
		$include = isset( $settings['active'] ) ? $settings['active'] : array();
		$exclude = empty( $include ) ? $order : array();
		$titles  = array();

		$defaults = array(
			'id'            => '',
			'order'         => is_string( $atts ) && ! empty( $atts ) ? $atts : $order,
			'exclude'       => is_string( $atts ) && ! empty( $atts ) ? '' : $exclude,
			'include'       => is_string( $atts ) && ! empty( $atts ) ? '' : $include,
			'layout'        => 'list',
			'container_tag' => 'div',
			'block_tag'     => 'div',
			'header_tag'    => 'h3',
			'before'        => '',
			'after'         => '',
			'return'        => FALSE
			);

		$atts = wp_parse_args( apply_filters( 'cn_output_content_block_atts', $atts ), $defaults );

		if ( ! empty( $atts['id'] ) ) {

			$blocks = array( $atts['id'] );

		} elseif ( ! empty( $atts['order'] ) ) {

			$blocks = cnFunction::parseStringList( $atts['order'], ',' );
		}

		// Nothing to render, exit.
		if ( empty( $blocks ) ) return '';

		// Cleanup user input. Trim whitespace and convert to lowercase.
		$blocks = array_map( 'strtolower', array_map( 'trim', $blocks ) );

		// Output the registered action in the order supplied by the user.
		foreach ( $blocks as $key ) {

			isset( $blockNumber ) ? $blockNumber++ : $blockNumber = 1;

			// Exclude/Include the metaboxes that have been requested to exclude/include.
			if ( ! empty( $atts['exclude'] ) ) {

				if ( in_array( $key, $atts['exclude'] ) ) continue;

			} else {

				if ( ! empty( $atts['include'] ) ) {

					if ( ! in_array( $key, $atts['include'] ) ) continue;
				}
			}

			ob_start();

			// If the hook has a registered meta data output callback registered, lets run it.
			if ( has_action( 'cn_output_meta_field-' . $key ) ) {

				// Grab the meta.
				$results = $this->getMeta( array( 'key' => $key, 'single' => TRUE ) );

				if ( ! empty( $results ) ) {

					do_action( "cn_output_meta_field-$key", $key, $results, $this, $shortcode_atts, $template );
				}
			}

			// Render the "Custom Fields" meta block content.
			if ( 'meta' == $key ) {

				$this->getMetaBlock( array(), $shortcode_atts, $template );
			}

			$hook = "cn_entry_output_content-$key";

			if ( has_action( $hook ) ) do_action( $hook, $this, $shortcode_atts, $template );

			$blockContent = ob_get_clean();

			if ( empty( $blockContent ) ) continue;

			$blockID = $this->getSlug() . '-' . $blockNumber;

			// Store the title in an array that can be accessed/passed from outside the content block loop.
			// And if there is no title for some reason, create one from the key.
			if ( $name = cnOptions::getContentBlocks( $key ) ) {

				$titles[ $blockID ] = $name;

			} elseif ( $name = cnOptions::getContentBlocks( $key, 'single' ) ) {

				$titles[ $blockID ] = $name;

			} else {

				$titles[ $blockID ] = ucwords( str_replace( array( '-', '_' ), ' ', $key ) );
			}

			//$titles[ $blockID ] = cnOptions::getContentBlocks( $key ) ? cnOptions::getContentBlocks( $key ) : ucwords( str_replace( array( '-', '_' ), ' ', $key ) );

			$blockContainerContent .= apply_filters(
				'cn_entry_output_content_block',
				sprintf(
					'<%2$s class="cn-entry-content-block cn-entry-content-block-%3$s" id="cn-entry-content-block-%4$s">%1$s%5$s</%2$s>' . PHP_EOL,
					sprintf(
						'<%1$s>%2$s</%1$s>',
						$atts['header_tag'],
						$titles[ $blockID ]
					),
					$atts['block_tag'],
					$key,
					$blockID,
					$blockContent
				),
				$this,
				$key,
				$blockID,
				$titles[ $blockID ],
				$blockContent,
				$atts,
				$shortcode_atts
			);
		}

		if ( empty( $blockContainerContent ) ) return '';

		$out = apply_filters(
			'cn_entry_output_content_block_container',
			sprintf(
				'<%1$s class="cn-entry-content-block-%2$s">%3$s</%1$s>' . PHP_EOL,
				$atts['container_tag'],
				$atts['layout'],
				$blockContainerContent
			),
			$this,
			$blockContainerContent,
			$titles,
			$atts,
			$shortcode_atts
		);

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Displays the category list for use in the class tag.
	 *
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @param bool    $return [optional] Return instead of echo.
	 * @return string
	 */
	public function getCategoryClass( $return = FALSE ) {

		$categories = $this->getCategory();
		$out        = array();

		if ( empty( $categories ) ) return '';

		foreach ( $categories as $category ) {
			$out[] = $category->slug;
		}

		return $this->echoOrReturn( $return, implode( ' ', $out ) );
	}

	/**
	 *
	 *
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @return string
	 */
	public function getRevisionDateBlock() {
		return '<span class="rev">' . date( 'Y-m-d', strtotime( $this->getUnixTimeStamp() ) ) . 'T' . date( 'H:i:s', strtotime( $this->getUnixTimeStamp() ) ) . 'Z' . '</span>' . "\n";
	}

	/**
	 *
	 *
	 * @access private
	 * @since unknown
	 * @version 1.0
	 * @return string
	 */
	public function getLastUpdatedStyle() {
		$age = (int) abs( time() - strtotime( $this->getUnixTimeStamp() ) );
		if ( $age < 657000 ) // less than one week: red
			$ageStyle = ' color:red; ';
		elseif ( $age < 1314000 ) // one-two weeks: maroon
			$ageStyle = ' color:maroon; ';
		elseif ( $age < 2628000 ) // two weeks to one month: green
			$ageStyle = ' color:green; ';
		elseif ( $age < 7884000 ) // one - three months: blue
			$ageStyle = ' color:blue; ';
		elseif ( $age < 15768000 ) // three to six months: navy
			$ageStyle = ' color:navy; ';
		elseif ( $age < 31536000 ) // six months to a year: black
			$ageStyle = ' color:black; ';
		else      // more than one year: don't show the update age
			$ageStyle = ' display:none; ';
		return $ageStyle;
	}

	/**
	 *
	 *
	 * @access public
	 * @since unknown
	 * @version 1.0
	 * @deprecated
	 * @return string|null
	 */
	public function returnToTopAnchor() {

		cnTemplatePart::returnToTop();
	}

	/**
	 * Outputs the vCard download permalink.
	 *
	 * Accepted attributes for the $atts array are:
	 *  class (string) The link class attribute.
	 *  text (string) The anchor text.
	 *  title (string) The link title attribute.
	 *  format (string) The tokens to use to display the vcard link block parts.
	 *   Permitted Tokens:
	 *    %text%
	 *    %icon%
	 *  follow (bool) Add add the rel="nofollow" attribute if set to FALSE
	 *  size (int) The icon size. Valid values are: 16, 24, 32, 48
	 *  slug (string) The entry's slug ID.
	 *  before (string) HTML to output before the email addresses.
	 *  after (string) HTML to after before the email addresses.
	 *  return (bool) Return string if set to TRUE instead of echo string.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @uses wp_parse_args()
	 * @param array   $atts [optional]
	 * @return string
	 */
	public function vcard( $atts = array() ) {

		/**
		 * @var wp_rewrite $wp_rewrite
		 * @var connectionsLoad $connections
		 */
		global $wp_rewrite, $connections;

		// The class.seo.file is only loaded in the frontend; do not attempt to remove the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( FALSE );

		$base      = get_option( 'connections_permalink' );
		$name      = $base['name_base'];
		$homeID    = $connections->settings->get( 'connections', 'home_page', 'page_id' ); // Get the directory home page ID.
		$piece     = array();
		$id        = FALSE;
		$token     = FALSE;
		$iconSizes = array( 16, 24, 32, 48 );
		$search    = array( '%text%', '%icon%' );
		$replace   = array();

		// These are values will need to be added to the query string in order to download unlisted entries from the admin.
		if ( 'unlisted' === $this->getVisibility() ) {
			$id = $this->getId();
			$token = wp_create_nonce( 'download_vcard_' . $this->getId() );
		}

		$defaults = array(
			'class'  => '',
			'text'   => __( 'Add to Address Book.', 'connections' ),
			'title'  => __( 'Download vCard', 'connections' ),
			'format' => '',
			'size'   => 24,
			'follow' => FALSE,
			'slug'   => '',
			'before' => '',
			'after'  => '',
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts , $defaults );

		/*
		 * Ensure the supplied size is valid, if not reset to the default value.
		 */
		$iconSize = in_array( $atts['size'], $iconSizes ) ? $atts['size'] : 32;

		// Create the permalink base based on context where the entry is being displayed.
		if ( in_the_loop() && is_page() ) {

			$permalink = trailingslashit( get_permalink() );

		} else {

			$permalink = trailingslashit( get_permalink( $homeID ) );
		}

		if ( ! empty( $atts['class'] ) ) $piece[] = 'class="' . $atts['class'] .'"';
		if ( ! empty( $atts['slug'] ) ) $piece[] = 'id="' . $atts['slug'] .'"';
		if ( ! empty( $atts['title'] ) ) $piece[] = 'title="' . $atts['title'] .'"';
		if ( ! empty( $atts['target'] ) ) $piece[] = 'target="' . $atts['target'] .'"';
		if ( ! $atts['follow'] ) $piece[] = 'rel="nofollow"';

		if ( $wp_rewrite->using_permalinks() ) {

			$piece[] = 'href="' . esc_url( add_query_arg( array( 'cn-id' => $id, 'cn-token' => $token ), $permalink . $name . '/' . $this->getSlug() . '/vcard/' ) ) . '"';

		} else {

			$piece[] = 'href="' . esc_url( add_query_arg( array( 'cn-entry-slug' => $this->getSlug(), 'cn-process' => 'vcard', 'cn-id' => $id, 'cn-token' => $token ), $permalink ) ) . '"';
		}

		$out = '<span class="vcard-block">';

		$replace[] = '<a ' . implode( ' ', $piece ) . '>' . $atts['text'] . '</a>';

		$replace[] = '<a ' . implode( ' ', $piece ) . '><image src="' . esc_url( CN_URL . 'assets/images/icons/vcard/vcard_' . $iconSize . '.png' ) . '" height="' . $iconSize . 'px" width="' . $iconSize . 'px"/></a>';

		$out .= str_ireplace(
			$search,
			$replace,
			empty( $atts['format'] ) ? '%text%' : $atts['format']
		);

		$out .= '</span>';

		// The class.seo.file is only loaded in the frontend; do not attempt to add the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink();

		$out = $atts['before'] . $out . $atts['after'] . PHP_EOL;

		return $this->echoOrReturn( $atts['return'], $out );
	}
}
