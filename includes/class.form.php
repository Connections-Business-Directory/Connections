<?php
/**
 * Class for creating various form HTML elements.
 *
 * @package     Connections
 * @subpackage  HTML Form Elements
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create custom HTML forms.
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnFormObjects {

	/**
	 * The nonce base.
	 *
	 * @var string
	 */
	private $nonceBase = 'connections';

	/**
	 * Visibility options.
	 *
	 * @var string[]
	 */
	private $visibilityOptions = array(
		'Public'   => 'public',
		'Private'  => 'private',
		'Unlisted' => 'unlisted',
	);

	/**
	 * cnFormObjects constructor.
	 */
	public function __construct() {

		/*
		 * Create the visibility option array based on the current user capability.
		 */
		foreach ( $this->visibilityOptions as $key => $option ) {
			if ( ! Connections_Directory()->currentUser->canViewVisibility( $option ) ) {
				unset( $this->visibilityOptions[ $key ] );
			}
		}
	}

	/**
	 * The form open tag.
	 *
	 * @since unknown
	 *
	 * @param array $attr Form attributes array.
	 */
	public function open( $attr ) {

		if ( isset( $attr['class'] ) ) {
			$attr['class'] = 'class="' . esc_attr( $attr['class'] ) . '" ';
		}

		if ( isset( $attr['id'] ) ) {
			$attr['id'] = 'id="' . esc_attr( $attr['id'] ) . '" ';
		}

		if ( isset( $attr['name'] ) ) {
			$attr['name'] = 'name="' . esc_attr( $attr['name'] ) . '" ';
		}

		if ( isset( $attr['action'] ) ) {
			$attr['action'] = 'action="' . esc_url( $attr['action'] ) . '" ';
		}

		if ( isset( $attr['accept'] ) ) {
			$attr['accept'] = 'accept="' . esc_attr( $attr['accept'] ) . '" ';
		}

		if ( isset( $attr['accept-charset'] ) ) {
			$attr['accept-charset'] = 'accept-charset="' . esc_attr( $attr['accept-charset'] ) . '" ';
		}

		if ( isset( $attr['enctype'] ) ) {
			$attr['enctype'] = 'enctype="' . esc_attr( $attr['enctype'] ) . '" ';
		}

		if ( isset( $attr['method'] ) ) {
			$attr['method'] = 'method="' . esc_attr( $attr['method'] ) . '" ';
		}

		$out = '<form ';

		foreach ( $attr as $key => $value ) {
			$out .= $value;
		}

		// HTML is escaped above.
		echo $out , '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * HTML close tag.
	 *
	 * @since unknown
	 */
	public function close() {
		echo '</form>';
	}

	/**
	 * Retrieves or displays the nonce field for forms using wp_nonce_field.
	 *
	 * @since unknown
	 *
	 * @param string $action  Action name.
	 * @param bool   $item    Item name. Use when protecting multiple items on the same page.
	 * @param string $name    Nonce name.
	 * @param bool   $referer Whether to set and display the referrer field for validation.
	 * @param bool   $echo    Whether to display or return the hidden form field.
	 *
	 * @return string
	 */
	public function tokenField( $action, $item = false, $name = '_cn_wpnonce', $referer = true, $echo = true ) {

		if ( false === $item ) {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action, $name, $referer, false );

		} else {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action . '_' . $item, $name, $referer, false );
		}

		if ( $echo ) {
			echo $token; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $token;
	}

	/**
	 * Retrieves URL with nonce added to the query string.
	 *
	 * @since unknown
	 *
	 * @param string $actionURL URL to add the nonce to.
	 * @param string $item      Nonce action name.
	 *
	 * @return string
	 */
	public function tokenURL( $actionURL, $item ) {

		return wp_nonce_url( $actionURL, $item );
	}

	/**
	 * Generate the complete nonce string, from the nonce base, the action and an item.
	 *
	 * @since unknown
	 *
	 * @param string $action Action name.
	 * @param bool   $item   Item name. Use when protecting multiple items on the same page.
	 *
	 * @return string
	 */
	public function getNonce( $action, $item = false ) {

		if ( false === $item ) {

			$nonce = $this->nonceBase . '_' . $action;

		} else {

			$nonce = $this->nonceBase . '_' . $action . '_' . $item;
		}

		return $nonce;
	}

	/**
	 * Renders a select dropdown.
	 *
	 * This is deprecated method, left in place for backward compatibility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 *
	 * @param string $name    The input option id/name value.
	 * @param array  $options An associative array. Key is the option value and the value is the option name.
	 * @param string $value   [optional] The selected option.
	 * @param string $class   The class applied to the select.
	 * @param string $id      UNUSED.
	 *
	 * @return string
	 */
	public function buildSelect( $name, $options, $value = '', $class = '', $id = '' ) {

		_deprecated_function( __METHOD__, '9.15', '\Connections_Directory\Form\Field\Select::create()' );

		return cnHTML::field(
			array(
				'type'     => 'select',
				'class'    => $class,
				'id'       => $name,
				'options'  => $options,
				'required' => false,
				'label'    => '',
				'return'   => true,
			),
			$value
		);
	}

	/**
	 * Renders a radio group.
	 *
	 * This is deprecated method, left in place for backward compatibility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 *
	 * @param string $name    The input option id/name value.
	 * @param string $id      UNUSED.
	 * @param array  $options An associative array. Key is the option name and the value is the option value.
	 * @param string $value   The selected option.
	 *
	 * @return string
	 */
	public function buildRadio( $name, $id, $options, $value = '' ) {

		_deprecated_function( __METHOD__, '9.15', '\Connections_Directory\Form\Field\Radio_Group::create()' );

		return cnHTML::field(
			array(
				'type'     => 'radio',
				'display'  => 'block',
				'class'    => '',
				'id'       => $name,
				'options'  => array_flip( $options ), // The options array is flipped to preserve backward compatibility.
				'required' => false,
				'return'   => true,
			),
			$value
		);
	}
}
