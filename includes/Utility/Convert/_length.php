<?php

namespace Connections_Directory\Utility\Convert;

/**
 * Class _length
 *
 * @package Connections_Directory\Utility
 */
final class _length {

	/**
	 * @since 10.3
	 * @var string
	 */
	private $decimal;

	/**
	 * @since 10.3
	 * @var string
	 */
	private $delimiter;

	/**
	 * @since 10.3
	 * @var float
	 */
	private $value;

	/**
	 * @since 10.3
	 * @var float[]
	 */
	private $ratio = array(
		'mm'   => 1000, // (si) millimeters
		'cm'   => 100, // (si) centimeters
		'dm'   => 10,  // (si) decimeters
		'm'    => 1,  // (si) meters
		'dam'  => .1, // (si) decameters
		'hm'   => .01, // (si) hectometers
		'km'   => .001, // (si) kilometers
		'in'   => 39.370078740157,
		'feet' => 3.2808398950131,
		'yd'   => 1.0936132983377,
		'mi'   => 0.00062137119223733,
		'li'   => 4.9709695378987, // US survey -- link
		'sft'  => 3.2808334366796, // US survey -- survey foot
		'rd'   => 0.198838781516,  // US survey -- rod
		'ch'   => 0.04970969537899, // US survey -- chain
		'fur'  => 0.0049709695378987, // US survey -- furlong
		'smi'  => 0.00062136994937697, // US survey -- survey mile
		'lea'  => 0.0002071237307458, // US survey -- league
		'nmi'  => 0.000539956803456,  // Nautical Mile
	);

	/**
	 * @since 10.3
	 * @var string
	 */
	private $unit;

	/**
	 * _length constructor.
	 *
	 * @since 10.3
	 *
	 * @param float  $length
	 * @param string $unit
	 */
	public function __construct( $length, $unit ) {

		$this->value = $length;
		$this->unit  = $unit;

		$locale_info = localeconv();

		$this->decimal   = $locale_info['decimal_point'];
		$this->delimiter = $locale_info['thousands_sep'];
	}

	/**
	 * Static constructor.
	 *
	 * @since 10.3
	 *
	 * @param float  $length
	 * @param string $unit
	 *
	 * @return _length
	 */
	public static function convert( $length, $unit ) {

		return new self( $length, $unit );
	}

	/**
	 * Format the current value.
	 *
	 * @since 10.3
	 *
	 * @param string   $decimal
	 * @param string   $delimiter
	 * @param int|null $precision
	 * @param bool     $round
	 *
	 * @return string
	 */
	public function format( $decimal = '.', $delimiter = ',', $precision = null, $round = true ) {

		if ( ! is_null( $precision ) ) {

			$value = $this->round( $precision, $round );

			return number_format( $value, $precision, $decimal, $delimiter ) . " {$this->unit}";
		}

		$parts = explode( $this->decimal, $this->value );

		return number_format( (float) $parts[0], 0, $decimal, $delimiter ) . "{$decimal}{$parts[1]} {$this->unit}";
	}

	/**
	 * Change the length/unit to be converted.
	 *
	 * @since 10.3
	 *
	 * @param float  $length
	 * @param string $unit
	 */
	public function from( $length, $unit ) {

		$this->value = $length;
		$this->unit  = $unit;
	}

	/**
	 * The unit of length to convert to.
	 *
	 * @param string   $unit
	 * @param int|null $precision
	 * @param bool     $round
	 *
	 * @return float|int
	 */
	public function to( $unit, $precision = null, $round = true ) {

		// Convert to si (m).
		$meters = $this->value * ( 1 / $this->ratio[ $this->unit ] );

		// Convert to desired unit.
		$value = $meters * $this->ratio[ $unit ];

		$this->value = $value;
		$this->unit  = $unit;

		if ( ! is_null( $precision ) ) {

			$value = $this->round( $precision, $round );
		}

		return $value;
	}

	/**
	 * Round the correct value.
	 *
	 * @since 10.3
	 *
	 * @param int  $precision
	 * @param bool $round
	 *
	 * @return float
	 */
	private function round( $precision, $round ) {

		$mode = $round ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN;

		return round( $this->value, $precision, $mode );
	}

	/**
	 * @since 10.3
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->format();
	}
}
