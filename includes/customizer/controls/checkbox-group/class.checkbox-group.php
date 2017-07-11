<?php
/**
 * Customizer Control: Checkbox Group.
 *
 * @package     Connections
 * @subpackage  Customizer Control : Checkbox Group
 * @since       8.6.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a checkbox group control.
 */
class cnCustomizer_Control_Checkbox_Group extends WP_Customize_Control {

	/**
	 * The control type.
	 *
	 * @access public
	 * @since  8.6.7
	 * @var    string
	 */
	public $type = 'cn-checkbox-group';

	/**
	 * Used to automatically generate all CSS output.
	 *
	 * @access public
	 * @since  8.6.7
	 * @var    array
	 */
	public $output = array();

	///**
	// * Data type
	// *
	// * @access public
	// * @var string
	// */
	//public $option_type = 'theme_mod';

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @access public
     * @since  8.6.7
	 */
	public function enqueue() {

		$url = cnURL::makeProtocolRelative( CN_URL );

		wp_enqueue_script( 'cn-customizer-checkbox_group', $url . 'includes/customizer/controls/checkbox-group/checkbox-group.js', array( 'jquery', 'customize-base' ), false, true );
		wp_enqueue_style( 'cn-customizer-checkbox_group-css', $url . 'includes/customizer/controls/checkbox-group/checkbox-group.css', null );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @see    WP_Customize_Control::to_json()
	 *
	 * @access public
	 * @since  8.6.7
	 */
	public function to_json() {

		parent::to_json();

		$this->json['default'] = $this->setting->default;

		if ( isset( $this->default ) ) {
			$this->json['default'] = $this->default;
		}

		$this->json['output']  = $this->output;
		$this->json['value']   = $this->value();
		$this->json['choices'] = $this->choices;
		$this->json['link']    = $this->get_link();
		$this->json['id']      = $this->id;

		$this->json['inputAttrs'] = '';

		foreach ( $this->input_attrs as $attr => $value ) {
			$this->json['inputAttrs'] .= $attr . '="' . esc_attr( $value ) . '" ';
		}

	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 *
	 * @see    WP_Customize_Control::render_content()
	 *
	 * @access protected
	 * @since  8.6.7
	 */
	public function render_content() {}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @see    WP_Customize_Control::print_template()
	 *
	 * @access protected
	 * @since  8.6.7
	 */
	public function content_template() {
		?>

		<# if ( ! data.choices ) { return; } #>

		<# if ( data.label ) { #>
			<span class="customize-control-title">{{ data.label }}</span>
		<# } #>

		<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>

		<ul>
			<# for ( key in data.choices ) { #>
				<li>
					<label>
						<input {{{ data.inputAttrs }}} type="checkbox" value="{{ key }}"<# if ( _.contains( data.value, key ) ) { #> checked<# } #> /> {{ data.choices[ key ] }}
					</label>
				</li>
			<# } #>
		</ul>
		<?php
	}

	/**
	 * A helper method which can be sued as the sanitize callback.
	 *
	 * @see WP_Customize_Manager::add_setting()
	 *
	 * @param string|array $value The control's value.
	 *
	 * @return array
	 */
	public static function sanitize( $value ) {

		$value = ( ! is_array( $value ) ) ? explode( ',', $value ) : $value;
		return ( ! empty( $value ) ) ? array_map( 'sanitize_text_field', $value ) : array();
	}
}
