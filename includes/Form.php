<?php
/**
 * Base form object.
 *
 * @since 10.4.46
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory;

use Connections_Directory\Form\Field;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_sanitize;
use Connections_Directory\Utility\_string;
use WP_Error;

/**
 * Abstract form class.
 */
abstract class Form {

	use Field\Attributes;
	use Field\Attribute\Classnames;
	use Field\Attribute\Data;
	use Field\Attribute\Id;
	use Field\Attribute\Name;
	use Field\Attribute\Prefix;

	/**
	 * Action URL.
	 *
	 * @since 10.4.46
	 *
	 * @var string
	 */
	protected $action = '';

	/**
	 * Form description.
	 *
	 * @since 10.4.46
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Form errors.
	 *
	 * @since 10.4.46
	 *
	 * @var WP_Error
	 */
	protected $error;

	/**
	 * Form fields.
	 *
	 * @since 10.4.46
	 *
	 * @var Field[]
	 */
	protected $fields = array();

	/**
	 * Footer HTML.
	 *
	 * @since 10.4.46
	 *
	 * @var string
	 */
	protected $footer = '';

	/**
	 * Header HTML.
	 *
	 * @since 10.4.46
	 *
	 * @var string
	 */
	protected $header = '';

	/**
	 * HTTP method.
	 *
	 * @since 10.4.46
	 *
	 * @var string
	 */
	protected $method = 'POST';

	/**
	 * Redirect URI.
	 *
	 * @since 10.4.46
	 *
	 * @var string
	 */
	protected $redirect = '';

	/**
	 * Reset on success?
	 *
	 * @since 10.4.46
	 *
	 * @var bool
	 */
	protected $reset = false;

	/**
	 * Current class shortname.
	 *
	 * @since 10.4.47
	 *
	 * @var string
	 */
	protected $shortname;

	/**
	 * Form submit button.
	 *
	 * @var Field\Submit
	 */
	protected $submit;

	/**
	 * Class constructor.
	 *
	 * @since 10.4.46
	 *
	 * @param array $parameters Form properties.
	 */
	public function __construct( array $parameters = array() ) {

		$this->error = new WP_Error();

		/**
		 * Filters the form properties.
		 *
		 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
		 * You can check the available forms in the `includes/Form` folder.
		 *
		 * @since 10.4.46
		 *
		 * @param array  $parameters The Form Parameters.
		 * @param static $form       The current instance of Form.
		 */
		$parameters = apply_filters(
			'Connections_Directory/Form/' . $this->getShortname() . '/Parameters',
			$parameters,
			$this
		);

		$this->setPrefix( _array::get( $parameters, 'prefix', '' ) );
		$this->addClass( _array::get( $parameters, 'class', '' ) );
		$this->setId( _array::get( $parameters, 'id', '' ) );
		$this->setName( _array::get( $parameters, 'name', '' ) );
		$this->setAction( _array::get( $parameters, 'action', '' ) );
		$this->addData( _array::get( $parameters, 'data', array() ) );
		$this->addFields( _array::get( $parameters, 'fields', array() ) );
		$this->addSubmit( _array::get( $parameters, 'submit', array() ) );

		$this->description = _array::get( $parameters, 'description', '' );
	}

	/**
	 * Create an instance of the Form.
	 *
	 * @since 10.4.46
	 *
	 * @param array $properties Form properties.
	 *
	 * @return static
	 */
	public static function create( array $properties = array() ): Form {

		return new static( $properties );
	}

	/**
	 * Get the class shortname.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	final protected function getShortname(): string {

		if ( ! isset( $this->shortname ) ) {

			$this->shortname = _::getClassShortName( $this );
		}

		return $this->shortname;
	}

	/**
	 * Add a field to the form.
	 *
	 * @since 10.4.46
	 *
	 * @param Field $field An instance of the Field object.
	 *
	 * @return static
	 */
	final public function addField( Field $field ): Form {

		$this->fields[] = $field;

		return $this;
	}

	/**
	 * Add form fields.
	 *
	 * @since 10.4.46
	 *
	 * @param Field[] $fields Form fields.
	 */
	protected function addFields( array $fields ) {

		foreach ( $fields as $field ) {

			$this->addField( $field );
		}
	}

