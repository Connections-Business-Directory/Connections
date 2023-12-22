<?php

namespace Connections_Directory\Utility;

use cnSEO;
use cnSettingsAPI;

/**
 * Class _url
 *
 * @package Connections_Directory\Utility
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _url {

	/**
	 * @link  http://publicmind.in/blog/url-encoding/
	 *
	 * @since unknown
	 *
	 * @param string $url The URL to encode.
	 *
	 * @return string A string containing the encoded URL with disallowed
	 *                characters converted to their percentage encodings.
	 */
	public static function encode( $url ) {

		$reserved = array(
			':' => '!%3A!ui',
			'/' => '!%2F!ui',
			'?' => '!%3F!ui',
			'#' => '!%23!ui',
			'[' => '!%5B!ui',
			']' => '!%5D!ui',
			'@' => '!%40!ui',
			'!' => '!%21!ui',
			'$' => '!%24!ui',
			'&' => '!%26!ui',
			"'" => '!%27!ui',
			'(' => '!%28!ui',
			')' => '!%29!ui',
			'*' => '!%2A!ui',
			'+' => '!%2B!ui',
			',' => '!%2C!ui',
			';' => '!%3B!ui',
			'=' => '!%3D!ui',
			'%' => '!%25!ui',
		);

		$url = rawurlencode( $url );
		$url = preg_replace( array_values( $reserved ), array_keys( $reserved ), $url );

		return $url;
	}

	/**
	 * Take a URL and see if it's prefixed with a protocol, if it's not then it will add the default prefix to the
	 * start of the string.
	 *
	 * @since 0.8
	 *
	 * @param string $url
	 * @param string $protocol
	 *
	 * @return string
	 */
	public static function prefix( $url, $protocol = 'http://' ) {

		/*
		 * @todo Refactor to use @see set_url_scheme()
		 */

		return parse_url( $url, PHP_URL_SCHEME ) === null ? $protocol . $url : $url;
	}

	/**
	 * Removes a forward slash from the beginning of the string if it exists.
	 *
	 * @since  8.1.6
	 *
	 * @param string $string String to remove the  slashes from.
	 *
	 * @return string String without the forward slashes.
	 */
	public static function unpreslashit( $string ) {

		if ( is_string( $string ) && 0 < strlen( $string ) ) {

			$string = ltrim( $string, '/\\' );
		}

		return $string;
	}

	/**
	 * Prepends a forward slash to a string.
	 *
	 * @since  8.1.6
	 *
	 * @param string $string String to  prepend the forward slash.
	 *
	 * @return string String with forward slash prepended.
	 */
	public static function preslashit( $string ) {

		if ( is_string( $string ) && 0 < strlen( $string ) ) {

			$string = '/' . self::unpreslashit( $string );
		}

		return $string;
	}

	/**
	 * Remove the protocol from the supplied URL.
	 *
	 * @since  8.4.2
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function makeRelative( $url ) {

		$url = str_replace(
			array(
				set_url_scheme( home_url(), 'http' ),
				set_url_scheme( home_url(), 'https' ),
			),
			set_url_scheme( home_url(), 'relative' ),
			$url
		);

		return $url;
	}

	/**
	 * Return a URL with the protocol scheme removed to make it a protocol relative URL.
	 *
	 * This is useful when enqueueing CSS and JavaScript files.
	 *
	 * @since 8.4.3
	 *
	 * @param string $url
	 *
	 * @return string|NULL The URL without the protocol scheme, NULL on error.
	 */
	public static function makeProtocolRelative( $url ) {

		return preg_replace( '(https?://)', '//', $url );
	}

	/**
	 * Create the URL to a file from its absolute system path.
	 *
	 * @since 0.8
	 *
	 * @param string $path The file path.
	 *
	 * @return string The URL to the file.
	 */
	public static function fromPath( $path ) {

		// Get correct URL and path to wp-content.
		$content_url = content_url();
		$content_dir = untrailingslashit( WP_CONTENT_DIR );

		// Fix path on Windows.
		// wp_normalize_path() in WP >= 3.9
		if ( function_exists( 'wp_normalize_path' ) ) {

			$path        = wp_normalize_path( $path );
			$content_dir = wp_normalize_path( $content_dir );

		} else {

			$path = str_replace( '\\', '/', $path );
			$path = preg_replace( '|/+|', '/', $path );

			$content_dir = str_replace( '\\', '/', $content_dir );
			$content_dir = preg_replace( '|/+|', '/', $content_dir );

		}

		// Create URL.
		$url = str_replace( $content_dir, $content_url, $path );

		return $url;
	}

	/**
	 * Create a permalink.
	 *
	 * @todo Should check to ensure require params are set and valid and pass back WP_Error if they are not.
	 * @todo The type case statement should have a default with an filter attached so this can be pluggable.
	 *
	 * NOTE: When the `name` permalink is being requested, use the entry slug.
	 *       It is saved id the db as URL encoded. If any other strings pass
	 *       for the `name` permalink must be URL encoded.
	 *       All the other permalink types will be URL encoded in this method
	 *       so pass stings without URL encoding.
	 *
	 * @since  0.7.3
	 *
	 * @global $wp_rewrite
	 * @global $post
	 *
	 * @param array $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type string $class      The class to give the anchor.
	 *                              Default: ''
	 *     @type string $text       The anchor text.
	 *                              Default: ''
	 *     @type string $title      The anchor title attribute.
	 *                              Default: ''
	 *     @type bool   $follow     Whether or not the anchor rel should be follow or nofollow
	 *                              Default: TRUE
	 *     @type string $rel        The rel attribute.
	 *                              Default: ''
	 *     @type string $slug       The slug of the object to build the permalink for. ie. the entry slug or term slug.
	 *                              Default: ''
	 *     @type string $on_click   The inline javascript on_click attribute.
	 *                              Default: ''
	 *     @type string $type       The type of permalink to create. ie. name, edit, home
	 *                              Default: 'name'
	 *     @type int    $home_id    The page ID of the directory home.
	 *                              Default: The page set as the Directory Home Page.
	 *     @type bool   $force_home Whether or not to for the page ID to the page ID of the page set as the Directory Home Page.
	 *                              Default: FALSE
	 *     @type string $data       What to return.
	 *                              Default: tag
	 *                              Accepts: tag | url
	 *     @type bool   $return     Whether or not to return or echo the permalink.
	 *                              Default: FALSE
	 * }
	 *
	 * @return string
	 */
	public static function permalink( $atts ) {

		/**
		 * @var $wp_rewrite \WP_Rewrite
		 * @noinspection PhpRedundantVariableDocTypeInspection
		 * @noinspection PhpFullyQualifiedNameUsageInspection
		 */
		global $wp_rewrite, $post;

		// The class.seo.file is only loaded in the frontend; do not attempt to remove the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) {
			cnSEO::doFilterPermalink( false );
		}

		// The anchor attributes.
		$piece = array();

		$defaults = array(
			'class'      => '',
			'text'       => '',
			'title'      => '',
			'follow'     => true,
			'rel'        => '',
			'slug'       => '',
			'on_click'   => '',
			'type'       => 'name',
			'home_id'    => cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
			'force_home' => false,
			'data'       => 'tag', // Valid: 'tag' | 'url'.
			'return'     => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Get the directory home page ID.
		$homeID = $atts['force_home'] ? cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ) : $atts['home_id'];

		// Get the settings for the base of each data type to be used in the URL.
		$base = get_option( 'connections_permalink' );

		// Create the permalink base based on context where the entry is being displayed.
		if ( in_the_loop() && is_page() ) {

			if ( $wp_rewrite->using_permalinks() ) {

				// Only slash it when using pretty permalinks.
				$permalink = trailingslashit( get_permalink( $homeID ) );

			} else {

				$permalink = get_permalink( $homeID );

				// If the current page is the front page, the `page_id` query var must be added because without it, WP
				// will redirect to the blog/posts home page.
				if ( is_front_page() ) {

					$permalink = add_query_arg( 'page_id', $post->ID, $permalink );
				}
			}

		} else {

			// If using pretty permalinks get the directory home page ID and slash it, otherwise just add the page_id to the query string.
			if ( $wp_rewrite->using_permalinks() ) {

				$permalink = trailingslashit( get_permalink( $homeID ) );

			} else {

				$permalink = add_query_arg( array( 'page_id' => $homeID, 'p' => false ), get_permalink( $homeID ) );
			}

		}

		$atts['permalink_root'] = $permalink;

		if ( ! empty( $atts['class'] ) ) {
			$piece['class'] = 'class="' . _escape::classNames( $atts['class'] ) . '"';
		}

		// if ( ! empty( $atts['slug'] ) ) $piece['id']        = 'id="' . $atts['slug'] .'"';

		if ( ! empty( $atts['title'] ) ) {
			$piece['title'] = 'title="' . _escape::attribute( $atts['title'] ) . '"';
		}

		if ( ! empty( $atts['target'] ) ) {
			$piece['target'] = 'target="' . _escape::attribute( $atts['target'] ) . '"';
		}

		if ( ! $atts['follow'] ) {
			$piece['follow'] = 'rel="nofollow"';
		}

		if ( ! empty( $atts['rel'] ) ) {
			$piece['rel'] = 'rel="' . _escape::attribute( $atts['rel'] ) . '"';
		}

		if ( ! empty( $atts['on_click'] ) ) {
			$piece['on_click'] = 'onClick="' . esc_js( $atts['on_click'] ) . '"';
		}

		/*
		 * NOTE: Use `rawurlencode()` when encoding the permalink for department, organization, district, county,
		 *       locality, region, and country. This is because `urlencode()` will encode a space as a `+` sign
		 *       which is not appropriate for a URL path. It is fine for a query string. So, when permalinks are
		 *       not enabled, `urlencode()` will be used.
		 */

		switch ( $atts['type'] ) {

			case 'home':
				break;

			case 'all':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . 'view/all/' );
				} else {
					$permalink = add_query_arg( 'cn-view', 'all', $permalink );
				}

				break;

			case 'submit':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . 'submit/' );
				} else {
					$permalink = add_query_arg( 'cn-view', 'submit', $permalink );
				}

				break;

			case 'edit':
				$result = Connections_Directory()->retrieve->entry( $atts['slug'] );

				if ( false !== $result ) {

					$id        = $result->id;
					$url       = _nonce::url( "admin.php?page=connections_manage&cn-action=edit_entry&id={$id}", 'entry_edit', $id );
					$permalink = admin_url( $url );
				}

				break;

			case 'delete':
				$result = Connections_Directory()->retrieve->entry( $atts['slug'] );

				if ( false !== $result ) {

					$id        = $result->id;
					$url       = _nonce::url( "admin.php?cn-action=delete_entry&id={$id}", 'entry_delete', $id );
					$permalink = admin_url( $url );
				}

				break;

			case 'name':
				if ( $wp_rewrite->using_permalinks() ) {

					// The entry slug is saved in the db urlencoded so we'll expect when the permalink for entry name is
					// requested that the entry slug is being used so urlencode() will not be use as not to double encode it.
					$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . $atts['slug'] );
				} else {
					$permalink = add_query_arg( array( 'cn-entry-slug' => $atts['slug'], 'cn-view' => 'detail' ), $permalink );
				}

				break;

			case 'detail':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['name_base'] . '/' . $atts['slug'] . '/detail' );
				} else {
					$permalink = add_query_arg( array( 'cn-entry-slug' => $atts['slug'], 'cn-view' => 'detail' ), $permalink );
				}

				break;

			case 'department':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['department_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-department', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			case 'organization':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['organization_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-organization', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			//case 'category':
			//
			//	if ( $wp_rewrite->using_permalinks() ) {
			//		$permalink = trailingslashit( $permalink . $base['category_base'] . '/' . $atts['slug'] );
			//	} else {
			//		$permalink = add_query_arg( 'cn-cat-slug', $atts['slug'] , $permalink );
			//	}
			//
			//	break;

			case 'district':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['district_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-district', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			case 'county':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['county_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-county', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			case 'locality':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['locality_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-locality', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			case 'region':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['region_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-region', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			case 'postal_code':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['postal_code_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-postal-code', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			case 'country':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['country_base'] . '/' . rawurlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( 'cn-country', urlencode( $atts['slug'] ), $permalink );
				}

				break;

			case 'character':
				if ( $wp_rewrite->using_permalinks() ) {
					$permalink = trailingslashit( $permalink . $base['character_base'] . '/' . urlencode( $atts['slug'] ) );
				} else {
					$permalink = add_query_arg( array( 'cn-char' => urlencode( $atts['slug'] ) ), $permalink );
				}

				break;

			case _string::endsWith( '-taxonomy-term', $atts['type'] ):
				$taxonomy = _string::replaceLast( '-taxonomy-term', '', $atts['type'] );

				$permalink = apply_filters(
					"Connections_Directory/Taxonomy/{$taxonomy}/Term/Permalink",
					$permalink,
					$atts
				);

				break;

			default:
				if ( has_filter( "cn_permalink-{$atts['type']}" ) ) {

					/**
					 * Allow extensions to add custom permalink types.
					 *
					 * The variable portion of the filter name if the permalink type being created.
					 *
					 * @since 8.5.17
					 *
					 * @param string $permalink The permalink.
					 * @param array  $atts      The attributes array.
					 */
					$permalink = apply_filters( "cn_permalink-{$atts['type']}", $permalink, $atts );
				}

				break;
		}

		$permalink = apply_filters( 'cn_permalink', $permalink, $atts );

		if ( 'url' == $atts['data'] ) {

			$out = esc_url( $permalink );

		} else {

			$piece['href'] = 'href="' . esc_url( $permalink ) . '"';

			$out = '<a ' . implode( ' ', $piece ) . '>' . _escape::html( $atts['text'] ) . '</a>';

		}

		// The class.seo.file is only loaded in the frontend; do not attempt to add the filter
		// otherwise it'll cause an error.
		if ( ! is_admin() ) {
			cnSEO::doFilterPermalink( true );
		}

		if ( $atts['return'] ) {
			return $out;
		}

		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
