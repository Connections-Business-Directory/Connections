<?php
/**
 * This is basically a copy/paste of the code which used to reside in cnOutput::getPhoneNumberBlock().
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $phoneNumbers
 * @var cnPhone      $phone
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_string;

$rows   = array();
$search = array( '%label%', '%number%', '%separator%' );

foreach ( $phoneNumbers as $phone ) {

	$replace = array();

	$classNames = array(
		'tel',
		'cn-phone-number',
	);

	$classNames[] = _string::applyPrefix( 'cn-phone-number-type-', $phone->type );

	if ( $phone->preferred ) {

		$classNames[] = 'cn-preferred';
		$classNames[] = 'cn-phone-number-preferred';
	}

	$replace[] = empty( $phone->name ) ? '' : '<span class="phone-name">' . esc_html( $phone->name ) . '</span>';

	if ( empty( $phone->number ) ) {

		$replace[] = '';

	} else {

		if ( Connections_Directory()->settings->get( 'connections', 'link', 'phone' ) ) {

			$replace[] = '<a class="value" href="' . esc_url( "tel:{$phone->number}" ) . '" value="' . esc_attr( preg_replace( '/[^0-9]/', '', $phone->number ) ) . '">' . esc_html( $phone->number ) . '</a>';

		} else {

			$replace[] = '<span class="value">' . esc_html( $phone->number ) . '</span>';
		}

	}

	$replace[] = '<span class="cn-separator">' . esc_html( $atts['separator'] ) . '</span>';

	$row = '<span class="' . _escape::classNames( $classNames ) . '">';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %number%' : $defaults['format'] ) : $atts['format']
	);

	// Set the hCard Phone Number Type.
	$row .= $entry->gethCardTelType( $phone->type );

	$row .= '</span>' . PHP_EOL;

	$rows[] = apply_filters( 'cn_output_phone_number', _string::normalize( $row ), $phone, $entry, $atts );
}

$block = '<span class="phone-number-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL . '</span>';

$block = apply_filters( 'cn_output_phone_numbers', $block, $phoneNumbers, $entry, $atts );

// HTML is escaped in the loop above.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
