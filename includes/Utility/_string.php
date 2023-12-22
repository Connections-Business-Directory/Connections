<?php

namespace Connections_Directory\Utility;

use WP_Error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class _string
 *
 * @package Connections_Directory\Utility
 */
final class _string {

	/**
	 * Insert a string at a specified position.
	 *
	 * @since 10.2
	 *
	 * @param string $string
	 * @param string $insert
	 * @param int    $position
	 *
	 * @return string
	 */
	public static function insert( $string, $insert, $position ) {

		return substr( $string, 0, $position ) . $insert . substr( $string, $position );
	}

	/**
	 * Transform supplied string to camelCase.
	 *
	 * NOTE: Limits the output to alphanumeric characters.
	 *
	 * @since 9.11
	 *
	 * @param string $string
	 * @param bool   $capitaliseInitial
	 *
	 * @return string
	 */
	public static function toCamelCase( $string, $capitaliseInitial = false ) {

		$string = str_replace( '-', '', ucwords( self::toKebabCase( $string ), '-' ) );

		if ( ! $capitaliseInitial ) {

			$string = lcfirst( $string );
		}

		return $string;
	}

	/**
	 * Transform supplied string to kebab-case.
	 *
	 * NOTE: Limits the output to alphanumeric characters.
	 *
	 * @since 9.11
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function toKebabCase( $string ) {

		// Added this because sanitize_title() will still allow %.
		$string = str_replace( '%', '-', $string );
		$string = str_replace( '_', '-', sanitize_title( $string ) );

		return self::replaceWhatWith( $string, '-', '-' );
	}

	/**
	 * Return a numeric only hash from string.
	 *
	 * @link https://stackoverflow.com/q/3379471/5351316
	 *
	 * @since 9.8
	 *
	 * @param string   $string
	 * @param int|null $length
	 *
	 * @return string
	 */
	public static function toNumericHash( $string, $length = null ) {

		$binhash = md5( $string, true );
		$numhash = unpack( 'N2', $binhash );
		$hash    = $numhash[1] . $numhash[2];

		if ( null !== $length && is_int( $length ) ) {

			$hash = substr( $hash, 0, $length );
		}

		return $hash;
	}

