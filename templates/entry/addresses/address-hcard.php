<?php
/**
 * This is basically a copy/paste of the code which use to reside in cnOutput::getAddressBlock().
 *
 * @todo Clean so it is better "template" code.
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $addresses
 * @var cnAddress    $address
 */

$out    = '';
$search = array(
	'%label%',
	'%line1%',
	'%line2%',
	'%line3%',
	'%line4%',
	'%district%',
	'%county%',
	'%city%',
	'%state%',
	'%zipcode%',
	'%country%',
	'%geo%',
	'%separator%',
);

$out .= '<span class="address-block">';

foreach ( $addresses as $address ) {
	$replace = array();

	$out .= '<span class="adr cn-address' . ( $address->preferred ? ' cn-preferred cn-address-preferred' : '' ) . '">';

	// The `notranslate` class is added to prevent Google Translate from translating the text.
	$replace[] = empty( $address->name ) ? '' : '<span class="address-name">' . $address->name . '</span>';
	$replace[] = empty( $address->line_1 ) ? '' : '<span class="street-address notranslate">' . $address->line_1 . '</span>';
	$replace[] = empty( $address->line_2 ) ? '' : '<span class="street-address notranslate">' . $address->line_2 . '</span>';
	$replace[] = empty( $address->line_3 ) ? '' : '<span class="street-address notranslate">' . $address->line_3 . '</span>';
	$replace[] = empty( $address->line_4 ) ? '' : '<span class="street-address notranslate">' . $address->line_4 . '</span>';

	if ( 0 == strlen( $address->district ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['district'] ) {

			$district = cnURL::permalink(
				array(
					'type'       => 'district',
					'slug'       => $address->district,
					'title'      => $address->district,
					'text'       => $address->district,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => TRUE,
				)
			);

		} else {

			$district = $address->district;
		}

		$replace[] = '<span class="district notranslate">' . $district . '</span>';

	}

	if ( 0 == strlen( $address->county ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['county'] ) {

			$county = cnURL::permalink(
				array(
					'type'       => 'county',
					'slug'       => $address->county,
					'title'      => $address->county,
					'text'       => $address->county,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => TRUE,
				)
			);

		} else {

			$county = $address->county;
		}

		$replace[] = '<span class="county notranslate">' . $county . '</span>';

	}

	if ( empty( $address->city ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['locality'] ) {

			$locality = cnURL::permalink(
				array(
					'type'       => 'locality',
					'slug'       => $address->city,
					'title'      => $address->city,
					'text'       => $address->city,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => TRUE,
				)
			);

		} else {

			$locality = $address->city;
		}

		$replace[] = '<span class="locality">' . $locality . '</span>';

	}

	if ( empty( $address->state ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['region'] ) {

			$region = cnURL::permalink(
				array(
					'type'       => 'region',
					'slug'       => $address->state,
					'title'      => $address->state,
					'text'       => $address->state,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => TRUE,
				)
			);

		} else {

			$region = $address->state;
		}

		$replace[] = '<span class="region">' . $region . '</span>';

	}

	if ( empty( $address->zipcode ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['postal_code'] ) {

			$postal = cnURL::permalink(
				array(
					'type'       => 'postal_code',
					'slug'       => $address->zipcode,
					'title'      => $address->zipcode,
					'text'       => $address->zipcode,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => TRUE,
				)
			);

		} else {

			$postal = $address->zipcode;
		}

		$replace[] = '<span class="postal-code">' . $postal . '</span>';

	}

	if ( empty( $address->country ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['country'] ) {

			$country = cnURL::permalink(
				array(
					'type'       => 'country',
					'slug'       => $address->country,
					'title'      => $address->country,
					'text'       => $address->country,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => TRUE,
					)
			);

		} else {

			$country = $address->country;
		}

		$replace[] = '<span class="country-name">' . $country . '</span>';

	}

	if ( ! empty( $address->latitude ) || ! empty( $address->longitude ) ) {
		$replace[] = '<span class="geo">' .
		             ( empty( $address->latitude ) ? '' : '<span class="latitude" title="' . $address->latitude . '"><span class="cn-label">' . __( 'Latitude', 'connections' ) . ': </span>' . $address->latitude . '</span>' ) .
		             ( empty( $address->longitude ) ? '' : '<span class="longitude" title="' . $address->longitude . '"><span class="cn-label">' . __( 'Longitude', 'connections' ) . ': </span>' . $address->longitude . '</span>' ) .
		             '</span>';
	}

	$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

	$out .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label% %line1% %line2% %line3% %line4% %district% %county% %city% %state%  %zipcode% %country%' : $defaults['format'] ) : $atts['format']
	);

	// Set the hCard Address Type.
	$out .= $entry->gethCardAdrType( $address->type );

	$out .= '</span>';
}

$out .= '</span>';

echo $out;
