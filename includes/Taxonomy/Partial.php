<?php
namespace Connections_Directory\Taxonomy\Partial;

use cnSanitize;
use cnSettingsAPI;
use cnTerm;
use Connections_Directory\Utility\_array;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve category parents with separator.
 *
 * NOTE: This is the Connections equivalent of @see get_category_parents() in WordPress core ../wp-includes/category-template.php
 *
 * @access public
 * @since  8.5.18
 * @static
 *
 * @param int    $id       Category ID.
 * @param string $taxonomy The term taxonomy.
 * @param array  $atts     The attributes array. {
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
function getTermParents( $id, $taxonomy, $atts = array() ) {

	$defaults = array(
		'permalink'  => _array::get( $atts, 'link', false ),
		'separator'  => '/',
		'nicename'   => false,
		'visited'    => array(),
		'force_home' => false,
		'home_id'    => cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' ),
	);

	$atts = cnSanitize::args( $atts, $defaults );

	$chain  = '';
	$parent = cnTerm::get( $id, $taxonomy );

	if ( is_wp_error( $parent ) ) {

		return $parent;
	}

	if ( $atts['nicename'] ) {

		$name = $parent->slug;

	} else {

		$name = $parent->name;
	}

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $atts['visited'] ) ) {

		$atts['visited'][] = $parent->parent;

		$chain .= getTermParents( $parent->parent, $taxonomy, $atts );
	}

	if ( true === $atts['permalink'] ) {

		$class = 'cn-' . esc_attr( sanitize_html_class( $taxonomy ) ) . '-breadcrumb-item';
		$id    = 'cn-' . esc_attr( $taxonomy ) . '-breadcrumb-item-' . esc_attr( $parent->term_id );
		$href  = esc_url( cnTerm::permalink( $parent->term_id, $taxonomy, $atts ) );
		$sep   = esc_html( $atts['separator'] );

		$chain .= '<span class="' . $class . '" id="' .  $id . '">' . '<a href="' . $href . '">' . $name . '</a>' . $sep . '</span>';

	} else {

		$chain .= $name . esc_html( $atts['separator'] );
	}

	return $chain;
}
