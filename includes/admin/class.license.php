<?php
/**
 * License handler for Connections Extensions, Templates and Themes.
 *
 * NOTE: This class depends on the cnPlugin_Updater class.
 *
 * CREDIT: This was based on "class-edd-license-handler.php" from Easy Digital Downloads.
 *
 * @package     Connections
 * @subpackage  License
 * @copyright   Copyright (c) 2016, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Class cnLicense
 */
class cnLicense {

	/**
	 * Plugin basename.
	 * @var string
	 */
	private $file;

	/**
	 * Plugin ID (download ID).
	 * @var int
	 */
	private $id = 0;

	/**
	 * Plugin name.
	 * @var string
	 */
	private $name;

	/**
	 * Plugin slug.
	 * @var string
	 */
	private $slug;

	/**
	 * Plugin version.
	 * @var string
	 */
	private $version;

	/**
	 * Plugin author.
	 * @var string
	 */
	private $author;

	/**
	 * Plugin license key.
	 * @var string
	 */
	private $key;

	/**
	 * Setup The item license.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param string $file      The main plugin file used for EDD SL Updater.
	 * @param string $name      The plugin name exactly as in the store.
	 * @param string $version   The current plugin version; not, the latest version.
	 * @param string $author    The plugin author.
	 * @param string $updateURL The EDD SL API Updater URL.
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
	 * Include the Plugin Updater..
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @return void
	 */
	private function includes() {

		if ( ! class_exists( 'cnPlugin_Updater' ) ) require_once CN_PATH . 'includes/admin/class.plugin-updater.php';
		if ( ! class_exists( 'cnLicense_Status' ) ) require_once CN_PATH . 'includes/admin/class.license-status.php';
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
		add_action( "cn_settings_field-license_{$this->slug}", array( $this, 'field' ), 10, 3 );

		$file = plugin_basename( $this->file );
		add_action( "in_plugin_update_message-{$file}", array( __CLASS__, 'changelog'), 10, 2 );
		add_action( "after_plugin_row_$file", array( $this, 'licenseStatus'), 10, 3 );

		add_action( 'admin_head-plugins.php', array( __CLASS__, 'style' ) );
	}

	/**
	 * Callback for the `admin_head-plugins.php` action.
	 *
	 * Adds the necessary custom CSS/JS on the Plugins admin page
	 * to support the display of the changelog and license status.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 */
	public static function style() {

		$style = <<<HERERDOC
<!-- Added by Connections Business Directory -->
<style type="text/css">
	p.cn-update-message-p-clear-before {
		margin-top: 1em;
	}
	p.cn-update-message-p-clear-before:before {
		content: none;
	}
	div.update-message ul {
		list-style-type: square;
		margin: 0 0 0 20px;
	}
	div.update-message ul.cn-changelog li {
		box-sizing: border-box;
		padding-right: 5%;
		width: 50%;
		margin: 0;
		float: left;
	}
	/* Adjust style to account for plugin support license key status row. */
	.plugins .plugin-update-tr.update.cn-license-status .plugin-update {
		-webkit-box-shadow: none;
		box-shadow: none;
	}
	.cn-license-status .notice-success p:before {
		color: #46b450;
		content: "\f147";
	}
	.cn-license-status .notice-warning p:before {
		color: #ffb900;
		content: "\f534";
	}
</style>
<script type='text/javascript'>
	/**
	 * Remove the box-shadow style fromm the row before the support license key status row.
	 * Do this via JS because this can not be done with CSS. :(
	 */
	jQuery(document).ready( function($) {
		
		/** 
		 * Deal with the "live" search introduced in WP 4.6.
		 * @link http://stackoverflow.com/a/19401707/5351316 
		 */
		var body = $('body');
		var observer = new MutationObserver( function( mutations ) {
		    mutations.forEach( function( mutation ) {
		        if ( mutation.attributeName === "class" ) {
		            //var attributeValue = $( mutation.target ).prop( mutation.attributeName );
		            //console.log( "Class attribute changed to:", attributeValue );
		            cnReStyle();
		        }
		    });
		});
		
		observer.observe( body[0], {
		    attributes: true
		});
		
		cnReStyle();
	});
	
	function cnReStyle() {
		jQuery('.plugin-update-tr.cn-license-status')
			.prev().find('th, td')
			.css({
				'webkit-box-shadow': 'none',
				'box-shadow': 'none'
			});
	}
</script>

HERERDOC;

		echo $style;
	}

