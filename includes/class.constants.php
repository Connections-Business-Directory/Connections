<?php

/**
 * Class cnConstants
 *
 * @since  8.5.24
 */
class cnConstants {

	/**
	 * Define plugin global constants.
	 */
	public static function define() {

		global $wpdb, $blog_id;

		if ( ! defined( 'CN_LOG' ) ) {
			/** @var string CN_LOG Whether or not to log actions and results for debugging. */
			define( 'CN_LOG', FALSE );
		}

		/** @var string CN_CURRENT_VERSION The current version. */
		define( 'CN_CURRENT_VERSION', ConnectionsLoad::VERSION );

		/** @var string CN_DB_VERSION The current DB version. */
		define( 'CN_DB_VERSION', '0.6' );

		/** @var string CN_UPDATE_URL The plugin update URL used for EDD SL Updater */
		define( 'CN_UPDATE_URL', 'http://connections-pro.com/edd-sl-api' );

		/** @var string CN_BASE_NAME */
		define( 'CN_BASE_NAME', Connections_Directory()->pluginBasename() );

		/** @var string CN_DIR_NAME */
		define( 'CN_DIR_NAME', dirname( CN_BASE_NAME ) );

		/** @var string CN_PATH */
		define( 'CN_PATH', Connections_Directory()->pluginPath() );

		/** @var string CN_URL */
		define( 'CN_URL', Connections_Directory()->pluginURL() );

		/*
		 * Core constants that can be overridden by setting in wp-config.php.
		 */
		if ( ! defined( 'CN_TEMPLATE_PATH' ) ) {

			/** @var string CN_TEMPLATE_PATH */
			define( 'CN_TEMPLATE_PATH', CN_PATH . 'templates' . DIRECTORY_SEPARATOR );
		}

		if ( ! defined( 'CN_TEMPLATE_URL' ) ) {

			/** @var string CN_TEMPLATE_URL */
			define( 'CN_TEMPLATE_URL', CN_URL . 'templates' . DIRECTORY_SEPARATOR );
		}

		if ( ! defined( 'CN_CACHE_PATH' ) ) {

			/** @var string CN_CACHE_PATH */
			define( 'CN_CACHE_PATH', CN_PATH . 'cache' . DIRECTORY_SEPARATOR );
		}

		if ( ! defined( 'CN_ADMIN_MENU_POSITION' ) ) {

			/** @var int CN_ADMIN_MENU_POSITION */
			define( 'CN_ADMIN_MENU_POSITION', NULL );
		}

		/*
		 * To run Connections in single site mode on multi-site.
		 * Add to wp-config.php: define('CN_MULTISITE_ENABLED', FALSE);
		 *
		 * @credit lancelot-du-lac
		 * @url http://wordpress.org/support/topic/plugin-connections-support-multisite-in-single-mode
		 */
		if ( ! defined( 'CN_MULTISITE_ENABLED' ) ) {

			if ( is_multisite() ) {

				/** @var bool CN_MULTISITE_ENABLED */
				define( 'CN_MULTISITE_ENABLED', TRUE );

			} else {

				/** @var bool CN_MULTISITE_ENABLED */
				define( 'CN_MULTISITE_ENABLED', FALSE );
			}
		}

		// Set the root image permalink endpoint name.
		if ( ! defined( 'CN_IMAGE_ENDPOINT' ) ) {

			/** @var string CN_IMAGE_ENDPOINT */
			define( 'CN_IMAGE_ENDPOINT', 'cn-image' );
		}

		// Set images subdirectory folder name.
		if ( ! defined( 'CN_IMAGE_DIR_NAME' ) ){

			/** @var string CN_IMAGE_DIR_NAME */
			define( 'CN_IMAGE_DIR_NAME', 'connections-images' );
		}

		/*
		 * Core constants that can be overridden in wp-config.php
		 * which enable support for multi-site file locations.
		 */
		if ( is_multisite() && CN_MULTISITE_ENABLED ) {

			// Get the core WP uploads info.
			$uploadInfo = wp_upload_dir();

			if ( ! defined( 'CN_IMAGE_PATH' ) ) {

				/** @var string CN_IMAGE_PATH */
				define( 'CN_IMAGE_PATH', trailingslashit( $uploadInfo['basedir'] ) . CN_IMAGE_DIR_NAME . DIRECTORY_SEPARATOR );
				// define( 'CN_IMAGE_PATH', WP_CONTENT_DIR . '/sites/' . $blog_id . '/connection_images/' );
			}

			if ( ! defined( 'CN_IMAGE_BASE_URL' ) ) {

				/** @var string CN_IMAGE_BASE_URL */
				define( 'CN_IMAGE_BASE_URL', trailingslashit( $uploadInfo['baseurl'] ) . CN_IMAGE_DIR_NAME . '/' );
				// define( 'CN_IMAGE_BASE_URL', network_home_url( '/wp-content/sites/' . $blog_id . '/connection_images/' ) );
			}

			if ( ! defined( 'CN_CUSTOM_TEMPLATE_PATH' ) ) {

				/** @var string CN_CUSTOM_TEMPLATE_PATH */
				define( 'CN_CUSTOM_TEMPLATE_PATH', WP_CONTENT_DIR . '/blogs.dir/' . $blog_id . '/connections_templates/' );
			}

			if ( ! defined( 'CN_CUSTOM_TEMPLATE_URL' ) ) {

				/** @var string CN_CUSTOM_TEMPLATE_URL */
				define( 'CN_CUSTOM_TEMPLATE_URL', network_home_url( '/wp-content/blogs.dir/' . $blog_id . '/connections_templates/' ) );
			}

			// Define the relative URL/s.
			/** @var string CN_RELATIVE_URL */
			define( 'CN_RELATIVE_URL', str_replace( network_home_url(), '', CN_URL ) );

			/** @var string CN_TEMPLATE_RELATIVE_URL */
			define( 'CN_TEMPLATE_RELATIVE_URL', str_replace( network_home_url(), '', CN_URL . 'templates/' ) );

			/** @var string CN_IMAGE_RELATIVE_URL */
			define( 'CN_IMAGE_RELATIVE_URL', str_replace( network_home_url(), '', CN_IMAGE_BASE_URL ) );

			/** @var string CN_CUSTOM_TEMPLATE_RELATIVE_URL */
			define( 'CN_CUSTOM_TEMPLATE_RELATIVE_URL', str_replace( network_home_url(), '', CN_CUSTOM_TEMPLATE_URL ) );

		} else {

			/**
			 * Pulled this block of code from wp_upload_dir(). Using this rather than simply using wp_upload_dir()
			 * because @see wp_upload_dir() will always return the upload dir/url (/sites/{id}/) for the current network site.
			 *
			 * We do not want this behavior if forcing Connections into single site mode on a multisite
			 * install of WP. Additionally we do not want the year/month sub dir appended.
			 *
			 * A filter could be used, hooked into `upload_dir` but that would be a little heavy as every time the custom
			 * dir/url would be needed the filter would have to be added and then removed not to mention other plugins could
			 * interfere by hooking into `upload_dir`.
			 *
			 * --> START <--
			 */
			$siteurl     = site_url();
			$upload_path = trim( get_option( 'upload_path' ) );

			if ( empty( $upload_path ) || 'wp-content/uploads' == $upload_path ) {

				$dir = WP_CONTENT_DIR . '/uploads';

			} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {

				// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
				$dir = path_join( ABSPATH, $upload_path );

			} else {

				$dir = $upload_path;
			}

			if ( ! $url = get_option( 'upload_url_path' ) ) {

				if ( empty( $upload_path ) || ( 'wp-content/uploads' == $upload_path ) || ( $upload_path == $dir ) ) {

					$url = content_url( '/uploads' );

				} else {

					$url = trailingslashit( $siteurl ) . $upload_path;
				}
			}

			// Obey the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
			// We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
			if ( defined( 'UPLOADS' ) && ! ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) ) {

				$dir = ABSPATH . UPLOADS;
				$url = trailingslashit( $siteurl ) . UPLOADS;
			}
			/*
			 * --> END <--
			 */

			if ( ! defined( 'CN_IMAGE_PATH' ) ){

				/** @var string CN_IMAGE_PATH */
				define( 'CN_IMAGE_PATH', $dir . DIRECTORY_SEPARATOR . CN_IMAGE_DIR_NAME . DIRECTORY_SEPARATOR );
			}
			if ( ! defined( 'CN_IMAGE_BASE_URL' ) ) {

				/** @var string CN_IMAGE_BASE_URL */
				define( 'CN_IMAGE_BASE_URL', $url . '/' . CN_IMAGE_DIR_NAME . '/' );
			}

			if ( ! defined( 'CN_CUSTOM_TEMPLATE_PATH' ) ) {

				/** @var string CN_CUSTOM_TEMPLATE_PATH */
				define( 'CN_CUSTOM_TEMPLATE_PATH', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'connections_templates' . DIRECTORY_SEPARATOR );
			}

			if ( ! defined( 'CN_CUSTOM_TEMPLATE_URL' ) ) {

				/** @var string CN_CUSTOM_TEMPLATE_URL */
				define( 'CN_CUSTOM_TEMPLATE_URL', content_url() . '/connections_templates/' );
			}

			// Define the relative URL/s.
			/** @var string CN_RELATIVE_URL */
			define( 'CN_RELATIVE_URL', str_replace( home_url(), '', CN_URL ) );

			/** @var string CN_TEMPLATE_RELATIVE_URL */
			define( 'CN_TEMPLATE_RELATIVE_URL', str_replace( home_url(), '', CN_URL . 'templates/' ) );

			/** @var string CN_IMAGE_RELATIVE_URL */
			define( 'CN_IMAGE_RELATIVE_URL', str_replace( home_url(), '', CN_IMAGE_BASE_URL ) );

			/** @var string CN_CUSTOM_TEMPLATE_RELATIVE_URL */
			define( 'CN_CUSTOM_TEMPLATE_RELATIVE_URL', str_replace( home_url(), '', CN_CUSTOM_TEMPLATE_URL ) );
		}