	/**
	 * Create the form submit button.
	 *
	 * @since 10.4.46
	 *
	 * @param array $properties Button properties.
	 */
	final protected function addSubmit( array $properties ) {

		$button = Field\Button::create( $properties );
		$button->setType( 'submit' );
		$button->addClass(
			array(
				'cbd-field',
				'cbd-field--' . strtolower( _::getClassShortName( $button ) ),
			)
		);

		/**
		 * Filters the form submit button.
		 *
		 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
		 * You can check the available forms in the `includes/Form` folder.
		 *
		 * @since 10.4.46
		 *
		 * @param array  $button The Form Parameters.
		 * @param static $form   The current instance of Form.
		 */
		$this->submit = apply_filters(
			'Connections_Directory/Form/' . $this->getShortname() . '/Submit',
			$button,
			$this
		);
	}

	/**
	 * Gets form fields.
	 *
	 * @since 10.4.46
	 *
	 * @return array
	 */
	final public function getFields(): array {

		return $this->fields;
	}

	/**
	 * Gets field value.
	 *
	 * @since 10.4.46
	 *
	 * @param string $name Field name.
	 *
	 * @return mixed|void
	 */
	final public function getFieldValue( string $name ) {

		foreach ( $this->getFields() as $field ) {

			if ( $name === $field->getName() && ! $field->isDisabled() ) {

				return $field->getValue();
			}
		}
	}

	/**
	 * Sets field value.
	 *
	 * @since 10.4.46
	 *
	 * @param string $name  Field name.
	 * @param mixed  $value Field value.
	 *
	 * @return object
	 */
	final public function setFieldValue( string $name, $value ): Form {

		foreach ( $this->getFields() as $field ) {

			if ( $name === $field->getName() ) {

				$field->setValue( $value );
			}
		}

		return $this;
	}

	/**
	 * Gets field values.
	 *
	 * @since 10.4.46
	 *
	 * @return array
	 */
	final public function getFieldValues(): array {

		$values = array();

		foreach ( $this->getFields() as $field ) {

			if ( ! $field->isDisabled() ) {

				$values[ $field->getName() ] = $field->getValue();
			}
		}

		return $values;
	}

	/**
	 * Sets field values.
	 *
	 * @since 10.4.46
	 *
	 * @param array $values Associative array of field values where the array index are the field names.
	 *
	 * @return static
	 */
	final public function setFieldValues( array $values ): Form {

		foreach ( $this->getFields() as $field ) {

			$key = $field->getName();

			if ( array_key_exists( $key, $values ) ) {

				$field->setValue( $values[ $key ] );
			}
		}

		return $this;
	}

	/**
	 * Validate the form fields against their schema.
	 *
	 * @since 10.4.46
	 *
	 * @return bool
	 */
	final public function validate(): bool {

		$this->error = new WP_Error();

		foreach ( $this->getFields() as $field ) {

			/** @var Form\Field\Input $field */
			$field->validate();

			if ( $field->hasError() ) {

				/**
				 * Filters the form field validation error message.
				 *
				 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
				 * You can check the available forms in the `includes/Form` folder.
				 *
				 * @since 10.4.46
				 *
				 * @param string $message The form field validation error message.
				 * @param Field  $field   An instance of Field.
				 * @param static $form    The current instance of Form.
				 */
				$message = apply_filters(
					'Connections_Directory/Form/' . $this->getShortname() . '/Validate/Field/Error/Message',
					$field->getError()->get_error_message(),
					$field,
					$this
				);

				$this->error->add(
					$field->getError()->get_error_code(),
					$message,
					$field->getName()
				);
			}
		}

		/**
		 * Filters the form validation errors.
		 *
		 * With this hook, you can implement custom validation checks and add new error messages.
		 *
		 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
		 * You can check the available forms in the `includes/Form` folder.
		 *
		 * @since 10.4.46
		 *
		 * @param WP_Error $error An instance of WP_Error.
		 * @param static   $form  The current instance of Form.
		 */
		$this->error = apply_filters(
			'Connections_Directory/Form/' . $this->getShortname() . '/Validate',
			$this->error,
			$this
		);

		return ! $this->error->has_errors();
	}

	/**
	 * Gets form field errors.
	 *
	 * Restructures the forms field errors into an associative array
	 * where the array index is the field name attribute and the
	 * array value is the error message.
	 *
	 * @since 10.4.46
	 *
	 * @return array
	 */
	final public function getErrors(): array {

		$errors = array();

		foreach ( $this->error->errors as $code => $messages ) {

			$key            = array_key_exists( $code, $this->error->error_data ) ? $this->error->error_data[ $code ] : $code;
			$errors[ $key ] = $messages[0];
		}

		return $errors;
	}

