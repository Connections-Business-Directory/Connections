<?php

namespace Connections_Directory;

use cnArray;
use cnFormatting;
use cnQuery;
use cnSanitize;
use cnSettingsAPI;
use Connections_Directory\Utility\_array;
use WP_Error;

/**
 * Class Content_Block
 *
 * @package Connections_Directory
 */
class Content_Block {

	/**
	 * @since 9.7
	 * @var string
	 */
	private $id;

	/**
	 * @since 9.8
	 * @var string
	 */
	protected $shortName = '';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array();

	/**
	 * @since 9.7
	 * @var mixed
	 */
	private $object;

	/**
	 * Content_Block constructor.
	 *
	 * @since 9.7
	 *
	 * @param string $id
	 * @param array  $atts {
	 *
	 *     @type string       $context             The context in which to add the Content Block.
	 *                                             Valid: list|single
	 *                                             Default: null (register in both contexts)
	 *     @type string       $name                The Content Block name. This will be shown as the setting option name and  the heading name.
	 *                                             Default: "humane readable" Content Block ID
	 *     @type string       $slug                The Content Block container ID.
	 *                                             Default: empty string
	 *     @type bool         $register_option     Whether to display the Content Block as a settings option in the admin.
	 *                                             Default: true
	 *     @type string       $script_handle       The registered JavaScript handle to enqueue.
	 *     @type string       $style_handle        The registered CSS handle to enqueue.
	 *     @type array|string $permission_callback The permission required in order to view the Content Block.
	 *     @type array|string $render_callback     The function/method called to display the Content Block.
	 *                                             Default: @see Content_Block::render()
	 *     @type int          $priority            The priority used when registering the $render_callback.
	 *                                             Default: 10
	 *     @type string       $heading             The Content Block heading. This will override the $name attribute when displaying the
	 *                                             heading on the frontend.
	 *                                             Default: empty string, defaults to `$name`
	 *     @type string       $block_tag           The Content Block container tag.
	 *                                             Default: div
	 *     @type string       $block_class         The Content Block container class.
	 *                                             Default: cn-content-block
	 *     @type string       $block_id            The Content Block container id.
	 *                                             Default: empty string
	 *     @type string       $heading_tag         The Content Block heading tag.
	 *                                             Default: h3
	 *     @type bool         $render_container    Whether to render block content within the Content Block container.
	 *                                             If set to `false` the heading will not be rendered.
	 *                                             Default: true
	 *     @type bool         $render_heading      Whether to render the Content Block heading.
	 *                                             Default: true
	 *     @type string       $before              The content to render before the Content Block container.
	 *     @type string       $after               The content to render after the Content Block container.
	 * }
	 */
	public function __construct( $id, $atts = array() ) {

		$this->id        = $id;
		$this->shortName = $this->getShortName();

		$atts = cnSanitize::args( $atts, $this->defaults() );

		if ( ! in_array( $atts['context'], array( null, 'list', 'single' ), true ) ) {

			$atts['context'] = null;
		}

		$atts['slug'] = 0 < strlen( $atts['slug'] ) ? $atts['slug'] : $id;

		$this->setProperties( $atts );
	}

	/**
	 * @since 9.7
	 */
	public static function add() {

		$me = new static( static::ID );

		Content_Blocks::instance()->add( $me );
	}

	/**
	 * @param string $id
	 * @param array  $atts
	 *
	 * @return Content_Block
	 */
	public static function create( $id, $atts = array() ) {

		return new self( $id, $atts );
	}

