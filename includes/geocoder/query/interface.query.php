<?php

namespace Connections_Directory\Geocoder\Query;

/**
 * Interface Query
 *
 * @package Connections_Directory\Geocoder\Query
 * @author  Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author  Steve A. Zahm
 * @license MIT License
 * @since 8.26
 */
interface Query {

	/**
	 * @since 8.26
	 *
	 * @param string $locale
	 *
	 * @return Query
	 */
	public function withLocale( $locale );

	/**
	 * @since 8.26
	 *
	 * @param int $limit
	 *
	 * @return Query
	 */
	public function withLimit( $limit );

	/**
	 * @since 8.26
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return Query
	 */
	public function withData( $name, $value );

	/**
	 * @since 8.26
	 *
	 * @return string|null
	 */
	public function getLocale();

	/**
	 * @since 8.26
	 *
	 * @return int
	 */
	public function getLimit();

	/**
	 * @since 8.26
	 *
	 * @param string     $name
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public function getData( $name, $default = null );

	/**
	 * @since 8.26
	 *
	 * @return array
	 */
	public function getAllData();

	/**
	 * @since 8.26
	 *
	 * @return string
	 */
	public function __toString();
}
