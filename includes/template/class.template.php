<?php

/**
 * Class for working with a template onject.
 *
 * @package     Connections
 * @subpackage  Template
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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


	/**
	 * Setup the template object.
	 *
	 * @access private
	 * @since 0.7.6
	 * @param (object) $atts
	 */
	public function __construct( $atts ) {

		$this->name = $atts->name;
		$this->slug = $atts->slug;
		$this->type = $atts->type;
		$this->version = $atts->version;
		$this->author = $atts->author;
		$this->authorURL = $atts->authorURL;
		$this->description = $atts->description;
		$this->custom = $atts->custom;
		$this->legacy = $atts->legacy;
		$this->path = $atts->path;
		$this->url = $atts->url;
		$this->thumbnail = $atts->thumbnail;
		$this->functions = $atts->functions;

		$this->parts = $atts->parts;

		// Add action that registers the template parts.
		// The do_action() will be run connectionsList() after cnTemplateFactory returns the cnTemplate object.
		if ( $this->legacy ) add_action( 'cn_register_legacy_template_parts', array( $this, 'registerLegacyParts' ) );

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
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getAuthorURL() {

		// if the http protocol is not part of the url, add it.
		$this->authorURL = ( ! empty( $this->authorURL ) && preg_match( "/https?/" , $this->authorURL ) == 0 ) ? 'http://' . $this->authorURL : '';

		return $this->authorURL;
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
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get the template base URL.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Get the template thumnail file name.
	 *
	 * @access public
	 * @since 0.7.6
	 * @return (string)
	 */
	public function getThumbnail() {
		$thumbnail = array();

		$thumbnail['name'] = $this->thumbnail;
		$thumbnail['url'] = $this->url . $this->thumbnail;

		return $thumbnail;
	}

	/**
	 * Register a template part.
	 *
	 * @access public
	 * @since 0.7.6
	 * @param $atts (array)
	 * @return (void)
	 */
	public function part( $atts = array() ) {

		// Permitted part types:
		// 		list_before
		// 		list_after
		// 		list_both
		// 		card_before
		// 		card_after
		// 		card_single_before
		// 		card_single_after
		// 		card
		// 		card_single
		// 		functions [for legacy template support only]

		$defaults = array(
			'part' => '',
			'type' => 'file',	// type can be file|action (string) for action
			'path' => NULL,		// the file path to be use to display the list body. ! required if type is file !
			'callback' => '',	// the callback name (string)|array [if callback is method]. ! required if type is action !
			);

		$atts = wp_parse_args( $atts, $defaults );

		switch ( $atts['type'] ) {
			case 'action':
				if ( ! has_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug ) )
					add_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug , $atts['callback'], 10, 6 );

				break;

			case 'file':
				// Maybe we should check if file exists first and if it doesn't register action to display missing file message.
				if ( ! has_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug ) )
					add_action( 'cn_action_' . $atts['tag'] . '-' . $this->slug , create_function( '$entry, $content, $template, $atts, $connections, $vCard', 'include(\'' . $atts['path'] . '\');' ), 10, 6 );

				break;
		}
	}

	/*
	 * These methods are only to support legacy templates by registering their
	 * templates parts via the cnTemplate::part()
	 */

	/**
	 * Register legacy template parts.
	 *
	 * @access private
	 * @since 0.7.6
	 * @return (void)
	 */
	public function registerLegacyParts() {

		if ( ! empty( $this->functions ) )
			$this->part( array( 'tag' => 'include_once', 'type' => 'action', 'callback' => array( $this, 'includeFunctions' ) ) );

		if ( ! empty( $this->parts['card'] ) )
			$this->part( array( 'tag' => 'card', 'type' => 'file', 'path' => $this->path . $this->parts['card'] ) );

		if ( ! empty( $this->parts['css'] ) )
			$this->part( array( 'tag' => 'css', 'type' => 'action', 'callback' => array( $this, 'printCSS' ) ) );

		if ( ! empty( $this->parts['js'] ) )
			$this->part( array( 'tag' => 'js', 'type' => 'action', 'callback' => array( $this, 'enqueueScript' ) ) );

	}

	/**
	 * Include the template functions.php file if present.
	 * NOTE: This has to be included within the class because legacy templates functions.php needs to be included within scope of $this.
	 *
	 * @access private
	 * @since 0.7.6
	 * @return (void)
	 */
	public function includeFunctions() {
		include_once( $this->path . $this->functions );
	}

	/**
	 * Loads the CSS file while replacing %%PATH%% with the URL
	 * to the template.
	 *
	 * @access private
	 * @since 0.7.6
	 * @return string
	 */
	public function printCSS() {
		$out = '';
		$search = array( "\r\n", "\r", "\n", "\t", '%%PATH%%' );
		$replace = array( ' ', ' ', ' ', ' ', $this->url );

		/**
		 * @TODO Create a page pre-process function so the CSS outputs only once in the page head.
		 */

		// Loads the CSS style in the body, valid HTML5 when set with the 'scoped' attribute.
		// However, if the sever is running the pagespeed mod, the scoped setting will cause the CSS
		// not to be applied because it is moved to the page head where it belongs.
		$out .= '<style type="text/css">';
		$out .= str_replace( $search , $replace , @file_get_contents( $this->path . $this->parts['css'] ) );
		$out .= '</style>';

		echo trim( $out ) . "\n";
	}

	/**
	 * Prints the template's JS in the theme's footer.
	 *
	 * @access private
	 * @since 0.7.6
	 * @return (void)
	 */
	public function enqueueScript() {

		// Prints the javascript tag in the footer if $template->js path is set
		wp_enqueue_script( "cn_{$this->slug}_js" , $this->url . 'template.js', array(), $this->version, TRUE );
	}

}