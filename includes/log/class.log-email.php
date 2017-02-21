<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Log all email sent through @see cnEmail.
 *
 * @package     Connections
 * @subpackage  Email Logger
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.2.10
 */
final class cnLog_Email {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since  8.2.10
	 *
	 * @var cnLog_Email
	 */
	private static $instance;

	/**
	 * @since 8.2.10
	 * @var   string
	 */
	const LOG_TYPE = 'cn-email';

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
	 */
	public static function init() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof cnLog_Email ) ) {

			self::$instance = new cnLog_Email;

			// Register log type.
			add_filter( 'cn_log_types', array( __CLASS__, 'registerLogType' ) );

			// Register the log view.
			add_filter( 'cn_log_views', array( __CLASS__, 'registerLogView' ) );
			add_action( 'admin_action_cn_log_email_view', array( __CLASS__, 'viewLog') );

			// Add action to log email after they are sent.
			add_action( 'cn_email_post_send', array( __CLASS__, 'add' ), 10, 11 );

			// Add filter to format meta key for display.
			add_filter( 'cn_log_meta_key', array( __CLASS__, 'metaKey') );

			// Add filter to format meta value for display.
			add_filter( 'cn_log_meta_value', array( __CLASS__, 'metaValue'), 10, 2 );
		}
	}

	/**
	 * Return an instance.
	 *
	 * @access public
	 * @since  8.5.34
	 *
	 * @return self
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Callback used to register the email log types with @see cnLog().
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @uses   apply_filters()
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public static function registerLogType( $types ) {

		$types[ self::LOG_TYPE ] = array(
			'id'   => self::LOG_TYPE,
			'name' => __( 'System Email', 'connections' ),
		);

		return apply_filters( 'cn_email_log_types', $types );
	}

	/**
	 * Get the registered email log types.
	 *
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	public static function types() {

		$types = array();

		$types[ self::LOG_TYPE ] = array(
			'id'   => self::LOG_TYPE,
			'name' => __( 'System Email', 'connections' ),
		);

		$types = apply_filters( 'cn_email_log_types', $types );

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
	 * Callback to register the email log view.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @param $view
	 *
	 * @return array
	 */
	public static function registerLogView( $view ) {

		$view[ self::LOG_TYPE ] = array(
			'id'       => self::LOG_TYPE,
			'name'     => __( 'System Email', 'connections' ),
			'callback' => array( __CLASS__, 'viewLogs' )
		);

		return $view;
	}

	/**
	 * Callback which renders the email log view.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   esc_attr()
	 * @uses   cnTemplatePart::table()
	 * @uses   get_current_screen()
	 * @uses   CN_Email_Log_List_Table::prepare_items()
	 * @uses   CN_Email_Log_List_Table::display()
	 * @uses   esc_html_e()
	 * @uses   do_action()
	 * @uses   CN_Email_Log_List_Table::search_box()
	 */
	public static function viewLogs() {

		$type = '';

		if ( isset( $_REQUEST['type'] ) && ! empty( $_REQUEST['type'] ) ) {

			$type = esc_attr( $_REQUEST['type'] );
		}

		/** @var CN_Email_Log_List_Table $table */
		$table = cnTemplatePart::table(
			'email-log',
			array(
				'screen' => get_current_screen()->id,
				'type' => $type
			)
		);

		$table->prepare_items();

		?>
		<h2><?php esc_html_e( 'Email Logs', 'connections' ); ?></h2>
		<?php do_action( 'cn_logs_email_top' ); ?>
		<form id="cn-email-logs-filter" method="get">
			<?php
			$table->search_box( __( 'Search', 'connections' ), self::LOG_TYPE );
			$table->display();
			?>
			<input type="hidden" name="cn-action" value="log_bulk_actions">
			<!--<input type="hidden" name="page" value="connections_tools"/>-->
			<!--<input type="hidden" name="tab" value="logs"/>-->
		</form>
		<?php do_action( 'cn_logs_email_bottom' ); ?>
	<?php
	}

	/**
	 * Callback which renders the email log detail view.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   wp_enqueue_style()
	 */
	public static function viewLog() {

		$id = empty( $_GET['log_id'] ) ? 0 : absint( $_GET['log_id'] );

		if ( ! $id ) {
			return;
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		$post      = get_post( $id );
		$post_meta = get_post_meta( $id );
		$meta      = array();

		foreach ( $post_meta as $key => $value ) {

			if ( FALSE === strpos( $key, cnLog::POST_META_PREFIX ) ) continue;

			$key = str_replace( cnLog::POST_META_PREFIX, '', $key );

			$meta[ $key ] = $value[0];
		}

		wp_enqueue_style( 'cn-admin' );

		require_once( ABSPATH . 'wp-admin/admin-header.php' );
		require_once( CN_PATH . 'includes/log/inc.log-email-detail.php' );
		require_once( ABSPATH . 'wp-admin/admin-footer.php' );
	}

	/**
	 * Returns email log meta data item as human readable.
	 *
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   cnFormatting::maybeJSONdecode()
	 * @uses   esc_html()
	 *
	 * @param $type
	 * @param $value
	 *
	 * @return string
	 */
	public static function viewLogItem( $type, $value ) {

		switch ( $type ) {

			case 'headers':

				$value = implode( '<br>', cnFormatting::maybeJSONdecode( $value ) );
				break;

			case 'type':
			case 'character_set':
				$value = esc_html( $value );
				break;

			case 'from':

				$value = esc_html( $value );

				break;

			case 'to':
			case 'cc':
			case 'bcc':
			case 'attachments':

				$value = cnFormatting::maybeJSONdecode( $value );

				if ( empty( $value ) ) {

					$value = __( 'None', 'connections' );

				} else {

					if ( is_array( $value ) ) {

						$value = implode( '<br>', array_map( 'esc_html', $value ) );

					} else {

						$value = esc_html( $value );
					}

				}

				break;

			case 'response':
				$value = ( 'success' == $value ? __( 'Successfully', 'connections' ) : __( 'Failed', 'connections' ) );
				break;
		}

		return $value;
	}

	/**
	 * @param $headers
	 * @param $type
	 * @param $charSet
	 * @param $from
	 * @param $to
	 * @param $cc
	 * @param $bcc
	 * @param $subject
	 * @param $message
	 * @param $attachments
	 * @param $response
	 */
	public static function add( $headers, $type, $charSet, $from, $to, $cc, $bcc, $subject, $message, $attachments, $response ) {

		$data = array(
			'post_title'   => $subject,
			'post_content' => $message,
			'type'         => self::parseLogType( $headers ),
		);

		$meta = array(
			'headers'       => self::parseHeader( $headers ),
			'type'          => $type,
			'character_set' => $charSet,
			'from'          => self::parseFrom( $from ),
			'to'            => self::parseTo( $to ),
			'cc'            => self::parseCC( $cc ),
			'bcc'           => self::parseCC( $bcc ),
			'attachments'   => self::parseAttachments( $attachments ),
			'response'      => self::parseResponse( $response ),
		);

		cnLog::insert( $data, $meta );
	}

	/**
	 * Parse the email headers for the "X-CN-Log-Type" header to set the log type.
	 *
	 * If header does not exist or is set to an invalid type the default type will be set.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param array $headers
	 *
	 * @return string
	 */
	private static function parseLogType( $headers ) {

		$type = self::LOG_TYPE;

		foreach ( $headers as $header ) {

			if ( FALSE !== strpos( $header, 'X-CN-Log-Type: ' ) ) {

				$type = str_replace( 'X-CN-Log-Type: ', '', $header );
			}

		}

		return cnLog::valid( $type ) ? $type : self::LOG_TYPE;
	}

	/**
	 * Header can be supplied as either an array or string. Convert to string if it is an array.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param array|string $headers
	 *
	 * @return mixed
	 */
	private static function parseHeader( $headers ) {

		$header = is_array( $headers ) ? cnFormatting::maybeJSONencode( $headers ) : $headers;

		return $header;
	}

	/**
	 * Convert the "from" address from an array to a string.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param array $from
	 *
	 * @return string
	 */
	private static function parseFrom( $from ) {

		if ( isset( $from['name'] ) && ! empty( $from['name'] ) ) {

			$field = sprintf( '%1$s <%2$s>', $from['name'], $from['email'] );

		} elseif ( isset( $from['email'] ) && ! empty( $from['email'] ) ) {

			$field = sprintf( '%s', $from['email'] );

		} else {

			$field = '';
		}

		return $field;
	}

	/**
	 * Convert the "to" addresses from an array to a comma delimited string.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param array $to
	 *
	 * @return mixed
	 */
	private static function parseTo( $to ) {

		return ! empty( $to ) ? cnFormatting::maybeJSONencode( $to ) : '';
	}

	/**
	 * Convert the "cc" and "bcc" addresses from an array to a comma delimited string.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param array $cc
	 *
	 * @return mixed
	 */
	private static function parseCC( $cc ) {

		$field = array();
		$count = count( $cc );

		if ( 1 <= $count ) {

			for ( $i = 0; $i < $count; $i++ ) {

				if ( isset( $cc[ $i ]['name'] ) && ! empty( $cc[ $i ]['name'] ) ) {

					$field[] = sprintf( '%1$s <%2$s>', $cc[ $i ]['name'], $cc[ $i ]['email'] );

				} elseif ( isset( $cc[ $i ]['email'] ) && ! empty( $cc[ $i ]['email'] ) ) {

					$field[] = sprintf( '%s', $cc[ $i ]['email'] );

				}
			}

		}

		return ! empty( $field ) ? cnFormatting::maybeJSONencode( $field ) : '';
	}

	/**
	 * Attachments can be supplied as either an array or string. Convert to string if it is an array.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param array|string $attachments
	 *
	 * @return mixed
	 */
	private static function parseAttachments( $attachments ) {

		return is_array( $attachments ) ? cnFormatting::maybeJSONencode( $attachments ) : $attachments;
	}

	/**
	 * The @see wp_mail() function returns a bool, this parses the bool to either the "success" or "fail" string.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param bool $response
	 *
	 * @return string
	 */
	private static function parseResponse( $response ) {

		return $response ? 'success' : 'fail';
	}

	/**
	 * Change the meta key names to be human readable.
	 *
	 * @access private
	 * @since 8.2.10
	 * @static
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public static function metaKey( $key ) {

		switch ( $key ) {

			case 'headers':
				$key = __( 'Email Headers:', 'connections' );
				break;

			case 'type':
				$key = __( 'Email Type:', 'connections' );
				break;

			case 'character_set':
				$key = __( 'Email Character Set:', 'connections' );
				break;

			case 'from':
				$key = __( 'Email From:', 'connections' );
				break;

			case 'to':
				$key = __( 'Email To:', 'connections' );
				break;

			case 'cc':
				$key = __( 'Email CC:', 'connections' );
				break;

			case 'bcc':
				$key = __( 'Email BCC:', 'connections' );
				break;

			case 'attachments':
				$key = __( 'Email Attachments:', 'connections' );
				break;

			case 'response':
				$key = __( 'Email Sent:', 'connections' );
				break;
		}

		return $key;
	}

	/**
	 * Change the meta values to be human readable.
	 *
	 * @access private
	 * @since  8.2.10
	 * @static
	 *
	 * @param string $value
	 * @param string $key
	 *
	 * @return string
	 */
	public static function metaValue( $value, $key ) {

		switch ( $key ) {

			case 'headers':

				$value = cnFormatting::maybeJSONdecode( $value );
				$value = '<ul><li>' . implode( '</li><li>', $value ) . '</li></ul>';
				break;

			case 'type':
			case 'character_set':
				$value = '<p>' . esc_html( $value ) . '</p>';
				break;

			case 'from':

				if ( empty( $value ) ) {

					$value = '<p>' . __( 'None', 'connections' ) . '</p>';

				} else {

					$value = '<p>' . esc_html( $value ) . '</p>';
				}

				break;

			case 'to':
			case 'cc':
			case 'bcc':
			case 'attachments':

				$value = cnFormatting::maybeJSONdecode( $value );

				if ( empty( $value ) ) {

					$value = '<p>' . __( 'None', 'connections' ) . '</p>';

				} else {

					if ( is_array( $value ) ) {

						$value = '<ul><li>' . implode( '</li><li>', array_map( 'esc_html', $value ) ) . '</li></ul>';

					} else {

						$value = '<ul><li>' . esc_html( $value ) . '</li></ul>';
					}

				}

				break;

			case 'response':
				$value = '<p>' . ( 'success' == $value ? __( 'Successfully', 'connections' ) : __( 'Failed', 'connections' ) ) . '</p>';
				break;
		}

		return $value;
	}
}
