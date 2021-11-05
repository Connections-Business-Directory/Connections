<?php
/**
 * This is basically a copy/paste of the code which used to reside in cnOutput::getEmailAddressBlock().
 *
 * @var array           $atts
 * @var cnOutput        $entry
 * @var cnCollection    $emailAddresses
 * @var cnEmail_Address $email
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_string;

$rows      = array();
$search    = array( '%label%', '%address%', '%icon%', '%separator%' );
$iconSizes = array( 16, 24, 32, 48, 64 );

// Replace the 'Name Tokens' with the entry's name.
$name = $entry->getName(
	array(
		'format' => empty( $atts['title'] ) ? '%first% %last% %type% email.' : $atts['title'],
	)
);

/*
 * Ensure the supplied size is valid, if not reset to the default value.
 */
in_array( $atts['size'], $iconSizes ) ? $iconSize = $atts['size'] : $iconSize = 32;

foreach ( $emailAddresses as $email ) {

	$replace = array();

	$classNames = array(
		'email',
		'cn-email-address',
	);

	if ( $email->preferred ) {

		$classNames[] = 'cn-preferred';
		$classNames[] = 'cn-email-address-preferred';
	}

	// Replace the 'Email Tokens' with the email info.
	$title = str_ireplace( array( '%type%', '%name%' ), array( $email->type, $email->name ), $name );

	$replace[] = empty( $email->name ) ? '' : '<span class="email-name">' . esc_html( $email->name ) . '</span>';
	$replace[] = empty( $email->address ) ? '' : '<span class="email-address"><a class="value" title="' . esc_attr( $title ) . '" href="' . esc_url( "mailto:{$email->address}" ) . '">' . esc_html( $email->address ) . '</a></span>';

	$replace[] = empty( $email->address ) ? '' : '<span class="email-icon"><a class="value" title="' . esc_attr( $title ) . '" href="' . esc_url( "mailto:{$email->address}" ) . '"><img src="' . esc_url( CN_URL . "assets/images/icons/mail/mail_{$iconSize}.png" ) . '" height="' . esc_attr( $iconSize ) . '" width="' . esc_attr( $iconSize ) . '"/></a></span>';
	$replace[] = '<span class="cn-separator">' . esc_html( $atts['separator'] ) . '</span>';

	$row = '<span class="' . _escape::classNames( $classNames ) . '">';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %address%' : $defaults['format'] ) : $atts['format']
	);

	// Set the hCard Email Address Type.
	$row .= '<span class="type" style="display: none;">INTERNET</span>';

	$row .= '</span>';

	$rows[] = apply_filters( 'cn_output_email_address', _string::normalize( $row ), $email, $entry, $atts );
}

$block = '<span class="email-address-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL . '</span>';

// This filter is required to allow the ROT13 encryption plugin to function.
$block = apply_filters( 'cn_output_email_addresses', $block, $emailAddresses, $entry, $atts );

// HTML is escaped in the loop above.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
