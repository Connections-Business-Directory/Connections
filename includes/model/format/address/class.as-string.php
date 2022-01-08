<?php

namespace Connections_Directory\Model\Format\Address;

use cnString;
use Connections_Directory\Model\Address;

/**
 * @package Connections_Directory\Model\Format\Address
 * @since   8.26
 *
 * @author Steven A. Zahm
 *
 * @license MIT License
 */
class As_String {

	/**
	 * @since 8.26
	 * @var string
	 */
	const LINE_1 = '{{line_1}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const LINE_2 = '{{line_2}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const LINE_3 = '{{line_3}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const LINE_4 = '{{line_4}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const LOCALITY = '{{locality}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const DISTRICT = '{{district}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const COUNTY = '{{county}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const REGION = '{{region}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const POSTAL_CODE = '{{postal_code}}';

	/**
	 * @since 8.26
	 * @var string
	 */
	const COUNTRY = '{{country}}';

	/**
	 * @since 8.26
	 *
	 * @param Address|\cnAddress $address
	 * @param string             $format
	 *
	 * @return String
	 */
	public static function format( $address, $format = '' ) {

		if ( 0 === strlen( $format ) ) {

			$format = self::LINE_1 . ' ' .
					  self::LINE_2 . ' ' .
					  self::LOCALITY . ' ' .
					  self::REGION . ' ' .
					  self::POSTAL_CODE . ' ' .
					  self::COUNTRY;
		}

		$replace = array(
			self::LINE_1      => $address->getLineOne(),
			self::LINE_2      => $address->getLineTwo(),
			self::LINE_3      => $address->getLineThree(),
			self::LINE_4      => $address->getLineFour(),
			self::LOCALITY    => $address->getLocality(),
			self::DISTRICT    => $address->getDistrict(),
			self::COUNTY      => $address->getCounty(),
			self::REGION      => $address->getRegion(),
			self::POSTAL_CODE => $address->getPostalCode(),
			self::COUNTRY     => $address->getCountry(),
		);

		$string = trim( strtr( $format, $replace ) );

		return cnString::replaceWhatWith( $string, ' ' );
	}
}
