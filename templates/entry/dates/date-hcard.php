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

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_string;

$rows   = array();
$search = array( '%label%', '%date%', '%separator%' );

foreach ( $dates as $date ) {

	$replace = array();

	if ( false === $date->date ) {

		continue;
	}

	$classNames = array(
		'vevent',
		'cn-date',
	);

	if ( $date->preferred ) {

		$classNames[] = 'cn-preferred';
		$classNames[] = 'cn-date-preferred';
	}

	$row = '<span class="' . _escape::classNames( $classNames ) . '">';

	// Hidden elements are to maintain hCalendar spec compatibility.
	$replace[] = empty( $date->name ) ? '' : '<span class="date-name">' . esc_html( $date->name ) . '</span>';
	$replace[] = empty( $date->date ) ? '' : '<abbr class="dtstart" title="' . esc_attr( $date->date->format( 'Ymd' ) ) . '">' . date_i18n( $atts['date_format'], $date->date->getTimestamp(), false ) . '</abbr>
                                              <span class="summary" style="display:none">' . esc_html( $date->name ) . ' - ' . $entry->getName( array( 'format' => $atts['name_format'] ) ) . '</span>
                                              <span class="uid" style="display:none">' . esc_html( $date->date->format( 'YmdHis' ) ) . '</span>';
	$replace[] = '<span class="cn-separator">' . esc_html( $atts['separator'] ) . '</span>';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %date%' : $defaults['format'] ) : $atts['format']
	);

	$row .= '</span>';

	$rows[] = apply_filters( 'cn_output_date', _string::normalize( $row ), $date, $entry, $atts );
}

$block = '<span class="date-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL . '</span>';
$block = apply_filters( 'cn_output_dates', $block, $dates, $entry, $atts );

// HTML is escape in the loop above.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
