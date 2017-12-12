<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Terms List Table class.
 *
 * @package     Connections
 * @subpackage  Template Parts : Term Admin Table
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2
 * @access      private
 */
class CN_Term_Admin_List_Table extends WP_List_Table {

	/**
	 * The taxonomy to build the table for.
	 *
	 * @access private
	 * @since  8.2
	 * @var string
	 */
	private $taxonomy;

	/**
	 * The field to order by @see cnTerm::getTaxonomyTerms().
	 *
	 * @access private
	 * @since  8.2
	 * @var string
	 */
	private $orderby = NULL;

	/**
	 * The term to search for @see cnTerm::getTaxonomyTerms().
	 *
	 * @access private
	 * @since  8.2
	 * @var string
	 */
	private $search = '';

	/**
	 * The number of terms to show per page.
	 *
	 * @access private
	 * @since  8.2
	 * @var int
	 */
	private $number;

	/**
	 * The offset which to start displaying terms from.
	 *
	 * @access private
	 * @since  8.2
	 * @var int
	 */
	private $offset;

	/**
	 * How deep a decedent term is.
	 *
	 * @access private
	 * @since  8.2
	 * @var int
	 */
	private $level;

	/**
	 * The term ID of the default term.
	 *
	 * @access private
	 * @since  8.2.7
	 * @var int
	 */
	private $default_term;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   cnTerm::getBy()
	 *
	 * @see    WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {

		$defaults = array(
			'taxonomy' => 'category',
		);

		$args = wp_parse_args( $args, $defaults );

		// @todo allow this to be settable via an option in arg. Also should verify the taxonomy exists.
		$this->taxonomy = $args['taxonomy'];

		parent::__construct(
			array(
				'plural'   => 'terms',
				'singular' => 'term',
				'ajax'     => FALSE,
				//'screen' => isset( $args['screen'] ) ? $args['screen'] : NULL,
				//'screen'   => "connections-{$this->taxonomy}",
			)
		);

		$this->default_term = get_option( "cn_default_{$this->taxonomy}" );
	}

	/**
	 * @see WP_List_Table::ajax_user_can()
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @return bool
	 */
	public function ajax_user_can() {

		return FALSE;
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   WP_List_Table::get_items_per_page()
	 * @uses   WP_List_Table::get_pagenum()
	 * @uses   WP_List_Table::set_pagination_args()
	 * @uses   apply_filters()
	 * @uses   wp_unslash()
	 * @uses   cnTerm::getTaxonomyTerms()
	 * @uses   CN_Term_Admin_List_Table::get_columns()
	 * @uses   CN_Term_Admin_List_Table::get_hidden_columns()
	 * @uses   CN_Term_Admin_List_Table::get_sortable_columns()
	 */
	public function prepare_items() {

		// @todo this should be a screen option.
		$per_page = $this->get_items_per_page( "cn_edit_{$this->taxonomy}_per_page", 100 );

		/**
		 * Filter the number of terms displayed per page for the terms list table.
		 *
		 * @since 8.2
		 *
		 * @param int $per_page Number of terms to be displayed.
		 */
		$per_page = apply_filters( "cn_edit_{$this->taxonomy}_per_page", $per_page );

		/**
		 * NOTE:
		 * Several of the $args vars are required in other parts of the class
		 * which is why they are also assigned to class vars as well as the local
		 * $args array var.
		 */

		$this->search = ! empty( $_REQUEST['s'] ) ? trim( wp_unslash( $_REQUEST['s'] ) ) : '';

		$args = array(
			'page'       => $this->get_pagenum(),
			'number'     => $per_page,
			'hide_empty' => FALSE,
			'search'     => $this->search,
		);

		if ( ! empty( $_REQUEST['orderby'] ) ) {

			$args['orderby'] = $this->orderby = trim( wp_unslash( $_REQUEST['orderby'] ) );
		}

		if ( ! empty( $_REQUEST['order'] ) ) {

			$args['order'] = trim( wp_unslash( $_REQUEST['order'] ) );
		}

		// Set variable because $args['number'] can be subsequently overridden if doing an orderby term query.
		$this->number = $args['number'];

		$args['offset'] = $this->offset = ( $args['page'] - 1 ) * $args['number'];

		// Query the all of terms.
		if ( is_null( $this->orderby ) ) {

			$args['number'] = $args['offset'] = 0;
		}

		$this->items = cnTerm::getTaxonomyTerms( $this->taxonomy, $args );

		$this->set_pagination_args(
			array(
				'total_items' => cnTerm::getTaxonomyTerms(
					$this->taxonomy,
					array(
						'hide_empty' => FALSE,
						'search'     => $this->search,
						'fields'     => 'count'
					)
				),
				'per_page'    => $per_page,
				//'total_pages' => $set_me, /** This will by calculated by @see WP_List_Table::set_pagination_args() if not supplied. */
			)
		);

		/**
		 * NOTE: If these methods are overridden @see WP_List_Table::get_column_info(),
		 * then the column filter in @see get_column_headers() is not run.
		 *
		 * As a workaround filters are added to the following methods. The downside
		 * is that the screen options to hide columns are not added. The only way for that
		 * to happen seems to be to init the table class on the `load-{page-hook}` action
		 * and set it as a global var so it can be accessed in the callback function that
		 * renders the plugin's admin page.
		 */
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @access protected
	 * @since  8.2
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {

		$actions = array(
			'delete' => __( 'Delete', 'connections' ),
		);

		return $actions;
	}

	/**
	 * Returns the current action selected from the bulk actions drop down.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @return bool|string
	 */
	public function current_action() {

		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['category'] ) && ( 'delete' == $_REQUEST['action'] || 'delete' == $_REQUEST['action2'] ) ) {

			return 'bulk-delete';
		}

		return parent::current_action();
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => _x( 'Name', 'term name', 'connections' ),
			'description' => __( 'Description', 'connections' ),
			'slug'        => __( 'Slug', 'connections' ),
			'posts'       => __( 'Count', 'connections' ), // Using the 'posts' key to take advantage of core WP CSS.
			'links'       => __( 'ID', 'connections' ), // Using the 'posts' key to take advantage of core WP CSS.
		);

