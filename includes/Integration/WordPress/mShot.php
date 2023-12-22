<?php

namespace Connections_Directory\Integration\WordPress;

use cnSanitize;
use cnString;
use cnURL;
use WP_Error;

/**
 * Class SiteShot
 *
 * @package Connections_Directory
 */
final class mShot {

	/**
	 * The provider API URL.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * var string
	 */
	const API = '//s0.wp.com/mshots/v1/';

	/**
	 * The URL to take a screenshot of.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * The width of the screenshot.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var int
	 */
	private $width = 0;

	/**
	 * Whether or not the screenshot should link to the URL.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var bool
	 */
	private $link = true;

	/**
	 * The string applied to the <img> or <a> title attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * The string applied to the <a> alt attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $alt = '';

	/**
	 * Whether or not to add the nofollow rel attribute to the link.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var bool
	 */
	private $follow = false;

	/**
	 * The link target attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $target = '';

	/**
	 * The string/HTML to be prepended to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $before = '';

	/**
	 * The string/HTML to be appended to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var string
	 */
	private $after = '';

	/**
	 * Whether or not to echo or return the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @var bool
	 */
	private $return = false;

	/**
	 * Set up the options.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param array $atts
	 */
	public function __construct( $atts = array() ) {

		$defaults = array(
			'url'    => 'connections-pro.com',
			'width'  => 200,
			'link'   => true,
			'title'  => '',
			'alt'    => '',
			'target' => '',
			'follow' => false,
			'before' => '',
			'after'  => '',
			'return' => false,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		$validTargets = array( '_blank', '_self', '_parent', '_top' );

		$this->setURL( $atts['url'] );
		$this->setWidth( $atts['width'] );
		$this->link = is_bool( $atts['link'] ) ? $atts['link'] : true;
		$this->setTitle( $atts['title'] );
		$this->setAlt( $atts['alt'] );
		$this->follow = is_bool( $atts['follow'] ) ? $atts['follow'] : false;
		$this->target = in_array( $atts['target'], $validTargets ) ? $atts['target'] : '_blank';
		$this->before = is_string( $atts['before'] ) && 0 < strlen( $atts['before'] ) ? $atts['before'] : '';
		$this->after  = is_string( $atts['after'] ) && 0 < strlen( $atts['after'] ) ? $atts['after'] : '';
		$this->return = is_bool( $atts['return'] ) ? $atts['return'] : false;
	}

	/**
	 * Set the URL to create a screenshot of.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $url
	 */
	public function setURL( $url ) {

		// If the http protocol is not part of the url, add it.
		$this->url = cnURL::prefix( $url );
	}

	/**
	 * Get the URL of site to take a screenshot of.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @return string|WP_Error
	 */
	public function getURL() {

		if ( false === filter_var( $this->url, FILTER_VALIDATE_URL ) ) {

			return new WP_Error( 'invalid_url', __( 'Invalid URL.', 'connections' ) );
		}

		return esc_url_raw( $this->url );
	}

	/**
	 * The size of the screenshot to request from the provider.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $size
	 *
	 * @return array
	 */
	public function setSize( $size ) {

		// Set the image size; These string values match the valid size for http://www.shrinktheweb.com
		switch ( $size ) {

			case 'mcr':
				$this->setWidth( 75 );
				$height = 56;
				break;

			case 'tny':
				$this->setWidth( 90 );
				$height = 68;
				break;

			case 'vsm':
				$this->setWidth( 100 );
				$height = 75;
				break;

			case 'sm':
				$this->setWidth( 120 );
				$height = 90;
				break;

			case 'lg':
				$this->setWidth( 200 );
				$height = 150;
				break;

			case 'xlg':
				$this->setWidth( 320 );
				$height = 240;
				break;

			default:
				$this->setWidth( 200 );
				$height = 150;
				break;
		}

		return array(
			'width'  => $this->width,
			'height' => $height,
		);
	}

	/**
	 * Set the image width.
	 *
	 * @access private
	 * @since  8.2.5
	 *
	 * @param int $width
	 */
	private function setWidth( $width ) {

		$this->width = absint( $width );
	}

	/**
	 * The string to set the <a> title attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $title
	 */
	public function setTitle( $title ) {

		$this->title = is_string( $title ) && 0 < strlen( $title ) ? cnSanitize::field( 'attribute', $title ) : '';
	}

	/**
	 * The string to set the <a> or <img> alt attribute.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $alt
	 */
	public function setAlt( $alt ) {

		$this->alt = is_string( $alt ) && 0 < strlen( $alt ) ? cnSanitize::field( 'attribute', $alt ) : '';
	}

	/**
	 * Create the provider API request URL.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @return string|WP_Error
	 */
	private function getSource() {

		if ( 0 == strlen( $this->url ) || empty( $this->width ) ) {

			return new WP_Error( 'no_url_or_width', __( 'No URL or width.', 'connections' ) );
		}

		return sprintf( '%1$s%2$s?w=%3$d', self::API, urlencode( $this->url ), $this->width );
	}

	/**
	 * The string/HTML to prepend to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $before
	 */
	public function setBefore( $before ) {

		$this->before = is_string( $before ) && 0 < strlen( $before ) ? $before : '';
	}

	/**
	 * The string/HTML to append to the output.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @param string $after
	 */
	public function setAfter( $after ) {

		$this->after = is_string( $after ) && 0 < strlen( $after ) ? $after : '';
	}

	/**
	 * Render the HTML for the screenshot.
	 *
	 * @access public
	 * @since  8.2.5
	 *
	 * @return string
	 */
	public function render() {

		$imageURI = $this->getSource();
		$url      = $this->getURL();

		if ( is_wp_error( $imageURI ) ) {

			$html = '<p class="cn-error">' . implode( '</p><p class="cn-error">', $imageURI->get_error_messages() ) . '</p>';

		} elseif ( is_wp_error( $url ) ) {

			$html = '<p class="cn-error">' . implode( '</p><p class="cn-error">', $url->get_error_messages() ) . '</p>';

		} else {

			$image = sprintf(
				'<img class="cn-screenshot" src="%1$s" %2$s %3$s width="%4$d"/>',
				esc_url( $imageURI ),
				$this->alt ? 'alt="' . esc_attr( $this->alt ) . '"' : '',
				! $this->link && $this->title ? 'title="' . esc_attr( $this->title ) . '"' : '',
				absint( $this->width )
			);

			$image = cnString::normalize( $image );

			if ( $this->link ) {

				$link = sprintf(
					'<a class="url" href="%1$s"%2$s %3$s target="%4$s">%5$s</a>',
					esc_url( $url ),
					$this->title ? ' title="' . esc_attr( $this->title ) . '"' : '',
					$this->follow ? '' : 'rel="nofollow"',
					esc_attr( $this->target ),
					$image
				);

				$html = cnString::normalize( $link );

			} else {

				$html = $image;
			}
		}

		$html = $this->before . $html . $this->after . PHP_EOL;

		if ( ! $this->return ) {

			// HTML is escaped above.
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html;
	}
}
