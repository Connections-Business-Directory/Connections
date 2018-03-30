<?php

/**
 * Class cnMessenger
 *
 * @since 8.16
 */
final class cnMessenger extends cnEntry_Collection_Item {

	/**
	 * @since 8.16
	 * @var string
	 */
	protected $uid = '';

	/**
	 * Hash map of the old array keys / object properties to cnMessenger properties.
	 *
	 * Used in self::__isset()
	 *
	 * @access protected
	 * @since  8.16
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
		'uid'        => 'uid',
	);

	/**
	 * Hash map of the the old array keys / object properties to cnMessenger method callbacks.
	 *
	 * Used in self::__get()
	 *
	 * @access protected
	 * @since  8.16
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
		'uid'        => 'getUserID',
	);

	/**
	 * cnMessenger constructor.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$types   = self::getTypes();
		$default = cnOptions::getDefaultMessengerType();

		$this->id         = (int) cnArray::get( $data, 'id', 0 );

		$preferred        = cnArray::get( $data, 'preferred', FALSE );

		$this->type       = cnSanitize::field( 'attribute', cnArray::get( $data, 'type', key( $default ) ), 'raw' );
		$this->visibility = cnSanitize::field( 'attribute', cnArray::get( $data, 'visibility', 'public' ), 'raw' );
		$this->order      = absint( cnArray::get( $data, 'order', 0 ) );
		$this->preferred  = cnFormatting::toBoolean( $preferred );
		$this->uid        = cnSanitize::field( 'phone-number', cnArray::get( $data, 'uid', '' ), 'raw' );

		/*
		 * // START -- Compatibility for previous versions.
		 */
		switch ( $this->type ) {
			case 'AIM':
				$this->type = 'aim';
				break;
			case 'Yahoo IM':
				$this->type = 'yahoo';
				break;
			case 'Jabber / Google Talk':
				$this->type = 'jabber';
				break;
			case 'Messenger':
				$this->type = 'messenger';
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
	 * Return an array of registered messenger types.
	 *
	 * @access private
	 * @since  8.16
	 *
	 * @return array
	 */
	private static function getTypes() {

		return cnOptions::getMessengerTypeOptions();
	}

	/**
	 * Escaped or sanitize cnMessenger based on context.
	 *
	 * @access public
	 * @since  8.16
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
		$self->uid        = cnSanitize::field( 'phone-number', $self->uid, $context );

		return $self;
	}

	/**
	 * @access public
	 * @since  8.16
	 *
	 * @return string
	 */
	public function getUserID() {

		return $this->uid;
	}

	/**
	 * @access public
	 * @since  8.16
	 *
	 * @param string $userID
	 *
	 * @return static
	 */
	public function setUserID( $userID ) {

		$this->uid = cnSanitize::field( 'phone-number', $userID, 'raw' );

		return $this;
	}

	/**
	 * @access public
	 * @since  8.16
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
			'uid'         => $this->getUserID(),
		);
	}
}
