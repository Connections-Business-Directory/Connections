<?php

/**
 * Class for working with a template object.
 *
 * @package     Connections
 * @subpackage  Template
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnTemplate
 */
class cnTemplate {

	/**
	 * Template name.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $name;

	/**
	 * Template slug [template directory name for legacy templates, both default and custom].
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $slug;

	/**
	 * Template type.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $type;

	/**
	 * Template version.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $version;

	/**
	 * Template's author's name.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $author;

	/**
	 * Template's author's home page.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $authorURL;

	/**
	 * Template description.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $description;

	/**
	 * Set TRUE if the template is NOT one of the supplied templates.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (bool)
	 */
	private $custom;

	/**
	 * Set TRUE if the template should use the legacy functions.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (bool)
	 */
	private $legacy;

	/**
	 * The template base path.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $path;

	/**
	 * Template URL.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $url;

	/**
	 * Template thumbnail file name.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $thumbnail;

	/**
	 * Template functions file name.
	 * NOTE: This is only set for legacy templates.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (string)
	 */
	private $functions;

	/**
	 * Registry of templates parts.
	 *
	 * @access private
	 * @since 0.7.6
	 * @var (array)
	 */
	public $parts = array();

	private $supports = array();

	/**
	 * Setup the template.
	 *
	 * @access public
	 * @since  0.7.6
	 *
	 * @param  object $atts
	 */
	public function __construct( $atts ) {

		$this->name        = $atts->name;
		$this->class       = $atts->class;
		$this->slug        = $atts->slug;
		$this->type        = $atts->type;
		$this->version     = $atts->version;
		$this->author      = $atts->author;
		$this->authorURL   = $atts->authorURL;
		$this->description = $atts->description;
		$this->custom      = $atts->custom;
		$this->legacy      = $atts->legacy;
		$this->path        = $atts->path;
		$this->url         = $atts->url;
		$this->thumbnail   = $atts->thumbnail;
		$this->functions   = $atts->functions;
		$this->parts       = $atts->parts;
		//$this->supports    = $atts->supports;

		/**
		 * @todo This code is commented out for now because it was implemented in @see cnTemplate_Customizer().
		 *       What needs to be done is the code extracted out of the cnTemplate_Customizer class into its own class
		 *       so it can be shared with this class and cnTemplate_Customizer.
		 */
		$this->setupTemplateFeatures( $atts->supports );

		// This filter is to make sure the legacy template file names are added to the search paths.
		add_filter( 'cn_template_file_names-' . $this->slug, array( $this, 'legacyFileNames' ), 10, 5 );

		// This filter will add the minified CSS and JS to the search paths if SCRIPT_DEBUG is not defined
		// or set to FALSE.
		//add_filter( 'cn_template_file_names-' . $this->slug, array( $this, 'minifiedFileNames' ), 11, 5 );

		// This will locate the template card to be used.
		$templatePath = $this->locate( $this->fileNames( 'card' ) );
		// var_dump($templatePath);

		if ( $templatePath !== FALSE ) {
			// var_dump($templatePath);
			$templatePath = addslashes( $templatePath );
			// The action should only be added once.
			if ( ! has_action( 'cn_template-' . $this->slug ) ) {

				// Add the action which will include the template file. The action is executed in cnTemplate_Part::cards().
				add_action( 'cn_template-' . $this->slug, create_function( '$entry, $template, $atts', 'include(\'' . $templatePath . '\');' ), 10, 3 );

				// @todo This is a legacy action that should be removed at some point in the future. 04.11.2014
				add_action( 'cn_action_card-' . $this->slug, create_function( '$entry, $template, $atts', 'include(\'' . $templatePath . '\');' ), 10, 3 );
			}

		}

		// This will locate the CSS file to be enqueued.
		$cssPath = $this->locate( $this->fileNames( $this->slug, NULL, NULL, 'css' ) );
		// var_dump($cssPath);

		if ( $cssPath !== FALSE ) {
			// var_dump($cssPath);

			$this->parts['css-path'] = $cssPath;
			$this->parts['css-url']  = cnURL::fromPath( $cssPath );

			// If `$this->parts['css']` is set then it is very likely a legacy template.
			// Legacy templates had the CSS rendered inline right before the results lists.
			if ( isset( $this->parts['css'] ) && ! empty( $this->parts['css'] ) ) {

				// The action should only be added once.
				if ( ! has_action( 'cn_template_inline_css-' . $this->slug ) ) {

					add_action( 'cn_template_inline_css-' . $this->slug , array( $this, 'printCSS' ), 10, 3 );
				}

			} else {

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueueCSS' ) );
			}
		}

		// This will locate the custom CSS file to be enqueued.
		$customCSS = $this->locate( $this->fileNames( "{$this->slug}-custom", NULL, NULL, 'css' ) );
		// var_dump($customCSS);

		// If a custom CSS file was found, lets register it.
		if ( $customCSS  !== FALSE ) {
			// var_dump($customCSS);

			$this->parts['css-custom-path'] = $customCSS;
			$this->parts['css-custom-url']  = cnURL::fromPath( $customCSS );
		}

		// This will locate the JS file to be included.
		$jsPath = $this->locate( $this->fileNames( $this->slug, NULL, NULL, 'js' ) );
		// var_dump($jsPath)

		if ( $jsPath !== FALSE ) {
			// var_dump($jsPath);

			$this->parts['js-path'] = $jsPath;
			$this->parts['js-url']  = cnURL::fromPath( $jsPath );

			// The action should only be added once.
			if ( ! has_action( 'cn_template_enqueue_js-' . $this->slug ) ) {

				add_action( 'cn_template_enqueue_js-' . $this->slug, array( $this, 'enqueueScript' ) );
			}
		}

		// Only legacy templates had a `functions.php` so only search for it on legacy templates.
		if ( $this->legacy == TRUE ) {

			$functionsPath = $this->locate( $this->fileNames( 'functions', NULL, NULL, 'php' ) );
			// var_dump($functionsPath);

			if ( $functionsPath !== FALSE ) {
				// var_dump($functionsPath);

				// The action should only be added once.
				if ( ! has_action( 'cn_template_include_once-' . $this->slug ) ) {

					add_action( 'cn_template_include_once-' . $this->slug, array( $this, 'includeFunctions' ), 10, 3 );
				}
			}

		}

		return $this;
	}

