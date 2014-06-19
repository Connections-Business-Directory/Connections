<?php

/**
 * Class to be used to render entry parts using shortcodes.
 *
 * @package     Connections
 * @subpackage  Entry Shortcode
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnEntry_Shortcode {

	/**
	 * An instance of the cnEntry object
	 *
	 * @access private
	 * @since 0.8
	 * @var object
	 */
	private $entry = NULL;

	/**
	 * The resulting content after being process thru the entry shortcodes.
	 *
	 * @access private
	 * @since 0.8
	 * @var string
	 */
	private $result = '';

	/**
	 * The method to be used to process the content thru the entry shortcode.
	 *
	 * @access public
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  string $content The content to be processed.
	 *
	 * @return string          The result.
	 */
	public static function process( $entry, $content ) {

		$return = new cnEntry_Shortcode( $entry, $content );

		return $return->result();
	}

	/**
	 * Set's up the core entry shortcode and starts the shortcode
	 * replacement process using the WordPress shortcode API.
	 *
	 * @access private
	 * @since 0.8
	 * @param object $entry   An instance of the cnEntry object.
	 * @param string $content The content to be processed.
	 */
	private function __construct( $entry, $content ) {

		$this->entry = $entry;

		// Register the entry shortcode.
		// Adding it here so it is only processed when $content is processed thru this method.
		add_shortcode( 'cn_entry', array( $this, 'shortcode' ) );

		if ( has_shortcode( $content, 'cn_entry' ) ) {

			$this->result = do_shortcode( $content );

		} else {

			$this->result = $content;
		}

		// Remove the runtime shortcode.
		remove_shortcode( 'cn_entry' );
	}

	/**
	 * Returns the processed content.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @return string The processed content.
	 */
	private function result() {

		return $this->result;
	}

	/**
	 * The core method that processes the content according to the
	 * entry part that the shortcode should add to the content.
	 *
	 * @access private
	 * @since 0.8
	 * @param  array  $atts    The shortcode attributes array.
	 * @param  string $content The content captured between an open/close shortcode.
	 * @param  string $tag     The shortcode tag.
	 *
	 * @return string          The processed content.
	 */
	public function shortcode( $atts, $content = '', $tag = 'cn_entry' ) {

		// Bail if self::$entry is not set because an instance of the cnEntry object is required.
		if ( is_null( $this->entry ) ) return '';

		$defaults = array(
			'part' => '',
			);

		// Normally we'd use shortcode_atts, but that strips keys from $atts that do not exist in $defaults.
		// Since $atts can contain various option for the different callback methods, we'll use wp_parse_args()
		// which leaves keys that do not exist in $atts.
		$atts = wp_parse_args( $atts, $defaults );

		// All the core methods in the cnEntry_HTML class echo by default, make sure to return instead.
		$atts['return'] = TRUE;

		switch ( $atts['part'] ) {

			case 'name':
				$out = $this->entry->getName( $atts );
				break;

			case 'title':
				$out = $this->entry->getTitle();
				break;

			case 'organization':
				$out = $this->entry->getOrganization();
				break;

			case 'department':
				$out = $this->entry->getDepartment();
				break;

			case 'contact':
				$out = $this->entry->getContactName( $atts );
				break;

			case 'family_relationships':
				$out = $this->entry->getFamilyMembers( $atts );
				break;

			case 'addresses':

				add_shortcode( 'cn_address', array( $this, 'address') );

				$out = has_shortcode( $content, 'cn_address' ) ? do_shortcode( $content ) : '';

				remove_shortcode( 'cn_address' );

				break;

			case 'phone_numbers':

				add_shortcode( 'cn_phone', array( $this, 'phone') );

				$out = has_shortcode( $content, 'cn_phone' ) ? do_shortcode( $content ) : '';

				remove_shortcode( 'cn_phone' );

				break;

			case 'email':

				add_shortcode( 'cn_email', array( $this, 'email') );

				$out = has_shortcode( $content, 'cn_email' ) ? do_shortcode( $content ) : '';

				remove_shortcode( 'cn_email' );

				break;

			case 'im':

				add_shortcode( 'cn_im', array( $this, 'im') );

				$out = has_shortcode( $content, 'cn_im' ) ? do_shortcode( $content ) : '';

				remove_shortcode( 'cn_im' );

				break;

			case 'social_networks':

				add_shortcode( 'cn_social_network', array( $this, 'socialNetwork') );

				$out = has_shortcode( $content, 'cn_social_network' ) ? do_shortcode( $content ) : '';

				remove_shortcode( 'cn_social_network' );

				break;

			case 'links':

				add_shortcode( 'cn_link', array( $this, 'link') );

				$out = has_shortcode( $content, 'cn_link' ) ? do_shortcode( $content ) : '';

				remove_shortcode( 'cn_link' );

				break;

			case 'dates':

				add_shortcode( 'cn_date', array( $this, 'date') );

				$out = has_shortcode( $content, 'cn_date' ) ? do_shortcode( $content ) : '';

				remove_shortcode( 'cn_date' );

				break;

			case 'bio':
				$out = $this->entry->getBio();
				break;

			case 'notes':
				$out = $this->entry->getNotes();
				break;

			default:

				// Custom shortcodes can be applied to the content using this filter.
				$out = apply_filters( 'cn_entry_part-' . $part, $content, $atts, $this->entry );

				break;
		}

		return $out;
	}

	/**
	 * Process the content adding the entry address details in place of the shortcode.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts    The shortcode attributes array.
	 * @param  string $content The content captured between an open/close shortcode tag.
	 * @param  string $tag     The shortcode tag.
	 *
	 * @return string          The processed content.
	 */
	public function address( $atts, $content = '', $tag = 'cn_address' ) {

		$out = '';

		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			'city'      => NULL,
			'state'     => NULL,
			'zipcode'   => NULL,
			'country'   => NULL,
			);

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$search = array(
			'%type%',
			'%line_1%',
			'%line_2%',
			'%line_3%',
			'%locality%',
			'%region%',
			'%postal-code%',
			'%country%',
			'%latitude%',
			'%longitude%',
			);

		$addresses = $this->entry->getAddresses( $atts );

		foreach ( $addresses as $address ) {

			$replace = array(
				'type'        => $address->name,
				'line_1'      => $address->line_1,
				'line_2'      => $address->line_2,
				'line_3'      => $address->line_3,
				'locality'    => $address->city,
				'region'      => $address->state,
				'postal_code' => $address->zipcode,
				'country'     => $address->country,
				'latitude'    => $address->latitude,
				'longitude'   => $address->longitude,
				);

			$out .= str_ireplace( $search, $replace, $content );
		}

		return $out;
	}

	/**
	 * Process the content adding the entry phone number details in place of the shortcode.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts    The shortcode attributes array.
	 * @param  string $content The content captured between an open/close shortcode tag.
	 * @param  string $tag     The shortcode tag.
	 *
	 * @return string          The processed content.
	 */
	public function phone( $atts, $content = '', $tag = 'cn_phone' ) {

		$out = '';

		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			);

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$search = array(
			'%type%',
			'%number%',
			);

		$phoneNumbers = $this->entry->getPhoneNumbers( $atts );

		foreach ( $phoneNumbers as $phone ) {

			$replace = array(
				'type'   => $phone->name,
				'number' => $phone->number,
				);

			$out .= str_ireplace( $search, $replace, $content );

		}

		return $out;
	}

	public function email( $atts, $content = '', $tag = 'cn_email' ) {

		$out = '';

		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			);

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$search = array(
			'%type%',
			'%address%',
			);

		$emailAddresses = $this->entry->getEmailAddresses( $atts );

		foreach ( $emailAddresses as $email ) {

			$replace = array(
				'type'    => $email->name,
				'address' => $email->address,
				);

			$out .= str_ireplace( $search, $replace, $content );

		}

		return $out;
	}

	/**
	 * Process the content adding the entry instant messenger details in place of the shortcode.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts    The shortcode attributes array.
	 * @param  string $content The content captured between an open/close shortcode tag.
	 * @param  string $tag     The shortcode tag.
	 *
	 * @return string          The processed content.
	 */
	public function im( $atts, $content = '', $tag = 'cn_im' ) {

		$out = '';

		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			);

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$search = array(
			'%type%',
			'%id%',
			);

		$networks = $this->entry->getIm( $atts );

		foreach ( $networks as $network ) {

			$replace = array(
				'type' => $network->name,
				'id'   => $network->id,
				);

			$out .= str_ireplace( $search, $replace, $content );

		}

		return $out;
	}

	/**
	 * Process the content adding the entry social network details in place of the shortcode.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts    The shortcode attributes array.
	 * @param  string $content The content captured between an open/close shortcode tag.
	 * @param  string $tag     The shortcode tag.
	 *
	 * @return string          The processed content.
	 */
	public function socialNetwork( $atts, $content = '', $tag = 'cn_social_network' ) {

		$out = '';

		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			);

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$search = array(
			'%type%',
			'%url%',
			);

		$networks = $this->entry->getSocialMedia( $atts );

		foreach ( $networks as $network ) {

			$replace = array(
				'type' => $network->name,
				'url'   => $network->url,
				);

			$out .= str_ireplace( $search, $replace, $content );

		}

		return $out;
	}

	/**
	 * Process the content adding the entry links details in place of the shortcode.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts    The shortcode attributes array.
	 * @param  string $content The content captured between an open/close shortcode tag.
	 * @param  string $tag     The shortcode tag.
	 *
	 * @return string          The processed content.
	 */
	public function link( $atts, $content = '', $tag = 'cn_link' ) {

		$out = '';

		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			);

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$search = array(
			'%type%',
			'%title%',
			'%url%',
			);

		$links = $this->entry->getLinks( $atts );

		foreach ( $links as $link ) {

			$replace = array(
				'type'  => $link->name,
				'title' => $link->title,
				'url'   => $link->url,
				);

			$out .= str_ireplace( $search, $replace, $content );

		}

		return $out;
	}

	/**
	 * Process the content adding the entry dates details in place of the shortcode.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts    The shortcode attributes array.
	 * @param  string $content The content captured between an open/close shortcode tag.
	 * @param  string $tag     The shortcode tag.
	 *
	 * @return string          The processed content.
	 */
	public function date( $atts, $content = '', $tag = 'cn_date' ) {

		$out = '';

		$defaults = array(
			'preferred' => FALSE,
			'type'      => NULL,
			);

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$search = array(
			'%type%',
			'%date%',
			);

		$dates = $this->entry->getDates( $atts );

		foreach ( $dates as $date ) {

			$replace = array(
				'type' => $date->name,
				'date' => $date->date,
				);

			$out .= str_ireplace( $search, $replace, $content );

		}

		return $out;
	}
}
