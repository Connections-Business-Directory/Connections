<?php

namespace Connections_Directory\Taxonomy;

use Connections_Directory\Utility\_array;

/**
 * Callback for the `setup_theme` action.
 *
 * NOTE: The `setup_theme` action is the action run closest after initializing of the $wp_rewrite global variable.
 *
 * @internal
 * @since 10.2
 */
function init() {

	/**
	 * Mimics the core WP function.
	 * @see  create_initial_taxonomies()
	 */

	global $wp_rewrite;

	$options = get_option( 'connections_permalink', array() );

	if ( ! is_array( $options ) ) {

		$options = array();
	}

	$category = _array::get( $options, 'category_base', 'cat' );
	$tag      = _array::get( $options, 'tag_base', 'tg' );

	// Get the taxonomy registry.
	$taxonomies = Registry::get();

	$taxonomies->register(
		'category',
		array(
			'hierarchical'          => true,
			'query_var'             => 'cn-cat-slug',
			'rewrite'               => array(
				'hierarchical' => true,
				'slug'         => $category,
				'with_front'   => ! $category || $wp_rewrite->using_index_permalinks(),
			),
			'public'                => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'_builtin'              => true,
			//'capabilities'          => array(
			//	'manage_terms' => 'connections_manage_categories',
			//	'edit_terms'   => 'connections_edit_categories',
			//	'delete_terms' => 'connections_delete_categories',
			//	'assign_terms' => 'connections_assign_categories',
			//),
			'show_in_rest'          => true,
			'rest_base'             => 'categories',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		)
	);

	// $taxonomies->register(
	// 	'tag',
	// 	array(
	// 		'hierarchical'          => false,
	// 		'query_var'             => 'cn-tag',
	// 		'rewrite'               => array(
	// 			'hierarchical' => false,
	// 			'slug'         => $tag,
	// 			'with_front'   => ! $tag || $wp_rewrite->using_index_permalinks(),
	// 		),
	// 		'public'                => true,
	// 		'show_ui'               => true,
	// 		'show_admin_column'     => true,
	// 		'_builtin'              => true,
	// 		//'capabilities'          => array(
	// 		//	'manage_terms' => 'connections_manage_tags',
	// 		//	'edit_terms'   => 'connections_edit_tags',
	// 		//	'delete_terms' => 'connections_delete_tags',
	// 		//	'assign_terms' => 'connections_assign_tags',
	// 		//),
	// 		'show_in_rest'          => true,
	// 		'rest_base'             => 'tags',
	// 		'rest_controller_class' => 'WP_REST_Terms_Controller',
	// 	)
	// );

	/**
	 * Fires after initializing the Registry object.
	 *
	 * Additional taxonomies should be registered on this hook.
	 *
	 * @since 10.2
	 *
	 * @param Registry $taxonomies Taxonomy Registry object.
	 */
	do_action( 'Connections_Directory/Taxonomy/Init', $taxonomies );
}
