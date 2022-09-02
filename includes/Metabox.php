<?php
/**
 * Abstract Metabox Class.
 *
 * @since      10.4.27
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory;

use cnEntry;

/**
 * Class Metabox
 *
 * @package Connections_Directory
 */
abstract class Metabox {

	/**
	 * The properties used to register the metabox with WordPress using {@see add_meta_box()}.
	 * The properties are passed as a callback parameter in {@see do_meta_boxes()}.
	 *
	 * @since 10.4.27
	 * @var array
	 */
	protected $callbackProperties = array();

	/**
	 * The context within the screen where the box should display.
	 *
	 * Valid values are 'normal', 'side', and 'advanced'.
	 *
	 * @since 10.4.27
	 * @var string
	 */
	protected $context = 'side';

	/**
	 * An instance of the cnEntry object.
	 *
	 * @since 10.4.27
	 * @var cnEntry
	 */
	protected $entry;

	/**
	 * Metabox key.
	 *
	 * Interestingly if either 'submitdiv' or 'linksubmitdiv' are used as
	 * the 'id' in the {@see add_meta_box()} function it will show up as a metabox
	 * that can not be hidden when the Screen Options tab is output via the
	 * {@see meta_box_prefs()} function.
	 *
	 * @since 10.4.27
	 * @var string
	 */
	protected $id;

	/**
	 * The page hooks that a metabox should render on.
	 *
	 * @since 10.4.27
	 * @var string[]
	 */
	protected $pages;

	/**
	 * The metabox priority.
	 *
	 * Valid values are 'high', 'core', 'default', 'low'.
	 *
	 * @since 10.4.27
	 * @var string
	 */
	protected $priority = 'core';

	/**
	 * The attributes passed from the shortcode.
	 *
	 * @since 10.4.27
	 * @var array
	 */
	protected $shortcodeAttributes = array();

	/**
	 * Metabox constructor.
	 *
	 * @since 10.4.27
	 */
	public function __construct() {}

	/**
	 * Get the metabox slug.
	 *
	 * @since 10.4.27
	 *
	 * @return string
	 */
	final public function getId() {

		return $this->id;
	}

	/**
	 * The default page hooks that a metabox should render on. The `public` page hook is the site's frontend.
	 *
	 * These are admin page hooks returned by @see add_submenu_page() when registering the admin pages.
	 * {@see cnAdminMenu::menu()}
	 *
	 * @since 10.4.27
	 *
	 * @return string[]
	 */
	final public static function getPageHooks() {

		if ( is_admin() ) {

			$pageHooks = apply_filters(
				'Connections_Directory/Metabox/Page_Hooks',
				array(
					'connections_page_connections_add',
					'connections_page_connections_manage',
				)
			);

			// Define the core pages and use them by default if no page where defined.
			// Check if doing AJAX because the page hooks are not defined when doing an AJAX request which cause undefined property errors.
			$pages = defined( 'DOING_AJAX' ) && DOING_AJAX ? array() : $pageHooks;

		} else {

			$pages = array( 'public' );
		}

		return $pages;
	}

	/**
	 * Get the metabox HTML.
	 *
	 * @since 10.4.27
	 *
	 * @return string
	 */
	abstract public function getHTML();

	/**
	 * Get the metabox title.
	 *
	 * @since 10.4.27
	 * @return string
	 */
	abstract public function getTitle();

	/**
	 * Echo metabox HTML.
	 *
	 * @since 10.4.27
	 */
	final public function render() {

		echo $this->getHTML(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Callback to render the "Publish" metabox.
	 *
	 * @since 10.4.27
	 *
	 * @param cnEntry $entry   An instance of the cnEntry object.
	 * @param array   $metabox The metabox attributes array set in self::register().
	 * @param array   $atts    The attributes passed from the shortcode.
	 */
	final public function renderCallback( $entry, $metabox, $atts = array() ) {

		$this->entry               = $entry;
		$this->callbackProperties  = $metabox;
		$this->shortcodeAttributes = $atts;

		$this->render();
	}

	/**
	 * Get the metabox HTML.
	 *
	 * @since 10.4.27
	 *
	 * @return string
	 */
	final public function __toString() {

		return $this->getHTML();
	}
}
