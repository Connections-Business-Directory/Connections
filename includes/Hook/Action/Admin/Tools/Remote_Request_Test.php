<?php
/**
 * A tool for examining the results of a WordPress {@see wp_remote_get()} or {@see wp_remote_post()} call.
 *
 * @since 10.4.39
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Hook\Action\Admin
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Action\Admin\Tools;

use Connections_Directory\Request;
use Connections_Directory\Utility\_;
use Connections_Directory\Utility\_nonce;
use WP_Error;

/**
 * Class Remote_Request_Test
 *
 * @package Connections_Directory\Hook\Action\Admin\Tests
 */
final class Remote_Request_Test {

	/**
	 * The WP HTTP API response debug data.
	 *
	 * @since 10.4.39
	 * @var array
	 */
	private static $debug;

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.39
	 */
	public static function register() {

		add_filter( 'cn_admin_tools_tabs', array( __CLASS__, 'registerTab' ) );
		add_action( 'Connections_Directory/Admin/Page/Tools/Tab/Tests', array( __CLASS__, 'postBox' ) );
	}

	/**
	 * Callback for the `cn_admin_tools_tabs` filter.
	 *
	 * Register the "Tests" tab.
	 *
	 * @since 10.4.39
	 *
	 * @param array $tabs The registered tabs.
	 *
	 * @return array
	 */
	public static function registerTab( array $tabs ): array {

		$tabs[] = array(
			'id'         => 'tests',
			'name'       => __( 'Tests', 'connections' ),
			'callback'   => array( __CLASS__, 'tabPanel' ),
			'capability' => 'manage_options',
		);

		return $tabs;
	}

	/**
	 * Callback for the `cn_tools_tab_{$id}` action.
	 *
	 * @since 10.4.39
	 */
	public static function tabPanel() {

		do_action( 'Connections_Directory/Admin/Page/Tools/Tab/Tests/Before' );
		do_action( 'Connections_Directory/Admin/Page/Tools/Tab/Tests' );
		do_action( 'Connections_Directory/Admin/Page/Tools/Tab/Tests/After' );
	}

