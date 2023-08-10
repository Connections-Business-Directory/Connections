<?php
/**
 * Password reset.
 *
 * @since 10.4.47
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form;

use cnScript;
use Connections_Directory\Form;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_token;

/**
 * Class Reset_Password
 *
 * @package Connections_Directory\Form
 */
final class Reset_Password extends Form {

	/**
	 * Reset_Password constructor.
	 *
	 * @param array $parameters The form parameters.
	 */
	public function __construct( array $parameters = array() ) {

		$defaults = array(
			'class'       => array( 'cbd-form__reset-password' ),
			'id'          => 'resetpassform',
			'name'        => 'resetpassform',
			'action'      => get_rest_url( get_current_blog_id(), 'cn-api/v1/account/reset-password' ),
			'data'        => array(
				'confirmation' => esc_html__( 'Your password has been reset.', 'connections' ),
			),
			'fields'      => $this->fields( $parameters ),
			'submit'      => array(
				'id'       => 'wp-submit',
				'name'     => 'wp-submit',
				'class'    => array(
					'button',
					'button-primary',
				),
				'text'     => __( 'Save Password', 'connections' ),
				'disabled' => true,
			),
			'description' => __(
				'Please enter a new password.',
				'connections'
			),
		);

		$parameters = _parse::parameters( $parameters, $defaults, false, false );

		$this->hooks();
		$this->registerScripts();

		parent::__construct( $parameters );
	}

	/**
	 * Register form hooks.
	 *
	 * @since 10.4.47
	 */
	protected function hooks() {

		add_filter(
			'Connections_Directory/Form/' . $this->getShortname() . '/Field',
			array( __CLASS__, 'generateFieldValues' )
		);

		add_filter(
			'Connections_Directory/Form/' . $this->getShortname() . '/Submit',
			array( __CLASS__, 'spanButtonText' )
		);

		add_action(
			'Connections_Directory/Form/' . $this->getShortname() . '/Render/Field/After',
			array( __CLASS__, 'showPasswordButton' ),
			10,
			2
		);

		add_action(
			'Connections_Directory/Form/' . $this->getShortname() . '/Render/After',
			static function () {
				wp_enqueue_script( 'Connections_Directory/Form/User_Login/Script' );
				wp_enqueue_script( 'Connections_Directory/Form/Rest_Password/Script' );
			}
		);

		add_action(
			'Connections_Directory/Form/' . $this->getShortname() . '/Render/After',
			array( __CLASS__, 'setCookie' )
		);
	}

	/**
	 * Register form scripts.
	 *
	 * @since 10.4.47
	 */
	protected function registerScripts() {

		$asset = cnScript::getAssetMetadata( 'frontend/script.js' );

		wp_register_script(
			'Connections_Directory/Form/User_Login/Script',
			$asset['src'],
			$asset['dependencies'],
			$asset['version'],
			true
		);

		$asset = cnScript::getAssetMetadata( 'frontend/forms/reset-password/script.js' );

		wp_register_script(
			'Connections_Directory/Form/Rest_Password/Script',
			$asset['src'],
			array_merge(
				$asset['dependencies'],
				array(
					'password-strength-meter',
					'wp-util',
				)
			),
			$asset['version'],
			true
		);

		wp_add_inline_script(
			'Connections_Directory/Form/Rest_Password/Script',
			sprintf(
				'var _resetPassword = %s',
				wp_json_encode(
					array(
						'ajax' => array(
							'root'     => admin_url(),
							'endpoint' => 'admin-ajax.php',
						),
					)
				)
			),
			'before'
		);

		wp_localize_script(
			'password-strength-meter',
			'pwsL10n',
			array(
				'empty'    => __( 'Strength indicator', 'connections' ),
				'short'    => __( 'Very weak', 'connections' ),
				'bad'      => __( 'Weak', 'connections' ),
				'good'     => _x( 'Medium', 'password strength', 'connections' ),
				'strong'   => __( 'Strong', 'connections' ),
				'mismatch' => __( 'Mismatch', 'connections' ),
			)
		);
	}

	/**
	 * Callback for the `Connections_Directory/Form/Reset_Password/Field` filter.
	 *
	 * Generate dynamic field values.
	 *
	 * @internal
	 * @since 10.4.47
	 *
	 * @param Field $field An instance of the Field object.
	 *
	 * @return Field
	 */
	public static function generateFieldValues( Field $field ): Field {

		if ( '_cnonce' === $field->getName() ) {

			$field->setValue( _token::create( 'user/reset-password' ) );
		}

		if ( 'pass1' === $field->getName() ) {

			$field->addData( 'pw', wp_generate_password( 24 ) );
		}

		if ( 'key' === $field->getName() && isset( $_GET['key'] ) ) {

			$field->setValue( $_GET['key'] );
		}

		return $field;
	}

	/**
	 * Callback for the `Connections_Directory/Form/Reset_Password/Render/Field/After` action.
	 *
	 * Render the show/hide password button.
	 *
	 * @internal
	 * @since 10.4.47
	 *
	 * @param Field $field Current instance of the Field object.
	 * @param Form  $form  The current instance of the form object.
	 */
	public static function showPasswordButton( Field $field, Form $form ) {

		if ( 'pass1' === $field->getName() ) {

			echo '<div style="float: right; position: relative;">';

			$button = Field\Button::create(
				array(
					'class'      => array(
						'button',
						'button-secondary',
						'cbd-field',
					),
					'name'       => 'password-toggle',
					'style'      => array(
						'border'          => '1px solid transparent',
						'box-shadow'      => 'none',
						'width'           => '2.5rem',
						'height'          => '2.5rem',
						'min-width'       => '40px',
						'min-height'      => '40px',
						'margin'          => '0',
						'padding'         => '0',
						'display'         => 'inline-flex',
						'align-items'     => 'center',
						'justify-content' => 'center',
						'vertical-align'  => 'middle',
						'position'        => 'absolute',
						'top'             => '0',
						'right'           => '0',
						'box-sizing'      => 'content-box',
						'background'      => 'transparent',
						'color'           => '#2271b1',
					),
					'attributes' => array(
						'aria-label' => __( 'Hide password.', 'connections' ),
					),
					'text'       => '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>',
				)
			);

			$button->addClass( 'cbd-field--' . strtolower( _::getClassShortName( $button ) ) );

			$button->render();

			echo '</div>';

			echo '<div data-component="password-strength-result" class="" aria-live="polite">' . esc_html__( 'Strength indicator', 'connections' ) . '</div>';
		}
	}

