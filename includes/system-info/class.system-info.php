<?php

/**
 * Class cnSystem_Info
 */
class cnSystem_Info {

	/**
	 * The log type to which the system info should be logged to.
	 *
	 * @since 8.2.10
	 * @var   string
	 */
	const LOG_TYPE = 'cn-system-info';

	/**
	 * Get the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @global wpdb $wpdb
	 *
	 * @uses   Browser()
	 * @uses   Connections_Directory()
	 * @uses   wp_get_theme()
	 * @uses   cnSystem_Info::getHost()
	 *
	 * @return string
	 */
	public static function get() {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! class_exists( 'Browser' ) ) {
			require_once CN_PATH . 'vendor/browser/Browser.php';
		}

		$browser = new Browser();

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Get theme info
		$theme_data   = wp_get_theme();
		$theme        = $theme_data->Name . ' ' . $theme_data->Version;
		$parent_theme = $theme_data->Template;

		if ( ! empty( $parent_theme ) ) {

			$parent_theme_data = wp_get_theme( $parent_theme );
			$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
		}

		// Try to identify the hosting provider
		$host = self::getHost();

		ob_start();
		require_once CN_PATH . 'includes/system-info/inc.system-info.php';
		return ob_get_clean();
	}

	/**
	 * Display the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   esc_html()
	 * @uses   cnSystem_Info::get()
	 */
	public static function display() {

		echo esc_html( self::get() );
	}


	/**
	 * The wp_ajax_ callback to create the system info text file for download.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   nocache_headers()
	 * @uses   current_time()
	 * @uses   wp_strip_all_tags()
	 * @uses   cnSystem_Info::get()
	 */
	public static function download() {

		$filename = apply_filters(
			'cn_system_info_filename',
			'connections-system-info-' . current_time( 'Y-m-d_H-i-s' )
		);

		nocache_headers();
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename=' . $filename . '.txt' );
		header( "Expires: 0" );

		echo wp_strip_all_tags( self::get() );
		exit;
	}

	/**
	 * The template_redirect action callback used to "remotely" display the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   cnCache::get()
	 * @uses   cnSystem_Info::display()
	 */
	public static function view() {

		if ( ! isset( $_GET['cn-system-info'] ) || empty( $_GET['cn-system-info'] ) ) {
			return;
		}

		$queryValue = $_GET['cn-system-info'];
		$token      = cnCache::get( 'system_info_remote_token', 'option-cache' );

		if ( $queryValue == $token ) {

			/** WordPress Plugin Administration API */
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			require_once( ABSPATH . 'wp-admin/includes/update.php' );

			echo '<pre>';
			self::display();
			echo '</pre>';
			exit;

		} else {

			wp_redirect( home_url() );
			exit;
		}

	}

	/**
	 * Email the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   add_filter()
	 * @uses   cnEmail()
	 * @uses   sanitize_email()
	 * @uses   sanitize_text_field()
	 * @uses   esc_html()
	 * @uses   cnSystem_Info::get()
	 * @uses   remove_filter()
	 *
	 * @param array $atts {
	 *     @type string $from_email The "from" email address.
	 *     @type string $from_name  The "from" name.
	 *     @type string $to_email   The "to" email address.
	 *     @type string $to_name    The "to" name.
	 *     @type string $subject    The email subject.
	 *     @type string $message    The message to precede the system info.
	 * }
	 *
	 * @return bool
	 */
	public static function email( $atts ) {

		$defaults = array(
			'from_email' => '',
			'from_name'  => '',
			'to_name'    => '',
			'to_email'   => '',
			'subject'    => '',
			'message'    => '',
		);

		$atts = cnSanitize::args( $atts, $defaults );

		$email = new cnEmail();

		// Set email to be sent as HTML.
		$email->HTML();

		// Set from whom.
		$email->from(
			sanitize_email( $atts['from_email'] ),
			sanitize_text_field( $atts['from_name'] )
		);

		// Set to whom.
		$email->to( sanitize_email( $atts['to_email'] ) );

		// Set the subject.
		$email->subject( sanitize_text_field( $atts['subject'] ) );

		$message = sanitize_text_field( $atts['message'] );
		$message .= PHP_EOL . PHP_EOL;
		$message .= '<pre>' .  esc_html( self::get() ) . '</pre>';

		// Set the message.
		$email->message( $message );

		add_filter( 'cn_email_header', array( __CLASS__, 'setEmailHeader' ) );

		$response = $email->send();

		remove_filter( 'cn_email_header', array( __CLASS__, 'setEmailHeader' ) );

		return $response;
	}

	/**
	 * Register the "cn-system-info" log type.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public static function registerEmailLogType( $types ) {

		$types[ self::LOG_TYPE ] = array(
			'id'       => self::LOG_TYPE,
			'name'     => __( 'System Info Email', 'connections' ),
		);

		return $types;
	}

	/**
	 * NOTE: Uses the @see cnLog_Email::viewLogs() view.
	 *
	 * @param array $view
	 *
	 * @return array
	 */
	public static function registerLogView( $view ) {

		$view[ self::LOG_TYPE ] = array(
			'id'       => self::LOG_TYPE,
			'name'     => __( 'System Info Email', 'connections' ),
			'callback' => array( 'cnLog_Email', 'viewLogs' )
		);

		return $view;
	}

	/**
	 * Add the custom email header to set the "cn-system-info" email log type.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @param array $header
	 *
	 * @return array
	 */
	public static function setEmailHeader( $header ) {

		$header[] = 'X-CN-Log-Type: cn-system-info';

		return $header;
	}

	/**
	 * Size Conversions.
	 *
	 * @author Chris Christoff
	 * @link   https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/release/2.4/includes/misc-functions.php#L521
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @param  string $v
	 * @return int|string
	 */
	public static function let_to_num( $v ) {
		$l   = substr( $v, -1 );
		$ret = substr( $v, 0, -1 );

		switch ( strtoupper( $l ) ) {
			case 'P': // fall-through
			case 'T': // fall-through
			case 'G': // fall-through
			case 'M': // fall-through
			case 'K': // fall-through
				$ret *= 1024;
				break;
			default:
				break;
		}

		return $ret;
	}

	/**
	 * Get user host
	 *
	 * Returns the web host this site is using if possible.
	 *
	 * @author    Pippin Williamson
	 * @copyright Copyright (c) 2015, Pippin Williamson
	 * @link      https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/release/2.4/includes/misc-functions.php#L188
	 *
	 * @since  8.3
	 * @return string $host if detected
	 */
	public static function getHost() {

		if ( defined( 'WPE_APIKEY' ) ) {
			$host = 'WP Engine';
		} elseif ( defined( 'PAGELYBIN' ) ) {
			$host = 'Pagely';
		} elseif ( 'localhost:/tmp/mysql5.sock' == DB_HOST ) {
			$host = 'ICDSoft';
		} elseif ( 'mysqlv5' == DB_HOST ) {
			$host = 'NetworkSolutions';
		} elseif ( FALSE !== strpos( DB_HOST, 'ipagemysql.com' ) ) {
			$host = 'iPage';
		} elseif ( FALSE !== strpos( DB_HOST, 'ipowermysql.com' ) ) {
			$host = 'IPower';
		} elseif ( FALSE !== strpos( DB_HOST, '.gridserver.com' ) ) {
			$host = 'MediaTemple Grid';
		} elseif ( FALSE !== strpos( DB_HOST, '.pair.com' ) ) {
			$host = 'pair Networks';
		} elseif ( FALSE !== strpos( DB_HOST, '.stabletransit.com' ) ) {
			$host = 'Rackspace Cloud';
		} elseif ( FALSE !== strpos( DB_HOST, '.sysfix.eu' ) ) {
			$host = 'SysFix.eu Power Hosting';
		} elseif ( FALSE !== strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) ) {
			$host = 'Flywheel';
		} else {
			// Adding a general fallback for data gathering
			$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
		}

		return $host;
	}

	/**
	 * Render the result of the DESCRIBE `$table_name` to mimic the output from the commandline.
	 *
	 * @access public
	 * @since  8.5.4
	 * @static
	 *
	 * @param string $tableName The table name to render the DESCRIBE query result.
	 *
	 * @return string
	 */
	public static function describeTable( $tableName ) {

		/** var wpdb $wpdb */
		global $wpdb;

		$table  = '';
		$column = array();

		$structure = $wpdb->get_results( 'DESCRIBE ' . $tableName, ARRAY_A );
		$headers   = self::getTableHeaders( array_shift( $structure ) );
		$widths    = self::getColumnWidths( $structure );

		foreach ( $headers as $header ) {

			$column[] = str_pad( '', $widths[ $header ], '-', STR_PAD_RIGHT );
		}

		$divider = '+-' . implode( '-+-', $column ) . '-+' . PHP_EOL;

		$table .= $divider;
		$column = array();

		foreach ( $headers as $header ) {

			$column[] = str_pad( $header, $widths[ $header ], ' ', STR_PAD_RIGHT );
		}

		$table .= '| ' . implode( ' | ', $column ) . ' |' . PHP_EOL;
		$table .= $divider;
		$column = array();

		foreach ( $structure as $row ) {

			foreach ( $row as $header => $cell ) {

				$column[] = str_pad( $cell, $widths[ $header ], ' ', STR_PAD_RIGHT );
			}

			$table .= '| ' . implode( ' | ', $column ) . ' |' . PHP_EOL;
			$table .= $divider;
			$column = array();
		}

		return $table;
	}

	/**
	 * Use to get the column header names.
	 *
	 * @access private
	 * @since  8.5.4
	 * @static
	 *
	 * @param array $structure The result a $wpdb->get_results( 'DESCRIBE ' . $tableName, ARRAY_A ) query.
	 *
	 * @return array
	 */
	private static function getTableHeaders( $structure ) {

		$headers = array_keys( $structure );

		return $headers;
	}

	/**
	 * Get the max column width.
	 *
	 * @access private
	 * @since  8.5.4
	 * @static
	 *
	 * @param array $structure The result a $wpdb->get_results( 'DESCRIBE ' . $tableName, ARRAY_A ) query.
	 *
	 * @return array
	 */
	private static function getColumnWidths( $structure ) {

		$widths = array();

		// Loop through the data for each column meta.
		foreach ( $structure as $row ) {

			// Loop through the meta for each column.
			foreach ( $row as $header => $value ) {

				// Check to see if the column width for column meta was already recorded.
				if ( isset( $widths[ $header ] ) ) {

					// Check the existing recorded column width  against the current value width, record the larger of the two.
					$widths[ $header ] = strlen( $value ) > $widths[ $header ] ? strlen( $value ) : $widths[ $header ];

				} else {

					// Record the column width of the meta value.
					$widths[ $header ] = strlen( $value );
				}

				// In case there is no value, use the column name as the column width.
				$widths[ $header ] = strlen( $header ) > $widths[ $header ] ? strlen( $header ) : $widths[ $header ];
			}
		}

		return $widths;
	}
}
