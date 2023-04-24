<?php

use Connections_Directory\Request;

/**
 * Class cnSystem_Info
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
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
	 * @internal
	 * @since 8.3
	 *
	 * @global wpdb $wpdb
	 *
	 * @return string
	 */
	public static function get() {

		/** @var wpdb $wpdb */
		global $wpdb;

		$browser = new Browser();

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Get theme info.
		$theme_data   = wp_get_theme();
		$theme        = $theme_data->Name . ' ' . $theme_data->Version;
		$parent_theme = $theme_data->Template;

		if ( ! empty( $parent_theme ) ) {

			$parent_theme_data = wp_get_theme( $parent_theme );
			$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
		}

		// Try to identify the hosting provider.
		$host = self::getHost();

		ob_start();
		require_once CN_PATH . 'includes/system-info/inc.system-info.php';
		return ob_get_clean();
	}

	/**
	 * Display the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function display() {

		echo trim( esc_textarea( self::get() ) );
	}


	/**
	 * The wp_ajax_ callback to create the system info text file for download.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function download() {

		$filename = apply_filters(
			'cn_system_info_filename',
			'connections-system-info-' . current_time( 'Y-m-d_H-i-s' )
		);

		nocache_headers();
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename=' . $filename . '.txt' );
		header( 'Expires: 0' );

		echo esc_textarea( wp_strip_all_tags( self::get() ) );
		exit;
	}

	/**
	 * Callback for the `template_redirect` action.
	 *
	 * The template_redirect action callback used to "remotely" display the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function view() {

		$requestToken = Request\System_Information_Token::input()->value();

		if ( empty( $requestToken ) ) {
			return;
		}

		$token = cnCache::get( 'system_info_remote_token', 'option-cache' );

		if ( $requestToken === $token ) {

			/** WordPress Plugin Administration API */
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once ABSPATH . 'wp-admin/includes/update.php';

			echo '<pre>';
			self::display();
			echo '</pre>';

		} else {

			wp_safe_redirect( home_url() ); // phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit
		}

		exit();
	}

	/**
	 * Email the system info.
	 *
	 * @internal
	 * @since 8.3
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

		$message  = sanitize_text_field( $atts['message'] );
		$message .= PHP_EOL . PHP_EOL;
		$message .= '<pre>' . esc_html( self::get() ) . '</pre>';

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
	 * @internal
	 * @since 8.3
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public static function registerEmailLogType( $types ) {

		$types[ self::LOG_TYPE ] = array(
			'id'   => self::LOG_TYPE,
			'name' => __( 'System Info Email', 'connections' ),
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
			'callback' => array( 'cnLog_Email', 'viewLogs' ),
		);

		return $view;
	}

	/**
	 * Add the custom email header to set the "cn-system-info" email log type.
	 *
	 * @internal
	 * @since 8.3
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
	 * @internal
	 * @since 8.3
	 *
	 * @param  string $v
	 *
	 * @return int|string
	 */
	public static function let_to_num( $v ) {
		$l   = substr( $v, -1 );
		$ret = substr( $v, 0, -1 );

		switch ( strtoupper( $l ) ) {
			case 'P': // fall-through.
			case 'T': // fall-through.
			case 'G': // fall-through.
			case 'M': // fall-through.
			case 'K': // fall-through.
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
	 * @since 8.3
	 *
	 * @return string $host if detected
	 */
	public static function getHost() {

		$serverName = Connections_Directory\Request\Server_Name::input()->value();

		if ( defined( 'WPE_APIKEY' ) ) {
			$host = 'WP Engine';
		} elseif ( defined( 'PAGELYBIN' ) ) {
			$host = 'Pagely';
		} elseif ( 'localhost:/tmp/mysql5.sock' == DB_HOST ) {
			$host = 'ICDSoft';
		} elseif ( 'mysqlv5' == DB_HOST ) {
			$host = 'NetworkSolutions';
		} elseif ( false !== strpos( DB_HOST, 'ipagemysql.com' ) ) {
			$host = 'iPage';
		} elseif ( false !== strpos( DB_HOST, 'ipowermysql.com' ) ) {
			$host = 'IPower';
		} elseif ( false !== strpos( DB_HOST, '.gridserver.com' ) ) {
			$host = 'MediaTemple Grid';
		} elseif ( false !== strpos( DB_HOST, '.pair.com' ) ) {
			$host = 'pair Networks';
		} elseif ( false !== strpos( DB_HOST, '.stabletransit.com' ) ) {
			$host = 'Rackspace Cloud';
		} elseif ( false !== strpos( DB_HOST, '.sysfix.eu' ) ) {
			$host = 'SysFix.eu Power Hosting';
		} elseif ( false !== strpos( $serverName, 'Flywheel' ) ) {
			$host = 'Flywheel';
		} else {
			// Adding a general fallback for data gathering.
			$host = 'DBH: ' . DB_HOST . ', SRV: ' . $serverName;
		}

		return $host;
	}

	/**
	 * Render the result of the DESCRIBE `$table_name` to mimic the output from the commandline.
	 *
	 * @since 8.5.4
	 *
	 * @param string $tableName The table name to render the DESCRIBE query result.
	 *
	 * @return string
	 */
	public static function describeTable( $tableName ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$table  = '';
		$column = array();

		$structure = $wpdb->get_results( 'DESCRIBE ' . $tableName, ARRAY_A );
		$headers   = self::getTableHeaders( reset( $structure ) );
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

				if ( ! is_string( $cell ) ) {
					$cell = '';
				}

				$column[] = str_pad( $cell, $widths[ $header ], ' ', STR_PAD_RIGHT );
			}

			$table .= '| ' . implode( ' | ', $column ) . ' |' . PHP_EOL;
			$table .= $divider;
			$column = array();
		}

		return $table;
	}

	/**
	 * Used to get the column header names.
	 *
	 * @internal
	 * @since 8.5.4
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
	 * @internal
	 * @since 8.5.4
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

				if ( ! is_string( $value ) ) {
					$value = '';
				}

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
