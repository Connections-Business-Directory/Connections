<?php

/**
 * API for registering templates.
 *
 * @package     Connections
 * @subpackage  Template Factory
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Singleton that registers and instantiates templates.
 *
 * @package Connections
 * @subpackage Template Factory
 * @since 0.7.6
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

			self::$instance = new self;
			self::$templates = new stdClass();

			// Add all the legacy templates found, including the default templates.
			add_action( 'plugins_loaded', array( __CLASS__, 'registerLegacy' ), 10.5 );

			// Initiate the active template classes.
			add_action( 'plugins_loaded', array( __CLASS__, 'activate' ), 100 );

			// Plugins can hook into this action to register templates.
			do_action( 'cn_register_template' );
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
	 *  thumbnail (string) [optional] The template's thumnail file name.
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
	 * @return (void)
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
			'parts'       => array()
			);

		$atts = wp_parse_args( $atts, $defaults );

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
	 * @param  (string) $slug
	 * @param  (string) [optional] $type
	 * @return (void)
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
	 * @return (void)
	 */
	public static function activate() {
		global $connections;

		foreach ( self::$templates as $type => $slug ) {

			foreach ( $slug as $template ) {

				if ( $template->legacy == FALSE && ! empty( $template->class ) ) {
					// var_dump($template->class);
					$connections->template->{ $template->class } = new $template->class( new cnTemplate( $template ) );
					// var_dump($connections->template);
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
	 * @return (void)
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

		$atts = array();
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
	 * @return (void)
	 */
	private static function scan() {

		/**
		 * --> START <-- Find the available templates
		 */
		$templatePaths = array( CN_TEMPLATE_PATH , CN_CUSTOM_TEMPLATE_PATH );
		$templates = new stdClass();

		foreach ( $templatePaths as $templatePath ) {
			if ( ! is_dir( $templatePath ) && ! is_readable( $templatePath ) ) continue;

			if ( ! $templateDirectories = @opendir( $templatePath ) ) continue;
			// var_dump($templatePath);

			//$templateDirectories = opendir($templatePath);

			while ( ( $templateDirectory = readdir( $templateDirectories ) ) !== FALSE ) {

				$path = trailingslashit( $templatePath . $templateDirectory );

				if ( is_dir( $path ) && is_readable( $path ) ) {

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
	 * @param (string)|(array) $type The template catalog to return by type.
	 * @return (object)
	 */
	public static function getCatalog( $types = array() ) {
		$templates = new stdClass();

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
	 * @param  (string) $type The template type.
	 * @param  (string) $slug The template slug.
	 * @return (object)|(bool) If the template is found a cnTemplate object is returned, otherwise FALSE.
	 */
	public static function getTemplate( $slug, $type = '' ) {

		// If the type not was supplied, we'll have to search the self::$templates for the $slug.
		if ( empty( $type ) ) {

			// $t == the template type $s == template slug.
			foreach ( self::$templates as $t => $s ) {

				if ( isset( self::$templates->{ $t }->{ $slug } ) ) {

					$template = new cnTemplate( self::$templates->{ $t }->{ $slug } );
					break;
				}
			}

			$template = isset( $template ) ? $template : FALSE;

		} else {

			$template = isset( self::$templates->{ $type }->{ $slug } ) ? new cnTemplate( self::$templates->{ $type }->{ $slug } ) : FALSE;
		}

		/*
		 * If the template is a legacy template, lets check that the path is still valid before
		 * returning it because it is possible the cached path no longer exists because the
		 * WP install was moved; for example, a  server migration or a site migration.
		 */
		if ( $template && $template->isLegacy() ) {

			return isset( $template ) && ( is_dir( $template->getPath() ) && is_readable( $template->getPath() ) ) ? $template : FALSE;

		} else {

			return $template;
		}

	}

}