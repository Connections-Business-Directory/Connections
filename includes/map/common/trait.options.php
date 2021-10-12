<?php

namespace Connections_Directory\Map\Common;

/**
 * Trait Options
 *
 * @package Connections_Directory\Map\Common
 * @author  Steven A. Zahm
 * @since   8.28
 */
trait Options {

	/**
	 * @since 8.28
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Get all defined options.
	 *
	 * @since 8.28
	 *
	 * @return array
	 */
	public function getOptions() {

		return $this->options;
	}

	/**
	 * Set options by calling options methods.
	 *
	 * @since 8.28
	 *
	 * @param array $options Options being added.
	 *
	 * @return $this
	 */
	public function setOptions( $options ) {

		foreach ( $options as $name => $value ) {

			$this->setOption( $name, $value );
		}

		return $this;
	}

	/**
	 * Set an option.
	 *
	 * If a set<OptionName> method does not exists save as supplied.
	 * The setter method should handle the converting from native value to expected value.
	 * The setter method should save the option value using the @see Options::store() method.
	 *
	 * @since 8.28
	 *
	 * @param string $name  Name of the option.
	 * @param mixed  $value Value of the option.
	 *
	 * @return $this
	 */
	public function setOption( $name, $value ) {

		$method = 'set' . ucfirst( $name );

		if ( method_exists( $this, $method ) ) {

			$this->$method( $value );

		} else {

			$this->store( $name, $value );
		}

		return $this;
	}

	/**
	 * Store the options value in the options array.
	 *
	 * @since 8.28
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return $this
	 */
	private function store( $name, $value ) {

		$this->options[ $name ] = $value;

		return $this;
	}

	/**
	 * Get an option.
	 *
	 * @since 8.28
	 *
	 * @param string $name    Name of the option.
	 * @param mixed  $default Default value if no option is set.
	 *
	 * @return mixed
	 */
	public function getOption( $name, $default = null ) {

		if ( isset( $this->options[ $name ] ) ) {
			return $this->options[ $name ];
		}

		return $default;
	}

	/**
	 * Remove an option.
	 *
	 * @since 8.28
	 *
	 * @param string $name Name of the option.
	 *
	 * @return $this
	 */
	public function removeOption( $name ) {

		unset( $this->options[ $name ] );

		return $this;
	}
}
