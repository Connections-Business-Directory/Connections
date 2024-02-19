<?php

namespace Connections_Directory;

use cnCSV_Batch_Export_All;
use cnEntry;
use cnMetaboxAPI;
use cnRewrite;
use cnTerm;
use Connections_Directory\Taxonomy\Term;
use Connections_Directory\Taxonomy\Widget;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_parse;
use WP;
use WP_Error;
use WP_Post_Type;
use WP_User;

/**
 * Class Taxonomy
 *
 * @package Connections_Directory\Taxonomy
 */
final class Taxonomy {

	/**
	 * Taxonomy key.
	 *
	 * @since 10.2
	 * @var string
	 */
	protected $name;

	/**
	 * Name of the taxonomy shown in the menu. Usually plural.
	 *
	 * @since 10.2
	 * @var string
	 */
	protected $label;

	/**
	 * Labels object for this taxonomy.
	 *
	 * If not set, tag labels are inherited for non-hierarchical types
	 * and category labels for hierarchical ones.
	 *
	 * @since 10.2
	 * @see   get_taxonomy_labels()
	 *
	 * @var _Labels
	 */
	protected $labels;

	/**
	 * A short descriptive summary of what the taxonomy is for.
	 *
	 * @since 10.2
	 * @var string
	 */
	protected $description = '';

	/**
	 * Whether a taxonomy is intended for use publicly either via the admin interface or by front-end users.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $public = true;

	/**
	 * Whether the taxonomy is publicly queryable.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $publicly_queryable = true;

	/**
	 * Whether the taxonomy is hierarchical.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $hierarchical = false;

	/**
	 * Whether to display a column for the taxonomy on the manage admin page.
	 *
	 * @since 10.4.40
	 * @var bool
	 */
	protected $show_admin_column = false;

	/**
	 * Whether to generate and allow a UI for managing terms in this taxonomy in the admin.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $show_ui = true;

	/**
	 * Whether to show the taxonomy in the admin menu.
	 *
	 * If true, the taxonomy is shown as a submenu of the object type menu. If false, no menu is shown.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $show_in_menu = true;

	///**
	// * Whether the taxonomy is available for selection in navigation menus.
	// *
	// * @since 10.2
	// * @var bool
	// */
	//protected $show_in_nav_menus = true;

	///**
	// * Whether to list the taxonomy in the tag cloud widget controls.
	// *
	// * @since 10.2
	// * @var bool
	// */
	//protected $show_tagcloud = true;

	///**
	// * Whether to show the taxonomy in the quick/bulk edit panel.
	// *
	// * @since 10.2
	// * @var bool
	// */
	//protected $show_in_quick_edit = true;

	///**
	// * Whether to display a column for the taxonomy on its post type listing screens.
	// *
	// * @since 10.2
	// * @var bool
	// */
	//protected $show_admin_column = false;

	/**
	 * Whether to register a Content Block for the taxonomy.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $register_content_block = true;

	/**
	 * Whether to register a widget for the taxonomy.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $register_widget = true;

	/**
	 * The Content Block properties.
	 *
	 * @see Content_Block::__construct()
	 *
	 * @since 10.2
	 * @var array
	 */
	protected $content_block_props = array();

	/**
	 * The Content Block properties.
	 *
	 * @see Content_Blocks\Entry\Taxonomy::__construct()
	 *
	 * @since 10.2
	 * @var array
	 */
	protected $content_block_render_props = array();

	/**
	 * The callback function for the meta box display.
	 *
	 * @since 10.2
	 * @var bool|callable
	 */
	protected $meta_box_cb = null;

	/**
	 * The callback function for sanitizing taxonomy data saved from a meta box.
	 *
	 * @since 10.2
	 * @var callable
	 */
	protected $meta_box_sanitize_cb = null;

	///**
	// * An array of object types this taxonomy is registered for.
	// *
	// * @since 10.2
	// * @var array
	// */
	//protected $object_type = null;

	/**
	 * Capabilities for this taxonomy.
	 *
	 * @since 10.2
	 * @var _Capabilities
	 */
	protected $cap;

	/**
	 * Rewrites information for this taxonomy.
	 *
	 * @since 10.2
	 * @var array|false
	 */
	protected $rewrite;

	/**
	 * Query var string for this taxonomy.
	 *
	 * @since 10.2
	 * @var string|false
	 */
	protected $query_var;

	/**
	 * Function that will be called when the count is updated.
	 *
	 * @since 10.2
	 * @var callable
	 */
	protected $update_count_callback;

	/**
	 * Whether this taxonomy should appear in the REST API.
	 *
	 * Default false. If true, standard endpoints will be registered with
	 * respect to $rest_base and $rest_controller_class.
	 *
	 * @since 10.2
	 * @var bool $show_in_rest
	 */
	protected $show_in_rest;

	/**
	 * The base path for this taxonomy's REST API endpoints.
	 *
	 * @since 10.2
	 * @var string|bool $rest_base
	 */
	protected $rest_base;

	/**
	 * The controller for this taxonomy's REST API endpoints.
	 *
	 * Custom controllers must extend WP_REST_Controller.
	 *
	 * @since 10.2
	 * @var string|bool $rest_controller_class
	 */
	protected $rest_controller_class;

	/**
	 * The default term name for this taxonomy. If you pass an array you have
	 * to set 'name' and optionally 'slug' and 'description'.
	 *
	 * @since 10.2
	 * @var array|string
	 */
	protected $default_term;

	/**
	 * The controller instance for this taxonomy's REST API endpoints.
	 *
	 * Lazily computed. Should be accessed using {@see WP_Taxonomy::get_rest_controller()}.
	 *
	 * @since 10.2
	 * @var WP_REST_Controller $rest_controller
	 */
	protected $rest_controller;

	/**
	 * Whether it is a built-in taxonomy.
	 *
	 * @since 10.2
	 * @var bool
	 */
	protected $_builtin;

	/**
	 * Constructor.
	 *
	 * @since 10.2
	 *
	 * @param string       $taxonomy    Taxonomy slug, must not exceed 32 characters.
	 * @param array|string $args        Optional. Array or query string of arguments for registering a taxonomy.
	 *                                  Default empty array.
	 */
	public function __construct( $taxonomy, $args = array() ) {

		$this->name = $taxonomy;

		/*
		 * @todo add the following properties:
		 * - allow_split :: only for hierarchical taxonomies. See the Split Categories addon.
		 * - show_split_ui
		 * - Convert between taxonomies.
		 */

		$this->setProperties( $args );

		$this->add_rewrite_rules();
		$this->addHooks();
		$this->addDefaultTerm();
	}

