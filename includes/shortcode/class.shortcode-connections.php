<?php

use Connections_Directory\Request;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_sanitize;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core [connections] shortcode.
 *
 * @package     Connections
 * @subpackage  Shortcode API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

class cnShortcode_Connections {

	public static function add() {

		/*
		 * Do not register the shortcode when doing ajax requests.
		 * This is primarily implemented so the shortcodes are not run during Yoast SEO page score admin ajax requests.
		 * The page score can cause the ajax request to fail and/or prevent the page from saving when page score is
		 * being calculated on the output from the shortcode.
		 */
		if ( ! Request::get()->isAjax() ) {

			add_shortcode( 'connections', array( __CLASS__, 'view' ) );
		}
	}

	/**
	 * Callback for the `[connections]` shortcode.
	 *
	 * Display results based on query var `cn-view`.
	 *
	 * @internal
	 * @since 0.7.3
	 * @since 10.4.40 Moved from {@file class.shortcode.php}.
	 *
	 * @param array|string $atts    Shortcode attributes array or empty string.
	 * @param string|null  $content The content of a shortcode when it wraps some content.
	 * @param string       $tag     Shortcode name.
	 *
	 * @return string
	 */
	public static function view( $atts, $content = '', $tag = 'connections' ) {

		// Ensure that the $atts var passed from WordPress is an array.
		if ( ! is_array( $atts ) ) {
			$atts = (array) $atts;
		}

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * Only show this message under the following condition:
		 * - ( The user is not logged in AND the 'Login Required' is checked ) AND ( neither of the shortcode visibility overrides are enabled ).
		 */
		if ( ( ! is_user_logged_in() && ! $instance->options->getAllowPublic() ) && ! ( $instance->options->getAllowPublicOverride() || $instance->options->getAllowPrivateOverride() ) ) {
			$message = $instance->settings->get( 'connections', 'connections_login', 'message' );

			// Format and texturize the message.
			$message = wptexturize( wpautop( $message ) );

			// Make any links and such clickable.
			$message = make_clickable( $message );

			// Apply the shortcodes.
			$message = do_shortcode( $message );

			return $message;
		}

		$view = cnQuery::getVar( 'cn-view' );

		switch ( $view ) {

			case 'submit':
				if ( has_action( 'cn_submit_entry_form' ) ) {

					ob_start();

					/**
					 * @todo There s/b capability checks just like when editing an entry so users can only submit when they have the permissions.
					 */
					do_action( 'cn_submit_entry_form', $atts, $content, $tag );

					return ob_get_clean();

				} else {

					return '<p>' . esc_html__( 'Future home of front end submissions.', 'connections' ) . '</p>';
				}

			case 'landing':
				return '<p>' . esc_html__( 'Future home of the landing pages, such a list of categories.', 'connections' ) . '</p>';

			case 'search':
				if ( has_action( 'Connections_Directory/Shortcode/View/Search' ) ) {

					ob_start();

					do_action( 'Connections_Directory/Shortcode/View/Search', $atts, $content, $tag );

					return ob_get_clean();

				} else {

					return '<p>' . esc_html__( 'Future home of the search page.', 'connections' ) . '</p>';
				}

			case 'results':
				if ( has_action( 'cn_submit_search_results' ) ) {

					ob_start();

					do_action( 'cn_submit_search_results', $atts, $content, $tag );

					return ob_get_clean();

				} else {

					return '<p>' . esc_html__( 'Future home of the search results landing page.', 'connections' ) . '</p>';
				}

			// Show the standard result list.
			case 'card':
				return self::shortcode( $atts, $content );

			// Show the "View All" result list using the "Names" template.
			case 'all':
				if ( ! is_array( $atts ) ) {
					$atts = array();
				}

				// Disable the output of the repeat character index.
				cnArray::set( $atts, 'repeat_alphaindex', false );

				// Force the use of the Names template.
				cnArray::set( $atts, 'template', 'names' );

				return self::shortcode( $atts, $content );

			// Show the entry detail using a template based on the entry type.
			case 'detail':
				switch ( cnQuery::getVar( 'cn-process' ) ) {

					case 'edit':
						if ( has_action( 'cn_edit_entry_form' ) ) {

							// Check to see if the entry has been linked to a user ID.
							$entryID = get_user_meta( get_current_user_id(), 'connections_entry_id', true );
							// var_dump( $entryID );

							$results = $instance->retrieve->entries( array( 'status' => 'approved,pending' ) );
							// var_dump( $results );

							/*
							 * The `cn_edit_entry_form` action should only be executed if the user is
							 * logged in, and they have the `connections_manage` capability and either the
							 * `connections_edit_entry` or `connections_edit_entry_moderated` capability.
							 */

							if ( is_user_logged_in() &&
								( current_user_can( 'connections_manage' ) || ( (int) $entryID == (int) $results[0]->id ) ) &&
								( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
								) {

								ob_start();

								if ( ! current_user_can( 'connections_edit_entry' ) && 'pending' === $results[0]->status ) {

									echo '<p>' . esc_html__( 'Your entry submission is currently under review, however, you can continue to make edits to your entry submission while your submission is under review.', 'connections' ) . '</p>';
								}

								do_action( 'cn_edit_entry_form', $atts, $content, $tag );

								return ob_get_clean();

							} else {

								return esc_html__( 'You are not authorized to edit entries. Please contact the admin if you received this message in error.', 'connections' );
							}

						}

						break;

					default:
						// Ensure an array is passed the cnRetrieve::entries method.
						// if ( ! is_array( $atts ) ) $atts = (array) $atts;

						$results = $instance->retrieve->entries( $atts );
						// var_dump($results);

						$atts['list_type'] = $instance->settings->get( 'connections', 'connections_display_single', 'template' ) ? $results[0]->entry_type : null;

						return self::shortcode( $atts, $content );
				}

				break;

			// Show the standard result list.
			default:
				// return self::shortcode( $atts, $content );

				if ( has_action( "cn_view_$view" ) ) {

					ob_start();

					do_action( "cn_view_$view", $atts, $content, $tag );

					return ob_get_clean();
				}

				break;
		}

		return self::shortcode( $atts, $content );
	}

	/**
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content = '', $tag = 'connections' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$html = '';

		if ( is_customize_preview() ) {

			/**
			 * Hook to allow the active template to be overridden and set to the current template being customized.
			 *
			 * @since 8.4
			 *
			 * @param array $atts {
			 *     @type string $template The template slug of the template being customized.
			 * }
			 */
			$atts = apply_filters( 'cn_template_customizer_template', $atts );
		}

