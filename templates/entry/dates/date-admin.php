<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @var cnCollection $dates
 * @var cnDate       $date
 */

$date_format = cnSettingsAPI::get( 'connections', 'display_general', 'date_format' );
$search      = array( '%label%', '%date%', '%separator%' );
$out         = '<span class="date-block">' . PHP_EOL;

foreach ( $dates as $date ) {

	if ( false === $date->date ) {

		continue;
	}

	$replace = array();

	$out .= "\t" . '<span class="cn-date' . ( $date->preferred ? ' cn-preferred cn-date-preferred' : '' ) . '">';

	$replace[] = ( empty( $date->name ) ) ? '' : '<span class="date-name">' . $date->name . '</span>';
	$replace[] = ( empty( $date->date ) ) ? '' : '<span>' . date_i18n( $date_format, $date->date->getTimestamp() , false ) . '</span>';
	$replace[] = '<span class="cn-separator">:</span>';

	$out .= str_ireplace(
		$search,
		$replace,
		'%label%%separator% %date%'
	);

	$out .= '</span>' . PHP_EOL;
}

$out .= '</span>' . PHP_EOL;

$out = cnString::replaceWhatWith( $out, ' ' );

echo $out;
