<?php

/**
 * Functions for used by the shortcode callbacks.
 *
 * @package     Connections
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template tag to call the entry list. All options can be passed as an
 * associative array. The options are identical to those available to the
 * shortcode.
 *
 * EXAMPLE:   connectionsEntryList( array('id' => 325) );
 *
 * @access public
 * @since unknown
 * @version 1.0
 * @param array $atts
 * @return string
 */
function connectionsEntryList($atts) {
	echo cnShortcode_Connections::shortcode($atts);
}

/**
 * Template tag to call the upcoming list. All options can be passed as an
 * associative array. The options are identical to those available to the
 * shortcode.
 *
 * EXAMPLE:   connectionsUpcomingList(array('days' => 30));
 *
 * @param array $atts
 * @return string
 */
function connectionsUpcomingList( $atts ) {
	echo _upcoming_list( $atts );
}

/**
 * Display the upcoming list.
 *
 * @access public
 * @since  unknown
 *
 * @param array  $atts
 * @param string $content [optional]
 * @param string $tag     [optional] When called as the callback for add_shortcode, the shortcode tag is passed
 *                        automatically. Manually setting the shortcode tag so the function can be called
 *                        independently.
 *
 * @return string
 */
function _upcoming_list( $atts, $content = NULL, $tag = 'upcoming_list' ) {
    global $connections, $wpdb;

	// $template =& $connections->template;
	$out = '';
	$alternate = '';

	$atts = shortcode_atts( array(
			'list_type'        => 'birthday',
			'days'             => '30',
			'include_today'    => TRUE,
			'private_override' => FALSE,
			'date_format'      => 'F jS',
			'show_lastname'    => FALSE,
			'show_title'       => TRUE,
			'list_title'       => NULL,
			'template'         => NULL
		),
		$atts,
		$tag
	);

	/*
	 * Convert some of the $atts values in the array to boolean.
	 */
	cnFormatting::toBoolean( $atts['include_today'] );
	cnFormatting::toBoolean( $atts['private_override'] );
	cnFormatting::toBoolean( $atts['show_lastname'] );
	cnFormatting::toBoolean( $atts['repeat_alphaindex'] );
	cnFormatting::toBoolean( $atts['show_title'] );

	/*
	 * If a list type was specified in the shortcode, load the template based on that type.
	 * However, if a specific template was specified, that should preempt the template to be loaded based on the list type if it was specified..
	 */
	if ( ! empty( $atts['template'] ) ) {
		$template = cnTemplateFactory::getTemplate( $atts['template'] );
	} else {
		$templateSlug = $connections->options->getActiveTemplate( $atts['list_type'] );
		$template = cnTemplateFactory::getTemplate( $templateSlug );
	}

	// No template found return error message.
	if ( $template == FALSE )
		return '<p style="color:red; font-weight:bold; text-align:center;">' . sprintf( __( 'ERROR: Template %1$s not found.', 'connections' ), $atts['template'] ) . '</p>';

	do_action( 'cn_template_include_once-' . $template->getSlug() );
	do_action( 'cn_template_enqueue_js-' . $template->getSlug() );

	/*
	 * Set the query vars and run query.
	 */

	// Show only public or private [if permitted] entries.
	if ( is_user_logged_in() || $atts['private_override'] != FALSE ) {
		$visibilityfilter = " AND (visibility='private' OR visibility='public') AND (" . $atts['list_type'] . " != '')";
	} else {
		$visibilityfilter = " AND (visibility='public') AND (`" . $atts['list_type'] . "` != '')";
	}

	// Get the current date from WP which should have the current time zone offset.
	$wpCurrentDate = date( 'Y-m-d', $connections->options->wpCurrentTime );

	// Whether or not to include the event occurring today or not.
	( $atts['include_today'] ) ? $includeToday = '<=' : $includeToday = '<';

	$newSQL = "SELECT * FROM ".CN_ENTRY_TABLE." WHERE"
		. "  (YEAR(DATE_ADD('$wpCurrentDate', INTERVAL ".$atts['days']." DAY))"
        . " - YEAR(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND)) )"
        . " - ( MID(DATE_ADD('$wpCurrentDate', INTERVAL ".$atts['days']." DAY),5,6)"
        . " < MID(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND),5,6) )"
        . " > ( YEAR('$wpCurrentDate')"
        . " - YEAR(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND)) )"
        . " - ( MID('$wpCurrentDate',5,6)"
        . " ".$includeToday." MID(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND),5,6) )"
		. $visibilityfilter;
	//$out .= print_r($newSQL , TRUE);

	$results = $wpdb->get_results( $newSQL );
	//$out .= print_r($results , TRUE);

	// If there are no results no need to proceed and output message.
	if ( empty( $results ) ) {

		$noResultMessage = __( 'No results.', 'connections' );
		$noResultMessage = apply_filters( 'cn_upcoming_no_result_message', $noResultMessage );
		$out .= '<p class="cn-upcoming-no-results">' . $noResultMessage . '</p>';

	} else {
		/*The SQL returns an array sorted by the birthday and/or anniversary date. However the year end wrap needs to be accounted for.
		Otherwise earlier months of the year show before the later months in the year. Example Jan before Dec. The desired output is to show
		Dec then Jan dates.  This function checks to see if the month is a month earlier than the current month. If it is the year is changed to the following year rather than the current.
		After a new list is built, it is resorted based on the date.*/
		foreach ( $results as $key => $row ) {

			if ( gmmktime(23, 59, 59, gmdate('m', $row->$atts['list_type']), gmdate('d', $row->$atts['list_type']), gmdate('Y', $connections->options->wpCurrentTime) ) < $connections->options->wpCurrentTime ) {
				$dateSort[] = $row->$atts['list_type'] = gmmktime(0, 0, 0, gmdate('m', $row->$atts['list_type']), gmdate('d', $row->$atts['list_type']), gmdate('Y', $connections->options->wpCurrentTime) + 1 );
			} else {
				$dateSort[] = $row->$atts['list_type'] = gmmktime(0, 0, 0, gmdate('m', $row->$atts['list_type']), gmdate('d', $row->$atts['list_type']), gmdate('Y', $connections->options->wpCurrentTime) );
			}
		}

		array_multisort( $dateSort, SORT_ASC, $results );

		if ( empty( $atts['list_title'] ) ) {

			switch ($atts['list_type']) {

				case 'birthday':
					if ( $atts['days'] >= 1 ) {
						$list_title = 'Upcoming Birthdays the next ' . $atts['days'] . ' days';
					} else {
						$list_title = 'Today\'s Birthdays';
					}
				break;

				case 'anniversary':
					if ( $atts['days'] >= 1 ) {
						$list_title = 'Upcoming Anniversaries the next ' . $atts['days'] . ' days';
					} else {
						$list_title = 'Today\'s Anniversaries';
					}
				break;

			}

		} else {
			$list_title = $atts['list_title'];
		}


		ob_start();

			// Prints the template's CSS file.
			do_action( 'cn_template_inline_css-' . $template->getSlug() , $atts );

			$out .= ob_get_contents();
		ob_end_clean();

		$out .= '<div class="connections-list cn-upcoming cn-' . $atts['list_type'] . '" id="cn-list" data-connections-version="' . $connections->options->getVersion() . '-' . $connections->options->getDBVersion() . '">' . "\n";

			$out .= "\n" . '<div class="cn-template cn-' . $template->getSlug() . '" id="cn-' . $template->getSlug() . '">' . "\n";

				$out .= "\n" . '<div class="cn-clear" id="cn-list-head">' . "\n";
					if ( $atts['show_title'] ) $out .= '<div class="cn-upcoming-title">' . $list_title  . '</div>';
				$out .= "\n" . '</div>' . "\n";

				$out .= '<div class="cn-clear" id="cn-list-body">' . "\n";

					foreach ( $results as $row ) {

						$entry = new cnvCard($row);
						$vCard =& $entry;

						$entry->name = '';

						$alternate == '' ? $alternate = '-alternate' : $alternate = '';

						/*
						 * Whether or not to show the last name.
						 * Setting $entry->name is for compatibility to versions prior to 0.7.1.6
						 */
						( ! $atts['show_lastname'] ) ? $entry->name = $entry->getFirstName() : $entry->name = $entry->getFullFirstLastName();
						if ( ! $atts['show_lastname'] ) $entry->setLastName('');

						$out .= '<div class="cn-upcoming-row' . $alternate . ' vcard ' . '">' . "\n";
							ob_start();
							do_action( 'cn_action_card-' . $template->getSlug(), $entry, $template, $atts );
						    $out .= ob_get_contents();
						    ob_end_clean();
						$out .= '</div>' . "\n";

					}

				$out .= "\n" . '</div>' . "\n";

				$out .= "\n" . '<div class="cn-clear" id="cn-list-foot">' . "\n";
				$out .= "\n" . '</div>' . "\n";

			$out .= "\n" . '</div>' . "\n";

		$out .= "\n" . '</div>' . "\n";

	}

	if ( cnSettingsAPI::get( 'connections', 'connections_compatibility', 'strip_rnt' ) ) {
		$search = array( "\r\n", "\r", "\n", "\t" );
		$replace = array( '', '', '', '' );
		$out = str_replace( $search , $replace , $out );
	}

	return $out;
}

function _connections_vcard( $atts , $content = NULL, $tag ) {

	$atts = shortcode_atts( array(
			'id' => NULL
		),
		$atts,
		$tag
	);

	if ( empty( $atts['id'] ) || ! is_numeric( $atts['id'] ) || empty( $content ) ) return '';

	$qTipContent = '<span class="cn-qtip-content-vcard" style="display: none">' . cnShortcode_Connections::shortcode( array( 'id' => $atts['id'] , 'template' => 'qtip-vcard' ) ) . '</span>';

	return '<span class="cn-qtip-vcard">' . $content . $qTipContent . '</span>';
}

function _connections_qtip( $atts , $content = NULL, $tag )
{
	$atts = shortcode_atts( array(
			'id' => NULL
		),
		$atts,
		$tag
	);

	if ( empty( $atts['id'] ) || ! is_numeric ($atts['id'] ) || empty( $content ) ) return '';

	$qTipContent = '<span class="cn-qtip-content-card" style="display: none">' . cnShortcode_Connections::shortcode( array( 'id' => $atts['id'] , 'template' => 'qtip-card' ) ) . '</span>';

	return '<span class="cn-qtip-card">' . $content . $qTipContent . '</span>';
}