		/** @var cnTemplate $template */
		$template = cnTemplateFactory::loadTemplate( $atts );

		if ( false === $template ) {
			return cnTemplatePart::loadTemplateError( $atts );
		}

		/*
		 * This filter adds the current template paths to cnLocate so when template
		 * part file overrides are being searched for, it'll also search in template
		 * specific paths. This filter is then removed at the end of the shortcode.
		 */
		add_filter( 'cn_locate_file_paths', array( $template, 'templatePaths' ) );
		cnShortcode::addFilterRegistry( 'cn_locate_file_paths' );

		/**
		 * @todo Move to to {@see cnTemplateFactory::loadTemplate()}???
		 *       Note: These same actions are also in the [upcoming_list] and [cn-entry] shortcodes.
		 */
		do_action( 'cn_template_include_once-' . $template->getSlug() );
		do_action( 'cn_template_enqueue_js-' . $template->getSlug() );

		/*
		 * Now that the template has been loaded, Validate the user supplied shortcode atts.
		 */
		$defaults = array(
			'id'                    => null,
			'slug'                  => null,
			'category'              => '',
			'category_in'           => '',
			'exclude_category'      => '',
			'category_name'         => '',
			'category_slug'         => '',
			'allow_public_override' => false,
			'private_override'      => false,
			'show_alphaindex'       => cnSettingsAPI::get( 'connections', 'display_results', 'index' ),
			'repeat_alphaindex'     => cnSettingsAPI::get( 'connections', 'display_results', 'index_repeat' ),
			'show_alphahead'        => cnSettingsAPI::get( 'connections', 'display_results', 'show_current_character' ),
			'list_type'             => null,
			'order_by'              => '',
			'limit'                 => null,
			'offset'                => null,
			'family_name'           => '',
			'last_name'             => '',
			'title'                 => '',
			'organization'          => '',
			'department'            => '',
			'district'              => null,
			'county'                => null,
			'city'                  => '',
			'state'                 => '',
			'zip_code'              => '',
			'country'               => '',
			'meta_query'            => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'content'               => '', // @todo Unused needs remove after all templates are updated to remove it.
			'near_addr'             => null,
			'latitude'              => null,
			'longitude'             => null,
			'radius'                => 10,
			'unit'                  => 'mi',
			'template'              => null, /* @since version 0.7.1.0 */
			'width'                 => null,
			'lock'                  => false,
			'force_home'            => false,
			'home_id'               => cnShortcode::getHomeID(),
		);

		$defaults = apply_filters( 'cn_list_atts_permitted', $defaults );
		$defaults = apply_filters( 'cn_list_atts_permitted-' . $template->getSlug(), $defaults );

		$atts = shortcode_atts( $defaults, $atts, $tag );

		$atts = apply_filters( 'cn_list_atts', $atts );
		$atts = apply_filters( 'cn_list_atts-' . $template->getSlug(), $atts );

		/*
		 * Convert some $atts values in the array to boolean.
		 */
		_format::toBoolean( $atts['allow_public_override'] );
		_format::toBoolean( $atts['private_override'] );
		_format::toBoolean( $atts['show_alphaindex'] );
		_format::toBoolean( $atts['repeat_alphaindex'] );
		_format::toBoolean( $atts['show_alphahead'] );
		_format::toBoolean( $atts['lock'] );
		_format::toBoolean( $atts['force_home'] );

		/*
		 * Sanitize integer values.
		 */
		$atts['width'] = _sanitize::integer( $atts['width'] );