	/**
	 * Get the template name.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the template slug.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * Get the template type.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Get the template version.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Get the template author name.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Get the template author's URL.
	 *
	 * @access public
	 * @since  0.7.6
	 *
	 * @return string
	 */
	public function getAuthorURL() {

		return cnURL::prefix( $this->authorURL );
	}

	/**
	 * Get the template description.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Whether the template is custom or not.
	 * Definition: A custom template is a template not bundled with core.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (bool)
	 */
	public function isCustom() {
		return $this->custom;
	}

	/**
	 * Whether or not a template is legacy or not.
	 * NOTE: A legacy template is a template that was developed before 0.7.6 and is not a plugin.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (bool)
	 */
	public function isLegacy() {
		return $this->legacy;
	}

	/**
	 * Get the template base path.
	 *
	 * @access public
	 * @since  0.7.6
	 * @uses   trailingslashit()
	 *
	 * @return string
	 */
	public function getPath() {

		/*
		 * The template path is required when registering a template, but is not enforced.
		 * So, there is a possibility that this value is empty.
		 *
		 * Since class name is absolutely required and the file defining said class is very
		 * likely to be in the folder with the rest of the template files, we'll use
		 * reflection to get the file name of the class and use the file name to get the
		 * directory name.
		 *
		 * Now, in theory, we should have the path to the template files.
		 */
		if ( empty( $this->path ) ) {

			$reflector = new ReflectionClass( $this->class );
			$this->path = trailingslashit( dirname( $reflector->getFileName() ) );
			// var_dump( $this->path );
		}

		return $this->path;
	}