		/*
		 * Set the table prefix accordingly depending if Connections is installed on a multisite WP installation.
		 */
		$prefix = ( is_multisite() && CN_MULTISITE_ENABLED ) ? $wpdb->prefix : $wpdb->base_prefix;

		/*
		 * Define the constants that can be used to reference the custom tables
		 */
		/** @var string CN_ENTRY_TABLE */
		define( 'CN_ENTRY_TABLE', $prefix . 'connections' );

		/** @var string CN_ENTRY_ADDRESS_TABLE */
		define( 'CN_ENTRY_ADDRESS_TABLE', $prefix . 'connections_address' );

		/** @var string CN_ENTRY_PHONE_TABLE */
		define( 'CN_ENTRY_PHONE_TABLE', $prefix . 'connections_phone' );

		/** @var string CN_ENTRY_EMAIL_TABLE */
		define( 'CN_ENTRY_EMAIL_TABLE', $prefix . 'connections_email' );

		/** @var string CN_ENTRY_MESSENGER_TABLE */
		define( 'CN_ENTRY_MESSENGER_TABLE', $prefix . 'connections_messenger' );

		/** @var string CN_ENTRY_SOCIAL_TABLE */
		define( 'CN_ENTRY_SOCIAL_TABLE', $prefix . 'connections_social' );

		/** @var string CN_ENTRY_LINK_TABLE */
		define( 'CN_ENTRY_LINK_TABLE', $prefix . 'connections_link' );

		/** @var string CN_ENTRY_DATE_TABLE */
		define( 'CN_ENTRY_DATE_TABLE', $prefix . 'connections_date' );

		/** @var string CN_ENTRY_TABLE_META */
		define( 'CN_ENTRY_TABLE_META', $prefix . 'connections_meta' );

		/** @var string CN_TERMS_TABLE */
		define( 'CN_TERMS_TABLE', $prefix . 'connections_terms' );

		/** @var string CN_TERM_TAXONOMY_TABLE */
		define( 'CN_TERM_TAXONOMY_TABLE', $prefix . 'connections_term_taxonomy' );

		/** @var string CN_TERM_RELATIONSHIP_TABLE */
		define( 'CN_TERM_RELATIONSHIP_TABLE', $prefix . 'connections_term_relationships' );

		/** @var string CN_TERM_META_TABLE */
		define( 'CN_TERM_META_TABLE', $prefix . 'connections_term_meta' );
	}
}
