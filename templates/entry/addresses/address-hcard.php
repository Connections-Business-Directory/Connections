<?php
/**
 * This is basically a copy/paste of the code which used to reside in cnOutput::getAddressBlock().
 *
 * @todo Clean so it is better "template" code.
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $addresses
 * @var cnAddress    $address
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

use Connections_Directory\Utility\_escape;

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

	$classNames = array(
		'adr',
		'cn-address',
	);

	if ( $address->preferred ) {

		$classNames[] = 'cn-preferred';
		$classNames[] = 'cn-address-preferred';
	}

	$out .= '<span class="' . _escape::classNames( $classNames ) . '">';

	// The `notranslate` class is added to prevent Google Translate from translating the text.
	$replace[] = empty( $address->name ) ? '' : '<span class="address-name">' . esc_html( $address->name ) . '</span>';
	$replace[] = empty( $address->line_1 ) ? '' : '<span class="street-address notranslate">' . esc_html( $address->line_1 ) . '</span>';
	$replace[] = empty( $address->line_2 ) ? '' : '<span class="street-address notranslate">' . esc_html( $address->line_2 ) . '</span>';
	$replace[] = empty( $address->line_3 ) ? '' : '<span class="street-address notranslate">' . esc_html( $address->line_3 ) . '</span>';
	$replace[] = empty( $address->line_4 ) ? '' : '<span class="street-address notranslate">' . esc_html( $address->line_4 ) . '</span>';

	if ( 0 == strlen( $address->district ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['district'] ) {

			// Returns escaped HTML.
			$district = cnURL::permalink(
				array(
					'type'       => 'district',
					'slug'       => $address->url['district'],
					'title'      => $address->district,
					'text'       => $address->district,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => true,
				)
			);

		} else {

			$district = esc_html( $address->district );
		}

		$replace[] = '<span class="district notranslate">' . $district . '</span>';

	}

	if ( 0 == strlen( $address->county ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['county'] ) {

			// Returns escaped HTML.
			$county = cnURL::permalink(
				array(
					'type'       => 'county',
					'slug'       => $address->url['county'],
					'title'      => $address->county,
					'text'       => $address->county,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => true,
				)
			);

		} else {

			$county = esc_html( $address->county );
		}

		$replace[] = '<span class="county notranslate">' . $county . '</span>';

	}

	if ( empty( $address->city ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['locality'] ) {

			// Returns escaped HTML.
			$locality = cnURL::permalink(
				array(
					'type'       => 'locality',
					'slug'       => $address->url['locality'],
					'title'      => $address->city,
					'text'       => $address->city,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => true,
				)
			);

		} else {

			$locality = esc_html( $address->city );
		}

		$replace[] = '<span class="locality">' . $locality . '</span>';

	}

	if ( empty( $address->state ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['region'] ) {

			// Returns escaped HTML.
			$region = cnURL::permalink(
				array(
					'type'       => 'region',
					'slug'       => $address->url['region'],
					'title'      => $address->state,
					'text'       => $address->state,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => true,
				)
			);

		} else {

			$region = esc_html( $address->state );
		}

		$replace[] = '<span class="region">' . $region . '</span>';

	}

	if ( empty( $address->zipcode ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['postal_code'] ) {

			// Returns escaped HTML.
			$postal = cnURL::permalink(
				array(
					'type'       => 'postal_code',
					'slug'       => $address->url['postal_code'],
					'title'      => $address->zipcode,
					'text'       => $address->zipcode,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => true,
				)
			);

		} else {

			$postal = esc_html( $address->zipcode );
		}

		$replace[] = '<span class="postal-code">' . $postal . '</span>';

	}

	if ( empty( $address->country ) ) {

		$replace[] = '';

	} else {

		if ( $atts['link']['country'] ) {

			// Returns escaped HTML.
			$country = cnURL::permalink(
				array(
					'type'       => 'country',
					'slug'       => $address->url['country'],
					'title'      => $address->country,
					'text'       => $address->country,
					'home_id'    => $entry->directoryHome['page_id'],
					'force_home' => $entry->directoryHome['force_home'],
					'return'     => true,
				)
			);

		} else {

			$country = esc_html( $address->country );
		}

		$replace[] = '<span class="country-name">' . $country . '</span>';

	}

	if ( ! empty( $address->latitude ) || ! empty( $address->longitude ) ) {
		$replace[] = '<span class="geo">' .
					 empty( $address->latitude ? '' : '<span class="latitude" title="' . esc_attr( $address->latitude ) . '"><span class="cn-label">' . esc_html__( 'Latitude', 'connections' ) . ': </span>' . esc_html( $address->latitude ) . '</span>' ) .
					 empty( $address->longitude ? '' : '<span class="longitude" title="' . esc_attr( $address->longitude ) . '"><span class="cn-label">' . esc_html__( 'Longitude', 'connections' ) . ': </span>' . esc_html( $address->longitude ) . '</span>' ) .
					 '</span>';
	}

	$replace[] = '<span class="cn-separator">' . esc_html( $atts['separator'] ) . '</span>';

	$out .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label% %line1% %line2% %line3% %line4% %district% %county% %city% %state%  %zipcode% %country%' : $defaults['format'] ) : $atts['format']
	);

	// Set the hCard Address Type. Returns static HTML. No need to escape.
	$out .= $entry->gethCardAdrType( $address->type );

	$out .= '</span>';
}

$out .= '</span>';

// HTML is escaped in the loop above.
echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
