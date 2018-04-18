<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * API for registering templates.
 *
 * @package     Connections
 * @subpackage  Template Factory
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6
 */

class cnTemplateFactory {

	/**
	 * The template registry.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (object)
	 */
	public static $templates;

	/**
	 * Stores the catalog of available legacy templates when cnTemplate::scan() is run.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var object
	 */
	public static $legacy;

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (object)
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.7.6
	 * @see cnTemplateFactory::getInstance()
	 * @see cnTemplateFactory();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class.
	 *
	 * @access public
	 * @since 0.7.6
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance  = new self;
			self::$templates = new stdClass();

			/*
			 * When init'ing in the admin, we must use the `admin_init` action hook
			 * so the templates are registered in time to be used on the
			 * Connections : Dashboard admin page.
			 *
			 * When init'ing in the frontend, we must use the `wp` action hook
			 * so the registered query vars have been parsed and available for use
			 * using the get_quer_var() function. Using this function too early will
			 * result in an empty string being returned.
			 */
			if ( is_admin() ) {

				// Add all the legacy templates found, including the default templates.
				add_action( 'admin_init', array( __CLASS__, 'registerLegacy' ) );

				// Initiate the active template classes.
				add_action( 'admin_init', array( __CLASS__, 'activate' ), 100 );

			} else {

				// Add all the legacy templates found, including the default templates.
				add_action( 'wp', array( __CLASS__, 'registerLegacy' ) );

				// Initiate the active template classes.
				add_action( 'wp', array( __CLASS__, 'activate' ), 100 );
			}

