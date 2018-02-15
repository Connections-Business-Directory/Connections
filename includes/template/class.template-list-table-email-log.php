<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Terms List Table class.
 *
 * @package     Connections
 * @subpackage  Template Parts : Email Log Table
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.3
 * @access      private
 */
class CN_Email_Log_List_Table extends WP_List_Table {

	/**
	 * The current email log type which is being viewed.
	 *
	 * @access private
	 * @since  8.3
	 * @var array
	 */
	private $type = '';

	/**
	 * The number of terms to show per page.
	 *
	 * @access private
	 * @since  8.3
	 * @var int
	 */
	private $number;

	/**
	 * The offset which to start displaying terms from.
	 *
	 * @access private
	 * @since  8.3
	 * @var int
	 */
	private $offset;

	/**
	 * The term to search for.
	 *
	 * @access private
	 * @since  8.3
	 * @var string
	 */
	private $search = '';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @uses   cnTerm::getBy()
	 *
	 * @see    WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {

		$defaults = array(
			'type' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$this->type = $args['type'];

		parent::__construct(
			array(
				'plural'   => 'email',
				'singular' => 'email',
				'ajax'     => FALSE,
				//'screen' => isset( $args['screen'] ) ? $args['screen'] : NULL,
				//'screen'   => "connections-{$this->taxonomy}",
			)
		);
	}

	/**
	 * @see WP_List_Table::ajax_user_can()
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @return bool
	 */
	public function ajax_user_can() {

		return FALSE;
	}

	/**
	 * Prepare the table data.
	 *
	 * @access public
	 * @since  8.3
	 */
	public function prepare_items() {

		// @todo this should be a screen option.
		$per_page = $this->get_items_per_page( 'cn_logs_per_page', 20 );

		/**
		 * Filter the number of terms displayed per page for the terms list table.
		 *
		 * @since 8.2
		 *
		 * @param int $per_page Number of terms to be displayed.
		 */
		$per_page = apply_filters( 'cn_logs_per_page', $per_page );

		/**
		 * NOTE:
		 * Several of the $args vars are required in other parts of the class
		 * which is why they are also assigned to class vars as well as the local
		 * $args array var.
		 */

		$this->search = ! empty( $_REQUEST['s'] ) ? trim( wp_unslash( $_REQUEST['s'] ) ) : '';

		//if ( ! empty( $_REQUEST['orderby'] ) ) {
		//
		//	$args['orderby'] = $this->orderby = trim( wp_unslash( $_REQUEST['orderby'] ) );
		//}
		//
		//if ( ! empty( $_REQUEST['order'] ) ) {
		//
		//	$args['order'] = trim( wp_unslash( $_REQUEST['order'] ) );
		//}

		//Set variable because $per_page can be subsequently overridden if doing an orderby term query.
		$this->number = $per_page;

		$this->offset = $this->get_pagenum();

		// Query the all of terms.
		//if ( is_null( $this->orderby ) ) {
		//
		//	$args['number'] = $args['offset'] = 0;
		//}

		$this->items = $this->getLogs();

		$this->set_pagination_args(
			array(
				'total_items' => cnLog::getCount(
					0,
					$this->type
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
	 * Get the email logs for the type being viewed.
	 *
	 * @access private
	 * @since  8.3
	 *
	 * @return array
	 */
	private function getLogs() {

		/** If the email log type is @see cnLog_Email::LOG_TYPE, then return all email log types */
		if ( cnLog_Email::LOG_TYPE == $this->type ) {

			$types = wp_list_pluck( cnLog_Email::types(), 'name', 'id' );
			$type = array_keys( $types );

		} elseif ( '' == $this->type || '-1' == $this->type ) {

			$type = array_keys( wp_list_pluck( cnLog_Email::types(), 'name', 'id' ) );

		} else {

			$type = $this->type;
		}

		$data  = array();
		$query = array(
			'post_parent'    => null,
			'type'           => $type,
			'paged'          => $this->offset,
			//'meta_query'     => $this->get_meta_query(),
			'posts_per_page' => $this->number,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		    's' => $this->search,
		);

		$logs = cnLog::getConnected( $query );

		if ( $logs ) {

			foreach ( $logs as $log ) {

				$meta = get_post_custom( $log->ID );

				$data[] = array(
					'id'          => $log->ID,
					'date'        => date_i18n( 'Y-m-d H:i:s', strtotime( $log->post_date ) ),
					'subject'     => $log->post_title,
					'from'        => $meta[ cnLog::POST_META_PREFIX . 'from' ][0],
					'to'          => $meta[ cnLog::POST_META_PREFIX . 'to' ][0],
					'cc'          => $meta[ cnLog::POST_META_PREFIX . 'cc' ][0],
					'bcc'         => $meta[ cnLog::POST_META_PREFIX . 'bcc' ][0],
					'attachments' => $meta[ cnLog::POST_META_PREFIX . 'attachments' ][0],
					'status'      => $meta[ cnLog::POST_META_PREFIX . 'response' ][0],
				);
			}
		}

		return $data;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'id'          => __( 'ID', 'connections' ),
			'date'        => __( 'Date', 'connections' ),
			'subject'     => __( 'Subject', 'connections' ),
			'from'        => __( 'From', 'connections' ),
			'to'          => __( 'To', 'connections' ),
			'cc'          => __( 'CC', 'connections' ),
			'bcc'         => __( 'BCC', 'connections' ),
			'attachments' => __( 'Attachments', 'connections' ),
			'status'      => __( 'Sent', 'connections' ),
		);

		/**
		 * Filter the columns.
		 *
		 * @since 8.3
		 *
		 * @param array $columns
		 */
		return apply_filters( 'cn_email_log_table_columns', $columns );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => array( 'orderby', bool )
	 *
	 * @access protected
	 * @since  8.3
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {

		$columns = array(
			'date' => array( 'date', FALSE ),
		);

		/**
		 * Filter the columns.
		 *
		 * @since 8.3
		 *
		 * @param array $columns
		 */
		return apply_filters( 'cn_email_log_table_sortable_columns', $columns );
	}

	/**
	 * NOTE: This method is incomplete.
	 *
	 * Retrieve the hidden columns from the user settings meta.
	 *
	 * @access protected
	 * @since  8.3
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	protected function get_hidden_columns() {

		$columns = array(
			'id'
		);

		/**
		 * Filter the columns.
		 *
		 * @since 8.3
		 *
		 * @param array $columns
		 */
		return apply_filters( 'cn_email_table_hidden_columns', $columns );
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

		return 'id';
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @access protected
	 * @since  8.3
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
	 * Email log type filter.
	 *
	 * @access protected
	 * @since  8.3
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		if ( 'top' !== $which ) {

			return;
		}

		$emailLogTypes = wp_list_pluck( cnLog_Email::types(), 'name', 'id' );

		unset( $emailLogTypes[ cnLog_Email::LOG_TYPE ] );

		cnHTML::select(
			array(
				'id'      => 'type',
				'default' => array( -1 => __( 'View All', 'connections' ) ),
				'options' => $emailLogTypes,
			),
			$this->type
		);

		submit_button( 'Filter', 'secondary', 'filter_action', FALSE, array( 'id' => 'email-log-query-submit' ) );
	}

	/**
	 * Renders the checkbox column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_cb( $log ) {

		$subject = esc_attr( $log['subject'] );
		$id      = esc_attr( $log['id'] );

		return '<label class="screen-reader-text" for="cb-select-' . $id . '">' . sprintf( __( 'Select %s', 'connections' ), $subject ) . '</label>' .
		       '<input type="checkbox" name="log[]" value="' . $id . '" id="cb-select-' . $id . '" />';
	}

	/**
	 * Renders the ID column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return mixed
	 */
	public function column_id( $log ) {
		return $log['id'];
	}

	/**
	 * Renders the date column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return mixed
	 */
	public function column_date( $log ) {

		return $log['date'];
	}

	/**
	 * Renders the from email address column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_from( $log ) {

		return cnLog_Email::viewLogItem( 'from', $log['from'] );
	}

	/**
	 * Renders the to email addresses column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_to( $log ) {

		return cnLog_Email::viewLogItem( 'to', $log['to'] );
	}

	/**
	 * Renders the courtesy copy email addresses column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_cc( $log ) {

		return cnLog_Email::viewLogItem( 'cc', $log['cc'] );
	}

	/**
	 * Renders the blind courtesy copy email addresses column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_bcc( $log ) {

		return cnLog_Email::viewLogItem( 'bcc', $log['bcc'] );
	}

	/**
	 * Renders the subject and log action items column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_subject( $log ) {

		$form    = new cnFormObjects();
		$actions = array();
		$out     = '';

		$viewURL = add_query_arg(
			array(
				'action' => 'cn_log_email_view',
				'log_id' => $log['id'] ),
			admin_url( 'admin.php' )
		);

		$viewURL = esc_url( $viewURL );
		$subject = esc_html( $log['subject'] );

		//$action = ;

		$deleteURL = $form->tokenURL(
			add_query_arg(
				array(
					'page' => 'connections_tools',
					'tab'  => 'logs',
					'type' => $this->type,
					'cn-action' => 'delete_log',
					'id' => $log['id'],
				),
				self_admin_url( 'admin.php' )
			),
			'log_delete_' . $log['id']
		);

		$out .= '<strong><a class="row-title" href="' . $viewURL . '" title="' .
		        esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'connections' ), $subject ) ) . '">' . $subject . '</a></strong><br />';

		$actions['delete'] = "<a class='delete-log' href='" . esc_url( $deleteURL ) . "'>" . __( 'Delete', 'connections' ) . "</a>";
		$actions['view']   = '<a href="' . $viewURL . '">' . __( 'View', 'connections' ) . '</a>';

		$out .= $this->row_actions( $actions );

		return $out;
	}

	/**
	 * Renders the attachments column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_attachments( $log ) {

		return cnLog_Email::viewLogItem( 'attachments', $log['attachments'] );
	}

	/**
	 * Renders the email sent status column.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @param array $log The log item data.
	 *
	 * @return string
	 */
	public function column_status( $log ) {

		return cnLog_Email::viewLogItem( 'response', $log['status'] );
	}

	/**
	 * Renders any custom columns.
	 *
	 * @access protected
	 * @since  8.3
	 *
	 * @param array  $log         The log item data.
	 * @param string $column_name The column name.
	 *
	 * @return string
	 */
	protected function column_default( $log, $column_name ) {

		/**
		 * Filter the displayed columns in the terms list table.
		 *
		 * The dynamic portion of the hook name, `$this->screen->taxonomy`,
		 * refers to the slug of the current taxonomy.
		 *
		 * @since 8.3
		 *
		 * @param array $log The email lod meta data.
		 */

		return apply_filters( 'cn_manage_email_log_custom_column', $log );
	}
}
