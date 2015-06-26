<?php
/**
 * Default filters.
 *
 * @package     Connections
 * @subpackage  Filters
 * @copyright   @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Versions prior to 8.2.9 saved the `"` slashed in the db, example: \"
 * These filters will remove that slash while leaving all the others alone.
 *
 * NOTE: Disabled in favor of using @see wp_unslash() for the "display" context in
 * cnSanitize::field() for the bio and notes field.
 */
//add_filter( 'cn_bio', 'wp_kses_stripslashes', 9 );
//add_filter( 'cn_notes', 'wp_kses_stripslashes', 9 );

if ( isset( $GLOBALS['wp_embed'] ) ) {

	add_filter( 'cn_output_bio', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
	add_filter( 'cn_output_bio', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );

	add_filter( 'cn_output_notes', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
	add_filter( 'cn_output_notes', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
}

add_filter( 'cn_output_bio', 'make_clickable', 9 );

add_filter( 'cn_output_bio', 'wptexturize' );
add_filter( 'cn_output_bio', 'convert_smilies' );
add_filter( 'cn_output_bio', 'convert_chars' );
add_filter( 'cn_output_bio', 'wpautop' );
add_filter( 'cn_output_bio', 'shortcode_unautop' );
//add_filter( 'cn_output_bio', 'prepend_attachment' );

add_filter( 'cn_output_bio', 'capital_P_dangit', 11 );
add_filter( 'cn_output_bio', 'do_shortcode', 11 ); // AFTER wpautop()

add_filter( 'cn_output_notes', 'make_clickable', 9 );

add_filter( 'cn_output_notes', 'wptexturize' );
add_filter( 'cn_output_notes', 'convert_smilies' );
add_filter( 'cn_output_notes', 'convert_chars' );
add_filter( 'cn_output_notes', 'wpautop' );
add_filter( 'cn_output_notes', 'shortcode_unautop' );
//add_filter( 'cn_output_notes', 'prepend_attachment' );

add_filter( 'cn_output_notes', 'capital_P_dangit', 11 );
add_filter( 'cn_output_notes', 'do_shortcode', 11 ); // AFTER wpautop()