		/**
		 * Filter the columns.
		 *
		 * @since 8.2
		 *
		 * @param array $columns
		 */
		return apply_filters( "cn_{$this->taxonomy}_table_columns", $columns );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => array( 'orderby', bool )
	 *
	 * @access protected
	 * @since  8.2
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {

		$columns =  array(
			'name'        => array( 'name', FALSE ),
			'description' => array( 'description', FALSE ),
			'slug'        => array( 'slug', FALSE ),
			'posts'       => array( 'count', FALSE ),
		);

		/**
		 * Filter the columns.
		 *
		 * @since 8.2
		 *
		 * @param array $columns
		 */
		return apply_filters( "cn_{$this->taxonomy}_table_sortable_columns", $columns );
	}

	/**
	 * NOTE: This method is incomplete.
	 * @todo Finish me.
	 *
	 * Retrieve the hidden columns from the user settings meta.
	 *
	 * @access protected
	 * @since  8.2
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	protected function get_hidden_columns() {

		/**
		 * Filter the columns.
		 *
		 * @since 8.2
		 *
		 * @param array $columns
		 */
		return apply_filters( "cn_{$this->taxonomy}_table_hidden_columns", array() );
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @access protected
	 * @since  8.5.6
	 *
	 * @return string Name of the default primary column.
	 */
	protected function get_default_primary_column_name() {

		return 'name';
	}

	/**
	 * Render the body of the table.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   WP_List_Table::get_column_count()
	 * @uses   WP_List_Table::no_items()
	 * @uses   WP_List_Table::display_rows()
	 * @uses   CN_Term_Admin_List_Table::_rows()
	 * @uses   cnTerm::get_hierarchy()
	 */
	public function display_rows_or_placeholder() {

		$count = 0;

		if ( empty( $this->items ) ) {

			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';

			return;
		}

		if ( is_null( $this->orderby ) ) {

			// Ignore children on searches.
			if ( ! empty( $this->search ) ) {

				$children = array();

			} else {

				$children = cnTerm::get_hierarchy( $this->taxonomy );
			}

			// Some funky recursion to get the job done( Paging & parents mainly ) is contained within,
			// Skip it for non-hierarchical taxonomies for performance sake
			$this->_rows( $this->taxonomy, $this->items, $children, $this->offset, $this->number, $count );

		} else {

			$this->display_rows();
		}
	}

