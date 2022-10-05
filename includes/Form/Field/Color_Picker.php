<?php
/**
 * Create and render a color picker field.
 *
 * @since 10.4.30
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Form\Field\Color_Picker
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
 * Class Color_Picker
 *
 * @package Connections_Directory\Form\Field
 */
class Color_Picker {

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
	 * @since 10.4.30
	 */
	public function __construct() {

		$this->addClass( 'cn-colorpicker' );
		$this->enqueueScripts();
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

	private function enqueueScripts() {

		wp_enqueue_style( 'wp-color-picker' );

		if ( is_admin() ) {

			wp_enqueue_script( 'wp-color-picker' );
			add_action( 'admin_print_footer_scripts', array( __CLASS__, 'colorpickerJS' ) );

		} else {

			/*
			 * WordPress seems to only register the color picker scripts for use in the admin.
			 * So, for the frontend, we must manually register and then enqueue.
			 * @url http://wordpress.stackexchange.com/a/82722/59053
			 */

			//phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
			wp_enqueue_script(
				'iris',
				admin_url( 'js/iris.min.js' ),
				array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
				false,
				1
			);

			//phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
			wp_enqueue_script(
				'wp-color-picker',
				admin_url( 'js/color-picker.min.js' ),
				array( 'iris' ),
				false,
				1
			);

			$colorpicker_l10n = array(
				'clear'         => __( 'Clear', 'connections' ),
				'defaultString' => __( 'Default', 'connections' ),
				'pick'          => __( 'Select Color', 'connections' ),
				'current'       => __( 'Current Color', 'connections' ),
			);

			wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );

			add_action( 'wp_footer', array( __CLASS__, 'colorpickerJS' ) );
		}
	}

	/**
	 * Callback for the `admin_print_footer_scripts` and `wp_footer` actions.
	 *
	 * Outputs the JS necessary to support the color picker.
	 *
	 * @internal
	 * @since 10.4.30
	 */
	public static function colorpickerJS() {

		?>
<script type="text/javascript">/* <![CDATA[ */
/*
 * Add the Color Picker to the input fields.
 */
jQuery( function( $ ) {

	$( '.cn-colorpicker' ).wpColorPicker();
} );
/* ]]> */</script>
		<?php
	}

	/**
	 * Get the field HTML.
	 *
	 * @since 10.4.30
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
}
