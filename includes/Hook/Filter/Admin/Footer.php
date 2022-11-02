<?php
/**
 * Add rating links to the admin footer.
 *
 * @since 10.4.31
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Hook\Filter\Admin
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Filter\Admin;

use Connections_Directory\Request;

/**
 * Class Footer
 *
 * @package Connections_Directory\Hook\Filter\Admin
 */
final class Footer {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.31
	 */
	public static function register() {

		add_filter( 'admin_footer_text', array( __CLASS__, 'rateUs' ) );
	}

	/**
	 * Callback for the `admin_footer_text` filter.
	 *
	 * Add rating links to the admin dashboard.
	 *
	 * @internal
	 * @since 8.2.9
	 *
	 * @param string $text The existing footer text.
	 *
	 * @return string
	 */
	public static function rateUs( $text ) {

		if ( Request::get()->isAjax() ) {

			return $text;
		}

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( in_array( get_current_screen()->id, get_object_vars( $instance->pageHook ) ) ) {

			$rate_text = sprintf(
				/* translators: Plugin review URI's. */
				__(
					'Thank you for using <a href="%1$s" target="_blank">Connections Business Directory</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>',
					'connections'
				),
				'https://connections-pro.com',
				'https://wordpress.org/support/plugin/connections/reviews/?filter=5#new-post'
			);

			return str_replace( '</span>', '', $text ) . ' | ' . wp_kses_post( $rate_text ) . '</span>';

		} else {

			return $text;
		}
	}
}