	/**
	 * Transform supplied string to snake-case.
	 *
	 * NOTE: Limits the output to alphanumeric characters.
	 *
	 * @since 9.11
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function toSnakeCase( $string ) {

		$string = str_replace( '-', '_', self::toKebabCase( $string ) );

		return $string;
	}

	/**
	 * Whether a string begins with string segment.
	 *
	 * @author  http://stackoverflow.com/users/63557/mrhus
	 * @license http://creativecommons.org/licenses/by-sa/3.0/
	 * @link    http://stackoverflow.com/a/834355
	 *
	 * @since 8.1
	 *
	 * @param string $needle
	 * @param string $haystack
	 *
	 * @return bool
	 */
	public static function startsWith( $needle, $haystack ) {

		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	/**
	 * Whether a string ends with string segment.
	 *
	 * @author  http://stackoverflow.com/users/63557/mrhus
	 * @license http://creativecommons.org/licenses/by-sa/3.0/
	 * @link    http://stackoverflow.com/a/834355
	 *
	 * @since 8.1
	 *
	 * @param string $needle
	 * @param string $haystack
	 *
	 * @return bool
	 */
	public static function endsWith( $needle, $haystack ) {

		return substr( $haystack, -strlen( $needle ) ) === $needle;
	}

	/**
	 * Apply a prefix to a string or to an array of strings.
	 *
	 * @since 10.4
	 *
	 * @param string          $prefix
	 * @param string|string[] $string
	 *
	 * @return string|string[]
	 */
	public static function applyPrefix( $prefix, $string ) {

		if ( empty( $string ) || empty( $prefix ) ) {

			return $string;
		}

		if ( is_array( $string ) ) {

			foreach ( $string as $key => $value ) {

				if ( ! _string::startsWith( $prefix, $value ) ) {

					$string[ $key ] = $prefix . $value;
				}
			}

		} elseif ( is_string( $string ) ) {

			if ( ! _string::startsWith( $prefix, $string ) ) {

				$string = $prefix . $string;
			}

		}

		return $string;
	}

	/**
	 * Remove prefix from string if it exists.
	 *
	 * @since 8.1
	 *
	 * @param string $needle
	 * @param string $haystack
	 *
	 * @return string
	 */
	public static function removePrefix( $needle, $haystack ) {

		if ( substr( $haystack, 0, strlen( $needle ) ) == $needle ) {

			return substr( $haystack, strlen( $needle ) );
		}

		return $haystack;
	}

	/**
	 * Replace only the FIRST occurrence of the search string with the replacement string.
	 *
	 * @link https://stackoverflow.com/a/1252710/5351316
	 * @link https://stackoverflow.com/a/22274299/5351316
	 *
	 * @since 8.24
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $subject
	 *
	 * @return string|string[]
	 */
	public static function replaceFirst( $search, $replace, $subject ) {

		if ( false !== $pos = strpos( $subject, $search ) ) {

			$subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
		}

		return $subject;
	}

	/**
	 * Replace only the LAST occurrence of the search string with the replacement string.
	 *
	 * @link https://stackoverflow.com/a/22269776/5351316
	 * @link https://stackoverflow.com/a/22274299/5351316
	 *
	 * @since 8.24
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $subject
	 *
	 * @return array|string
	 */
	public static function replaceLast( $search, $replace, $subject ) {

		return substr_replace( $subject, $replace, strrpos( $subject, $search ), strlen( $search ) );
	}

	/**
	 * General purpose function to do a little more than just white-space trimming and cleaning, it can do
	 * characters-to-replace and characters-to-replace-with. You can do the following:
	 *
	 * 1. Normalize white-spaces, so that all multiple \r, \n, \t, \r\n, \0, 0x0b, 0x20 and all control characters
	 *    can be replaced with a single space, and also trim from both ends of the string.
	 * 2. Remove all undesired characters.
	 * 3. Remove duplicates.
	 * 4. Replace multiple occurrences of characters with a character or string.
	 *
	 * @link http://pageconfig.com/post/remove-undesired-characters-with-trim_all-php
	 *
	 * @since 8.1.6
	 *
	 * @param string      $string
	 * @param string|null $what
	 * @param string      $with
	 *
	 * @return string
	 */
	public static function replaceWhatWith( $string, $what = null, $with = ' ' ) {

		if ( ! is_string( $string ) ) {
			return '';
		}

		if ( is_null( $what ) ) {

			//	Character      Decimal      Use
			//	"\0"            0           Null Character
			//	"\t"            9           Tab
			//	"\n"           10           New line
			//	"\x0B"         11           Vertical Tab
			//	"\r"           13           New Line in Mac
			//	" "            32           Space

			$what = "\x00-\x20";    //all white-spaces and control chars
		}

		// @todo This should probably use preg_quote() and not wp_slash(). What was I thinking???

		return trim( preg_replace( '/[' . wp_slash( $what ) . ']+/u', $with, $string ), $what );
	}

	/**
	 * Normalize a string. Replace all occurrence of one or more spaces with a single space, remove control characters
	 * and trim whitespace from both ends.
	 *
	 * @since 8.1.6
	 *
	 * @param string $string The string to normalize.
	 *
	 * @return string
	 */
	public static function normalize( $string ) {

		return self::replaceWhatWith( $string );
	}

	/**
	 * Create excerpt from the supplied string.
	 *
	 * NOTE: The `more` string will be inserted before the last HTML tag if one exists.
	 *       If not, it'll be appended to the end of the string.
	 *       If the length is set `p`, the `more` string will not be appended.
	 *
	 * NOTE: The length maybe exceeded in attempt to end the excerpt at the end of a sentence.
	 *
	 * @todo  If the string contains HTML tags, those too will be counted when determining whether or not to append the `more` string. This should be fixed.
	 *
	 * Filters:
	 *   cn_excerpt_length       => change the default excerpt length of 55 words.
	 *   cn_excerpt_more         => change the default more string of &hellip;
	 *   cn_excerpt_allowed_tags => change the allowed HTML tags.
	 *   cn_entry_excerpt        => change returned excerpt
	 *
	 * Credit:
	 * @link http://wordpress.stackexchange.com/a/141136
	 *
	 * @since 8.1.5
	 *
	 * @param string $string String to create the excerpt from.
	 * @param array  $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type int    $length       The length, number of words, of the excerpt to create.
	 *                                If set to `p` the excerpt will be the first paragraph, no word limit.
	 *                                Default: 55.
	 *     @type string $more         The string appended to the end of the excerpt when $length is exceeded.
	 *                                Default: &hellip
	 *     @type array  $allowed_tags An array containing the permitted tags.
	 * }
	 *
	 * @return string
	 */
	public static function excerpt( $string, $atts = array() ) {

		if ( empty( $string ) || ! is_string( $string ) ) {
			return '';
		}

		$defaults = array(
			'length'       => apply_filters( 'cn_excerpt_length', 55 ),
			'more'         => apply_filters( 'cn_excerpt_more', __( '&hellip;', 'connections' ) ),
			'allowed_tags' => apply_filters(
				'cn_excerpt_allowed_tags',
				array(
					'style',
					'span',
					'br',
					'em',
					'strong',
					'i',
					'ul',
					'ol',
					'li',
					'a',
					'p',
					'img',
					'video',
					'audio',
				)
			),
		);

		$atts = wp_parse_args( $atts, $defaults );

		// Save a copy of the raw text for the filter.
		$raw = $string;

		// Whether to append the more string.
		// This is only true if the word count is more than the word length limit.
		// This is not set if length is set to `p`.
		$appendMore = false;

		// Strip all shortcode from the text.
		$string = strip_shortcodes( $string );

		$string = str_replace( ']]>', ']]&gt;', $string );

		if ( 'p' === $atts['length'] ) {

			$excerpt = substr( $string, 0, strpos( $string, '</p>' ) + 4 );

		} else {

			$string  = self::stripTags( $string, false, '<' . implode( '><', $atts['allowed_tags'] ) . '>' );
			$tokens  = array();
			$excerpt = '';
			$count   = 0;

			// Divide the string into tokens; HTML tags, or words, followed by any whitespace.
			preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $string, $tokens );

			foreach ( $tokens[0] as $token ) {

				if ( $count >= $atts['length'] && preg_match( '/[\?\.\!]\s*$/uS', $token ) ) {

					// Limit reached, continue until ? . or ! occur at the end.
					$excerpt .= trim( $token );

					// If the length limit was reached, append the more string.
					$appendMore = true;

					break;
				}

				// Add words to complete sentence.
				$count++;

				// Append what's left of the token.
				$excerpt .= $token;
			}

		}

		$excerpt = trim( force_balance_tags( $excerpt ) );

		// No need to append the more string if the excerpted string matches the original string.
		if ( trim( $string ) == $excerpt ) {

			$appendMore = false;
		}

		$lastCloseTag = strrpos( $excerpt, '</' );
		$lastSpace    = strrpos( $excerpt, ' ' );

		// Determine if the string ends with a HTML tag or word.
		if ( ( ! preg_match( '/[\s\?\.\!]$/', $excerpt ) ) &&
			 ( false !== $lastCloseTag && ( false !== $lastSpace && $lastCloseTag > $lastSpace ) ) ) {

			// Inside last HTML tag.
			if ( $appendMore ) {
				$excerpt = substr_replace( $excerpt, $atts['more'], $lastCloseTag, 0 );
			}

		} else {

			// After the content.
			if ( $appendMore ) {
				$excerpt .= $atts['more'];
			}
		}

		return apply_filters( 'cn_excerpt', $excerpt, $raw, $atts );
	}

