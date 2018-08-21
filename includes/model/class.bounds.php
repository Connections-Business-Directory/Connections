<?php

namespace Connections_Directory\Model;

use Connections_Directory\Geocoder\Assert;

/**
 * Class Bounds
 *
 * @package Connections_Directory\Model
 *
 * @author  William Durand <william.durand1@gmail.com>
 * @author  Steven A. Zahm
 * @license MIT License
 *
 * @since 8.26
 */
final class Bounds {

	/**
	 * @since 8.26
	 * @var float
	 */
	private $south;

	/**
	 * @since 8.26
	 * @var float
	 */
	private $west;

	/**
	 * @since 8.26
	 * @var float
	 */
	private $north;

	/**
	 * @since 8.26
	 * @var float
	 */
	private $east;

	/**
	 * @since 8.26
	 *
	 * @param float $south
	 * @param float $west
	 * @param float $north
	 * @param float $east
	 */
	public function __construct( $south, $west, $north, $east ) {

		Assert::notNull( $south );
		Assert::notNull( $west );
		Assert::notNull( $north );
		Assert::notNull( $east );

		$south = (float) $south;
		$north = (float) $north;
		$west  = (float) $west;
		$east  = (float) $east;

		Assert::latitude( $south );
		Assert::latitude( $north );
		Assert::longitude( $west );
		Assert::longitude( $east );

		$this->south = $south;
		$this->west  = $west;
		$this->north = $north;
		$this->east  = $east;
	}

	/**
	 * Returns the south bound.
	 *
	 * @since 8.26
	 *
	 * @return float
	 */
	public function getSouth() {

		return $this->south;
	}

	/**
	 * Returns the west bound.
	 *
	 * @since 8.26
	 *
	 * @return float
	 */
	public function getWest() {

		return $this->west;
	}

	/**
	 * Returns the north bound.
	 *
	 * @since 8.26
	 *
	 * @return float
	 */
	public function getNorth() {

		return $this->north;
	}

	/**
	 * Returns the east bound.
	 *
	 * @since 8.26
	 *
	 * @return float
	 */
	public function getEast() {

		return $this->east;
	}

	/**
	 * Returns an array with bounds.
	 *
	 * @since 8.26
	 *
	 * @return array
	 */
	public function toArray() {

		return array(
			'south' => $this->getSouth(),
			'west'  => $this->getWest(),
			'north' => $this->getNorth(),
			'east'  => $this->getEast(),
		);
	}
}
