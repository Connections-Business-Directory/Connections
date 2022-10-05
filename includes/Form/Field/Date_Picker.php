<?php
/**
 * Create and render a date picker field.
 *
 * @since 10.4.29
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Form\Field\Date_Picker
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Form\Field;

use Connections_Directory\Form\Field\Attribute\Classnames;
use Connections_Directory\Form\Field\Attribute\Id;
use Connections_Directory\Form\Field\Attribute\Name;
use Connections_Directory\Form\Field\Attribute\Prefix;
use Connections_Directory\Form\Field\Attribute\Read_Only;
use Connections_Directory\Form\Field\Attribute\Value;
use Connections_Directory\Utility\_string;

/**
 * Class Date_Picker
 *
 * @package Connections_Directory\Form\Field
 */
class Date_Picker {

	use Attributes;
	use Classnames;
	use Id;
	use Name;
	use Prefix;
	use Read_Only;
	use Value;

	/**
	 * Field constructor.
	 *
	 * @since 10.4.29
	 */
	public function __construct() {

		$this->addClass( 'cn-datepicker' );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'cn-admin-jquery-datepicker' );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'datepickerJS' ) );
		add_action( 'wp_footer', array( __CLASS__, 'datepickerJS' ) );
	}

	/**
	 * Create an instance of the Field.
	 *
	 * @since 10.4.29
	 *
	 * @return static
	 */
	public static function create() {

		return new static();
	}

	/**
	 * Callback for the `admin_print_footer_scripts` and `wp_footer` actions.
	 *
	 * Outputs the JS necessary to support the datepicker.
	 *
	 * NOTE: Incredibly I came to the same solution as used in WordPress core {@see wp_localize_jquery_ui_datepicker()}.
	 *
	 * @todo Should use {@see cnFormatting::dateFormatPHPTojQueryUI()} instead to convert the PHP datetime format to a
	 *       compatible jQueryUI Datepicker compatibly format.
	 *
	 * @internal
	 * @since 10.4.29
	 */
	public static function datepickerJS() {

		?>
<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the jQuery UI Datepicker to the date input fields.
 */
jQuery( function( $ ) {

	if ( $.fn.datepicker ) {

		$( '.postbox, .cn-metabox' ).on( 'focus', '.cn-datepicker', function( e ) {

			$( this ).datepicker( {
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				yearRange: 'c-100:c+10',
				dateFormat: 'yy-mm-dd',
				// beforeShow: function(i) { if ( $( i ).attr('readonly') ) { return false; } }
			} ).keydown( false );

			e.preventDefault();
		} );
	}
} );
/* ]]> */</script>
		<?php
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4.29
	 *
	 * @return string
	 */
	public function getFieldHTML() {

		$html   = '';
		$prefix = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

		/** @var string $id */
		$id = _string::applyPrefix( $prefix, $this->getId() );

		$classNames = _string::applyPrefix( $prefix, $this->class );

		$html .= Text::create()
					 ->setId( $id )
					 ->addClass( $classNames )
					 ->setName( $id )
					 ->setReadOnly( $this->isReadOnly() )
					 ->setValue( $this->getValue() )
					 ->getHTML();

		return $html;
	}

	/**
	 * Get the field and field label HTML.
	 *
	 * @since 10.4.29
	 *
	 * @return string
	 */
	public function getHTML() {

		return $this->getFieldHTML();
	}

	/**
	 * Echo field and field label HTML.
	 *
	 * @since 10.4.29
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Return the field HTML.
	 *
	 * @since 10.4.29
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}

	/**
	 * Set the Field value.
	 *
	 * @since 10.4.29
	 *
	 * @param string $value The field value.
	 *
	 * @return static
	 */
	public function setValue( $value ) {

		$this->value = 0 < strlen( $value ) ? gmdate( 'm/d/Y', strtotime( $value ) ) : '';

		return $this;
	}
}
