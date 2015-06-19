<?php
/**
 * License handler for Connections Extensions, Templates and Themes.
 * NOTE: This class depends on the EDD SL Updater class.
 *
 * CREDIT: This class was based on "class-edd-license-handler.php" from
 * 		Easy Digital Downloads.
 *
 * @package     Connections
 * @subpackage  License
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

class cnLicense {

	private $file;
	private $name;
	private $slug;
	private $version;
	private $author;
	private $key;

	/**
	 * Setup The item license.
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $file      The main plugin file used for EDD SL Updater.
	 * @param  string $name      The plugin name exactly as in the store.
	 * @param  string $version   The current plugin version; not, the latest version.
	 * @param  string $author    The plugin author.
	 * @param  string $updateURL The EDD SL API Updater URL.
	 *
	 * @return void
	 */
	public function __construct( $file, $name, $version, $author, $updateURL = NULL ) {

		// Create a slug from the $name var. This will be used as the settings ID when registering the settings field.
		// NOTE: Based on WP function sanitize_key().
		$slug = self::getSlug( $name );

		// Grab the licenses from the db. Have to use get_option because this
		// is being run before cnSettingsAPI has been init/d.
		$licenses = get_option( 'connections_licenses', FALSE );
		$key      = isset( $licenses[ $slug ] ) ? $licenses[ $slug ] : '';

		$this->file      = $file;
		$this->name      = $name;
		$this->slug      = $slug;
		$this->version   = $version;
		$this->author    = $author;
		$this->key       = $key;
		$this->updateURL = is_null( $updateURL ) ? CN_UPDATE_URL : $updateURL;

		$this->includes();
		$this->hooks();
		$this->updater();

		// delete_transient( 'connections_license-' . $this->slug );
	}

	/**
	 * Include the EDD SL Updater class.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @return void
	 */
	private function includes() {

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) require_once CN_PATH . 'vendor/edd-sl/EDD_SL_Plugin_Updater.php';
	}

	/**
	 * Setup hooks.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   add_action()
	 * @uses   did_action()
	 * @uses   do_action()
	 * @uses   add_filter()
	 *
	 * @return void
	 */
	private function hooks() {

		/*
		 * Register the settings tabs shown on the Settings admin page tabs, sections and fields.
		 * NOTE: The settings tab and section only needs to be registered once.
		 * The did_action( `cn_register_licenses_tab`) action will ensure that they are.
		 */
		add_action( 'cn_register_licenses_tab', array( __CLASS__, 'registerSettingsTabSection' ) );
		if ( did_action( 'cn_register_licenses_tab' ) === 0 ) do_action( 'cn_register_licenses_tab' );
		add_filter( 'cn_register_settings_fields', array( $this, 'registerSettingsFields' ) );

		// Activate license key on settings save
		add_action( 'admin_init', array( $this, 'activate' ) );

		// Deactivate license key
		add_action( 'admin_init', array( $this, 'deactivate' ) );

		/*
		 * Add action to render the license settings field.
		 * NOTE: The license settings field type action only needs to be registered once.
		 * If it were not limited to register only once, the field would render for each time
		 * a new instance of this class initiated.
		 */
		if ( ! has_action( 'cn_settings_field-license' ) ) add_action( 'cn_settings_field-license', array( $this, 'field' ), 10, 3 );
	}

	/**
	 * Register the extension, template or theme with EDD SL.
	 *
	 * @access private
	 * @since  0.8
	 * @see    EDD_SL_Plugin_Updater
	 *
	 * @return void
	 */
	private function updater() {

		new EDD_SL_Plugin_Updater(
			$this->updateURL,
			$this->file,
			array(
				'version'   => $this->version,
				'license'   => $this->key,
				'item_name' => $this->name,
				'author'    => $this->author
			)
		);
	}

	/**
	 * Resiters the Lecenses settings tab and section.
	 * This function in ran only once and is called by the
	 * `cn_register_licenses_tab` action hook in $this->hooks()
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @see    hooks()
	 * @uses   add_filter()
	 *
	 * @return void
	 */
	public static function registerSettingsTabSection() {

		add_filter( 'cn_register_settings_tabs', array( __CLASS__, 'registerSettingsTab' ) );
		add_filter( 'cn_register_settings_sections', array( __CLASS__, 'registerSettingsSections' ) );
	}

	/**
	 * Add the "Licenses" settings tab on the Connections : Settings admin page.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @return array   The settings tabs options array.
	 */
	public static function registerSettingsTab( $tabs ) {

		$tabs[] = array(
			'id'        => 'licenses',
			'position'  => 50,
			'title'     => __( 'Licenses' , 'connections' ),
			'page_hook' => 'connections_page_connections_settings'
		);

		return $tabs;
	}

	/**
	 * Register the "Licenses" settings sections.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @param  array  $sections
	 *
	 * @return array  The settings sections options array.
	 */
	public static function registerSettingsSections( $sections ) {

		$sections[] = array(
			'plugin_id' => 'connections',
			'tab'       => 'licenses',
			'id'        => 'licenses',
			'position'  => 10,
			'title'     => '',
			'callback'  => create_function( '', "echo '<p>' , __( 'To receive automatic extension and template updates, enter and activate your site license.' , 'connections' ) , '</p>';" ),
			'page_hook' => 'connections_page_connections_settings'
		);

		return $sections;
	}

	/**
	 * Add license field to settings
	 *
	 * @access public
	 * @since  0.8
	 * @param  array   $settings
	 *
	 * @return array   The field settings array.
	 */
	public function registerSettingsFields( $fields ) {

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => $this->slug,
			'position'          => 10,
			'page_hook'         => 'connections_page_connections_settings',
			'tab'               => 'licenses',
			'section'           => 'licenses',
			'title'             => $this->name,
			'desc'              => '',
			'help'              => '',
			'type'              => 'license',
			'default'           => '',
			'sanitize_callback' => array( $this, 'sanitize' ),
		);

		return $fields;
	}

	/**
	 * Callback registered the render the license key custom settings field type.
	 *
	 * @access private
	 * @since  0.8
	 * @param  string $name  The item name.
	 * @param  string $value The item license key.
	 * @param  array  $field The field options array.
	 *
	 * @return string        The HTML license field.
	 */
	public function field( $name, $value, $field ) {

		// The field size. Valid values are: small | regular | large
		$size   = isset( $field['size'] ) && ! empty( $field['size'] ) ? $field['size'] : 'regular';

		// Get the status if the item's license key.
		$status = self::status( $field['title'], $value );

		// Retrieve the items license data.
		$data   = get_option( 'connections_license_data' );
		// var_dump( $data );

		if ( $data !== FALSE ) {

			// If there was an error message in the EDD SL API response, set the description to the error message.
			if ( isset( $data[ $field['id'] ]->success ) && $data[ $field['id'] ]->success === FALSE ) {

				// $status = isset( $data[ $field['id'] ] ) && isset( $data[ $field['id'] ]->license ) ? $data[ $field['id'] ]->license : 'unknown';
				$error  = isset( $data[ $field['id'] ] ) && isset( $data[ $field['id'] ]->error ) ? $data[ $field['id'] ]->error : 'unknown';

				switch ( $error ) {

					case 'expired':

						$field['desc'] = __( 'License has expired.', 'connections' );

						if ( isset( $data[ $field['id'] ]->renewal_url ) ) {

							$field['desc'] .= sprintf( ' <a href="%1$s" title="%2$s">%2$s</a>',
								esc_url( $data[ $field['id'] ]->renewal_url ),
								__( 'Renew license.', 'connections' )
							);

						}

						break;

					case 'item_name_mismatch':

						$field['desc'] = __( 'License entered is not for this item.', 'connections' );
						break;

					case 'missing':

						$field['desc'] = __( 'Invalid license.', 'connections' );
						break;

					case 'revoked':

						$field['desc'] = __( 'License has been revoked.', 'connections' );
						break;

					case 'no_activations_left':

						$field['desc'] = __( 'License activation limit has been reached.', 'connections' );
						break;

					case 'key_mismatch':

						$field['desc'] = __( 'License key mismatch.', 'connections' );
						break;

					case 'license_not_activable':

						$field['desc'] = __( 'Bundle license keys can not be activated. Use item license key instead.', 'connections' );
						break;

					default:

						$field['desc'] = __( 'An unknown error has occurred.', 'connections' );
						break;
				}

			} else {

				// If there was no error message, display the current license status.
				switch ( $status ) {

					case 'invalid':

						$field['desc'] = __( 'License key invalid.', 'connections' );
						break;

					case 'expired':

						$field['desc'] = __( 'License has expired.', 'connections' );

						if ( isset( $data[ $field['id'] ]->renewal_url ) ) {

							$field['desc'] .= sprintf( ' <a href="%1$s" title="%2$s">%2$s</a>',
								esc_url( $data[ $field['id'] ]->renewal_url ),
								__( 'Renew license.', 'connections' )
							);

						}

						break;

					case 'inactive':

						$field['desc'] = __( 'License is not active.', 'connections' );
						break;

					case 'disabled':

						$field['desc'] = __( 'License has been disabled.', 'connections' );
						break;

					case 'site_inactive':

						$field['desc'] = __( 'License is not active on this site.', 'connections' );
						break;

					case 'item_name_mismatch':

						$field['desc'] = __( 'License entered is not for this item.', 'connections' );
						break;

					case 'valid':

						$expiryDate = strtotime( $data[ $field['id'] ]->expires );

						if ( $expiryDate !== FALSE ) {

							$field['desc'] = sprintf( __( 'License is valid and you are receiving updates. Your support license key will expire on %s.', 'connections' ), date('F jS Y', $expiryDate ) );

						} elseif ( 'lifetime' == $data[ $field['id'] ]->expires ) {

							$field['desc'] = __( 'Lifetime license is valid and you are receiving updates.', 'connections' );

						} else {

							$field['desc'] = __( 'License is valid', 'connections' );
						}

						break;

					case 'deactivated':

						$field['desc'] = __( 'License is deactivated.', 'connections' );
						break;

					case 'failed':

						$field['desc'] = __( 'License validation failed.' , 'connections' );
						break;

					default:
						// $field['desc'] = __( 'License status in unknown.', 'connections' );
						break;
				}

			}

		}

		// Render the text input.
		printf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>',
			$size,
			$name,
			isset( $value ) ? $value : ''
		);

		// Render either the "Activate" or "Deactivate" button base on the current license status.
		switch ( $status ) {

			case 'valid':

				printf( '<input type="submit" class="button-secondary" name="%1$s-deactivate_license" value="%2$s">',
					$field['id'],
					__( 'Deactivate', 'connections' )
				);

				break;

			default:

				printf( '<input type="submit" class="button-secondary" name="%1$s-activate_license" value="%2$s">',
					$field['id'],
					__( 'Activate', 'connections' )
				);

				break;
		}

		// Render the current license status.
		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) )
			printf( '<span  class="description"> %1$s</span>', $field['desc'] );

	}

	/**
	 * Get the license current status. This staus will be refreshed once per day.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @uses   get_option()
	 * @uses   get_transient()
	 * @uses   get_option()
	 * @uses   update_option()
	 * @uses   set_transient()
	 * @param  string $name The item name.
	 * @param  string $key  The item license key.
	 *
	 * @return string       The item license status.
	 */
	public static function status( $name, $key ) {

		$status = 'unknown';

		if ( empty( $name ) || empty( $key ) ) {

			return $status;
		}

		// Retrieve the items license data.
		$data = get_option( 'connections_license_data' );
		$slug = self::getSlug( $name );

		if ( ( $license = get_transient( 'connections_license-' . $slug ) ) === FALSE ) {

			$data[ $slug ] = self::license( 'status', $name, $key );

			update_option( 'connections_license_data', $data );

			set_transient( 'connections_license-' . $slug, $data[ $slug ], DAY_IN_SECONDS );

			// var_dump($data[ $slug ]);
			return $data[ $slug ]->license;

		}

		// var_dump( $license );
		if ( isset( $license->license ) ) {

			return $license->license;
		}

		return $status;
	}

	/**
	 * The filter applied to the sanitize license key when the settings are saved.
	 * This will also attempt to activate/deactivate license keys.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   get_option()
	 * @uses   update_option()
	 * @uses   delete_transient()
	 * @uses   sanitize_text_field()
	 * @param  array  $settings The settings options array.
	 *
	 * @return array            The settings options array.
	 */
	public function sanitize( $settings ) {

		// Retrieve license keys and data.
		$keys = get_option( 'connections_licenses' );
		$data = get_option( 'connections_license_data' );

		// Retrieve the old key from the options.
		$oldKey = isset( $keys[ $this->slug ] ) ? $keys[ $this->slug ] : FALSE;

		// Retrieve the new key from the user submitted value.
		$newKey = isset( $settings[ $this->slug ] ) ? $settings[ $this->slug ] : '';

		// Sanitize the new key.
		if ( isset( $settings[ $this->slug ] ) ) $settings[ $this->slug ] = sanitize_text_field( $newKey );

		// If the old key does not equal the new key, deactivate the old key and activate the new key; if supplied.
		if ( $oldKey && $oldKey != $newKey ) {

			self::license( 'deactivate', $this->name, $oldKey );

			if ( ! empty( $newKey ) ) self::license( 'activate', $this->name, $newKey );
		}

		// If the old key was empty and the new is not, activate the new key.
		if ( empty( $oldKey ) && ! empty( $newKey ) ) {

			self::license( 'activate', $this->name, $newKey );
		}

		// If the new key is empty, remove the license data and delete the transient.
		if ( empty( $newKey ) ) {

			$data[ $this->slug ] = array();

			update_option( 'connections_license_data', $data );
			delete_transient( 'connections_license-' . $this->slug );
		}

		return $settings;
	}

	/**
	 * Activate the license key.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   get_option()
	 * @uses   sanitize_text_field()
	 * @uses   update_option()
	 *
	 * @return void
	 */
	public function activate() {

		if ( ! isset( $_POST['connections_licenses'] ) ) {

			return;
		}

		if ( ! isset( $_POST['connections_licenses'][ $this->slug ] ) || empty( $_POST['connections_licenses'][ $this->slug ] ) ) {

			return;
		}

		// Run on activate button press
		if ( isset( $_POST[ $this->slug . '-activate_license' ] ) ) {

			// Retrieve license keys and data.
			$keys = get_option( 'connections_licenses' );
			$data = get_option( 'connections_license_data' );

			// If the status is already `valid`, no need to attempt to activate the key again; bail.
			if ( isset( $data[ $this->slug ]->license ) && $data[ $this->slug ]->license === 'valid' ) return;

			$key = sanitize_text_field( $_POST['connections_licenses'][ $this->slug ] );

			$keys[ $this->slug ] = $key;

			// Active the license.
			self::license( 'activate', $this->name, $key );

			// Save the license key.
			update_option( 'connections_licenses', $keys );
		}
	}

	/**
	 * Deactivate the license key.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   get_option()
	 *
	 * @return void
	 */
	public function deactivate() {

		if ( ! isset( $_POST['connections_licenses'] ) ) {

			return;
		}

		if ( ! isset( $_POST['connections_licenses'][ $this->slug ] ) || empty( $_POST['connections_licenses'][ $this->slug ] ) ) {

			return;
		}

		// Run on deactivate button press
		if ( isset( $_POST[ $this->slug . '-deactivate_license' ] ) ) {

			// Retrieve license keys and data.
			$keys = get_option( 'connections_licenses' );
			$data = get_option( 'connections_license_data' );

			// If the status is already `deactivated`, no need to attempt to deactivate the key again; bail.
			if ( isset( $data[ $this->slug ]->license ) && $data[ $this->slug ]->license === 'deactivated' ) return;

			// Deactivate the license.
			self::license( 'deactivate', $this->name, $this->key );
		}
	}

	/**
	 * Activate, deactivate and check license status.
	 *
	 * @access public
	 * @since  0.8
	 * @static
	 * @uses   get_option()
	 * @uses   wp_remote_get()
	 * @uses   add_query_arg()
	 * @uses   is_wp_error()
	 * @uses   wp_remote_retrieve_body()
	 * @uses   update_option()
	 * @uses   set_transient()
	 * @param  string $action  The action to perform on the license.
	 * @param  string $name    The item name.
	 * @param  string $license The item license key.
	 * @param  string $url     The EDD SL Updater URL.
	 *
	 * @return mixed           bool | object The EDD SL response for the item on success or FALSE on fail.
	 */
	public static function license( $action, $name, $license, $url = NULL ) {

		$licenses = get_option( 'connections_license_data' );
		$slug     = self::getSlug( $name );
		$url      = is_null( $url ) ? CN_UPDATE_URL : esc_url( $url );

		$licenses = ( $licenses === FALSE ) ? array() : $licenses;

		// Set the EDD SL API action.
		switch ( $action ) {

			case 'activate':

				$eddAction = 'activate_license';
				break;

			case 'deactivate':

				$eddAction = 'deactivate_license';
				break;

			case 'status':

				$eddAction = 'check_license';
				break;
		}

		// Data to send to the API
		$query = array(
			'edd_action' => $eddAction,
			'license'    => $license,
			'item_name'  => urlencode( $name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_get(
			add_query_arg( $query, $url ),
			array(
				'timeout'   => 15,
				'sslverify' => FALSE
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) return FALSE;

		// Decode the license data
		$data = json_decode( wp_remote_retrieve_body( $response ) );

		switch ( $action ) {

			case 'activate':

				// Add the license data to the licenses data option.
				$licenses[ $slug ] = $data;

				update_option( 'connections_license_data', $licenses );

				// Save license data in transient.
				set_transient( 'connections_license-' . $slug, $data, DAY_IN_SECONDS );

				return $data;

				break;

			case 'deactivate':

				// EDD SL reports either 'deactivated' or 'failed' as the license status.
				// Unlike when activating a license, EDD does not report and error and its message.
				// So...
				// We'll do a status check, set 'succes' as false and set the message to the license
				// status returned by doing a license check.
				if ( $data->license == 'failed' ) {

					$data = self::license( 'status', $name, $license, $url );

					$data->success = FALSE;
					$data->error   = $data->license;
				}

				// Add the license data to the licenses data option.
				$licenses[ $slug ] = $data;

				update_option( 'connections_license_data', $licenses );

				// Save license data in transient.
				set_transient( 'connections_license-' . $slug, $data, DAY_IN_SECONDS );

				return $data;

				break;

			case 'status':

				return $data;

				break;
		}

		return FALSE;
	}

	/**
	 * Create item slug from item name.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 * @param  string $name The item name.
	 *
	 * @return string       The item slug.
	 */
	private static function getSlug( $name ) {

		return preg_replace( '/[^a-z0-9_\-]/', '', str_replace( ' ', '_', strtolower( $name ) ) );
	}
}
