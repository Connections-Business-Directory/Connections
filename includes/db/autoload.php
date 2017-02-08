<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register( function ( $class ) {

	if ( class_exists( $class ) ) {
		// If the class exists, do not attempt to load.
		return;
	}

	// project-specific namespace prefix
	$prefix = 'Connections\\DB\\';

	// base directory for the namespace prefix
	$base_dir = __DIR__ . '/';

	// does the class use the namespace prefix?
	$len = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = substr( $class, $len );

	// replace all underscores with hyphen
	$relative_class = str_replace( '_', '-', $relative_class );

	// prepend class name with `class.`
	$relative_class = strtolower( preg_replace( '/([^\\\\]+)$/i', '$2class.$1', $relative_class ) );

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	// if the file exists, require it
	if ( file_exists( $file ) ) {

		require $file;

	} else {

		wp_die( esc_html( "The file attempting to be loaded at $file does not exist." ) );
	}
});
