<?php

add_action(
	'Connections_Directory/Render/Template/Entry/Before',
	/**
	 * @param array        $atts     The shortcode attributes.
	 * @param cnEntry_HTML $entry    The current Entry.
	 * @param cnTemplate   $template The current template.
	 */
	function( $atts, $entry, $template ) {
		do_action_deprecated(
			'cn_action_entry_before',
			array( $atts, $entry ),
			'9.10',
			'Connections_Directory/Render/Template/Entry/Before'
		);
	},
	10,
	3
);

add_action(
	'Connections_Directory/Render/Template/Entry/Before',
	/**
	 * @param array        $atts     The shortcode attributes.
	 * @param cnEntry_HTML $entry    The current Entry.
	 * @param cnTemplate   $template The current template.
	 */
	function( $atts, $entry, $template ) {
		do_action_deprecated(
			'cn_action_entry_both',
			array( $atts, $entry ),
			'9.10',
			'Connections_Directory/Render/Template/Entry/Before',
			'Hook into both `Connections_Directory/Render/Template/Entry/Before`, at priority 11,  and `Connections_Directory/Render/Template/Entry/After`, at priority 9, instead.'
		);
	},
	11,
	3
);

add_action(
	'Connections_Directory/Render/Template/Entry/After',
	/**
	 * @param array        $atts     The shortcode attributes.
	 * @param cnEntry_HTML $entry    The current Entry.
	 * @param cnTemplate   $template The current template.
	 */
	function( $atts, $entry, $template ) {
		do_action_deprecated(
			'cn_action_entry_both',
			array( $atts, $entry ),
			'9.10',
			'Connections_Directory/Render/Template/Entry/After',
			'Hook into both `Connections_Directory/Render/Template/Entry/Before`, at priority 11,  and `Connections_Directory/Render/Template/Entry/After`, at priority 9, instead.'
		);
	},
	9,
	3
);

add_action(
	'Connections_Directory/Render/Template/Entry/After',
	/**
	 * @param array        $atts     The shortcode attributes.
	 * @param cnEntry_HTML $entry    The current Entry.
	 * @param cnTemplate   $template The current template.
	 */
	function( $atts, $entry, $template ) {
		do_action_deprecated(
			'cn_action_entry_after',
			array( $atts, $entry ),
			'9.10',
			'Connections_Directory/Render/Template/Entry/After'
		);
	},
	10,
	3
);

add_action(
	'Connections_Directory/Render/Template/Single_Entry/Before',
	/**
	 * @param array        $atts     The shortcode attributes.
	 * @param cnEntry_HTML $entry    The current Entry.
	 * @param cnTemplate   $template The current template.
	 */
	function( $atts, $entry, $template ) {
		do_action_deprecated(
			'cn_entry_actions-before',
			array( $atts, $entry ),
			'9.10',
			'Connections_Directory/Render/Template/Single_Entry/Before',
			'Set priority 9 in order to have the same placement as deprecated hook.'
		);
	},
	9,
	3
);

add_action(
	'Connections_Directory/Render/Template/Single_Entry/Before',
	/**
	 * @param array        $atts     The shortcode attributes.
	 * @param cnEntry_HTML $entry    The current Entry.
	 * @param cnTemplate   $template The current template.
	 */
	function( $atts, $entry, $template ) {
		do_action_deprecated(
			'cn_entry_actions',
			array( $atts, $entry ),
			'9.10',
			'Connections_Directory/Render/Template/Single_Entry/Before'
		);
	},
	10,
	3
);

add_action(
	'Connections_Directory/Render/Template/Single_Entry/After',
	/**
	 * @param array        $atts     The shortcode attributes.
	 * @param cnEntry_HTML $entry    The current Entry.
	 * @param cnTemplate   $template The current template.
	 */
	function( $atts, $entry, $template ) {
		do_action_deprecated(
			'cn_entry_actions-after',
			array( $atts, $entry ),
			'9.10',
			'Connections_Directory/Render/Template/Single_Entry/After'
		);
	},
	10,
	3
);

add_action(
	'cn_clean_entry_cache',
	function() {
		do_action_deprecated(
			'cn_process_cache-entry',
			array(),
			'9.15',
			'cn_clean_entry_cache'
		);
	},
	10,
	3
);

add_action(
	'Connections_Directory/Taxonomy/category/Attach_Terms',
	/**
	 * @param cnEntry $entry
	 * @param array   $terms
	 * @param string  $action
	 */
	function( $entry, $terms, $action ) {
		do_action_deprecated(
			'cn_process_taxonomy-category',
			array( $action, $entry->getId() ),
			'10.2',
			'Connections_Directory/Taxonomy/{taxonomy_slug}/Attach_Terms'
		);
	},
	10,
	3
);
