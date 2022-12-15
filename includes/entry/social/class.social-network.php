<?php

/**
 * Class cnEntry_Social_Network
 *
 * @since 9.1
 *
 * @property string $url
 */
final class cnEntry_Social_Network extends cnEntry_Collection_Item {

	/**
	 * @since 9.1
	 * @var string
	 */
	protected $url = '';

	/**
	 * Hash map of the old array keys / object properties to cnEntry_Social_Network properties.
	 *
	 * Used in self::__isset()
	 *
	 * @access protected
	 * @since  9.1
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
		'url'        => 'url',
	);

	/**
	 * Hash map of the the old array keys / object properties to cnEntry_Social_Network method callbacks.
	 *
	 * Used in self::__get()
	 *
	 * @access protected
	 * @since  9.1
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
		'url'        => 'getURL',
	);

	/**
	 * cnEntry_Social_Network constructor.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$types   = self::getTypes();
		$default = cnOptions::getDefaultSocialNetworkType();

		$this->id  = (int) cnArray::get( $data, 'id', 0 );
		$preferred = cnArray::get( $data, 'preferred', false );
		$type      = cnArray::get( $data, 'type', key( $default ) );

		$this->type       = array_key_exists( $type, $types ) ? $type : key( $default );
		$this->visibility = cnArray::get( $data, 'visibility', 'public' );
		$this->order      = absint( cnArray::get( $data, 'order', 0 ) );
		$this->preferred  = cnFormatting::toBoolean( $preferred );
		$url              = cnArray::get( $data, 'url', '' );

		if ( is_string( $url ) && 0 < strlen( $url ) ) {

			$this->url = cnSanitize::field( 'url', cnURL::prefix( $url ), 'raw' );
		}

		/*
		 * // START -- Compatibility for previous versions.
		 */
		/*
		 * // END -- Compatibility for previous versions.
		 */

		// $this->name = $types[ $this->type ];
		$this->name = array_key_exists( $this->type, $types ) ? $types[ $this->type ] : $default[ $this->type ];

		// Previous versions saved NULL for visibility under some circumstances (bug), default to public in this case.
		if ( empty( $this->visibility ) ) {

			$this->visibility = 'public';
		}
	}

	/**
	 * Return an array of registered social network types.
	 *
	 * @access private
	 * @since  9.1
	 *
	 * @return array
	 */
	private static function getTypes() {

		return cnOptions::getSocialNetworkTypeOptions();
	}

	/**
	 * Escaped or sanitize cnEntry_Social_Network based on context.
	 *
	 * @access public
	 * @since  9.1
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
		$self->url        = cnSanitize::field( 'url', cnURL::prefix( $self->url ), $context );

		return $self;
	}

	/**
	 * @access public
	 * @since  9.1
	 *
	 * @return string
	 */
	public function getURL() {

		return $this->url;
	}

	/**
	 * @access public
	 * @since  9.1
	 *
	 * @param string $url
	 *
	 * @return static
	 */
	public function setURL( $url ) {

		if ( is_string( $url ) && 0 < strlen( $url ) ) {

			$this->url = cnSanitize::field( 'url', cnURL::prefix( $url ), 'raw' );
		}

		return $this;
	}

	/**
	 * @access public
	 * @since  9.1
	 *
	 * @return array
	 */
	public function toArray() {

		return array(
			'id'         => $this->getID(),
			'type'       => $this->type,
			'name'       => $this->getName(),
			'visibility' => $this->visibility,
			'order'      => $this->order,
			'preferred'  => $this->preferred,
			'url'        => $this->url,
		);
	}
}
