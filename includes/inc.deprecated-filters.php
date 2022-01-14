<?php

use Connections_Directory\Utility\_array;
use function Connections_Directory\Utility\_deprecated\_applyFilters as apply_filters_deprecated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'Connections_Directory/Rewrite/CPT_Rules/Landing',
	/**
	 * @since 10.2
	 *
	 * @param array        $rules
	 * @param WP_Post_Type $post
	 *
	 * @return array
	 */
	function( $rules, $post ) {

		return apply_filters_deprecated(
			'cn_cpt_rewrite_rule-landing',
			array(
				$rules,
				$post->rewrite['slug'],
			),
			'10.2',
			'Connections_Directory/Rewrite/CPT_Rules/Landing'
		);
	},
	10,
	2
);

add_filter(
	'Connections_Directory/Rewrite/CPT_Rules/View',
	/**
	 * @since 10.2
	 *
	 * @param array        $rules
	 * @param WP_Post_Type $post
	 *
	 * @return array
	 */
	function( $rules, $post ) {

		return apply_filters_deprecated(
			'cn_cpt_rewrite_rule-view',
			array( $rules, $post->rewrite['slug'] ),
			'10.2',
			'Connections_Directory/Rewrite/CPT_Rules/View'
		);
	},
	10,
	2
);

add_filter(
	'bulk_actions-connections_page_connections_manage_category_terms',
	/**
	 * @since 10.2
	 *
	 * @param array $actions An array of actions to register for display in the bulk action dropdown.
	 *
	 * @return array
	 */
	function( $actions ) {

		return apply_filters_deprecated(
			'bulk_actions-connections_page_connections_categories',
			array( $actions ),
			'10.2',
			'bulk_actions-connections_page_connections_manage_category_terms'
		);
	}
);

add_filter(
	'Connections_Directory/Query/Term/Get_Terms/Properties',
	/**
	 * @since 10.3
	 *
	 * @param array    $args       An array of get_terms() arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 */
	function( $args, $taxonomies ) {

		return apply_filters_deprecated(
			'cn_get_terms_args',
			array( $args, $taxonomies ),
			'10.3',
			'Connections_Directory/Query/Term/Get_Terms/Properties'
		);
	},
	10,
	2
);

add_filter(
	'Connections_Directory/Query/Term/Get_Terms/Exclude_Terms_Clause',
	/**
	 * @since 10.3
	 *
	 * @param string   $exclusions `NOT IN` clause of the terms query.
	 * @param array    $args       An array of terms query arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 */
	function( $exclusions, $args, $taxonomies ) {

		return apply_filters_deprecated(
			'cn_term_exclusions',
			array( $exclusions, $args, $taxonomies ),
			'10.3',
			'Connections_Directory/Query/Term/Get_Terms/Exclude_Terms_Clause'
		);
	},
	10,
	3
);

add_filter(
	'Connections_Directory/Query/Term/Get_Terms/Select_Fields',
	/**
	 * @since 10.3
	 *
	 * @param string[] $selects    An array of fields to select for the terms query.
	 * @param array    $args       An array of term query arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 */
	function( $selects, $args, $taxonomies ) {

		return apply_filters_deprecated(
			'cn_get_terms_fields',
			array( $selects, $args, $taxonomies ),
			'10.3',
			'Connections_Directory/Query/Term/Get_Terms/Select_Fields'
		);
	},
	10,
	3
);

add_filter(
	'Connections_Directory/Query/Term/Get_Terms/Clauses',
	/**
	 * @since 10.3
	 *
	 * @param string[] $clauses    Array of query SQL clauses.
	 * @param string[] $taxonomies An array of taxonomy names.
	 * @param array    $args       An array of term query arguments.
	 */
	function( $clauses, $taxonomies, $args ) {

		$clauses['orderBy'] = &$clauses['orderby'];
		_array::forget( $clauses, 'order' );

		return apply_filters_deprecated(
			'cn_terms_clauses',
			array( $clauses, $taxonomies, $args ),
			'10.3',
			'Connections_Directory/Query/Term/Get_Terms/Clauses'
		);
	},
	10,
	3
);
