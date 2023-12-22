<?php

namespace Connections_Directory\Geocoder\Provider;

use WP_Error;
use cnCollection as Collection;
use Connections_Directory\Geocoder\Query\Address;
use Connections_Directory\Geocoder\Query\Coordinates;

/**
 * Providers MUST always be stateless and immutable.
 *
 * @author  William Durand <william.durand1@gmail.com>
 * @author  Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author  Steven A. Zahm
 * @license MIT License
 */
interface Provider {

	/**
	 * @param Address $query
	 *
	 * @return Collection|WP_Error
	 */
	public function geocode( Address $query );

	/**
	 * @param Coordinates $query
	 *
	 * @return Collection|WP_Error
	 */
	public function reverse( Coordinates $query );

	/**
	 * Returns the provider's name.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns the provider's id.
	 *
	 * @return string
	 */
	public function getID();
}