	/**
	 * Get the template base URL.
	 *
	 * @access public
	 * @since  0.7.6
	 * @uses   cnURL::fromPath()
	 * @uses   getPath()
	 *
	 * @return string
	 */
	public function getURL() {

		/*
		 * The template URL is required when registering a template, but is not enforced.
		 * So, there is a possibility that this value is empty.
		 *
		 * Let get the URL from the $this->getPath().
		 */
		if ( empty( $this->url ) ) {

			$this->url = cnURL::fromPath( $this->getPath() );
		}

		return $this->url;
	}

	/**
	 * Get the template thumbnail file name.
	 *
	 * @access public
	 * @since  0.7.6
	 *
	 * @return array
	 */
	public function getThumbnail() {
		$thumbnail = array();

		if ( $this->thumbnail ) {

			$thumbnail['name'] = $this->thumbnail;
			$thumbnail['url']  = $this->url . $this->thumbnail;
		}

		return $thumbnail;
	}

	public function setupTemplateFeatures( $features ) {

		if ( is_array( $features ) ) {

			foreach ( $features as $feature => $options ) {

				$this->supports[ $feature ] = $options;
			}

		} else {

			$this->supports[ $features ] = TRUE;
		}
	}

	public function supports( $feature ) {

		return array_key_exists( $feature, $this->supports );
	}

	public function getSupportsOptions( $feature ) {

		if ( $this->supports( $feature ) ) {

			return $this->supports[ $feature ];
		}

		return FALSE;
	}

	/**
	 * Retrieve a template setting value by setting ID slug.
	 *
	 * @access public
	 * @since  8.6.7
	 *
	 * @param string $key     The setting ID slug in which to retrieve the setting value.
	 * @param null   $default The default setting value if the requested $key is not set or does not exist.
	 *
	 * @return mixed|null
	 */
	public function getOption( $key, $default = NULL ) {

		if ( get_query_var( 'cn-entry-slug' ) ) {

			/**
			 * @var array $option
			 */
			$options = cnSettingsAPI::get( 'connections_template', $this->getSlug(), 'single' );

		} else {

			/**
			 * @var array $options
			 */
			$options = cnSettingsAPI::get( 'connections_template', $this->getSlug(), 'card' );
		}

		$value = isset( $options[ $key ] ) ? $options[ $key ] : $default;

		return $value;
	}

