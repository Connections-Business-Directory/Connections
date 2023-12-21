<?php
namespace Connections_Directory\Shortcode;

use Connections_Directory\Request;
use Connections_Directory\Taxonomy\Term;
use cnQuery;
use cnRetrieve;
use cnRewrite;
use cnSettingsAPI;
use cnTerm;
use WP_Post;
use WP_User;

/**
 * Class Conditional_content
 *
 * @package Connections_Directory\Shortcode
 */
class Conditional_Content {

	/**
	 * @since 9.14
	 * @var array
	 */
	private $atts;

	/**
	 * @since 9.14
	 * @var string
	 */
	private $content;

	/**
	 * @since 9.12
	 * @var string
	 */
	private $html = '';

	/**
	 * Shortcode support hyphens in the tag name. Bug was fixed:
	 *
	 * @link https://core.trac.wordpress.org/ticket/17657
	 * @since 9.12
	 * @var string
	 */
	protected static $tag = 'cn-content';

	/**
	 * @since 9.12
	 *
	 * @noinspection PhpUnusedParameterInspection
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 */
	public function __construct( $atts, $content, $tag ) {

		$this->parseAtts( $atts );

		$this->content = $content;

		if ( $this->isCondition() ) {

			$this->render();
			$this->maybeAddAction();
		}
	}

	/**
	 * Register the shortcode.
	 *
	 * @since 9.14
	 */
	public static function add() {

		/*
		 * Do not register the shortcode when doing ajax requests.
		 * This is primarily implemented so the shortcodes are not run during Yoast SEO page score admin ajax requests.
		 * The page score can cause the ajax request to fail and/or prevent the page from saving when page score is
		 * being calculated on the output from the shortcode.
		 */
		if ( ! Request::get()->isAjax() ) {

			add_shortcode( static::$tag, array( __CLASS__, 'shortcode' ) );
		}
	}

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @see Conditional_Content::add()
	 *
	 * @since 9.12
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return static
	 */
	public static function shortcode( $atts, $content, $tag ) {

		return new static( $atts, $content, $tag );
	}

	/**
	 * The shortcode default attributes.
	 *
	 * @since 9.12
	 *
	 * @return array
	 */
	private function getDefaults() {

		return array(
			'id'        => false,
			'block'     => 'content',
			'condition' => 'is_front_page',
			'parameter' => null,
			'insert'    => null,
		);
	}

	/**
	 * Parse the user supplied attributes.
	 *
	 * @since 9.12
	 *
	 * @param array $atts
	 */
	private function parseAtts( $atts ) {

		$defaults = $this->getDefaults();
		$atts     = shortcode_atts( $defaults, $atts, static::$tag );

		if ( is_numeric( $atts['id'] ) ) {

			$atts['id'] = absint( $atts['id'] );

		} else {

			$atts['id'] = false;
		}

		if ( is_numeric( $atts['parameter'] ) ) {

			$atts['parameter'] = absint( $atts['parameter'] );
		}

		$this->atts = $atts;
	}

	/**
	 * Set the shortcode HTML.
	 *
	 * @since 9.12
	 */
	public function render() {

		if ( 0 < strlen( $this->content ) ) {

			$this->html = do_shortcode( $this->content );
			return;
		}

		$post = WP_Post::get_instance( $this->atts['id'] );

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		switch ( $this->atts['block'] ) {

			case 'content':
				$this->html = $this->getPostContent( $post );
				break;

			case 'title':
				$this->html = $post->post_title;
				break;
		}
	}

	/**
	 * Get post content, applying filters.
	 *
	 * @since 9.16
	 *
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	private function getPostContent( $post ) {

		// No recursive loops!
		$html = ( get_the_ID() === $this->atts['id'] || get_queried_object_id() === $this->atts['id'] )
			  ? ''
			  : apply_filters( 'the_content', $post->post_content );

		return apply_filters( 'Connections_Directory/Shortcode/Conditional_Content/Post_Content', $html, $post );
	}

	/**
	 * Return the action hook handle.
	 *
	 * @since 9.14
	 *
	 * @return string
	 */
	private function actionHandle() {

		switch ( $this->atts['insert'] ) {

			case 'head':
				$handle = 'cn_action_list_before';
				break;

			case 'foot':
				$handle = 'cn_action_list_after';
				break;

			default:
				$handle = $this->atts['insert'];
		}

		return $handle;
	}