	/**
	 * Called when unregistering a taxonomy.
	 *
	 * @since 10.2
	 */
	public function _destruct() {

		$this->remove_rewrite_rules();
		$this->removeHooks();
		$this->removeDefaultTerm();
	}

	///**
	// * @since 10.2
	// *
	// * @param string $property
	// *
	// * @throws Exception
	// * @return mixed
	// */
	//public function getProperty( $property ) {
	//
	//	if ( property_exists( $this, $property ) ) {
	//
	//		return $this->{$property};
	//	}
	//
	//	throw new Exception( 'Undefined object property ' . __CLASS__ . '::' . $property );
	//}

	/**
	 * Sets taxonomy properties.
	 *
	 * Based on {@see \WP_Taxonomy::set_props()}.
	 *
	 * @since 10.2
	 *
	 * @param array|string $args Array or query string of arguments for registering a taxonomy.
	 */
	private function setProperties( $args ) {

		$args = wp_parse_args( $args );

		/**
		 * Filters the arguments for registering a taxonomy.
		 *
		 * @since 10.2
		 *
		 * @param array  $args     Array of arguments for registering a taxonomy.
		 * @param string $taxonomy Taxonomy slug.
		 */
		$args = apply_filters( 'Connections_Directory/Taxonomy/Set_Properties', $args, $this->name );

		$defaults = array(
			'labels'                     => array(),
			'description'                => '',
			'public'                     => true,
			'publicly_queryable'         => null,
			'hierarchical'               => false,
			'show_ui'                    => null,
			'show_in_menu'               => null,
			// 'show_in_nav_menus'           => null,
			// 'show_tagcloud'               => null,
			// 'show_in_quick_edit'          => null,
			// 'show_admin_column'           => false,
			'register_content_block'     => true,
			'register_widget'            => true,
			'content_block_props'        => array(),
			'content_block_render_props' => array(),
			'meta_box_cb'                => null,
			'meta_box_sanitize_cb'       => null,
			'capabilities'               => array(),
			'rewrite'                    => true,
			'query_var'                  => $this->name,
			'update_count_callback'      => '',
			'show_in_rest'               => false,
			'rest_base'                  => false,
			'rest_controller_class'      => false,
			'default_term'               => null,
			'_builtin'                   => false,
		);

		$args = array_merge( $defaults, $args );

		// If not set, default to the setting for 'public'.
		if ( null === $args['publicly_queryable'] ) {
			$args['publicly_queryable'] = $args['public'];
		}

		if ( false !== $args['query_var'] && ( is_admin() || false !== $args['publicly_queryable'] ) ) {
			if ( true === $args['query_var'] ) {
				$args['query_var'] = $this->name;
			} else {
				$args['query_var'] = sanitize_title_with_dashes( $args['query_var'] );
			}
		} else {
			// Force 'query_var' to false for non-public taxonomies.
			$args['query_var'] = false;
		}

		if ( false !== $args['rewrite'] && ( is_admin() || get_option( 'permalink_structure' ) ) ) {
			$args['rewrite'] = wp_parse_args(
				$args['rewrite'],
				array(
					'with_front'   => false,
					'hierarchical' => false,
					'ep_mask'      => EP_NONE,
					'paged'        => false,
					'feed'         => false,
					'forcomments'  => false,
					'walk_dirs'    => false,
					'endpoints'    => false,
				)
			);

			if ( empty( $args['rewrite']['slug'] ) ) {
				$args['rewrite']['slug'] = sanitize_title_with_dashes( $this->name );
			}
		}

		// If not set, default to the setting for 'public'.
		if ( null === $args['show_ui'] ) {
			$args['show_ui'] = $args['public'];
		}

		// If not set, default to the setting for 'show_ui'.
		if ( null === $args['show_in_menu'] || ! $args['show_ui'] ) {
			$args['show_in_menu'] = $args['show_ui'];
		}

		// @todo Add support for the `show_in_nav_menus` parameter.
		//// If not set, default to the setting for 'public'.
		//if ( null === $args['show_in_nav_menus'] ) {
		//	$args['show_in_nav_menus'] = $args['public'];
		//}

		// @todo Add support for the `show_tagcloud` parameter.
		//// If not set, default to the setting for 'show_ui'.
		//if ( null === $args['show_tagcloud'] ) {
		//	$args['show_tagcloud'] = $args['show_ui'];
		//}

		// @todo Add support for the `show_in_quick_edit` parameter.
		//// If not set, default to the setting for 'show_ui'.
		//if ( null === $args['show_in_quick_edit'] ) {
		//	$args['show_in_quick_edit'] = $args['show_ui'];
		//}

		$default_caps = $this->getDefaultCapabilities();

		$args['cap'] = (object) array_merge( $default_caps, $args['capabilities'] );
		unset( $args['capabilities'] );

		//$args['object_type'] = array_unique( (array) $object_type );

		// If not set, default to the setting for 'public'.
		if ( null === $args['register_content_block'] ) {
			$args['register_content_block'] = $args['public'];
		}

		// If not set, default to the setting for 'public'.
		if ( null === $args['register_widget'] ) {
			$args['register_widget'] = $args['public'];
		}

		// If not set, use the default meta box.
		if ( null === $args['meta_box_cb'] ) {

			$args['meta_box_cb'] = array( $this, 'renderMetabox' );
		}

		$args['name'] = $this->name;

		// Default meta box sanitization callback depends on the value of 'meta_box_cb'.
		if ( null === $args['meta_box_sanitize_cb'] ) {

			$args['meta_box_sanitize_cb'] = array( $this, 'sanitizeTerms' );
		}

		// Default taxonomy term.
		if ( ! empty( $args['default_term'] ) ) {
			if ( ! is_array( $args['default_term'] ) ) {
				$args['default_term'] = array( 'name' => $args['default_term'] );
			}
			$args['default_term'] = wp_parse_args(
				$args['default_term'],
				array(
					'name'        => '',
					'slug'        => '',
					'description' => '',
				)
			);
		}

		foreach ( $args as $property_name => $property_value ) {
			$this->$property_name = $property_value;
		}

		$this->setLabels( $args['labels'] );
		$this->label = $this->labels->name;
	}

