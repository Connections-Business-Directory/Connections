<?php
/**
 * This is a copy/paste of the code which use to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection $addresses
 * @var cnAddress    $address
 */

foreach ( $addresses as $address ) {
	$outCache = array();

	echo '<div style="margin: 10px 0;">';
	( $address->preferred ) ? $preferred = '*' : $preferred = '';

	if ( ! empty( $address->name ) ) echo '<span style="display: block"><strong>' , $address->name , $preferred , '</strong></span>';
	if ( ! empty( $address->line_1 ) ) echo '<span style="display: block">' , $address->line_1 , '</span>';
	if ( ! empty( $address->line_2 ) ) echo '<span style="display: block">' , $address->line_2 , '</span>';
	if ( ! empty( $address->line_3 ) ) echo '<span style="display: block">' , $address->line_3 , '</span>';
	if ( 0 < strlen( $address->line_4 ) ) echo '<span style="display: block">' , $address->line_4 , '</span>';

	if ( 0 < strlen( $address->district ) ) echo '<span style="display: block">' , $address->district , '</span>';
	if ( 0 < strlen( $address->county ) ) echo '<span style="display: block">' , $address->county , '</span>';

	if ( ! empty( $address->city ) ) $outCache[] = '<span>' . $address->city . '</span>';
	if ( ! empty( $address->state ) ) $outCache[] = '<span>' . $address->state . '</span>';
	if ( ! empty( $address->zipcode ) ) $outCache[] = '<span>' . $address->zipcode . '</span>';

	if ( ! empty( $outCache ) ) echo '<span style="display: block">' , implode( '&nbsp;', $outCache ) , '</span>';

	if ( ! empty( $address->country ) ) echo '<span style="display: block">' , $address->country , '</span>';
	if ( ! empty( $address->latitude ) && ! empty( $address->longitude ) ) echo '<span style="display: block">' , '<strong>' , __( 'Latitude', 'connections' ) , ':</strong>' , ' ' , $address->latitude , ' ' , '<strong>' , __( 'Longitude', 'connections' ) , ':</strong>' , ' ', $address->longitude , '</span>';
	echo '</div>';
}

unset( $outCache );
