<?php
/**
 * Create and render a slider field.
 *
 * @since 10.4.30
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Form\Field\Slider
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
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_string;

/**
 * Class Slider
 *
 * @package Connections_Directory\Form\Field
 */
class Slider {

	use Attributes;
	use Classnames;
	use Id;
	use Name;
	use Prefix;
	use Read_Only;
	use Value;

	/**
	 * The slider field properties.
	 *
	 * @since 10.4.30
	 * @var array{min: int, max: int, step: int, value: int}
	 */
	private $options = array(
		'min'   => 0,
		'max'   => 100,
		'step'  => 1,
		'value' => 0,
	);

	/**
	 * The array of all registered slider settings.
	 *
	 * @since 10.4.30
	 *
	 * @var array
	 */
	private static $slider = array();

	/**
	 * Field constructor.
	 *
	 * @since 10.4.28
	 */
	public function __construct() {

		wp_enqueue_script( 'jquery-ui-slider' );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'sliderJS' ) );
		add_action( 'wp_footer', array( __CLASS__, 'sliderJS' ) );
	}

	/**
	 * Create an instance of the Field.
	 *
	 * @since 10.4.30
	 *
	 * @return static
	 */
	public static function create() {

		return new static();
	}

	/**
	 * The option parameters for the slider.
	 *
	 * @since 10.4.30
	 *
	 * @param array{min: int, max: int, step: int, value: int} $options The slider options.
	 */
	public function setOptions( $options ) {

		$this->options = _parse::parameters( $options, $this->options );

		return $this;
	}

	/**
	 * Callback for the `admin_print_footer_scripts` and `wp_footer` actions.
	 *
	 * Outputs the JS necessary to support the color picker.
	 *
	 * @internal
	 * @since 10.4.30
	 */
	public static function sliderJS() {

		?>
<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the jQuery UI Slider input fields.
 */
jQuery( function ( $ ) {

		<?php
		foreach ( self::$slider as $id => $option ) {

			printf(
				'$( "#cn-slider-%1$s" ).slider({
				value: %2$d,
				min: %3$d,
				max: %4$d,
				step: %5$d,
				slide: function( event, ui ) {
					$( "#%1$s" ).val( ui.value );
				}
			});',
				esc_attr( $id ),
				wp_json_encode( absint( $option['value'] ) ),
				wp_json_encode( absint( $option['min'] ) ),
				wp_json_encode( absint( $option['max'] ) ),
				wp_json_encode( absint( $option['step'] ) )
			);
			echo PHP_EOL;
			printf(
				'$( "#%1$s" ).change(function() {
					$( "#cn-slider-%1$s" ).slider( "value", $(this).val() );
				});',
				esc_attr( $id )
			);
		}
		?>

});
/* ]]> */</script>
		<?php
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

		$this->setOptions( array( 'value' => $this->getValue() ) );

		self::$slider[ $id ] = $this->options;

		$html .= "<div class=\"cn-slider-container\" id=\"cn-slider-{$id}\"></div>";

		$html .= Number::create()
					   ->setId( $id )
					   ->addClass( 'small-text' )
					   ->setName( $id )
					   ->addAttribute( 'min', $this->options['min'] )
					   ->addAttribute( 'max', $this->options['max'] )
					   ->addAttribute( 'step', $this->options['step'] )
					   ->setValue( $this->getValue() )
					   ->setReadOnly( $this->isReadOnly() )
					   ->getHTML();

		return $html;
	}

	/**
	 * Get the field and field label HTML.
	 *
	 * @since 10.4.30
	 *
	 * @return string
	 */
	public function getHTML() {

		return $this->getFieldHTML();
	}

	/**
	 * Echo field and field label HTML.
	 *
	 * @since 10.4.30
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Return the field HTML.
	 *
	 * @since 10.4.30
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}

	/**
	 * Set the Field value.
	 *
	 * @since 10.4.30
	 *
	 * @param string $value The field value.
	 *
	 * @return static
	 */
	public function setValue( $value ) {

		$this->value = absint( $value );

		return $this;
	}
}
