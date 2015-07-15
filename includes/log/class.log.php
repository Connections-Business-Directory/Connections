<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class for logging events and errors.
 *
 * Based on the WP Logging Class by Pippin Williamson -- Copyright (c) 2012, Pippin Williamson
 * @link https://github.com/pippinsplugins/WP-Logging
 *
 * @package     Connections
 * @subpackage  Log API
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2.10
 *
 * Log Types
 * =========
 * Log entries are designed to be separated into "types". By default there are no types. They must be registered
 * using the `cn_log_types` filter.
 *
 * <code>
 * function cn_add_log_types( $types ) {
 *
 *     $types[] = 'registration';
 *     return $types;
 * }
 * add_filter( 'cn_log_types', 'cn_add_log_types' );
 * </code>
 *
 * Inserting Log Entries
 * =====================
 *
 * There are two ways to record a log entry, one is quick and simple and the one is more involved,
 * but also gives more control.
 *
 * Using @see cnLog::add():
 *
 * <code>
 *     $log = cnLog::add( $title = '', $message = '', $parent = 0, $type = null );
 * </code>
 *
 *  * $title   (string) - The log title
 *  * $message (string) - The log message
 *  * $parent  (int)    - The post object ID that you want this log entry connected to, if any
 *  * $type    (string) - The type classification to give this log entry. Must be one of the types registered
 *                        in `cnLog::types()`. This is optional.
 *
 * A sample log entry insert might look like this:
 *
 * <code>
 *     $title   = 'Payment Error';
 *     $message = 'There was an error processing the payment. Here are details of the transaction: (details shown here)'
 *     $parent  = 46; // This might be the ID of a payment post type item we want this log item connected to
 *     $type    = 'error';
 *
 *     cnLog::add( $title, $message, $parent, $type );
 * </code>
 *
 * Or, without a type:
 *
 * <code>
 *     $title   = 'Payment Error';
 *     $message = 'There was an error processing the payment. Here are details of the transaction: (details shown here)'
 *     $parent  = 46; // This might be the ID of a payment post type item we want this log item connected to
 *
 *     cnLog::add( $title, $message, $parent );
 * </code>
 *
 * Using @see cnLog::insert():
 *
 * <code>
 *     $log = cnLog::insert( $data = array(), $meta = array() );
 * </code>
 *
 * This method requires that all log data be passed via arrays. One array is used for the main post object data and
 * one for additional log meta to be recorded with the log entry.
 *
 * The `$data` array accepts all arguments that can be passed to @see wp_insert_post() with one additional parameter for `type`.
 *
