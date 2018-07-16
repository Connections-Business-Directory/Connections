<?php

/**
 * Class cnDate
 *
 * @since 8.22
 */
final class cnEntry_Date extends cnEntry_Collection_Item {

	/**
	 * @since 8.22
	 * @var string
	 */
	protected $date = '';

	/**
	 * Hash map of the old array keys / object properties to cnPhone properties.
	 *
	 * Used in self::__isset()
	 *
	 * @access protected
	 * @since  8.22
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
		'date'       => 'date',
	);

	/**
	 * Hash map of the the old array keys / object properties to cnPhone method callbacks.
	 *
	 * Used in self::__get()
	 *
	 * @access protected
	 * @since  8.22
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
		'date'       => 'getDate',
	);

	/**
	 * cnEntry_Date constructor.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$types   = self::getTypes();
		$default = cnOptions::getDefaultDateType();

		$this->id         = (int) cnArray::get( $data, 'id', 0 );

		$preferred        = cnArray::get( $data, 'preferred', FALSE );

		$this->type       = cnSanitize::field( 'attribute', cnArray::get( $data, 'type', key( $default ) ), 'raw' );
		$this->visibility = cnSanitize::field( 'attribute', cnArray::get( $data, 'visibility', 'public' ), 'raw' );
		$this->order      = absint( cnArray::get( $data, 'order', 0 ) );
		$this->preferred  = cnFormatting::toBoolean( $preferred );
		$this->date       = cnSanitize::field( 'date', cnArray::get( $data, 'uid', '' ), 'raw' );

		/*
		 * // START -- Compatibility for previous versions.
		 */
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
	 * Return an array of registered date types.
	 *
	 * @access private
	 * @since  8.22
	 *
	 * @return array
	 */
	private static function getTypes() {

		return cnOptions::getDateTypeOptions();
	}

	/**
	 * Escaped or sanitize cnEntry_Date based on context.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param static $self
	 * @param string $context
	 *
	 * @return static
	 */
	protected function prepareContext( $self, $context ) {

		$self->id         = absint( $self->id );
		$self->type       = cnSanitize::field( 'attribute', $self->type, $context );
		$self->visibility = cnSanitize::field( 'attribute', $self->visibility, $context );
		$self->order      = absint( $self->order );
		$self->preferred  = cnFormatting::toBoolean( $self->preferred );
		$self->date       = cnSanitize::field( 'date', $self->date, $context );

		return $self;
	}

	/**
	 * @access public
	 * @since  8.22
	 *
	 * @return string
	 */
	public function getDate() {

		return $this->date;
	}

	/**
	 * @access public
	 * @since  8.22
	 *
	 * @param string $date
	 *
	 * @return static
	 */
	public function setDate( $date ) {

		$this->date = cnSanitize::field( 'date', $date, 'raw' );

		return $this;
	}

	/**
	 * @access public
	 * @since  8.22
	 *
	 * @return array
	 */
	public function toArray() {

		return array(
			'id'          => $this->getID(),
			'type'        => $this->type,
			'name'        => $this->getName(),
			'visibility'  => $this->visibility,
			'order'       => $this->order,
			'preferred'   => $this->preferred,
			'date'        => $this->getDate(),
		);
	}
}
