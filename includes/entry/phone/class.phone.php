<?php

/**
 * Class cnPhone
 *
 * @since 8.10
 */
final class cnPhone extends cnEntry_Collection_Item {

	/**
	 * @since 8.10
	 * @var string
	 */
	protected $number = '';

	/**
	 * Hash map of the old array keys / object properties to cnPhone properties.
	 *
	 * Used in self::__isset()
	 *
	 * @access protected
	 * @since  8.10
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
		'number'     => 'number',
	);

	/**
	 * Hash map of the the old array keys / object properties to cnPhone method callbacks.
	 *
	 * Used in self::__get()
	 *
	 * @access protected
	 * @since  8.10
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
		'number'     => 'getNumber',
	);

	/**
	 * cnPhone constructor.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$types   = self::getTypes();
		$default = cnOptions::getDefaultPhoneType();

		$this->id          = (int) cnArray::get( $data, 'id', 0 );

		$preferred         = cnArray::get( $data, 'preferred', FALSE );

		$this->type        = cnSanitize::field( 'attribute', cnArray::get( $data, 'type', key( $default ) ), 'raw' );
		$this->visibility  = cnSanitize::field( 'attribute', cnArray::get( $data, 'visibility', 'public' ), 'raw' );
		$this->order       = absint( cnArray::get( $data, 'order', 0 ) );
		$this->preferred   = cnFormatting::toBoolean( $preferred );
		$this->number      = cnSanitize::field( 'phone-number', cnArray::get( $data, 'number', '' ), 'raw' );

		/*
		 * // START -- Compatibility for previous versions.
		 */
		switch ( $this->type ) {
			case 'home':
				$this->type = 'homephone';
				break;
			case 'cell':
				$this->type = 'cellphone';
				break;
			case 'work':
				$this->type = 'workphone';
				break;
			case 'fax':
				$this->type = 'workfax';
				break;
		}
		/*
		 * // END -- Compatibility for previous versions.
		 */

		$this->name = $types[ $this->type ];

		// Previous versions saved NULL for visibility under some circumstances (bug), default to public in this case.
		if ( empty( $this->visibility ) ) {

			$this->visibility = 'public';
		}
	}

	/**
	 * Return an array of registered phone types.
	 *
	 * @access private
	 * @since  8.10
	 *
	 * @return array
	 */
	private static function getTypes() {

		return cnOptions::getPhoneTypeOptions();
	}

	/**
	 * Escaped or sanitize cnPhone based on context.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param static $self
	 * @param string $context
	 *
	 * @return static
	 */
	protected function prepareContext( $self, $context ) {

		$self->id          = absint( $self->id );
		$self->type        = cnSanitize::field( 'attribute', $self->type, $context );
		$self->visibility  = cnSanitize::field( 'attribute', $self->visibility, $context );
		$self->order       = absint( $self->order );
		$self->preferred   = cnFormatting::toBoolean( $self->preferred );
		$self->number      = cnSanitize::field( 'phone-number', $self->number, $context );

		return $self;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return string
	 */
	public function getNumber() {

		return $this->number;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @param string $number
	 *
	 * @return static
	 */
	public function setNumber( $number ) {

		$this->number = cnSanitize::field( 'phone-number', $number, 'raw' );

		return $this;
	}

	/**
	 * @access public
	 * @since  8.10
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
			'number'      => $this->number,
		);
	}
}