	/**
	 * Callback for the `Connections_Directory/Admin/Page/Tools/Tab/Tests` action.
	 *
	 * Render the postbox message.
	 *
	 * @since 10.4.39
	 *
	 * @internal
	 *
	 * @return void
	 */
	public static function postBox() {

		$action = new self();
		$nonce  = Request\Nonce::from( INPUT_POST, 'remote-request-test' );

		if ( ! $action->isValid() && $nonce->isValid() ) {
			return;
		}

		$requestType = 'get';
		$requestURL  = '';

		if ( $nonce->isValid() ) {

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verification is validated.
			if ( isset( $_POST['request-type'] ) && in_array( $_POST['request-type'], array( 'get', 'post' ) ) ) {

				$requestType = sanitize_key( $_POST['request-type'] );
			}

			if ( isset( $_POST['request-url'] ) && '' !== $_POST['request-url'] ) {

				$requestURL = esc_url_raw( $_POST['request-url'] );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		?>
		<div class="postbox">
			<h3><span><?php _e( 'Remote Request', 'connections' ); ?></span></h3>
			<div class="inside">
				<?php
				if ( '' !== $requestURL ) {
					self::displayResults( $requestType, $requestURL );
				}
				?>
				<h4>Request</h4>
				<form method="post">
					<div>
						<ul>
							<?php
							printf( '<li><label><input type="radio" name="request-type" value="get" %1$s> GET</label></li>', checked( $requestType, 'get', false ) );
							printf( '<li><label><input type="radio" name="request-type" value="post" %1$s> POST</label></li>', checked( $requestType, 'post', false ) );
							?>
						</ul>
					</div>
					<div>
						<?php
						printf( '<label>URL: <input class="regular-text" name="request-url" type="text" value="%1$s"/></label>', esc_attr( $requestURL ) );
						_nonce::field( 'remote-request-test' );
						?>
						<input type="submit" class="button" />
					</div>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
	}

	/**
	 * The results of a WordPress wp_remote_*() call.
	 *
	 * @since 10.4.39
	 *
	 * @param string $type The request type.
	 * @param string $url  The request url.
	 *
	 * @return void
	 */
	private static function displayResults( string $type, string $url ) {

		add_action( 'http_api_debug', array( __CLASS__, 'debug' ), 10, 5 );

		switch ( $type ) {

			case 'get':
				$response = wp_remote_get( $url );
				break;

			case 'post':
				$response = wp_remote_post( $url );
				break;

			default:
				$response = '';
		}

		if ( is_array( $response ) || is_wp_error( $response ) ) {

			if ( is_array( $response ) && array_key_exists( 'body', $response ) ) {

				// Decode the response body data.
				$response['body'] = _::maybeJSONdecode( wp_remote_retrieve_body( $response ) );
			}

			$debug  = wp_json_encode( self::$debug, JSON_PRETTY_PRINT );
			$result = wp_json_encode( $response, JSON_PRETTY_PRINT );
			$rows   = array(
				'debug'    => substr_count( $debug, PHP_EOL ) + 2,
				'response' => substr_count( $result, PHP_EOL ) + 2,
			);

			printf( '<h3>Results for %s</h3>', esc_url( $url ) );
			printf( '<h4>Request Properties</h4>' );
			printf( '<textarea class="large-text" rows="%1$d" cols="30" style="white-space: pre;" readonly>%2$s</textarea>', esc_attr( $rows['debug'] ), esc_textarea( $debug ) );
			printf( '<h4>Request Response</h4>' );
			printf( '<textarea class="large-text" rows="%1$d" cols="30" style="white-space: pre;" readonly>%2$s</textarea>', esc_attr( $rows['response'] ), esc_textarea( $result ) );
		}
	}

	/**
	 * Debug HTTP requests in WordPress
	 *
	 * Fires after an HTTP API response is received and before the response is returned.
	 *
	 * Example:
	 *
	 * [24-Apr-2019 06:50:16 UTC] https://downloads.wordpress.org/plugin/elementor.2.5.14.zip
	 * [24-Apr-2019 06:50:16 UTC] {"errors":{"http_request_failed":["cURL error 28: Resolving timed out after 10518 milliseconds"]},"error_data":[]}
	 * [24-Apr-2019 06:50:16 UTC] Requests
	 * [24-Apr-2019 06:50:16 UTC] response
	 * [24-Apr-2019 06:50:16 UTC] {"method":"GET","timeout":300,"redirection":5,"httpversion":"1.0","user-agent":"WordPress\/5.1.1; http:\/\/astra-sites-dev-test.sharkz.in","reject_unsafe_urls":true,"blocking":true,"headers":[],"cookies":[],"body":null,"compress":false,"decompress":true,"sslverify":true,"sslcertificates":"\/var\/www\/html\/astra-sites-dev-test.sharkz.in\/public_html\/wp-includes\/certificates\/ca-bundle.crt","stream":true,"filename":"\/tmp\/elementor.2.5.14-FOXodB.tmp","limit_response_size":null,"_redirection":5}
	 *
	 * @since 10.4.39
	 *
	 * @param array|WP_Error $response    HTTP response or WP_Error object.
	 * @param string         $context     Context under which the hook is fired.
	 * @param string         $class       HTTP transport used.
	 * @param array          $parsed_args HTTP request arguments.
	 * @param string         $url         The request URL.
	 */
	public static function debug( $response, $context, $class, $parsed_args, $url ) {

		self::$debug = compact( 'context', 'class', 'parsed_args', 'url' );

		// Remove this filter callback, so it only runs once.
		remove_filter( current_filter(), array( __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Whether the current user has the required role capability.
	 *
	 * @since 10.4.39
	 *
	 * @return bool
	 */
	private function isValid(): bool {

		return current_user_can( 'manage_options' );
	}
}
