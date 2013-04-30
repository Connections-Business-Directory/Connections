<?php

/**
 * Static class for displaying template parts.
 *
 * @package     Connections
 * @subpackage  Cache
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// CREDIT:  http://markjaquith.wordpress.com/2013/04/26/fragment-caching-in-wordpress/
class cnCache {

	/*
	Usage:
		$frag = new CWS_Fragment_Cache( 'unique-key', 3600 ); // Second param is TTL
		if ( !$frag->output() ) { // NOTE, testing for a return of false
			functions_that_do_stuff_live();
			these_should_echo();
			// IMPORTANT
			$frag->store();
			// YOU CANNOT FORGET THIS. If you do, the site will break.
		}
	*/


	const GROUP = 'cws-fragments';
	var $key;
	var $ttl;

	public function __construct( $key, $ttl ) {
		$this->key = $key;
		$this->ttl = $ttl;
	}

	public function output() {
		$output = wp_cache_get( $this->key, self::GROUP );
		if ( !empty( $output ) ) {
			// It was in the cache
			echo $output;
			return true;
		} else {
			ob_start();
			return false;
		}
	}

	public function store() {
		$output = ob_get_flush(); // Flushes the buffers
		wp_cache_add( $this->key, $output, self::GROUP, $this->ttl );
	}


	// OR do this:
	// Credit:  https://gist.github.com/westonruter/5475349


	/*
	Usage:
		cache_fragment_output( 'unique-key', 3600, function () {
			functions_that_do_stuff_live();
			these_should_echo();
		});
	*/

	function cache_fragment_output( $key, $ttl, $function ) {
		$group = 'fragment-cache';
		$output = wp_cache_get( $key, $group );
		if ( empty($output) ) {
			ob_start();
			call_user_func( $function );
			$output = ob_get_clean();
			wp_cache_add( $key, $output, $group, $ttl );
		}
		echo $output;
	}

	// Checkout the Pods implementation
	// https://github.com/pods-framework/pods/blob/2.x/classes/PodsView.php


	// Make sure to add my own flush function to purge all caches and transients.
}