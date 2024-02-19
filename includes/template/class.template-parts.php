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

use Connections_Directory\Request;
use Connections_Directory\Taxonomy\Term;
use Connections_Directory\Template\Hook_Transient;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;
use Connections_Directory\Utility\_nonce;
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_url;
use function Connections_Directory\Taxonomy\Partial\getTermParents;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class cnTemplatePart
 *
 * @since 10.4.40 Extend with stdClass to remove "Creation of dynamic property" deprecation notices.
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnTemplatePart extends stdClass {

	/**
	 * Register the default template actions.
	 *
	 * @internal
	 * @since 0.7.6.5
	 */
	public static function hooks() {

		add_action( 'cn_action_list_before', array( __CLASS__, 'doListActionsBefore' ), 5 );
		add_action( 'cn_action_list_after', array( __CLASS__, 'doListActionsAfter' ), 5 );

		add_action( 'cn_list_actions', array( __CLASS__, 'listActions' ) );
		add_action( 'Connections_Directory/Render/Template/Single_Entry/Before', array( __CLASS__, 'entryActions' ), 10, 2 );

		add_action( 'cn_list_action-view_all', array( __CLASS__, 'listAction_ViewAll' ) );

		add_action( 'cn_entry_action-back', array( __CLASS__, 'entryAction_Back' ), 10, 2 );
		add_action( 'cn_entry_action-vcard', array( __CLASS__, 'entryAction_vCard' ), 10, 2 );

		add_action( 'cn_list_no_results', array( __CLASS__, 'noResults' ), 10, 2 );

		add_action( 'cn_action_list_before', array( __CLASS__, 'categoryDescription' ), 10, 2 );
		add_action( 'cn_action_list_before', array( __CLASS__, 'searchingMessage' ), 11, 3 );

		add_action( 'cn_list_character_index', array( __CLASS__, 'index' ) );
		add_action( 'cn_list_return_to_target', array( __CLASS__, 'returnToTopTarget' ) );

		// add_action( 'cn_action_entry_after', array( __CLASS__, 'JSON' ), 10, 2 );
	}

	/**
	 * Get the template part path or the rendered template part.
	 *
	 * If the template is being loaded, the output will be buffered by default
	 * and the result of the buffer returned.
	 *
	 * @since  0.8.11
	 *
	 * @param  string  $base         The base template name.
	 * @param  string  $name         The template name.
	 * @param  array   $params       An array of arguments that will be extract() if the template part is to be loaded.
	 * @param  boolean $load         Whether to load the template.
	 * @param  boolean $buffer
	 * @param  boolean $require_once Whether to require() or require_once() the template part.
	 *
	 * @return string|bool The template part file path, if one is located.
	 */
	public static function get( $base, $name = null, $params = array(), $load = true, $buffer = true, $require_once = true ) {

		$files = cnLocate::fileNames( $base, $name );

		if ( $load ) {

			if ( $buffer ) {

				ob_start();

				cnTemplatePart::locate( $files, $params, true, $require_once );

				$part = ob_get_clean();

			} else {

				return cnTemplatePart::locate( $files, $params, true, $require_once );
			}

		} else {

			$part = cnTemplatePart::locate( $files, $params );
		}

		return $part;
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * @since  0.8.11
	 *
	 * @param string|array $files        Template file(s) to search for, in order of priority.
	 * @param array        $params       An array of arguments that will be extract() if the template part is to be loaded.
	 * @param boolean      $load         If true the template file will be loaded.
	 * @param boolean      $require_once Whether to require_once or require. Default is to require_once.
	 *
	 * @return string|bool The template part file path, if one is located.
	 */
	public static function locate( $files, $params, $load = false, $require_once = true ) {

		$located = cnLocate::file( $files );

		if ( $load && $located ) {

			return self::load( $located, $params, $require_once );
		}

		return $located;
	}

	/**
	 * Load the template.
	 *
	 * This is basically a Connections version of WP core {@see load_template()}.
	 *
	 * The difference is an array $params can be passed which will be
	 * extract() so the $params are in scope of the template part.
	 *
	 * @since  0.8.11
	 *
	 * @global array      $posts
	 * @global WP_Post    $post       Global post object.
	 * @global bool       $wp_did_header
	 * @global WP_Query   $wp_query   WordPress Query object.
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 * @global wpdb       $wpdb       WordPress database abstraction object.
	 * @global string     $wp_version
	 * @global WP         $wp         Current WordPress environment instance.
	 * @global int        $id
	 * @global WP_Comment $comment    Global comment object.
	 * @global int        $user_ID
	 *
	 * @param string $file         The file path of the template part to be loaded.
	 * @param array  $params       An array of arguments that will be extract().
	 * @param bool   $require_once Whether to require_once or require. Default is to require_once.
	 *
	 * @return bool Unless the required file returns another value.
	 */
	public static function load( $file, $params = array(), $require_once = true ) {

		/** @noinspection PhpUnusedLocalVariableInspection */
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if ( is_array( $wp_query->query_vars ) ) {

			extract( $wp_query->query_vars, EXTR_SKIP );
		}

		if ( is_array( $params ) ) {

			extract( $params );
		}

		if ( $require_once ) {

			$result = require_once $file;

		} else {

			$result = require $file;
		}

		return ! ( false === $result );
	}

	/**
	 * Load and init an instance of the WP_list_Table class.
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @param string $type The type of the list table to load and init.
	 * @param array  $args Optional. Arguments to pass to the class.
	 *
	 * @return mixed bool|object Returns or echos the HTML output of the table class. FALSE on failure.
	 */
	public static function table( $type, $args = array() ) {

		$table = array(
			'term-admin' => 'CN_Term_Admin_List_Table',
			'email-log'  => 'CN_Email_Log_List_Table',
		);

		if ( array_key_exists( $type, $table ) ) {

			require_once CN_PATH . 'includes/template/class.template-list-table-' . $type . '.php';

			return new $table[ $type ]( $args );
		}

		return false;
	}

	/**
	 * Load and init an instance of the Walker class.
	 *
	 * @access public
	 * @since  8.2
	 * @static
	 *
	 * @param string $type The type of the walker to load and init.
	 * @param array  $args Optional. Arguments to pass to the class.
	 *
	 * @return false|string Returns or echos the HTML output of the walker class. FALSE on failure.
	 */
	public static function walker( $type, $args = array() ) {

		$walker = array(
			'term-list'            => 'CN_Walker_Term_List',
			'term-select'          => 'CN_Walker_Term_Select_List',
			'term-select-enhanced' => 'CN_Walker_Term_Select_List_Enhanced',
			'term-checklist'       => 'CN_Walker_Term_Check_List',
			'term-radio-group'     => 'CN_Walker_Term_Radio_Group',
		);

		if ( array_key_exists( $type, $walker ) ) {

			return call_user_func( array( $walker[ $type ], 'render' ), $args );
		}

		return false;
	}

	/**
	 * Echo or return the supplied string.
	 *
	 * @access protected
	 * @since  8.2.6
	 *
	 * @param bool   $return
	 * @param string $html
	 *
	 * @return string
	 */
	public static function echoOrReturn( $return, $html ) {

		if ( $return ) {

			return $html;

		} else {

			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}
	}

	/**
	 * Display the template no found error message.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @uses   shortcode_atts()
	 *
	 * @param array $atts The shortcode $atts array.
	 *
	 * @return string       The error message.
	 */
	public static function loadTemplateError( $atts ) {

		$defaults = array(
			'template' => null,
		);

		$atts = shortcode_atts( $defaults, $atts );

		/* translators: Template name. */
		return '<p style="color:red; font-weight:bold; text-align:center;">' . sprintf( esc_html__( 'ERROR: Template %1$s not found.', 'connections' ), $atts['template'] ) . '</p>';
	}

	/**
	 * Output the return to top div.
	 *
	 * @access public
	 * @since  0.7.6.5
	 * @uses   wp_parse_args()
	 * @uses   apply_filters()
	 *
	 * @param array $atts [optional]
	 *
	 * @return string
	 */
	public static function returnToTopTarget( $atts = array() ) {

		$defaults = array(
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = apply_filters( 'cn_filter_return_to_top_target', '<div id="cn-top" style="position: absolute; top: 0; right: 0;"></div>' );

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Renders a Connections compatible form opening.
	 *
	 * @since 0.8
	 *
	 * @global $wp_rewrite
	 *
	 * @param array{ home_id: int, force_home: bool, return: bool } $atts The method parameter arguments.
	 *                                                                    Generally, the shortcode options are passed.
	 *
	 * @return string
	 */
	public static function formOpen( $atts = array() ) {

		/** @var WP_Rewrite $wp_rewrite */
		global $wp_rewrite;

		$defaults = array(
			'return' => false,
		);

		/**
		 * Filter the default attributes array.
		 *
		 * @since 8.5.14
		 */
		$defaults = apply_filters( 'cn_form_open_default_atts', $defaults );

		$atts = wp_parse_args( $atts, $defaults );

		/**
		 * Filter the user supplied attributes.
		 *
		 * @since 8.5.14
		 */
		$atts = apply_filters( 'cn_form_open_atts', $atts );

		$html   = '';
		$action = '';

		if ( is_customize_preview() ) {

			return self::echoOrReturn( $atts['return'], $html );
		}

		// Get the directory home page ID.
		$homeID = $atts['force_home'] ? cnSettingsAPI::get( 'connections', 'home_page', 'page_id' ) : $atts['home_id'];

		// The base post permalink is required, do not filter the permalink through cnSEO.
		cnSEO::doFilterPermalink( false );

		if ( $wp_rewrite->using_permalinks() ) {

			$isHomepage = cnSettingsAPI::get( 'connections', 'home_page', 'page_id' ) != $atts['home_id'] ? true : false;

			if ( $isHomepage || $atts['force_home'] ) {

				$permalink = get_permalink( $homeID );

				/**
				 * Filter the form action attribute.
				 *
				 * @since 8.5.15
				 * @since 10.4.39 Changed filter hook name.
				 *
				 * @param string $permalink The form action permalink.
				 * @param array  $atts      The filter parameter arguments.
				 */
				$permalink = apply_filters( 'Connections_Directory/Template/Partial/Search/Form_Action', $permalink, $atts );
				$permalink = _url::makeRelative( $permalink );

				$action = " action=\"{$permalink}\"";
			}

			// Changed `$isHomepage` to `TRUE` in for action attribute ternary so the search is always off the page root.
			// See this issue: https://connections-pro.com/support/topic/image-grid-category-dropdown/#post-395856
			// Doesn't seem to cause any issues, but I can not remember the purpose of defaulting to  the current page
			// for the form action when home_id always should default to the current page unless set otherwise.
			// @see https://connections-pro.com/support/topic/cross-referencing-search-terms/
			// @see https://connections-pro.com/support/topic/cross-referencing-using-different-fields/
			//
			// Reverted the above change due to
			// @see https://connections-pro.com/support/topic/image-grid-category-dropdown/#post-395816
			$html .= '<form class="cn-form" id="cn-cat-select"' . $action . ' method="get">';

			if ( is_front_page() ) {
				$html .= '<input type="hidden" name="page_id" value="' . $homeID . '">';
			}

		} else {

			$html .= '<form class="cn-form" id="cn-cat-select" method="get">';
			$html .= '<input type="hidden" name="' . ( is_page() ? 'page_id' : 'p' ) . '" value="' . $homeID . '">';
		}

		// Add the cnSEO permalink filter.
		cnSEO::doFilterPermalink();

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Renders a form closing tag, nothing more.
	 * Just a simple helper function to compliment cnTemplatePart::formOpen().
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public static function formClose( $atts = array() ) {

		$defaults = array(
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( is_customize_preview() ) {

			return self::echoOrReturn( $atts['return'], '' );
		}

		$out = '</form>';

		return self::echoOrReturn( $atts['return'], $out );
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
			'return' => false,
		);

		$atts     = wp_parse_args( $atts, $defaults );
		$isSingle = cnQuery::getVar( 'cn-entry-slug' ) ? true : false;

		$out = '<div class="cn-list-head">' . PHP_EOL;

		ob_start();
		do_action( 'cn_action_list_before', $atts, $results );
		do_action( 'cn_action_list_both', $atts, $results );

		do_action( 'cn_action_list_before-' . $template->getSlug(), $atts, $results );
		Hook_Transient::instance()->add( 'cn_action_list_before-' . $template->getSlug() );

		do_action( 'cn_action_list_both-' . $template->getSlug(), $atts, $results );
		Hook_Transient::instance()->add( 'cn_action_list_both-' . $template->getSlug() );

		$out .= ob_get_clean();

		// This action only is required when the index is to be displayed.
		if ( ! $isSingle && ( $atts['show_alphaindex'] && ! $atts['repeat_alphaindex'] ) ) {

			// The character index template part.
			ob_start();
			do_action( 'cn_list_character_index', $atts );
			$out .= ob_get_clean();
		}

		$out .= '</div>' . ( WP_DEBUG ? '<!-- END .cn-list-head -->' : '' ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The result list body.
	 *
	 * @since  0.8
	 *
	 * @param array      $atts     The shortcode $atts array.
	 * @param array      $results  The cnRetrieve query results.
	 * @param cnTemplate $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function body( $atts, $results, $template ) {

		$defaults = array(
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$class = apply_filters( 'cn_list_body_class', array( 'cn-list-body' ) );

		$class = apply_filters( 'cn_list_body_class-' . $template->getSlug(), $class );
		Hook_Transient::instance()->add( 'cn_list_body_class-' . $template->getSlug() );

		array_walk( $class, 'sanitize_html_class' );

		ob_start();

		do_action( 'cn_list_before_body', $atts, $results, $template );

		$before = ob_get_clean();

		$open = '<div class="' . implode( ' ', array_unique( array_filter( $class ) ) ) . '" id="cn-list-body">' . PHP_EOL;

		ob_start();

		// If there are no results no need to proceed and output message.
		if ( empty( $results ) ) {

			// The no results message.
			do_action( 'cn_list_no_results', $atts, $results, $template );

		} else {

			// Check to see if there is a template file override.
			$part = self::get(
				'list',
				'body',
				array(
					'atts'     => $atts,
					'results'  => $results,
					'template' => $template,
				)
			);

			// If one was found, lets include it. If not, run the core function.
			if ( $part ) {

				echo $part; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			} else {

				self::cards( $atts, $results, $template );
			}
		}

		$content = ob_get_clean();

		$close = '</div>' . ( WP_DEBUG ? '<!-- END #cn-list-body -->' : '' ) . PHP_EOL;

		ob_start();

		do_action( 'cn_list_after_body', $atts, $results, $template );

		$after = ob_get_clean();

		$html = $before . $open . $content . $close . $after;

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Render the body section headings.
	 *
	 * @since 9.10
	 *
	 * @param array  $atts           The shortcode $atts array.
	 * @param string $currentLetter  The current character.
	 * @param string $previousLetter The previous character.
	 */
	private static function bodySectionHead( $atts, $currentLetter, $previousLetter ) {

		if ( $currentLetter !== $previousLetter ) {

			if ( ( $atts['show_alphaindex'] && $atts['repeat_alphaindex'] ) || $atts['show_alphahead'] ) {

				printf( '<div class="cn-list-section-head" id="cn-char-%1$s">', esc_html( $currentLetter ) );
			}

			// This action only is required when the index is to be displayed.
			if ( $atts['show_alphaindex'] && $atts['repeat_alphaindex'] ) {

				// The character index template part.
				do_action( 'cn_list_character_index', $atts );
			}

			if ( $atts['show_alphahead'] && ! Request::get()->isSearch() ) {

				printf( '<h4 class="cn-alphahead">%1$s</h4>', esc_html( $currentLetter ) );
			}

			if ( ( $atts['show_alphaindex'] && $atts['repeat_alphaindex'] ) || $atts['show_alphahead'] ) {

				echo '</div>' . ( WP_DEBUG ? '<!-- END #cn-char-' . esc_html( $currentLetter ) . ' -->' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

		}
	}

	/**
	 * Create the array of classes for the Entry Card.
	 *
	 * @since 9.10
	 *
	 * @param cnEntry_HTML $entry
	 * @param cnTemplate   $template
	 * @param bool         $isSingle
	 * @param int          $rowCount
	 *
	 * @return array
	 */
	private static function cardClass( $entry, $template, $isSingle, $rowCount ) {

		$class = array();

		if ( ! $isSingle ) {

			array_push( $class, ( ++$rowCount % 2 ) ? 'cn-list-row-alternate' : 'cn-list-row' );
		}

		/*
		 * @todo
		 * All templates need updated to remove use of the .cn-entry and .cn-entry-single classes
		 * and use .cn-list-item and .cn-list-item.cn-list-item-is-single instead.
		 */
		array_push( $class, 'cn-list-item' );

		if ( $isSingle ) {

			array_push( $class, 'cn-list-item-is-single' );
		}

		array_push( $class, 'vcard', $entry->getEntryType(), $entry->getCategoryClass( true ) );

		$class = apply_filters( 'cn_list_row_class', $class, $entry );
		$class = apply_filters( "cn_list_row_class-{$template->getSlug()}", $class, $entry );

		Hook_Transient::instance()->add( 'cn_list_row_class-' . $template->getSlug() );

		return $class;
	}

	/**
	 * The result list cards.
	 *
	 * @since  0.8
	 *
	 * @param array      $atts     The shortcode $atts array.
	 * @param array      $results  The cnRetrieve query results.
	 * @param cnTemplate $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function cards( $atts, $results, $template ) {

		$defaults = array(
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$html = '';

		$previousLetter = '';
		$rowCount       = 0;
		$isSingle       = cnQuery::getVar( 'cn-entry-slug' ) ? true : false;

		foreach ( $results as $row ) {

			$entry = new cnEntry_vCard( $row );
			/** @noinspection PhpUnusedLocalVariableInspection */
			$vCard =& $entry;

			// Configure the page where the entry link to.
			$entry->directoryHome( array( 'page_id' => $atts['home_id'], 'force_home' => $atts['force_home'] ) );

			ob_start();

			/*
			 * Section heading does not need to render on the single Entry view.
			 */
			if ( ! $isSingle ) {

				$currentLetter = strtoupper( mb_substr( $entry->getSortColumn(), 0, 1 ) );

				self::bodySectionHead( $atts, $currentLetter, $previousLetter );

				$previousLetter = $currentLetter;
			}

			// Display the Entry Actions.
			if ( $isSingle ) {

				/**
				 * @param array        $atts     The shortcode attributes.
				 * @param cnEntry_HTML $entry    The current Entry.
				 * @param cnTemplate   $template The current template.
				 */
				do_action(
					'Connections_Directory/Render/Template/Single_Entry/Before',
					$atts,
					$entry,
					$template
				);

			} else {

				/**
				 * @param array        $atts     The shortcode attributes.
				 * @param cnEntry_HTML $entry    The current Entry.
				 * @param cnTemplate   $template The current template.
				 */
				do_action(
					'Connections_Directory/Render/Template/Entry_List/Before',
					$atts,
					$entry,
					$template
				);
			}

			/**
			 * @param array        $atts     The shortcode attributes.
			 * @param cnEntry_HTML $entry    The current Entry.
			 * @param cnTemplate   $template The current template.
			 */
			do_action(
				'Connections_Directory/Render/Template/Entry/Before',
				$atts,
				$entry,
				$template
			);

			do_action( 'cn_action_entry_before-' . $template->getSlug(), $atts, $entry );
			Hook_Transient::instance()->add( 'cn_action_entry_before-' . $template->getSlug() );

			do_action( 'cn_action_entry_both-' . $template->getSlug(), $atts, $entry );
			Hook_Transient::instance()->add( 'cn_action_entry_both-' . $template->getSlug() );

			printf(
				'<div class="%1$s" id="%3$s" data-entry-type="%2$s" data-entry-id="%4$d" data-entry-slug="%3$s">',
				_escape::classNames( implode( ' ', self::cardClass( $entry, $template, $isSingle, ++$rowCount ) ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_attr( $entry->getEntryType() ),
				esc_attr( $entry->getSlug() ),
				absint( $entry->getId() )
			);

			do_action( 'cn_template-' . $template->getSlug(), $entry, $template, $atts );

			echo PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #' . esc_html( $entry->getSlug() ) . ' -->' : '' ) . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			// After entry actions.
			do_action( 'cn_action_entry_both-' . $template->getSlug(), $atts, $entry );
			Hook_Transient::instance()->add( 'cn_action_entry_both-' . $template->getSlug() );

			do_action( 'cn_action_entry_after-' . $template->getSlug(), $atts, $entry );
			Hook_Transient::instance()->add( 'cn_action_entry_after-' . $template->getSlug() );

			/**
			 * @param array        $atts     The shortcode attributes.
			 * @param cnEntry_HTML $entry    The current Entry.
			 * @param cnTemplate   $template The current template.
			 */
			do_action(
				'Connections_Directory/Render/Template/Entry/After',
				$atts,
				$entry,
				$template
			);

			// Display the Entry Actions.
			if ( $isSingle ) {

				/**
				 * @param array        $atts     The shortcode attributes.
				 * @param cnEntry_HTML $entry    The current Entry.
				 * @param cnTemplate   $template The current template.
				 */
				do_action(
					'Connections_Directory/Render/Template/Single_Entry/After',
					$atts,
					$entry,
					$template
				);

			} else {

				/**
				 * @param array        $atts     The shortcode attributes.
				 * @param cnEntry_HTML $entry    The current Entry.
				 * @param cnTemplate   $template The current template.
				 */
				do_action(
					'Connections_Directory/Render/Template/Entry_List/After',
					$atts,
					$entry,
					$template
				);
			}

			$html .= ob_get_clean();
		}

		return self::echoOrReturn( $atts['return'], $html );
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
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '<div class="cn-list-foot">' . PHP_EOL;

			ob_start();

			do_action( 'cn_action_list_both-' . $template->getSlug(), $atts, $results );
			Hook_Transient::instance()->add( 'cn_action_list_both-' . $template->getSlug() );

			do_action( 'cn_action_list_after-' . $template->getSlug(), $atts, $results );
			Hook_Transient::instance()->add( 'cn_action_list_after-' . $template->getSlug() );

			do_action( 'cn_action_list_both', $atts, $results );
			do_action( 'cn_action_list_after', $atts, $results );

			$out .= ob_get_clean();

		$out .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END .cn-list-foot -->' : '' ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The action callback to render the list action before the result list.
	 *
	 * @access public
	 * @since  8.2.8
	 * @static
	 *
	 * @param array $atts The shortcode $atts array.
	 *
	 * @return string
	 */
	public static function doListActionsBefore( $atts ) {

		$out = '';

		$defaults = array(
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! cnQuery::getVar( 'cn-entry-slug' ) ) {

			// List actions template part.
			ob_start();
			do_action( 'cn_list_actions-before', $atts );
			do_action( 'cn_list_actions', $atts );
			$out = ob_get_clean();
		}

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The action callback to render the list action after the result list.
	 *
	 * @access public
	 * @since  8.2.8
	 * @static
	 *
	 * @param array $atts The shortcode $atts array.
	 *
	 * @return string
	 */
	public static function doListActionsAfter( $atts ) {

		$out = '';

		$defaults = array(
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Display the Results List Actions.
		if ( ! cnQuery::getVar( 'cn-entry-slug' ) ) {

			// List actions template part.
			do_action( 'cn_list_actions-after', $atts );
		}

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Output the result list actions.
	 *
	 * @since  0.7.6.5
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public static function listActions( $atts = array() ) {

		$out = '';

		$defaults = array(
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'before'        => '',
			'before-item'   => '',
			'after-item'    => '',
			'after'         => '',
			'return'        => false,
		);

		$atts = wp_parse_args( $atts, apply_filters( 'cn_list_actions_atts', $defaults ) );

		$settings = cnSettingsAPI::get( 'connections', 'list_actions', 'actions' );

		if ( ! isset( $settings['active'] ) || empty( $settings['active'] ) ) {
			return '';
		}

		foreach ( $settings['active'] as $key => $slug ) {

			if ( ! has_action( "cn_list_action-{$slug}" ) ) {
				continue;
			}

			ob_start();

			do_action( "cn_list_action-{$slug}", $atts );

			$action = ob_get_clean();

			if ( strlen( $action ) < 1 ) {
				continue;
			}

			$out .= sprintf(
				PHP_EOL . "\t" . '%1$s<%2$s class="cn-list-action-item" id="cn-list-action-%3$s">%4$s</%2$s>%5$s',
				$atts['before-item'],
				$atts['item_tag'],
				esc_attr( $slug ),
				$action,
				$atts['after-item']
			);
		}

		$out = sprintf(
			'<%1$s class="cn-list-actions">%2$s' . PHP_EOL . '</%1$s>',
			$atts['container_tag'],
			$out
		);

		$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Callback for the cn_list_action-view_all action which outputs the "View All" link
	 * in the list actions.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @param array $atts The $atts from self::listActions() passed by the action callback.
	 */
	public static function listAction_ViewAll( $atts ) {

		$defaults = array(
			'type'       => 'all',
			'text'       => __( 'View All', 'connections' ),
			'rel'        => 'canonical',
			'home_id'    => _array::get( $atts, 'home_id', cnShortcode::getHomeID() ),
			'force_home' => _array::get( $atts, 'force_home', false ),
			'return'     => false,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		// No need to display if the user is viewing the "View All" page.
		if ( 'all' != cnQuery::getVar( 'cn-view' ) ) {

			// Output the "View All" link.
			cnURL::permalink( $atts );
		}
	}

	/**
	 * Output the entry list actions.
	 *
	 * @since 0.7.6.5
	 *
	 * @param array   $atts [optional]
	 * @param cnEntry $entry Instance of the cnEntry class.
	 *
	 * @return string
	 */
	public static function entryActions( $atts, $entry ) {

		$out = '';

		$defaults = array(
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'before'        => '',
			'before-item'   => '',
			'after-item'    => '',
			'after'         => '',
			'return'        => false,
		);

		$atts = wp_parse_args( $atts, apply_filters( 'cn_entry_actions_atts', $defaults ) );

		$settings = cnSettingsAPI::get( 'connections', 'entry_actions', 'actions' );

		if ( ! isset( $settings['active'] ) || empty( $settings['active'] ) ) {

			return self::echoOrReturn( $atts['return'], $out );
		}

		foreach ( $settings['active'] as $key => $slug ) {

			if ( ! has_action( "cn_entry_action-{$slug}" ) ) {
				continue;
			}

			ob_start();

			do_action( "cn_entry_action-{$slug}", $atts, $entry );

			$action = ob_get_clean();

			if ( strlen( $action ) < 1 ) {
				continue;
			}

			$out .= sprintf(
				PHP_EOL . "\t" . '%1$s<%2$s class="cn-entry-action-item" id="cn-entry-action-%3$s">%4$s</%2$s>%5$s',
				empty( $atts['before-item'] ) ? '' : $atts['before-item'],
				$atts['item_tag'],
				esc_attr( $slug ),
				$action,
				empty( $atts['after-item'] ) ? '' : $atts['after-item']
			);
		}

		$out = sprintf(
			'<%1$s id="cn-entry-actions">%2$s' . PHP_EOL . '</%1$s>',
			$atts['container_tag'],
			$out
		);

		$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Callback for the cn_entry_action-back action which outputs the "Go back to directory." link.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @param array  $atts  The $atts from self::entryActions() passed by the action callback.
	 * @param object $entry An instance of the cnEntry object; passed by the action callback.
	 */
	public static function entryAction_Back( $atts, $entry ) {

		$defaults = array(
			'type'       => 'home',
			'text'       => __( 'Go back to directory.', 'connections' ),
			// 'on_click' => 'history.back();return false;',
			'force_home' => $atts['force_home'],
			'home_id'    => $atts['home_id'],
			'return'     => false,
		);

		$atts = cnSanitize::args( apply_filters( 'cn_entry_action_back_atts', $defaults ), $defaults );

		cnURL::permalink( $atts );
	}

	/**
	 * Callback for the cn_entry_action-vcard action which outputs the "Add to Address Book." link.
	 *
	 * @access  private
	 * @since  0.8
	 * @param  array    $atts  The $atts from self::entryActions() passed by the action callback.
	 * @param  cnOutput $entry An instance of the cnEntry object; passed by the action callback.
	 * @return void
	 */
	public static function entryAction_vCard( $atts, $entry ) {

		$entry->vcard( array( 'return' => false ) );
	}

	/**
	 * Output the current category description.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @param array $atts [optional]
	 * @param array $results [optional]
	 *
	 * @return string
	 */
	public static function categoryDescription( $atts = array(), $results = array() ) {

		$html = '';

		// Check whether the category description should be displayed or not.
		if ( ! cnSettingsAPI::get( 'connections', 'connections_display_results', 'cat_desc' ) ) {

			return $html;
		}

		$defaults = array(
			'before' => '',
			'after'  => '',
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( false !== $current = cnCategory::getCurrent() ) {

			$category = new cnCategory( $current );

			$html = $category->getDescriptionBlock(
				array(
					'return' => true,
				)
			);
		}

		$html = $atts['before'] . $html . $atts['after'] . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Display a message box above the search results with information
	 * about the current query and the option (a button) to clear results.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @param  array           $atts     The shortcode $atts array.
	 * @param  array           $results  The cnRetrieve query results.
	 * @param  cnTemplate|null $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function searchingMessage( $atts = array(), $results = array(), $template = null ) {

		// Check whether the category description should be displayed or not.
		if ( ! cnSettingsAPI::get( 'connections', 'connections_display_results', 'search_message' ) ) {
			return '';
		}

		$html     = '';
		$messages = array();
		$defaults = array(
			'header'        => '',
			'header_tag'    => 'h3',
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'before'        => '',
			'before-item'   => '',
			'after-item'    => '',
			'after'         => '',
			'return'        => false,
		);

		$atts = wp_parse_args( $atts, apply_filters( 'cn_search_results_atts', $defaults ) );

		// Get the directory home page ID.
		$homeID = $atts['force_home'] ? cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) : $atts['home_id'];

		// $addAction = cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) != $atts['home_id'] ? TRUE : FALSE;

		// The base post permalink is required, do not filter the permalink through cnSEO.
		if ( ! is_admin() ) {
			cnSEO::doFilterPermalink( false );
		}

		$permalink = get_permalink( $homeID );

		$permalink = apply_filters( 'cn_permalink', $permalink, $atts );

		// Re-enable the filter.
		if ( ! is_admin() ) {
			cnSEO::doFilterPermalink();
		}

		// Store the query vars.
		$queryVars                    = array();
		$queryVars['cn-s']            = Request\Entry_Search_Term::input()->value() ? esc_html( Request\Entry_Search_Term::input()->value() ) : false;
		$queryVars['cn-char']         = 1 === mb_strlen( Request\Entry_Initial_Character::input()->value() ) ? esc_html( Request\Entry_Initial_Character::input()->value() ) : false;
		$queryVars['cn-cat']          = cnQuery::getVar( 'cn-cat' ) ? cnQuery::getVar( 'cn-cat' ) : false;
		$queryVars['cn-organization'] = cnQuery::getVar( 'cn-organization' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-organization' ) ) ) : false;
		$queryVars['cn-department']   = cnQuery::getVar( 'cn-department' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-department' ) ) ) : false;
		$queryVars['cn-district']     = cnQuery::getVar( 'cn-district' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-district' ) ) ) : false;
		$queryVars['cn-county']       = cnQuery::getVar( 'cn-county' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-county' ) ) ) : false;
		$queryVars['cn-locality']     = cnQuery::getVar( 'cn-locality' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-locality' ) ) ) : false;
		$queryVars['cn-region']       = cnQuery::getVar( 'cn-region' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-region' ) ) ) : false;
		$queryVars['cn-postal-code']  = cnQuery::getVar( 'cn-postal-code' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-postal-code' ) ) ) : false;
		$queryVars['cn-country']      = cnQuery::getVar( 'cn-country' ) ? esc_html( urldecode( cnQuery::getVar( 'cn-country' ) ) ) : false;
		// if ( cnQuery::getVar('cn-near-coord') ) $queryVars['cn-near-coord']     = cnQuery::getVar('cn-near-coord');
		// if ( cnQuery::getVar('cn-radius') ) $queryVars['cn-radius']             = cnQuery::getVar('cn-radius');
		// if ( cnQuery::getVar('cn-unit') ) $queryVars['cn-unit']                 = cnQuery::getVar('cn-unit');

		$messages = apply_filters( 'cn_search_results_messages-before', $messages, $atts, $results, $template );

		if ( $queryVars['cn-cat'] ) {

			$categoryID = $queryVars['cn-cat'];
			$terms      = array();

			// Since the `cn-cat` query var can be an array, we'll only add the category slug
			// template name when querying a single category.
			if ( is_array( $categoryID ) ) {

				foreach ( $categoryID as $id ) {

					$term    = cnTerm::getBy( 'id', $id, 'category' );
					$terms[] = esc_html( $term->name );
				}

			} else {

				$term    = cnTerm::getBy( 'id', $categoryID, 'category' );
				$terms[] = esc_html( $term->name );
			}
			/* translators: Taxonomy term names. */
			$messages['cn-cat'] = sprintf( __( 'You are searching within category(ies): %s', 'connections' ), implode( ', ', $terms ) );
		}

		if ( $queryVars['cn-s'] ) {

			// If value is a string, string the white space and covert to an array.
			if ( ! is_array( $queryVars['cn-s'] ) ) {

				$originalString    = array( $queryVars['cn-s'] );
				$queryVars['cn-s'] = _parse::stringList( $queryVars['cn-s'], '\s' );
				$queryVars['cn-s'] = array_merge( $originalString, $queryVars['cn-s'] );
				$queryVars['cn-s'] = array_unique( $queryVars['cn-s'] );
			}

			// Trim any white space from around the terms in the array.
			array_walk( $queryVars['cn-s'], 'trim' );

			$messages['cn-s'] = sprintf(
				/* translators: Keyword search terms. */
				__( 'You are searching for the keyword(s): %s', 'connections' ),
				esc_html( implode( ', ', $queryVars['cn-s'] ) )
			);
		}

		if ( 1 === mb_strlen( $queryVars['cn-char'] ) ) {

			$messages['cn-char'] = sprintf(
				/* translators: Initial character search term. */
				__( 'The results are being filtered by the character: %s', 'connections' ),
				function_exists( 'mb_substr' ) ? mb_substr( $queryVars['cn-char'], 0, 1 ) : substr( $queryVars['cn-char'], 0, 1 )
			);
		}

		if ( $queryVars['cn-organization'] ) {

			$messages['cn-organization'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the organization: %s', 'connections' ),
				$queryVars['cn-organization']
			);
		}

		if ( $queryVars['cn-department'] ) {

			$messages['cn-department'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the department: %s', 'connections' ),
				$queryVars['cn-department']
			);
		}

		if ( $queryVars['cn-district'] ) {

			$messages['cn-district'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the district: %s', 'connections' ),
				$queryVars['cn-district']
			);
		}

		if ( $queryVars['cn-county'] ) {

			$messages['cn-county'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the county: %s', 'connections' ),
				$queryVars['cn-county']
			);
		}

		if ( $queryVars['cn-locality'] ) {

			$messages['cn-locality'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the locality: %s', 'connections' ),
				$queryVars['cn-locality']
			);
		}

		if ( $queryVars['cn-region'] ) {

			$messages['cn-region'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the region: %s', 'connections' ),
				$queryVars['cn-region']
			);
		}

		if ( $queryVars['cn-postal-code'] ) {

			$messages['cn-postal-code'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the postal code: %s', 'connections' ),
				$queryVars['cn-postal-code']
			);
		}

		if ( $queryVars['cn-country'] ) {

			$messages['cn-country'] = sprintf(
				/* translators: Search term. */
				__( 'The results are being filtered by the country: %s', 'connections' ),
				$queryVars['cn-country']
			);
		}

		$messages = apply_filters( 'cn_search_results_messages-after', $messages, $atts, $results, $template );
		$messages = apply_filters( 'cn_search_results_messages', $messages, $atts, $results, $template );

		if ( ! empty( $messages ) ) {

			if ( 0 < strlen( $atts['header'] ) ) {

				$header = sprintf(
					'<%1$s class="cn-search-message-header">%2$s</%1$s>',
					$atts['header_tag'],
					esc_html( $atts['header'] )
				);

			} else {

				$header = '';
			}

			// Render the HTML <li> items.
			foreach ( $messages as $key => $message ) {

				$html .= sprintf(
					PHP_EOL . "\t" . '<%2$s class="cn-search-message-list-item" id="cn-search-message-list-item-%3$s">%1$s%4$s%5$s</%2$s>',
					$atts['before-item'],
					$atts['item_tag'],
					esc_attr( $key ),
					wp_kses_post( $message ),
					$atts['after-item']
				);
			}

			// Wrap the <li> items in a <ul>.
			$html = sprintf(
				'<%1$s class="cn-search-message-list">%2$s' . PHP_EOL . '</%1$s>',
				$atts['container_tag'],
				$html
			);

			// Add the clear search button.
			$html .= sprintf(
				'<div id="cn-clear-search"><a class="button btn" id="cn-clear-search-button" href="%1$s">%2$s</a></div>' . PHP_EOL,
				esc_url( $permalink ),
				esc_html__( 'Clear Search', 'connections' )
			);

			// Wrap it all in a <div>.
			$html = '<div id="cn-search-messages">' . $header . $html . '</div>' . PHP_EOL;
		}

		$html = $atts['before'] . $html . $atts['after'] . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Outputs the "No Results" message.
	 *
	 * @access public
	 * @since  0.7.6.5
	 * @uses   wp_parse_args()
	 * @uses   apply_filters()
	 * @param  array           $atts    [optional] The shortcode $atts array.
	 * @param  array           $results [optional] The cnRetrieve query results.
	 * @param  cnTemplate|null $template An instance of the cnTemplate object.
	 *
	 * @return string
	 */
	public static function noResults( $atts = array(), $results = array(), $template = null ) {

		if ( ! empty( $results ) ) {
			return '';
		}

		$defaults = array(
			'tag'     => 'p',
			'message' => __( 'No results.', 'connections' ),
			'before'  => '',
			'after'   => '',
			'return'  => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$atts['message'] = apply_filters( 'cn_list_no_result_message', $atts['message'] );

		if ( $template instanceof cnTemplate ) {

			$atts['message'] = apply_filters( 'cn_list_no_result_message-' . $template->getSlug(), $atts['message'] );
		}

		$out = sprintf(
			'<%1$s class="cn-list-no-results">%2$s</%1$s>',
			$atts['tag'],
			esc_html( $atts['message'] )
		);

		$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Outputs entry data JSON encoded in HTML data attribute.
	 * This is an action called by the `cn_action_entry_after` hook.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param array   $atts  Shortcode $atts passed by the `cn_action_entry_after` action hook.
	 * @param cnEntry $entry An instance the cnEntry object.
	 *
	 * @return string
	 */
	public static function JSON( $atts, $entry ) {

		$defaults = array(
			'tag'                => 'div',
			'before'             => '',
			'after'              => '',
			'return'             => false,
			'show_addresses'     => true,
			'show_phone_numbers' => true,
			'show_email'         => true,
			'show_im'            => true,
			'show_social_media'  => true,
			'show_links'         => true,
			'show_dates'         => true,
			'show_bio'           => true,
			'show_notes'         => true,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$data = array(
			'type'           => $entry->getEntryType(),
			'id'             => $entry->getId(),
			'ruid'           => $entry->getRuid(),
			'slug'           => $entry->getSlug(),
			'name'           => array(
				'full'   => $entry->getName( $atts ),
				'prefix' => $entry->getHonorificPrefix(),
				'first'  => $entry->getFirstName(),
				'middle' => $entry->getMiddleName(),
				'last'   => $entry->getLastName(),
				'suffix' => $entry->getHonorificSuffix(),
			),
			'title'          => $entry->getTitle(),
			'organization'   => $entry->getOrganization(),
			'department'     => $entry->getDepartment(),
			'contact_name'   => array(
				'full'  => $entry->getContactName(),
				'first' => $entry->getContactFirstName(),
				'last'  => $entry->getContactLastName(),
			),
			'family_name'    => $entry->getFamilyName(),
			'family_members' => $entry->getFamilyMembers(),
			'categories'     => $entry->getCategory(),
			'meta'           => $entry->getMeta( $atts ),
		);

		if ( $atts['show_addresses'] ) {
			$data['addresses'] = $entry->getAddresses( $atts );
		}

		if ( $atts['show_phone_numbers'] ) {
			$data['phone_numbers'] = $entry->getPhoneNumbers( $atts );
		}

		if ( $atts['show_email'] ) {
			$data['email_addresses'] = $entry->getEmailAddresses( $atts );
		}

		if ( $atts['show_im'] ) {
			$data['im'] = $entry->getIm( $atts );
		}

		if ( $atts['show_social_media'] ) {
			$data['social_media'] = $entry->getSocialMedia( $atts );
		}

		if ( $atts['show_links'] ) {
			$data['links'] = $entry->getLinks( $atts );
		}

		if ( $atts['show_dates'] ) {
			$data['dates'] = $entry->getDates( $atts );
		}

		if ( $atts['show_bio'] ) {
			$data['bio'] = $entry->getBio();
		}

		if ( $atts['show_notes'] ) {
			$data['notes'] = $entry->getNotes();
		}

		$out = sprintf(
			'<%1$s class="cn-entry-data-json" data-entry-data-json=\'%2$s\'></%1$s>',
			$atts['tag'],
			htmlspecialchars( json_encode( $data ), ENT_QUOTES, 'UTF-8' )
		);

		$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The return to top anchor.
	 *
	 * @since 0.7.3
	 *
	 * @param array $atts [optional].
	 *
	 * @return string
	 */
	public static function returnToTop( array $atts = array() ) {

		$defaults = array(
			'tag'    => 'span',
			'href'   => '#cn-top',
			'style'  => array(),
			'title'  => __( 'Return to top.', 'connections' ),
			'text'   => '<img src="' . CN_URL . 'assets/images/uparrow.gif" alt="' . __( 'Return to top.', 'connections' ) . '"/>',
			'before' => '',
			'after'  => '',
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$css = _html::stringifyCSSAttributes( $atts['style'] );

		$anchor = '<a href="' . $atts['href'] . '" title="' . $atts['title'] . '">' . $atts['text'] . '</a>';

		$out = '<' . $atts['tag'] . ' class="cn-return-to-top"' . ( 0 < strlen( $css ) ? " style=\"{$css}\"" : '' ) . '>' . $anchor . '</' . $atts['tag'] . '>';

		$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The last updated message for an entry.
	 *
	 * @since 0.7.6.5
	 *
	 * @param  array $atts [optional]
	 *
	 * @return string
	 */
	public static function updated( $atts = array() ) {
		$out = '';

		$defaults = array(
			'timestamp' => '',
			'tag'       => 'span',
			'style'     => array(),
			'before'    => '',
			'after'     => '',
			'return'    => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// No need to continue if the timestamp was not supplied.
		if ( ! isset( $atts['timestamp'] ) || empty( $atts['timestamp'] ) ) {

			return self::echoOrReturn( $atts['return'], $out );
		}

		$age = absint( current_time( 'timestamp', true ) - strtotime( $atts['timestamp'] ) );

		if ( $age < 657000 ) { // less than one week: red.
			$atts['style']['color'] = 'red';
		} elseif ( $age < 1314000 ) { // one-two weeks: maroon.
			$atts['style']['color'] = 'maroon';
		} elseif ( $age < 2628000 ) { // two weeks to one month: green.
			$atts['style']['color'] = 'green';
		} elseif ( $age < 7884000 ) { // one - three months: blue.
			$atts['style']['color'] = 'blue';
		} elseif ( $age < 15768000 ) { // three to six months: navy.
			$atts['style']['color'] = 'navy';
		} elseif ( $age < 31536000 ) { // six months to a year: black.
			$atts['style']['color'] = 'black';
		} else {      // more than one year: don't show the update age.
			$atts['style']['display'] = 'none';
		}

		$css = _html::stringifyCSSAttributes( $atts['style'] );

		/* translators: Human readable timestamp. */
		$updated = sprintf( __( 'Updated %1$s ago.', 'connections' ), human_time_diff( strtotime( $atts['timestamp'] ), current_time( 'timestamp', true ) ) );

		$out = '<' . _escape::tagName( $atts['tag'] ) . ' class="cn-last-updated" style="' . _escape::css( $css ) . '">' . $updated . '</' . _escape::tagName( $atts['tag'] ) . '>';

		$out = ( empty( $atts['before'] ) ? '' : $atts['before'] ) . $out . ( empty( $atts['after'] ) ? '' : $atts['after'] ) . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Create the search input.
	 *
	 * Accepted option for the $atts property are:
	 *     return (bool) Whether to return or echo the result.
	 *
	 * @since 0.7.3
	 *
	 * @param array{show_label: bool, return: bool} $atts
	 *
	 * @return string
	 */
	public static function search( $atts = array() ) {
		$out = '';

		$defaults = array(
			'show_label' => true,
			'return'     => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$searchValue = Request\Entry_Search_Term::input()->value();

		// Check to see if there is a template file override.
		$part = self::get( 'search', null, array( 'atts' => $atts, 'searchValue' => $searchValue ) );

		// If one was found, lets include it. If not, run the core function.
		if ( $part ) {

			$out .= $part;

		} else {

			$out .= '<span class="cn-search">';
				if ( $atts['show_label'] ) {
					$out .= '<label for="cn-search-input">Search Directory</label>';
				}
				$out .= '<input type="text" id="cn-search-input" name="cn-s" value="' . esc_attr( $searchValue ) . '" placeholder="' . esc_attr__( 'Search', 'connections' ) . '"/>';
				$out .= '<input type="submit" name="" id="cn-search-submit" class="cn-search-button" value="Search Directory" style="text-indent: -9999px;" tabindex="-1" />';
			$out     .= '</span>';

		}

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Outputs a submit button.
	 *
	 * Accepted option for the $atts property are:
	 *     name (string) The input name attribute.
	 *     value (string) The input value attribute.
	 *     return (bool) Whether to return or echo the result.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public static function submit( $atts = array() ) {

		$defaults = array(
			'name'   => '',
			'value'  => __( 'Submit', 'connections' ),
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '<input type="submit" name="' . esc_attr( $atts['name'] ) . '" id="cn-submit" class="button" value="' . esc_attr( $atts['value'] ) . '" tabindex="-1" />';

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Creates the initial character filter control.
	 *
	 * @since 0.7.4
	 *
	 * @param array $atts
	 *
	 * @return string|void
	 */
	public static function index( $atts = array() ) {

		if ( Request::get()->isSearch() ) {

			return;
		}

		$links   = array( PHP_EOL );
		$current = Request\Entry_Initial_Character::input()->value();

		$defaults = array(
			'status'     => array( 'approved' ),
			'visibility' => array(),
			'tag'        => 'div',
			'style'      => array(),
			'return'     => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		$characters = cnRetrieve::getCharacters( $atts );
		// $currentPageURL = add_query_arg( array( 'page' => FALSE , 'cn-action' => 'cn_filter' )  );

		if ( 1 < mb_strlen( $current ) ) {

			$current = '';
		}

		foreach ( $characters as $key => $char ) {
			$char = strtoupper( $char );

			// If we're in the admin, add the nonce to the URL to be verified when settings the current user filter.
			if ( is_admin() ) {

				$links[] = '<a' . ( $current === $char ? ' class="cn-char-current button"' : ' class="cn-char button"' ) . ' href="' . esc_url( _nonce::url( add_query_arg( array( 'cn-char' => urlencode( $char ) ) ), 'filter' ) ) . '">' . $char . '</a> ' . PHP_EOL;

			} else {

				$url = cnURL::permalink(
					array(
						'type'       => 'character',
						'slug'       => $char,
						'title'      => $char,
						'class'      => ( $current === $char ? 'cn-char-current' : 'cn-char' ),
						'text'       => $char,
						'home_id'    => _array::get( $atts, 'home_id', cnShortcode::getHomeID() ),
						'force_home' => _array::get( $atts, 'force_home', false ),
						'return'     => true,
					)
				);

				$links[] = $url . PHP_EOL;
			}

		}

		$css   = _escape::css( _html::stringifyCSSAttributes( $atts['style'] ) );
		$style = 0 < strlen( $css ) ? ' style="' . $css . '"' : '';
		$tag   = _escape::tagName( $atts['tag'] );

		$out = "<{$tag} class=\"cn-alphaindex\"{$style}>" . implode( ' ', $links ) . "</{$tag}>" . PHP_EOL;

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Retrieves the current character and outs a hidden form input.
	 *
	 * @internal
	 * @since  0.7.4
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public static function currentCharacter( $atts = array() ) {
		$out     = '';
		$current = Request\Entry_Initial_Character::input()->value();

		$defaults = array(
			'type'   => 'input', // Reserved for future use. Will define the type of output to render. In this case a form input.
			'hidden' => true,
			'return' => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Only output if there is a current character set in the query string.
		if ( 1 === mb_strlen( $current ) ) {
			$out .= '<input class="cn-current-char-input" name="cn-char" title="' . esc_attr( __( 'Current Character', 'connections' ) ) . '" type="' . ( $atts['hidden'] ? 'hidden' : 'text' ) . '" size="1" value="' . esc_attr( $current ) . '">';
		}

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Creates the pagination controls.
	 *
	 * @access public
	 * @since  0.7.3
	 * @static
	 *
	 * @param array $atts {
	 *     Optional. Array or string of arguments for generating paginated links for archives.
	 *
	 *     @type int    $limit              The number of entries per page to be displayed.
	 *                                      Default: 20
	 *     @type bool   $show_all           Whether to show all pages.
	 *                                      Default: FALSE
	 *     @type int    $end_size           How many numbers on either the start and the end list edges.
	 *                                      Default: 2
	 *     @type int    $mid_size           How many numbers to either side of the current pages.
	 *                                      Default: 2
	 *     @type bool   $prev_next          Whether to include the previous and next links in the list.
	 *                                      Default: TRUE
	 *     @type bool   $prev_text          The previous page text.
	 *                                      Default: '«'
	 *     @type bool   $next_text          The next page text.
	 *                                      Default: '«'
	 *     @type string $add_fragment       A string to append to each link.
	 *                                      Default: empty
	 *     @type string $before_page_number A string to appear before the page number.
	 *                                      Default: empty
	 *     @type string $after_page_number  A string to append after the page number.
	 *                                      Default: empty
	 *     @type bool   $return             Whether to return or echo the pagination control. Set to TRUE to return instead of echo.
	 *                                      Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function pagination( $atts = array() ) {

		/**
		 * @var WP_Rewrite $wp_rewrite
		 * @var WP_Query $wp_query
		 * @var connectionsLoad $connections
		 */
		global $wp_rewrite, $wp_query, $connections;

		// The class.seo.file is only loaded in the frontend; do not attempt to remove the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) {
			cnSEO::doFilterPermalink( false );
		}

		$out = '';

		$translated = __( 'Page', 'connections' ); // Supply translatable string.

		$defaults = array(
			'limit'              => 20,
			'show_all'           => false,
			'end_size'           => 2,
			'mid_size'           => 2,
			'prev_next'          => true,
			'prev_text'          => __( '&laquo;', 'connections' ),
			'next_text'          => __( '&raquo;', 'connections' ),
			'add_fragment'       => '',
			'before_page_number' => '<span class="screen-reader-text">' . $translated . ' </span>',
			'after_page_number'  => '',
			'return'             => false,
		);

		/**
		 * Filter the default attributes array.
		 *
		 * @since 8.5.14
		 */
		$defaults = apply_filters( 'cn_pagination_default_atts', $defaults );

		$atts = wp_parse_args( $atts, $defaults );

		/**
		 * Filter the user supplied attributes.
		 *
		 * @since 8.5.14
		 */
		$atts = apply_filters( 'cn_pagination_atts', $atts );

		$total     = $connections->retrieve->resultCountNoLimit;
		$pageCount = absint( $atts['limit'] ) ? ceil( $total / $atts['limit'] ) : 0;

		if ( $pageCount > 1 ) {

			$current   = 1;
			$queryVars = array();

			// Get page/post permalink.
			// Only slash it when using pretty permalinks.
			$permalink = $wp_rewrite->using_permalinks() ? trailingslashit( get_permalink() ) : get_permalink();

			// Get the settings for the base of each data type to be used in the URL.
			$base = get_option( 'connections_permalink' );

			// Store the query vars.
			if ( Request\Entry_Search_Term::input()->value() ) {
				$queryVars['cn-s'] = urlencode( Request\Entry_Search_Term::input()->value() );
			}

			if ( Request\Entry_Initial_Character::input()->value() ) {
				$queryVars['cn-char'] = urlencode( Request\Entry_Initial_Character::input()->value() );
			}

			if ( cnQuery::getVar( 'cn-cat' ) ) {
				$queryVars['cn-cat'] = cnQuery::getVar( 'cn-cat' );
			}

			if ( cnQuery::getVar( 'cn-organization' ) ) {
				$queryVars['cn-organization'] = cnQuery::getVar( 'cn-organization' );
			}

			if ( cnQuery::getVar( 'cn-department' ) ) {
				$queryVars['cn-department'] = cnQuery::getVar( 'cn-department' );
			}

			if ( cnQuery::getVar( 'cn-locality' ) ) {
				$queryVars['cn-locality'] = cnQuery::getVar( 'cn-locality' );
			}

			if ( cnQuery::getVar( 'cn-region' ) ) {
				$queryVars['cn-region'] = cnQuery::getVar( 'cn-region' );
			}

			if ( cnQuery::getVar( 'cn-postal-code' ) ) {
				$queryVars['cn-postal-code'] = cnQuery::getVar( 'cn-postal-code' );
			}

			if ( cnQuery::getVar( 'cn-country' ) ) {
				$queryVars['cn-country'] = cnQuery::getVar( 'cn-country' );
			}

			if ( cnQuery::getVar( 'cn-near-coord' ) ) {
				$queryVars['cn-near-coord'] = cnQuery::getVar( 'cn-near-coord' );
			}

			if ( cnQuery::getVar( 'cn-radius' ) ) {
				$queryVars['cn-radius'] = cnQuery::getVar( 'cn-radius' );
			}

			if ( cnQuery::getVar( 'cn-unit' ) ) {
				$queryVars['cn-unit'] = cnQuery::getVar( 'cn-unit' );
			}

			if ( is_front_page() && cnQuery::getVar( 'page_id' ) ) {

				$queryVars['page_id'] = cnQuery::getVar( 'page_id' );
			}

			// Current page.
			if ( cnQuery::getVar( 'cn-pg' ) ) {
				$current = absint( cnQuery::getVar( 'cn-pg' ) );
			}

			/*
			 * Create the page permalinks. If on a post or custom post type, use query vars.
			 */
			if ( cnShortcode::isSupportedPostType( get_queried_object() ) && $wp_rewrite->using_permalinks() ) {

				// Add the category base and path if paging through a category.
				if ( cnQuery::getVar( 'cn-cat-slug' ) ) {
					$permalink = trailingslashit( $permalink . $base['category_base'] . '/' . cnQuery::getVar( 'cn-cat-slug' ) );
				}

				// Add the country base and path if paging through a country.
				if ( cnQuery::getVar( 'cn-country' ) ) {

					_array::forget( $queryVars, 'cn-country' );
					$permalink = trailingslashit( $permalink . $base['country_base'] . '/' . cnQuery::getVar( 'cn-country' ) );
				}

				// Add the region base and path if paging through a region.
				if ( cnQuery::getVar( 'cn-region' ) ) {

					_array::forget( $queryVars, 'cn-region' );
					$permalink = trailingslashit( $permalink . $base['region_base'] . '/' . cnQuery::getVar( 'cn-region' ) );
				}

				// Add the postal code base and path if paging through a postal code.
				if ( cnQuery::getVar( 'cn-postal-code' ) ) {

					_array::forget( $queryVars, 'cn-postal-code' );
					$permalink = trailingslashit( $permalink . $base['postal_code_base'] . '/' . cnQuery::getVar( 'cn-postal-code' ) );
				}

				// Add the locality base and path if paging through a locality.
				if ( cnQuery::getVar( 'cn-locality' ) ) {

					_array::forget( $queryVars, 'cn-locality' );
					$permalink = trailingslashit( $permalink . $base['locality_base'] . '/' . cnQuery::getVar( 'cn-locality' ) );
				}

				// Add the organization base and path if paging through an organization.
				if ( cnQuery::getVar( 'cn-organization' ) ) {
					$permalink = trailingslashit( $permalink . $base['organization_base'] . '/' . cnQuery::getVar( 'cn-organization' ) );
				}

				// Add the department base and path if paging through a department.
				if ( cnQuery::getVar( 'cn-department' ) ) {
					$permalink = trailingslashit( $permalink . $base['department_base'] . '/' . cnQuery::getVar( 'cn-department' ) );
				}

				$args = array(
					'base'               => $permalink . '%_%',
					'format'             => user_trailingslashit( 'pg/%#%', 'cn-paged' ),
					'total'              => $pageCount,
					'current'            => $current,
					'show_all'           => $atts['show_all'],
					'end_size'           => $atts['end_size'],
					'mid_size'           => $atts['mid_size'],
					'prev_next'          => $atts['prev_next'],
					'prev_text'          => $atts['prev_text'],
					'next_text'          => $atts['next_text'],
					'type'               => 'array',
					'add_args'           => $queryVars,
					'add_fragment'       => $atts['add_fragment'],
					'before_page_number' => $atts['before_page_number'],
					'after_page_number'  => $atts['after_page_number'],
				);

			} else {

				if ( $wp_rewrite->using_permalinks() ) {

					$atts['format'] = '?cn-pg=%#%';

				} elseif ( isset( $wp_query->query ) && ! empty( $wp_query->query ) && ! is_front_page() ) {

					$atts['format'] = '&cn-pg=%#%';

				} elseif ( isset( $wp_query->query ) && ! empty( $wp_query->query ) && is_front_page() ) {

					$atts['format'] = '?cn-pg=%#%';

				} else {

					$atts['format'] = '?cn-pg=%#%';
				}

				$args = array(
					'base'               => $permalink . '%_%',
					// Ensure the correct format is set based on if there are query vars or not.
					'format'             => $atts['format'],
					'total'              => $pageCount,
					'current'            => $current,
					'show_all'           => $atts['show_all'],
					'end_size'           => $atts['end_size'],
					'mid_size'           => $atts['mid_size'],
					'prev_next'          => $atts['prev_next'],
					'prev_text'          => $atts['prev_text'],
					'next_text'          => $atts['next_text'],
					'type'               => 'array',
					'add_args'           => $queryVars,
					'add_fragment'       => $atts['add_fragment'],
					'before_page_number' => $atts['before_page_number'],
					'after_page_number'  => $atts['after_page_number'],
				);

			}

			$args  = apply_filters( 'cn_pagination_links_args', $args );
			$links = paginate_links( $args );

			$out .= '<span class="cn-page-nav" id="cn-page-nav">';
			$out .= join( PHP_EOL, $links );
			$out .= '</span>';
		}

		// The class.seo.file is only loaded in the frontend; do not attempt to add the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) {
			cnSEO::doFilterPermalink();
		}

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * Render the category breadcrumb.
	 *
	 * @access public
	 * @since  8.5.18
	 * @static
	 *
	 * @param array $atts The attributes array. {
	 *
	 *     @type bool   $link       Whether to format as link or as a string.
	 *                              Default: FALSE
	 *     @type string $separator  How to separate categories.
	 *                              Default: '/'
	 *     @type bool   $force_home Default: FALSE
	 *     @type int    $home_id    Default: The page set as the directory home page.
	 *     @type bool   $return     Whether to return or echo the pagination control. Set to TRUE to return instead of echo.
	 *                              Default: FALSE
	 * }
	 *
	 * @return string A list of category parents on success.
	 */
	public static function categoryBreadcrumb( $atts ) {

		$defaults = array(
			'link'       => false,
			'separator'  => '/',
			'force_home' => false,
			'home_id'    => cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
			'return'     => false,
		);

		$atts = cnSanitize::args( $atts, $defaults );

		$html = '';
		$term = cnCategory::getCurrent();

		if ( $term instanceof Term ) {

			$home = cnURL::permalink(
				array(
					'type'       => 'home',
					'title'      => esc_html__( 'Home', 'connections' ),
					'text'       => esc_html__( 'Home', 'connections' ),
					'force_home' => $atts['force_home'],
					'home_id'    => $atts['home_id'],
					'return'     => true,
				)
			);

			$breadcrumb = getTermParents(
				$term->parent,
				'category',
				array(
					'link'       => $atts['link'],
					'separator'  => $atts['separator'],
					'force_home' => $atts['force_home'],
					'home_id'    => $atts['home_id'],
				)
			);

			if ( is_wp_error( $breadcrumb ) ) {

				$breadcrumb = '';
			}

			// $currentLink = '<a href="' . esc_url( cnTerm::permalink( $current, 'category', $atts ) ) . '">' . $current->name . '</a>';

			$html = '<span class="cn-category-breadcrumb-home">' . $home . $atts['separator'] . '</span>' . $breadcrumb . '<span class="cn-category-breadcrumb-item" id="cn-category-breadcrumb-item-' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</span>';

			$html = '<div class="cn-category-breadcrumb">' . $html . '</div>';
		}

		return self::echoOrReturn( $atts['return'], $html );
	}

	/**
	 * Retrieve category parents with separator.
	 *
	 * NOTE: This is the Connections equivalent of @see get_category_parents() in WordPress core ../wp-includes/category-template.php
	 *
	 * @since 8.5.18
	 * @deprecated 10.3.1
	 *
	 * @param int   $id   Category ID.
	 * @param array $atts The attributes array. {
	 *
	 *     @type bool   $link       Whether to format as link or as a string.
	 *                              Default: FALSE
	 *     @type string $separator  How to separate categories.
	 *                              Default: '/'
	 *     @type bool   $nicename   Whether to use nice name for display.
	 *                              Default: FALSE
	 *     @type array  $visited    Already linked to categories to prevent duplicates.
	 *                              Default: array()
	 *     @type bool   $force_home Default: FALSE
	 *     @type int    $home_id    Default: The page set as the directory home page.
	 * }
	 *
	 * @return string|WP_Error A list of category parents on success, WP_Error on failure.
	 */
	public static function getCategoryParents( $id, $atts = array() ) {

		_deprecated_function( __METHOD__, '10.3.1', '\Connections_Directory\Taxonomy\Partial\getTermParents()' );

		return getTermParents( $id, 'category', $atts );
	}

	/**
	 * Parent public function that outputs the various categories output formats.
	 *
	 * Accepted option for the $atts property are:
	 *    type (string) The output type of the categories. Valid options are: select || multiselect || radio || checkbox
	 *    group (bool) Whether to create option groups using the root parent as the group label. Used for select && multiselect only.
	 *    default (string) The default string to show as the first item in the list. Used for select && multiselect only.
	 *    show_select_all (bool) Whether to show the "Select All" option. Used for select && multiselect only.
	 *    select_all (string) The string to use for the "Select All" option. Used for select && multiselect only.
	 *    show_empty (bool) Whether to display empty categories.
	 *    show_count (bool) Whether to display the category count.
	 *    depth (int) The number of levels deep to show categories. Setting to 0 will show all levels.
	 *    parent_id (array) An array of root parent category IDs to limit the list to.
	 *    return (bool) Whether to return or echo the result.
	 *
	 * NOTE: The $atts array is passed to a number of private methods to output the categories.
	 *
	 * @access  public
	 * @version 1.0
	 * @since   0.7.3
	 * @uses    wp_parse_args()
	 *
	 * @param array $atts [optional]
	 * @param array $value [optional] An indexed array of category ID/s that should be marked as "SELECTED".
	 *
	 * @return string
	 */
	public static function category( $atts = array(), $value = array() ) {

		$defaults = array(
			'type'            => 'select',
			'group'           => false,
			'default'         => __( 'Select Category', 'connections' ),
			'show_select_all' => true,
			'select_all'      => __( 'Show All Categories', 'connections' ),
			'show_empty'      => true,
			'show_count'      => false,
			'depth'           => 0,
			'parent_id'       => array(),
			'exclude'         => array(),
			'return'          => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		switch ( $atts['type'] ) {

			case 'select':
				$out = self::categorySelect( $atts, $value );
				break;

			case 'multiselect':
				$out = self::categorySelect( $atts, $value );
				break;

			case 'radio':
				if ( isset( $atts['layout'] ) && 'table' == $atts['layout'] ) {

					$out = self::categoryInput( $atts );

				} else {

					$out = self::categoryRadioGroup( $atts, $value );
				}

				break;

			case 'checkbox':
				if ( isset( $atts['layout'] ) && 'table' == $atts['layout'] ) {

					$out = self::categoryInput( $atts );

				} else {

					$out = self::categoryChecklist( $atts );
				}

				break;

			case 'link':
				if ( isset( $atts['layout'] ) && 'table' == $atts['layout'] ) {

					$out = self::categoryLink( $atts );

				} else {

					// For backwards compatibility.
					$atts['child_of']   = isset( $atts['parent_id'] ) && ! empty( $atts['parent_id'] ) ? $atts['parent_id'] : 0;
					$atts['hide_empty'] = isset( $atts['show_empty'] ) && false === $atts['show_empty'] ? true : false;

					$out = cnTemplatePart::walker( 'term-list', $atts );
				}

				break;

			default:
				$out = '';
				break;
		}

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The private function called by cnTemplate::category that outputs the select, multiselect; grouped and ungrouped.
	 *
	 * Accepted option for the $atts property are:
	 *     type (string) The output type of the categories. Valid options are: select || multiselect
	 *     group (bool) Whether to create option groups using the root parent as the group label. Used for select && multiselect only.
	 *     default (string) The default string to show as the first item in the list. Used for select && multiselect only.
	 *     show_select_all (bool) Whether to show the "Select All" option. Used for select && multiselect only.
	 *     select_all (string) The string to use for the "Select All" option. Used for select && multiselect only.
	 *     show_empty (bool) Whether to display empty categories.
	 *     show_count (bool) Whether to display the category count.
	 *     depth (int) The number of levels deep to show categories. Setting to 0 will show all levels.
	 *     parent_id (array) An array of root parent category IDs to limit the list to.
	 *     return (bool) Whether to return or echo the result.
	 *
	 * @access  private
	 * @version 1.0
	 * @since   0.7.3
	 *
	 * @param array $atts
	 * @param array $value [optional] An indexed array of category ID/s that should be marked as "SELECTED".
	 *
	 * @return string
	 */
	private static function categorySelect( $atts, $value = array() ) {

		if ( empty( $value ) ) {

			if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

				$slug = explode( '/', cnQuery::getVar( 'cn-cat-slug' ) );

				// If the category slug is a descendant, use the last slug from the URL for the query.
				$selected = end( $slug );

			} elseif ( cnQuery::getVar( 'cn-cat' ) ) {

				// If value is a string, strip the white space and covert to an array.
				$selected = wp_parse_id_list( cnQuery::getVar( 'cn-cat' ) );

			} else {

				$selected = 0;
			}

		} else {

			$selected = $value;
		}

		$defaults = array(
			'on_change' => 'this.form.submit()',
			'selected'  => $selected,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// For backwards compatibility.
		$atts['show_option_all'] = isset( $atts['select_all'] ) && ! empty( $atts['select_all'] ) ? $atts['select_all'] : '';
		$atts['hide_empty']      = isset( $atts['show_empty'] ) && false === $atts['show_empty'] ? true : false;

		return cnTemplatePart::walker( 'term-select-enhanced', $atts );
	}

	/**
	 * The private function called by cnTemplate::category that outputs a term radio group list.
	 * Each category root parent and its descendants are output in an unordered list.
	 *
	 * @access  private
	 * @since   8.2.4
	 * @static
	 *
	 * @uses cnQuery::getVar()
	 * @uses wp_parse_id_list()
	 * @uses wp_parse_args()
	 * @uses cnTemplatePart::walker()
	 *
	 * @param array $atts  {
	 *     Optional. An array of arguments @see CN_Walker_Term_Radio_Group::render().
	 *     NOTE: Additionally, all valid options as supported in @see cnTerm::getTaxonomyTerms().
	 * }
	 * @param array $value An index array containing either the term ID/s or term slugs which are CHECKED.
	 *
	 * @return false|string
	 */
	private static function categoryRadioGroup( $atts, $value = array() ) {

		if ( empty( $value ) ) {

			if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

				$slug = explode( '/', cnQuery::getVar( 'cn-cat-slug' ) );

				// If the category slug is a descendant, use the last slug from the URL for the query.
				$selected = end( $slug );

			} elseif ( cnQuery::getVar( 'cn-cat' ) ) {

				// If value is a string, strip the white space and covert to an array.
				$selected = wp_parse_id_list( cnQuery::getVar( 'cn-cat' ) );

			} else {

				$selected = 0;
			}

		} else {

			$selected = $value;
		}

		$defaults = array(
			'selected' => $selected,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// For backwards compatibility.
		$atts['hide_empty'] = isset( $atts['show_empty'] ) && false === $atts['show_empty'] ? true : false;

		return cnTemplatePart::walker( 'term-radio-group', $atts );
	}

	/**
	 * The private function called by cnTemplate::category that outputs a term checklist.
	 * Each category root parent and its descendants are output in an unordered list.
	 *
	 * @since 8.2.4
	 *
	 * @param array $atts  {
	 *     Optional. An array of arguments @see CN_Walker_Term_Check_List::render().
	 *     NOTE: Additionally, all valid options as supported in @see cnTerm::getTaxonomyTerms().
	 * }
	 * @param array $value An index array containing either the term ID/s or term slugs which are CHECKED.
	 *
	 * @return false|string
	 */
	private static function categoryChecklist( $atts, $value = array() ) {

		if ( empty( $value ) ) {

			if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

				$slug = explode( '/', cnQuery::getVar( 'cn-cat-slug' ) );

				// If the category slug is a descendant, use the last slug from the URL for the query.
				$selected = end( $slug );

			} elseif ( cnQuery::getVar( 'cn-cat' ) ) {

				// If value is a string, strip the white space and covert to an array.
				$selected = wp_parse_id_list( cnQuery::getVar( 'cn-cat' ) );

			} else {

				$selected = 0;
			}

		} else {

			$selected = $value;
		}

		$defaults = array(
			'selected' => $selected,
		);

		$atts = wp_parse_args( $atts, $defaults );

		return cnTemplatePart::walker( 'term-checklist', $atts );
	}

	/**
	 * The private function called by cnTemplate::category that outputs the radio && checkbox in a table layout.
	 * Each category root parent and its descendants are output in an unordered list.
	 *
	 * Accepted option for the $atts property are:
	 *     type (string) The output type of the categories. Valid options are: select || multiselect
	 *     show_empty (bool) Whether to display empty categories.
	 *     show_count (bool) Whether to display the category count.
	 *     depth (int) The number of levels deep to show categories. Setting to 0 will show all levels.
	 *     parent_id (array) An array of root parent category IDs to limit the list to.
	 *     layout (string) The layout to be used for rendering the categories. Valid options are: list || table
	 *     columns (int) The number of columns in the table.
	 *     return (bool) Whether to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses cnQuery::getVar()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private static function categoryInput( $atts = array() ) {
		global $connections;

		$selected = ( cnQuery::getVar( 'cn-cat' ) ) ? cnQuery::getVar( 'cn-cat' ) : array();
		$level    = 0;
		$out      = '';
		$trClass  = 'alternate';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'type'       => 'radio',
			'show_empty' => true,
			'show_count' => true,
			'depth'      => 0,
			'parent_id'  => array(),
			'exclude'    => array(),
			'layout'     => 'list',
			'columns'    => 3,
			'return'     => false,
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
			if ( ! $atts['show_empty'] && ( empty( $category->count ) && empty( $category->children ) ) ) {
				unset( $categories[ $key ] );
			}

			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) {
				unset( $categories[ $key ] );
			}

			// Do not show the excluded category as options.
			if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) {
				unset( $categories[ $key ] );
			}
		}

		// Build the table grid.
		$table = array();
		$rows  = ceil( count( $categories ) / $atts['columns'] );
		$keys  = array_keys( $categories );

		for ( $row = 1; $row <= $rows; $row++ ) {
			for ( $col = 1; $col <= $atts['columns']; $col++ ) {
				$table[ $row ][ $col ] = array_shift( $keys );
			}
		}

		$out .= '<table cellspacing="0" cellpadding="0" class="cn-cat-table">';
		$out .= '<tbody>';

		foreach ( $table as $row => $cols ) {

			$trClass = ( 'alternate' === $trClass ) ? '' : 'alternate';

			$out .= '<tr' . ( $trClass ? ' class="' . $trClass . '"' : '' ) . '>';

			foreach ( $cols as $col => $key ) {

				// When building the table grid, NULL will be the result of the array_shift when it runs out of $keys.
				if ( null === $key ) {
					continue;
				}

				$tdClass = array( 'cn-cat-td' );

				if ( 1 == $row ) {
					$tdClass[] = '-top';
				}

				if ( $row == $rows ) {
					$tdClass[] = '-bottom';
				}

				if ( 1 == $col ) {
					$tdClass[] = '-left';
				}

				if ( $col == $atts['columns'] ) {
					$tdClass[] = '-right';
				}

				$out .= '<td class="' . implode( '', $tdClass ) . '" style="width: ' . floor( 100 / $atts['columns'] ) . '%">';

				$out .= '<ul class="cn-cat-tree">';

				$out .= self::categoryInputOption( $categories[ $key ], $level + 1, $atts['depth'], $selected, $atts );

				$out .= '</ul>';

				$out .= '</td>';
			}

			$out .= '</tr>';
		}

		$out .= '</tbody>';
		$out .= '</table>';

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The private recursive function to build the list item.
	 *
	 * Accepted option for the $atts property are:
	 *     type (string)
	 *     show_empty (bool) Whether to display empty categories.
	 *     show_count (bool) Whether to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int    $level    The current category level.
	 * @param int    $depth    The depth limit.
	 * @param array  $selected An array of the selected category IDs.
	 * @param array  $atts
	 *
	 * @return string
	 */
	private static function categoryInputOption( $category, $level, $depth, $selected, $atts ) {

		$out = '';

		$defaults = array(
			'type'       => 'radio',
			'show_empty' => true,
			'show_count' => true,
			'exclude'    => array(),
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Do not show the excluded category as options.
		if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) {
			return $out;
		}

		if ( $atts['show_empty'] || ! empty( $category->count ) || ! empty( $category->children ) ) {

			$count = ( $atts['show_count'] ) ? ' (' . $category->count . ')' : '';

			$out .= '<li class="cn-cat-parent">';

			$out .= sprintf( '<input type="%1$s" class="cn-radio" id="%2$s" name="cn-cat" value="%3$s" %4$s/>', $atts['type'], $category->slug, $category->term_id, checked( $selected, $category->term_id, false ) );
			$out .= sprintf( '<label for="%1$s"> %2$s</label>', $category->slug, $category->name . $count );

			/*
			 * Only show the descendants based on the following criteria:
			 *  - There are descendant categories.
			 *  - The descendant depth is < than the current $level
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
	 *     show_empty (bool) Whether to display empty categories.
	 *     show_count (bool) Whether to display the category count.
	 *     depth (int) The number of levels deep to show categories. Setting to 0 will show all levels.
	 *     parent_id (array) An array of root parent category IDs to limit the list to.
	 *     layout (string) The layout to be used for rendering the categories. Valid options are: list || table
	 *     columns (int) The number of columns in the table.
	 *     return (bool) Whether to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses cnQuery::getVar()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private static function categoryLink( $atts = array() ) {
		global $connections;

		$level   = 0;
		$out     = '';
		$trClass = 'alternate';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'show_empty' => true,
			'show_count' => true,
			'depth'      => 0,
			'parent_id'  => array(),
			'exclude'    => array(),
			'layout'     => 'list',
			'columns'    => 3,
			'return'     => false,
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
			if ( ! $atts['show_empty'] && ( empty( $category->count ) && empty( $category->children ) ) ) {
				unset( $categories[ $key ] );
			}

			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) {
				unset( $categories[ $key ] );
			}

			// Do not show the excluded category as options.
			if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) {
				unset( $categories[ $key ] );
			}
		}

		// Build the table grid.
		$table = array();
		$rows  = ceil( count( $categories ) / $atts['columns'] );
		$keys  = array_keys( $categories );
		for ( $row = 1; $row <= $rows; $row++ ) {
			for ( $col = 1; $col <= $atts['columns']; $col++ ) {
				$table[ $row ][ $col ] = array_shift( $keys );
			}
		}

		$out .= '<table cellspacing="0" cellpadding="0" class="cn-cat-table">';
		$out .= '<tbody>';

		foreach ( $table as $row => $cols ) {
			$trClass = ( 'alternate' === $trClass ) ? '' : 'alternate';

			$out .= '<tr' . ( $trClass ? ' class="' . $trClass . '"' : '' ) . '>';

			foreach ( $cols as $col => $key ) {
				// When building the table grid, NULL will be the result of the array_shift when it runs out of $keys.
				if ( null === $key ) {
					continue;
				}

				$tdClass = array( 'cn-cat-td' );

				if ( 1 == $row ) {
					$tdClass[] = '-top';
				}

				if ( $row == $rows ) {
					$tdClass[] = '-bottom';
				}

				if ( 1 == $col ) {
					$tdClass[] = '-left';
				}

				if ( $col == $atts['columns'] ) {
					$tdClass[] = '-right';
				}

				$out .= '<td class="' . implode( '', $tdClass ) . '" style="width: ' . floor( 100 / $atts['columns'] ) . '%">';

				$out .= '<ul class="cn-cat-tree">';

				$out .= self::categoryLinkDescendant( $categories[ $key ], $level + 1, $atts['depth'], array(), $atts );

				$out .= '</ul>';

				$out .= '</td>';
			}

			$out .= '</tr>';
		}

		$out .= '</tbody>';
		$out .= '</table>';

		return self::echoOrReturn( $atts['return'], $out );
	}

	/**
	 * The private recursive function to build the category link item.
	 *
	 * Accepted option for the $atts property are:
	 *     type (string)
	 *     show_empty (bool) Whether to display empty categories.
	 *     show_count (bool) Whether to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int    $level    The current category level.
	 * @param int    $depth    The depth limit.
	 * @param array  $slug     An array of the category slugs to be used to build the permalink.
	 * @param array  $atts
	 *
	 * @return string
	 */
	private static function categoryLinkDescendant( $category, $level, $depth, $slug, $atts ) {

		/**
		 * @var WP_Rewrite $wp_rewrite
		 * @var connectionsLoad $connections
		 */
		global $wp_rewrite;

		$out = '';

		$defaults = array(
			'show_empty' => true,
			'show_count' => true,
			'exclude'    => array(),
			'force_home' => false,
			'home_id'    => cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Do not show the excluded category as options.
		if ( ! empty( $atts['exclude'] ) && in_array( $category->term_id, $atts['exclude'] ) ) {
			return $out;
		}

		if ( $atts['show_empty'] || ! empty( $category->count ) || ! empty( $category->children ) ) {

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
			if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

				// Category slug.
				$queryCategorySlug = cnQuery::getVar( 'cn-cat-slug' );
				if ( ! empty( $queryCategorySlug ) ) {
					// If the category slug is a descendant, use the last slug from the URL for the query.
					$queryCategorySlug = explode( '/', $queryCategorySlug );

					if ( isset( $queryCategorySlug[ count( $queryCategorySlug ) - 1 ] ) ) {
						$currentCategory = $queryCategorySlug[ count( $queryCategorySlug ) - 1 ];
					}
				}

			} elseif ( cnQuery::getVar( 'cn-cat' ) ) {

				$currentCategory = cnQuery::getVar( 'cn-cat' );

			} else {

				$currentCategory = '';

			}

			$out .= '<li class="cat-item cat-item-' . $category->term_id . ( $currentCategory == $category->slug || $currentCategory == $category->term_id ? ' current-cat' : '' ) . ' cn-cat-parent">';

			// Create the permalink anchor.
			$out .= cnURL::permalink(
				array(
					'type'       => 'category',
					'slug'       => implode( '/', $slug ),
					'title'      => $category->name,
					'text'       => $category->name . $count,
					'home_id'    => $atts['home_id'],
					'force_home' => $atts['force_home'],
					'return'     => true,
				)
			);

			/*
			 * Only show the descendants based on the following criteria:
			 *  - There are descendant categories.
			 *  - The descendant depth is < than the current $level
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