		/*
		 * The post editor entity encodes the post text we have to decode it
		 * so a match can be made when the query is run.
		 */
		$atts['family_name']   = html_entity_decode( $atts['family_name'] );
		$atts['last_name']     = is_array( $atts['last_name'] ) ? array_map( 'html_entity_decode', $atts['last_name'] ) : html_entity_decode( $atts['last_name'] );
		$atts['title']         = is_array( $atts['title'] ) ? array_map( 'html_entity_decode', $atts['title'] ) : html_entity_decode( $atts['title'] );
		$atts['organization']  = is_array( $atts['organization'] ) ? array_map( 'html_entity_decode', $atts['organization'] ) : html_entity_decode( $atts['organization'] );
		$atts['department']    = is_array( $atts['department'] ) ? array_map( 'html_entity_decode', $atts['department'] ) : html_entity_decode( $atts['department'] );
		$atts['city']          = is_array( $atts['city'] ) ? array_map( 'html_entity_decode', $atts['city'] ) : html_entity_decode( $atts['city'] );
		$atts['state']         = is_array( $atts['state'] ) ? array_map( 'html_entity_decode', $atts['state'] ) : html_entity_decode( $atts['state'] );
		$atts['zip_code']      = is_array( $atts['zip_code'] ) ? array_map( 'html_entity_decode', $atts['zip_code'] ) : html_entity_decode( $atts['zip_code'] );
		$atts['country']       = is_array( $atts['country'] ) ? array_map( 'html_entity_decode', $atts['country'] ) : html_entity_decode( $atts['country'] );
		$atts['category_name'] = html_entity_decode( $atts['category_name'] );

		if ( 0 < strlen( $atts['meta_query'] ) ) {

			// The meta query syntax follows the JSON standard, except, the WordPress Shortcode API does not allow
			// brackets within shortcode options, so parenthesis have to be used instead, so, lets swap them
			// that was json_decode can be run and the resulting array used in cnRetrieve::entries().
			$atts['meta_query'] = str_replace( array( '(', ')' ), array( '[', ']' ), $atts['meta_query'] );

			$metaQuery = _::maybeJSONdecode( $atts['meta_query'] );

			$atts['meta_query'] = is_array( $metaQuery ) ? $metaQuery : array();
		}

		$atts = apply_filters( 'cn_list_retrieve_atts', $atts );
		$atts = apply_filters( 'cn_list_retrieve_atts-' . $template->getSlug(), $atts );

		$results = $instance->retrieve->entries( $atts );
		// $html .= print_r( $instance->lastQuery, TRUE );

		// Apply any registered filters to the results.
		if ( ! empty( $results ) ) {

			$results = apply_filters( 'cn_list_results', $results );
			$results = apply_filters( 'cn_list_results-' . $template->getSlug(), $results );
			cnShortcode::addFilterRegistry( 'cn_list_results-' . $template->getSlug() );
		}

		$class    = array( 'cn-template', "cn-{$template->getSlug()}" );
		$isSingle = cnQuery::getVar( 'cn-entry-slug' ) ? true : false;

		if ( $isSingle ) {

			array_push( $class, 'cn-template-is-single' );
		}

		ob_start();

			// Prints the template's CSS file.
			// NOTE: This is primarily to support legacy templates which included a CSS
			// file which was not enqueued in the page header.
			do_action( 'cn_template_inline_css-' . $template->getSlug(), $atts );

			// The return to top anchor.
			do_action( 'cn_list_return_to_target', $atts );

		$html .= ob_get_clean();

		$html .= sprintf(
			'<div class="cn-list" id="cn-list" data-connections-version="%1$s-%2$s"%3$s>',
			esc_attr( $instance->options->getVersion() ),
			esc_attr( $instance->options->getDBVersion() ),
			empty( $atts['width'] ) ? '' : ' style="' . _escape::css( "width: {$atts['width']}px;" ) . '"'
		);

		$html .= sprintf(
			'<div class="%1$s" id="cn-%2$s" data-template-version="%3$s">',
			_escape::classNames( $class ),
			esc_attr( $template->getSlug() ),
			esc_attr( $template->getVersion() )
		);

		// The filter should check $content that content is not empty before processing $content.
		// And if it is empty the filter should return (bool) FALSE, so the core template parts can be executed.
		$content = apply_filters( "cn_shortcode_content-$tag", false, $content, $atts, $results, $template );

		if ( false === $content ) {

			ob_start();

			// Render the core result list header.
			cnTemplatePart::header( $atts, $results, $template );

			// Render the core result list body.
			cnTemplatePart::body( $atts, $results, $template );

			// Render the core result list footer.
			cnTemplatePart::footer( $atts, $results, $template );

			$html .= ob_get_clean();

		} else {

			$html .= $content;
		}

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-' . esc_attr( $template->getSlug() ) . ' -->' : '' ) . PHP_EOL;

		$html .= PHP_EOL . '</div>' . ( WP_DEBUG ? '<!-- END #cn-list -->' : '' ) . PHP_EOL;

		// Clear any filters that have been added.
		// This allows support using the shortcode multiple times on the same page.
		cnShortcode::clearFilterRegistry();

		// @todo This should be run via a filter.
		return cnShortcode::removeEOL( $html );
	}
}
