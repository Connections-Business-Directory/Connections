<?php

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {

	require dirname( __FILE__ ) . '/../connections.php';

	// Create the table structure.
	require dirname( __FILE__ ) . '/../includes/class.schema.php';
	cnSchema::create();
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

//echo "Activating Connections Business Directory...\n";
//activate_plugin( 'connections/connections.php' );


function _disable_http_requests( $status = FALSE, $args = array(), $url = '' ) {

	return new WP_Error( 'no_http_requests_in_unit_tests', __( 'HTTP Requests disabled for unit tests', 'connections' ) );
}
add_filter( 'pre_http_request', '_disable_http_requests', 10, 3 );

global $current_user;

$current_user = new WP_User(1);
$current_user->set_role('administrator');
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