			// Plugins can hook into this action to register templates.
			do_action( 'cn_register_template', self::$instance );
		}
	}

	/**
	 * Return an instance.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return object cnTemplateFactory
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Register a template.
	 *
	 * Accepted options for the $atts property are:
	 *  class (string) [required] The name of the class o initialize which contains the templates methods and properties.
	 *  name (string) [required] The template name.
	 *  slug (string) [optional] The template slug.
	 *  type (string) [required] The template type.
	 *  version (string) [required] The template version.
	 *  author (string) [required] The authors name.
	 *  authorURL (string) [optional] The author's website.
	 *  description (string) [optional] Template description.
	 *  custom (bool) Whether this is a custom template or not. [Definition of custom is a template not bundled with core.]
	 *  legacy (bool) [optional|required] Whether or not the template being registered is a legacy template. NOTE: required only when registering legacy templates.
	 *  path (string) [required] The base path to the template's folder.
	 *  url (string) [required] The base URL to the templates's folder.
	 *  thumbnail (string) [optional] The template's thumbnail file name.
	 *  functions (string) [required] The name of the templates functions file. NOTE: required only when registering legacy templates.
	 *  parts (array) [optional] The name of the template's CSS|JS|PHP file for rendering the entry info. NOTE: required only when registering legacy templates.
	 *  	Accepted values for parts:
	 *  		css (string) [optional] The file name of the CSS file.
	 *  		js (string) [optional] The file name of the JS file.
	 *  		card (string) [required] The file name of the PHP file used to render the entry content.
	 *
	 * @access public
	 * @since 0.7.6
	 * @uses sanitize_title_with_dashes()
	 * @param  (array) $atts
	 * @return void
	 */
	public static function register( $atts ) {

		$defaults = array(
			'class'       => '',
			'name'        => '',
			'slug'        => '',
			'type'        => '',
			'version'     => '',
			'author'      => '',
			'authorURL'   => '',
			'description' => '',
			'custom'      => TRUE,
			'legacy'      => FALSE,
			'path'        => '',
			'url'         => '',
			'thumbnail'   => '',
			'functions'   => '',
			'parts'       => array(),
			'supports'    => array(),
			);

		$atts = wp_parse_args( $atts, $defaults );

		/**
		 * Allow plugins to alter template's registration options prior to being registered.
		 *
		 * The dynamic portion of the action hook name is the template's slug.
		 *
		 * @since 8.6.6
		 *
		 * @param array $atts
		 */
		$atts = apply_filters( "cn_template_register_options-{$atts['slug']}", $atts );

		/**
		 * @var $name string
		 * @var $type string
		 */
		extract( $atts );

		// Since the template slug is optional, but required, we'll create the slug from the template's name.
		if ( empty( $slug ) ) $slug = $atts['slug'] = sanitize_title_with_dashes( $name, '', 'save' );

		// PHP 5.4 warning fix.
		if ( ! isset( self::$templates->{ $type } ) ) self::$templates->{ $type } = new stdClass();
		// if ( ! isset( self::$templates->{ $type }->{ $slug } ) ) self::$templates->{ $type }->{ $slug } = new stdClass();
		// self::$templates->{ $type } = new stdClass();
		self::$templates->{ $type }->{ $slug } = new stdClass();

		self::$templates->{ $type }->{ $slug } = (object) $atts;
	}

	/**
	 * Unregister a template.
	 *
	 * @access public
	 * @since 0.7.6
	 * @param  string $slug
	 * @param  string [optional] $type
	 * @return void
	 */
	public static function unregister( $slug, $type = '' ) {

		// If the type not was supplied, we'll have to search the self::$templates for the $slug.
		if ( empty( $type ) ) {

			// $t == the template type $s == template slug.
			foreach ( self::$templates as $t => $s ) {

				if ( isset( self::$templates->{ $t }->{ $slug } ) )
					unset( self::$templates->{ $t }->{ $slug } );
			}

		} else {

			if ( isset( self::$templates->{ $type }->{ $slug } ) )
				unset( self::$templates->{ $type }->{ $slug } );
		}

	}

	/**
	 * Activate registered templates.
	 *
	 * @access private
	 * @since 0.7.6
	 * @return void
	 */
	public static function activate() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		foreach ( self::$templates as $type => $slug ) {

			foreach ( $slug as $template ) {

				if ( $template->legacy == FALSE && ! empty( $template->class ) ) {

					// Init an instance of the cnTemplate object with $template.
					$t = new cnTemplate( $template );

					// If the template has a core class, init it passing its instance of cnTemplate
					// so it is easily accessible within its class.
					$instance->template->{ $template->class } = new $template->class( $t );

					// Add a reference to its instance of cnTemplate to the plugins globally accessible instance.
					// This is to allow easy access when loading the template within the shortcode.
					$instance->template->{ $template->slug }  = $t;

				} else {

					// If the template does not have a code class, init an instance of the cnTemplate object with $template.
					// and add it to the plugins globally accessible instance.
					$instance->template->{ $template->slug } = new cnTemplate( $template );
				}

			}

		}

	}

	/**
	 * Scan for and register legacy templates.
	 * NOTE: A legacy template is a template that was developed before 0.7.6 and is not a plugin.
	 *
	 * @access private
	 * @uses get_transient()
	 * @uses set_transient()
	 * @since 0.7.6
	 * @return void
	 */
	public static function registerLegacy() {

		$legacyTemplates = get_transient( 'cn_legacy_templates' );

		if ( $legacyTemplates === FALSE ) {

			// Build a catalog of all legacy templates.
			self::scan();

			set_transient( 'cn_legacy_templates', self::$legacy, 60*60*24 );

		} else {

			self::$legacy = $legacyTemplates;
		}

		$atts  = array();
		$parts = array();

		// Register each template.
		foreach ( self::$legacy as $type => $meta ) {

			foreach ( $meta as $template ) {

				$atts['class']       = '';
				$atts['name']        = $template->name;
				$atts['slug']        = $template->slug;
				$atts['type']        = $type;
				$atts['version']     = $template->version;
				$atts['author']      = $template->author;
				$atts['authorURL']   = $template->uri;
				$atts['description'] = $template->description;
				$atts['custom']      = $template->custom;
				$atts['legacy']      = TRUE;

				$atts['path']        = ( $template->custom ) ? trailingslashit( CN_CUSTOM_TEMPLATE_PATH . $template->slug ) : trailingslashit( CN_TEMPLATE_PATH . $template->slug );
				$atts['url']         = ( $template->custom ) ? trailingslashit( CN_CUSTOM_TEMPLATE_URL . $template->slug ) : trailingslashit( CN_TEMPLATE_URL . $template->slug );

				$atts['thumbnail']   = isset( $template->thumbnailURL ) ? 'thumbnail.png' : '';
				$atts['functions']   = isset( $template->phpPath ) ? 'functions.php' : '';

				$parts['css']        = isset( $template->cssPath ) ? 'styles.css' : '';
				$parts['js']         = isset( $template->jsPath ) ? 'template.js' : '';
				$parts['card']       = 'template.php';

				$atts['parts']       = $parts;

				self::register( $atts );
			}

		}

	}

	/**
	 * Builds a catalog of all the available Legacy templates from the supplied and the custom template directories.
	 *
	 * @access private
	 * @since 0.7.6
	 * @return void
	 */
	private static function scan() {

		/**
		 * --> START <-- Find the available templates
		 */
		$templatePaths = array( CN_TEMPLATE_PATH , CN_CUSTOM_TEMPLATE_PATH );
		$templates     = new stdClass();
		$baseDirs      = array();

		if ( 0 < strlen( ini_get( 'open_basedir' ) ) ) {

			$baseDirs = explode( PATH_SEPARATOR, ini_get( 'open_basedir' ) );

			foreach ( $baseDirs as $key => $path ) {

				$baseDirs[ $key ] = wp_normalize_path( $path );
			}
		}

		foreach ( $templatePaths as $templatePath ) {

			$templatePath = wp_normalize_path( $templatePath );

			foreach ( $baseDirs as $path ) {

				if ( FALSE === stripos( $templatePath, $path ) ) {
					continue;
				}
			}

			if ( ! is_dir( $templatePath ) && ! is_readable( $templatePath ) ) continue;

			if ( ! $templateDirectories = @opendir( $templatePath ) ) continue;
			// var_dump($templatePath);

			//$templateDirectories = opendir($templatePath);

			while ( ( $templateDirectory = readdir( $templateDirectories ) ) !== FALSE ) {

				$path = trailingslashit( $templatePath . $templateDirectory );

				if ( @is_dir( $path ) && @is_readable( $path ) ) {

					if ( file_exists( $path . 'meta.php' ) && file_exists( $path . 'template.php' ) ) {

						$template = new stdClass();
						include( $path . 'meta.php');
						$template->slug = $templateDirectory;

						if ( ! isset( $template->type ) ) $template->type = 'all';

						// PHP 5.4 warning fix.
						if ( ! isset( $templates->{ $template->type } ) ) $templates->{ $template->type } = new stdClass();
						if ( ! isset( $templates->{ $template->type }->{ $template->slug } ) ) $templates->{ $template->type }->{ $template->slug } = new stdClass();

						// Load the template metadate from the meta.php file
						$templates->{ $template->type }->{ $template->slug }->name        = $template->name;
						$templates->{ $template->type }->{ $template->slug }->version     = $template->version;
						$templates->{ $template->type }->{ $template->slug }->uri         = isset( $template->uri ) ? 'http://' . $template->uri : '';
						$templates->{ $template->type }->{ $template->slug }->author      = $template->author;
						$templates->{ $template->type }->{ $template->slug }->description = isset( $template->description ) ? $template->description : '';

						$templates->{ $template->type }->{ $template->slug }->path        = $path;
						$templates->{ $template->type }->{ $template->slug }->slug        = $template->slug ;
						$templates->{ $template->type }->{ $template->slug }->custom      = ( CN_CUSTOM_TEMPLATE_PATH === $templatePath ) ? TRUE : FALSE;

						if ( file_exists( $path . 'styles.css' ) ) $templates->{ $template->type }->{ $template->slug }->cssPath         = TRUE;
						if ( file_exists( $path . 'template.js' ) ) $templates->{ $template->type }->{ $template->slug }->jsPath         = TRUE;
						if ( file_exists( $path . 'functions.php' ) ) $templates->{ $template->type }->{ $template->slug }->phpPath      = TRUE;
						if ( file_exists( $path . 'thumbnail.png' ) ) $templates->{ $template->type }->{ $template->slug }->thumbnailURL = TRUE;
					}
				}
			}

			//var_dump($templateDirectories);
			@closedir( $templateDirectories );
		}
		/**
		 * --> END <-- Find the available templates
		 */
		self::$legacy = $templates;
	}

	/**
	 * Returns the catalog of all registered templates by type.
	 *
	 * @access public
	 * @since 0.7.6
	 * @param string|array $types The template catalog to return by type.
	 * @return object
	 */
	public static function getCatalog( $types = array() ) {

		$templates = new stdClass();

		// Purge the transient so the page is freshly scanned by the template API.
		delete_transient( 'cn_legacy_templates' );

		self::registerLegacy();
		//self::activateOLD();

		// Convert to an array.
		if ( ! is_array( $types ) ) {

			// Attempt to remove any stray spaces a user may have typed.
			$types = explode( ',', trim( str_replace( ' ', '', $types ) ) );
		}

		if ( empty( $types ) ) {

			// Return all template types.
			foreach ( self::$templates as $template ) {

				/*
				 * If the template is a legacy template, lets check that the path is still valid before
				 * returning it because it is possible the cached path no longer exists because the
				 * WP install was moved; for example, a  server migration or a site migration.
				 */
				if ( $template->legacy && is_dir( $template->path ) && is_readable( $template->path ) ) {

					$templates->{ $template->slug } = new cnTemplate( $template );

				} else if ( ! $template->legacy ) {

					$templates->{ $template->slug } = new cnTemplate( $template );
				}

			}

		} else {

			// Return on the specified template types.

			// Merge in templates registered for the "all" template type.
			$types[] = 'all';

			foreach ( $types as $type ) {

				// If there are no registered templates by the requested type, move on.
				if ( ! isset( self::$templates->$type ) ) continue;

				foreach ( self::$templates->$type as $template ) {

					/*
					 * If the template is a legacy template, lets check that the path is still valid before
					 * returning it because it is possible the cached path no longer exists because the
					 * WP install was moved; for example, a  server migration or a site migration.
					 */
					if ( $template->legacy && is_dir( $template->path ) && is_readable( $template->path ) ) {

						$templates->{ $template->slug } = new cnTemplate( $template );

					} else if ( ! $template->legacy ) {

						$templates->{ $template->slug } = new cnTemplate( $template );
					}

				}

			}

		}

		return $templates;
	}

	/**
	 * Return the requested template.
	 *
	 * @access public
	 * @since 0.7.6
	 * @param  string $type The template type.
	 * @param  string $slug The template slug.
	 *
	 * @return cnTemplate|FALSE If the template is found a cnTemplate object is returned, otherwise FALSE.
	 */
	public static function getTemplate( $slug, $type = '' ) {

		/** @var $template cnTemplate */

		/**
		 * Filter the template to get based on its slug.
		 *
		 * @since 8.4
		 *
		 * @param string $slug The template slug.
		 * @param string $type The template type.
		 */
		$slug = apply_filters( 'cn_get_template', $slug, $type );

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// If the type not was supplied, we'll have to search the self::$templates for the $slug.
		if ( empty( $type ) ) {

			// $t == the template type $s == template slug.
			foreach ( self::$templates as $t => $s ) {

				if ( isset( self::$templates->{ $t }->{ $slug } ) ) {

					$template = $instance->template->{ $slug };

					break;
				}
			}

			$template = isset( $template ) ? $template : FALSE;

		} else {

			$template = isset( self::$templates->{ $type }->{ $slug } ) ? $instance->template->{ $slug } : FALSE;
		}

		/*
		 * If the template is a legacy template, lets check that the path is still valid before
		 * returning it because it is possible the cached path no longer exists because the
		 * WP install was moved; for example, a  server migration or a site migration.
		 */
		if ( $template instanceof cnTemplate && $template->isLegacy() ) {

			return isset( $template ) && ( is_dir( $template->getPath() ) && is_readable( $template->getPath() ) ) ? $template : FALSE;

		} elseif ( $template instanceof cnTemplate ) {

			return $template;

		} else {

			return $template;
		}
	}

	/**
	 * Load the template. The template that will be loaded will be
	 * determined by the template activated under the `All` template type.
	 * Unless overridden by either the `template` or `list_type` shortcode
	 * options.
	 *
	 * @access private
	 * @since  0.8
	 * @static
	 *
	 * @param  array $atts The shortcode atts array.
	 *
	 * @return cnTemplate|FALSE An instance the of cnTemplate object or `FALSE` if the template was not found/loaded.
	 */
	public static function loadTemplate( $atts ) {

		$type     = 'all';
		$defaults = array(
			'list_type'     => NULL,
			'template'      => NULL,
		);

		/**
		 * @since 0.7.9.4
		 *
		 * @param array $atts {
		 *     @type string $list_type The shortcode list_type attribute value.
		 *     @type string $template  The template slug.
		 * }
		 */
		$defaults = apply_filters( 'cn_load_template', $defaults );
		$atts     = shortcode_atts( $defaults, $atts );

		if ( ! empty( $atts['list_type'] ) ) {

			$permittedTypes = array( 'individual', 'organization', 'family', 'connection_group');

			// Convert to array. Trim the space characters if present.
			$atts['list_type'] = explode( ',' , str_replace( ' ', '', $atts['list_type'] ) );

			// Set the template type to the first in the entry type from the supplied if multiple list types are provided.
			if ( in_array( $atts['list_type'][0], $permittedTypes ) ) {

				$type = $atts['list_type'][0];

				// Change the list type to family from connection_group to maintain compatibility with versions 0.7.0.4 and earlier.
				if ( $type == 'connection_group' ) $type = 'family';
			}
		}

		/*
		 * If a list type was specified in the shortcode, load the template based on that type.
		 * However, if a specific template was specified, that should preempt the template to be loaded based on the list type if it was specified..
		 */
		if ( ! empty( $atts['template'] ) ) {

			$template = self::getTemplate( $atts['template'] );

		} else {

			// Grab an instance of the Connections object.
			$instance = Connections_Directory();

			$slug     = $instance->options->getActiveTemplate( $type );
			$template = self::getTemplate( $slug );
		}

		// If the template was not located, return FALSE.
		// This will in turn display the template not found error message
		// later in the execution of the shortcode.
		if ( $template == FALSE ) return FALSE;

		/** @var cnTemplate $template */
		do_action( 'cn_register_legacy_template_parts' );
		do_action( 'cn_action_include_once-' . $template->getSlug() );
		do_action( 'cn_action_js-' . $template->getSlug() );

		return $template;
	}

}