	/**
	 * Recursive method to prepare to render a table row maintaining parent/child term relationships.
	 *
	 * @access private
	 * @since  8.2
	 *
	 * @param string $taxonomy
	 * @param array  $terms
	 * @param array  $children
	 * @param int    $start
	 * @param int    $per_page
	 * @param int    $count
	 * @param int    $parent
	 * @param int    $level
	 */
	private function _rows( $taxonomy, $terms, &$children, $start, $per_page, &$count, $parent = 0, $level = 0 ) {

		$end = $start + $per_page;

		foreach ( $terms as $key => $term ) {

			if ( $count >= $end )
				break;

			if ( $term->parent != $parent && empty( $_REQUEST['s'] ) )
				continue;

			// If the page starts in a subtree, print the parents.
			if ( $count == $start && $term->parent > 0 && empty( $_REQUEST['s'] ) ) {
				$my_parents = $parent_ids = array();
				$p = $term->parent;
				while ( $p ) {
					$my_parent = cnTerm::get( $p, $taxonomy );
					$my_parents[] = $my_parent;
					$p = $my_parent->parent;
					if ( in_array( $p, $parent_ids ) ) // Prevent parent loops.
						break;
					$parent_ids[] = $p;
				}
				unset( $parent_ids );

				$num_parents = count( $my_parents );
				while ( $my_parent = array_pop( $my_parents ) ) {
					echo "\t";
					$this->single_row( $my_parent, $level - $num_parents );
					$num_parents--;
				}
			}

			if ( $count >= $start ) {
				echo "\t";
				$this->single_row( $term, $level );
			}

			++$count;

			unset( $terms[ $key ] );

			if ( isset( $children[ $term->term_id ] ) && empty( $_REQUEST['s'] ) )
				$this->_rows( $taxonomy, $terms, $children, $start, $per_page, $count, $term->term_id, $level + 1 );
		}
	}

	/**
	 * Render the term table row.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   sanitize_term()
	 * @uses   WP_List_Table::single_row_columns()
	 *
	 * @staticvar string $class
	 *
	 * @param object $term
	 * @param int    $level
	 */
	public function single_row( $term, $level = 0 ) {

		$term = sanitize_term( $term, 'cn_' . $this->taxonomy );

		static $class = '';
		$class = $class == '' ? ' class="alternate"' : '';

		$this->level = $level;

		echo '<tr id="tag-' . $term->term_id . '"' . $class . '>';
		$this->single_row_columns( $term );
		echo '</tr>';
	}

	/**
	 * Render the term checkbox column.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @param  object $term
	 *
	 * @return string
	 */
	public function column_cb( $term ) {

		if ( $term->term_id != $this->default_term ) {

			return '<label class="screen-reader-text" for="cb-select-' . $term->term_id . '">' .
			      sprintf( __( 'Select %s', 'connections' ), $term->name ) .
			       '</label>' . '<input type="checkbox" name="' . $this->taxonomy . '[]" value="' . $term->term_id . '" id="cb-select-' . $term->term_id . '" />';
		}

		return '&nbsp;';
	}