	/**
	 * Get form action URL.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	public function getAction(): string {

		return $this->action;
	}

	/**
	 * Set the URL for sending requests on submission.
	 *
	 * @since 10.4.46
	 *
	 * @param string $url The action URL.
	 *
	 * @return static
	 */
	final public function setAction( string $url ): Form {

		$valid = wp_http_validate_url( $url );

		if ( false !== $valid ) {

			$this->action = $valid;
		}

		return $this;
	}

	/**
	 * Get HTTP method.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	final public function getMethod(): string {

		return $this->method;
	}

	/**
	 * Set HTTP method.
	 *
	 * @since 10.4.46
	 *
	 * @param string $method The form HTTP method.
	 */
	final public function setMethod( string $method ) {

		if ( ! in_array( $method, array( 'GET', 'POST' ), true ) ) {

			$this->method = 'POST';

		} else {

			$this->method = $method;
		}
	}

	/**
	 * Get for redirect URL.
	 *
	 * @since 10.4.46
	 *
	 * @return string Redirect URL.
	 */
	final public function getRedirect(): string {

		return $this->redirect;
	}

	/**
	 * Get the sanitized and validated redirect URL.
	 *
	 * @since 10.4.46
	 *
	 * @return string The sanitized and validated redirect URL.
	 */
	final public function getSafeRedirect(): string {

		$sanitized = wp_sanitize_redirect( $this->getRedirect() );

		return wp_validate_redirect( $sanitized );
	}

	/**
	 * Set form redirect after submission URL.
	 *
	 * @since 10.4.46
	 *
	 * @param string $redirect Redirect URL.
	 *
	 * @return Form
	 */
	final public function setRedirect( string $redirect ): Form {

		$valid = wp_http_validate_url( $redirect );

		if ( false !== $valid ) {

			$this->redirect = $valid;

		} else {

			$this->redirect = '';
		}

		return $this;
	}

	/**
	 * Whether to reset the form after submission.
	 *
	 * @since 10.4.46
	 *
	 * @return bool
	 */
	final public function isReset(): bool {

		return $this->reset;
	}

	/**
	 * Set whether the form should reset after submission.
	 *
	 * @since 10.4.46
	 *
	 * @param bool $reset Reset value.
	 *
	 * @return Form
	 */
	final public function setReset( bool $reset ): Form {

		$this->reset = $reset;

		return $this;
	}

	/**
	 * Prepare the form attributes and stringify them.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	protected function prepareAttributes(): string {

		$attributes = array();
		$prefix     = 0 < strlen( $this->getPrefix() ) ? $this->getPrefix() . '-' : '';

		$classNames = _string::applyPrefix( $prefix, $this->class );
		array_unshift( $classNames, 'cbd-form' );

		/** @var string $id */
		$id = _string::applyPrefix( $prefix, $this->getId() );

		_array::set( $attributes, 'class', _escape::classNames( $classNames ) );
		_array::set( $attributes, 'id', _escape::id( $id ) );
		_array::set( $attributes, 'name', _escape::attribute( $this->getName() ) );

		// Set action.
		if ( strpos( $this->getAction(), get_rest_url() ) === 0 ) {

			_array::set( $attributes, 'action', '#' );
			$action = _string::removePrefix( get_rest_url(), $this->getAction() );
			$this->addData( 'action', $action );

		} else {

			_array::set( $attributes, 'action', esc_url( $this->getAction() ) );
		}

		_array::set( $attributes, 'method', _escape::attribute( $this->getMethod() ) );

		// Set redirect.
		$this->addData( 'redirect', $this->getRedirect() );

		// Set reset.
		if ( $this->isReset() ) {

			$this->addData( 'reset', true );
		}

		// Set component.
		$this->addData( 'component', strtolower( 'form-' . $this->getShortname() ) );

		// Sort the attributes alphabetically, because, why not.
		ksort( $this->attributes, SORT_NATURAL );

		// Merge in the data attributes.
		$attributes = array_merge( $attributes, _html::prepareDataAttributes( $this->data ) );