	/**
	 * Remove both the script and style tags from the supplied string.
	 *
	 * @since 10.4.28
	 *
	 * @param string $string The string to remove the script tags.
	 *
	 * @return string
	 */
	public static function stripScripts( $string ) {

		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );

		if ( ! is_string( $string ) ) {

			$string = '';
		}

		return $string;
	}

	/**
	 * Properly strip all HTML tags including script and style.
	 *
	 * This differs from strip_tags() because it removes the contents of
	 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
	 * will return 'something'. wp_strip_all_tags will return ''
	 *
	 * NOTE: This is the Connections equivalent of @see wp_strip_all_tags() in WordPress core ../wp-includes/formatting.php
	 *
	 * This differs from @see wp_strip_all_tags() in that it adds the `$allowed_tags` param to be passed to `strip_tags()`.
	 *
	 * @since 8.5.22
	 *
	 * @param string $string        String containing HTML tags.
	 * @param bool   $remove_breaks Whether to remove left over line breaks and white space chars.
	 * @param string $allowed_tags  String of tags which will not be stripped.
	 *
	 * @return string The processed string.
	 */
	public static function stripTags( $string, $remove_breaks = false, $allowed_tags = '' ) {

		if ( ! is_string( $string ) ) {
			return '';
		}

		$string = self::stripScripts( $string );
		$string = strip_tags( $string, $allowed_tags ); // phpcs:ignore WordPressVIPMinimum.Functions.StripTags.StripTagsTwoParameters

		if ( $remove_breaks ) {

			$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
		}

		return trim( $string );
	}

	/**
	 * Texturize string, apply wpautop and make links clickable.
	 *
	 * @since 10.4.28
	 *
	 * @param string $string The string to texturize.
	 *
	 * @return string
	 */
	public static function texturize( $string ) {

		return wptexturize( wpautop( make_clickable( $string ) ) );
	}

	/**
	 * Truncates string.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ellipsis if the text is longer than length.
	 *
	 * Filters:
	 *   cn_excerpt_length       => change the default excerpt length of 55 words.
	 *   cn_excerpt_more         => change the default more string of &hellip;
	 *   cn_excerpt_allowed_tags => change the allowed HTML tags.
	 *   cn_entry_excerpt        => change returned excerpt
	 *
	 * Credit {@link http://book.cakephp.org/3.0/en/core-libraries/text.html#truncating-text}
	 *
	 * @since 8.5.3
	 *
	 * @param string $string String to truncate.
	 * @param array  $atts {
	 *     Optional. An array of arguments.
	 *
	 *     @type int    $length       The length, number of characters to limit the string to.
	 *     @type string $more         The string appended to the end of the excerpt when $length is exceeded.
	 *                                Default: &hellip
	 *     @type bool   $exact        If FALSE, the truncation will occur at the first whitespace after the point at which $length is exceeded.
	 *                                Default: false
	 *     @type bool   $html         If TRUE, HTML tags will be respected and will not be cut off.
	 *                                Default: true
	 *     @type array  $allowed_tags An array containing the permitted tags.
	 * }
	 *
	 * @return string
	 */
	public static function truncate( $string, $atts = array() ) {

		$defaults = array(
			'length'       => apply_filters( 'cn_excerpt_length', 55 ),
			'more'         => apply_filters( 'cn_excerpt_more', __( '&hellip;', 'connections' ) ),
			'exact'        => false,
			'html'         => true,
			'allowed_tags' => apply_filters(
				'cn_excerpt_allowed_tags',
				array(
					'style',
					'br',
					'em',
					'strong',
					'i',
					'ul',
					'ol',
					'li',
					'a',
					'p',
					'img',
					'video',
					'audio',
				)
			),
		);

		if ( ! empty( $defaults['html'] ) && 'utf-8' === strtolower( mb_internal_encoding() ) ) {

			$defaults['ellipsis'] = "\xe2\x80\xa6";
		}

		$atts = wp_parse_args( $atts, $defaults );

		// Strip all shortcode from the text.
		$string = strip_shortcodes( $string );

		// Strip escaped shortcodes.
		$string = str_replace( ']]>', ']]&gt;', $string );

		if ( $atts['html'] ) {

			if ( mb_strlen( preg_replace( '/<.*?>/', '', $string ) ) <= $atts['length'] ) {

				return $string;
			}

			$totalLength = mb_strlen( self::stripTags( $atts['more'] ) );
			$openTags    = array();
			$truncate    = '';

			$string = self::stripTags( $string, false, '<' . implode( '><', $atts['allowed_tags'] ) . '>' );

			preg_match_all( '/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $string, $tags, PREG_SET_ORDER );

			foreach ( $tags as $tag ) {

				if ( ! preg_match( '/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2] ) ) {

					if ( preg_match( '/<[\w]+[^>]*>/s', $tag[0] ) ) {

						array_unshift( $openTags, $tag[2] );

					} elseif ( preg_match( '/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag ) ) {

						$pos = array_search( $closeTag[1], $openTags );

						if ( false !== $pos ) {

							array_splice( $openTags, $pos, 1 );
						}
					}

				}

				$truncate     .= $tag[1];
				$contentLength = mb_strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3] ) );

				if ( $contentLength + $totalLength > $atts['length'] ) {

					$left           = $atts['length'] - $totalLength;
					$entitiesLength = 0;

					if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE ) ) {

						foreach ( $entities[0] as $entity ) {

							if ( $entity[1] + 1 - $entitiesLength <= $left ) {

								$left--;
								$entitiesLength += mb_strlen( $entity[0] );

							} else {

								break;
							}
						}
					}

					$truncate .= mb_substr( $tag[3], 0, $left + $entitiesLength );
					break;

				} else {

					$truncate    .= $tag[3];
					$totalLength += $contentLength;
				}

				if ( $totalLength >= $atts['length'] ) {
					break;
				}

			}

		} else {

			if ( mb_strlen( $string ) <= $atts['length'] ) {

				return $string;
			}

			$truncate = mb_substr( $string, 0, $atts['length'] - mb_strlen( $atts['more'] ) );
		}

		if ( ! $atts['exact'] ) {

			$spacepos = mb_strrpos( $truncate, ' ' );

			if ( $atts['html'] ) {

				$truncateCheck = mb_substr( $truncate, 0, $spacepos );
				$lastOpenTag   = mb_strrpos( $truncateCheck, '<' );
				$lastCloseTag  = mb_strrpos( $truncateCheck, '>' );

				if ( $lastOpenTag > $lastCloseTag ) {

					preg_match_all( '/<[\w]+[^>]*>/s', $truncate, $lastTagMatches );

					$lastTag  = array_pop( $lastTagMatches[0] );
					$spacepos = mb_strrpos( $truncate, $lastTag ) + mb_strlen( $lastTag );
				}

				$bits = mb_substr( $truncate, $spacepos );

				preg_match_all( '/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER );

				if ( ! empty( $droppedTags ) ) {

					if ( ! empty( $openTags ) ) {

						foreach ( $droppedTags as $closingTag ) {

							if ( ! in_array( $closingTag[1], $openTags ) ) {

								array_unshift( $openTags, $closingTag[1] );
							}
						}

					} else {

						foreach ( $droppedTags as $closingTag ) {

							$openTags[] = $closingTag[1];
						}
					}
				}
			}

			$truncate = mb_substr( $truncate, 0, $spacepos );

			// If truncate still empty, then we don't need to count ellipsis in the cut.
			if ( 0 === mb_strlen( $truncate ) ) {

				$truncate = mb_substr( $string, 0, $atts['length'] );
			}
		}

		$truncate .= $atts['more'];

		if ( $atts['html'] ) {

			foreach ( $openTags as $tag ) {

				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	/**
	 * Strips all numeric characters from the supplied string and returns the string.
	 *
	 * @since 9.11
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function stripNonNumeric( $string ) {

		return preg_replace( '/[^0-9]/', '', $string );
	}

	/**
	 * Generate a more truly "random" alpha-numeric string.
	 *
	 * NOTE:  If @see openssl_random_pseudo_bytes() does not exist, this will silently fall back to
	 *        {@see cnString::quickRandom()}.
	 *
	 * Function borrowed from Laravel 4.2
	 *
	 * @link https://github.com/laravel/framework/blob/4.2/src/Illuminate/Support/Str.php
	 *
	 * @since 8.3
	 *
	 * @param int $length
	 *
	 * @return string|WP_Error Random string on success, WP_Error on failure.
	 */
	public static function random( $length = 16 ) {

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {

			$bytes = openssl_random_pseudo_bytes( $length * 2 );

			if ( false === $bytes ) {

				return new WP_Error( 'general_random_string', __( 'Unable to generate random string.', 'connections' ) );
			}

			return substr( str_replace( array( '/', '+', '=' ), '', base64_encode( $bytes ) ), 0, $length );
		}

		return self::quickRandom( $length );
	}

	/**
	 * Generate a "random" alpha-numeric string.
	 *
	 * Should not be considered sufficient for cryptography, etc.
	 *
	 * Function borrowed from Laravel 5.1
	 * @link https://github.com/laravel/framework/blob/5.1/src/Illuminate/Support/Str.php#L270
	 *
	 * @since 8.3
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function quickRandom( $length = 16 ) {

		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return substr( str_shuffle( str_repeat( $pool, $length ) ), 0, $length );
	}
}
