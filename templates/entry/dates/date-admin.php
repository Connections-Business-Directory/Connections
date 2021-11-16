<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @var cnCollection $dates
 * @var cnDate       $date
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_string;

$date_format = cnSettingsAPI::get( 'connections', 'display_general', 'date_format' );
$search      = array( '%label%', '%date%', '%separator%' );
$out         = '<span class="date-block">' . PHP_EOL;

foreach ( $dates as $date ) {

	$replace = array();

	if ( false === $date->date ) {

		continue;
	}

	$classNames = array(
		'cn-date',
	);

	if ( $date->preferred ) {

		$classNames[] = 'cn-preferred';
		$classNames[] = 'cn-date-preferred';
	}

	$row = '<span class="' . _escape::classNames( $classNames ) . '">';

	$replace[] = empty( $date->name ) ? '' : '<span class="date-name">' . esc_html( $date->name ) . '</span>';
	$replace[] = empty( $date->date ) ? '' : '<span>' . date_i18n( $date_format, $date->date->getTimestamp(), false ) . '</span>';
	$replace[] = '<span class="cn-separator">:</span>';

	$row .= str_ireplace(
		$search,
		$replace,
		'%label%%separator% %date%'
	);

	$row .= '</span>';

	$out .= _string::normalize( $row ) . PHP_EOL;
}

$out .= '</span>' . PHP_EOL;

// HTML is escape in the loop above.
echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
