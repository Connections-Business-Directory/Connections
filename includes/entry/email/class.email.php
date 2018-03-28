<?php

/**
 * Class cnEmail_Address
 *
 * @since 8.14
 */
final class cnEmail_Address extends cnEntry_Collection_Item {

	/**
	 * @since 8.14
	 * @var string
	 */
	protected $address = '';

	/**
	 * Hash map of the old array keys / object properties to cnEmail_Address properties.
	 *
	 * Used in self::__isset()
	 *
	 * @access protected
	 * @since  8.14
	 * @var    array
	 */
	protected $properties = array(
		// 'expected' => 'actual',
		'id'         => 'id',
		'type'       => 'type',
		'name'       => 'name',
		'visibility' => 'visibility',
		'order'      => 'order',
		'preferred'  => 'preferred',
		'address'    => 'address',
	);

	/**
	 * Hash map of the the old array keys / object properties to cnEmail_Address method callbacks.
	 *
	 * Used in self::__get()
	 *
	 * @access protected
	 * @since  8.14
	 * @var    array
	 */
	protected $methods = array(
		// 'field'   => 'method',
		'id'         => 'getID',
		'type'       => 'getType',
		'name'       => 'getName',
		'visibility' => 'getVisibility',
		'order'      => 'getOrder',
		'preferred'  => 'getPreferred',
		'address'    => 'getAddress',
	);

	/**
	 * cnEmail_Address constructor.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$types   = self::getTypes();
		$default = cnOptions::getDefaultEmailType();

		$this->id          = (int) cnArray::get( $data, 'id', 0 );

		$preferred         = cnArray::get( $data, 'preferred', FALSE );

		$this->type        = cnSanitize::field( 'attribute', cnArray::get( $data, 'type', key( $default ) ), 'raw' );
		$this->visibility  = cnSanitize::field( 'attribute', cnArray::get( $data, 'visibility', 'public' ), 'raw' );
		$this->order       = absint( cnArray::get( $data, 'order', 0 ) );
		$this->preferred   = cnFormatting::toBoolean( $preferred );
		$this->address     = sanitize_email( cnArray::get( $data, 'address', '' ) );

		$this->name = $types[ $this->type ];

		// Previous versions saved NULL for visibility under some circumstances (bug), default to public in this case.
		if ( empty( $this->visibility ) ) {

			$this->visibility = 'public';
		}
	}

	/**
	 * Return an array of registered email address types.
	 *
	 * @access private
	 * @since  8.14
	 *
	 * @return array
	 */
	private static function getTypes() {

		return cnOptions::getEmailTypeOptions();
	}

	/**
	 * Escaped or sanitize cnEmail_Address based on context.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param cnEmail_Address $self
	 * @param string          $context
	 *
	 * @return cnEmail_Address
	 */
	protected function prepareContext( $self, $context ) {

		$self->id          = absint( $self->id );
		$self->type        = cnSanitize::field( 'attribute', $self->type, $context );
		$self->visibility  = cnSanitize::field( 'attribute', $self->visibility, $context );
		$self->order       = absint( $self->order );
		$self->preferred   = cnFormatting::toBoolean( $self->preferred );
		$self->address     = sanitize_email( $self->address );

		return $self;
	}

	/**
	 * @access public
	 * @since  8.14
	 *
	 * @return string
	 */
	public function getAddress() {

		return $this->address;
	}

	/**
	 * @access public
	 * @since  8.14
	 *
	 * @param string $address
	 *
	 * @return cnEmail_Address
	 */
	public function setAddress( $address ) {

		$this->address = sanitize_email( $address );

		return $this;
	}

	/**
	 * @access public
	 * @since  8.14
	 *
	 * @return array
	 */
	public function toArray() {

		return array(
			'id'          => $this->id,
			'type'        => $this->type,
			'name'        => $this->getName(),
			'visibility'  => $this->visibility,
			'order'       => $this->order,
			'preferred'   => $this->preferred,
			'address'     => $this->address,
		);
	}
}