	/**
	 * Whether or not to register the action hook.
	 *
	 * @since 9.14
	 */
	private function maybeAddAction() {

		// No actions to add if there is no content.
		if ( 0 === strlen( $this->html ) && is_null( $this->atts['insert'] ) ) {

			return;
		}

		add_action(
			$this->actionHandle(),
			function () {
				echo $this->html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		);
	}

	/**
	 * Whether or not the defined condition is met.
	 *
	 * @since 9.12
	 *
	 * @return bool
	 */
	private function isCondition() {

		switch ( $this->atts['condition'] ) {

			case 'current_user_can':
				$condition = $this->currentUserCan( $this->atts['parameter'] );
				break;

			case 'current_user_has_role':
				$condition = $this->currentUserHasRole( $this->atts['parameter'] );
				break;

			case 'in_category':
				$condition = $this->inCategory( $this->atts['parameter'] );
				break;

			case 'is_category':
				$condition = $this->isCategory( $this->atts['parameter'] );
				break;

			case 'is_front_page':
				$condition = $this->isFrontPage();
				break;

			case 'is_home':
				$condition = $this->isHome();
				break;

			case 'is_region':
				$condition = $this->isRegion( $this->atts['parameter'] );
				break;

			case 'is_search':
				$condition = $this->isSearch();
				break;

			case 'is_single':
				$condition = $this->isSingle( $this->atts['parameter'] );
				break;

			case 'is_user_logged_in':
				$condition = is_user_logged_in();
				break;

			default:
				$condition = apply_filters(
					"Connections_Directory/Shortcode/Conditional_Content/is_condition/{$this->atts['condition']}",
					false,
					$this->atts['parameter'],
					$this
				);
		}

		return $condition;
	}

	/**
	 * Whether or not the current user can do capability.
	 *
	 * The `$parameter` is validated against the registered capabilities.
	 *
	 * @since 9.14
	 *
	 * @param int|null|string $parameter
	 *
	 * @return bool
	 */
	private function currentUserCan( $parameter ) {

		global $wp_roles;

		$condition    = false;
		$roles        = $wp_roles->roles;
		$capabilities = array();

		foreach ( $roles as $role ) {

			$capabilities = array_merge( $capabilities, array_keys( (array) $role['capabilities'] ) );
		}

		$capabilities = array_unique( $capabilities );

		if ( in_array( $parameter, $capabilities ) ) {

			$condition = current_user_can( $parameter );
		}

		return $condition;
	}

	/**
	 * Whether or not the current user is attached to role.
	 *
	 * @since 9.14
	 *
	 * @param int|null|string $parameter
	 *
	 * @return bool
	 */
	private function currentUserHasRole( $parameter ) {

		$condition = false;
		$user      = wp_get_current_user();

		if ( $user instanceof WP_User && in_array( $parameter, (array) $user->roles ) ) {

			$condition = true;
		}

		return $condition;
	}

	/**
	 * Whether or no if the current Entry is attached to a certain category, parameter is required.
	 *
	 * @since 9.14
	 *
	 * @param int|null|string $parameter
	 *
	 * @return bool
	 */
	private function inCategory( $parameter ) {

		$condition  = false;
		$queryValue = cnQuery::getVar( 'cn-entry-slug', false );

		if ( ! is_null( $parameter ) && $queryValue ) {

			$result = Connections_Directory()->retrieve->entry( urldecode( $queryValue ) );

			if ( false !== $result ) {

				$terms = cnRetrieve::entryTerms( $result->id, 'category' );

				if ( is_array( $terms ) ) {

					$term_ids   = wp_list_pluck( $terms, 'term_id' );
					$term_slugs = wp_list_pluck( $terms, 'slug' );

					if ( in_array( $parameter, $term_ids ) || in_array( $parameter, $term_slugs ) ) {

						$condition = true;
					}
				}
			}
		}

		return $condition;
	}

	/**
	 * Whether or not if the current query is filtering by category if no parameter is used.
	 * When parameter is used, will check the current query matches the parameter.
	 *
	 * @since 9.14
	 *
	 * @param int|null|string $parameter
	 *
	 * @return bool
	 */
	public function isCategory( $parameter ) {

		$condition  = false;
		$queryValue = cnQuery::getVar( 'cn-cat', cnQuery::getVar( 'cn-cat-slug', false ) );

		if ( is_null( $parameter ) && $queryValue ) {

			$condition = true;

		} elseif ( ! is_null( $parameter ) && $queryValue ) {

			$field  = is_numeric( $parameter ) ? 'id' : 'slug';
			$result = cnTerm::getBy( $field, $queryValue );

			if ( $result instanceof Term ) {

				if ( (int) $result->term_id === $parameter || $result->slug === $parameter ) {

					$condition = true;
				}
			}
		}

		return $condition;
	}

	/**
	 * Whether or not this the main query, not filtered by HTTP query variables.
	 *
	 * @since 9.14
	 *
	 * @return bool
	 */
	private function isFrontPage() {

		global $wp_query;

		// Remove the cn-image query vars.
		$wp_query_vars = array_diff_key( (array) $wp_query->query_vars, array_flip( array( 'src', 'w', 'h', 'q', 'a', 'zc', 'f', 's', 'o', 'cc', 'ct' ) ) );

		// Grab the array containing all query vars registered by Connections.
		$registeredQueryVars = cnRewrite::queryVars( array() );
		return ! (bool) array_intersect( $registeredQueryVars, array_keys( $wp_query_vars ) );
	}

	/**
	 * Whether or not the current page is the page set as the Directory Homepage.
	 *
	 * @since 9.14
	 *
	 * @return bool
	 */
	private function isHome() {

		$condition = false;
		$home      = (int) cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' );

		if ( $this->isFrontPage() && ( get_the_ID() === $home || get_queried_object_id() === $home ) ) {

			$condition = true;
		}

		return $condition;
	}

	/**
	 * Whether the user is filtering the directory results by region (state) or not.
	 *
	 * @since 10.4.25
	 *
	 * @param null|string $parameter The region value that the condition must meet.
	 *
	 * @return bool
	 */
	private function isRegion( $parameter = null ) {

		$condition  = false;
		$queryValue = cnQuery::getVar( 'cn-region', false );

		if ( ! is_null( $parameter ) && $queryValue ) {

			if ( $queryValue === $parameter ) {

				$condition = true;
			}
		}

		return $condition;
	}

	/**
	 * Whether search query is requested.
	 *
	 * @since 9.14
	 *
	 * @return bool
	 */
	public function isSearch() {

		return Request::get()->isSearch();
	}

	/**
	 * Whether or not the current view is the Entry detail/profile view based on HTTP query variable.
	 *
	 * @since 9.14
	 *
	 * @param int|null|string $parameter
	 *
	 * @return bool
	 */
	private function isSingle( $parameter = null ) {

		$condition  = false;
		$queryValue = cnQuery::getVar( 'cn-entry-slug', false );

		if ( is_null( $parameter ) && $queryValue ) {

			$condition = true;

		} elseif ( ! is_null( $parameter ) && $queryValue ) {

			$result = Connections_Directory()->retrieve->entry( urldecode( $queryValue ) );

			if ( false !== $result ) {

				if ( (int) $result->id === $parameter || $result->slug === $parameter ) {

					$condition = true;
				}
			}
		}

		return $condition;
	}

	/**
	 * @since 9.12
	 *
	 * @return string
	 */
	public function __toString() {

		return is_null( $this->atts['insert'] ) ? $this->html : '';
	}
}