	/**
	 * @see Content_Block::__construct()
	 *
	 * @since 9.7
	 *
	 * @return array
	 */
	private function defaults() {

		return array(
			'context'             => null,
			'name'                => ucwords( str_replace( array( '-', '_' ), ' ', $this->id ) ),
			'slug'                => '',
			'register_option'     => true,
			'script_handle'       => '',
			'style_handle'        => '',
			'permission_callback' => array( $this, 'permission' ),
			'render_callback'     => array( $this, 'render' ),
			'priority'            => 10,
			'heading'             => '',
			'block_tag'           => 'div',
			'block_class'         => 'cn-content-block',
			'block_id'            => '',
			'heading_tag'         => 'h3',
			'render_container'    => true,
			'render_heading'      => true,
			'before'              => '',
			'after'               => '',
		);
	}

	/**
	 * @since 9.7
	 *
	 * @return string
	 */
	public function getID() {

		return $this->id;
	}

	/**
	 * @since 9.7
	 *
	 * @return mixed
	 */
	public function getObject() {

		return $this->object;
	}

	/**
	 * @since 9.7
	 *
	 * @param $object
	 */
	public function useObject( $object ) {

		$this->object = $object;
	}

	/**
	 * @since 9.7
	 *
	 * @return array
	 */
	public function getProperties() {

		return $this->properties;
	}

	/**
	 * @since 9.7
	 *
	 * @param array $properties
	 */
	public function setProperties( $properties ) {

		if ( is_array( $properties ) ) {

			foreach ( $properties as $property => $value ) {

				$this->set( $property, $value );
			}
		}
	}

	/**
	 * Set a Content Block property value by ID.
	 *
	 * @since 9.7
	 *
	 * @param string $property
	 * @param mixed  $value
	 */
	public function set( $property, $value ) {

		if ( in_array( $property, array( 'register_option', 'render_container', 'render_heading' ) ) ) {

			cnFormatting::toBoolean( $value );
		}

		cnArray::set( $this->properties, $property, $value );
	}

	/**
	 * Get a Content Block value by ID.
	 *
	 * @since 9.7
	 *
	 * @param string $property
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get( $property, $default = null ) {

		return cnArray::get( $this->properties, $property, $default );
	}

	/**
	 * This must be overridden in the registered Content Block subclass.
	 *
	 * This is the default callback registered when adding a Content Block. This can be overridden by setting the
	 * `permission_callback` parameter when registering a Content Block. @see Content_Block::__construct()
	 *
	 * @since 9.7
	 *
	 * @return bool|WP_Error
	 */
	public function permission() {

		/* translators: Class method name. */
		return new WP_Error( 'invalid-method', sprintf( __( 'Method "%s" not implemented. Must be overridden in subclass.', 'connections' ), __METHOD__ ) );
	}

	/**
	 * Whether the Content Block is active.
	 *
	 * @since 10.4.11
	 *
	 * @return bool
	 */
	public function isActive() {

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			$settings = cnSettingsAPI::get( 'connections', 'display_single', 'content_block' );

		} else {

			$settings = cnSettingsAPI::get( 'connections', 'display_list', 'content_block' );
		}

		$active = _array::get( $settings, 'active', array() );