	/**
	 * Adds the necessary rewrite rules for the taxonomy.
	 *
	 * Based on:
	 * @see \WP_Taxonomy::add_rewrite_rules()
	 *
	 * @since 10.2
	 *
	 * @global WP $wp Current WordPress environment instance.
	 */
	protected function add_rewrite_rules() {

		global $wp;

		// Non-publicly queryable taxonomies should not register query vars, except in the admin.
		if ( false !== $this->query_var && $wp ) {
			$wp->add_query_var( $this->query_var );
		}

		if ( false !== $this->rewrite && ( is_admin() || get_option( 'permalink_structure' ) ) ) {

			$namespace = cnRewrite::$namespace;

			$tag   = "%{$namespace}_{$this->name}%";
			$query = $this->query_var ? "{$this->query_var}=" : "taxonomy={$this->name}&term="; // @todo The "taxonomy={$this->name}&term=" is not likely needed.

			if ( $this->hierarchical && $this->rewrite['hierarchical'] ) {
				$regex = '(.+?)';
			} else {
				$regex = '([^/]+)';
			}

			add_rewrite_tag( $tag, $regex, $query );
			// add_permastruct( $name, "%pagename%/{$this->rewrite['slug']}/%{$name}%/country/%Connections_Directory\country%", $this->rewrite );

			// Add filter that add the rewrite rules.
			add_filter( 'Connections_Directory/Rewrite/Root_Rules/Taxonomy', array( $this, 'addRootRewriteRules' ), 10, 2 );
			add_filter( 'Connections_Directory/Rewrite/Page_Rules/Taxonomy', array( $this, 'addPageRewriteRules' ) );
			add_filter( 'Connections_Directory/Rewrite/CPT_Rules/Taxonomy', array( $this, 'addCPTRewriteRules' ), 10, 2 );
			add_filter( 'Connections_Directory/Rewrite/Permalink_Slugs', array( $this, 'addRewriteSlug' ) );

			// Register the settings field for th permalink slug.
			add_filter( 'cn_register_settings_fields', array( $this, 'registerSettingsFields' ), 10, 1 );
		}
	}

	/**
	 * Removes any rewrite rules, permastructs, and rules for the taxonomy.
	 *
	 * Based on:
	 * @see \WP_Taxonomy::remove_rewrite_rules()
	 *
	 * @since 10.2
	 *
	 * @global WP $wp Current WordPress environment instance.
	 */
	public function remove_rewrite_rules() {

		global $wp;

		// Remove query var.
		if ( false !== $this->query_var ) {
			$wp->remove_query_var( $this->query_var );
		}

		$namespace = cnRewrite::$namespace;
		$tag       = "%{$namespace}_{$this->name}%";

		// Remove rewrite tags and permastructs.
		if ( false !== $this->rewrite ) {

			remove_rewrite_tag( $tag );
			// remove_permastruct( $name );

			remove_filter( 'Connections_Directory/Rewrite/Root_Rules/Taxonomy', array( $this, 'addRootRewriteRules' ) );
			remove_filter( 'Connections_Directory/Rewrite/Page_Rules/Taxonomy', array( $this, 'addPageRewriteRules' ) );
			remove_filter( 'Connections_Directory/Rewrite/CPT_Rules/Taxonomy', array( $this, 'addCPTRewriteRules' ) );
			remove_filter( 'Connections_Directory/Rewrite/Permalink_Slugs', array( $this, 'addRewriteSlug' ) );
			remove_filter( 'cn_register_settings_fields', array( $this, 'registerSettingsFields' ) );
		}
	}

	/**
	 * Add hooks required to support the registered taxonomy.
	 *
	 * @since 10.2
	 */
	protected function addHooks() {

		// Register on priority `1` to match the priority that all other `core` metaboxes are registered on.
		add_action( 'cn_metabox', array( $this, 'registerMetabox' ), 1 );

		// Register the admin menu item.
		add_filter( 'cn_submenu', array( $this, 'registerAdminMenu' ) );

		// Sanitize terms.
		add_filter( "Connections_Directory/Taxonomy/{$this->name}/Sanitize_Terms", $this->meta_box_sanitize_cb );

		// Attach taxonomy terms to an Entry.
		add_action( "Connections_Directory/Taxonomy/{$this->name}/Attach_Terms", array( $this, 'attachTerms' ), 10, 2 );

		// Map capabilities to core default capabilities.
		//add_filter( 'map_meta_cap', array( $this, 'mapCapabilities' ), 10, 4 );
		add_filter( 'user_has_cap', array( $this, 'userHasCapability' ), 10, 4 );

		///**
		// * Register the taxonomy Content Block.
		// *
		// * Register on priority `18` because the "core" Content Blocks are registered at priority `19` and
		// * they must be registered before priority `20`.
		// *
		// * @see Content_Blocks::instance()
		// */
		//add_action( 'init', array( $this, 'registerContentBlock' ), 18 );
		add_action( 'Connections_Directory/Content_Blocks/Register', array( $this, 'registerContentBlock' ) );

		// Register the taxonomy widget.
		add_action( 'widgets_init', array( $this, 'registerWidget' ) );

		// Add support for CSV Export.
		add_filter( 'cn_csv_export_fields_config', array( $this, 'exportConfiguration' ) );
		add_filter( 'cn_csv_export_fields', array( $this, 'registerCSVHeader' ) );
		add_filter( "cn_export_header-taxonomy-{$this->getSlug()}", array( $this, 'exportHeader' ), 10, 3 );
		add_filter( "cn_export_field-taxonomy-{$this->getSlug()}", array( $this, 'exportTerms' ), 10, 4 );

		// Add support for CSV Import.
		add_filter( 'cncsv_map_import_fields', array( $this, 'registerCSVHeader' ) );
		add_action( 'cncsv_import_fields', array( $this, 'importTerms' ), 10, 3 );

		// Permalink
		add_filter(
			"Connections_Directory/Taxonomy/{$this->getSlug()}/Term/Permalink",
			array( $this, 'permalink' ),
			10,
			2
		);
	}

	/**
	 * Remove the registered hooks.
	 *
	 * @see Taxonomy::addHooks()
	 *
	 * @since 10.2
	 */
	protected function removeHooks() {

		remove_action( 'cn_metabox', array( $this, 'registerMetabox' ), 1 );
		remove_filter( 'cn_submenu', array( $this, 'registerAdminMenu' ) );
		remove_filter( "Connections_Directory/Taxonomy/{$this->name}/Sanitize_Terms", $this->meta_box_sanitize_cb );
		remove_action( "Connections_Directory/Attach/Taxonomy/{$this->name}", array( $this, 'attachTerms' ) );
		// remove_filter( 'map_meta_cap', array( $this, 'mapCapabilities' ) );
		remove_filter( 'user_has_cap', array( $this, 'userHasCapability' ) );
		remove_action( 'init', array( $this, 'registerContentBlock' ), 18 );
		remove_action( 'Connections_Directory/Content_Blocks/Register', array( $this, 'registerContentBlock' ) );
		remove_action( 'widgets_init', array( $this, 'registerWidget' ) );

		remove_filter( 'cn_csv_export_fields_config', array( $this, 'exportConfiguration' ) );
		remove_filter( 'cn_csv_export_fields', array( $this, 'registerCSVHeader' ) );
		remove_filter( "cn_export_header-taxonomy-{$this->getSlug()}", array( $this, 'exportHeader' ) );
		remove_filter( "cn_export_field-taxonomy-{$this->getSlug()}", array( $this, 'exportTerms' ) );
		remove_filter( 'cncsv_map_import_fields', array( $this, 'registerCSVHeader' ) );
		remove_action( 'cncsv_import_fields', array( $this, 'importTerms' ) );

		remove_filter(
			"Connections_Directory/Taxonomy/{$this->getSlug()}/Term/Permalink",
			array( $this, 'permalink' )
		);
	}