*@link http://codex.wordpress.org/Function_Reference/wp_insert_post
 *
 * The $data array expects key/value pairs for any meta data that should be recorded with the log entry.
 * The meta data is stored is normal post meta, though all meta data is prefixed with `_cn_log_`.
 *
 * Creating a log entry with @see cnLog::insert() might look like this:
 *
 * <code>
 *     $data = array(
 *         'post_title'   => 'Payment Error',
 *         'post_content' => 'There was an error processing the payment. Here are details of the transaction: (details shown here)',
 *         'post_parent'  => 46, // This might be the ID of a payment post type item we want this log item connected to
 *         'type'         => 'error'
 *     );
 *
 *     $meta = array(
 *         'customer_ip' => 'xxx.xx.xx.xx', // the customer's IP address
 *         'user_id'     => get_current_user_id() // the ID number of the currently logged-in user
 *     );
 *
 *     $log = cnLog::insert( $data, $meta );
 * </code>
 *
 * Retrieving Log Entries
 * ======================
 *
 * There are two methods for retrieving entries that have been stored via this logging class:
 *
 *  * `cnLog::get( $object_id = 0, $type = '', $paged = -1 )`
 *  * `cnLog::getConnected( $args = array() )`
 *
 * @see cnLog::get() is the simple method that lets you quickly retrieve logs that are connected to a specific post object.
 * For example, to retrieve error logs connected to post ID 57, you'd do:
 *
 * <code>
 *     $logs = cnLog::get( 57, 'error' );
 * </code>
 *
 * This will retrieve the first 20 related to ID 57. Note that the third parameter is for `$paged`.
 * This allows you to pass a page number (in the case you're building an admin UI for showing logs) and
 * cnLog will adjust the logs retrieved to match the page number.
 *
 * If you need more granular control, you will want to use @see cnLog::getConnected(). This method takes a
 * single array of key/value pairs as the only parameter. The `$args` array accepts all arguments that
 * can be passed to @see get_posts(), with one additional parameter for `type`.
 * @link http://codex.wordpress.org/Function_Reference/get_posts
 *
 * Here's an example of using @see cnLog::getConnected():
 *
 * <code>
 *     $args = array(
 *         'post_parent'    => 57,
 *         'posts_per_page' => 10,
 *         'paged'          => get_query_var( 'paged' ),
 *         'type'           => 'error'
 *     );
 *
 *     $logs = cnLog::getConnected( $args );
 * </code>
 *
 * If you want to retrieve all log entries and ignore pagination, you can do this:
 *
 * <code>
 *     $args = array(
 *         'post_parent'    => 57,
 *         'posts_per_page' => -1,
 *         'type'           => 'error'
 *     );
 *     $logs = cnLog::getConnected( $args );
 * </code>
 *
 * Both cnLog::get() and cnLog::getConnected() will return a typical array of post objects, just like @see get_posts().
 * @link http://codex.wordpress.org/Template_Tags/get_posts
 *
 * Get Log Entry Counts
 * ======================
 *
 * The @see cnLog::getCount() method allows you to retrieve a count for the total number of log entries stored in the database.
 * It allows you to retrieve logs attached to a specific post object ID, of a particular type, and also allows you to
 * pass optional meta options for only counting log entries that have meta values stored.
 *
 * The method looks like this:
 *
 * <code>
 *     cnLog::getCount( $object_id = 0, $type = '', $meta_query = array() )
 * </code>
 *
 * To retrieve the total number of `error` logs attached to post 57, you can do this:
 *
 * <code>
 *     $count = cnLog::getCount( 57, 'error' );
 * </code>
 *
 * To retrieve the total number of logs (regardless of type) attached to post object ID 57, you can do this:
 *
 * <code>
 *     $count = cnLog::getCount( 57 );
 * </code>
 *
 * The third parameter is for passing a meta query array. This array should be in the same form as meta queries
 * passed to @see WP_Query. For example, to retrieve a count of error log entries that have a user IP that match a
 * specific IP address, you can do this:
 * @link http://codex.wordpress.org/Class_Reference/WP_Query
 *
 * <code>
 *     $meta_query = array(
 *         array(
 *             'key'   => '_wp_log_customer_ip', // the meta key
 *             'value' => 'xxx.xx.xx.xx'         // the IP address to retrieve logs for
 *         )
 *     );
 *     $count = cnLog::getCount( 57, 'error', $meta_query );
 * </code>
 *
 * Purging Logs
 * ======================
 *
 * To purge older logs you need to first set the purge conditional to true then set up a cron job to
 * perform the purging. It's recommended you set the cron job to hourly so that you can stay on top of
 * purging your logs. Running daily and deleting 100 logs (the default number) means that many sites would
 * never actually stay caught up with log purging.
 *
 * <code>
 *     add_filter( 'cn_log_purge', '__return_true' );
 *
 *     $scheduled = wp_next_scheduled( 'cn_log_purge_process' );
 *
 *     if ( false == $scheduled ) {
 *
 *         wp_schedule_event( time(), 'hourly', 'cn_log_purge_process' );
 *     }
 * </code>
 *
 * The default time period is to purge logs that are over 2 weeks old. To change that use `cn_log_purge_when`.
 * If we wanted to purge logs older than 1 month.
 *
 * <code>
 *     function change_purge_time(){
 *         return '1 month ago';
 *     }
 *     add_filter( 'cn_log_purge_when', 'change_purge_time' );
 * </code>
 *
 * The purge query is run via @see get_posts() and you can filter any argument in the array with the
 * `cn_log_purge_query_atts` filter. By default 100 logs are purged each time the purge process is run.
 * You could up this number if your server will handle the load.
 *
 * Logs are set to bypass the WordPress trash system. If you want to have logs hit the WordPress trash system
 * then you'd need to filter `cn_log_force_delete_log` and return false.
 *
 * <code>
 *     add_filter( 'cn_log_force_delete_log', '__return_false' );
 * </code>
 *
 * If you make your logs hit the WordPress trash then you'll need to write your own routine to clear the trash
 * so logs don't build up.
 */
final class cnLog {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since  8.2.10
	 *
	 * @var cnLog
	 */
	private static $instance;

	/**
	 * @since 8.2.10
	 * @var   string
	 */
	const POST_TYPE = 'cn_log';

	/**
	 * @since 8.2.10
	 * @var   string
	 */
	const TAXONOMY  = 'cn_log_type';

	/**
	 * @since 8.2.10
	 * @var   string
	 */
	const POST_META_PREFIX = '_cn_log_';

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since  8.2.10
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * @access public
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   add_action()
	 *
	 * @return cnLog
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof cnLog ) ) {

			self::$instance = new cnLog;

			// Register log post type.
			add_action( 'init', array( __CLASS__, 'registerPostType' ), 1 );

			// Register types taxonomy and default types.
			add_action( 'init', array( __CLASS__, 'registerTaxonomy' ), 1 );

			// Register the  actions for the logs views.
			add_action( 'init', array( __CLASS__, 'registerViews' ) );

			// Create a cron job for this hook to start pruning.
			add_action( 'cn_log_purge_process', array( __CLASS__, 'purge' ) );

			// Register a metabox for debugging purposes.
			add_action( 'add_meta_boxes', array( __CLASS__, 'registerMetabox' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the wp_log Post Type
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   register_post_type()
	 *
	 * @return void
	 */
	public static function registerPostType() {

		$atts = array(
			'public'          => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'labels'          => array(
				'name'               => _x( 'Logs', 'post type general name', 'connections' ),
				'singular_name'      => _x( 'Log', 'post type singular name', 'connections' ),
				'menu_name'          => 'Connections :: ' . __( 'Logs', 'connections' ),
				'add_new'            => _x( 'Add New', 'post', 'connections' ),
				'add_new_item'       => __( 'Add New Log', 'connections' ),
				'edit_item'          => __( 'Edit Log', 'connections' ),
				'new_item'           => __( 'New Log', 'connections' ),
				'view_item'          => __( 'View Log', 'connections' ),
				'search_items'       => __( 'Search Logs', 'connections' ),
				'not_found'          => __( 'No logs found.', 'connections' ),
				'not_found_in_trash' => __( 'No logs found in Trash.', 'connections' ),
				'all_items'          => __( 'All Logs', 'connections' )
			),
			'query_var'       => FALSE,
			'rewrite'         => FALSE,
			'capability_type' => 'post',
			'supports'        => array( 'title', 'editor' ),
			'can_export'      => FALSE,
			'capabilities'    => array(
				'create_posts'       => 'do_not_allow',
				'edit_post'          => 'activate_plugins',
				'edit_posts'         => 'activate_plugins',
				'edit_others_posts'  => 'activate_plugins',
				'delete_post'        => 'activate_plugins',
				'delete_posts'       => 'activate_plugins',
				'read_post'          => 'activate_plugins',
				'read_private_posts' => 'do_not_allow',
				'publish_posts'      => 'do_not_allow',
			),
			'map_meta_cap'    => FALSE,
		);

		register_post_type( self::POST_TYPE, apply_filters( 'cn_log_post_type_atts', $atts ) );
	}

	/**
	 * Registers the Type Taxonomy
	 *
	 * The Type taxonomy is used to determine the type of log entry
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   register_taxonomy()
	 * @uses   term_exists()
	 * @uses   wp_insert_term()
	 *
	 * @return void
	 */
	public static function registerTaxonomy() {

		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'public'            => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'labels'            => array(
					'name'                       => _x( 'Log Types', 'taxonomy general name', 'connections' ),
					'singular_name'              => _x( 'Type', 'taxonomy singular name', 'connections' ),
					'search_items'               => __( 'Search Log Types', 'connections' ),
					'popular_items'              => NULL,
					'all_items'                  => __( 'All Log Types', 'connections' ),
					'edit_item'                  => __( 'Edit Log Type', 'connections' ),
					'view_item'                  => __( 'View Log Type', 'connections' ),
					'update_item'                => __( 'Update Log Type', 'connections' ),
					'add_new_item'               => __( 'Add New Log Type', 'connections' ),
					'new_item_name'              => __( 'New Log Type Name', 'connections' ),
					'separate_items_with_commas' => __( 'Separate log types with commas', 'connections' ),
					'add_or_remove_items'        => __( 'Add or remove log types.', 'connections' ),
					'choose_from_most_used'      => __( 'Choose from the most used log types.', 'connections' ),
					'not_found'                  => __( 'No log types found.', 'connections' ),
				),
				'show_tagcloud'     => FALSE,
				'show_admin_column' => TRUE,
				'capabilities'      => array(
					'manage_terms' => 'activate_plugins',
					'edit_terms'   => 'activate_plugins',
					'delete_terms' => 'activate_plugins',
					'assign_terms' => 'activate_plugins',
				),
			)
		);

		$types = wp_list_pluck( self::types(), 'name', 'id' );

		if ( ! empty( $types ) ) {

			foreach ( $types as $type => $name ) {

				if ( ! term_exists( $type, self::TAXONOMY ) ) {

					wp_insert_term( $type, self::TAXONOMY );
				}
			}

		}
	}

	/**
	 * Callback on init hook to register the actions for the log views that have been registered.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function registerViews() {

		foreach ( self::views() as $view ) {

			add_action( 'cn_logs_view_' . $view['id'], $view['callback'] );
		}
	}

	/**
	 * Returns the log post type.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @return string
	 */
	public static function getPostType() {

		return self::POST_TYPE;
	}

	/**
	 * Returns the post meta prefix.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @return string
	 */
	public static function getPostMetaPrefix() {

		return self::POST_META_PREFIX;
	}

	/**
	 * Log types
	 *
	 * Sets up the default log types and allows for new ones to be created
	 *
	 * @access public
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	public static function types() {

		$types = apply_filters( 'cn_log_types', array() );

		foreach ( $types as $key => $type ) {

			// Each log type should be an array, if it not, it is not valid, remove it.
			if ( ! is_array( $type ) ) {

				unset( $types[ $key ] );
			}

			// Each log type must have at least an ID and a Name, if it does not, remove it.
			if ( ! isset( $type['id'] ) && ! isset( $type['name'] ) ) {

				unset( $types[ $key ] );
			}
		}

		return $types;
	}

	/**
	 * Get the registered log views.
	 *
	 * @access public
	 * @since  8.3
	 *
	 * @return array
	 */
	public static function views() {

		/**
		 * Filter used to register the meta about the view for a registered log type.
		 *
		 * @since 8.3
		 *
		 * @param array $args {
		 *     @type string       $id The log view ID.
		 *     @type string       $name The log view name.
		 *     @type array|string $callback The log view callback which will display teh logs for the registered log type.
		 * }
		 */
		return apply_filters( 'cn_log_views', array() );
	}

	/**
	 * Check if a log type is valid.
	 *
	 * Checks to see if the specified type is in the registered list of types.
	 *
	 * @access private
	 * @since  8.2.10
	 *
	 * @param  string $type
	 *
	 * @return bool
	 */
	public static function valid( $type ) {

		return array_key_exists( $type, wp_list_pluck( self::types(), 'name', 'id' ) );
	}

	/**
	 * Add a new log entry.
	 *
	 * Fast and simple way to add a new log.
	 * Use self::insert() if you need to store custom meta data
	 *
	 * @access public
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   self::insert()
	 *
	 * @param string $title
	 * @param string $message
	 * @param int    $parent
	 * @param string $type
	 *
	 * @return int|WP_Error The ID of the new log entry or the value 0 or WP_Error on failure.
	 */
	public static function add( $title = '', $message = '', $parent = 0, $type = '' ) {

		$data = array(
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'type'         => $type
		);

		return self::insert( $data );
	}

	/**
	 * Insert a new log entry.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   do_action()
	 * @uses   wp_parse_args()
	 * @uses   wp_insert_post()
	 * @uses   update_post_meta()
	 * @uses   wp_set_object_terms()
	 * @uses   sanitize_key()
	 * @uses   cnLog::valid()
	 *
	 * @param array $data
	 * @param array $meta
	 *
	 * @return int|WP_Error The ID of the newly created log item or the value 0 or WP_Error on failure.
	 */
	public static function insert( $data, $meta = array() ) {

		$defaults = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_parent'    => 0,
			'post_content'   => '',
			'type'           => FALSE
		);

		$data = wp_parse_args( $data, $defaults );

		do_action( 'cn_pre_insert_log', $data, $meta );

		// Insert the log entry.
		$id = wp_insert_post( $data );

		// Insert the log type, if supplied.
		if ( $data['type'] && self::valid( $data['type'] ) ) {

			wp_set_object_terms( $id, $data['type'], self::TAXONOMY, FALSE );
		}

		// Insert log meta, if supplied.
		if ( $id && ! empty( $meta ) ) {

			foreach ( $meta as $key => $value ) {

				update_post_meta( $id, self::POST_META_PREFIX . sanitize_key( $key ), $value );
			}
		}

		do_action( 'cn_post_insert_log', $id, $data, $meta );

		return $id;
	}

	/**
	 * Update and existing log entry.
	 *
	 * @access public
	 * @since  8.2.10
	 *
	 * @uses   wp_parse_args()
	 * @uses   wp_update_post()
	 * @uses   update_post_meta()
	 *
	 * @param array $data
	 * @param array $meta
	 *
	 * @return int|WP_Error The ID or the value 0 or WP_Error on failure.
	 */
	public static function update( $data, $meta = array() ) {

		$defaults = array(
			'post_type'   => self::POST_TYPE,
			'post_status' => 'publish',
			'post_parent' => 0
		);

		$data = wp_parse_args( $data, $defaults );

		do_action( 'cn_pre_update_log', $data, $meta );

		// Update the log entry.
		$id = wp_update_post( $data );

		if ( $id && ! empty( $meta ) ) {

			foreach ( $meta as $key => $value ) {

				if ( ! empty( $value ) ) {

					update_post_meta( $id, self::POST_META_PREFIX . sanitize_key( $key ), $value );
				}
			}
		}

		do_action( 'wp_post_update_log', $id, $data, $meta );

		return $id;
	}

	/**
	 * Delete a log.
	 *
	 * @access public
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   cnLog::valid()
	 * @uses   get_posts()
	 * @uses   wp_delete_post()
	 *
	 * @param int    $id
	 * @param string $type       Log type.
	 * @param array  $meta_query Log meta query.
	 *
	 * @return void
	 */
	public static function deleteConnected( $id = 0, $type = '', $meta_query = array() ) {

		$query = array(
			'post_parent'    => $id,
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids'
		);

		if ( ! empty( $type ) && self::valid( $type ) ) {

			$query['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $type,
				)
			);
		}

		if ( ! empty( $meta_query ) ) {

			$query['meta_query'] = $meta_query;
		}

		$logs = get_posts( $query );

		if ( $logs ) {

			foreach ( $logs as $log ) {

				wp_delete_post( $log->ID, TRUE );
			}
		}
	}

	/**
	 * Delete a specific log by ID.
	 *
	 * @param $id
	 *
	 * @return array|bool|WP_Post
	 */
	public static function delete( $id  ) {

		return wp_delete_post( $id, TRUE );
	}

	/**
	 * Retrieve log items for a an object ID.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   cnLog::getConnected()
	 *
	 * @param int    $id
	 * @param string $type
	 * @param int    $paged
	 *
	 * @return array|false An indexed array of the connected logs or false if none were found.
	 */
	public static function get( $id = 0, $type = '', $paged = 20 ) {

		return self::getConnected( array( 'post_parent' => $id, 'type' => $type, 'paged' => $paged ) );
	}

	/**
	 * Retrieve all connected logs.
	 *
	 * Used for retrieving logs related to particular items, such as a specific error.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   wp_parse_args()
	 * @uses   get_posts()
	 * @uses   get_query_var()
	 * @uses   cnLog::valid()
	 *
	 * @param array $atts
	 *
	 * @return array|false An indexed array of logs or false if none were found.
	 */
	public static function getConnected( $atts = array() ) {

		$defaults = array(
			'post_parent'    => 0,
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'paged'          => get_query_var( 'paged' ),
			'type'           => FALSE
		);

		$query = wp_parse_args( $atts, $defaults );

		if ( $query['type'] ) {

			if ( is_array( $query['type'] ) ) {

				$types = array();

				foreach ( $query['type'] as $type ) {

					if ( self::valid( $type ) ) $types[] = $type;
				}

			} else {

				$types = '';

				if ( self::valid( $query['type'] ) ) $types = $query['type'];
			}

			if ( ! empty( $types ) ) {

				$query['tax_query'] = array(
					array(
						'taxonomy' => self::TAXONOMY,
						'field'    => 'slug',
						'terms'    => $types
					)
				);

			}
		}

		$logs = get_posts( $query );

		if ( $logs ) {
			return $logs;
		}

		// No logs found.
		return FALSE;
	}

	/**
	 * Retrieves number of log entries connected to particular object ID
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   WP_Query()
	 * @uses   self::valid()
	 *
	 * @param int          $id
	 * @param array|string $type
	 * @param array        $meta_query
	 *
	 * @return int
	 */
	public static function getCount( $id = 0, $type = '', $meta_query = array() ) {

		$query = array(
			'post_parent'    => $id,
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish'
		);

		if ( ! empty( $type ) ) {

			if ( is_array( $type ) ) {

				$types = array();

				foreach ( $type as $id ) {

					if ( self::valid( $id ) ) $types[] = $id;
				}

			} else {

				$types = '';

				if ( self::valid( $type ) ) $types = $type;
			}

			$query['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $types
				)
			);

		}

		if ( ! empty( $meta_query ) ) {

			$query['meta_query'] = $meta_query;
		}

		$logs = new WP_Query( $query );

		return (int) $logs->post_count;
	}

	/**
	 * Allows you to tie in a cron job and purge logs.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   cnLog::getLogsToPurge()
	 * @uses   cnLog::PurgeLogs()
	 */
	public static function purge() {

		$purge = apply_filters( 'cn_log_purge', FALSE );

		if ( FALSE === $purge ) {
			return;
		}

		$logs = self::getLogsToPurge();

		if ( ! empty( $logs ) ) {

			self::PurgeLogs( $logs );
		}
	}

	/**
	 * Purge old logs.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   apply_filters()
	 * @uses   wp_delete_post()
	 *
	 * @param array $logs An array of to purge.
	 */
	private static function PurgeLogs( $logs ) {

		$force = apply_filters( 'cn_log_force_delete_log', TRUE );

		foreach ( $logs as $log ) {

			wp_delete_post( $log->ID, $force );
		}
	}

	/**
	 * Returns an array of logs to purge.
	 *
	 * @access public
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   apply_filters()
	 * @uses   get_posts()
	 *
	 * @return array $logs An array of logs that were returned from @see get_posts().
	 */
	private static function getLogsToPurge() {

		$how_old = apply_filters( 'cn_log_purge_when', '2 weeks ago' );

		$atts = array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => '100',
			'date_query'     => array(
				array(
					'column' => 'post_date',
					'before' => (string) $how_old,
				)
			)
		);

		$logs = get_posts( apply_filters( 'cn_log_purge_query_atts', $atts ) );

		return $logs;
	}

	/**
	 * Register the log meta metabox.
	 *
	 * @access public
	 * @since  8.2.10
	 * @static
	 */
	public static function registerMetabox() {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			add_meta_box(
				'cn_log_meta_box',
				__( 'Log Meta', 'connections' ),
				array( __CLASS__, 'renderMetabox' ),
				self::POST_TYPE,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Callback to display the log meta.
	 *
	 * @access public
	 * @since  8.2.10
	 * @static
	 */
	public static function renderMetabox() {

		$results = get_post_meta( get_the_ID() );

		if ( empty( $results ) ) {

			echo '<p>' . __( 'No log meta found.', 'connections' ) . '</p>';

		} else {

			echo '<dl>';

			foreach ( $results as $key => $meta ) {

				if ( FALSE === strpos( $key, self::POST_META_PREFIX ) ) continue;

				$keyBase    = str_replace( self::POST_META_PREFIX, '', $key );
				$keyDisplay = apply_filters( 'cn_log_meta_key', $keyBase );

				echo '<dt>' . esc_html( $keyDisplay ) . '</dt>';

				foreach ( $meta as $value ) {

					$value = apply_filters( 'cn_log_meta_value', $value, $keyBase );

					if ( empty( $value ) ) {

						echo '<dd><p>' . __( 'No meta value.', 'connections' ) . '</p></dd>';

					} else {

						echo '<dd>' . wp_kses_post( $value ) . '</dd>';
					}

				}
			}

			echo '</dl>';
		}

	}
}

function cnInitLogAPI() {

	cnLog::instance();
}

/*
 * Action added in the init hook to allow other plugins time to register there log types.
 * The priority is set at -1 because the post types and taxonomy are registered in the
 * init hook at priority 1.
 */
add_action( 'init', 'cnInitLogAPI', -1 );
