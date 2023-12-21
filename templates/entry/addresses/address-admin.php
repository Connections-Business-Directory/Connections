<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection $addresses
 * @var cnAddress    $address
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

foreach ( $addresses as $address ) {
	$outCache = array();

	echo '<div style="margin: 10px 0;">';
	( $address->preferred ) ? $preferred = '*' : $preferred = '';

	if ( ! empty( $address->name ) ) {
		echo '<span style="display: block"><strong>', esc_html( "{$address->name}{$preferred}" ), '</strong></span>';
	}

	if ( ! empty( $address->line_1 ) ) {
		echo '<span style="display: block">', esc_html( $address->line_1 ), '</span>';
	}

	if ( ! empty( $address->line_2 ) ) {
		echo '<span style="display: block">', esc_html( $address->line_2 ), '</span>';
	}

	if ( ! empty( $address->line_3 ) ) {
		echo '<span style="display: block">', esc_html( $address->line_3 ), '</span>';
	}

	if ( 0 < strlen( $address->line_4 ) ) {
		echo '<span style="display: block">', esc_html( $address->line_4 ), '</span>';
	}

	if ( 0 < strlen( $address->district ) ) {
		echo '<span style="display: block">', esc_html( $address->district ), '</span>';
	}

	if ( 0 < strlen( $address->county ) ) {
		echo '<span style="display: block">', esc_html( $address->county ), '</span>';
	}

	if ( ! empty( $address->city ) ) {
		$outCache[] = '<span>' . esc_html( $address->city ) . '</span>';
	}

	if ( ! empty( $address->state ) ) {
		$outCache[] = '<span>' . esc_html( $address->state ) . '</span>';
	}

	if ( ! empty( $address->zipcode ) ) {
		$outCache[] = '<span>' . esc_html( $address->zipcode ) . '</span>';
	}

	if ( ! empty( $outCache ) ) {
		// HTML is escaped above.
		echo '<span style="display: block">', implode( '&nbsp;', $outCache ), '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( ! empty( $address->country ) ) {
		echo '<span style="display: block">', esc_html( $address->country ), '</span>';
	}

	if ( ! empty( $address->latitude ) && ! empty( $address->longitude ) ) {
		echo '<span style="display: block">', '<strong>', esc_html__( 'Latitude', 'connections' ), ':</strong>', ' ', esc_html( $address->latitude ), ' ', '<strong>', esc_html__( 'Longitude', 'connections' ), ':</strong>', ' ', esc_html( $address->longitude ), '</span>';
	}

	echo '</div>';
}

unset( $outCache );