	/**
	 * Register the add-on, template or extension, with the Plugin Updater.
	 *
	 * @access private
	 * @since  0.8
	 */
	private function updater() {

		$enabled = $this->isBetaSupportEnabled();

		cnPlugin_Updater::register(
			$this->file,
			array(
				'item_name' => $this->name,
				'author'    => $this->author,
				'version'   => $this->version,
				'license'   => $this->key,
				'beta'      => $enabled,
			)
		);

		cnLicense_Status::register(
			$this->file,
			array(
				'item_id'   => $this->id,
				'item_name' => $this->name,
				'author'    => $this->author,
				'version'   => $this->version,
				'license'   => $this->key,
				'beta'      => $enabled,
			)
		);
	}

	/**
	 * Whether or not beta support has been enabled for the download.
	 *
	 * @access public
	 * @since  8.11
	 *
	 * @return bool
	 */
	public function isBetaSupportEnabled() {

		$beta    = get_option( 'connections_beta', array() );
		$enabled = cnArray::get( $beta, $this->slug, FALSE );

		return cnFormatting::toBoolean( $enabled );
	}

	/**
	 * Reciters the Licenses settings tab and section.
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
	 * @param array $tabs
	 *
	 * @return array The settings tabs options array.
	 */
	public static function registerSettingsTab( $tabs ) {

		$tabs[] = array(
			'id'        => 'licenses',
			'position'  => 50,
			'title'     => __( 'Licenses' , 'connections' ),
			'page_hook' => 'connections_page_connections_settings'
		);

		$tabs[] = array(
			'id'        => 'beta',
			'position'  => 50.1,
			'title'     => __( 'Beta Versions' , 'connections' ),
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

		$sections[] = array(
			'plugin_id' => 'connections',
			'tab'       => 'beta',
			'id'        => 'beta',
			'position'  => 10,
			'title'     => '',
			'callback'  => create_function( '', "echo '<p>' , __( 'By checking any of the checkboxes below you are opting in to receiving pre-release version updates. You can opt out at any time by unchecking the options below. Pre-release version updates, like regular updates do not install automatically so you will retain the opportunity to skip installing a pre-release version update.' , 'connections' ) , '</p>';" ),
			'page_hook' => 'connections_page_connections_settings'
		);

		return $sections;
	}

	/**
	 * Add license field to settings
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param $fields
	 *
	 * @return array The field settings array.
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
			'type'              => "license_{$this->slug}",
			'default'           => '',
			'sanitize_callback' => array( $this, 'sanitizeKey' ),
		);

		$fields[] = array(
			'plugin_id'         => 'connections',
			'id'                => $this->slug,
			'position'          => 10,
			'page_hook'         => 'connections_page_connections_settings',
			'tab'               => 'beta',
			'section'           => 'beta',
			'title'             => $this->name,
			'desc'              => sprintf( __( 'Receive updates for pre-release versions of %s.', 'connections' ), $this->name ),
			'help'              => '',
			'type'              => 'checkbox',
			'default'           => '',
			//'sanitize_callback' => array( $this, 'sanitizeBeta' ),
		);

		return $fields;
	}

	/**
	 * Callback for the `"after_plugin_row_$plugin_file"` action.
	 *
	 * Displays the plugin's support license key status message below the plugin's row on the Plugins admin page.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 *
	 * @param string $file
	 * @param array  $plugin
	 * @param string $context
	 */
	public function licenseStatus( $file, $plugin, $context ) {
		global /*$status, $page, $s,*/ $totals;

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		$screen = get_current_screen();
		$status = self::statusMessage( $this );

		$type    = $status['type'];
		$message = $status['message'];

		if ( $screen->in_admin( 'network' ) ) {

			$is_active = is_plugin_active_for_network( $file );

		} else {

			$is_active = is_plugin_active( $file );
		}

		$class = $is_active ? 'active' : 'inactive';

		if ( ! empty( $totals['upgrade'] ) && ! empty( $plugin['update'] ) ) {

			$class .= ' update';
		}

		printf( '<tr class="plugin-update-tr %s cn-license-status">', $class );

		printf( '<td colspan="%d" class="plugin-update">', esc_attr( $wp_list_table->get_column_count() ) );

		printf(
			'<div class="update-message notice inline notice-%s"><p>%s</p></div>',
			sanitize_html_class( $type ),
			$message
		);

		echo '</td></tr>';
	}

	/**
	 * Callback for the `"in_plugin_update_message-{$file}"` action.
	 *
	 * Displays the plugin's changelog beneath the update available message/action.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 *
	 * @param array  $plugin
	 * @param object $info
	 */
	public static function changelog( $plugin, $info ) {

		echo '</p>'; // Required to close the open <p> tag that exists when this action is run.

		// Show the upgrade notice if it exists.
		if ( isset( $info->upgrade_notice ) && ! empty( $info->upgrade_notice ) ) {

			echo '<p class="cn-update-message-p-clear-before"><strong>' . sprintf( esc_html__( 'Upgrade notice for version: %s', 'connections' ), $info->new_version ) . '</strong></p>';
			echo '<ul><li>' . $info->upgrade_notice . '</li></ul>';
		}

		$sections = maybe_unserialize( $info->sections );

		//--> START Changelog
		if ( isset( $sections['changelog'] ) && ! empty( $sections['changelog'] ) ) {

			// Create the regex that'll parse the changelog for the latest version.
			// NOTE: regex to support readme.txt parsing support in EDD-SL.
			$regex = '~<h([1-6])>' . preg_quote( $info->new_version ) . '.+?</h\1>(.+?)<h[1-6]>~is';

			preg_match( $regex, $sections['changelog'], $matches );
			//echo '<p>' . print_r( $matches, TRUE ) .  '</p>';

			// NOTE: If readme.txt support was not enabled for plugin, parse the changelog meta added by EDD-SL.
			if ( ! isset( $matches[2] ) || empty( $matches[2] ) ) {

				// Create the regex that'll parse the changelog for the latest version.
				$regex = '~<(p)><strong>=\s' . preg_quote( $info->new_version ) . '.+?</strong></\1>(.+?)<p>~is';

				preg_match( $regex, $sections['changelog'], $matches );
				//echo '<p>' . print_r( $matches, TRUE ) .  '</p>';
			}

			// Check if If changelog is found for the current version.
			if ( isset( $matches[2] ) && ! empty( $matches[2] ) ) {

				preg_match_all( '~<li>(.+?)</li>~', $matches[2], $matches );
				// echo '<p>' . print_r( $matches, TRUE ) .  '</p>';

				// Make sure the change items were found and not empty before proceeding.
				if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {

					$ul = FALSE;

					// Finally, lets render the changelog list.
					foreach ( $matches[1] as $key => $line ) {

						if ( ! $ul ) {

							echo '<p class="cn-update-message-p-clear-before"><strong>' . esc_html__( 'Take a minute to update, here\'s why:', 'connections' ) . '</strong></p>';
							echo '<ul class="cn-changelog">';
							$ul = TRUE;
						}

						echo '<li style="' . ( $key % 2 == 0 ? ' clear: left;' : '' ) . '">' . $line . '</li>';
					}

					if ( $ul ) {

						echo '</ul><div style="clear: left;"></div>';
					}
				}
			}
		}
		//--> END Changelog

		echo '<p class="cn-update-message-p-clear-before">'; // Required to open a </p> tag that exists when this action is run.
	}

	/**
	 * Returns the plugins license status message and message type.
	 *
	 * @access private
	 * @since  8.5.24
	 * @static
	 *
	 * @param cnLicense $license
	 *
	 * @return array
	 */
	private static function statusMessage( $license ) {

		$status = array();

		/*
		 * First check to ensure a license key has been saved for the item, if not, bail.
		 */
		if ( 0 == strlen( $license->key ) ) {

			$status['type'] = 'warning';
			$status['code'] = 'no_key';

			$message = esc_html__( 'License has not been activated.', 'connections' );

			$message .= sprintf(
				' <a href="%1$s" title="%2$s">%2$s</a>',
				esc_url( self_admin_url( 'admin.php?page=connections_settings&tab=licenses' ) ),
				esc_html__( 'Please activate now in order to receive support and to enable in admin updates.', 'connections' )
			);

			$status['message'] = $message;

			return $status;
		}

		// Retrieve the items license data.
		$data = cnLicense_Status::get( $license->slug );

		if ( is_wp_error( $data ) ) {

			$status['type']    = 'error';
			$status['code']    = $data->get_error_code();
			$status['message'] = $data->get_error_message();
			return $status;
		}

		// If there was an error message in the EDD SL API response, set the description to the error message.
		if ( isset( $data->success ) && FALSE === $data->success ) {

			// $status = isset( $data[ $slug ] ) && isset( $data[ $slug ]->license ) ? $data[ $slug ]->license : 'unknown';
			$error  = isset( $data->error ) ? $data->error : 'unknown';

			switch ( $error ) {

				case 'expired':

					$status['type'] = 'error';
					$status['code'] = 'expired';

					$message = esc_html__( 'Support license key has expired. Your are no longer receiving support and in admin updates.', 'connections' );

					if ( isset( $data->renewal_url ) ) {

						$message .= sprintf(
							' <a href="%1$s" title="%2$s">%2$s</a>',
							esc_url( $data->renewal_url ),
							esc_html__( 'Click here to renew the support license.', 'connections' )
						);

					}

					$status['message'] = $message;

					return $status;

				case 'invalid_item_id':
				case 'item_name_mismatch':

					$status['type']    = 'error';
					$status['code']    = 'item_name_mismatch';
					$status['message'] = esc_html__( 'License entered is not for this item.', 'connections' );
					break;

				case 'missing':

					$status['type']    = 'error';
					$status['code']    = 'missing';
					$status['message'] = esc_html__( 'Invalid license.', 'connections' );
					break;

				case 'revoked':

					$status['type']    = 'error';
					$status['code']    = 'revoked';
					$status['message'] = esc_html__( 'License has been revoked.', 'connections' );
					break;

				case 'no_activations_left':

					$status['type']    = 'warning';
					$status['code']    = 'no_activations_left';
					$status['message'] = esc_html__( 'License activation limit has been reached.', 'connections' );
					break;

				case 'key_mismatch':

					$status['type']    = 'error';
					$status['code']    = 'key_mismatch';
					$status['message'] = esc_html__( 'License key mismatch.', 'connections' );
					break;

				case 'license_not_activable':

					$status['type']    = 'error';
					$status['code']    = 'license_not_activable';
					$status['message'] = esc_html__( 'Bundle license keys can not be activated. Use item license key instead.', 'connections' );
					break;

				default:

					$status['type']    = 'error';
					$status['code']    = 'unknown_error';
					$status['message'] = esc_html__( 'An unknown error has occurred.', 'connections' );

					delete_option( 'connections_license_data' );
					break;
			}

		} elseif ( isset( $data->success ) && TRUE === $data->success ) {

			// Get the status if the item's license key.
			//$status = self::status( $license->name, $license->key );

			// If there was no error message, display the current license status.
			switch ( $data->license ) {

				case 'invalid':

					$status['type']    = 'error';
					$status['code']    = 'invalid';
					$status['message'] = esc_html__( 'License key invalid.', 'connections' );
					break;

				case 'expired':

					$status['type'] = 'error';
					$status['code'] = 'expired';

					$message = esc_html__( 'Support license key has expired. Your are no longer receiving support and in admin updates.', 'connections' );

					if ( isset( $data->renewal_url ) ) {

						$message .= sprintf(
							' <a href="%1$s" title="%2$s">%2$s</a>',
							esc_url( $data->renewal_url ),
							esc_html__( 'Click here to renew support license.', 'connections' )
						);

					}

					$status['message'] = $message;

					break;

				case 'inactive':

					$status['type']    = 'warning';
					$status['code']    = 'inactive';
					$status['message'] = esc_html__( 'License is not active.', 'connections' );
					break;

				case 'disabled':

					$status['type']    = 'error';
					$status['code']    = 'disabled';
					$status['message'] = esc_html__( 'License has been disabled.', 'connections' );
					break;

				case 'site_inactive':

					$status['type']    = 'warning';
					$status['code']    = 'site_inactive';
					$status['message'] = esc_html__( 'License is not active on this site.', 'connections' );
					break;

				case 'invalid_item_id':
				case 'item_name_mismatch':

					$status['type']    = 'error';
					$status['code']    = 'item_name_mismatch';
					$status['message'] = esc_html__( 'License entered is not for this item.', 'connections' );
					break;

				case 'valid':

					$status['type'] = 'success';
					$status['code'] = 'valid';

					$expiryDate = strtotime( $data->expires );

					if ( $expiryDate !== FALSE ) {

						$message = sprintf( esc_html__( 'License is valid and you are receiving updates. Your support license key will expire on %s.', 'connections' ), date( 'F jS Y', $expiryDate ) );

					} elseif ( 'lifetime' == $data->expires ) {

						$message = esc_html__( 'Lifetime license is valid and you are receiving updates.', 'connections' );

					} else {

						$message = esc_html__( 'License is valid', 'connections' );
					}

					$status['message'] = $message;

					break;

				case 'deactivated':

					$status['type']    = 'warning';
					$status['code']    = 'deactivated';
					$status['message'] = esc_html__( 'License is deactivated.', 'connections' );
					break;

				case 'failed':

					$status['type']    = 'error';
					$status['code']    = 'failed';
					$status['message'] = esc_html__( 'License validation failed.' , 'connections' );
					break;

				default:

					$status['type']    = 'error';
					$status['code']    = 'unknown_error';
					$status['message'] = esc_html__( 'An unknown error has occurred.', 'connections' );

					delete_option( 'connections_license_data' );
			}

		} else {

			$status['type']    = 'error';
			$status['code']    = 'unknown_error';
			$status['message'] = esc_html__( 'An unknown error has occurred.', 'connections' );

			delete_option( 'connections_license_data' );
		}

		return $status;
	}

	/**
	 * Callback registered the render the license key custom settings field type.
	 *
	 * @access private
	 * @since  0.8
	 * @param  string $name  The item name.
	 * @param  string $value The item license key.
	 * @param  array  $field The field options array.
	 */
	public function field( $name, $value, $field ) {

		// The field size. Valid values are: small | regular | large
		$size   = isset( $field['size'] ) && ! empty( $field['size'] ) ? $field['size'] : 'regular';

		// Get the status if the item's license key.
		//$status = self::status( $field['title'], $value );
		$status = self::statusMessage( $this );

		// Render the text input.
		printf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>',
			$size,
			$name,
			isset( $value ) ? $value : ''
		);

		// Render either the "Activate" or "Deactivate" button base on the current license status.
		switch ( $status['code'] ) {

			case 'valid':

				printf(
					'<input type="submit" class="button-secondary" name="%1$s-deactivate_license" value="%2$s">',
					$field['id'],
					esc_html__( 'Deactivate', 'connections' )
				);

				break;

			default:

				printf(
					'<input type="submit" class="button-secondary" name="%1$s-activate_license" value="%2$s">',
					$field['id'],
					esc_html__( 'Activate', 'connections' )
				);
		}

		// Render the current license status.
		if ( 'no_key' != $status['code'] ) {

			printf(
				'<span class="description update-message notice inline notice-%1$s">%2$s</span>',
				sanitize_html_class( $status['type'] ),
				$status['message']
			);
		}
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
	public function sanitizeKey( $settings ) {

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

			update_option( 'connections_license_data', $data, FALSE );
			//delete_transient( 'connections_license-' . $this->slug );
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

			//delete_transient( 'connections_license-' . $this->slug );

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
			update_option( 'connections_licenses', $keys, FALSE );

			wp_clean_plugins_cache();
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

			//delete_transient( 'connections_license-' . $this->slug );

			// Retrieve license keys and data.
			//$keys = get_option( 'connections_licenses' );
			$data = get_option( 'connections_license_data' );

			// If the status is already `deactivated`, no need to attempt to deactivate the key again; bail.
			if ( isset( $data[ $this->slug ]->license ) && $data[ $this->slug ]->license === 'deactivated' ) return;

			// Deactivate the license.
			self::license( 'deactivate', $this->name, $this->key );

			wp_clean_plugins_cache();
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
	 * @return object|WP_Error The EDD SL response for the item on success or WP_Error on fail.
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
			add_query_arg( $query, trailingslashit( $url ) ),
			array(
				'timeout'   => 15,
				'sslverify' => cnHTTP::verifySSL(),
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {

			return $response;
		}

		// Decode the license data
		$data = json_decode( wp_remote_retrieve_body( $response ) );

		switch ( $action ) {

			case 'activate':

				// Add the license data to the licenses data option.
				$licenses[ $slug ] = $data;

				update_option( 'connections_license_data', $licenses, FALSE );

				// Save license data in transient.
				//set_transient( 'connections_license-' . $slug, $data, DAY_IN_SECONDS );

				return $data;

			case 'deactivate':

				// EDD SL reports either 'deactivated' or 'failed' as the license status.
				// Unlike when activating a license, EDD does not report and error and its message.
				// So...
				// We'll do a status check, set 'success' as false and set the message to the license
				// status returned by doing a license check.
				if ( $data->license == 'failed' ) {

					$data = self::license( 'status', $name, $license, $url );

					$data->success = FALSE;
					$data->error   = $data->license;
				}

				// Add the license data to the licenses data option.
				$licenses[ $slug ] = $data;

				update_option( 'connections_license_data', $licenses, FALSE );

				// Save license data in transient.
				//set_transient( 'connections_license-' . $slug, $data, DAY_IN_SECONDS );

				return $data;

			case 'status':

				// Save license data in transient.
				//set_transient( 'connections_license-' . $slug, $data, DAY_IN_SECONDS );

				return $data;
		}

		return new WP_Error(
			"cn_license_{$action}_error",
			sprintf( esc_html__( 'License %s error.', 'connections' ), $action ),
			$query
		);
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