	/**
	 * Callback for the `Connections_Directory/Form/Reset_Password/Render/After` action.
	 *
	 * Set the user login and password reset key in a cookie.
	 *
	 * @since 10.4.48
	 */
	public static function setCookie() {

		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

			$name   = 'wp-resetpass-' . COOKIEHASH;
			$value  = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
			$domain = is_string( COOKIE_DOMAIN ) ? COOKIE_DOMAIN : '';

			setcookie( $name, $value, 0, '/', $domain, is_ssl(), true );
		}
	}

	/**
	 * Callback for the `Connections_Directory/Form/Reset_Password/Submit` filter.
	 *
	 * Add a span tag around the submit button that can be used to add a loading spinner via CSS.
	 *
	 * @internal
	 * @since 10.4.47
	 *
	 * @param Field\Button $button An instance of the Button object.
	 *
	 * @return Field\Button
	 */
	public static function spanButtonText( Field\Button $button ): Field\Button {

		$text = $button->getText();

		return $button->text( '<span class="cbd-field--button__text">' . $text . '</span>' );
	}

	/**
	 * Register the form fields.
	 *
	 * @since 10.4.47
	 *
	 * @param array $parameters The parameters to use when creating the form fields.
	 *
	 * @return Field[]
	 */
	private function fields( array $parameters ): array {

		$defaults = array(
			'label_password_1' => __( 'New password.', 'connections' ),
			'id_password_1'    => 'pass1',
			'label_password_2' => __( 'Confirm new password.', 'connections' ),
			'id_password_2'    => 'pass2',
		);

		$parameters = _parse::parameters( $parameters, $defaults );

		return array(
			Field\Password::create(
				array(
					'id'           => $parameters['id_password_1'],
					'label'        => $parameters['label_password_1'],
					'name'         => 'pass1',
					'required'     => true,
					'autocomplete' => 'new-password',
					'attributes'   => array(
						'spellcheck' => 'false',
					),
					'data'         => array(
						'1p-ignore' => 'true',
						'lpignore'  => 'true',
					),
					'schema'       => array(
						'type'      => 'string',
						'maxLength' => 4096, // https://wordpress.stackexchange.com/a/400958/59053 .
					),
					'style'        => array(
						// 'max-width' => 'calc( 100% - 2.5rem )',
						'padding-right' => '2.5rem',
					),
				)
			),
			Field\Checkbox::create(
				array(
					'id'      => 'pw_weak',
					'name'    => 'pw_weak',
					'label'   => __( 'Confirm use of weak password?', 'connections' ),
					'default' => false,
					'schema'  => array(
						'type' => 'boolean',
					),
				)
			),
			Field\Password::create(
				array(
					'id'           => $parameters['id_password_2'],
					'label'        => $parameters['label_password_2'],
					'name'         => 'pass2',
					'required'     => true,
					'autocomplete' => 'new-password',
					'attributes'   => array(
						'spellcheck' => 'false',
					),
					'data'         => array(
						'1p-ignore' => 'true',
						'lpignore'  => 'true',
					),
					'disabled'     => true,
					'schema'       => array(
						'type'      => 'string',
						'maxLength' => 4096, // https://wordpress.stackexchange.com/a/400958/59053 .
					),
				)
			),
			Field\Hidden::create(
				array(
					'name'         => 'key',
					'value'        => '',
					'autocomplete' => 'off',
					'schema'       => array(
						'type'      => 'string',
						/*
						 * Set max accepted string to 20 characters.
						 * The generated key is 20 characters.
						 * @see get_password_reset_key()
						 */
						'maxLength' => 20,
					),
				)
			),
			Field\Hidden::create(
				array(
					'name'   => '_cnonce',
					'value'  => '',
					/*
					 * NOTE: The schema does not validate the token;
					 * it only ensures that the value matches the expected pattern.
					 * The token must still be validated.
					 */
					'schema' => array(
						'type'    => 'string',
						'pattern' => '^[a-fA-F0-9]{10}$',
					),
				)
			),
		);
	}

	/**
	 * Get the form footer.
	 *
	 * @since 10.4.47
	 *
	 * @return string
	 */
	protected function getFooter(): string {

		$button = Field\Button::create(
			array(
				'class' => array(
					'button',
					'button-secondary',
				),
				'name'  => 'generate-password',
				'text'  => __( 'Generate Password', 'connections' ),
			)
		);

		$button->addClass(
			array(
				'cbd-field',
				'cbd-field--' . strtolower( _::getClassShortName( $button ) ),
			)
		);

		$button->css(
			array(
				'margin-right' => '1rem',
				'width'        => 'calc( 50% - 1rem )',
			)
		);

		$this->submit->css( 'width', '50%' );

		$this->footer .= '<p class="description indicator-hint">' . wp_get_password_hint() . '</p>';
		$this->footer .= $button;

		return parent::getFooter();
	}
}
