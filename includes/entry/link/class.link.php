<?php

/**
 * Class cnLink
 *
 * @since 8.19
 */
final class cnLink extends cnEntry_Collection_Item {

	/**
	 * @since 8.19
	 * @var string
	 */
	protected $title;

	/**
	 * @since 8.19
	 * @var string
	 */
	protected $url;

	/**
	 * @since 8.19
	 * @var string
	 */
	protected $target;

	/**
	 * @since 8.19
	 * @var bool
	 */
	protected $follow;

	/**
	 * @since 8.19
	 * @var bool
	 */
	protected $image;

	/**
	 * @since 8.19
	 * @var bool
	 */
	protected $logo;

	/**
	 * Hash map of the old array keys / object properties to cnLink properties.
	 *
	 * Used in self::__isset()
	 *
	 * @access protected
	 * @since  8.19
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
		'title'      => 'title',
		'address'    => 'url',
		'url'        => 'url',
		'target'     => 'target',
		'follow'     => 'follow',
		'image'      => 'image',
		'logo'       => 'logo',
	);

	/**
	 * Hash map of the the old array keys / object properties to cnLink method callbacks.
	 *
	 * Used in self::__get()
	 *
	 * @access protected
	 * @since  8.19
	 * @var    array
	 */
	protected $methods = array(
		// 'field'     => 'method',
		'id'           => 'getID',
		'type'         => 'getType',
		'name'         => 'getName',
		'visibility'   => 'getVisibility',
		'order'        => 'getOrder',
		'preferred'    => 'getPreferred',
		'title'        => 'getTitle',
		'address'      => 'getURL',
		'url'          => 'getURL',
		'target'       => 'getTarget',
		'follow'       => 'getFollow',
		'followString' => 'getFollowString',
		'image'        => 'attachedToPhoto',
		'logo'         => 'attachedToLogo',
	);

	/**
	 * cnLink constructor.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {

		$types   = self::getTypes();
		$default = cnOptions::getDefaultLinkType();
		$target  = cnSettingsAPI::get( 'connections', 'fieldset-link', 'default-target' );
		$follow  = cnSettingsAPI::get( 'connections', 'fieldset-link', 'follow-link' );

		$follow = cnArray::get( $data, 'follow', $follow );
		$image  = cnArray::get( $data, 'image', FALSE );
		$logo   = cnArray::get( $data, 'logo', FALSE );

		$this->id          = (int) cnArray::get( $data, 'id', 0 );
		$preferred         = cnArray::get( $data, 'preferred', FALSE );
		$url               = cnArray::get( $data, 'url', '' );

		if ( is_string( $url ) && 0 < strlen( $url ) ) {

			$url = cnSanitize::field( 'url', cnURL::prefix( $url ), 'raw' );
		}

		$this->type        = cnSanitize::field( 'attribute', cnArray::get( $data, 'type', key( $default ) ), 'raw' );
		$this->visibility  = cnSanitize::field( 'attribute', cnArray::get( $data, 'visibility', 'public' ), 'raw' );
		$this->order       = absint( cnArray::get( $data, 'order', 0 ) );
		$this->preferred   = cnFormatting::toBoolean( $preferred );
		$this->title       = cnSanitize::field( 'name', cnArray::get( $data, 'title', '' ), 'raw' );
		$this->url         = is_string( $url ) ? $url : '';
		$this->target      = cnSanitize::field( 'attribute', cnArray::get( $data, 'target', $target ), 'raw' );
		$this->follow      = cnFormatting::toBoolean( $follow );
		$this->image       = cnFormatting::toBoolean( $image );
		$this->logo        = cnFormatting::toBoolean( $logo );

		$this->name = $types[ $this->type ];

		// Previous versions saved NULL for visibility under some circumstances (bug), default to public in this case.
		if ( empty( $this->visibility ) ) {

			$this->visibility = 'public';
		}
	}

	/**
	 * Return an array of registered link types.
	 *
	 * @access private
	 * @since  8.19
	 *
	 * @return array
	 */
	private static function getTypes() {

		return cnOptions::getLinkTypeOptions();
	}

	/**
	 * Escaped or sanitize cnLink based on context.
	 *
	 * @access public
	 * @since  8.19
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
		$self->title      = cnSanitize::field( 'name', $self->title, $context );
		$self->url        = cnSanitize::field( 'url', cnURL::prefix( $self->url ), $context );
		$self->target     = cnSanitize::field( 'attribute', $self->target, $context );
		$self->follow     = cnFormatting::toBoolean( $self->follow );
		$self->image      = cnFormatting::toBoolean( $self->image );
		$self->logo       = cnFormatting::toBoolean( $self->logo );

		return $self;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @return string
	 */
	public function getTitle() {

		return $this->title;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @param string $title
	 *
	 * @return static
	 */
	public function setTitle( $title ) {

		$this->title = cnSanitize::field( 'name', $title, 'raw' );

		return $this;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @return string
	 */
	public function getURL() {

		return $this->url;
	}

	/**
	 * @access public
	 * @since  8.19
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
	 * @since  8.19
	 *
	 * @return string
	 */
	public function getTarget() {

		return $this->target;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @param string $target
	 *
	 * @return static
	 */
	public function setTarget( $target ) {

		$this->target = cnSanitize::field( 'attribute', $target, 'raw' );

		return $this;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @return bool
	 */
	public function getFollow() {

		return $this->follow;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @param string $follow
	 *
	 * @return static
	 */
	public function setFollow( $follow ) {

		$this->follow = cnFormatting::toBoolean( $follow );

		return $this;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @return string
	 */
	public function getFollowString() {

		return $this->follow ? 'dofollow' : 'nofollow';
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @return bool
	 */
	public function attachedToLogo() {

		return $this->logo;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @return bool
	 */
	public function attachedToPhoto() {

		return $this->image;
	}

	/**
	 * Attach the URL to either the logo or photo.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param $type
	 */
	public function attachTo( $type ) {

		switch ( $type ) {

			case 'logo':
				$this->logo = TRUE;
				break;

			case 'photo':
				$this->image = TRUE;
				break;
		}
	}

	/**
	 * Return the image type that the link is attached to.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @return false|string Return FALSE if link not attached to either the logo or photo.
	 *                      Return `logo` or `photo` if attached to one of the image types.
	 */
	public function attachedTo() {

		if ( TRUE === $this->logo || TRUE === $this->image ) {

			if ( TRUE === $this->logo ) {

				return 'logo';

			} elseif ( TRUE === $this->image ) {

				return 'photo';
			}

		}

		return FALSE;
	}

	/**
	 * @access public
	 * @since  8.19
	 *
	 * @return array
	 */
	public function toArray() {

		return array(
			'id'           => $this->id,
			'type'         => $this->type,
			'name'         => $this->getName(),
			'visibility'   => $this->visibility,
			'order'        => $this->order,
			'preferred'    => $this->preferred,
			'title'        => $this->title,
			'url'          => $this->url,
			'target'       => $this->target,
			'follow'       => $this->follow,
			'followString' => $this->getFollowString(),
			'image'        => $this->image,
			'logo'         => $this->logo,
		);
	}
}
