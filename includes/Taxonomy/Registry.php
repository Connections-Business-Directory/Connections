<?php

namespace Connections_Directory\Taxonomy;

use Connections_Directory\Taxonomy;
use WP_Error;

/**
 * Class Registry
 *
 * @package Connections_Directory\Taxonomy
 */
final class Registry {

	/**
	 * @since 10.2
	 * @var self
	 */
	private static $instance;

	/**
	 * Registered taxonomies.
	 *
	 * @since 10.2
	 * @var Taxonomy[]
	 */
	private $taxonomies = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 10.2
	 */
	protected function __construct() { /* Do nothing here */ }

	/**
	 * Initialize the Taxonomy Registry instance.
	 *
	 * @since 10.2
	 */
	protected static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self();
		}
	}

	/**
	 * Get the Registry instance.
	 *
	 * @since 10.2
	 *
	 * @return Registry
	 */
	public static function get() {

		if ( ! self::$instance instanceof self ) {

			self::init();
		}

		return self::$instance;
	}

	/**
	 * @since 10.2
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function exists( $slug ) {

		return isset( $this->taxonomies[ $slug ] );
	}

	/**
	 * @since 10.2
	 *
	 * @param string $slug
	 *
	 * @return Taxonomy|false
	 */
	public function getTaxonomy( $slug ) {

		if ( ! $this->exists( $slug ) ) {

			return false;
		}

		return $this->taxonomies[ $slug ];
	}

	/**
	 * Get all registered Taxonomies.
	 *
	 * @since 10.2
	 *
	 * @return Taxonomy[]
	 */
	public function getTaxonomies() {

		return $this->taxonomies;
	}

	/**
	 * @since 10.2
	 *
	 * @param string $slug
	 * @param array  $atts
	 *
	 * @return Taxonomy|WP_Error
	 */
	public function register( $slug, $atts ) {

		if ( empty( $slug ) || strlen( $slug ) > 32 ) {
			_doing_it_wrong(
				__METHOD__,
				esc_html__( 'Taxonomy names must be between 1 and 32 characters in length.', 'connections' ),
				'10.2'
			);

			return new WP_Error(
				'taxonomy_length_invalid',
				__( 'Taxonomy names must be between 1 and 32 characters in length.', 'connections' ),
				$slug
			);
		}

		if ( isset( $this->taxonomies[ $slug ] ) ) {
			return new WP_Error(
				'taxonomy_exists',
				__( 'Taxonomy has already been registered.', 'connections' ),
				$slug
			);
		}

		$taxonomy = new Taxonomy( $slug, $atts );

		$this->taxonomies[ $taxonomy->getSlug() ] = $taxonomy;

		/**
		 * Fires after a taxonomy is registered.
		 *
		 * @since 10.2
		 *
		 * @param Taxonomy $taxonomy The registered taxonomy.
		 */
		do_action( 'Connections_Directory/Taxonomy/Registry/Registered', $taxonomy );

		return $taxonomy;
	}

	/**
	 * @since 10.2
	 *
	 * @param string $slug
	 *
	 * @return bool|WP_Error
	 */
	public function unregister( $slug ) {

		if ( ! $this->exists( $slug ) ) {

			return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.', 'connections' ), $slug );
		}

		$taxonomy = $this->getTaxonomy( $slug );

		// Do not allow unregistering internal taxonomies.
		if ( $taxonomy->isBuiltin() ) {

			return new WP_Error( 'invalid_taxonomy', __( 'Unregistering a built-in taxonomy is not allowed.', 'connections' ) );
		}

		$taxonomy->_destruct();

		// Remove the taxonomy.
		unset( $this->taxonomies[ $taxonomy->getSlug() ] );

		/**
		 * Fires after a taxonomy is registered.
		 *
		 * @since 10.2
		 *
		 * @param Taxonomy $taxonomy The registered taxonomy.
		 */
		do_action( 'Connections_Directory/Taxonomy/Registry/Unregistered', $taxonomy );

		return true;
	}
}
