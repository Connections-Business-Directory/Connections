<?php
/**
 * The user login form.
 *
 * @since 10.4.46
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Form
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form;

use cnScript;
use Connections_Directory\Form;
use Connections_Directory\Request;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_token;

/**
 * Class User_Login
 *
 * @package Connections_Directory\Form
 */
final class User_Login extends Form {

	/**
	 * User_Login constructor.
	 *
	 * @param array $parameters The form parameters.
	 */
	public function __construct( array $parameters = array() ) {

		$defaults = array(
			'class'  => array( 'cbd-form__user-login' ),
			'id'     => 'loginform',
			'name'   => 'loginform',
			'action' => get_rest_url( get_current_blog_id(), 'cn-api/v1/account/login' ),
			'fields' => $this->fields( $parameters ),
			'submit' => array(
				'id'    => 'wp-submit',
				'name'  => 'wp-submit',
				'class' => array( 'button', 'button-primary' ),
				'text'  => __( 'Log In', 'connections' ),
			),
		);

		$parameters = _parse::parameters( $parameters, $defaults, false, false );

		$this->hooks();
		$this->registerScripts();
		$this->maybeRedirect();

		parent::__construct( $parameters );
	}

	/**
	 * Register form hooks.
	 *
	 * @since 10.4.46
	 */
	protected function hooks() {

		add_filter(
			'Connections_Directory/Form/' . $this->getShortname() . '/Field',
			array( __CLASS__, 'generateNonce' )
		);

		add_filter(
			'Connections_Directory/Form/' . $this->getShortname() . '/Submit',
			array( __CLASS__, 'spanButtonText' )
		);

		add_action(
			'Connections_Directory/Form/' . $this->getShortname() . '/Render/After',
			static function () {
				wp_enqueue_script( 'Connections_Directory/Form/User_Login/Script' );
			}
		);
	}

	/**
	 * Register form scripts.
	 *
	 * @since 10.4.46
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
	}

	/**
	 * Set up the form redirect data attribute.
	 *
	 * @Since 10.4.50
	 */
	protected function maybeRedirect() {

		$requested = Request\Redirect::input()->value();

		$redirect = 0 < strlen( $requested ) ? $requested : admin_url();

		$this->setRedirect( $redirect );
	}

	/**
	 * Callback for the `Connections_Directory/Form/User_Login/Field` filter.
	 *
	 * @internal
	 * @since 10.4.46
	 *
	 * @param Field $field An instance of the Field object.
	 *
	 * @return Field
	 */
	public static function generateNonce( Field $field ): Field {

		if ( '_cnonce' === $field->getName() ) {

			$field->setValue( _token::create( 'user/login' ) );
		}

		return $field;
	}

	/**
	 * Add a span tag around the submit button that can be used to add a loading spinner via CSS.
	 *
	 * @internal
	 * @since 10.4.46
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
	 * The user login form fields.
	 *
	 * @since 10.4.46
	 *
	 * @param array{
	 *     label_username: string,
	 *     label_password: string,
	 *     label_remember: string,
	 *     id_username: string,
	 *     id_password: string,
	 *     id_remember: string,
	 *     remember: bool,
	 * } $parameters Field parameters.
	 *
	 * @return Field[]
	 */
	private function fields( array $parameters ): array {

		$defaults = array(
			'label_username' => __( 'Username or Email Address', 'connections' ),
			'label_password' => __( 'Password', 'connections' ),
			'label_remember' => __( 'Remember Me', 'connections' ),
			'id_username'    => 'user_login',
			'id_password'    => 'user_pass',
			'id_remember'    => 'rememberme',
			'remember'       => true,
		);

		$parameters = _parse::parameters( $parameters, $defaults );

		$parameters['remember'] = _format::toBoolean( $parameters['remember'] );

		$fields = array(
			Field\Text::create(
				array(
					'id'           => $parameters['id_username'],
					'label'        => $parameters['label_username'],
					'name'         => 'log',
					'required'     => true,
					'autocomplete' => 'username',
					'attributes'   => array(
						'maxlength' => 100,
					),
					'data'         => array(
						'1p-ignore' => 'true',
						'lpignore'  => 'true',
					),
					'schema'       => array(
						'type'      => 'string',
						/*
						 * Max `user_login` is 60 characters, and max `user_email` is 100 characters.
						 * Set max accepted string to 100 characters.
						 * @link https://codex.wordpress.org/Database_Description#Table:_wp_users
						 */
						'maxLength' => 100,
					),
				)
			),
			Field\Password::create(
				array(
					'id'           => $parameters['id_password'],
					'label'        => $parameters['label_password'],
					'name'         => 'pwd',
					'required'     => true,
					'autocomplete' => 'current-password',
					'attributes'   => array(),
					'data'         => array(
						'1p-ignore' => 'true',
						'lpignore'  => 'true',
					),
					'schema'       => array(
						'type'      => 'string',
						'maxLength' => 4096, // https://wordpress.stackexchange.com/a/400958/59053 .
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

		if ( $parameters['remember'] ) {

			$fields[] = Field\Checkbox::create(
				array(
					'id'      => $parameters['id_remember'],
					'label'   => $parameters['label_remember'],
					'name'    => 'rememberme',
					'value'   => '0',
					'default' => '0',
					'schema'  => array(
						'type' => 'string',
						'enum' => array( '0', '1' ),
					),
				)
			)->setLabelPosition( 'implicit/after' );
		}

		return $fields;
	}
}
