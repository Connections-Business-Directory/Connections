<?php

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
			array( $rules, $post->rewrite['slug']),
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
