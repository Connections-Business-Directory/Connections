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
	 *
	 * @uses   add_action()
	 *
	 * @return cnLog_Email
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof cnLog_Email ) ) {

			self::$instance = new cnLog_Email;

			// Register log type.
			add_filter( 'cn_log_types', array( __CLASS__, 'registerLogType' ) );

			// Add action to log email after they are sent.
			add_action( 'cn_email_post_send', array( __CLASS__, 'add' ), 10, 11 );

			// Add filter to format meta key for display.
			add_filter( 'cn_log_meta_key', array( __CLASS__, 'metaKey') );

			// Add filter to format meta value for display.
			add_filter( 'cn_log_meta_value', array( __CLASS__, 'metaValue'), 10, 2 );
		}

		return self::$instance;
	}

	/**
	 * @param array $types
	 *
	 * @return array
	 */
	public static function registerLogType( $types ) {

		$types[] = self::LOG_TYPE;

		return apply_filters( 'cn_email_log_types', $types );
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

// Fire up email logging of email sent through cnEmail.
cnLog_Email::instance();