	/**
	 * Register a template part.
	 *
	 * This is a deprecated function. Its current purpose is to only register template
	 * parts that used a callback function rather including a file. To my knowledge,
	 * only the core templates used this structure. The commercial templates all
	 * included their template files.
	 *
	 * @access public
	 * @since  0.7.6
	 * @deprecated since 0.8
	 * @param  $atts array The part options array.
	 *
	 * @return void
	 */
	public function part( $atts = array() ) {

		$defaults = array(
			'tag'      => '',
			'type'     => '',
			'callback' => '',
			);

		$atts = wp_parse_args( $atts, $defaults );

		if ( $atts['type'] == 'action' ) {

			switch ( $atts['tag'] ) {

				case 'card':

					if ( ! has_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug ) ) {

						add_action( 'cn_template-' . $this->slug, $atts['callback'], 10, 3 );

						add_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug , $atts['callback'], 10, 3 );
					}

					break;

				case 'card_single':

					if ( ! has_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug ) ) {

						add_action( 'cn_template-' . $this->slug, $atts['callback'], 10, 3 );

						add_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug , $atts['callback'], 10, 3 );
					}

					break;

				case 'css':

					// code here...

					break;

				case 'js':

					if ( ! has_action( 'cn_template_enqueue_js-' . $this->slug ) ) {

						add_action( 'cn_template_enqueue_js-' . $this->slug, $atts['callback'], 10, 3 );
					}

					break;
			}

		}

	}

	/**
	 * Locate the file paths of the template, CSS and JS files
	 * based on the supplied array of file names. The file name
	 * array should be in order of highest priority first, lowest
	 * priority last.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   filePaths()
	 * @param  array  $files An indexed array of file names to search for.
	 *
	 * @return string        The absolution file system path to the located file.
	 */
	private function locate( $files ) {

		$paths = $this->filePaths();

		// Try locating this template file by looping through the template paths.
		/*foreach ( $paths as $filePath ) {

			// Try to find a template file.
			foreach ( $files as $fileName ) {
				// var_dump( $filePath . $fileName );

				if ( file_exists( $filePath . $fileName ) ) {
					// var_dump( $filePath . $fileName );

					$path = $filePath . $fileName;
					break 2;
				}
			}
		}*/

		// Try to find a template file.
		foreach ( $files as $fileName ) {

			// Try locating this template file by looping through the template paths.
			foreach ( $paths as $filePath ) {

				$absolutePath = $this->checkForMinified( $filePath . $fileName );
				// var_dump( $absolutePath );

				if ( file_exists( $absolutePath ) ) {
					//var_dump( $absolutePath );

					return $absolutePath;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Check to see if a minified file exists for the supplied CSS|JS template resource and return its
	 * absolute server path.
	 *
	 * @access private
	 * @since  8.2.8
	 *
	 * @param string $filePath Absolute server path to CSS|JS template resource file.
	 *
	 * @return string
	 */
	private function checkForMinified( $filePath ) {

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {

			return $filePath;
		}

		$file = pathinfo( $filePath );

		if ( 'css' == $file['extension'] || 'js' == $file['extension'] ) {

			$minified = $file['dirname'] . DIRECTORY_SEPARATOR . $file['filename'] . '.min.' . $file['extension'];
			// var_dump( $minified );

			if ( file_exists( $minified ) ) {
				// var_dump( $minified );

				return $minified;
			}

		}

		return $filePath;
	}

	/**
	 * Returns an array of file paths to be search for template files.
	 *
	 * The file paths is an indexed array where the highest priority path
	 * is first and the lowest priority is last.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   trailingslashit()
	 * @uses   is_child_theme()
	 * @uses   trailingslashit()
	 * @uses   get_stylesheet_directory()
	 * @uses   get_template_directory()
	 * @uses   getPath()
	 * @uses   apply_filters()
	 *
	 * @return array An indexed array of file paths.
	 */
	private function filePaths() {

		$path  = array();

		$template_directory = trailingslashit( 'connections-templates' );

		$upload_dir = wp_upload_dir();

		// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
		if ( is_child_theme() ) {

			$path[5] = trailingslashit( get_stylesheet_directory() ) . $template_directory . trailingslashit( $this->slug );
		}

		$path[10]  = trailingslashit( get_template_directory() ) . $template_directory . trailingslashit( $this->slug );
		$path[40]  = trailingslashit( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'connections-templates' . DIRECTORY_SEPARATOR . $this->slug );
		$path[50]  = trailingslashit( $upload_dir['basedir'] ) . $template_directory . trailingslashit( $this->slug );
		$path[99]  = CN_CUSTOM_TEMPLATE_PATH . trailingslashit( $this->slug );
		$path[100] = $this->getPath();

		$path = apply_filters( 'cn_template_file_paths-' . $this->slug, $path );
		// var_dump($path);

		// Sort the file paths based on priority.
		ksort( $path, SORT_NUMERIC );

		return array_filter( $path );
	}

	/**
	 * An indexed array of file names to be searched for.
	 *
	 * The file names is an index array of file names where the
	 * highest priority is first and the lowest priority is last.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   apply_filters()
	 * @uses   cnQuery::getVar()
	 * @param  string $base The base file name. Typically `card` for a template file and the template slug for CSS and JS files.
	 * @param  string $name The template part name; such as `single` or `category`.
	 * @param  string $slug The template part slug; such as an entry slug or category slug.
	 * @param  string $ext  [optional] The template file name extension. Defaults to `php`.
	 *
	 * @return array        An indexed array of file names to search for.
	 */
	private function fileNames( $base, $name = NULL, $slug = NULL, $ext = 'php' ) {

		$files = array();

		if ( cnQuery::getVar( 'cn-cat' ) ) {

			$categoryID = cnQuery::getVar( 'cn-cat' );

			// Since the `cn-cat` query var can be an array, we'll only add the category slug
			// template name when querying a single category.
			if ( ! is_array( $categoryID ) ) {

				$term = cnTerm::getBy( 'id', $categoryID, 'category' );

				$files[] = $this->fileName( $base, 'category', $term->slug, $ext );
			}

			$files[] = $this->fileName( $base, 'category', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-cat-slug' ) ) {

			$files[] = $this->fileName( $base, 'category', cnQuery::getVar( 'cn-cat-slug'), $ext );
			$files[] = $this->fileName( $base, 'category', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-country' ) ) {

			$country = $this->queryVarSlug( cnQuery::getVar( 'cn-country' ) );

			$files[] = $this->fileName( $base, 'country', $country, $ext );
			$files[] = $this->fileName( $base, 'country', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-region' ) ) {

			$region  = $this->queryVarSlug( cnQuery::getVar( 'cn-region' ) );

			$files[] = $this->fileName( $base, 'region', $region, $ext );
			$files[] = $this->fileName( $base, 'region', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-postal-code' ) ) {

			$zipcode = $this->queryVarSlug( cnQuery::getVar( 'cn-postal-code' ) );

			$files[] = $this->fileName( $base, 'postal-code', $zipcode, $ext );
			$files[] = $this->fileName( $base, 'postal-code', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-locality' ) ) {

			$locality = $this->queryVarSlug( cnQuery::getVar( 'cn-locality' ) );

			$files[] = $this->fileName( $base, 'locality', $locality, $ext );
			$files[] = $this->fileName( $base, 'locality', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-organization' ) ) {

			$organization = $this->queryVarSlug( cnQuery::getVar( 'cn-organization' ) );

			$files[] = $this->fileName( $base, 'organization', $organization, $ext );
			$files[] = $this->fileName( $base, 'organization', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-department' ) ) {

			$department = $this->queryVarSlug( cnQuery::getVar( 'cn-department' ) );

			$files[] = $this->fileName( $base, 'department', $department, $ext );
			$files[] = $this->fileName( $base, 'department', NULL, $ext );
			// var_dump( $files );
		}

		if ( cnQuery::getVar( 'cn-entry-slug' ) ) {

			$files[] = $this->fileName( $base, NULL, cnQuery::getVar( 'cn-entry-slug'), $ext );
			$files[] = $this->fileName( $base, 'single', NULL, $ext );
			// var_dump( $files );
		}

		// Add the base as the least priority, since it is required.
		$files[] = $this->fileName( $base, NULL, NULL, $ext );

		/**
		 * Allow template choices to be filtered.
		 *
		 * The resulting array should be in the order of most specific first, least specific last.
		 * e.g. 0 => card-single.php, 1 => card.php
		 */
		$files = apply_filters( 'cn_template_file_names-' . $this->slug, $files, $base, $name, $slug, $ext );
		// var_dump( $files );

		// Sort the files based on priority
		ksort( $files, SORT_NUMERIC );
		// var_dump( $files );

		return array_filter( $files );
	}

	/**
	 * Create file name from supplied attributes.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @param  string $base The base file name.
	 * @param  string $name The template part name.
	 * @param  string $slug The template part slug.
	 * @param  string $ext  The template file name extension.
	 *
	 * @return string       The file name.
	 */
	private function fileName( $base, $name = NULL, $slug = NULL, $ext = 'php' ) {

		$name = array( $base, $name, $slug );
		$name = array_filter( $name );
		$name = implode( '-', $name ) . '.' . $ext;

		//return strtolower( sanitize_file_name( $name ) );
		return strtolower( $name );
	}

	/**
	 * Takes a supplied query var and creates a file system safe slug.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   sanitize_file_name()
	 * @param  string $queryVar A query var.
	 *
	 * @return string           A file system safe string.
	 */
	private function queryVarSlug( $queryVar ) {

		return strtolower( sanitize_file_name( urldecode( $queryVar ) ) );
	}

	/**
	 * This is the callback function that will add the legacy file names to
	 * the file name array.
	 *
	 * @access private
	 * @since  0.8
	 * @see    __construct()
	 * @see    fileNames()
	 * @param  array  $files An indexed array of file names to search for.
	 * @param  string $base The base file name. Passed via filter from fileNames().
	 * @param  string $name The template part name. Passed via filter from fileNames().
	 * @param  string $slug The template part slug. Passed via filter from fileNames().
	 * @param  string $ext  The template file name extension. Passed via filter from fileNames().
	 *
	 * @return array        An indexed array of file names to search for.
	 */
	public function legacyFileNames( $files, $base, $name, $slug, $ext ) {

		switch ( $ext ) {

			case 'php':

				// If this is a legacy template which has a `functions.php` file and it being searched for thru
				// @see locate(), do not add the legacy 'template.php' filename.
				if ( $base == 'functions' ) {

					// Only the base name needs to be searched for when searching for `functions.php`.
					$files = array( array_pop( $files ) );
					// var_dump($files);
					break;
				}

				// If this is a legacy template, add the file 'template.php' as the least priority ( last in the file name array )
				if ( isset( $this->parts['card'] ) && ! empty( $this->parts['card'] ) ) $files[] = $this->parts['card'];

				break;

			case 'css':

				// If this is a legacy template, add the file 'styles.css' as the least priority ( last in the file name array )
				if ( isset( $this->parts['css'] ) && ! empty( $this->parts['css'] ) ) $files[] = $this->parts['css'];

				break;

			case 'js':

				// If this is a legacy template, add the file 'template.js' as the least priority ( last in the file name array )
				if ( isset( $this->parts['js'] ) && ! empty( $this->parts['js'] ) ) $files[] = $this->parts['js'];

				break;

		}

		return $files;
	}

	/**
	 * This is the callback function that will add the minified CSS and JS
	 * file names to the file name array.
	 *
	 * The minified file names will only be added if SCRIPT_DEBUG is defined
	 * and set to true.
	 *
	 * @access private
	 * @since  0.8
	 * @see    __construct()
	 * @see    fileNames()
	 * @param  array  $files An indexed array of file names to search for.
	 * @param  string $base The base file name. Passed via filter from fileNames().
	 * @param  string $name The template part name. Passed via filter from fileNames().
	 * @param  string $slug The template part slug. Passed via filter from fileNames().
	 * @param  string $ext  The template file name extension. Passed via filter from fileNames().
	 *
	 * @return array        An indexed array of file names to search for.
	 */
	public function minifiedFileNames( $files, $base, $name, $slug, $ext ) {

		// If SCRIPT_DEBUG is set and TRUE the minified file names
		// do not need added to the $files name array.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) return $files;

		if ( $ext == 'css' || $ext == 'js' ) {

			$i = 0;

			foreach ( $files as $fileName ) {

				// Create the minified file name.
				$position = strrpos( $fileName, '.' );
				$minified = substr( $fileName, 0, $position ) . '.min' . substr( $fileName, $position );

				// Insert the minified file name into the array.
				array_splice( $files, $i, 0, $minified );

				// Increment the insert position. Adding `2` to take into account the updated insert position
				// due to an item being inserted into the array.
				$i = $i + 2;
			}
		}

		return $files;
	}

	/**
	 * This a callback for the filter `cn_locate_file_paths` which adds
	 * the template paths that cnLocate will search. The filter is added
	 * in cnShortcode_Connections::shortcode(). This is done so when cnLocate
	 * is searching for template part override files it'll search in the template
	 * paths too. The filter is then removed at the end of cnShortcode_Connections::shortcode()
	 * via a call to cnShortcode::clearFilterRegistry(). This is to ensure that the template
	 * paths are only searched in that instance of the shortcode.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   trailingslashit()
	 * @uses   wp_upload_dir()
	 * @uses   is_child_theme()
	 * @param  array  $path An index array containing the file paths to be searched.
	 *
	 * @return array
	 */
	public function templatePaths( $path ) {

		$template_directory = trailingslashit( 'connections-templates' );

		$upload_dir = wp_upload_dir();

		// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
		if ( is_child_theme() ) {

			$path[5] = trailingslashit( get_stylesheet_directory() ) . $template_directory . trailingslashit( $this->slug );
		}

		$path[20]  = trailingslashit( get_template_directory() ) . $template_directory . trailingslashit( $this->slug );
		$path[30]  = trailingslashit( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'connections-templates' . DIRECTORY_SEPARATOR . $this->slug );
		$path[40]  = trailingslashit( $upload_dir['basedir'] ) . $template_directory . trailingslashit( $this->slug );
		$path[80]  = CN_CUSTOM_TEMPLATE_PATH . trailingslashit( $this->slug );
		$path[100] = $this->getPath();

		return $path;
	}

	/**
	 * Include the template functions.php file if present.
	 *
	 * NOTE: This has to be included within the class because legacy templates
	 * `functions.php` needs to be included within scope of $this.
	 *
	 * @access private
	 * @since  0.7.6
	 *
	 * @return void
	 */
	public function includeFunctions() {

		// var_dump( $this->path . $this->functions );
		include_once( $this->path . $this->functions );
	}

	/**
	 * Loads the CSS file while replacing %%PATH%% with the URL
	 * to the template.
	 *
	 * @access private
	 * @since  0.7.6
	 *
	 * @return string
	 */
	public function printCSS() {

		$out     = '';
		$search  = array( "\r\n", "\r", "\n", "\t", '%%PATH%%' );
		$replace = array( ' ', ' ', ' ', ' ', $this->getURL() );

		/**
		 * @TODO Create a page pre-process function so the CSS outputs only once in the page head.
		 */

		// Loads the CSS style in the body, valid HTML5 when set with the 'scoped' attribute.
		// However, if the sever is running the pagespeed mod, the scoped setting will cause the CSS
		// not to be applied because it is moved to the page head where it belongs.
		$out .= '<style type="text/css">';
		/** @noinspection PhpUsageOfSilenceOperatorInspection */
		$out .= str_replace( $search, $replace, @file_get_contents( $this->parts['css-path'] ) );
		$out .= '</style>';

		echo trim( $out ) . PHP_EOL;
	}

	/**
	 * Enqueue the template CSS file.
	 *
	 * @access private
	 * @since  0.8
	 * @uses   apply_filters()
	 * @uses   wp_enqueue_style()
	 *
	 * @return void
	 */
	public function enqueueCSS() {

		// Ensure the core CSS is added as a required CSS file when enqueueing the template's CSS.
		$required = array( 'cn-public' );
		$required = apply_filters( 'cn_template_required_css-' . $this->slug, $required, $this );
		$handle   = "cnt-{$this->slug}";
		$url      = cnURL::makeProtocolRelative( $this->parts['css-url'] );

		wp_enqueue_style( $handle, $url, $required, $this->version );

		if ( isset( $this->parts['css-custom-url'] ) ) {

			$customURL = cnURL::makeProtocolRelative( $this->parts['css-custom-url'] );

			wp_enqueue_style( "cnt-{$this->slug}-custom", $customURL, array( "cnt-{$this->slug}" ), $this->version );
		}

		/**
		 * Runs after the template's CSS and custom CSS have been enqueued.
		 *
		 * The variable part of the hook name is the template's slug.
		 *
		 * @since 8.4
		 *
		 * @param string $handle   The template's registered CSS handle.
		 * @param array  $required The template's registered dependencies.
		 */
		do_action( 'cn_template_enqueue_css-' . $this->slug, $handle, $required );
	}

	/**
	 * Enqueues the template's JS in the theme's footer.
	 *
	 * @access private
	 * @since  0.7.6
	 * @return void
	 */
	public function enqueueScript() {

		$required = apply_filters( 'cn_template_required_js-' . $this->slug, array(), $this );
		$url      = cnURL::makeProtocolRelative( $this->parts['js-url'] );

		wp_enqueue_script( "cnt-{$this->slug}", $url, $required, $this->version, TRUE );
	}

}