		return in_array( $this->getID(), $active );
	}

	/**
	 * Whether the current user is permitted to view the Content Block.
	 *
	 * @since 9.7
	 *
	 * @return bool
	 */
	public function isPermitted() {

		$callable  = $this->get( 'permission_callback' );
		$permitted = false;

		if ( is_callable( $callable ) ) {

			$permitted = call_user_func( $callable );
		}

		if ( ! is_bool( $permitted ) ) {

			$permitted = false;
		}

		return $permitted;
	}

	/**
	 * Renders the Content Block heading registered with the `heading` parameter. If the `heading` parameter was not
	 * supplied when registering the Content Block the heading will default to the `name` parameter.
	 *
	 * @since 9.7
	 *
	 * @return string
	 */
	public function heading() {

		if ( true !== $this->get( 'render_heading', true ) ) {

			return '';
		}

		if ( 0 < strlen( $this->get( 'heading', '' ) ) ) {

			$heading = $this->get( 'heading', '' );

		} elseif ( 0 < strlen( $this->get( 'name', '' ) ) ) {

			$heading = $this->get( 'name', '' );

		} else {

			$heading = ucwords( str_replace( array( '-', '_' ), ' ', $this->getID() ) );
		}

		return apply_filters(
			'Connections_Directory/Content_Block/Heading',
			$heading,
			$this->getID(),
			$this->getObject()
		);
	}

	/**
	 * The Content Block contents. Content should be echoed.
	 *
	 * This must be overridden in the registered Content Block subclass.
	 *
	 * @since 9.7
	 */
	protected function content() {

		$hook = "Connections_Directory/Content_Block/Content/{$this->getID()}";

		if ( has_action( $hook ) ) {

			do_action( $hook, $this );

		} else {

			/* translators: Class method name. */
			printf( esc_html__( 'Method "%s" not implemented. Must be overridden in subclass.', 'connections' ), __METHOD__ );
		}
	}

	/**
	 * Callback for the `"Connections_Directory/Content_Block/Render/{$this->getID()}"` action.
	 *
	 * The variable portion of the hook name is the registered content block ID.
	 *
	 * This is the default callback registered when adding a Content Block. This can be overridden by setting the
	 * `render_callback` parameter when registering a Content Block. @see Content_Block::__construct()
	 *
	 * @since 9.7
	 *
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function render( $echo = true ) {

		$html = '';

		if ( $this->isPermitted() ) {

			ob_start();

			$this->content();

			$html = ob_get_clean();

			if ( 0 < strlen( $html ) && true === $this->get( 'render_container' ) ) {

				$containerID = $this->get( 'block_id', '' );
				$idAttribute = '';

				if ( is_string( $containerID ) && 0 < strlen( $containerID ) ) {

					$idAttribute = ' id="' . esc_attr( $containerID ) . '"';
				}

				$heading = $this->heading();
				$heading = empty( $heading ) ? '' : sprintf( '<%1$s>%2$s</%1$s>', $this->get( 'heading_tag' ), $heading );

				$html = sprintf(
					'<%1$s class="%2$s"%3$s>%4$s%5$s%6$s%7$s</%1$s>' . PHP_EOL,
					$this->get( 'block_tag' ),
					$this->get( 'block_class' ),
					$idAttribute,
					$this->get( 'before' ),
					$heading,
					$html,
					$this->get( 'after' )
				);

			} elseif ( 0 < strlen( $html ) && false === $this->get( 'render_container' ) ) {

				$html = $this->get( 'before' ) . $html . $this->get( 'after' );
			}

			// Frontend script.
			if ( 0 < strlen( $html ) && ! empty( $handle = $this->get( 'script_handle' ) ) ) {

				wp_enqueue_script( $handle );
			}
		}

		if ( true === $echo ) {

			// HTML is escaped in the render callback.
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html;
	}

	/**
	 * The `"Connections_Directory/Content_Block/Render/{$this->getID()}"` action.
	 *
	 * The variable portion of the hook name is the registered content block ID.
	 *
	 * @since 9.7
	 *
	 * @return string
	 */
	public function asHTML() {

		$html = '';
		$hook = "Connections_Directory/Content_Block/Render/{$this->getID()}";

		if ( has_action( $hook ) ) {

			ob_start();

			do_action( $hook, true );

			$html = ob_get_clean();

			if ( ! is_string( $html ) ) {

				$html = '';
			}
		}

		return $html;
	}

	/**
	 * Returns rendered Content Block.
	 *
	 * @since 9.7
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->asHTML();
	}

	/**
	 * Get called class short name.
	 *
	 * @since 9.8
	 *
	 * @link https://stackoverflow.com/a/41264231/5351316
	 *
	 * @return string
	 */
	protected function getShortName() {

		$shortName = substr( static::class, ( $p = strrpos( static::class, '\\' ) ) !== false ? $p + 1 : 0 );

		if ( ! is_string( $shortName ) ) {

			$shortName = '';
		}

		return $shortName;
	}
}