		return _html::stringifyAttributes( $attributes );
	}

	/**
	 * Generate the form header.
	 *
	 * @since 10.4.47
	 *
	 * @return string
	 */
	protected function getHeader(): string {

		if ( $this->description ) {

			$this->header .= '<p class="cbd-form__description">' . _sanitize::html( $this->description ) . '</p>';
		}

		$this->header .= '<div class="cbd-form__messages" data-component="messages" role="alert"></div>';

		return $this->header;
	}

	/**
	 * Generate the form footer.
	 *
	 * @since 10.4.47
	 *
	 * @return string
	 */
	protected function getFooter(): string {

		if ( $this->submit instanceof Field\Submit || $this->submit instanceof Field\Button ) {

			$this->footer .= $this->submit->getHTML();
		}

		return $this->footer;
	}

	/**
	 * Get the form HTML.
	 *
	 * @since 10.4.46
	 *
	 * @return string
	 */
	public function getHTML(): string {

		/*
		 * Capture action output.
		 */
		ob_start();

		/**
		 * Runs before the form HTML has been generated.
		 *
		 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
		 * You can check the available forms in the `includes/Form` folder.
		 *
		 * @since 10.4.46
		 *
		 * @param static $form The current instance of Form.
		 */
		do_action(
			'Connections_Directory/Form/' . $this->getShortname() . '/Render/Before',
			$this
		);

		/*
		 * Add captured action output to the form HTML.
		 */
		$html = ob_get_clean();

		$html .= '<form ' . $this->prepareAttributes() . '>';

		// Render header.
		$header = $this->getHeader();

		if ( 0 < strlen( $header ) ) {

			$html .= '<div class="cbd-form__header">' . $header . '</div>';
		}

		// Render fields.
		if ( $this->fields ) {

			$hidden = '';
			$html  .= '<div class="cbd-form__fields">';

			foreach ( $this->getFields() as $field ) {

				/**
				 * Filters the form field before render.
				 *
				 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
				 * You can check the available forms in the `includes/Form` folder.
				 *
				 * @since 10.4.46
				 *
				 * @param Field  $field   An instance of Field.
				 * @param static $form    The current instance of Form.
				 *
				 * @var Field $field
				 */
				$field = apply_filters(
					'Connections_Directory/Form/' . $this->getShortname() . '/Field',
					$field,
					$this
				);

				$fieldType = strtolower( _::getClassShortName( $field ) );

				if ( ! $field instanceof Field\Hidden ) {

					$classNames = array(
						'cbd-form__field',
						"cbd-form__field--{$fieldType}",
					);

					$html .= '<div class="' . _escape::classNames( $classNames ) . '">';

					/*
					 * Capture action output.
					 */
					ob_start();

					/**
					 * Runs before a form field HTML has been generated.
					 *
					 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
					 * You can check the available forms in the `includes/Form` folder.
					 *
					 * @since 10.4.47
					 *
					 * @param Field  $field The current form field.
					 * @param static $form  The current instance of Form.
					 */
					do_action(
						'Connections_Directory/Form/' . $this->getShortname() . '/Render/Field/Before',
						$field,
						$this
					);

					/*
					 * Add captured action output to the form HTML.
					 */
					$html .= ob_get_clean();

					$fieldClassNames = array(
						'cbd-field',
						"cbd-field--{$fieldType}",
					);

					$field->addClass( $fieldClassNames );

					if ( $field->label instanceof Field\Label ) {

						$field->label->addClass( 'cbd-field--label' );
					}

					$html .= $field->getHTML();

					/*
					 * Capture action output.
					 */
					ob_start();

					/**
					 * Runs after a form field HTML has been generated.
					 *
					 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
					 * You can check the available forms in the `includes/Form` folder.
					 *
					 * @since 10.4.47
					 *
					 * @param Field  $field The current form field.
					 * @param static $form  The current instance of Form.
					 */
					do_action(
						'Connections_Directory/Form/' . $this->getShortname() . '/Render/Field/After',
						$field,
						$this
					);

					/*
					 * Add captured action output to the form HTML.
					 */
					$html .= ob_get_clean();

					$html .= '</div>';

				} else {

					$hidden .= $field->getHTML();
				}
			}

			$html .= $hidden;

			$html .= '</div>';
		}

		// Render footer.
		$footer = $this->getFooter();

		if ( 0 < strlen( $footer ) ) {

			$html .= '<div class="cbd-form__footer">' . $footer . '</div>';
		}

		$html .= '</form>';

		/*
		 * Capture action output.
		 */
		ob_start();

		/**
		 * Runs after the form HTML has been generated.
		 *
		 * The dynamic part of the hook refers to the form name (e.g. `User_Login`).
		 * You can check the available forms in the `includes/Form` folder.
		 *
		 * @since 10.4.46
		 *
		 * @param static $form The current instance of Form.
		 */
		do_action(
			'Connections_Directory/Form/' . $this->getShortname() . '/Render/After',
			$this
		);

		/*
		 * Add captured action output to the form HTML.
		 */
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Echo the form HTML.
	 *
	 * @since 10.4.46
	 */
	public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 10.4.46
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getHTML();
	}
}
