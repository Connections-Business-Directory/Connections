<?php
/**
 * Create and render a Quicktag field.
 *
 * @since 10.4.28
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections_Directory\Form\Field\Quicktag
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Id;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Read_Only;
use Connections_Directory\Form\Field\Attribute\Value;
use Connections_Directory\Utility\_string;

/**
 * Class Quicktag
 *
 * @package Connections_Directory\Form\Field
 */
class Quicktag {

	use Attributes;
	use Id;
	use Prefix;
	use Read_Only;
	use Value;

	/**
	 * The array of all registered quicktag textareas.
	 *
	 * @since 10.4.28
	 *
	 * @var array
	 */
	private static $quickTagIDs = array();

	/**
	 * Field constructor.
	 *
	 * @since 10.4.28
	 */
	public function __construct() {

		wp_enqueue_script( 'jquery' );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'quickTagJS' ) );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'quickTagJS' ) );
	}

	/**
	 * Create an instance of the Field.
	 *
	 * @since 10.4.28
	 *
	 * @return static
	 */
	public static function create() {

		return new static();
	}

	/**
	 * Callback for the `admin_print_footer_scripts` and `wp_print_footer_scripts` actions.
	 *
	 * Outputs the JS necessary to support the quicktag textareas.
	 *
	 * @internal
	 * @since 10.4.28
	 */
	public static function quickTagJS() {

		if ( wp_script_is( 'quicktags' ) ) {

			echo '<script type="text/javascript">/* <![CDATA[ */ ' . PHP_EOL;

			foreach ( self::$quickTagIDs as $id ) {

				echo 'quicktags("' . esc_js( $id ) . '");' . PHP_EOL;
			}

			echo ' /* ]]> */</script>';
		}
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4.28
	 *
	 * @return string
	 */
	public function getFieldHTML() {

		$html   = '';
		$prefix = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

		/** @var string $id */
		$id = _string::applyPrefix( $prefix, $this->getId() );

		array_push( self::$quickTagIDs, $this->getId() );

		$html .= '<div class="wp-editor-container">';

		$html .= Textarea::create()
						 ->setId( $id )
						 ->addClass( 'wp-editor-area' )
						 ->setName( $id )
						 ->addAttribute( 'rows', 20 )
						 ->addAttribute( 'cols', 40 )
						 ->setReadOnly( $this->isReadOnly() )
						 ->setValue( $this->getValue() )
						 ->getHTML();

		$html .= '</div>';

		return $html;
	}

	/**
	 * Get the field and field label HTML.
	 *
	 * @since 10.4.28
	 *
	 * @return string
	 */
	public function getHTML() {

		return $this->getFieldHTML();
	}

	/**
	 * Echo field and field label HTML.
	 *
	 * @since 10.4.28
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Return the field HTML.
	 *
	 * @since 10.4.28
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
