<?php

namespace Connections_Directory\Utility\_deprecated;

use Connections_Directory\Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrapper for deprecated arguments so we can apply some extra logic.
 *
 * @since 10.3
 *
 * @param string $argument
 * @param string $version
 * @param string $message
 */
function _argument( $argument, $version, $message = '' ) {

	if ( 'development' !== wp_get_environment_type() ) {

		return;
	}

	$request = Request::get();

	if ( $request->isAjax() || $request->isRest() ) {

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( 'deprecated_argument_run', $argument, $message, $version );

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( "The {$argument} argument is deprecated since version {$version}. {$message}" );

	} else {

		_deprecated_argument( esc_html( $argument ), esc_html( $version ), esc_html( $message ) );
	}
}

/**
 * Applies a deprecated filter with notice only if used.
 *
 * @since 10.3
 *
 * @param string $tag         The name of the filter hook.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default empty.
 * @param string $message     Optional. A message regarding the change. Default empty.
 */
function _applyFilters( $tag, $args, $version, $replacement = '', $message = '' ) {

	if ( ! has_filter( $tag ) ) {
		return $args[0];
	}

	_hook( $tag, $version, $replacement, $message );

	return apply_filters_ref_array( $tag, $args );
}

/**
 * Runs a deprecated action with notice only if used.
 *
 * @since 10.3
 *
 * @param string $tag         The name of the action hook.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 * @param string $version     The version of WooCommerce that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 */
function _doAction( $tag, $args, $version, $replacement = '', $message = '' ) {

	if ( ! has_action( $tag ) ) {
		return;
	}

	_hook( $tag, $version, $replacement, $message );
	do_action_ref_array( $tag, $args );
}

/**
 * Wrapper for deprecated file, so we can apply some extra logic.
 *
 * @since 10.4.18
 *
 * @param string $file        The file that was included.
 * @param string $version     The version of WordPress that deprecated the file.
 * @param string $replacement Optional. The file that should have been included based on ABSPATH.
 *                            Default empty.
 * @param string $message     Optional. A message regarding the change. Default empty.
 */
function _file( $file, $version, $replacement = '', $message = '' ) {

	if ( 'development' !== wp_get_environment_type() ) {

		return;
	}

	$request = Request::get();

	if ( $request->isAjax() || $request->isRest() ) {

		$log_string  = "{$file} is deprecated since version {$version}.";
		$log_string .= $replacement ? " Replace with {$replacement}." : ' No alternative available.';

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( 'deprecated_file_included', $file, $replacement, $version, $message );

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $log_string );

	} else {

		_deprecated_file( esc_html( $file ), esc_html( $version ), esc_html( $replacement ), esc_html( $message ) );
	}
}

/**
 * Wrapper for deprecated functions so we can apply some extra logic.
 *
 * @since 10.3
 *
 * @param string $function    Function used.
 * @param string $version     Version the message was added in.
 * @param string $replacement Replacement for the called function.
 */
function _func( $function, $version, $replacement = '' ) {

	if ( 'development' !== wp_get_environment_type() ) {

		return;
	}

	$request = Request::get();

	if ( $request->isAjax() || $request->isRest() ) {

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( 'deprecated_function_run', $function, $replacement, $version );

		$log_string  = "The {$function} function is deprecated since version {$version}.";
		$log_string .= $replacement ? " Replace with {$replacement}." : '';

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $log_string );

	} else {

		_deprecated_function( esc_html( $function ), esc_html( $version ), esc_html( $replacement ) );
	}
}

/**
 * Wrapper for deprecated hook, so we can apply some extra logic.
 *
 * @since 10.3
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 */
function _hook( $hook, $version, $replacement = '', $message = '' ) {

	if ( 'development' !== wp_get_environment_type() ) {

		return;
	}

	$request = Request::get();

	if ( $request->isAjax() || $request->isCLI() || $request->isRest() ) {

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

		$message     = empty( $message ) ? '' : ' ' . $message;
		$log_string  = "{$hook} is deprecated since version {$version}";
		$log_string .= $replacement ? "! Use {$replacement} instead." : ' with no alternative available.';

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $log_string . $message );

	} else {

		_deprecated_hook( esc_html( $hook ), esc_html( $version ), esc_html( $replacement ), esc_html( $message ) );
	}
}
