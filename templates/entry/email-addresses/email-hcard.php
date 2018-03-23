<?php
/**
 * This is basically a copy/paste of the code which use to reside in cnOutput::getEmailAddressBlock().
 *
 * @todo Clean so it is better "template" code.
 *
 * @var array           $atts
 * @var cnOutput        $entry
 * @var cnCollection    $emailAddresses
 * @var cnEmail_Address $email
 */

$rows      = array();
$search    = array( '%label%', '%address%', '%icon%', '%separator%' );
$iconSizes = array( 16, 24, 32, 48, 64 );

// Replace the 'Name Tokens' with the entry's name.
$name = $entry->getName(
	array(
		'format' => empty( $atts['title'] ) ? '%first% %last% %type% email.' : $atts['title']
	)
);

/*
 * Ensure the supplied size is valid, if not reset to the default value.
 */
in_array( $atts['size'], $iconSizes ) ? $iconSize = $atts['size'] : $iconSize = 32;

foreach ( $emailAddresses as $email ) {

	$replace = array();

	// Replace the 'Email Tokens' with the email info.
	$title = str_ireplace( array( '%type%', '%name%' ), array( $email->type, $email->name ), $name );

	$replace[] = ( empty( $email->name ) ) ? '' : '<span class="email-name">' . $email->name . '</span>';
	$replace[] = ( empty( $email->address ) ) ? '' : '<span class="email-address"><a class="value" title="' . $title . '" href="mailto:' . $email->address . '">' . $email->address . '</a></span>';

	/** @noinspection HtmlUnknownTarget */
	$replace[] = ( empty( $email->address ) ) ? '' : '<span class="email-icon"><a class="value" title="' . $title . '" href="mailto:' . $email->address . '"><img src="' . CN_URL . 'assets/images/icons/mail/mail_' . $iconSize . '.png" height="' . $iconSize . '" width="' . $iconSize . '"/></a></span>';
	$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

	$row = "\t" . '<span class="email cn-email-address' . ( $email->preferred ? ' cn-preferred cn-email-address-preferred' : '' ) . '">';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %address%' : $defaults['format'] ) : $atts['format']
	);

	// Set the hCard Email Address Type.
	$row .= '<span class="type" style="display: none;">INTERNET</span>';

	$row .= '</span>';

	$rows[] = apply_filters( 'cn_output_email_address', cnString::replaceWhatWith( $row, ' ' ), $email, $entry, $atts );
}

$block = '<span class="email-address-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL . '</span>';

// This filter is required to allow the ROT13 encryption plugin to function.
$block = apply_filters( 'cn_output_email_addresses', $block, $emailAddresses, $entry, $atts );

echo $block;
