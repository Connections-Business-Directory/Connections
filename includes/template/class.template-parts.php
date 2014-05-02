<?php

/**
 * Static class for displaying template parts.
 *
 * @package     Connections
 * @subpackage  Template Parts
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnTemplatePart {

	/**
	 * Register the default template actions.
	 *
	 * @access private
	 * @since 0.7.6.5
	 * @uses add_action()
	 * @return (void)
	 */
	public static function init() {

		add_action( 'cn_list_actions', array( __CLASS__, 'listActions' ) );
		add_action( 'cn_entry_actions', array( __CLASS__, 'entryActions' ), 10, 2 );

		add_action( 'cn_list_action-view_all', array( __CLASS__, 'listAction_ViewAll') );

		add_action( 'cn_entry_action-back', array( __CLASS__, 'entryAction_Back'), 10, 2 );
		add_action( 'cn_entry_action-vcard', array( __CLASS__, 'entryAction_vCard'), 10, 2 );

		add_action( 'cn_list_no_results', array( __CLASS__, 'noResults' ), 10, 2 );

		add_action( 'cn_action_list_before', array( __CLASS__, 'categoryDescription'), 10, 2 );
		add_action( 'cn_action_list_before', array( __CLASS__, 'searchingMessage' ), 11, 3 );

		add_action( 'cn_list_character_index', array( __CLASS__, 'index' ) );
		add_action( 'cn_list_return_to_target', array( __CLASS__, 'returnToTopTarget' ) );

		// add_action( 'cn_action_entry_after', array( __CLASS__, 'JSON' ), 10, 2 );
	}

	/**
	 * Display the template no found error message.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @uses   shortcode_atts()
	 * @param  array  $atts The shortcode $atts array.
	 * @return string       The error message.
	 */
	public static function loadTemplateError( $atts ) {

		$defaults = array(
			'template'      => NULL,
		);

		$atts = shortcode_atts( $defaults, $atts );

		return '<p style="color:red; font-weight:bold; text-align:center;">' . sprintf( __( 'ERROR: Template %1$s not found.', 'connections' ), $atts['template'] ) . '</p>';
	}

	/**
	 * Output the return to top div.
	 *
	 * @access public
	 * @since  0.7.6.5
	 * @uses   wp_parse_args()
	 * @uses   apply_filters()
	 * @param  array  $atts [optional]
	 *
	 * @return string
	 */
	public static function returnToTopTarget( $atts = array() ) {

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = apply_filters( 'cn_filter_return_to_top_target', '<div id="cn-top" style="position: absolute; top: 0; right: 0;"></div>' );

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Renders a Connections compatible form opening.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @global $wp_rewrite
	 * @uses   get_permalink()
	 * @uses   is_front_page()
	 * @uses   is_page()
	 *
	 * @return string
	 */
	public static function formOpen( $atts = array() ) {
		global $wp_rewrite;

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '';

		// Get the directory home page ID.
		$homeID = $atts['force_home'] ? cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) : $atts['home_id'];

		$addAction = cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) != $atts['home_id'] ? TRUE : FALSE ;

		// The base post permalink is required, do not filter the permalink thru cnSEO.
		cnSEO::doFilterPermalink( FALSE );

		$permalink = get_permalink( $homeID );

		if ( $wp_rewrite->using_permalinks() ) {

			$out .= '<form class="cn-form" id="cn-cat-select" action="' . ( $addAction || $atts['force_home'] ? $permalink : '' ) . '" method="get">';
			if ( is_front_page() ) $out .= '<input type="hidden" name="page_id" value="' . $homeID .'">';

		} else {

			$out .= '<form class="cn-form" id="cn-cat-select" method="get">';
			$out .= '<input type="hidden" name="' . ( is_page() ? 'page_id' : 'p' ) . '" value="' . $homeID .'">';
		}

		// Add the cnSEO permailink filter.
		cnSEO::doFilterPermalink();

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Renders a form closing tag, nothing more.
	 * Just a simple helper function to compliment cnTemplatePart::formOpen().
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @return string
	 */
	public static function formClose( $atts = array() ) {

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '';

		$out .= '</form>';

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The result list head.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function header( $atts, $results, $template ) {

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '';

		$out .= PHP_EOL . '<div class="cn-list-head cn-clear" id="cn-list-head">' . PHP_EOL;

			// Display the Results List Actions.
			if ( ! get_query_var( 'cn-entry-slug' ) ) {

				// List actions template part.
				ob_start();
					do_action( 'cn_list_actions-before', $atts );
					do_action( 'cn_list_actions', $atts );
					$out .= ob_get_contents();
				ob_end_clean();

			}

			ob_start();
				do_action( 'cn_action_list_before', $atts, $results );
				do_action( 'cn_action_list_before-' . $template->getSlug(), $atts, $results );
				cnShortcode::addFilterRegistry( 'cn_action_list_before-' . $template->getSlug() );

				do_action( 'cn_action_list_both', $atts, $results );
				do_action( 'cn_action_list_both-' . $template->getSlug(), $atts, $results );
				cnShortcode::addFilterRegistry( 'cn_action_list_both-' . $template->getSlug() );

				$out .= ob_get_contents();
			ob_end_clean();

			//  This action only is required when the index is to be displayed.
			if ( $atts['show_alphaindex'] && ! $atts['repeat_alphaindex'] ) {

				// The character index template part.
				ob_start();
					do_action( 'cn_list_character_index', $atts );
				$out .= ob_get_clean();
			}

		$out .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list-head -->' : '' ) . PHP_EOL;

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The result list body.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function body( $atts, $results, $template ) {

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '';

		$out .= PHP_EOL . '<div class="connections-list cn-list-body cn-clear" id="cn-list-body">' . PHP_EOL;

		ob_start();

		// If there are no results no need to proceed and output message.
		if ( empty( $results ) ) {

			// The no results message.
			do_action( 'cn_list_no_results', $atts, $results, $template );

		} else {

			self::cards( $atts, $results, $template );
		}

		$out .= ob_get_clean();

		$out .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list-body -->' : '' ) . PHP_EOL;

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The result list cards.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function cards( $atts, $results, $template ) {

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '';

		$previousLetter = '';
		$alternate      = '';

		/*
		 * When an entry is assigned multiple categories and the RANDOM order_by shortcode attribute
		 * is used, this will cause the entry to show once for every category it is assigned.
		 *
		 * The same issue occurs when an entry has been assigned multiple address and each address
		 * falls within the geo bounds when performing a geo-limiting query.
		 */
		// $skipEntry = array();

		foreach ( $results as $row ) {

			$entry = new cnvCard( $row );
			$vCard =& $entry;

			// Configure the page where the entry link to.
			$entry->directoryHome( array( 'page_id' => $atts['home_id'], 'force_home' => $atts['force_home'] ) );

			// @TODO --> Fix this somehow in the query, see comment above for $skipEntry.
			// if ( in_array( $entry->getId(), $skipEntry ) ) continue;
			// $skipEntry[] = $entry->getId();

			$currentLetter = strtoupper( mb_substr( $entry->getSortColumn(), 0, 1 ) );

			if ( $currentLetter != $previousLetter ) {

				$out .= sprintf( '<div class="cn-list-section-head" id="cn-char-%1$s">', $currentLetter );

					//  This action only is required when the index is to be displayed.
					if ( $atts['show_alphaindex'] && $atts['repeat_alphaindex'] ) {

						// The character index template part.
						ob_start();

							do_action( 'cn_list_character_index', $atts );
						$out .= ob_get_clean();
					}

					if ( $atts['show_alphahead'] ) $out .= sprintf( '<h4 class="cn-alphahead">%1$s</h4>', $currentLetter );

				$out .= '</div>' . ( WP_DEBUG ? '<!-- END #cn-char-' . $currentLetter . ' -->' : '' );

				$previousLetter = $currentLetter;
			}

			// Before entry actions.
			ob_start();

				// Display the Entry Actions.
				if ( get_query_var( 'cn-entry-slug' ) ) {

					do_action( 'cn_entry_actions-before', $atts, $entry );
					do_action( 'cn_entry_actions', $atts, $entry );
				}

				do_action( 'cn_action_entry_before', $atts, $entry );
				do_action( 'cn_action_entry_before-' . $template->getSlug(), $atts, $entry );
				cnShortcode::addFilterRegistry( 'cn_action_entry_before-' . $template->getSlug() );

				do_action( 'cn_action_entry_both', $atts, $entry  );
				do_action( 'cn_action_entry_both-' . $template->getSlug(), $atts, $entry );
				cnShortcode::addFilterRegistry( 'cn_action_entry_both-' . $template->getSlug() );

			$out .= ob_get_clean();

			$out .= sprintf( '<div class="cn-list-row%1$s vcard %2$s %3$s" id="%4$s" data-entry-type="%2$s" data-entry-id="%5$d" data-entry-slug="%4$s">',
					$alternate = $alternate == '' ? '-alternate' : '',
					$entry->getEntryType(),
					$entry->getCategoryClass( TRUE ),
					$entry->getSlug(),
					$entry->getId()
				);

				ob_start();

					do_action( 'cn_template-' . $template->getSlug(), $entry, $template, $atts );

				$out .= ob_get_clean();

			$out .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #' . $entry->getSlug() . ' -->' : '' ) . PHP_EOL;

			// After entry actions.
			ob_start();

				do_action( 'cn_action_entry_both', $atts, $entry  );
				do_action( 'cn_action_entry_both-' . $template->getSlug(), $atts ,$entry );
				cnShortcode::addFilterRegistry( 'cn_action_entry_both-' . $template->getSlug() );

				do_action( 'cn_action_entry_after', $atts, $entry );
				do_action( 'cn_action_entry_after-' . $template->getSlug(), $atts, $entry );
				cnShortcode::addFilterRegistry( 'cn_action_entry_after-' . $template->getSlug() );

				// Display the Entry Actions.
				if ( get_query_var( 'cn-entry-slug' ) ) {

					do_action( 'cn_action_entry_actions-after', $atts, $entry );
				}

			$out .= ob_get_clean();

		}

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The result list foot.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function footer( $atts, $results, $template ) {

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '';

		$out .= PHP_EOL . '<div class="cn-clear" id="cn-list-foot">' . PHP_EOL;

			ob_start();
				do_action( 'cn_action_list_both' , $atts , $results  );
				do_action( 'cn_action_list_both-' . $template->getSlug() , $atts , $results );
				cnShortcode::addFilterRegistry( 'cn_action_list_both-' . $template->getSlug() );

				do_action( 'cn_action_list_after' , $atts , $results );
				do_action( 'cn_action_list_after-' . $template->getSlug() , $atts , $results );
				cnShortcode::addFilterRegistry( 'cn_action_list_after-' . $template->getSlug() );

				// Display the Results List Actions.
				if ( ! get_query_var( 'cn-entry-slug' ) ) {

					// List actions template part.
					do_action( 'cn_list_actions-after', $atts );
				}

				$out .= ob_get_contents();
			ob_end_clean();

		$out .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list-foot -->' : '' ) . PHP_EOL;

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Output the result list actions.
	 *
	 * @access public
	 * @since 0.7.6.5
	 * @param  (array)  $atts [optional]
	 * @uses wp_parse_args()
	 * @uses apply_filters()
	 * @return string | void
	 */
	public static function listActions( $atts = array() ) {
		$out = '';
		$actions = array();

		$defaults = array(
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'before'        => '',
			'before-item'   => '',
			'after-item'    => '',
			'after'         => '',
			'return'        => FALSE
		);

		$atts = wp_parse_args( $atts, apply_filters( 'cn_list_actions_atts', $defaults ) );

		$settings = cnSettingsAPI::get( 'connections', 'list_actions', 'actions' );

		if ( ! isset( $settings['active'] ) || empty( $settings['active'] ) ) return;

		foreach ( $settings['active'] as $key => $slug ) {

			if ( ! has_action( "cn_list_action-{$slug}" ) ) continue;

			ob_start();

			do_action( "cn_list_action-{$slug}", $atts );

			$action = ob_get_clean();

			if ( strlen( $action ) < 1 ) continue;

			$out .= sprintf( '%1$s<%2$s class="cn-list-action-item">%3$s</%2$s>%4$s',
				$atts['before-item'],
				$atts['item_tag'],
				$action,
				$atts['after-item']
			 );
		}

		$out = sprintf( '<%1$s id="cn-list-actions">%2$s</%1$s>',
				$atts['container_tag'],
				$out
			);

		if ( $atts['return'] ) return PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
		echo PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
	}

	/**
	 * Callback for the cn_list_action-view_all action which outputs the "View All" link
	 * in the list actions.
	 *
	 * @access private
	 * @since  0.8
	 * @param  array  $atts The $atts from self::listActions() passed by the action callback.
	 *
	 * @return void
	 */
	public static function listAction_ViewAll( $atts ) {

		// No need to display if the user is viewing the "View All" page.
		if ( get_query_var( 'cn-view' ) == 'all' ) return;

		// Output the "View All" link.
		cnURL::permalink( array( 'type' => 'all', 'text' => __( 'View All', 'connections' ), 'rel' => 'canonical', 'return' => FALSE ) );
	}

	/**
	 * Output the entry list actions.
	 *
	 * @access public
	 * @since 0.7.6.5
	 * @param (array)  $atts [optional]
	 * @param (object) $entry Instance of the cnEntry class.
	 * @uses wp_parse_args()
	 * @uses apply_filters()
	 * @return string | void
	 */
	public static function entryActions( $atts = array(), $entry ) {
		$out = '';
		$actions = array();

		$defaults = array(
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'before'        => '',
			'before-item'   => '',
			'after-item'    => '',
			'after'         => '',
			'return'        => FALSE
		);

		$atts = wp_parse_args( $atts, apply_filters( 'cn_entry_actions_atts', $defaults ) );

		$settings = cnSettingsAPI::get( 'connections', 'entry_actions', 'actions' );

		if ( ! isset( $settings['active'] ) || empty( $settings['active'] ) ) return;

		foreach ( $settings['active'] as $key => $slug ) {

			if ( ! has_action( "cn_entry_action-{$slug}" ) ) continue;

			ob_start();

			do_action( "cn_entry_action-{$slug}", $atts, $entry );

			$action = ob_get_clean();

			if ( strlen( $action ) < 1 ) continue;

			$out .= sprintf( '%1$s<%2$s class="cn-entry-action-item">%3$s</%2$s>%4$s',
				empty( $atts['before-item'] ) ? '' : $atts['before-item'],
				$atts['item_tag'],
				$action,
				empty( $atts['after-item'] ) ? '' : $atts['after-item']
			 );
		}

		$out = sprintf( '<%1$s id="cn-entry-actions">%2$s</%1$s>',
				$atts['container_tag'],
				$out
			);

		if ( $atts['return'] ) return PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
		echo PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
	}

	/**
	 * Callback for the cn_entry_action-back action which outputs the "Go back to directory." link.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts  The $atts from self::entryActions() passed by the action callback.
	 * @param  object $entry An instance of the cnEntry object; passed by the action callback.
	 * @return void
	 */
	public static function entryAction_Back( $atts, $entry ) {

		cnURL::permalink( array( 'type' => 'home', 'text' => __( 'Go back to directory.', 'connections' ), 'on_click' => 'history.back();return false;', 'return' => FALSE ) );
	}

	/**
	 * Callback for the cn_entry_action-vcard action which outputs the "Add to Address Book." link.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array  $atts  The $atts from self::entryActions() passed by the action callback.
	 * @param  object $entry An instance of the cnEntry object; passed by the action callback.
	 * @return void
	 */
	public static function entryAction_vCard( $atts, $entry ) {

		$entry->vcard( array( 'return' => FALSE ) );
	}

	/**
	 * Output the current category description.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  (array)  $atts [optional]
	 * @param  (array)  $results [optional]
	 * @uses get_query_var()
	 * @return (string) | (void)
	 */
	public static function categoryDescription( $atts = array(), $results = array() ) {
		global $connections;

		// Check whether or not the category description should be displayed or not.
		if ( ! cnSettingsAPI::get( 'connections', 'connections_display_results', 'cat_desc' ) ) return;

		$out = '';

		$defaults = array(
			'before'        => '',
			'after'         => '',
			'return'        => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'slug', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			$out = $category->getDescriptionBlock( array( 'return' => TRUE ) );
		}

		if ( get_query_var( 'cn-cat' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$categorySlug = explode( '/' , get_query_var( 'cn-cat' ) );

			if ( isset( $categorySlug[ count( $categorySlug ) - 1 ] ) ) $categorySlug = $categorySlug[ count( $categorySlug ) - 1 ];

			$term = $connections->term->getTermBy( 'id', $categorySlug, 'category' );

			$category = new cnCategory( $term );

			$out = $category->getDescriptionBlock( array( 'return' => TRUE ) );
		}

		if ( $atts['return'] ) return "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . "\n";
		echo "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . "\n";
	}

	/**
	 * Display a message box above the search results with information
	 * about the current query and the option (a button) to clear results.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array  $atts     The shortcode $atts array.
	 * @param  array  $results  The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function searchingMessage( $atts = array(), $results = array(), $template = FALSE ) {

		// Check whether or not the category description should be displayed or not.
		if ( ! cnSettingsAPI::get( 'connections', 'connections_display_results', 'search_message' ) ) return;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = array();

		// Get the directory home page ID.
		$homeID = $atts['force_home'] ? cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) : $atts['home_id'];

		$addAction = cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) != $atts['home_id'] ? TRUE : FALSE ;

		// The base post permalink is required, do not filter the permalink thru cnSEO.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( FALSE );

		$permalink = get_permalink( $homeID );

		// Re-enable the filter.
		if ( ! is_admin() ) cnSEO::doFilterPermalink();


		// Store the query vars
		$queryVars['cn-s']            = get_query_var('cn-s') ? esc_html( urldecode( get_query_var('cn-s') ) ) : FALSE;
		$queryVars['cn-char']         = get_query_var('cn-char') ? esc_html( urldecode( get_query_var('cn-char') ) ) : FALSE;
		$queryVars['cn-cat']          = get_query_var('cn-cat') ? get_query_var('cn-cat') : FALSE;
		$queryVars['cn-organization'] = get_query_var('cn-organization') ? esc_html( urldecode( get_query_var('cn-organization') ) ) : FALSE;
		$queryVars['cn-department']   = get_query_var('cn-department') ? esc_html( urldecode( get_query_var('cn-department') ) ) : FALSE;
		$queryVars['cn-locality']     = get_query_var('cn-locality') ? esc_html( urldecode( get_query_var('cn-locality') ) ) : FALSE;
		$queryVars['cn-region']       = get_query_var('cn-region') ? esc_html( urldecode( get_query_var('cn-region') ) ) : FALSE;
		$queryVars['cn-postal-code']  = get_query_var('cn-postal-code') ? esc_html( urldecode( get_query_var('cn-postal-code') ) ) :  FALSE;
		$queryVars['cn-country']      = get_query_var('cn-country') ? esc_html( urldecode( get_query_var('cn-country') ) ) : FALSE;
		// if ( get_query_var('cn-near-coord') ) $queryVars['cn-near-coord']     = get_query_var('cn-near-coord');
		// if ( get_query_var('cn-radius') ) $queryVars['cn-radius']             = get_query_var('cn-radius');
		// if ( get_query_var('cn-unit') ) $queryVars['cn-unit']                 = get_query_var('cn-unit');

		if ( $queryVars['cn-cat'] ) {

			$categoryID = $queryVars['cn-cat'];
			$terms      = array();

			// Since the `cn-cat` query var can be an array, we'll only add the category slug
			// template name when querying a single category.
			if ( is_array( $categoryID ) ) {

				foreach ( $categoryID as $id ) {

					$term    = $instance->term->getTermBy( 'id', $id, 'category' );
					$terms[] = esc_html( $term->name );
				}

			} else {

				$term    = $instance->term->getTermBy( 'id', $categoryID, 'category' );
				$terms[] = esc_html( $term->name );
			}

			$out[] = sprintf( __( 'You are searching within category(ies): %s', 'connections' ), implode( ', ', $terms ) );
		}

		if ( $queryVars['cn-s'] ) {

			// If value is a string, string the white space and covert to an array.
			if ( ! is_array( $queryVars['cn-s'] ) ) $queryVars['cn-s'] = explode( ' ' , trim( $queryVars['cn-s'] ) );

			// Trim any white space from around the terms in the array.
			array_walk( $queryVars['cn-s'] , 'trim' );

			$out[] = sprintf( __( 'You are searching for the keyword(s): %s', 'connections' ), implode( ', ', $queryVars['cn-s'] ) );
		}

		if ( $queryVars['cn-char'] ) {

			$out[] = sprintf( __( 'The results are being filtered by the character: %s', 'connections' ), $queryVars['cn-char'] );
		}

		if ( $queryVars['cn-organization'] ) {

			$out[] = sprintf( __( 'The results are being filtered by the organization: %s', 'connections' ), $queryVars['cn-organization'] );
		}

		if ( $queryVars['cn-department'] ) {

			$out[] = sprintf( __( 'The results are being filtered by the department: %s', 'connections' ), $queryVars['cn-department'] );
		}

		if ( $queryVars['cn-locality'] ) {

			$out[] = sprintf( __( 'The results are being filtered by the locality: %s', 'connections' ), $queryVars['cn-locality'] );
		}

		if ( $queryVars['cn-region'] ) {

			$out[] = sprintf( __( 'The results are being filtered by the region: %s', 'connections' ), $queryVars['cn-region'] );
		}

		if ( $queryVars['cn-postal-code'] ) {

			$out[] = sprintf( __( 'The results are being filtered by the postal code: %s', 'connections' ), $queryVars['cn-postal-code'] );
		}

		if ( $queryVars['cn-country'] ) {

			$out[] = sprintf( __( 'The results are being filtered by the country: %s', 'connections' ), $queryVars['cn-country'] );
		}

		// Convert the search messages in a HTML UL list.
		if ( ! empty( $out ) ) {

			$out = '<li class="cn-search-message">' . implode( '</li><li class="cn-search-message">', $out ) . '</li>';
			$out = '<ul id="cn-search-message-list">' . $out . '</ul>';

			$out .= sprintf( '<div id="cn-clear-search"><a class="button btn" id="cn-clear-search-button" href="%1$s">%2$s</a></div>',
				$permalink,
				__( 'Clear Search' )
			);

			$out = '<div id="cn-search-messages">' . $out . '</div>';

		} else {

			$out = '';
		}

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Outputs the "No Results" meesage.
	 *
	 * @access public
	 * @since  0.7.6.5
	 * @uses   wp_parse_args()
	 * @uses   apply_filters()
	 * @param  array  $atts    [optional] The shortcode $atts array.
	 * @param  array  $results [optional] The cnRetrieve query results.
	 * @param  object $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function noResults( $atts = array(), $results = array(), $template = FALSE ) {

		if ( ! empty( $results ) ) return;

		$out     = '';

		$defaults = array(
			'tag'     => 'p',
			'message' => __('No results.', 'connections'),
			'before'  => '',
			'after'   => '',
			'return'  => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$atts['message'] = apply_filters( 'cn_list_no_result_message' , $atts['message'] );
		if ( $template !== FALSE ) $atts['message'] = apply_filters( 'cn_list_no_result_message-' . $template->getSlug() , $atts['message'] );

		$out .= sprintf('<%1$s class="cn-list-no-results">%2$s</%1$s>',
				$atts['tag'],
				$atts['message']
			);

		if ( $atts['return'] ) return PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
		echo PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
	}

	/**
	 * Outputs entry data JSON encoded in HTML data attribute.
	 * This is an action called by the `cn_action_entry_after` hook.
	 *
	 * @access  public
	 * @since  0.8
	 * @uses   wp_parse_args()
	 * @param array  $atts  Shortcode $atts passed by the `cn_action_entry_after` action hook.
	 * @param object $entry An instance the the cnEntry object.
	 *
	 * return void
	 */
	public static function JSON( $atts, $entry ) {

		// Stores the entry data.
		$data = array();

		$defaults = array(
			'tag'                => 'div',
			'before'             => '',
			'after'              => '',
			'return'             => FALSE,
			'show_addresses'     => TRUE,
			'show_phone_numbers' => TRUE,
			'show_email'         => TRUE,
			'show_im'            => TRUE,
			'show_social_media'  => TRUE,
			'show_links'         => TRUE,
			'show_dates'         => TRUE,
			'show_bio'           => TRUE,
			'show_notes'         => TRUE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$data = array(
			'type'            => $entry->getEntryType(),
			'id'              => $entry->getId(),
			'ruid'            => $entry->getRuid(),
			'slug'            => $entry->getSlug(),
			'name'            => array(
				'full'   => $entry->getName( $atts ),
				'prefix' => $entry->getHonorificPrefix(),
				'first'  => $entry->getFirstName(),
				'middle' => $entry->getMiddleName(),
				'last'   => $entry->getLastName(),
				'suffix' => $entry->getHonorificSuffix(),
				),
			'title'           => $entry->getTitle(),
			'organization'    => $entry->getOrganization(),
			'department'      => $entry->getDepartment(),
			'contact_name'    => array(
				'full'   => $entry->getContactName(),
				'first'  => $entry->getContactFirstName(),
				'last'   => $entry->getContactLastName()
				),
			'family_name'     => $entry->getFamilyName(),
			'family_members'  => $entry->getFamilyMembers(),
			'categories'      => $entry->getCategory(),
			'meta'            => $entry->getMeta( $atts ),
			);

		if ( $atts['show_addresses'] ) $data['addresses'] = $entry->getAddresses( $atts );
		if ( $atts['show_phone_numbers'] ) $data['phone_numbers'] = $entry->getPhoneNumbers( $atts );
		if ( $atts['show_email'] ) $data['email_addresses'] = $entry->getEmailAddresses( $atts );
		if ( $atts['show_im'] ) $data['im'] = $entry->getIm( $atts );
		if ( $atts['show_social_media'] ) $data['social_media'] = $entry->getSocialMedia( $atts );
		if ( $atts['show_links'] ) $data['links'] = $entry->getLinks( $atts );
		if ( $atts['show_dates'] ) $data['dates'] = $entry->getDates( $atts );
		if ( $atts['show_bio'] ) $data['bio'] = $entry->getBio();
		if ( $atts['show_notes'] ) $data['notes'] = $entry->getNotes();

		$out =  sprintf('<%1$s class="cn-entry-data-json" data-entry-data-json=\'%2$s\'></%1$s>',
				$atts['tag'],
				htmlspecialchars( json_encode( $data ), ENT_QUOTES, 'UTF-8' )
			);

		if ( $atts['return'] ) return PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
		echo PHP_EOL . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;
	}

	/**
	 * The return to top anchor.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public static function returnToTop( $atts = array() ) {
		$styles = '';

		$defaults = array(
			'tag'    => 'span',
			'href'   => '#cn-top',
			'style'  => array(),
			'title'  => __('Return to top.', 'connections'),
			'text'   => '<img src="' . CN_URL . 'assets/images/uparrow.gif" alt="' . __('Return to top.', 'connections') . '"/>',
			'before' => '',
			'after'  => '',
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( is_array( $atts['style'] ) && ! empty( $atts['style'] ) ) {

			array_walk( $atts['style'], create_function( '&$i, $property', '$i = "$property: $i";' ) );
			$styles = implode( $atts['style'], '; ' );
		}

		$anchor = '<a href="' . $atts['href'] . '" title="' . $atts['title'] . '">' . $atts['text'] . '</a>';

		$out = '<' . $atts['tag'] . ' class="cn-return-to-top"' . ( $styles ? ' style="' . $styles . '"' : ''  ) . '>' . $anchor . '</' . $atts['tag'] . '>';

		if ( $atts['return'] ) return "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . "\n";
		echo "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . "\n";
	}

	/**
	 * The last updated messagefor an entry.
	 *
	 * @access public
	 * @since 0.7.6.5
	 * @uses wp_parse_args()
	 * @uses human_time_diff()
	 * @uses current_time()
	 * @param (array) $atts [optional]
	 * @return (string)
	 */
	public static function updated( $atts = array() ) {
		$out = '';
		$styles = '';

		$defaults = array(
			'timestamp'   => '',
			'tag'         => 'span',
			'style'       => array(),
			'before'      => '',
			'after'       => '',
			'return'      => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		// No need to continue if the timestamp was not supplied.
		if ( ! isset( $atts['timestamp'] ) || empty( $atts['timestamp'] ) ) {

			if ( $atts['return'] ) return $out;
			echo $out;
		}

		$age = (int) abs( time() - strtotime( $atts['timestamp'] ) );

		if ( $age < 657000 ) // less than one week: red
			$atts['style']['color'] = 'red';
		elseif ( $age < 1314000 ) // one-two weeks: maroon
			$atts['style']['color'] = 'maroon';
		elseif ( $age < 2628000 ) // two weeks to one month: green
			$atts['style']['color'] = 'green';
		elseif ( $age < 7884000 ) // one - three months: blue
			$atts['style']['color'] = 'blue';
		elseif ( $age < 15768000 ) // three to six months: navy
			$atts['style']['color'] = 'navy';
		elseif ( $age < 31536000 ) // six months to a year: black
			$atts['style']['color'] = 'black';
		else      // more than one year: don't show the update age
			$atts['style']['display'] = 'none';

		if ( is_array( $atts['style'] ) && ! empty( $atts['style'] ) ) {

			array_walk( $atts['style'], create_function( '&$i, $property', '$i = "$property: $i";' ) );
			$styles = implode( $atts['style'], '; ' );
		}

		$updated = sprintf( __( 'Updated %1$s ago.' ), human_time_diff( strtotime( $atts['timestamp'] ), current_time( 'timestamp' ) ) );

		$out = '<' . $atts['tag'] . ' class="cn-last-updated"' . ( $styles ? ' style="' . $styles . '"' : ''  ) . '>' . $updated . '</' . $atts['tag'] . '>';

		if ( $atts['return'] ) return "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . "\n";
		echo "\n" . ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . "\n";
	}

	/**
	 * Outputs the legacy character index. This is being deprecated in favor of cnTemplatePart::index().
	 * This was added for backward compatibility only for the legacy templates.
	 *
	 * @access public
	 * @since 0.7.6.5
	 * @deprecated since 0.7.6.5
	 * @uses wp_parse_args()
	 * @uses is_ssl()
	 * @uses add_query_arg()
	 * @param  (array) $atts [optional]
	 * @return (string)
	 */
	public static function characterIndex( $atts = array() ) {
		static $out = '';
		$links = array();
		$alphaindex = range( "A", "Z" );

		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		/*
		 * $out is a static variable so if is not empty, this method was already run,
		 * so there is no need to rebuild the chracter index.
		 */
		if ( ! empty( $out ) ) {
			if ( $atts['return'] ) return $out;
			echo $out;
			return;
		}

		// The URL in the address bar
		$requestedURL  = is_ssl() ? 'https://' : 'http://';
		$requestedURL .= $_SERVER['HTTP_HOST'];
		$requestedURL .= $_SERVER['REQUEST_URI'];

		$parsedURL   = @parse_url( $requestedURL );

		$redirectURL = explode( '?', $requestedURL );
		$redirectURL = $redirectURL[0];

		// Ensure array index is set, prevent PHP error notice.
		if( ! isset( $parsedURL['query'] ) ) $parsedURL['query'] = array();

		$parsedURL['query'] = preg_replace( '#^\??&*?#', '', $parsedURL['query'] );

		// Add back on to the URL any remaining query string values.
		if ( $redirectURL && ! empty( $parsedURL['query'] ) ) {
			parse_str( $parsedURL['query'], $_parsed_query );
			$_parsed_query = array_map( 'rawurlencode_deep',  $_parsed_query );
		}

		foreach ( $alphaindex as $letter ) {

			if ( empty( $parsedURL['query'] ) ) {
				$links[] = '<a href="#cn-char-' . $letter . '">' . $letter . '</a>';
			} else {
				$links[] = '<a href="' . add_query_arg( $_parsed_query, $redirectURL . '#cn-char-' . $letter ) . '">' . $letter . '</a>';
			}

		}

		$out = "\n" . '<div class="cn-alphaindex">' . implode( ' ', $links ). '</div>' . "\n";

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Create the search input.
	 *
	 * Accepted option for the $atts property are:
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @uses get_query_var()
	 * @param array $atts [optional]
	 * @return string
	 */
	public static function search( $atts = array() ) {
		$out = '';

		$defaults = array(
			'show_label' => TRUE,
			'return'     => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$searchValue = ( get_query_var('cn-s') ) ? get_query_var('cn-s') : '';

		// Check to see if there is a template file override.
		$part = cnLocate::file( cnLocate::fileNames( 'search' ) );

		// If one was found, lets include it. If not, run the core function.
		if ( $part ) {

			ob_start();
				include $part;
			$out .= ob_get_clean();

		} else {

			$out .= '<span class="cn-search">';
				if ( $atts['show_label'] ) $out .= '<label for="cn-s">Search Directory</label>';
				$out .= '<input type="text" id="cn-search-input" name="cn-s" value="' . esc_attr( $searchValue ) . '" placeholder="' . __('Search', 'connections') . '"/>';
				$out .= '<input type="submit" name="" id="cn-search-submit" class="cn-search-button" value="" tabindex="-1" />';
			$out .= '</span>';

		}

		// Output the the search input.
		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Outputs a submit button.
	 *
	 * Accepted option for the $atts property are:
	 * 	name (string) The input name attribute.
	 * 	value (string) The input value attribute.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public static function submit( $atts = array() ) {
		$out = '';

		$defaults = array(
			'name'   => '',
			'value'  => __('Submit', 'connections'),
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out .= '<input type="submit" name="' . $atts['name'] . '" id="cn-submit" class="button" value="' . $atts['value'] . '" tabindex="-1" />';

		// Output a submit button.
		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Creates the initisl character filter control.
	 *
	 * Accepted option for the $atts property are:
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access public
	 * @since 0.7.4
	 * @uses add_query_arg()
	 * @uses get_query_var()
	 * @uses wp_parse_args()
	 * @uses is_admin()
	 * @param  (array)  $atts [description]
	 * @return (string)
	 */
	public static function index( $atts = array() ) {
		$out     = '';
		$links   = array();
		$current = '';
		$styles  = '';

		$defaults = array(
			'status' => array( 'approved' ),
			'tag'    => 'div',
			'style'  => array(),
			'return' => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$characters = cnRetrieve::getCharacters( $atts );
		// $currentPageURL = add_query_arg( array( 'page' => FALSE , 'cn-action' => 'cn_filter' )  );

		// If in the admin init an instance of the cnFormObjects class to be used to create the URL nonce.
		if ( is_admin() ) $form = new cnFormObjects();

		// Current character
		if ( is_admin() ) {
			if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) ) $current = urldecode( $_GET['cn-char'] );
		} else {
			if ( get_query_var('cn-char') ) $current = urldecode( get_query_var('cn-char') );
		}

		if ( is_array( $atts['style'] ) && ! empty( $atts['style'] ) ) {

			array_walk( $atts['style'], create_function( '&$i, $property', '$i = "$property: $i";' ) );
			$styles = implode( $atts['style'], '; ' );
		}

		foreach ( $characters as $key => $char ) {
			$char = strtoupper( $char );

			// If we're in the admin, add the nonce to the URL to be verified when settings the current user filter.
			if ( is_admin() ) {

				$links[] = '<a' . ( $current == $char ? ' class="cn-char-current"' : ' class="cn-char"' ) . ' href="' . $form->tokenURL( add_query_arg( array( 'cn-char' => urlencode( $char ) ) /*, $currentPageURL*/ ) , 'filter' ) . '">' . $char . '</a> ';

			} else {

				$url = cnURL::permalink( array(
					'type'       => 'character',
					'slug'       => $char,
					'title'      => $char,
					'class'      => ( $current == $char ? 'cn-char-current' : 'cn-char' ),
					'text'       => $char,
					'home_id'    => in_the_loop() && is_page() ? get_the_id() : cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
					'force_home' => FALSE,
					'return'     => TRUE,
					)
				);

				// $links[] = '<a' . ( $current == $char ? ' class="cn-char-current"' : ' class="cn-char"' ) . ' href="' . add_query_arg( array( 'cn-char' => urlencode( $char ) ) /*, $currentPageURL*/ ) . '">' . $char . '</a> ';
				$links[] = $url;
			}

		}

		// $out = '<div class="">' . . '</div>';
		$out = "\n" . '<' . $atts['tag'] . ' class="cn-alphaindex"' . ( $styles ? ' style="' . $styles . '"' : ''  ) . '>' . implode( ' ', $links ) . '</' . $atts['tag'] . '>' . "\n";

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Retrieves the current character and outs a hidden form input.
	 *
	 * @access public
	 * @since 0.7.4
	 * @uses wp_parse_args()
	 * @uses is_admin()
	 * @uses get_query_var()
	 * @uses esc_attr()
	 * @param  (array)
	 * @return (string)
	 */
	public static function currentCharacter( $atts = array() ) {
		$out = '';
		$current = '';

		$defaults = array(
			'type'   => 'input',	// Resevered for future use. Will define the type of output to render. In this case a form input.
			'hidden' => TRUE,
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Current character
		if ( is_admin() ) {
			if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) ) $current = urldecode( $_GET['cn-char'] );
		} else {
			if ( get_query_var('cn-char') ) $current = urldecode( get_query_var('cn-char') );
		}

		// Only output if there is a current character set in the query string.
		if ( 0 < strlen( $current ) ) $out .= '<input class="cn-current-char-input" name="cn-char" title="' . __('Current Character', 'connections') . '" type="' . ( $atts['hidden'] ? 'hidden' : 'text' ) . '" size="1" value="' . esc_attr( $current ) . '">';

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Creates the pagination controls.
	 *
	 * Accepted option for the $atts property are:
	 * 	limit (int) The pagination page limit.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @uses wp_parse_args()
	 * @uses get_permalink()
	 * @uses get_query_var()
	 * @uses add_query_arg()
	 * @uses absint()
	 * @uses trailingslashit()
	 * @param array $atts [optional]
	 * @return string
	 */
	public static function pagination( $atts = array() ) {
		global $wp_rewrite, $post,  $connections;

		// The class.seo.file is only loaded in the frontend; do not attempt to remove the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink( FALSE );

		$out = '';

		$defaults = array(
			'limit'  => 20,
			'return' => FALSE
		);

		$atts = wp_parse_args($atts, $defaults);

		$pageCount = ceil( $connections->retrieve->resultCountNoLimit / $atts['limit'] );

		if ( $pageCount > 1 ) {

			$current   = 1;
			$disabled  = array();
			$url       = array();
			$page      = array();
			$queryVars = array();

			// Get page/post permalink.
			// Only slash it when using pretty permalinks.
			$permalink = $wp_rewrite->using_permalinks() ? trailingslashit( get_permalink() ) : get_permalink();

			// Get the settings for the base of each data type to be used in the URL.
			$base = get_option('connections_permalink');

			// Store the query vars
			if ( get_query_var('cn-s') ) $queryVars['cn-s']                       = urlencode( get_query_var('cn-s') );
			if ( get_query_var('cn-char') ) $queryVars['cn-char']                 = get_query_var('cn-char');
			if ( get_query_var('cn-cat') ) $queryVars['cn-cat']                   = get_query_var('cn-cat');
			if ( get_query_var('cn-organization') ) $queryVars['cn-organization'] = get_query_var('cn-organization');
			if ( get_query_var('cn-department') ) $queryVars['cn-department']     = get_query_var('cn-department');
			if ( get_query_var('cn-locality') ) $queryVars['cn-locality']         = get_query_var('cn-locality');
			if ( get_query_var('cn-region') ) $queryVars['cn-region']             = get_query_var('cn-region');
			if ( get_query_var('cn-postal-code') ) $queryVars['cn-postal-code']   = get_query_var('cn-postal-code');
			if ( get_query_var('cn-country') ) $queryVars['cn-country']           = get_query_var('cn-country');
			if ( get_query_var('cn-near-coord') ) $queryVars['cn-near-coord']     = get_query_var('cn-near-coord');
			if ( get_query_var('cn-radius') ) $queryVars['cn-radius']             = get_query_var('cn-radius');
			if ( get_query_var('cn-unit') ) $queryVars['cn-unit']                 = get_query_var('cn-unit');

			// Current page
			if ( get_query_var('cn-pg') ) $current = absint( get_query_var('cn-pg') );

			$page['first'] = 1;
			$page['previous'] = ( $current - 1 >= 1 ) ? $current - 1 : 1;
			$page['next'] = ( $current + 1 <= $pageCount ) ? $current + 1 : $pageCount;
			$page['last'] = $pageCount;

			// The class to apply to the disabled links.
			( $current > 1 ) ? $disabled['first'] = '' : $disabled['first'] = ' disabled';
			( $current - 1 >= 1 ) ? $disabled['previous'] = '' : $disabled['previous'] = ' disabled';
			( $current + 1 <= $pageCount ) ? $disabled['next'] = '' : $disabled['next'] = ' disabled';
			( $current < $pageCount ) ? $disabled['last'] = '' : $disabled['last'] = ' disabled';

			/*
			 * Create the page permalinks. If on a post or custom post type, use query vars.
			 */
			if ( is_page() && $wp_rewrite->using_permalinks() ) {

				// Add the category base and path if paging thru a category.
				if ( get_query_var('cn-cat-slug') ) $permalink = trailingslashit( $permalink . $base['category_base'] . '/' . get_query_var('cn-cat-slug') );

				// Add the organization base and path if paging thru a organization.
				if ( get_query_var('cn-organization') ) $permalink = trailingslashit( $permalink . $base['organization_base'] . '/' . get_query_var('cn-organization') );

				// Add the department base and path if paging thru a department.
				if ( get_query_var('cn-department') ) $permalink = trailingslashit( $permalink . $base['department_base'] . '/' . get_query_var('cn-department') );

				// Add the locality base and path if paging thru a locality.
				if ( get_query_var('cn-locality') ) $permalink = trailingslashit( $permalink . $base['locality_base'] . '/' . get_query_var('cn-locality') );

				// Add the region base and path if paging thru a region.
				if ( get_query_var('cn-region') ) $permalink = trailingslashit( $permalink . $base['region_base'] . '/' . get_query_var('cn-region') );

				// Add the postal code base and path if paging thru a postal code.
				if ( get_query_var('cn-postal-code') ) $permalink = trailingslashit( $permalink . $base['postal_code_base'] . '/' . get_query_var('cn-postal-code') );

				// Add the country base and path if paging thru a country.
				if ( get_query_var('cn-country') ) $permalink = trailingslashit( $permalink . $base['country_base'] . '/' . get_query_var('cn-country') );

				$url['first']    = add_query_arg( $queryVars , $permalink . 'pg/' . $page['first'] );
				$url['previous'] = add_query_arg( $queryVars , $permalink . 'pg/' . $page['previous'] );
				$url['next']     = add_query_arg( $queryVars , $permalink . 'pg/' . $page['next'] );
				$url['last']     = add_query_arg( $queryVars , $permalink . 'pg/' . $page['last'] );

			} else {

				// If on the front page, add the query var for the page ID.
				if ( is_front_page() ) $permalink = add_query_arg( 'page_id' , $post->ID );

				// Add back on the URL any other Connections query vars.
				$permalink = add_query_arg( $queryVars , $permalink );

				$url['first']    = add_query_arg( array( 'cn-pg' => $page['first'] ) , $permalink );
				$url['previous'] = add_query_arg( array( 'cn-pg' => $page['previous'] ) , $permalink );
				$url['next']     = add_query_arg( array( 'cn-pg' => $page['next'] ) , $permalink );
				$url['last']     = add_query_arg( array( 'cn-pg' => $page['last'] ) , $permalink );
			}

			// Build the html page nav.
			$out .= '<span class="cn-page-nav" id="cn-page-nav">';

			$out .= '<a href="' . esc_url( $url['first'] ) . '" title="' . __('First Page', 'connections') . '" class="cn-first-page' . $disabled['first'] . '">&laquo;</a> ';
			$out .= '<a href="' . esc_url( $url['previous'] ) . '" title="' . __('Previous Page', 'connections') . '" class="cn-prev-page' . $disabled['previous'] . '" rel="prev">&lsaquo;</a> ';

			$out .= '<span class="cn-paging-input"><input type="text" size="1" value="' . $current . '" name="cn-pg" title="' . __('Current Page', 'connections') . '" class="current-page"> ' . __('of', 'connections') . ' <span class="total-pages">' . $pageCount . '</span></span> ';

			$out .= '<a href="' . esc_url( $url['next'] ) . '" title="' . __('Next Page', 'connections') . '" class="cn-next-page' . $disabled['next'] . '" rel="next">&rsaquo;</a> ';
			$out .= '<a href="' . esc_url( $url['last'] ) . '" title="' . __('Last Page', 'connections') . '" class="cn-last-page' . $disabled['last'] . '">&raquo;</a>';

			$out .= '</span>';
		}

		// The class.seo.file is only loaded in the frontend; do not attempt to add the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) cnSEO::doFilterPermalink();
		// Output the page nav.
		if ( $atts['return']) return $out;
		echo $out;
	}

	/**
	 * Parent public function that outputs the various categories output formats.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string) The ouput type of the categories. Valid options options are: select || multiselect || radio || checkbox
	 * 	group (bool) Whether or not to create option groups using the root parent as the group label. Used for select && multiselect only.
	 * 	default (string) The default string to show as the first item in the list. Used for select && multiselect only.
	 * 	show_select_all (bool) Whether or not to show the "Select All" option. Used for select && multiselect only.
	 * 	select_all (string) The string to use for the "Select All" option. Used for select && multiselect only.
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * NOTE: The $atts array is passed to a number of private methods to output the categories.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public static function category( $atts = NULL ) {

		$defaults = array(
			'type'            => 'select',
			'group'           => FALSE,
			'default'         => __('Select Category', 'connections'),
			'show_select_all' => TRUE,
			'select_all'      => __('Show All Categories', 'connections'),
			'show_empty'      => TRUE,
			'show_count'      => FALSE,
			'depth'           => 0,
			'parent_id'       => array(),
			'exclude'         => array(),
			'return'          => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		switch ( $atts['type'] ) {
			case 'select':
				$out = self::categorySelect( $atts );
				break;

			case 'multiselect':
				$out = self::categorySelect( $atts );
				break;

			case 'radio':
				$out = self::categoryInput( $atts );
				break;

			case 'checkbox':
				$out = self::categoryInput( $atts );
				break;

			case 'link':
				$out = self::categoryLink( $atts );
				break;
		}

		if ( $atts['return'] ) return $out;
	}

	/**
	 * The private function called by cnTemplate::category that outputs the select, multiselect; grouped and ungrouped.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string) The ouput type of the categories. Valid options options are: select || multiselect
	 * 	group (bool) Whether or not to create option groups using the root parent as the group label. Used for select && multiselect only.
	 * 	default (string) The default string to show as the first item in the list. Used for select && multiselect only.
	 * 	show_select_all (bool) Whether or not to show the "Select All" option. Used for select && multiselect only.
	 * 	select_all (string) The string to use for the "Select All" option. Used for select && multiselect only.
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses get_query_var()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private static function categorySelect( $atts ) {
		global $connections;
		$selected = array();

		if ( get_query_var( 'cn-cat' ) ) {

			$selected = get_query_var( 'cn-cat' );


		} elseif ( get_query_var( 'cn-cat-slug' ) ) {

			// If the category slug is a descendant, use the last slug from the URL for the query.
			$queryCategorySlug = explode( '/' , get_query_var( 'cn-cat-slug' ) );

			if ( isset( $queryCategorySlug[ count( $queryCategorySlug ) - 1 ] ) ) $selected = $queryCategorySlug[ count( $queryCategorySlug ) - 1 ];
		}

		// If value is a string, strip the white space and covert to an array.
		if ( ! is_array( $selected ) ) {

			$selected = str_replace( ' ', '', $selected );

			$selected = explode( ',', $selected );
		}

		$level = 1;
		$out   = '';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'type'            => 'select',
			'group'           => FALSE,
			'class'           => array( 'cn-category-select' ),
			'name'            => 'cn-cat',
			'style'           => array(),
			'enhanced'        => TRUE,
			'on_change'       => 'this.form.submit()',
			'default'         => __( 'Select Category', 'connections' ),
			'show_select_all' => TRUE,
			'select_all'      => __( 'Show All Categories', 'connections' ),
			'show_empty'      => TRUE,
			'show_count'      => FALSE,
			'depth'           => 0,
			'parent_id'       => array(),
			'exclude'         => array(),
			'return'          => FALSE,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! is_array( $atts['parent_id'] ) ) {
			// Trim extra whitespace.
			$atts['parent_id'] = trim( str_replace( ' ', '', $atts['parent_id'] ) );

			// Convert to array.
			$atts['parent_id'] = explode( ',', $atts['parent_id'] );
		}

		if ( ! is_array( $atts['exclude'] ) ) {
			// Trim extra whitespace.
			$atts['exclude'] = trim( str_replace( ' ', '', $atts['exclude'] ) );

			// Convert to array.
			$atts['exclude'] = explode( ',', $atts['exclude'] );
		}

		// Add the 'cn-enhanced-select' class for the jQuery Chosen Plugin will enhance the drop down.
		if ( $atts['enhanced'] ) $atts['class'] = array_merge( (array) $atts['class'], array('cn-enhanced-select') );

		// $out .= PHP_EOL . '<select class="cn-cat-select" name="' . ( ( $atts['type'] == 'multiselect' ) ? 'cn-cat[]' : 'cn-cat' ) . '"' . ( ( $atts['type'] == 'multiselect' ) ? ' MULTIPLE ' : '' ) . ( ( $atts['type'] == 'multiselect' ) ? '' : ' onchange="this.form.submit()" ' ) . 'data-placeholder="' . esc_attr($atts['default']) . '">';
		$out .= sprintf( '<select %1$s name="%2$s"%3$s%4$sdata-placeholder="%5$s"%6$s>',
			empty( $atts['class'] ) ? '' : cnHTML::attribute( 'class', $atts['class'] ),
			$atts['type'] == 'multiselect' ? esc_attr( $atts['name'] ) . '[]' : esc_attr( $atts['name'] ),
			empty( $atts['style'] ) ? '' : cnHTML::attribute( 'style', $atts['style'] ),
			$atts['type'] == 'multiselect' ? '' : ( empty( $atts['on_change'] ) ? '' : sprintf( ' onchange="%s" ', esc_js( $atts['on_change'] ) ) ),
			esc_attr( $atts['default'] ),
			$atts['type'] == 'multiselect' ? ' MULTIPLE' : ''
			);

		$out .= PHP_EOL . '<option value=""></option>';

		if ( $atts['show_select_all'] ) $out .= PHP_EOL . '<option value="">' . esc_attr( $atts['select_all'] ) . '</option>';

		foreach ( $categories as $key => $category ) {
			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) continue;

			// Do not show the excluded category as options.
			if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) continue;

			// If grouping by root parent is enabled, open the optiongroup tag.
			if ( $atts['group'] && ! empty( $category->children ) )
				$out .= sprintf( '<optgroup label="%1$s">' , $category->name );

			// Call the recursive function to build the select options.
			$out .= self::categorySelectOption( $category, $level, $atts['depth'], $selected, $atts );

			// If grouping by root parent is enabled, close the optiongroup tag.
			if ( $atts['group'] && ! empty( $category->children ) )
				$out .= '</optgroup>' . PHP_EOL;
		}

		$out .= '</select>' . PHP_EOL;

		// if ( $atts['type'] == 'multiselect' ) $out .= self::submit( array( 'return' => TRUE ) );

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The private recursive function to build the select options.
	 *
	 * Accepted option for the $atts property are:
	 * 	group (bool) Whether or not to create option groups using the root parent as the group label. Used for select && multiselect only.
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int $level The current category level.
	 * @param int $depth The depth limit.
	 * @param array $selected An array of the selected category IDs / slugs.
	 * @param array $atts
	 * @return string
	 */
	private static function categorySelectOption( $category, $level, $depth, $selected, $atts ) {

		$out = '';

		$defaults = array(
			'group'      => FALSE,
			'show_empty' => TRUE,
			'show_count' => TRUE,
			'exclude'    => array(),
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Do not show the excluded category as options.
		if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) return $out;

		// The padding in px to indent descendant categories. The 7px is the default pad applied in the CSS which must be taken in to account.
		$pad = ( $level > 1 ) ? $level * 12 + 7 : 7;
		//$pad = str_repeat($atts['pad_char'], max(0, $level));

		// Set the option SELECT attribute if the category is one of the currently selected categories.
		if ( is_array( $selected ) ) {
			$strSelected = ( ( in_array( $category->term_id, $selected ) ) || ( in_array( $category->slug, $selected ) ) ) ? ' SELECTED ' : '';
		} else {
			$strSelected = ( ( $selected == $category->term_id ) || ( $selected == $category->slug ) ) ? ' SELECTED ' : '';
		}
		// $strSelected = $selected ? ' SELECTED ' : '';

		$class = 'class="cn-cat-level-' . $level . '"';

		// Category count to be appended to the category name.
		$count = ( $atts['show_count'] ) ? ' (' . $category->count . ')' : '';

		// If option grouping is TRUE, show only the select option if it is a descendant. The root parent was used as the option group label.
		if ( ( $atts['group'] && $level > 1 ) && ( $atts['show_empty'] || ! empty( $category->count ) || ! empty( $category->children ) ) ) {

			$out .= sprintf('<option %1$s style="padding-left: %2$dpx !important" value="%3$s"%4$s>' . /*$pad .*/ $category->name . $count . '</option>',
				$class,
				$pad,
				$category->term_id,
				$strSelected
				);
		}

		// If option grouping is FALSE, show the root parent and descendant options.
		elseif ( ! $atts['group'] && ( $atts['show_empty'] || ! empty($category->count) || ! empty($category->children) ) ) {

			$out .= sprintf('<option %1$s style="padding-left: %2$dpx !important" value="%3$s"%4$s>' . /*$pad .*/ $category->name . $count . '</option>',
				$class,
				$pad,
				$category->term_id,
				$strSelected
				);
		}

		/*
		 * Only show the descendants based on the following criteria:
		 * 	- There are descendant categories.
		 * 	- The descendant depth is < than the current $level
		 *
		 * When descendant depth is set to 0, show all descendants.
		 * When descendant depth is set to < $level, call the recursive function.
		 */
		if ( ! empty( $category->children ) && ($depth <= 0 ? -1 : $level) < $depth ) {
			foreach ( $category->children as $child ) {
				$out .= self::categorySelectOption( $child, $level + 1, $depth, $selected, $atts );
			}
		}

		return $out;
	}

	/**
	 * The private function called by cnTemplate::category that outputs the radio && checkbox in a table layout.
	 * Each category root parent and its descendants are output in an unordered list.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string) The ouput type of the categories. Valid options options are: select || multiselect
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	layout (string) The layout to be used for rendering the categories. Valid options are: list || table
	 * 	columns (int) The number of columns in the table.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses get_query_var()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private static function categoryInput( $atts = NULL ) {
		global $connections;

		$selected = ( get_query_var('cn-cat') ) ? get_query_var('cn-cat') : array();
		$categories = array();
		$level = 0;
		$out = '';
		$trClass = 'alternate';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'type'       => 'radio',
			'show_empty' => TRUE,
			'show_count' => TRUE,
			'depth'      => 0,
			'parent_id'  => array(),
			'exclude'    => array(),
			'layout'     => 'list',
			'columns'    => 3,
			'return'     => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );


		if ( ! empty( $atts['parent_id'] ) && ! is_array( $atts['parent_id'] ) ) {
			// Trim extra whitespace.
			$atts['parent_id'] = trim( str_replace( ' ', '', $atts['parent_id'] ) );

			// Convert to array.
			$atts['parent_id'] = explode( ',', $atts['parent_id'] );
		}

		if ( ! is_array( $atts['exclude'] ) ) {
			// Trim extra whitespace.
			$atts['exclude'] = trim( str_replace( ' ', '', $atts['exclude'] ) );

			// Convert to array.
			$atts['exclude'] = explode( ',', $atts['exclude'] );
		}

		foreach ( $categories as $key => $category ) {
			// Remove any empty root parent categories so the table builds correctly.
			if ( ! $atts['show_empty'] && ( empty($category->count ) && empty( $category->children ) ) ) unset( $categories[ $key ] );

			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) unset( $categories[ $key ] );

			// Do not show the excluded category as options.
			if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) unset( $categories[ $key ] );
		}

		switch ( $atts['layout'] ) {

			case 'table':

				// Build the table grid.
				$table = array();
				$rows = ceil(count( $categories ) / $atts['columns'] );
				$keys = array_keys( $categories );

				for ( $row = 1; $row <= $rows; $row++ )
					for ( $col = 1; $col <= $atts['columns']; $col++ )
						$table[$row][$col] = array_shift($keys);

				$out .= '<table cellspacing="0" cellpadding="0" class="cn-cat-table">';
					$out .= '<tbody>';

					foreach ( $table as $row => $cols ) {

						$trClass = ( $trClass == 'alternate' ) ? '' : 'alternate';

						$out .= '<tr' . ( $trClass ? ' class="' . $trClass . '"' : '' ) . '>';

						foreach ( $cols as $col => $key ) {

							// When building the table grid, NULL will be the result of the array_shift when it runs out of $keys.
							if ( $key === NULL ) continue;

							$tdClass = array('cn-cat-td');
							if ( $row == 1 ) $tdClass[] = '-top';
							if ( $row == $rows ) $tdClass[] = '-bottom';
							if ( $col == 1 ) $tdClass[] = '-left';
							if ( $col == $atts['columns'] ) $tdClass[] = '-right';

							$out .= '<td class="' . implode( '', $tdClass ) . '" style="width: ' . floor( 100 / $atts['columns'] ) . '%">';

								$out .= '<ul class="cn-cat-tree">';

									$out .= self::categoryInputOption( $categories[ $key ], $level + 1, $atts['depth'], $selected, $atts);

								$out .= '</ul>';

							$out .= '</td>';
						}

						$out .= '</tr>';
					}

					$out .= '</tbody>';
				$out .= '</table>';

				break;

			case 'list':

				$out .= '<ul class="cn-cat-tree">';

				foreach ( $categories as $key => $category ) {

					// Limit the category tree to only the supplied root parent categories.
					if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) continue;

					// Call the recursive function to build the select options.
					$out .= self::categoryInputOption( $categories[ $key ], $level + 1, $atts['depth'], $selected, $atts);
				}

				$out .= '</ul>';

				break;
		}


		if ( $atts['return']) return $out;
		echo $out;
	}

	/**
	 * The private recursive function to build the list item.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string)
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int $level The current category level.
	 * @param int $depth The depth limit.
	 * @param array $selected An array of the selected category IDs.
	 * @param array $atts
	 * @return string
	 */
	private static function categoryInputOption( $category, $level, $depth, $selected, $atts ) {

		$out = '';

		$defaults = array(
			'type'       => 'radio',
			'show_empty' => TRUE,
			'show_count' => TRUE,
			'exclude'    => array(),
		);

		$atts = wp_parse_args($atts, $defaults);

		// Do not show the excluded category as options.
		if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) return $out;

		if ( $atts['show_empty'] || ! empty( $category->count ) || ! empty( $category->children ) ) {

			$count = ( $atts['show_count'] ) ? ' (' . $category->count . ')' : '';

			$out .= '<li class="cn-cat-parent">';

			$out .= sprintf( '<input type="%1$s" class="cn-radio" id="%2$s" name="cn-cat" value="%3$s" %4$s/>', $atts['type'], $category->slug, $category->term_id, checked( $selected, $category->term_id, FALSE ) );
			$out .= sprintf( '<label for="%1$s"> %2$s</label>', $category->slug, $category->name . $count );

			/*
			 * Only show the descendants based on the following criteria:
			 * 	- There are descendant categories.
			 * 	- The descendant depth is < than the current $level
			 *
			 * When descendant depth is set to 0, show all descendants.
			 * When descendant depth is set to < $level, call the recursive function.
			 */
			if ( ! empty( $category->children ) && ( $depth <= 0 ? -1 : $level ) < $depth ) {

				$out .= '<ul class="cn-cat-children">';

				foreach ( $category->children as $child ) {
					$out .= self::categoryInputOption( $child, $level + 1, $depth, $selected, $atts );
				}

				$out .= '</ul>';
			}

			$out .= '</li>';
		}

		return $out;
	}

	/**
	 * The private function called by cnTemplate::category that outputs the category links in two formats:
	 *  - A table layout with one cell per root parent category containing all descendants in an unordered list.
	 *  - An unordered list.
	 *
	 * Accepted option for the $atts property are:
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	layout (string) The layout to be used for rendering the categories. Valid options are: list || table
	 * 	columns (int) The number of columns in the table.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses get_query_var()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private static function categoryLink( $atts = NULL ) {
		global $connections;

		$categories = array();
		$level = 0;
		$out = '';
		$trClass = 'alternate';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'show_empty' => TRUE,
			'show_count' => TRUE,
			'depth'      => 0,
			'parent_id'  => array(),
			'exclude'    => array(),
			'layout'     => 'list',
			'columns'    => 3,
			'return'     => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! empty( $atts['parent_id'] ) && ! is_array( $atts['parent_id'] ) ) {
			// Trim extra whitespace.
			$atts['parent_id'] = trim( str_replace(' ', '', $atts['parent_id'] ) );

			// Convert to array.
			$atts['parent_id'] = explode( ',', $atts['parent_id'] );
		}

		if ( ! is_array( $atts['exclude'] ) ) {
			// Trim extra whitespace.
			$atts['exclude'] = trim( str_replace( ' ', '', $atts['exclude'] ) );

			// Convert to array.
			$atts['exclude'] = explode( ',', $atts['exclude'] );
		}

		foreach ( $categories as $key => $category ) {
			// Remove any empty root parent categories so the table builds correctly.
			if ( ! $atts['show_empty'] && ( empty( $category->count ) && empty( $category->children ) ) ) unset( $categories[ $key ] );

			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) unset( $categories[ $key ] );

			// Do not show the excluded category as options.
			if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) unset( $categories[ $key ] );
		}

		switch ( $atts['layout'] ) {

			case 'table':

				// Build the table grid.
				$table = array();
				$rows = ceil(count( $categories ) / $atts['columns'] );
				$keys = array_keys( $categories );
				for ( $row = 1; $row <= $rows; $row++ )
					for ( $col = 1; $col <= $atts['columns']; $col++ )
						$table[ $row ][ $col ] = array_shift( $keys );

				$out .= '<table cellspacing="0" cellpadding="0" class="cn-cat-table">';
					$out .= '<tbody>';

					foreach ( $table as $row => $cols ) {
						$trClass = ( $trClass == 'alternate' ) ? '' : 'alternate';

						$out .= '<tr' . ( $trClass ? ' class="' . $trClass . '"' : '' ) . '>';

						foreach ( $cols as $col => $key ) {
							// When building the table grid, NULL will be the result of the array_shift when it runs out of $keys.
							if ( $key === NULL ) continue;

							$tdClass = array('cn-cat-td');
							if ( $row == 1 ) $tdClass[] = '-top';
							if ( $row == $rows ) $tdClass[] = '-bottom';
							if ( $col == 1 ) $tdClass[] = '-left';
							if ( $col == $atts['columns'] ) $tdClass[] = '-right';

							$out .= '<td class="' . implode( '', $tdClass) . '" style="width: ' . floor( 100 / $atts['columns'] ) . '%">';

								$out .= '<ul class="cn-cat-tree">';

									$out .= self::categoryLinkDescendant( $categories[ $key ], $level + 1, $atts['depth'], array(), $atts );

								$out .= '</ul>';

							$out .= '</td>';
						}

						$out .= '</tr>';
					}

					$out .= '</tbody>';
				$out .= '</table>';

				break;

			case 'list':

				$out .= '<ul class="cn-cat-tree">';

				foreach ( $categories as $key => $category )
				{
					// Limit the category tree to only the supplied root parent categories.
					if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) continue;

					// Call the recursive function to build the select options.
					$out .= self::categoryLinkDescendant( $category, $level + 1, $atts['depth'], array(), $atts );
				}

				$out .= '</ul>';

				break;
		}

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The private recursive function to build the category link item.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string)
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int $level The current category level.
	 * @param int $depth The depth limit.
	 * @param array $slug An array of the category slugs to be used to build the permalink.
	 * @param array $atts
	 * @return string
	 */
	private static function categoryLinkDescendant ( $category, $level, $depth, $slug, $atts ) {
		global $wp_rewrite, $connections;

		$out = '';

		$defaults = array(
			'show_empty' => TRUE,
			'show_count' => TRUE,
			'exclude'    => array(),
		);

		$atts = wp_parse_args($atts, $defaults);

		// Do not show the excluded category as options.
		if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) return $out;

		if ( $atts['show_empty'] || ! empty( $category->count ) || ! empty ( $category->children ) ) {

			$count = ( $atts['show_count'] ) ? ' (' . $category->count . ')' : '';

			/*
			 * Determine of pretty permalink is enabled.
			 * If it is, add the category slug to the array which will be imploded to be used to build the URL.
			 * If it is not, set the $slug to the category term ID.
			 */
			if ( $wp_rewrite->using_permalinks() ) {
				$slug[] = $category->slug;
			} else {
				$slug = array( $category->slug );
			}

			/*
			 * Get tge current category from the URL / query string.
			 */
			if ( get_query_var( 'cn-cat-slug' ) ) {

				// Category slug
				$queryCategorySlug = get_query_var( 'cn-cat-slug' );
				if ( ! empty( $queryCategorySlug ) ) {
					// If the category slug is a descendant, use the last slug from the URL for the query.
					$queryCategorySlug = explode( '/' , $queryCategorySlug );

					if ( isset( $queryCategorySlug[ count( $queryCategorySlug )-1 ] ) ) $currentCategory = $queryCategorySlug[ count( $queryCategorySlug )-1 ];
				}

			} elseif ( get_query_var( 'cn-cat' ) ) {

				$currentCategory = get_query_var( 'cn-cat' );

			} else {

				$currentCategory = '';

			}

			$out .= '<li class="cat-item cat-item-' . $category->term_id . ( $currentCategory == $category->slug || $currentCategory == $category->term_id ? ' current-cat' : '' ) . ' cn-cat-parent">';

			// Create the permalink anchor.
			$out .= $connections->url->permalink( array(
				'type'   => 'category',
				'slug'   => implode( '/' , $slug ),
				'title'  => $category->name,
				'text'   => $category->name . $count,
				'return' => TRUE
				)
			);

			/*
			 * Only show the descendants based on the following criteria:
			 * 	- There are descendant categories.
			 * 	- The descendant depth is < than the current $level
			 *
			 * When descendant depth is set to 0, show all descendants.
			 * When descendant depth is set to < $level, call the recursive function.
			 */
			if ( ! empty( $category->children ) && ( $depth <= 0 ? -1 : $level ) < $depth ) {

				$out .= '<ul class="children cn-cat-children">';

				foreach ( $category->children as $child ) {
					$out .= self::categoryLinkDescendant( $child, $level + 1, $depth, $slug, $atts );
				}

				$out .= '</ul>';
			}

			$out .= '</li>';
		}

		return $out;
	}

}

// Init the Template Parts API
cnTemplatePart::init();