	/**
	 * Callback for the `Connections_Directory/Rewrite/Taxonomy/Root_Rules` filter.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param array $rules
	 * @param int   $pageID
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function addRootRewriteRules( $rules, $pageID ) {

		$namespace = cnRewrite::$namespace;

		$token = "%{$namespace}_{$this->name}%";
		$slug  = $this->rewrite['slug'];

		$addQuery = array( 'cn-view' => 'card' );
		$args     = $this->rewrite;

		return array_merge(
			$rules,
			cnRewrite::generateRule( "{$slug}/{$token}/%country%/%region%/%postal_code%/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%country%/%region%/%postal_code%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%country%/%region%/%locality%/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%country%/%region%/%locality%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%country%/%region%/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%country%/%region%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%country%/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%country%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%region%/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%region%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%locality%/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%locality%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%postal_code%/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/%postal_code%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}/pg/%page%", $args, $addQuery, true ),
			cnRewrite::generateRule( "{$slug}/{$token}", $args, $addQuery, true )
		);
	}

	/**
	 * Callback for the `Connections_Directory/Rewrite/Taxonomy/Page_Rules` filter.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param array $rules
	 *
	 * @return array
	 */
	public function addPageRewriteRules( $rules ) {

		$namespace = cnRewrite::$namespace;

		$token = "%{$namespace}_{$this->name}%";
		$slug  = $this->rewrite['slug'];

		$addQuery = array( 'cn-view' => 'card' );
		$args     = $this->rewrite;

		return array_merge(
			$rules,
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%/%region%/%postal_code%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%/%region%/%postal_code%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%/%region%/%locality%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%/%region%/%locality%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%/%region%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%/%region%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%country%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%region%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%region%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%locality%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%locality%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%postal_code%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/%postal_code%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "%pagename%/{$slug}/{$token}", $args, $addQuery )
		);
	}

	/**
	 * Callback for the `Connections_Directory/Rewrite/CPT_Rules/Taxonomy` filter.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param array        $rules
	 * @param WP_Post_Type $post
	 *
	 * @return array
	 */
	public function addCPTRewriteRules( $rules, $post ) {

		if ( ! $post instanceof WP_Post_Type ) {

			return $rules;
		}

		$namespace = cnRewrite::$namespace;

		$postSlug  = $post->rewrite['slug'];
		$postToken = "%{$namespace}CPT_{$post->name}%";

		$slug  = $this->rewrite['slug'];
		$token = "%{$namespace}_{$this->name}%";

		$addQuery = array( 'cn-view' => 'card' );
		$args     = $this->rewrite;

		return array_merge(
			$rules,
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%/%region%/%postal_code%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%/%region%/%postal_code%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%/%region%/%locality%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%/%region%/%locality%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%/%region%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%/%region%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%country%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%region%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%region%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%locality%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%locality%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%postal_code%/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/%postal_code%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}/pg/%page%", $args, $addQuery ),
			cnRewrite::generateRule( "{$postSlug}/{$postToken}/{$slug}/{$token}", $args, $addQuery )
		);
	}

	/**
	 * Callback for the `Connections_Directory/Rewrite/Permalink_Slugs` filter.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param array $slugs
	 *
	 * @return array
	 */
	public function addRewriteSlug( $slugs ) {

		$options = get_option( 'connections_permalink', array() );

		if ( ! is_array( $options ) ) {

			$options = array();
		}

		$slug = _array::get( $options, "{$this->getSlug()}_base", $this->rewrite['slug'] );

		_array::set( $slugs, $this->getSlug(), $slug );

		return $slugs;
	}

	/**
	 * @since 10.2
	 *
	 * @return string[]
	 */
	protected function getDefaultCapabilities() {

		return array(
			'manage_terms' => "connections_manage_{$this->name}",
			'edit_terms'   => "connections_edit_{$this->name}",
			'delete_terms' => "connections_delete_{$this->name}",
			'assign_terms' => "connections_assign_{$this->name}",
		);
	}

	/**
	 * Get the taxonomy capabilities settings.
	 *
	 * @since 10.2
	 *
	 * @return _Capabilities
	 */
	public function getCapabilities() {

		return $this->cap;
	}

	///**
	// * @since 10.2
	// *
	// * @param string[] $caps    Array of the user's capabilities.
	// * @param string   $cap     Capability name.
	// * @param int      $user_id The user ID.
	// * @param array    $args    Adds the context to the cap. Typically the object ID.
	// *
	// * @return string[] Capabilities for meta capability.
	// */
	//public function mapCapabilities( $caps, $cap, $user_id, $args ) {
	//
	//	$default_caps = $this->getDefaultCapabilities();
	//
	//	if ( ! in_array( $cap, $default_caps, true ) ) {
	//
	//		return $caps;
	//	}
	//
	//	/* Set an empty array for the caps. */
	//	$caps = array();
	//
	//	switch ( $cap ) {
	//
	//		case "connections_manage_{$this->name}":
	//		case "connections_edit_{$this->name}":
	//		case "connections_delete_{$this->name}":
	//			$caps[] = 'connections_edit_categories';
	//			break;
	//
	//		case "connections_assign_{$this->name}":
	//			$caps[] = 'connections_add_entry';
	//			$caps[] = 'connections_add_entry_moderated';
	//			$caps[] = 'connections_edit_entry';
	//			$caps[] = 'connections_edit_entry_moderated';
	//			break;
	//
	//		default:
	//			$caps[] = 'do_not_allow';
	//	}
	//
	//	return $caps;
	//}

	/**
	 * Callback for the `user_has_cap` filter.
	 *
	 * Assign default capabilities based on the legacy capabilies for managing categories.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name
	 *                          and boolean values represent whether the user has that capability.
	 * @param string[] $caps    Required primitive capabilities for the requested capability.
	 * @param array    $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type string    $0 Requested capability.
	 *     @type int       $1 Concerned user ID.
	 *     @type mixed  ...$2 Optional second and further parameters, typically object ID.
	 * }
	 *
	 * @param WP_User  $user    The user object.
	 *
	 * @return bool[]
	 * @noinspection SpellCheckingInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function userHasCapability( $allcaps, $caps, $args, $user ) {

		static $applyFilter = true;

		$cap = $args[0];

		$taxonomyCapabilities = array(
			"connections_manage_{$this->name}",
			"connections_edit_{$this->name}",
			"connections_delete_{$this->name}",
			"connections_assign_{$this->name}",
		);

		if ( in_array( $cap, $taxonomyCapabilities, true ) && $applyFilter ) {

			$applyFilter = false;
			$key         = array_search( $cap, $taxonomyCapabilities );

			switch ( $cap ) {

				case "connections_manage_{$this->name}":
				case "connections_edit_{$this->name}":
				case "connections_delete_{$this->name}":
					$allcaps[ $taxonomyCapabilities[ $key ] ] = current_user_can( 'connections_edit_categories' );
					break;

				case "connections_assign_{$this->name}":
					$allcaps[ $taxonomyCapabilities[ $key ] ] = current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) || current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' );
					break;

			}

		} else {

			$applyFilter = true;
		}

		return $allcaps;
	}

	/**
	 * Callback for the `cn_register_settings_fields` filter.
	 *
	 * Registers the permalink slug for the taxonomy with the Settings API.
	 *
	 * @internal
	 * @since  10.2
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function registerSettingsFields( $fields ) {

		$pageHook = 'connections_page_connections_settings';

		$fields[] = array(
			'plugin_id' => 'connections',
			'id'        => "{$this->getSlug()}_base",
			'position'  => 10,
			'page_hook' => $pageHook,
			'tab'       => 'advanced',
			'section'   => 'permalink',
			/* translators: Taxonomy name. */
			'title'     => sprintf( __( '%s Slug', 'connections' ), $this->labels->singular_name ),
			/* translators: Taxonomy name. */
			'desc'      => sprintf( __( 'Enter a permalink slug for the %s in the URL.', 'connections' ), strtolower( $this->labels->singular_name ) ),
			'help'      => '',
			'type'      => 'text',
			'size'      => 'regular',
			'default'   => $this->rewrite['slug'],
			'schema'    => array(
				'type' => 'string',
			),
		);

		return $fields;
	}

	/**
	 * Insert the taxonomy default term and save the term ID in the options table.
	 *
	 * Based on:
	 * @see register_taxonomy()
	 *
	 * @since 10.2
	 */
	protected function addDefaultTerm() {

		if ( ! empty( $this->default_term ) ) {

			$term = cnTerm::exists( $this->default_term['name'], $this->name );

			if ( $term ) {

				update_option( "connections_default_term_{$this->name}", $term['term_id'] );

			} else {

				$term = cnTerm::insert(
					$this->default_term['name'],
					$this->name,
					array(
						'slug'        => sanitize_title( $this->default_term['slug'] ),
						'description' => $this->default_term['description'],
					)
				);

				if ( ! is_wp_error( $term ) ) {

					update_option( "connections_default_term_{$this->name}", $term['term_id'] );
				}
			}
		}
	}

	/**
	 * Remove the default term ID from the options table.
	 *
	 * Based on:
	 * @see unregister_taxonomy()
	 *
	 * @since 10.2
	 */
	protected function removeDefaultTerm() {

		// Remove custom taxonomy default term option.
		if ( ! empty( $this->default_term ) ) {

			delete_option( "connections_default_term_{$this->name}" );
		}
	}

	/**
	 * @since 10.2
	 *
	 * @return _Labels
	 */
	public function getLabels() {

		return $this->labels;
	}

	/**
	 * Set up the taxonomy labels.
	 *
	 * Similar to {@see get_taxonomy_labels()}.
	 *
	 * @since 10.2
	 *
	 * @param array $labels
	 */
	private function setLabels( $labels ) {

		if ( $this->isHierarchical() ) {

			$defaults = array(
				'name'                       => _x( 'Categories', 'taxonomy general name', 'connections' ),
				'singular_name'              => _x( 'Category', 'taxonomy singular name', 'connections' ),
				'search_items'               => __( 'Search Categories', 'connections' ),
				'popular_items'              => '',
				'all_items'                  => __( 'All Categories', 'connections' ),
				'select_items'               => __( 'Select Categories', 'connections' ),
				'parent_item'                => __( 'Parent Category', 'connections' ),
				'parent_item_colon'          => __( 'Parent Category:', 'connections' ),
				'edit_item'                  => __( 'Edit Category', 'connections' ),
				'view_item'                  => __( 'View Category', 'connections' ),
				'update_item'                => __( 'Update Category', 'connections' ),
				'add_new_item'               => __( 'Add New Category', 'connections' ),
				'new_item_name'              => __( 'New Category Name', 'connections' ),
				'separate_items_with_commas' => '',
				'add_or_remove_items'        => '',
				'choose_from_most_used'      => '',
				'not_found'                  => __( 'No categories found.', 'connections' ),
				'no_terms'                   => __( 'No categories', 'connections' ),
				'filter_by_item'             => __( 'Filter by category', 'connections' ),
				'items_list_navigation'      => __( 'Categories list navigation', 'connections' ),
				'items_list'                 => __( 'Categories list', 'connections' ),
				/* translators: Tab heading when selecting from the most used terms. */
				'most_used'                  => _x( 'Most Used', 'categories', 'connections' ),
				'back_to_items'              => __( '&larr; Go to Categories', 'connections' ),
				'content_block_option_name'  => _x( 'Categories', 'taxonomy general name', 'connections' ),
				'content_block_heading'      => _x( 'Categories', 'taxonomy general name', 'connections' ),
				'content_block_label'        => _x( 'Categories', 'taxonomy general name', 'connections' ),
				'content_block_label_colon'  => _x( 'Categories:', 'taxonomy general name with trailing colon', 'connections' ),
				'widget_name'                => _x( 'Entry Categories', 'Name for the widget displayed on the configuration page.', 'connections' ),
				'widget_description'         => _x( 'Entry Categories', 'Description for the widget displayed on the configuration page.', 'connections' ),
			);

		} else {

			$defaults = array(
				'name'                       => _x( 'Tags', 'taxonomy general name', 'connections' ),
				'singular_name'              => _x( 'Tag', 'taxonomy singular name', 'connections' ),
				'search_items'               => __( 'Search Tags', 'connections' ),
				'popular_items'              => __( 'Popular Tags', 'connections' ),
				'all_items'                  => __( 'All Tags', 'connections' ),
				'select_items'               => __( 'Select Tags', 'connections' ),
				'parent_item'                => '',
				'parent_item_colon'          => '',
				'edit_item'                  => __( 'Edit Tag', 'connections' ),
				'view_item'                  => __( 'View Tag', 'connections' ),
				'update_item'                => __( 'Update Tag', 'connections' ),
				'add_new_item'               => __( 'Add New Tag', 'connections' ),
				'new_item_name'              => __( 'New Tag Name', 'connections' ),
				'separate_items_with_commas' => __( 'Separate tags with commas', 'connections' ),
				'add_or_remove_items'        => __( 'Add or remove tags', 'connections' ),
				'choose_from_most_used'      => __( 'Choose from the most used tags', 'connections' ),
				'not_found'                  => __( 'No tags found.', 'connections' ),
				'no_terms'                   => __( 'No tags', 'connections' ),
				'filter_by_item'             => '',
				'items_list_navigation'      => __( 'Tags list navigation', 'connections' ),
				'items_list'                 => __( 'Tags list', 'connections' ),
				/* translators: Tab heading when selecting from the most used terms. */
				'most_used'                  => _x( 'Most Used', 'tags', 'connections' ),
				'back_to_items'              => __( '&larr; Go to Tags', 'connections' ),
				'content_block_option_name'  => _x( 'Tags', 'taxonomy general name', 'connections' ),
				'content_block_heading'      => _x( 'Tags', 'taxonomy general name', 'connections' ),
				'content_block_label'        => _x( 'Tags', 'taxonomy general name', 'connections' ),
				'content_block_label_colon'  => _x( 'Tags:', 'taxonomy general name with trailing colon', 'connections' ),
				'widget_name'                => _x( 'Entry Tags', 'Name for the widget displayed on the configuration page.', 'connections' ),
				'widget_description'         => _x( 'Entry Tags', 'Description for the widget displayed on the configuration page.', 'connections' ),
			);
		}

		/**
		 * This section is similar to {@see _get_custom_object_labels()}.
		 */

		$labels = _parse::parameters( $labels, $defaults, true, false );

		$defaults['name']           = _array::get( $labels, 'name', ( is_string( $this->label ) && ! empty( $this->label ) ? $this->label : $defaults['name'] ) );
		$defaults['singular_name']  = _array::get( $labels, 'singular_name', $defaults['name'] );
		$defaults['name_admin_bar'] = _array::get( $labels, 'name_admin_bar', $defaults['name'] );
		$defaults['menu_name']      = _array::get( $labels, 'menu_name', $defaults['name'] );
		$defaults['all_items']      = _array::get( $labels, 'all_items', $defaults['menu_name'] );
		$defaults['archives']       = _array::get( $labels, 'archives', $defaults['all_items'] );

		$defaults['name_field_description']   = __( 'The name is how it appears on your site.', 'connections' );
		$defaults['slug_field_description']   = __( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'connections' );
		$defaults['parent_field_description'] = __( 'Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.', 'connections' );
		$defaults['desc_field_description']   = __( 'The description is not prominent by default; however, some themes may show it.', 'connections' );

		// $defaults = clone ( (object) $core );

		/**
		 * Filters the labels of a specific taxonomy.
		 *
		 * The dynamic portion of the hook name, `$this->name`, refers to the taxonomy slug.
		 *
		 * @since 10.2
		 *
		 * @param object $labels Object with labels for the taxonomy as member variables.
		 */
		$defaults = apply_filters( "Connections_Directory/Taxonomy/Labels/{$this->name}", $defaults );

		// Ensure that the filtered labels contain all required default values.
		$labels = (object) array_merge( (array) $defaults, (array) $labels );

		$this->labels = $labels;
	}

	/**
	 * Get the taxonomy slug.
	 *
	 * @since 10.2
	 *
	 * @return string
	 */
	public function getSlug() {

		return $this->name;
	}

	/**
	 * Get the query var string for this taxonomy.
	 *
	 * @since 10.3
	 *
	 * @return false|string
	 */
	public function getQueryVar() {

		return $this->query_var;
	}

	/**
	 * Whether the taxonomy is a "core" builtin taxonomy.
	 *
	 * @since 10.2
	 *
	 * @return bool
	 */
	public function isBuiltin() {

		return $this->_builtin;
	}

	/**
	 * Whether the taxonomy is hierarchical.
	 *
	 * @since 10.2
	 *
	 * @return bool
	 */
	public function isHierarchical() {

		return $this->hierarchical;
	}

	/**
	 * Whether to display the admin UI.
	 *
	 * @since 10.2
	 *
	 * @return bool
	 */
	public function showUI() {

		return $this->show_ui;
	}

	/**
	 * Whether the taxonomy is public.
	 *
	 * @since 10.2
	 *
	 * @return bool
	 */
	public function isPublic() {

		return $this->public;
	}

	/**
	 * Whether the taxonomy is publicly queryable.
	 *
	 * @since 10.3
	 *
	 * @return bool
	 */
	public function isPublicQueryable() {

		return $this->publicly_queryable;
	}

	/**
	 * Callback for the `Connections_Directory/Taxonomy/{$this->getSlug()}/Term/Permalink` filter.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param string $permalink
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function permalink( $permalink, $atts ) {

		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {

			$permalink = trailingslashit( $permalink . "{$this->rewrite['slug']}/{$atts['slug']}" );

		} else {

			$permalink = add_query_arg( $this->query_var, $atts['slug'], $permalink );
		}

		return $permalink;
	}

	/**
	 * Callback for the `cn_metabox` action.
	 *
	 * Registers the metabox with the Metabox API.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param cnMetaboxAPI $metabox
	 */
	public function registerMetabox( $metabox ) {

		if ( ! $this->show_ui || ! is_callable( $this->meta_box_cb ) ) {
			return;
		}

		$metabox::add(
			array(
				'id'       => "{$this->name}div",
				'title'    => $this->labels->name,
				'pages'    => Metabox::getPageHooks(),
				'context'  => 'side',
				'priority' => 'core',
				'callback' => $this->meta_box_cb,
			)
		);
	}

	/**
	 * Callback for the `cn_submenu` filter.
	 * @see \cnAdminMenu::menu()
	 *
	 * Register the admin menu item for the taxonomy.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param int[] $menu
	 *
	 * @return int[]
	 */
	public function registerAdminMenu( $menu ) {

		static $position = 60;

		if ( ! $this->show_ui || ! $this->show_in_menu ) {

			return $menu;
		}

		while ( array_key_exists( $position, $menu ) ) {

			$position++;
		}

		$menu[ $position ] = array(
			'hook'       => $this->name,
			'page_title' => "Connections : {$this->labels->name}",
			'menu_title' => $this->labels->menu_name,
			'capability' => $this->cap->manage_terms,
			'menu_slug'  => "connections_manage_{$this->name}_terms",
			'function'   => array( $this, 'renderManageTerms' ),
		);

		return $menu;
	}

	/**
	 * Callback for the `{$page_hook}` action.
	 * @see file://./wp-admin/admin.php
	 *
	 * NOTE: Action hook is executed after the `admin_init` and `load-{$page_hook}` actions.
	 *
	 * Render the taxonomy term management admin screen based on action.
	 *
	 * @internal
	 * @since 10.2
	 */
	public function renderManageTerms() {

		/**
		 * Assign self to $taxonomy, so it can be access in included files.
		 *
		 * @noinspection PhpUnusedLocalVariableInspection
		 */
		$taxonomy =& $this;

		$action = Request\Admin_Action::input()->value();

		switch ( $action ) {

			case "edit_{$this->name}":
				include 'Taxonomy/Partials/edit-taxonomy-term.php';
				break;

			default:
				include 'Taxonomy/Partials/manage-taxonomy-terms.php';
		}
	}

	/**
	 * Callback for the `Connections_Directory/Content_Blocks/Register` action.
	 *
	 * Registers the Content Block.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param Content_Blocks $registry
	 *
	 * @internal
	 */
	public function registerContentBlock( $registry ) {

		if ( true !== $this->register_content_block ) {

			return;
		}

		// The "legacy" categories taxonomy already has a registered Content Block.
		if ( 'category' === $this->name ) {

			return;
		}

		$props = wp_parse_args(
			$this->content_block_props,
			array(
				'name'            => $this->getLabels()->content_block_option_name,
				'register_option' => true,
				'heading'         => $this->getLabels()->content_block_heading,
			)
		);

		/**
		 * This filter primarily exists to allow addons that added custom taxonomies change the registered ID for backwards compatibility.
		 *
		 * @since 10.2
		 *
		 * @param string $blockID The Taxonomy Content Block ID.
		 */
		$blockID = apply_filters( 'Connections_Directory/Taxonomy/Register/Content_Block/ID', "taxonomy-{$this->getSlug()}" );

		$block = new Content_Blocks\Entry\Taxonomy( $blockID, $this, $props );

		$block->setProperties( $this->content_block_render_props );

		// Content_Blocks::instance()->add( $block );
		$registry->add( $block );
	}

	/**
	 * Callback for the `widgets_init` action.
	 *
	 * Register the taxonomy widget.
	 *
	 * @internal
	 * @since 10.2
	 */
	public function registerWidget() {

		if ( true !== $this->register_widget ) {

			return;
		}

		/**
		 * This filter primarily exists to allow addons that added custom taxonomies change the registered ID for backwards compatibility.
		 *
		 * @since 10.2
		 *
		 * @param string $widgetID The Taxonomy Widget ID.
		 */
		$widgetID = apply_filters( 'Connections_Directory/Taxonomy/Register/Widget/ID', "connections_{$this->name}_taxonomy_widget" );

		$options = array(
			'description' => $this->getLabels()->widget_description,
		);

		$control = array();

		$widget = new Widget(
			$widgetID,
			"Connections : {$this->getLabels()->widget_name}",
			$this,
			$options,
			$control
		);

		register_widget( $widget );
	}

	/**
	 * Render the metabox partials.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param cnEntry $entry   An instance of the cnEntry object.
	 * @param array   $metabox {
	 *     Hierarchical taxonomy metabox arguments.
	 *
	 *     @type string   $id       Metabox 'id' attribute.
	 *     @type string   $title    Metabox title.
	 *     @type callable $callback Metabox display callback.
	 *     @type array    $args {
	 *         Extra meta box arguments.
	 *
	 *         @type string $taxonomy Taxonomy. Default 'category'.
	 *     }
	 * }
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function renderMetabox( $entry, $metabox ) {

		/**
		 * Assign self to $taxonomy, so it can be accessed in the included files.
		 * @noinspection PhpUnusedLocalVariableInspection
		 */
		$taxonomy =& $this;

		if ( 'category' === $this->name ) {

			_array::set( $metabox, 'args.name', 'entry_category' );

		} else {

			_array::set( $metabox, 'args.name', "taxonomy_terms[{$this->name}]" );
		}

		if ( $this->isHierarchical() ) {

			include 'Taxonomy/Partials/metabox-hierarchical.php';

		} else {

			include 'Taxonomy/Partials/metabox.php';
		}
	}

	/**
	 * Sanitizes POST values from a checkbox taxonomy metabox.
	 *
	 * Based on:
	 * @see taxonomy_meta_box_sanitize_cb_checkboxes()
	 * @see taxonomy_meta_box_sanitize_cb_input()
	 *
	 * The default callback for the `meta_box_sanitize_cb` property for sanitizing
	 * the supplied taxonomy terms before attaching them to a directory entry.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param int[]|string[] $terms
	 *
	 * @return int[]|string[]
	 * @noinspection PhpUnused
	 */
	public function sanitizeTerms( $terms ) {

		if ( $this->isHierarchical() ) {

			$terms = array_filter( $terms );
			return array_unique( array_map( 'intval', $terms ) );
		}

		/*
		 * Assume that a 'taxonomy_terms' string is a comma-separated list of term names.
		 * Some languages may use a character other than a comma as a delimiter,
		 * standardize on commas before parsing the list.
		 */
		if ( ! is_array( $terms ) ) {

			$delimiter = _x( ',', 'Term delimiter.', 'connections' );

			if ( ',' !== $delimiter ) {

				$terms = str_replace( $delimiter, ',', $terms );
			}

			$terms = explode( ',', trim( $terms, " \n\t\r\0\x0B," ) );
		}

		$sanitized = array();

		foreach ( $terms as $term ) {

			// Empty terms are invalid input.
			if ( empty( $term ) ) {
				continue;
			}

			// Get the term ID from the term name.
			$_term = cnTerm::getTaxonomyTerms(
				$this->name,
				array(
					'name'       => $term,
					'fields'     => 'ids',
					'hide_empty' => false,
				)
			);

			if ( ! empty( $_term ) ) {

				// Add the term ID to the sanitized results.
				$sanitized[] = (int) $_term[0];

			} else {

				// No existing term was found, so pass the string. A new term will be created.
				$sanitized[] = $term;
			}
		}

		return $sanitized;
	}

	/**
	 * Callback for the `Connections_Directory/Taxonomy/{$this->name}/Attach_Terms` action.
	 *
	 * Attach taxonomy terms to Entry object.
	 *
	 * @internal
	 * @since  10.2
	 *
	 * @param cnEntry $entry  An instance of Entry which to attach terms to.
	 * @param array   $terms  An array of term IDs or term names to attach to the Entry.
	 *
	 * @return int[]|WP_Error
	 */
	public function attachTerms( $entry, $terms = array() ) {

		if ( ! current_user_can( $this->cap->assign_terms ) ) {

			return new WP_Error(
				'attach_terms_taxonomy',
				__( 'Sorry, you are not allowed to assign terms in this taxonomy.', 'connections' ),
				$this->name
			);
		}

		$entryID = $entry->getId();
		$terms   = array_filter( $terms );

		if ( empty( $terms ) ) {

			if ( 'category' === $this->name ) {

				$default_term_id = get_option( 'cn_default_category' );

			} else {

				$default_term_id = get_option( "connections_default_term_{$this->name}" );
			}

			$terms = array( (int) $default_term_id );
		}

		$terms = apply_filters( "Connections_Directory/Taxonomy/{$this->getSlug()}/Sanitize_Terms", $terms );

		return Connections_Directory()->term->setTermRelationships( $entryID, $terms, $this->name );
	}

	/**
	 * Callback for the `cn_csv_export_fields` and `cncsv_map_import_fields` filters.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param string[] $headers
	 *
	 * @return string[]
	 */
	public function registerCSVHeader( $headers ) {

		// The core `category` taxonomy is "hardcoded" in the CSV export/import, no need to register it.
		if ( 'category' === $this->getSlug() ) {

			return $headers;
		}

		$headers[ "taxonomy-{$this->getSlug()}" ] = $this->getLabels()->name;

		return $headers;
	}

	/**
	 * Callback for the `cn_csv_export_fields_config` filter.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function exportConfiguration( $fields ) {

		// The core `category` taxonomy is "hardcoded" in the CSV export/import, no need to register it.
		if ( 'category' === $this->getSlug() ) {

			return $fields;
		}

		$fields[] = array(
			'field' => $this->getSlug(),
			'type'  => "taxonomy-{$this->getSlug()}",
		);

		return $fields;
	}

	/**
	 * Callback for the `cn_export_header-taxonomy-{$this->getSlug()}` filter.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param string                 $header
	 * @param array                  $field
	 * @param cnCSV_Batch_Export_All $export
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function exportHeader( $header, $field, $export ) {

		return $this->getLabels()->name;
	}

	/**
	 * Callback for the `cn_export_field-taxonomy-{$this->getSlug()}` filter.
	 *
	 * Export the taxonomy terms.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param string                 $data
	 * @param object                 $entry
	 * @param array                  $field
	 * @param cnCSV_Batch_Export_All $export
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function exportTerms( $data, $entry, $field, $export ) {

		$data = '';

		// Process terms table and list all terms in a single cell.
		$names = array();

		$terms = $export->getTerms( $entry->id, $this->getSlug() );

		foreach ( $terms as $term ) {

			$names[] = $term->name;
		}

		if ( ! empty( $names ) ) {

			$data = $export->escapeAndQuote( implode( ',', $names ) );
		}

		return $data;
	}

	/**
	 * Callback for the `cncsv_import_fields` action.
	 *
	 * Import terms and attach them to the entry.
	 *
	 * @internal
	 * @since 10.2
	 *
	 * @param int          $id    The entry ID.
	 * @param array        $row   The parsed data from the CSV file.
	 * @param \cnCSV_Entry $entry An instance of the cnCSV_Entry object.
	 *
	 * @noinspection PhpFullyQualifiedNameUsageInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function importTerms( $id, $row, $entry ) {

		$taxonomy = $this->getSlug();
		$termIDs  = array();
		$parsed   = $entry->arrayPull( $row, "taxonomy-{$this->getSlug()}", $termIDs );

		if ( ! empty( $parsed ) ) {

			/*
			 * Convert the supplied terms to an array and sanitize.
			 * Apply the same filters added to the core WP default filters for `pre_term_name`
			 * so the term name will return a match if it exists.
			 */
			$terms = explode( ',', $parsed );
			$terms = array_map( 'sanitize_text_field', $terms );
			$terms = array_map( 'wp_filter_kses', $terms );
			$terms = array_map( '_wp_specialchars', $terms );

			foreach ( $terms as $name ) {

				// Query the db for the term to be added.
				$term = cnTerm::getBy( 'name', $name, $taxonomy );

				// Existing term.
				if ( $term instanceof Term ) {

					$termIDs[] = $term->term_id;

				} else {

					// Insert new terms.
					$insert_result = cnTerm::insert(
						$name,
						$taxonomy,
						array( 'slug' => '', 'parent' => '0', 'description' => '' )
					);

					if ( ! is_wp_error( $insert_result ) ) {

						$termIDs[] = $insert_result['term_id'];
					}
				}
			}

		}

		// Do not set term relationships if $termIDs is empty because if updating, it will delete existing relationships.
		if ( ! empty( $termIDs ) ) {

			// Connections_Directory()->term->setTermRelationships( $id, $termIDs, $taxonomy );
			$this->attachTerms( $entry, $termIDs );
		}
	}
}