	/**
	 * Render the term name column.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   cnFormObjects::tokenURL()
	 * @uses   apply_filters()
	 * @uses   esc_attr()
	 * @uses   cnTerm::permalink()
	 * @uses   WP_List_Table::row_actions()
	 *
	 * @param  object $term
	 *
	 * @return string
	 */
	public function column_name( $term ) {

		$form    = new cnFormObjects();
		$actions = array();
		$out     = '';

		$pad = str_repeat( '&#8212; ', max( 0, $this->level ) );

		/**
		 * Filter display of the term name in the terms list table.
		 *
		 * The default output may include padding due to the term's
		 * current level in the term hierarchy.
		 *
		 * @since 8.2
		 *
		 * @see   WP_Terms_List_Table::column_name()
		 *
		 * @param string $pad_tag_name The term name, padded if not top-level.
		 * @param object $term         Term object.
		 */
		$name = apply_filters( 'cn_term_name', $pad . ' ' . $term->name, $term );
		$uri  = wp_get_referer();

		$location = add_query_arg(
			array(
				'page'            => $_GET['page'],
				'cn-action'       => "edit_{$this->taxonomy}",
				'id'              => $term->term_id,
				//'wp_http_referer' => urlencode( wp_unslash( $uri ) ),
			),
			$uri
		);

		$editURL = $form->tokenURL(
			$location,
			"{$this->taxonomy}_edit_{$term->term_id}"
		);

		$out .= '<strong><a class="row-title" href="' . esc_url( $editURL ) . '" title="' .
		        esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'connections' ), $name ) ) . '">' . $name . '</a></strong><br />';

		$actions['edit']   = '<a href="' . esc_url( $editURL ) . '">' . __( 'Edit', 'connections' ) . '</a>';

		if ( $term->term_id != $this->default_term ) {

			$deleteURL = $form->tokenURL(
				"admin.php?cn-action=delete-term&id={$term->term_id}&taxonomy={$this->taxonomy}",
				'term_delete_' . $term->term_id
			);

			$actions['delete'] = "<a class='delete-tag' href='" . esc_url( $deleteURL ) . "'>" . __( 'Delete', 'connections' ) . "</a>";
		}

		$actions['view']   = '<a href="' . cnTerm::permalink( $term ) . '">' . __( 'View', 'connections' ) . '</a>';

		/**
		 * Filter the action links displayed for each term in the terms list table.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
		 *
		 * @since 8.2
		 *
		 * @param array  $actions An array of action links to be displayed. Default
		 *                        'Edit', 'Delete', and 'View'.
		 * @param object $term    Term object.
		 */
		$actions = apply_filters( "cn_{$this->taxonomy}_row_actions", $actions, $term );

		$out .= $this->row_actions( $actions );

		return $out;
	}

	/**
	 * Render the term description column.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @param  object $term
	 *
	 * @return string
	 */
	public function column_description( $term ) {

		return $term->description;
	}

	/**
	 * Render the term slug column.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   apply_filters()
	 *
	 * @param  object $term
	 *
	 * @return string
	 */
	public function column_slug( $term ) {

		/**
		 * Filter the editable term slug.
		 *
		 * @since 8.2
		 *
		 * @param string $slug The current term slug.
		 */
		return apply_filters( 'cn_editable_slug', $term->slug );
	}

	/**
	 * Render the term count column.
	 *
	 * NOTE: Using the "posts" column ID in order to take advantage of core WP CSS styles.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @uses   number_format_i18n()
	 * @uses   cnFormObjects::tokenURL()
	 *
	 * @param  object $term
	 *
	 * @return string
	 */
	public function column_posts( $term ) {

		$form = new cnFormObjects();

		$count = number_format_i18n( $term->count );

		$categoryFilterURL = $form->tokenURL( 'admin.php?cn-action=filter&category=' . $term->term_id, 'filter' );

		// For now, limit the count filter to only the `category` taxonomy.
		if ( $count && 'category' === $this->taxonomy ) {

			$out = '<a href="' . $categoryFilterURL . '">' . $count . '</a>';

		} else {

			$out = $count;
		}

		return $out;
	}

	/**
	 * Render the term count column.
	 *
	 * NOTE: Using the "links" column ID in order to take advantage of core WP CSS styles.
	 *
	 * @access public
	 * @since  8.2
	 *
	 * @param  object $term
	 *
	 * @return string
	 */
	public function column_links( $term ) {

		return $term->term_id;
	}

	/**
	 * @param object $term
	 * @param string $column_name
	 */
	public function column_default( $term, $column_name ) {

		/**
		 * Filter the displayed columns in the terms list table.
		 *
		 * The dynamic portion of the hook name, `$this->screen->taxonomy`,
		 * refers to the slug of the current taxonomy.
		 *
		 * @since 8.2
		 *
		 * @param object $term        Term object.
		 * @param string $column_name Name of the column.
		 * @param int    $term_id     Term ID.
		 */

		do_action( "cn_manage_{$this->taxonomy}_custom_column", $term, $column_name, $term->term_id );
	}
}
