<?php

/**
 * Class registering the core metaboxes for add/edit an entry.
 *
 * @package     Connections
 * @subpackage  Core Metaboxes
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnEntryMetabox
 */
class cnEntryMetabox {

	/**
	 * The core metabox options array.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $metaboxes = array();

	/**
	 * An associative array of visibility options permitted by the current user.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $visibility = array();

	/**
	 * Initiate the core metaboxes and fields.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @return void
	 */
	public static function init() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Build the array that defines the core metaboxes.
		self::register();

		// Register the core metaboxes the Metabox API.
		foreach ( self::$metaboxes as $atts ) {

			cnMetaboxAPI::add( $atts );
		}

		// Set the "Visibility" options that can be set by the current user.
		if ( is_user_logged_in() ) self::$visibility = $instance->options->getVisibilityOptions();
	}

	/**
	 * Register the core metabox and fields.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @return void
	 */
	private static function register() {

		if ( is_admin() ) {

			$pageHooks = apply_filters( 'cn_admin_default_metabox_page_hooks', array( 'connections_page_connections_add', 'connections_page_connections_manage' ) );

			// Define the core pages and use them by default if no page where defined.
			// Check if doing AJAX because the page hooks are not defined when doing an AJAX request which cause undefined property errors.
			$pages = defined('DOING_AJAX') && DOING_AJAX ? array() : $pageHooks;

		} else {

			$pages = array( 'public' );
		}

		/*
		 * Now we're going to have to keep track of which TinyMCE plugins
		 * WP core supports based on version, sigh.
		 */
		if ( version_compare( $GLOBALS['wp_version'], '3.8.999', '<' ) ) {

			$tinymcePlugins = array( 'inlinepopups', 'tabfocus', 'paste', 'wordpress', 'wplink', 'wpdialogs' );

		} else {

			$tinymcePlugins = array( 'tabfocus', 'paste', 'wordpress', 'wplink', 'wpdialogs' );
		}

		if ( current_user_can( 'unfiltered_html' ) ) {

			$rteOptions = array();

		} else {

			$rteOptions = array(
				'media_buttons' => current_user_can( 'unfiltered_html' ) ? TRUE : FALSE,
				'tinymce'       => array(
					'editor_selector'   => 'tinymce',
					'toolbar1'          => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, alignleft, aligncenter, alignright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
					'toolbar2'          => '',
					'inline_styles'     => TRUE,
					'relative_urls'     => FALSE,
					'remove_linebreaks' => FALSE,
					'plugins'           => implode( ',', $tinymcePlugins )
				)
			);
		}

		self::$metaboxes[] = array(
			'id'       => 'submitdiv',
			'title'    => __( 'Publish', 'connections' ),
			'pages'    => $pages,
			'context'  => 'side',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'publish' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'categorydiv',
			'title'    => __( 'Categories', 'connections' ),
			'pages'    => $pages,
			'context'  => 'side',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'category' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-image',
			'title'    => __( 'Image', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'image' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-logo',
			'title'    => __( 'Logo', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'logo' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-address',
			'title'    => __( 'Addresses', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'address' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-phone',
			'title'    => __( 'Phone Numbers', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'phone' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-email',
			'title'    => __( 'Email Addresses', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'email' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-messenger',
			'title'    => __( 'Messenger IDs', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'messenger' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-social-media',
			'title'    => __( 'Social Media IDs', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'social' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-links',
			'title'    => __( 'Links', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'links' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-date',
			'title'    => __( 'Dates', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'date' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-bio',
			'title'    => __( 'Biographical Info', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'fields' => array(
				array(
					'id'         => 'bio',
					'type'       => 'rte',
					'value'      => 'getBio',
					'options'    => $rteOptions,
				),
			),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-note',
			'title'    => __( 'Notes', 'connections' ),
			'pages'    => $pages,
			'context'  => 'normal',
			'priority' => 'core',
			'fields' => array(
				array(
					'id'         => 'notes',
					'type'       => 'rte',
					'value'      => 'getNotes',
					'options'    => $rteOptions,
				),
			),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-meta',
			'title'    => __( 'Custom Fields', 'connections' ),
			'pages'    => $pages,
			'name'     => 'Meta',
			'desc'     => __( 'Custom fields can be used to add extra metadata to an entry that you can use in your template.', 'connections' ),
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'meta' ),
		);
	}

	/**
	 * Callback to render the "Publish" metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @global string $plugin_page
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register().
	 * @return void
	 */
	public static function publish( $entry, $metabox, $atts = array() ) {
		global $plugin_page;

		$defaults = array(
			'action'     => NULL,
			'entry_type' => array(
				__( 'Individual', 'connections' )   => 'individual',
				__( 'Organization', 'connections' ) => 'organization',
				__( 'Family', 'connections' )       => 'family'
			),
			'default'    => array(
				'type'       => 'individual',
				'visibility' => 'public',
			),
		);

		// Do not use the `cn_admin_metabox_publish_atts` filter. Let in for backward compatibility for version prior to 0.8.
		$defaults        = wp_parse_args( apply_filters( 'cn_admin_metabox_publish_atts', $atts ), $defaults );

		$atts            = wp_parse_args( apply_filters( 'cn_metabox_publish_atts', $atts ), $defaults );
		$atts['default'] = wp_parse_args( $atts['default'], $defaults['default'] );

		if ( isset( $_GET['cn-action'] ) ) {

			$action = esc_attr( $_GET['cn-action'] );

		} else {

			$action = $atts['action'];
		}

		$visibility = $entry->getId() ? $entry->getVisibility() : $atts['default']['visibility'];
		$type       = $entry->getId() ? $entry->getEntryType()  : $atts['default']['type'];

		// if ( $action == NULL ) {

			// The options have to be flipped because of an earlier stupid decision
			// of making the array keys the option labels. This basically provide
			// backward compatibility.
			cnHTML::radio(
				array(
					'display' => 'block',
					'id'      => 'entry_type',
					'options' => array_flip( $atts['entry_type'] ),
					'before'  => '<div id="entry-type">',
					'after'   => '</div>',
					),
				$type
				);

		// }

		cnHTML::radio(
			array(
				'display' => 'block',
				'id'      => 'visibility',
				'options' => array(
					'public'   => __( 'Public', 'connections' ),
					'private'  => __( 'Private', 'connections' ),
					'unlisted' => __( 'Unlisted', 'connections' ),
					),
				'before'  => '<div id="visibility">',
				'after'   => '</div>',
				),
			$visibility
		);

		// Create URL to current admin page.
		$adminURL = admin_url( 'admin.php', ( is_ssl() ? 'https' : 'http' ) );
		$adminURL = add_query_arg( array( 'page' => $plugin_page ), $adminURL );

		echo '<div id="minor-publishing"></div>';

		echo '<div id="major-publishing-actions">';

			switch ( TRUE ) {

				case ( $action ==  'edit_entry' || $action == 'edit' ):

					echo '<input type="hidden" name="cn-action" value="update_entry"/>';
					echo '<div id="cancel-button"><a href="' . esc_url( $adminURL ) . '" class="button cn-button cn-button-warning">' , __( 'Cancel', 'connections' ) , '</a></div>';
					echo '<div id="publishing-action"><input  class="button-primary" type="submit" name="update" value="' , __( 'Update', 'connections' ) , '" /></div>';

					break;

				case ( $action == 'copy_entry' || $action == 'copy' ):

					echo '<input type="hidden" name="cn-action" value="duplicate_entry"/>';
					echo '<div id="cancel-button"><a href="' . esc_url( $adminURL ) . '" class="button cn-button cn-button-warning">' , __( 'Cancel', 'connections' ) , '</a>';
					echo '</div><div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' , __( 'Add Entry', 'connections' ) , '" /></div>';

					break;

				default:

					echo '<input type="hidden" name="cn-action" value="add_entry"/>';
					echo '<div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' , __( 'Add Entry', 'connections' ) , '" /></div>';

					break;
			}

			echo '<div class="clear"></div>';
		echo '</div>';
	}

	/**
	 * The category metabox.
	 *
	 * @access public
	 * @since  0.8
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array   $metabox The metabox options array from self::register().
	 * @return string           The category metabox.
	 */
	public static function category( $entry, $metabox ) {

		echo '<div class="categorydiv" id="taxonomy-category">';
		echo '<div id="category-all" class="tabs-panel">';

		cnTemplatePart::walker(
			'term-checklist',
			array(
				'selected' => cnTerm::getRelationships( $entry->getID(), 'category', array( 'fields' => 'ids' ) ),
			)
		);

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Callback to render the "Name" metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register().
	 * @return void
	 */
	public static function name( $entry, $metabox, $atts = array() ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// This array will store field group IDs as the fields are registered.
		// This array will be checked for an existing ID before rendering
		// a field to prevent multiple field group IDs from being rendered.
		$groupIDs = array();

		// This array will store field IDs as the fields are registered.
		// This array will be checked for an existing ID before rendering
		// a field to prevent multiple field IDs from being rendered.
		$fieldIDs = array();

		$defaults = array(
			// Define the entry type so the correct fields will be rendered. If an entry type is all registered entry types, render all fields assuming this is new entry.
			'type'  => /*$entry->getEntryType() ? $entry->getEntryType() : */array( 'individual', 'organization', 'family'),
			// The entry type to which the meta fields are being registered.
			'individual' => array(
				// The entry type field meta. Contains the arrays that define the field groups and their respective fields.
				'meta'   => array(
					// This key is the field group ID and it must be unique. Duplicates will be discarded.
					'name' => array(
						// Whether or not to render the field group.
						'show'  => TRUE,
						// The fields within the field group.
						'field' => array(
							// This key is the field ID.
							'prefix' => array(
								// Each field must have an unique ID. Duplicates will be discarded.
								'id'        => 'honorific_prefix',
								// Whether or not to render the field.
								'show'      => TRUE,
								// The field label if supplied.
								'label'     => __( 'Prefix' , 'connections' ),
								// Whether or not the field is required. If it is required 'class="required"' will be added to the field.
								// This will be used by jQuery Validate.
								'required'  => FALSE,
								// The field type.
								'type'      => 'text',
								// The field value.
								'value'     => strlen( $entry->getHonorificPrefix() ) > 0 ? $entry->getHonorificPrefix( 'edit' ) : '',
								'before'    => '<span id="cn-name-prefix">',
								'after'     => '</span>',
								),
							'first' => array(
								'id'        => 'first_name',
								'show'      => TRUE,
								'label'     => __( 'First Name' , 'connections' ),
								'required'  => TRUE,
								'type'      => 'text',
								'value'     => strlen( $entry->getFirstName() ) > 0 ? $entry->getFirstName( 'edit' ) : '',
								'before'    => '<span id="cn-name-first">',
								'after'     => '</span>',
								),
							'middle' => array(
								'id'        => 'middle_name',
								'show'      => TRUE,
								'label'     => __( 'Middle Name' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getMiddleName() ) > 0 ? $entry->getMiddleName( 'edit' ) : '',
								'before'    => '<span id="cn-name-middle">',
								'after'     => '</span>',
								),
							'last' => array(
								'id'        => 'last_name',
								'show'      => TRUE,
								'label'     => __( 'Last Name' , 'connections' ),
								'required'  => TRUE,
								'type'      => 'text',
								'value'     => strlen( $entry->getLastName() ) > 0 ? $entry->getLastName( 'edit' ) : '',
								'before'    => '<span id="cn-name-last">',
								'after'     => '</span>',
								),
							'suffix' => array(
								'id'        => 'honorific_suffix',
								'show'      => TRUE,
								'label'     => __( 'Suffix' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getHonorificSuffix() ) > 0 ? $entry->getHonorificSuffix( 'edit' ) : '',
								'before'    => '<span id="cn-name-suffix">',
								'after'     => '</span>',
								),
							),
						),
					'title' => array(
						'show'  => TRUE,
						'field' => array(
							'title' => array(
								'id'        => 'title',
								'show'      => TRUE,
								'label'     => __( 'Title' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getTitle() ) > 0 ? $entry->getTitle( 'edit' ) : '',
								),
							),
						),
					'organization' => array(
						'show'  => TRUE,
						'field' => array(
							'organization' => array(
								'id'        => 'organization',
								'show'      => TRUE,
								'label'     => __( 'Organization' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getOrganization() ) > 0 ? $entry->getOrganization( 'edit' ) : '',
								),
							),
						),
					'department' => array(
						'show'  => TRUE,
						'field' => array(
							'department' => array(
								'id'        => 'department',
								'show'      => TRUE,
								'label'     => __( 'Department' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getDepartment() ) > 0 ? $entry->getDepartment( 'edit' ) : '',
								),
							),
						),
					),
				),
			'organization' => array(
				'meta' => array(
					'organization' => array(
						'show'  => TRUE,
						'field' => array(
							'organization' => array(
								'id'        => 'organization',
								'show'      => TRUE,
								'label'     => __( 'Organization' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getOrganization() ) > 0 ? $entry->getOrganization( 'edit' ) : '',
								),
							),
						),
					'department' => array(
						'show'  => TRUE,
						'field' => array(
							'department' => array(
								'id'        => 'department',
								'show'      => TRUE,
								'label'     => __( 'Department' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getDepartment() ) > 0 ? $entry->getDepartment( 'edit' ) : '',
								),
							),
						),
					'contact' => array(
						'show'  => TRUE,
						'field' => array(
							'contact_first_name' => array(
								'id'        => 'contact_first_name',
								'show'      => TRUE,
								'label'     => __( 'Contact First Name' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getContactFirstName() ) > 0 ? $entry->getContactFirstName( 'edit' ) : '',
								'before'    => '<span class="cn-half-width" id="cn-contact-first-name">',
								'after'     => '</span>',
								),
							'contact_last_name' => array(
								'id'        => 'contact_last_name',
								'show'      => TRUE,
								'label'     => __( 'Contact Last Name' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $entry->getContactLastName() ) > 0 ? $entry->getContactLastName( 'edit' ) : '',
								'before'    => '<span class="cn-half-width" id="cn-contact-last-name">',
								'after'     => '</span>',
								),
							),
						),
					),
				),
			'family' => array(
				// Instead of supplying the field meta, a callback can be used instead.
				// This is useful if the entry type output is complex. Like the 'family entry type.'
				// If a callback is supplied the 'meta' key is passed as $atts and the $entry object is passed.
				'callback' => array( __CLASS__, 'family' ),
				'meta'     => array(),
				),
			);

		$atts = wp_parse_args( apply_filters( 'cn_metabox_name_atts', $atts ), $defaults );

		foreach ( (array) $atts['type'] as $entryType ) {

			if ( array_key_exists( $entryType, $atts ) ) {

				if ( isset( $atts[ $entryType ]['callback'] ) ) {

					call_user_func( $atts[ $entryType ]['callback'], $entry, $atts[ $entryType ]['meta'] );
					continue;
				}

				/*
				 * Dump the output in a var that way it can mre more easily broke up and filters added later.
				 */
				$out = '';

				foreach ( $atts[ $entryType ]['meta'] as $type => $meta ) {

					if ( in_array( $type, $groupIDs ) ) {

						continue;

					} else {

						$groupIDs[] = $type;
					}

					$out .= '<div class="cn-metabox" id="cn-metabox-section-' . $type . '">' . PHP_EOL;

					if ( $meta['show'] == TRUE ) {

						foreach( $meta['field'] as $field ) {

							if ( in_array( $field['id'], $fieldIDs ) ) {

								continue;

							} else {

								$fieldIDs[] = $field['id'];
							}

							if ( $field['show'] ) {

								$defaults = array(
									'type'     => '',
									'class'    => array(),
									'id'       => '',
									'style'    => array(),
									'options'  => array(),
									'value'    => '',
									'required' => FALSE,
									'label'    => '',
									'before'   => '',
									'after'    => '',
									'return'   => TRUE,
									);

								$field = wp_parse_args( $field, $defaults );

								$out .= cnHTML::field(
									array(
										'type'     => $field['type'],
										'class'    => $field['class'],
										'id'       => $field['id'],
										'style'    => $field['style'],
										'options'  => $field['options'],
										'required' => $field['required'],
										'label'    => $field['label'],
										'before'   => $field['before'],
										'after'    => $field['after'],
										'return'   => TRUE,
									),
									$field['value']
								);
							}
						}
					}

					$out .= '</div>' . PHP_EOL;
				}

				echo $out;
			}
		}
	}

	/**
	 * Callback to render the 'family' entry type part of the 'Name' metabox.
	 * Called from self::name()
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register(). Passed from self::name().
	 * @return void
	 */
	public static function family( $entry, $atts ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$html = '';
		$id   = $entry->getId();
		$ckey = $entry->getId() ? 'relative_select_entry_' . $id : 'relative_select_user_' . $instance->currentUser->getID();

		if ( FALSE !== ( $cache = cnCache::get( $ckey, 'transient' ) ) ) {

			echo $cache;
			return;
		}

		// Retrieve all the entries of the "individual" entry type that the user is permitted to view and is approved.
		$individuals = cnRetrieve::individuals();

		// Get the core entry relations.
		$relations   = $instance->options->getDefaultFamilyRelationValues();

		$html .= '<div class="cn-metabox" id="cn-metabox-section-family">';

			// --> Start template for Family <-- \\
			$html .= '<textarea id="cn-relation-template" style="display: none">';

			$html .= cnHTML::select(
				array(
					'class'    => 'family-member-name',
					'id'       => 'family_member[::FIELD::][entry_id]',
					'default'  => __( 'Select Entry', 'connections' ),
					'options'  => $individuals,
					'enhanced' => TRUE,
					'return'   => TRUE,
				)
			);

			$html .= cnHTML::select(
				array(
					'class'    => 'family-member-relation',
					'id'       => 'family_member[::FIELD::][relation]',
					'default'  => __( 'Select Relation', 'connections' ),
					'options'  => $relations,
					'enhanced' => TRUE,
					'return'   => TRUE,
				)
			);

			$html .= '</textarea>';
			// --> End template for Family <-- \\

			$html .= '<label for="family_name">' . __( 'Family Name', 'connections' ) . ':</label>';
			$html .= '<input type="text" name="family_name" value="' . $entry->getFamilyName() . '" />';

			$html .= '<ul id="cn-relations">';

			if ( $entry->getFamilyMembers() ) {

				foreach ( $entry->getFamilyMembers() as $key => $value ) {

					$token = str_replace( '-', '', cnUtility::getUUID() );

					if ( array_key_exists( $key, $individuals ) ) {

						$html .= '<li id="relation-row-' . $token . '" class="cn-relation"><i class="fa fa-sort"></i> ';

							$html .= cnHTML::select(
								array(
									'class'    => 'family-member-name',
									'id'       => 'family_member[' . $token . '][entry_id]',
									'default'  => __( 'Select Entry', 'connections' ),
									'options'  => $individuals,
									'enhanced' => TRUE,
									'return'   => TRUE,
									),
									$key
								);

							$html .= cnHTML::select(
								array(
									'class'   => 'family-member-relation',
									'id'      => 'family_member[' . $token . '][relation]',
									'default'  => __( 'Select Relation', 'connections' ),
									'options' => $relations,
									'enhanced' => TRUE,
									'return'   => TRUE,
									),
									$value
								);

							$html .= '<a href="#" class="cn-remove cn-button button cn-button-warning" data-type="relation" data-token="' . $token . '">' . __( 'Remove', 'connections' ) . '</a>';

						$html .= '</li>';
					}
				}
			}

			$html .= '</ul>';

			$html .= '<p class="add"><a id="add-relation" class="button">' . __( 'Add Relation', 'connections' ) . '</a></p>';

		$html .= '</div>';

		cnCache::set( $ckey, $html, YEAR_IN_SECONDS, 'transient' );

		echo $html;
	}

	/**
	 * Renders the image/photo metabox.
	 *
	 * @access public
	 * @since  0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox options array from self::register().
	 * @return string          The image/photo metabox.
	 */
	public static function image( $entry, $metabox ) {

		if ( $entry->getImageLinked() ) {

			$selected = $entry->getImageDisplay() ? 'show' : 'hidden';

			echo '<div class="cn-center">';

				if ( method_exists( $entry, 'getImage' ) ) {

					$entry->getImage(
						array(
							'image'  => 'photo',
							'preset' => 'profile',
							'action' => 'edit',
							)
						);

				} else {

					// Since the getImage() method did not exist, the cnEntry_HTML needs to be init/d.
					$out = new cnEntry_HTML();
					$out->set( $entry->getId() );

					$out->getImage(
						array(
							'image'  => 'photo',
							'preset' => 'profile',
							'action' => 'edit',
							)
						);

				}

				cnHTML::radio(
					array(
						'format'  => 'inline',
						'id'      => 'imgOptions',
						'options' => array(
							'show'   => __( 'Display', 'connections' ),
							'hidden' => __( 'Not Displayed', 'connections' ),
							'remove' => __( 'Remove', 'connections' ),
							),
						'before'   => '<div>',
						'after'    => '</div>',
						),
					$selected
				);

			echo '</div>';
		}

		echo '<label for="original_image">' , __( 'Select Image', 'connections' ) , ':';
		echo '<input type="file" accept="image/*" value="" name="original_image" size="25" /></label>';

		echo '<p class="suggested-dimensions">';
			printf( __( 'Maximum upload file size: %s.', 'connections' ), esc_html( size_format( wp_max_upload_size() ) ) );
		echo '</p>';
	}

	/**
	 * Renders the logo metabox.
	 *
	 * @access public
	 * @since  0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox options array from self::register().
	 * @return string          The logo metabox.
	 */
	public static function logo( $entry, $metabox ) {

		if ( $entry->getLogoLinked() ) {

			$selected = $entry->getLogoDisplay() ? 'show' : 'hidden';

			echo '<div class="cn-center">';

				if ( method_exists( $entry, 'getImage' ) ) {

					$entry->getImage(
						array(
							'image'  => 'logo',
							'action' => 'edit',
							)
						);

				} else {

					// Since the getImage() method did not exist, the cnEntry_HTML needs to be init/d.
					$out = new cnEntry_HTML();
					$out->set( $entry->getId() );

					$out->getImage(
						array(
							'image'  => 'logo',
							'action' => 'edit',
							)
						);
				}

				cnHTML::radio(
					array(
						'format'  => 'inline',
						'id'      => 'logoOptions',
						'options' => array(
							'show'   => __( 'Display', 'connections' ),
							'hidden' => __( 'Not Displayed', 'connections' ),
							'remove' => __( 'Remove', 'connections' ),
							),
						'before'   => '<div>',
						'after'    => '</div>',
						),
					$selected
				);

			echo '</div>';
		}

		echo '<label for="original_logo">' , __( 'Select Logo', 'connections' ) , ':';
		echo '<input type="file" accept="image/*" value="" name="original_logo" size="25" /></label>';

		echo '<p class="suggested-dimensions">';
			printf( __( 'Maximum upload file size: %s.', 'connections' ), esc_html( size_format( wp_max_upload_size() ) ) );
		echo '</p>';
	}

	/**
	 * Renders the address metabox.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  cnEntry $entry An instance of the cnEntry object.
	 * @param  array   $atts  The metabox options array from self::register().
	 *
	 * @return string The address metabox.
	 */
	public static function address( $entry, $atts ) {

		echo '<div class="widgets-sortables ui-sortable" id="addresses">' , PHP_EOL;

		// --> Start template <-- \\
		echo '<textarea id="address-template" style="display: none;">' , PHP_EOL;

			self::addressField( new stdClass() );

		echo '</textarea>' , PHP_EOL;
		// --> End template <-- \\

		$addresses = $entry->getAddresses( array(), FALSE, FALSE, 'edit' );
		//print_r($addresses);

		if ( ! empty( $addresses ) ) {

			foreach ( $addresses as $address ) {

				$token = str_replace( '-', '', cnUtility::getUUID() );

				echo '<div class="widget address" id="address-row-'  . $token . '">' , PHP_EOL;

				self::addressField( $address, $token );

				echo '</div>' , PHP_EOL;
			}
		}

		echo  '</div>' , PHP_EOL;

		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="address" data-container="addresses">' , __( 'Add Address', 'connections' ) , '</a></p>' , PHP_EOL;
	}

	/**
	 * Renders the address field.
	 *
	 * @access private
	 * @since  8.5.13
	 *
	 * @param stdClass $address
	 * @param string   $token
	 */
	private static function addressField( $address, $token = '::FIELD::' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Grab the address types.
		$addressTypes = $instance->options->getDefaultAddressValues();

		?>

		<div class="widget-top">
			<div class="widget-title-action"><a class="widget-action"></a></div>

			<div class="widget-title">
				<h4>

					<?php

					cnHTML::field(
						array(
							'type'     => 'select',
							'class'    => '',
							'id'       => 'address[' . $token . '][type]',
							'options'  => $addressTypes,
							'required' => FALSE,
							'before'   => '<span class="address-type">',
							'label'    => __( 'Address Type', 'connections' ),
							'return'   => FALSE,
						),
						isset( $address->type ) ? $address->type : ''
					);

					cnHTML::field(
						array(
							'type'     => 'radio',
							'format'   => 'inline',
							'class'    => '',
							'id'       => 'address[preferred]',
							'options'  => array( $token => __( 'Preferred', 'connections' ) ),
							'required' => FALSE,
							'before'   => '<span class="preferred">',
							'after'    => '</span></span>',
							'return'   => FALSE,
						),
						isset( $address->preferred ) && $address->preferred ? $token : ''
					);

					// Only show this if there are visibility options that the user is permitted to see.
					if ( ! empty( self::$visibility ) ) {

						cnHTML::field(
							array(
								'type'     => 'radio',
								'format'   => 'inline',
								'class'    => '',
								'id'       => 'address[' . $token . '][visibility]',
								'options'  => self::$visibility,
								'required' => FALSE,
								'before'   => '<span class="visibility">' . __( 'Visibility', 'connections' ) . ' ',
								'after'    => '</span>',
								'return'   => FALSE,
							),
							isset( $address->visibility ) ? $address->visibility : 'public'
						);
					}

					?>

				</h4>
			</div>

		</div>

		<div class="widget-inside">

			<div class="address-local">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][line_1]',
						'required' => FALSE,
						'label'    => __( 'Address Line 1', 'connections' ),
						'before'   => '<div class="address-line">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->line_1 ) ? $address->line_1 : ''
				);

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][line_2]',
						'required' => FALSE,
						'label'    => __( 'Address Line 2', 'connections' ),
						'before'   => '<div class="address-line">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->line_2 ) ? $address->line_2 : ''
				);

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][line_3]',
						'required' => FALSE,
						'label'    => __( 'Address Line 3', 'connections' ),
						'before'   => '<div class="address-line">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->line_3 ) ? $address->line_3 : ''
				);

				?>

			</div>

			<div class="address-region">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][city]',
						'required' => FALSE,
						'label'    => __( 'City', 'connections' ),
						'before'   => '<div class="address-city">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->city ) ? $address->city : ''
				);

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][state]',
						'required' => FALSE,
						'label'    => __( 'State', 'connections' ),
						'before'   => '<div class="address-state">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->state ) ? $address->state : ''
				);

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][zipcode]',
						'required' => FALSE,
						'label'    => __( 'Zipcode', 'connections' ),
						'before'   => '<div class="address-zipcode">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->zipcode ) ? $address->zipcode : ''
				);

				?>

			</div>

			<?php

			cnHTML::field(
				array(
					'type'     => 'text',
					'class'    => '',
					'id'       => 'address[' . $token . '][country]',
					'required' => FALSE,
					'label'    => __( 'Country', 'connections' ),
					'before'   => '<div class="address-country">',
					'after'    => '</div>',
					'return'   => FALSE,
				),
				isset( $address->country ) ? $address->country : ''
			);

			?>

			<div class="address-geo">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][latitude]',
						'required' => FALSE,
						'label'    => __( 'Latitude', 'connections' ),
						'before'   => '<div class="address-latitude">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->latitude ) ? $address->latitude : ''
				);

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'address[' . $token . '][longitude]',
						'required' => FALSE,
						'label'    => __( 'Longitude', 'connections' ),
						'before'   => '<div class="address-longitude">',
						'after'    => '</div>',
						'return'   => FALSE,
					),
					isset( $address->longitude ) ? $address->longitude : ''
				);

				?>

				<?php if ( is_admin() ) : ?>
					<div class="geocode-button-container">
						<a class="geocode button" data-uid="<?php echo $token; ?>" href="#"><?php esc_html_e( 'Geocode', 'connections' ); ?></a>
					</div>
				<?php endif; ?>

			</div>

			<div class="clear"></div>

			<?php if ( is_admin() ) : ?>
				<div class="map" id="map-<?php echo $token; ?>" data-map-id="<?php echo $token; ?>" style="display: none; height: 400px;"><?php esc_html_e( 'Geocoding Address.', 'connections' ); ?></div>
			<?php endif; ?>

			<?php if ( isset( $address->id ) ) : ?>
			<input type="hidden" name="address[<?php echo $token; ?>][id]" value="<?php echo $address->id; ?>">
			<?php endif; ?>

			<p class="cn-remove-button">
				<a href="#" class="cn-remove cn-button button cn-button-warning"
				   data-type="address"
				   data-token="<?php echo $token; ?>"><?php esc_html_e( 'Remove', 'connections' ); ?></a>
			</p>

		</div>
		<?php
	}

	/**
	 * Renders the phone metabox.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array   $metabox The metabox options array from self::register().
	 *
	 * @return string The phone metabox.
	 */
	public static function phone( $entry, $metabox ) {

		echo '<div class="widgets-sortables ui-sortable" id="phone-numbers">' , PHP_EOL;

		// --> Start template <-- \\
		echo '<textarea id="phone-template" style="display: none;">' , PHP_EOL;

			self::phoneField( new stdClass() );

		echo '</textarea>' , PHP_EOL;
		// --> End template <-- \\

		$phoneNumbers = $entry->getPhoneNumbers( array(), FALSE );

		if ( ! empty( $phoneNumbers ) ) {

			foreach ( $phoneNumbers as $phone ) {

				$token = str_replace( '-', '', cnUtility::getUUID() );

				echo '<div class="widget phone" id="phone-row-' . $token . '">' , PHP_EOL;

					self::phoneField( $phone, $token );

				echo '</div>' , PHP_EOL;
			}
		}

		echo '</div>' , PHP_EOL;

		echo '<p class="add"><a href="#" class="cn-add cn-button button" data-type="phone" data-container="phone-numbers">' , __( 'Add Phone Number', 'connections' ) , '</a></p>' , PHP_EOL;
	}

	/**
	 * Renders the phone field.
	 *
	 * @access private
	 * @since  8.5.11
	 *
	 * @param stdClass $phone
	 * @param string   $token
	 */
	private static function phoneField( $phone, $token = '::FIELD::' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Grab the phone types.
		$phoneTypes = $instance->options->getDefaultPhoneNumberValues();

		?>

		<div class="widget-top">
			<div class="widget-title-action"><a class="widget-action"></a></div>

			<div class="widget-title">
				<h4>

					<?php

					cnHTML::field(
						array(
							'type'     => 'select',
							'class'    => '',
							'id'       => 'phone[' . $token . '][type]',
							'options'  => $phoneTypes,
							'required' => FALSE,
							'label'    => __( 'Phone Type', 'connections' ),
							'return'   => FALSE,
						),
						isset( $phone->type ) ? $phone->type : ''
					);

					cnHTML::field(
						array(
							'type'     => 'radio',
							'format'   => 'inline',
							'class'    => '',
							'id'       => 'phone[preferred]',
							'options'  => array( $token => __( 'Preferred', 'connections' ) ),
							'required' => FALSE,
							'before'   => '<span class="preferred">',
							'after'    => '</span>',
							'return'   => FALSE,
						),
						isset( $phone->preferred ) && $phone->preferred ? $token : ''
					);

					// Only show this if there are visibility options that the user is permitted to see.
					if ( ! empty( self::$visibility ) ) {

						cnHTML::field(
							array(
								'type'     => 'radio',
								'format'   => 'inline',
								'class'    => '',
								'id'       => 'phone[' . $token . '][visibility]',
								'options'  => self::$visibility,
								'required' => FALSE,
								'before'   => '<span class="visibility">' . __( 'Visibility', 'connections' ) . ' ',
								'after'    => '</span>',
								'return'   => FALSE,
							),
							isset( $phone->visibility ) ? $phone->visibility : 'public'
						);
					}

					?>

				</h4>
			</div>

		</div>

		<div class="widget-inside">

			<div class="phone-number-container">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'phone[' . $token . '][number]',
						'required' => FALSE,
						'label'    => __( 'Phone Number', 'connections' ),
						'before'   => '',
						'after'    => '',
						'return'   => FALSE,
					),
					isset( $phone->number ) ? $phone->number : ''
				);

				?>

			</div>

			<?php

			if ( isset( $phone->id ) ) {

				echo '<input type="hidden" name="phone[', $token, '][id]" value="', $phone->id, '">', PHP_EOL;
			}

			?>

			<p class="cn-remove-button">
				<a href="#" class="cn-remove cn-button button cn-button-warning"
				   data-type="phone"
				   data-token="<?php echo $token; ?>"><?php esc_html_e( 'Remove', 'connections' ); ?></a>
			</p>

		</div>

		<?php
	}

	/**
	 * Renders the email metabox.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array   $metabox The metabox options array from self::register().
	 *
	 * @return string The email metabox.
	 */
	public static function email( $entry, $metabox ) {

		echo '<div class="widgets-sortables ui-sortable" id="email-addresses">' , PHP_EOL;

		// --> Start template <-- \\
		echo '<textarea id="email-template" style="display: none;">' , PHP_EOL;

			self::emailField( new stdClass() );

		echo '</textarea>' , PHP_EOL;
		// --> End template <-- \\

		$emailAddresses = $entry->getEmailAddresses( array(), FALSE );
		//print_r($emailAddresses);

		if ( ! empty( $emailAddresses ) ) {

			foreach ( $emailAddresses as $email ) {

				$token = str_replace( '-', '', cnUtility::getUUID() );

				echo '<div class="widget email" id="email-row-' . $token . '">' , PHP_EOL;

					self::emailField( $email, $token );

				echo '</div>' , PHP_EOL;
			}
		}

		echo  '</div>' , PHP_EOL;

		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="email" data-container="email-addresses">' , __( 'Add Email Address', 'connections' ) , '</a></p>' , PHP_EOL;
	}

	/**
	 * Renders the email field.
	 *
	 * @access private
	 * @since  8.5.11
	 *
	 * @param stdClass $email
	 * @param string   $token
	 */
	private static function emailField( $email, $token = '::FIELD::' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Grab the email types.
		$emailTypes = $instance->options->getDefaultEmailValues();

		?>

		<div class="widget-top">
			<div class="widget-title-action"><a class="widget-action"></a></div>

			<div class="widget-title">
				<h4>

					<?php

					cnHTML::field(
						array(
							'type'     => 'select',
							'class'    => '',
							'id'       => 'email[' . $token . '][type]',
							'options'  => $emailTypes,
							'required' => FALSE,
							'label'    => __( 'Email Type', 'connections' ),
							'return'   => FALSE,
						),
						isset( $email->type ) ? $email->type : ''
					);

					cnHTML::field(
						array(
							'type'     => 'radio',
							'format'   => 'inline',
							'class'    => '',
							'id'       => 'email[preferred]',
							'options'  => array( $token => __( 'Preferred', 'connections' ) ),
							'required' => FALSE,
							'before'   => '<span class="preferred">',
							'after'    => '</span>',
							'return'   => FALSE,
						),
						isset( $email->preferred ) && $email->preferred ? $token : ''
					);

					// Only show this if there are visibility options that the user is permitted to see.
					if ( ! empty( self::$visibility ) ) {

						cnHTML::field(
							array(
								'type'     => 'radio',
								'format'   => 'inline',
								'class'    => '',
								'id'       => 'email[' . $token . '][visibility]',
								'options'  => self::$visibility,
								'required' => FALSE,
								'before'   => '<span class="visibility">' . __( 'Visibility', 'connections' ) . ' ',
								'after'    => '</span>',
								'return'   => FALSE,
							),
							isset( $email->visibility ) ? $email->visibility : 'public'
						);
					}

					?>

				</h4>
			</div>

		</div>

		<div class="widget-inside">

			<div class="email-address-container">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'email[' . $token . '][address]',
						'required' => FALSE,
						'label'    => __( 'Email Address', 'connections' ),
						'before'   => '',
						'after'    => '',
						'return'   => FALSE,
					),
					isset( $email->address ) ? $email->address : ''
				);

				?>

			</div>

			<?php

			if ( isset( $email->id ) ) {

				echo '<input type="hidden" name="email[' , $token , '][id]" value="' , $email->id , '">' , PHP_EOL;
			}

			?>

			<p class="cn-remove-button">
				<a href="#" class="cn-remove cn-button button cn-button-warning"
				   data-type="email"
				   data-token="<?php echo $token; ?>"><?php esc_html_e( 'Remove', 'connections' ); ?></a>
			</p>

		</div>

		<?php
	}

	/**
	 * Renders the instant messenger metabox.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array   $metabox The metabox options array from self::register().
	 *
	 * @return string The instant messenger metabox.
	 */
	public static function messenger( $entry, $metabox ) {

		echo '<div class="widgets-sortables ui-sortable" id="im-ids">' , PHP_EOL;

		// --> Start template <-- \\
		echo '<textarea id="im-template" style="display: none;">' , PHP_EOL;

			self::messengerField( new stdClass() );

		echo '</textarea>' , PHP_EOL;
		// --> End template <-- \\

		$imIDs = $entry->getIm( array(), FALSE );
		//print_r($imIDs);

		if ( ! empty( $imIDs ) ) {

			foreach ( $imIDs as $network ) {

				$token = str_replace( '-', '', cnUtility::getUUID() );

				echo '<div class="widget im" id="im-row-'  . $token . '">' , PHP_EOL;

					self::messengerField( $network, $token );

				echo '</div>' , PHP_EOL;
			}
		}

		echo  '</div>' , PHP_EOL;

		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="im" data-container="im-ids">' , __( 'Add Messenger ID', 'connections' ) , '</a></p>' , PHP_EOL;
	}

	/**
	 * Renders the email field.
	 *
	 * @access private
	 * @since  8.5.11
	 *
	 * @param stdClass $network
	 * @param string   $token
	 */
	private static function messengerField( $network, $token = '::FIELD::' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Grab the email types.
		$messengerTypes = $instance->options->getDefaultIMValues();

		?>

		<div class="widget-top">
			<div class="widget-title-action"><a class="widget-action"></a></div>

			<div class="widget-title">
				<h4>

					<?php

					cnHTML::field(
						array(
							'type'     => 'select',
							'class'    => '',
							'id'       => 'im[' . $token . '][type]',
							'options'  => $messengerTypes,
							'required' => FALSE,
							'label'    => __( 'IM Type', 'connections' ),
							'return'   => FALSE,
						),
						isset( $network->type ) ? $network->type : ''
					);

					cnHTML::field(
						array(
							'type'     => 'radio',
							'format'   => 'inline',
							'class'    => '',
							'id'       => 'im[preferred]',
							'options'  => array( $token => __( 'Preferred', 'connections' ) ),
							'required' => FALSE,
							'before'   => '<span class="preferred">',
							'after'    => '</span>',
							'return'   => FALSE,
						),
						isset( $network->preferred ) && $network->preferred ? $token : ''
					);

					// Only show this if there are visibility options that the user is permitted to see.
					if ( ! empty( self::$visibility ) ) {

						cnHTML::field(
							array(
								'type'     => 'radio',
								'format'   => 'inline',
								'class'    => '',
								'id'       => 'im[' . $token . '][visibility]',
								'options'  => self::$visibility,
								'required' => FALSE,
								'before'   => '<span class="visibility">' . __( 'Visibility', 'connections' ) . ' ',
								'after'    => '</span>',
								'return'   => FALSE,
							),
							isset( $network->visibility ) ? $network->visibility : 'public'
						);
					}

					?>

				</h4>
			</div>

		</div>

		<div class="widget-inside">

			<div class="messenger-container">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'im[' . $token . '][id]',
						'required' => FALSE,
						'label'    => __( 'IM Network ID', 'connections' ),
						'before'   => '',
						'after'    => '',
						'return'   => FALSE,
						),
					isset( $network->id ) ? $network->id : ''
				);

				?>

			</div>

			<?php

			if ( isset( $network->uid ) ) {

				echo '<input type="hidden" name="im[', $token, '][uid]" value="', $network->uid, '">' , PHP_EOL;
			}

			?>

			<p class="cn-remove-button">
				<a href="#" class="cn-remove cn-button button cn-button-warning"
				   data-type="im"
				   data-token="<?php echo $token; ?>"><?php esc_html_e( 'Remove', 'connections' ); ?></a>
			</p>

		</div>

		<?php
	}

	/**
	 * Renders the social media network metabox.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array   $metabox The metabox options array from self::register().
	 *
	 * @return string The social media network metabox.
	 */
	public static function social( $entry, $metabox ) {

		echo '<div class="widgets-sortables ui-sortable" id="social-media">' , PHP_EOL;

		// --> Start template <-- \\
		echo '<textarea id="social-template" style="display: none;">' , PHP_EOL;

			self::socialField( new stdClass() );

		echo '</textarea>' , PHP_EOL;
		// --> End template <-- \\

		$socialNetworks = $entry->getSocialMedia( array(), FALSE );
		//print_r($socialNetworks);

		if ( ! empty( $socialNetworks ) ) {

			foreach ( $socialNetworks as $network ) {

				$token = str_replace( '-', '', cnUtility::getUUID() );

				echo '<div class="widget social-media" id="social-row-'  . $token . '">' , PHP_EOL;

					self::socialField( $network, $token );

				echo '</div>' , PHP_EOL;
			}
		}

		echo  '</div>' , PHP_EOL;

		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="social" data-container="social-media">' , __( 'Add Social Media ID', 'connections' ) , '</a></p>' , PHP_EOL;
	}

	/**
	 * Renders the social media network field.
	 *
	 * @access private
	 * @since  8.5.11
	 *
	 * @param stdClass $network
	 * @param string   $token
	 */
	private static function socialField( $network, $token = '::FIELD::' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Grab the email types.
		$socialTypes = $instance->options->getDefaultSocialMediaValues();

		?>

		<div class="widget-top">
			<div class="widget-title-action"><a class="widget-action"></a></div>

			<div class="widget-title">
				<h4>

					<?php

					cnHTML::field(
						array(
							'type'     => 'select',
							'class'    => '',
							'id'       => 'social[' . $token . '][type]',
							'options'  => $socialTypes,
							'required' => FALSE,
							'label'    => __( 'Social Network', 'connections' ),
							'return'   => FALSE,
						),
						isset( $network->type ) ? $network->type : ''
					);

					cnHTML::field(
						array(
							'type'     => 'radio',
							'format'   => 'inline',
							'class'    => '',
							'id'       => 'social[preferred]',
							'options'  => array( $token => __( 'Preferred', 'connections' ) ),
							'required' => FALSE,
							'before'   => '<span class="preferred">',
							'after'    => '</span>',
							'return'   => FALSE,
						),
						isset( $network->preferred ) && $network->preferred ? $token : ''
					);

					// Only show this if there are visibility options that the user is permitted to see.
					if ( ! empty( self::$visibility ) ) {

						cnHTML::field(
							array(
								'type'     => 'radio',
								'format'   => 'inline',
								'class'    => '',
								'id'       => 'social[' . $token . '][visibility]',
								'options'  => self::$visibility,
								'required' => FALSE,
								'before'   => '<span class="visibility">' . __( 'Visibility', 'connections' ) . ' ',
								'after'    => '</span>',
								'return'   => FALSE,
							),
							isset( $network->visibility ) ? $network->visibility : 'public'
						);
					}

					?>

				</h4>
			</div>

		</div>

		<div class="widget-inside">

			<div class="social-media-container">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'social[' . $token . '][url]',
						'required' => FALSE,
						'label'    => __( 'URL', 'connections' ),
						'before'   => '',
						'after'    => '',
						'return'   => FALSE,
					),
					isset( $network->url ) ? $network->url : ''
				);

				?>

			</div>

			<?php

			if ( isset( $network->id ) ) {

				echo '<input type="hidden" name="social[' , $token , '][id]" value="' , $network->id , '">' , PHP_EOL;
			}
			?>

			<p class="cn-remove-button">
				<a href="#" class="cn-remove cn-button button cn-button-warning"
				   data-type="social"
				   data-token="<?php echo $token; ?>"><?php esc_html_e( 'Remove', 'connections' ); ?></a>
			</p>

		</div>

		<?php
	}

	/**
	 * Renders the links metabox.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array   $metabox The metabox options array from self::register().
	 *
	 * @return string  The links metabox.
	 */
	public static function links( $entry, $metabox ) {

		echo '<div class="widgets-sortables ui-sortable" id="links">' , PHP_EOL;

		// --> Start template <-- \\
		echo '<textarea id="link-template" style="display: none;">' , PHP_EOL;

			self::linkField( new stdClass() );

		echo '</textarea>' , PHP_EOL;
		// --> End template <-- \\

		$links = $entry->getLinks( array(), FALSE );
		//print_r($links);

		if ( ! empty( $links ) ) {

			foreach ( $links as $link ) {

				$token = str_replace( '-', '', cnUtility::getUUID() );

				echo '<div class="widget link" id="link-row-'  . $token . '">' , PHP_EOL;

					self::linkField( $link, $token );

				echo '</div>' , PHP_EOL;
			}
		}

		echo  '</div>' , PHP_EOL;

		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="link" data-container="links">' , __( 'Add Link', 'connections' ) , '</a></p>' , PHP_EOL;
	}

	/**
	 * Renders the link field.
	 *
	 * @access private
	 * @since  8.5.12
	 *
	 * @param stdClass $link
	 * @param string   $token
	 */
	private static function linkField( $link, $token = '::FIELD::' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Grab the email types.
		$linkTypes = $instance->options->getDefaultLinkValues();

		?>

		<div class="widget-top">
			<div class="widget-title-action"><a class="widget-action"></a></div>

			<div class="widget-title">
				<h4>

					<?php

					cnHTML::field(
						array(
							'type'     => 'select',
							'class'    => '',
							'id'       => 'link[' . $token . '][type]',
							'options'  => $linkTypes,
							'required' => FALSE,
							'label'    => __( 'Type', 'connections' ),
							'return'   => FALSE,
						),
						isset( $link->type ) ? $link->type : ''
					);

					cnHTML::field(
						array(
							'type'     => 'radio',
							'format'   => 'inline',
							'class'    => '',
							'id'       => 'link[preferred]',
							'options'  => array( $token => __( 'Preferred', 'connections' ) ),
							'required' => FALSE,
							'before'   => '<span class="preferred">',
							'after'    => '</span>',
							'return'   => FALSE,
						),
						isset( $link->preferred ) && $link->preferred ? $token : ''
					);

					// Only show this if there are visibility options that the user is permitted to see.
					if ( ! empty( self::$visibility ) ) {

						cnHTML::field(
							array(
								'type'     => 'radio',
								'format'   => 'inline',
								'class'    => '',
								'id'       => 'link[' . $token . '][visibility]',
								'options'  => self::$visibility,
								'required' => FALSE,
								'before'   => '<span class="visibility">' . __( 'Visibility', 'connections' ) . ' ',
								'after'    => '</span>',
								'return'   => FALSE,
							),
							isset( $link->visibility ) ? $link->visibility : 'public'
						);
					}

					?>

				</h4>
			</div>

		</div>

		<div class="widget-inside">

			<div class="link-title-container">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'link[' . $token . '][title]',
						'required' => FALSE,
						'label'    => __( 'Title', 'connections' ),
						'before'   => '',
						'after'    => '',
						'return'   => FALSE,
					),
					isset( $link->title ) ? $link->title : ''
				);

				?>

			</div>

			<div class="link-url-container">

				<?php

				cnHTML::field(
					array(
						'type'     => 'text',
						'class'    => '',
						'id'       => 'link[' . $token . '][url]',
						'required' => FALSE,
						'label'    => __( 'URL', 'connections' ),
						'before'   => '',
						'after'    => '',
						'return'   => FALSE,
					),
					isset( $link->url ) ? $link->url : ''
				);

				?>

			</div>

			<div class="link-target-follow-container">

				<?php

				cnHTML::field(
					array(
						'type'     => 'select',
						'class'    => '',
						'id'       => 'link[' . $token . '][target]',
						'options'  => array(
							'new'  => __( 'New Window', 'connections' ),
							'same' => __( 'Same Window', 'connections' ),
						),
						'required' => FALSE,
						'label'    => __( 'Target', 'connections' ),
						'before'   => '<span class="target">',
						'after'    => '</span>',
						'return'   => FALSE,
					),
					isset( $link->target ) ? $link->target : ''
				);

				cnHTML::field(
					array(
						'type'     => 'select',
						'class'    => '',
						'id'       => 'link[' . $token . '][follow]',
						'options'  => array(
							'nofollow' => 'nofollow',
							'dofollow' => 'dofollow',
						),
						'required' => FALSE,
						'label'    => '',
						'before'   => '<span class="follow">',
						'after'    => '</span>',
						'return'   => FALSE,
					),
					isset( $link->followString ) ? $link->followString : ''
				);

				?>

			</div>

			<div class="link-assignment">

				<label>
					<input type="radio" name="link[image]" value="<?php echo $token; ?>" <?php if ( isset( $link->image ) ) checked( $link->image, TRUE ); ?>>
					<?php esc_html_e( 'Assign link to the image.', 'connections' ); ?>
				</label>
				<label>
					<input type="radio" name="link[logo]" value="<?php echo $token; ?>" <?php if ( isset( $link->logo ) ) checked( $link->logo, TRUE ); ?>>
					<?php esc_html_e( 'Assign link to the logo.', 'connections' ); ?>
				</label>

			</div>

			<?php if ( isset( $link->id ) ) : ?>
			<input type="hidden" name="link[<?php echo $token; ?>][id]" value="<?php echo $link->id; ?>">
			<?php endif; ?>

			<p class="cn-remove-button">
				<a href="#" class="cn-remove cn-button button cn-button-warning"
				   data-type="link"
				   data-token="<?php echo $token; ?>"><?php esc_html_e( 'Remove', 'connections' ); ?></a>
			</p>

		</div>

		<?php
	}

	/**
	 * Renders the dates metabox.
	 *
	 * @access public
	 * @since  0.8
	 *
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array   $metabox The metabox options array from self::register().
	 *
	 * @return string The dates metabox.
	 */
	public static function date( $entry, $metabox ) {

		echo '<div class="widgets-sortables ui-sortable" id="dates">' , PHP_EOL;

		// --> Start template <-- \\
		echo '<textarea id="date-template" style="display: none;">' , PHP_EOL;

			self::dateField( new stdClass() );

		echo '</textarea>' , PHP_EOL;
		// --> End template <-- \\

		$dates = $entry->getDates( array(), FALSE );
		//print_r($dates);

		if ( ! empty( $dates ) ) {

			foreach ( $dates as $date ) {

				$token = str_replace( '-', '', cnUtility::getUUID() );

				echo '<div class="widget date" id="date-row-'  . $token . '">' , PHP_EOL;

					self::dateField( $date, $token );

				echo '</div>' , PHP_EOL;
			}
		}

		echo  '</div>' , PHP_EOL;

		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="date" data-container="dates">' , __( 'Add Date', 'connections' ) , '</a></p>' , PHP_EOL;
	}

	/**
	 * Renders the social media network field.
	 *
	 * @access private
	 * @since  8.5.11
	 *
	 * @param stdClass $date
	 * @param string   $token
	 */
	private static function dateField( $date, $token = '::FIELD::' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// Grab the date types.
		$dateTypes = $instance->options->getDateOptions();

		?>

		<div class="widget-top">
			<div class="widget-title-action"><a class="widget-action"></a></div>

			<div class="widget-title">
				<h4>

					<?php

					cnHTML::field(
						array(
							'type'     => 'select',
							'class'    => '',
							'id'       => 'date[' . $token . '][type]',
							'options'  => $dateTypes,
							'required' => FALSE,
							'label'    => __( 'Type', 'connections' ),
							'return'   => FALSE,
						),
						isset( $date->type ) ? $date->type : ''
					);

					cnHTML::field(
						array(
							'type'     => 'radio',
							'format'   => 'inline',
							'class'    => '',
							'id'       => 'date[preferred]',
							'options'  => array( $token => __( 'Preferred', 'connections' ) ),
							'required' => FALSE,
							'before'   => '<span class="preferred">',
							'after'    => '</span>',
							'return'   => FALSE,
						),
						isset( $date->preferred ) && $date->preferred ? $token : ''
					);

					// Only show this if there are visibility options that the user is permitted to see.
					if ( ! empty( self::$visibility ) ) {

						cnHTML::field(
							array(
								'type'     => 'radio',
								'format'   => 'inline',
								'class'    => '',
								'id'       => 'date[' . $token . '][visibility]',
								'options'  => self::$visibility,
								'required' => FALSE,
								'before'   => '<span class="visibility">' . __( 'Visibility', 'connections' ) . ' ',
								'after'    => '</span>',
								'return'   => FALSE,
							),
							isset( $date->visibility ) ? $date->visibility : 'public'
						);
					}

					?>

				</h4>
			</div>

		</div>

		<div class="widget-inside">

			<?php

			cnHTML::field(
				array(
					'type'     => 'text',
					'class'    => 'datepicker',
					'id'       => 'date[' . $token . '][date]',
					'required' => FALSE,
					'label'    => __( 'Date', 'connections' ),
					'before'   => '',
					'after'    => '',
					'return'   => FALSE,
				),
				isset( $date->date ) ? date( 'm/d/Y', strtotime( $date->date ) ) : ''
			);

			?>

			<?php if ( isset( $date->id ) ) : ?>
				<input type="hidden" name="date[<?php echo $token; ?>][id]" value="<?php echo $date->id; ?>">
			<?php endif; ?>

			<p class="cn-remove-button">
				<a href="#" class="cn-remove cn-button button cn-button-warning"
				   data-type="date"
				   data-token="<?php echo $token; ?>"><?php esc_html_e( 'Remove', 'connections' ); ?></a>
			</p>

		</div>

		<?php
	}

	/**
	 * Callback to render the "Custom Fields" metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @param  cnEntry $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register().
	 * @return void
	 */
	public static function meta( $entry, $metabox ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$results =  $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, entry_id
			FROM " . CN_ENTRY_TABLE_META . " WHERE entry_id = %d
			ORDER BY meta_key,meta_id", $entry->getId()), ARRAY_A );

		$metabox = $metabox['args'];
		$keys    = cnMeta::key( 'entry' );
		$options = array();

		// Toss the meta that is saved as part of a custom field.
		if ( ! empty( $results ) ) {

			foreach ( $results as $metaID => $meta ) {

				if ( cnMeta::isPrivate( $meta['meta_key'] ) ) unset( $results[ $metaID ] );
			}
		}

		// Build the meta key select drop down options.
		if ( ! empty( $keys ) ) {
			$options = array_combine( array_map( 'esc_attr', array_keys( $keys ) ), array_map( 'esc_html', $keys ) );
			array_walk( $options, create_function( '&$key', '$key = "<option value=\"$key\">$key</option>";' ) );
		}

		array_unshift( $options, '<option value="-1">&mdash; ' . __( 'Select', 'connections' ) . ' &mdash;</option>');
		$options = implode( $options, PHP_EOL );

		// echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';

		echo '<div class="cn-metabox-section" id="meta-fields">';

		?>

		<table id="list-table" style="<?php echo ( empty( $results ) ? 'display: none;' : 'display: table;' ) ?>">
			<thead>
				<tr>
					<th class="left"><?php _e( 'Name', 'connections' ); ?></th>
					<th><?php _e( 'Value', 'connections' ); ?></th>
				</tr>
			</thead>

			<tbody id="the-list">

			<?php

			if ( ! empty( $results ) ) {

				foreach ( $results as $metaID => $meta ) {

					// Class added to alternate tr rows for CSS styling.
					$alternate = ! isset( $alternate ) || $alternate == '' ? 'alternate' : '';

					?>

					<tr id="meta-<?php echo $meta['meta_id']; ?>" class="<?php echo $alternate; ?>">

						<td class="left">
							<label class="screen-reader-text" for='meta[<?php echo $meta['meta_id']; ?>][key]'><?php _e( 'Key', 'connections' ); ?></label>
							<input name='meta[<?php echo $meta['meta_id']; ?>][key]' id='meta[<?php echo $meta['meta_id']; ?>][key]' type="text" size="20" value="<?php echo esc_textarea( $meta['meta_key'] ) ?>" />
							<div class="submit">
								<input type="submit" name="deletemeta[<?php echo $meta['meta_id']; ?>]" id="deletemeta[<?php echo $meta['meta_id']; ?>]" class="button deletemeta button-small" value="<?php _e( 'Delete', 'connections' ); ?>" />
							</div>
						</td>

						<td>
							<label class="screen-reader-text" for='meta[<?php echo $meta['meta_id']; ?>][value]'><?php _e( 'Value', 'connections' ); ?></label>
							<textarea name='meta[<?php echo $meta['meta_id']; ?>][value]' id='meta[<?php echo $meta['meta_id']; ?>][value]' rows="2" cols="30"><?php echo esc_textarea( cnFormatting::maybeJSONencode( $meta['meta_value'] ) ) ?></textarea>
						</td>

					</tr>

					<?php
				}

				?>

			<?php

			}

			?>

			<!-- This is the row that will be cloned via JS when adding a new Custom Field. -->
			<tr style="display: none;">

				<td class="left">
					<label class="screen-reader-text" for='newmeta[0][key]'><?php _e( 'Key', 'connections' ); ?></label>
					<input name='newmeta[0][key]' id='newmeta[0][key]' type="text" size="20" value=""/>
					<div class="submit">
						<input type="submit" name="deletemeta[0]" id="deletemeta[0]" class="button deletemeta button-small" value="<?php _e( 'Delete', 'connections' ); ?>" />
						<!-- <input type="submit" name="newmeta-0-submit" id="newmeta-0-submit" class="button updatemeta button-small" value="Update" /> -->
					</div>
					<!-- <input type="hidden" id="_ajax_nonce" name="_ajax_nonce" value="0db0025bba" /> -->
				</td>
				<td>
					<label class="screen-reader-text" for='newmeta[0][value]'><?php _e( 'Value', 'connections' ); ?></label>
					<textarea name='newmeta[0][value]' id='newmeta[0][value]' rows="2" cols="30"></textarea>
				</td>

			</tr>

			</tbody>
		</table>

		<p><strong><?php _e( 'Add New Custom Field:', 'connections' ); ?></strong></p>

		<table id="newmeta">
			<thead>
				<tr>
					<th class="left"><label for="metakeyselect"><?php _e( 'Name', 'connections' ); ?></label></th>
					<th><label for="metavalue"><?php _e( 'Value', 'connections' ); ?></label></th>
				</tr>
			</thead>
			<tbody>

				<tr>

					<td id="newmetaleft" class="left">
						<select id="metakeyselect" name="metakeyselect">
							<?php echo $options; ?>
						</select>
						<input class="hide-if-js" type=text id="metakeyinput" name="newmeta[99][key]" value=""/>
						<a href="#postcustomstuff" class="postcustomstuff hide-if-no-js"> <span id="enternew"><?php _e( 'Enter New', 'connections' ); ?></span> <span id="cancelnew" class="hidden"><?php _e( 'Cancel', 'connections' ); ?></span></a>
					</td>

					<td>
						<textarea id="metavalue" name="newmeta[99][value]" rows="2" cols="25"></textarea>
					</td>

				</tr>



			</tbody>
			<tfoot>
				<td colspan="2">
					<div class="submit">
						<input type="submit" name="addmeta" id="newmeta-submit" class="button" value="<?php _e( 'Add Custom Field', 'connections' ); ?>" />
					</div>
					<!-- <input type="hidden" id="_ajax_nonce-add-meta" name="_ajax_nonce-add-meta" value="a7f70d2878" /> -->
				</td>
			</tfoot>
		</table>

		<?php

		if ( isset( $metabox['desc'] ) && ! empty( $metabox['desc'] ) ) {

			printf( '<p>%1$s</p>',
				esc_html( $metabox['desc'] )
			);
		}

		echo '</div>';

	}

}

// Init the class.
add_action( 'cn_metabox', array( 'cnEntryMetabox', 'init' ), 1 );
