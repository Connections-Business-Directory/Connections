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
 * @since  unknown
 *
 * @param array $atts
 */
function connectionsEntryList($atts) {
	echo cnShortcode_Connections::shortcode( $atts );
}

/**
 * Template tag to call the upcoming list. All options can be passed as an
 * associative array. The options are identical to those available to the
 * shortcode.
 *
 * EXAMPLE:   connectionsUpcomingList(array('days' => 30));
 *
 * @param array $atts
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
function _upcoming_list( $atts, $content = null, $tag = 'upcoming_list' ) {

	/**
	 * @var ConnectionsLoad $connections
	 */
	global $connections;

	// $template =& $connections->template;
	$out = '';
	$alternate = '';

	$templateTypeDefaults = array(
		'list_type' => 'birthday',
		'template'  => null,
	);

	$templateType = cnSanitize::args( $atts, $templateTypeDefaults );

	/*
	 * If a list type was specified in the shortcode, load the template based on that type.
	 * However, if a specific template was specified, that should preempt the template to be loaded based on the list type if it was specified..
	 */
	if ( ! empty( $atts['template'] ) ) {
		$template = cnTemplateFactory::getTemplate( $templateType['template'] );
	} else {
		$templateSlug = $connections->options->getActiveTemplate( $templateType['list_type'] );
		$template = cnTemplateFactory::getTemplate( $templateSlug );
	}

	// No template found return error message.
	if ( $template === false ) return cnTemplatePart::loadTemplateError( $templateType );

	$defaults = array(
		'list_type'        => 'birthday',
		'days'             => '30',
		'include_today'    => true,
		'private_override' => false,
		'name_format'      => '',
		'date_format'      => 'F jS',
		'year_type'        => 'upcoming',
		'year_format'      => '%y ' . __( 'Year(s)', 'connections' ),
		'show_lastname'    => false,
		'show_title'       => true,
		'list_title'       => '',
		'no_results'       => apply_filters( 'cn_upcoming_no_result_message', __( 'No results.', 'connections' ) ),
		'template'         => null,
		'content'          => '',
		'force_home'       => true,
		'home_id'          => cnSettingsAPI::get( 'connections', 'home_page', 'page_id' ),
	);

	$defaults = apply_filters( 'cn_list_atts_permitted', $defaults );
	$defaults = apply_filters( 'cn_list_atts_permitted-' . $template->getSlug(), $defaults );

	$atts = shortcode_atts( $defaults, $atts, $tag );

	$atts = apply_filters( 'cn_list_atts', $atts );
	$atts = apply_filters( 'cn_list_atts-' . $template->getSlug(), $atts );

	/*
	 * Convert some of the $atts values in the array to boolean.
	 */
	cnFormatting::toBoolean( $atts['include_today'] );
	cnFormatting::toBoolean( $atts['private_override'] );
	cnFormatting::toBoolean( $atts['show_lastname'] );
	cnFormatting::toBoolean( $atts['repeat_alphaindex'] );
	cnFormatting::toBoolean( $atts['show_title'] );

	if ( 0 == strlen( $atts['name_format'] ) ) {

		$atts['name_format'] = $atts['show_lastname'] ? '%first% %last%' : '%first%';
	}

	/** @var cnTemplate $template */
	do_action( 'cn_register_legacy_template_parts' );
	do_action( 'cn_action_include_once-' . $template->getSlug() );
	do_action( 'cn_action_js-' . $template->getSlug() );

	/*
	 * This filter adds the current template paths to cnLocate so when template
	 * part file overrides are being searched for, it'll also search in template
	 * specific paths. This filter is then removed at the end of the shortcode.
	 */
	add_filter( 'cn_locate_file_paths', array( $template, 'templatePaths' ) );
	cnShortcode::addFilterRegistry( 'cn_locate_file_paths' );

	do_action( 'cn_template_include_once-' . $template->getSlug() );
	do_action( 'cn_template_enqueue_js-' . $template->getSlug() );

	$results = Connections_Directory()->retrieve->upcoming(
		array(
			'type'                  => $atts['list_type'],
			'days'                  => $atts['days'],
			'today'                 => $atts['include_today'],
			'visibility'            => array(),
			'private_override'      => $atts['private_override'],
			'return'                => 'data', // Valid options are `data` which are the results returned from self::entries() or `id` which are the entry ID/s.
		)
	);

	// If there are no results no need to proceed and output message.
	if ( empty( $results ) ) {

		if ( 0 < strlen( $atts['no_results'] ) ) {

			$out .= '<p class="cn-upcoming-no-results">' . $atts['no_results'] . '</p>';

		} else {

			$out .= '&nbsp;'; // Need to return something for Gutenberg support. Otherwise the loading spinner never stops.
		}

	} else {

		if ( empty( $atts['list_title'] ) ) {

			switch ( $atts['list_type'] ) {

				case 'birthday':
					if ( $atts['days'] >= 1 ) {
						$list_title = 'Upcoming Birthdays the next ' . $atts['days'] . ' days.';
					} else {
						$list_title = 'Today\'s Birthdays';
					}
					break;

				case 'anniversary':
					if ( $atts['days'] >= 1 ) {
						$list_title = 'Upcoming Anniversaries the next ' . $atts['days'] . ' days.';
					} else {
						$list_title = 'Today\'s Anniversaries';
					}
					break;

				default:
					if ( $atts['days'] >= 1 ) {
						$list_title = "Upcoming {$atts['list_type']} the next {$atts['days']} days.";
					} else {
						$list_title = "Today's {$atts['list_type']}";
					}
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

						$entry = new cnEntry_vCard( $row );
						$vCard =& $entry;

						// Configure the page where the entry link to.
						$entry->directoryHome( array( 'page_id' => $atts['home_id'], 'force_home' => $atts['force_home'] ) );

						if ( ! $atts['show_lastname'] ) {

							$entry->setLastName( '' );
						}

						$entry->name = $entry->getName(
							array(
								'format' => $atts['name_format']
							)
						);

						$alternate == '' ? $alternate = '-alternate' : $alternate = '';

						$out .= '<div class="cn-upcoming-row' . $alternate . ' vcard ' . '">' . "\n";
							ob_start();
							do_action( 'cn_action_card-' . $template->getSlug(), $entry, $template, $atts );
						    $out .= ob_get_contents();
						    ob_end_clean();
						$out .= '</div>' . "\n";

					}

				$out .= "\n" . '</div>' . "\n";

				$out .= "\n" . '<div class="cn-list-foot">' . "\n";
				$out .= "\n" . '</div>' . "\n";

			$out .= "\n" . '</div>' . "\n";

		$out .= "\n" . '</div>' . "\n";

	}

	if ( cnSettingsAPI::get( 'connections', 'connections_compatibility', 'strip_rnt' ) ) {
		$search = array( "\r\n", "\r", "\n", "\t" );
		$replace = array( '', '', '', '' );
		$out = str_replace( $search , $replace , $out );
	}

	// Clear any filters that have been added.
	// This allows support using the shortcode multiple times on the same page.
	cnShortcode::clearFilterRegistry();

	return $out;
}

function _connections_vcard( $atts , $content = null, $tag = 'connections_vcard' ) {

	$atts = shortcode_atts(
		array(
			'id' => null,
		),
		$atts,
		$tag
	);

	if ( empty( $atts['id'] ) || ! is_numeric( $atts['id'] ) || empty( $content ) ) return '';

	$qTipContent = '<span class="cn-qtip-content-vcard" style="display: none">' . cnShortcode_Connections::shortcode( array( 'id' => $atts['id'] , 'template' => 'qtip-vcard' ) ) . '</span>';

	return '<span class="cn-qtip-vcard">' . $content . $qTipContent . '</span>';
}

function _connections_qtip( $atts , $content = null, $tag = 'connections_qtip' )
{
	$atts = shortcode_atts(
		array(
			'id' => null,
		),
		$atts,
		$tag
	);

	if ( empty( $atts['id'] ) || ! is_numeric( $atts['id'] ) || empty( $content ) ) return '';

	$qTipContent = '<span class="cn-qtip-content-card" style="display: none">' . cnShortcode_Connections::shortcode( array( 'id' => $atts['id'] , 'template' => 'qtip-card' ) ) . '</span>';

	return '<span class="cn-qtip-card">' . $content . $qTipContent . '</span>';
}