/**
 * Dummy class for taxonomy capabilities. Used for code completion in IDE only.
 *
 * @package Connections_Directory
 * @internal
 * @since   10.2
 *
 * @property string $manage_terms
 * @property string $edit_terms
 * @property string $delete_terms
 * @property string $assign_terms
 */
final class _Capabilities {}

/**
 * Dummy class for taxonomy labels. Used for code completion in IDE only.
 *
 * @see Taxonomy::setLabels()
 *
 * @package Connections_Directory
 * @internal
 * @since 10.2
 *
 * @property string $name
 * @property string $singular_name
 * @property string $menu_name
 * @property string $search_items
 * @property string $popular_items
 * @property string $all_items
 * @property string $select_items
 * @property string $parent_item
 * @property string $parent_item_colon
 * @property string $name_field_description
 * @property string $slug_field_description
 * @property string $parent_field_description
 * @property string $desc_field_description
 * @property string $edit_item
 * @property string $view_item
 * @property string $update_item
 * @property string $add_new_item
 * @property string $new_item_name
 * @property string $separate_items_with_commas
 * @property string $add_or_remove_items
 * @property string $choose_from_most_used
 * @property string $not_found
 * @property string $no_terms
 * @property string $filter_by_item
 * @property string $items_list_navigation
 * @property string $items_list
 * @property string $most_used
 * @property string $back_to_items
 * @property string $content_block_option_name
 * @property string $content_block_heading
 * @property string $content_block_label
 * @property string $content_block_label_colon
 * @property string $widget_name
 * @property string $widget_description
 */
final class _Labels {}
