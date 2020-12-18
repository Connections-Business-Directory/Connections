<?php

namespace Connections_Directory\Sitemaps;

/**
 * Class Providers
 *
 * @package Connections_Directory\Sitemaps
 */
final class Registry {

	/**
	 * @since 10.0
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Registered sitemap providers.
	 *
	 * @since 10.0
	 *
	 * @var Provider[]
	 */
	private $providers = array();

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 10.0
	 */
	protected function __construct() { /* Do nothing here */ }

	/**
	 * Initialize the Provider registry.
	 *
	 * @since 10.0
	 */
	protected static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self();
		}
	}

	/**
	 * Get the Registry instance.
	 *
	 * @since 10.0
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
	 * Add a new Provider to the registry.
	 *
	 * @since 10.0
	 *
	 * @param string   $name     Unique name for the sitemap provider.
	 * @param Provider $provider An instance of the Provider object.
	 *
	 * @return bool Whether the provider was added successfully.
	 */
	public function addProvider( $name, Provider $provider ) {

		if ( isset( $this->providers[ $name ] ) ) {
			return false;
		}

		/**
		 * Filters the sitemap provider instances before it is added.
		 *
		 * @since 10.0
		 *
		 * @param Provider $provider Instance of Provider.
		 * @param string   $name     Name of the provider.
		 */
		$provider = apply_filters( 'Connections_Directory/Sitemaps/Registry/Add_Provider', $provider, $name );

		$this->providers[ $name ] = $provider;

		return true;
	}

	/**
	 * Get all registered Providers.
	 *
	 * @since 10.0
	 *
	 * @return Provider[]
	 */
	public function getProviders() {

		return $this->providers;
	}
}
