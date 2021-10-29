<?php
/**
 * This is basically a copy/paste of the code which used to reside in cnOutput::getDateBlock().
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $dates
 * @var cnEntry_Date $date
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

$rows   = array();
$search = array( '%label%', '%date%', '%separator%' );

foreach ( $dates as $date ) {

	$replace = array();

	if ( false === $date->date ) {

		continue;
	}

	$row = "\t" . '<span class="vevent cn-date' . ( $date->preferred ? ' cn-preferred cn-date-preferred' : '' ) . '">';

	// Hidden elements are to maintain hCalendar spec compatibility.
	$replace[] = empty( $date->name ) ? '' : '<span class="date-name">' . $date->name . '</span>';
	$replace[] = empty( $date->date ) ? '' : '<abbr class="dtstart" title="' . $date->date->format( 'Ymd' ) . '">' . date_i18n( $atts['date_format'], $date->date->getTimestamp(), false ) . '</abbr>
                                              <span class="summary" style="display:none">' . $date->name . ' - ' . $entry->getName( array( 'format' => $atts['name_format'] ) ) . '</span>
                                              <span class="uid" style="display:none">' . $date->date->format( 'YmdHis' ) . '</span>';
	$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %date%' : $defaults['format'] ) : $atts['format']
	);

	$row .= '</span>' . PHP_EOL;

	$rows[] = apply_filters( 'cn_output_date', cnString::replaceWhatWith( $row, ' ' ), $date, $entry, $atts );

}

$block = '<span class="date-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL . '</span>';
$block = apply_filters( 'cn_output_dates', $block, $dates, $entry, $atts );

echo $block;
