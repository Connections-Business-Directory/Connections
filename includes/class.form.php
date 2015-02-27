<?php

/**
 * Class for creating various form HTML elements.
 *
 * @todo This class is an absolute mess, clean and optimize.
 *
 * @package     Connections
 * @subpackage  HTML Form Elements
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create custom HTML forms.
 */
class cnFormObjects {
	private $nonceBase = 'connections';
	private $validate;
	private $visibiltyOptions = array( 'Public'=>'public', 'Private'=>'private', 'Unlisted'=>'unlisted' );

	public function __construct() {
		// Load the validation class.
		$this->validate = new cnValidate();

		/*
		 * Create the visibility option array based on the current user capability.
		 */
		foreach ( $this->visibiltyOptions as $key => $option ) {
			if ( ! $this->validate->userPermitted( $option ) ) unset( $this->visibiltyOptions[$key] );
		}
	}

	/**
	 * The form open tag.
	 *
	 * @todo Finish adding form tag attributes.
	 * @param array
	 * @return string
	 */
	public function open( $attr ) {

		if ( isset( $attr['id'] ) ) $attr['id'] = 'id="' . esc_attr( $attr['id'] ) . '" ';
		if ( isset( $attr['name'] ) ) $attr['name'] = 'name="' . esc_attr( $attr['name'] ) . '" ';
		if ( isset( $attr['action'] ) ) $attr['action'] = 'action="' . esc_attr( $attr['action'] ) . '" ';
		if ( isset( $attr['accept'] ) ) $attr['accept'] = 'accept="' . esc_attr( $attr['accept'] ) . '" ';
		if ( isset( $attr['accept-charset'] ) ) $attr['accept-charset'] = 'accept-charset="' . esc_attr( $attr['accept-charset'] ) . '" ';
		if ( isset( $attr['enctype'] ) ) $attr['enctype'] = 'enctype="' . esc_attr( $attr['enctype'] ) . '" ';
		if ( isset( $attr['method'] ) ) $attr['method'] = 'method="' . esc_attr( $attr['method'] ) . '" ';

		$out = '<form ';

		foreach ( $attr as $key => $value ) {
			$out .= $value;
		}

		echo $out , '>';
	}

	/**
	 *
	 *
	 * @return string HTML close tag
	 */
	public function close() {
		echo '</form>';
	}

	//Function inspired from:
	//http://www.melbournechapter.net/wordpress/programming-languages/php/cman/2006/06/16/php-form-input-and-cross-site-attacks/
	/**
	 * Creates a random token.
	 *
	 * @param string  $formId The form ID
	 *
	 * @return string
	 */
	public function token( $formId = NULL ) {
		$token = md5( uniqid( rand(), true ) );

		return $token;
	}

	/**
	 * Retrieves or displays the nonce field for forms using wp_nonce_field.
	 *
	 * @param string  $action  Action name.
	 * @param bool    $item    [optional] Item name. Use when protecting multiple items on the same page.
	 * @param string  $name    [optional] Nonce name.
	 * @param bool    $referer [optional] Whether to set and display the refer field for validation.
	 * @param bool    $echo    [optional] Whether to display or return the hidden form field.
	 * @return string
	 */
	public function tokenField( $action, $item = FALSE, $name = '_cn_wpnonce', $referer = TRUE, $echo = TRUE ) {
		$name = esc_attr( $name );

		if ( $item == FALSE ) {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action, $name, $referer, FALSE );

		} else {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action . '_' . $item, $name, $referer, FALSE );
		}

		if ( $echo ) echo $token;

		// if ( $referer ) wp_referer_field( $echo, 'previous' );

		return $token;
	}

	/**
	 * Retrieves URL with nonce added to the query string.
	 *
	 * @param string  $actionURL URL to add the nonce to.
	 * @param string  $item      Nonce action name.
	 * @return string
	 */
	public function tokenURL( $actionURL, $item ) {

		return wp_nonce_url( $actionURL, $item );
	}

	/**
	 * Generate the complete nonce string, from the nonce base, the action and an item.
	 *
	 * @param string  $action Action name.
	 * @param bool    $item   [optional] Item name. Use when protecting multiple items on the same page.
	 * @return string
	 */
	public function getNonce( $action, $item = FALSE ) {

		if ( $item == FALSE ) {

			$nonce = $this->nonceBase . '_' . $action;

		} else {

			$nonce = $this->nonceBase . '_' . $action . '_' . $item;
		}

		return $nonce;
	}

	/**
	 * Renders a select drop down.
	 *
	 * This is deprecated method, left in place for backward compatibility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param string  $name    The input option id/name value.
	 * @param array   $options An associative array. Key is the option value and the value is the option name.
	 * @param string  $value   [optional] The selected option.
	 * @param string  $class   The class applied to the select.
	 * @param string  $id      UNUSED
	 *
	 * @return string
	 */
	public function buildSelect( $name, $options, $value = '', $class='', $id='' ) {

		$select = cnHTML::field(
			array(
				'type'     => 'select',
				'class'    => $class,
				'id'       => $name,
				'options'  => $options,
				'required' => FALSE,
				'label'    => '',
				'return'   => TRUE,
			),
			$value
		);

		return $select;
	}

	/**
	 * Renders a radio group.
	 *
	 * This is deprecated method, left in place for backward compatibility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param string  $name    The input option id/name value.
	 * @param string  $id      UNUSED
	 * @param array   $options An associative array. Key is the option name and the value is the option value.
	 * @param string  $value   [optional] The selected option.
	 *
	 * @return string
	 */
	public function buildRadio( $name, $id, $options, $value = '' ) {

		$radio = cnHTML::field(
			array(
				'type'     => 'radio',
				'display'  => 'block',
				'class'    => '',
				'id'       => $name,
				'options'  => array_flip( $options ), // The options array is flipped to preserve backward compatibility.
				'required' => FALSE,
				'return'   => TRUE,
			),
			$value
		);

		return $radio;
	}

	/**
	 * Registers the entry edit form metaboxes.
	 *
	 * This is deprecated method, left in place for backward compatibility only.
	 *
	 * NOTE: This should only be called by the "load-$page_hook" action.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param string  $pageHook The page hook to add the entry edit metaboxes to.
	 * @return void
	 */
	public function registerEditMetaboxes( $pageHook ) {

		// Retrieve all metabox registered with cnMetaboxAPI.
		$metaboxes = cnMetaboxAPI::get();

		foreach ( $metaboxes as $metabox ) {

			$metabox['pages'] = array( $pageHook );

			cnMetaboxAPI::add( $metabox );
		}

		cnMetaboxAPI::register();
	}

	/**
	 * Renders the name metabox.
	 *
	 * This is deprecated method, left in place for backward compatibility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instance of the cnEntry object.
	 */
	public function metaboxName( $entry ) {

		cnEntryMetabox::name( $entry, $metabox = array() );
	}

}
