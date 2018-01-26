<?php
/**
 * This is basically a copy/paste of the code which use to reside in cnOutput::getPhoneNumberBlock().
 *
 * @todo Clean so it is better "template" code.
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $phoneNumbers
 * @var cnPhone      $phone
 */

$rows         = array();
$search       = array( '%label%' , '%number%' , '%separator%' );

foreach ( $phoneNumbers as $phone ) {
	$replace = array();

	$row = "\t" . '<span class="tel cn-phone-number' . ( $phone->preferred ? ' cn-preferred cn-phone-number-preferred' : '' ) . '">';

	$replace[] = empty( $phone->name ) ? '' : '<span class="phone-name">' . $phone->name . '</span>';

	if ( empty( $phone->number ) ) {
		$replace[] = '';
	} else {

		if ( Connections_Directory()->settings->get( 'connections', 'link', 'phone' ) ) {

			$replace[] = '<a class="value" href="tel:' . $phone->number . '" value="' . preg_replace( '/[^0-9]/', '', $phone->number ) . '">' . $phone->number . '</a>';

		} else {

			$replace[] = '<span class="value">' . $phone->number . '</span>';
		}

	}

	$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %number%' : $defaults['format'] ) : $atts['format']
	);

	// Set the hCard Phone Number Type.
	$row .= $entry->gethCardTelType( $phone->type );

	$row .= '</span>' . PHP_EOL;

	$rows[] = apply_filters( 'cn_output_phone_number', cnString::replaceWhatWith( $row, ' ' ), $phone, $entry, $atts );
}

$block = '<span class="phone-number-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL .'</span>';

$block = apply_filters( 'cn_output_phone_numbers', $block, $phoneNumbers, $entry, $atts );

echo $block;
