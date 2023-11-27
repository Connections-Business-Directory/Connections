<?php
/**
 * REST API Entry Controller
 *
 * @author     Steven A. Zahm
 * @category   API
 * @package    Connections
 * @subpackage REST_API
 * @since      8.5.26
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */

namespace Connections_Directory\API\REST\Route;

use cnEntry;
use cnEntry_Action;
use cnEntry_HTML;
use cnFileSystem;
use cnOptions;
use cnSanitize;
use Connections_Directory\API\REST\Route;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_sanitize;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function Connections_Directory\API\REST\Functions\is_field_included;

/**
 * REST API Entry Controller.
 *
 * @since 8.5.26
 */
class Entry extends WP_REST_Controller {

	use Route;

	/**
	 * REST API version.
	 *
	 * @since 8.5.26
	 */
	const VERSION = '1';

	/**
	 * The REST namespace.
	 *
	 * @since 8.5.26
	 * @var string
	 */
	protected $namespace;

	/**
	 * Constructor.
	 *
	 * @since 8.5.26
	 */
	public function __construct() {

		$this->namespace = 'cn-api/v' . self::VERSION;
		$this->rest_base = 'entry';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 8.5.26
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Required to be true, as resource does not support trashing.', 'connections' ),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/moderate',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the directory entry.', 'connections' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => function ( $request ) {

						global $wpdb;

						$data = $request->get_params();

						$id     = _array::get( $data, 'id' );
						$action = _array::get( $data, 'action', 'unapprove' );

						$statuses = array(
							'approve'   => 'approved',
							'unapprove' => 'pending',
						);

						$result = cnEntry_Action::status( $statuses[ $action ], $id );

						if ( true !== $result ) {

							$error = new WP_Error( 'db_update_error', 'Could not update entry in the database.', $wpdb->last_error );
							$error->add_data( array( 'status' => 500 ) );

							return $error;
						}

						$entry = $this->get_entry( $id );
						$entry->directoryHome();

						$response = $this->prepare_item_for_response( $entry, $request );

						return rest_ensure_response( $response );
					},
					'permission_callback' => array( $this, 'moderate_item_permissions_check' ),
					'args'                => array(
						'id'     => array(
							'description' => __( 'Unique identifier for the entry.', 'connections' ),
							'type'        => 'integer',
							// 'context'     => array( 'edit' ),
							'readonly'    => true,
						),
						'action' => array(
							'description'       => __( 'Limit results to those matching a string.', 'connections' ),
							'type'              => 'string',
							'enum'              => array( 'approve', 'unapprove' ),
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => 'rest_validate_request_arg',
						),
					),
				),
				'schema' => function () {

					return array(
						'$schema'    => 'http://json-schema.org/draft-04/schema#',
						'title'      => 'moderate',
						'type'       => 'object',
						'properties' => array(
							'id' => array(
								'description' => __( 'Unique identifier for directory entry.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'edit' ),
								'readonly'    => true,
							),
							'action' => array(
								'description'       => __( 'Limit results to those matching a string.', 'connections' ),
								'type'              => 'string',
								'enum'              => array( 'approve', 'unapprove' ),
								'sanitize_callback' => 'sanitize_key',
								'validate_callback' => 'rest_validate_request_arg',
							),
						),
					);
				},
			)
		);
	}

	/**
	 * Check if a given request has access to read /entry.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! is_user_logged_in() && cnOptions::loginRequired() ) {

			return new WP_Error(
				'rest_forbidden_context',
				__( 'Permission denied. Login required.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get a collection of directory entries.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$results = $this->get_entries( $request );

		$entries = array();

		foreach ( $results as $result ) {

			$entry = new cnEntry_HTML( $result );
			$entry->directoryHome();

			$data      = $this->prepare_item_for_response( $entry, $request );
			$entries[] = $this->prepare_response_for_collection( $data );
		}

		return rest_ensure_response( $entries );
	}

	/**
	 * Get entry by id.
	 *
	 * @since 9.6
	 *
	 * @param int $id Supplied ID.
	 *
	 * @return cnEntry_HTML|WP_Error Entry object if ID is valid, WP_Error otherwise.
	 */
	public function get_entry( int $id ) {

		$error = new WP_Error( 'rest_entry_invalid_id', __( 'Invalid entry ID.', 'connections' ), array( 'status' => 404 ) );

		if ( $id <= 0 ) {

			return $error;
		}

		$data = Connections_Directory()->retrieve->entry( $id );

		if ( false === $data ) {

			return $error;
		}

		$entry = new cnEntry_HTML( $data );
		$entry->directoryHome();

		return $entry;
	}

	/**
	 * Get requested entries.
	 *
	 * @since 10.4.44 Deprecate the `id` parameter in favor of the `include` parameter.
	 *        The `id` parameter will be used as the default parameter value if set.
	 * @since 10.4.44 Deprecate the `category_in` parameter in favor of the `tax_relation` parameter.
	 *        The `category_in` (default `false`) will be used as the default parameter value if set.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	protected function get_entries( WP_REST_Request $request ): array {

		$id      = _array::get( $request, 'id', array() );
		$include = _array::get( $request, 'include', array() );
		$id__in  = 0 < count( $include ) ? $include : $id;

		$categoryIn       = _array::get( $request, 'category_in', false );
		$taxonomyRelation = _format::toBoolean( $categoryIn ) ? 'AND' : 'OR';
		$taxonomyRelation = _array::get( $request, 'tax_relation', $taxonomyRelation );

		$category = 'AND' === $taxonomyRelation ? 'category__and' : 'category';

		$arguments = array(
			'list_type'        => _array::get( $request, 'type' ),
			$category          => _array::get( $request, 'categories' ),
			'category__not_in' => _array::get( $request, 'categories_exclude' ),
			'id'               => $id__in,
			'id__not_in'       => _array::get( $request, 'exclude' ),
			'limit'            => _array::get( $request, 'per_page', 10 ),
			'offset'           => _array::get( $request, 'offset', 0 ),
		);

		/**
		 * Filters the query arguments when querying entries via the REST API.
		 *
		 * @since 10.4.43
		 *
		 * @param array           $arguments The array of arguments for querying entries.
		 * @param WP_REST_Request $request   The REST API request.
		 */
		$arguments = apply_filters(
			'Connections_Directory/API/REST/Route/Entry/Get_Items/Arguments',
			$arguments,
			$request
		);

		return Connections_Directory()->retrieve->entries( $arguments );
	}

	/**
	 * Checks if a given request has access to read an entry.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {

		if ( is_user_logged_in() ) {

			/**
			 * @todo
			 *
			 * $request['context'] can be one of: view, embed, edit
			 * When user logged in, view context should be evaluated to ensure user has
			 * read capabilities for the requested entry.
			 */

			if ( 'edit' === $request['context'] &&
				 ( ! current_user_can( 'connections_edit_entry' ) || ! current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				return new WP_Error(
					'rest_forbidden_context',
					__( 'Permission denied. Current user does not have required capability(ies) assigned.', 'connections' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}

		} else {

			if ( cnOptions::loginRequired() ) {

				return new WP_Error(
					'rest_forbidden_context',
					__( 'Permission denied. Login required.', 'connections' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}
		}

		return true;
	}

	/**
	 * Retrieves a single entry.
	 *
	 * @since 9.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {

		$entry = $this->get_entry( $request['id'] );

		if ( $entry instanceof WP_Error ) {

			return $entry;
		}

		$data     = $this->prepare_item_for_response( $entry, $request );
		$response = rest_ensure_response( $data );

		$response->link_header( 'alternate', $entry->getPermalink(), array( 'type' => 'text/html' ) );

		return $response;
	}

	/**
	 * Check if a given request has access to update a specific item.
	 *
	 * @since 10.4.46
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function moderate_item_permissions_check( WP_REST_Request $request ) {

		$entry = $this->get_entry( $request['id'] );

		if ( $entry instanceof WP_Error ) {

			return $entry;
		}

		if ( ! current_user_can( 'connections_edit_entry' ) ) {

			return new WP_Error(
				'rest_cannot_edit',
				__( 'Sorry, you are not allowed to edit this entry.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		/**
		 * @todo Need to check terms permissions.
		 * @see WP_REST_Posts_Controller::check_assign_terms_permission()
		 */

		return true;
	}

	/**
	 * Check if a given request has access to update a specific item.
	 *
	 * @since 10.4.43
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {

		$entry = $this->get_entry( $request['id'] );

		if ( $entry instanceof WP_Error ) {

			return $entry;
		}

		if ( ! current_user_can( 'connections_edit_entry' )
			 && ! current_user_can( 'connections_edit_entry_moderated' )
		) {

			return new WP_Error(
				'rest_cannot_edit',
				__( 'Sorry, you are not allowed to edit this entry.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		/**
		 * @todo Need to check terms permissions.
		 * @see WP_REST_Posts_Controller::check_assign_terms_permission()
		 */

		return true;
	}

	/**
	 * Update an entry.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		global $wpdb;

		$entryID = _sanitize::integer( $request['id'] );
		$isValid = $this->get_entry( $entryID );

		if ( $isValid instanceof WP_Error ) {

			return $isValid;
		}

		// $preUpdateEntry = $isValid;
		$entry = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $entry ) ) {

			return $entry;
		}

		if ( false === $entry->update() ) {

			$error = new WP_Error( 'db_update_error', 'Could not update entry in the database.', $wpdb->last_error );
			$error->add_data( array( 'status' => 500 ) );

			return $error;
		}

		// Get the updated entry from the database.
		$entry->set( $entry->getId() );
		$entry->directoryHome();

		/**
		 * Runs after an entry is updated.
		 *
		 * @since 10.4.43
		 *
		 * @param cnEntry_HTML    $entry   An instance of the Entry object.
		 * @param WP_REST_Request $request The Request object.
		 */
		do_action(
			'Connections_Directory/API/REST/Route/Entry/Update/After',
			$entry,
			$request
		);

		$response = $this->prepare_item_for_response( $entry, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to delete an entry.
	 *
	 * @since 9.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {

		$isValid = $this->get_entry( $request['id'] );

		if ( $isValid instanceof WP_Error ) {

			return $isValid;
		}

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( ! current_user_can( 'connections_delete_entry' ) ) {

			return new WP_Error(
				'rest_cannot_delete',
				__( 'Sorry, you are not allowed to delete this entry.', 'connections' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Deletes a single entry.
	 *
	 * @since 9.6
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {

		$entry = $this->get_entry( $request['id'] );

		if ( $entry instanceof WP_Error ) {

			return $entry;
		}

		/*
		 * Ensure `edit` context when returning the deleted item.
		 */
		$request->set_param( 'context', 'edit' );

		/*
		 * Return only the original logo/photo image meta.
		 */
		$request->set_param(
			'_images',
			array(
				array(
					'type' => 'logo',
					'size' => 'original',
				),
				array(
					'type' => 'photo',
					'size' => 'original',
				),
			)
		);

		$id       = $entry->getId();
		$previous = $this->prepare_item_for_response( $entry, $request );

		$entry->delete( $id );

		// Delete any metadata associated with the entry.
		cnEntry_Action::meta( 'delete', $id );

		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $previous->get_data(),
			)
		);

		/**
		 * Fires immediately after a single entry is deleted via the REST API.
		 *
		 * @since 9.6
		 *
		 * @param cnEntry_HTML     $entry    The deleted or trashed directory entry.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action(
			'Connections_Directory/API/REST/Route/Entry/Delete/After',
			$entry,
			$response,
			$request
		);

		return $response;
	}

	/**
	 * Prepare an entry for an update.
	 *
	 * @since 10.4.43
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return cnEntry_HTML|WP_Error The prepared item, or WP_Error object on failure.
	 */
	protected function prepare_item_for_database( $request ) {

		$entryID = _sanitize::integer( $request['id'] );
		$entry   = $this->get_entry( $entryID );

		if ( $entry instanceof WP_Error ) {

			return $entry;
		}

		// The request data utilized to update an existing entry.
		$data = $request->get_params();

		// Set the entry type.
		if ( array_key_exists( 'type', $data ) ) {

			$entry->setEntryType( $data['type'] );
		}

		if ( 'individual' === $entry->getEntryType() ) {

			// Set the entry honorific prefix.
			if ( _array::exists( $data, 'honorific_prefix' ) ) {

				$entry->setHonorificPrefix( $data['honorific_prefix'] );
			}

			// Set the entry given (first) name.
			if ( _array::exists( $data, 'given_name' ) ) {

				$entry->setFirstName( $data['given_name'] );
			}

			// Set the entry additional_name (middle) name.
			if ( _array::exists( $data, 'additional_name' ) ) {

				$entry->setMiddleName( $data['additional_name'] );
			}

			// Set the entry family (last) name.
			if ( _array::exists( $data, 'family_name' ) ) {

				$entry->setLastName( $data['family_name'] );
			}

			// Set the entry honorific suffix.
			if ( _array::exists( $data, 'honorific_suffix' ) ) {

				$entry->setHonorificSuffix( $data['honorific_suffix'] );
			}

			// Set the entry job title.
			if ( _array::exists( $data, 'job_title' ) ) {

				$entry->setTitle( $data['job_title'] );
			}
		}

		// Set the entry organization.
		if ( _array::has( $data, 'org.organization_name' ) ) {

			$entry->setOrganization( _array::get( $data, 'org.organization_name', '' ) );
		}

		// Set the entry department.
		if ( _array::has( $data, 'org.organization_unit' ) ) {

			$entry->setDepartment( _array::get( $data, 'org.organization_unit', '' ) );
		}

		// Set the entry organization contact given (first) name and family (last) name.
		if ( 'organization' === $entry->getEntryType() ) {

			if ( _array::has( $data, 'contact.given_name' ) ) {

				$entry->setContactFirstName( _array::get( $data, 'contact.given_name', '' ) );
			}

			if ( _array::has( $data, 'contact.family_name' ) ) {

				$entry->setContactLastName( _array::get( $data, 'contact.family_name', '' ) );
			}
		}

		// Set entry slug.
		if ( _array::exists( $data, 'slug' ) ) {

			$oldSlug = $entry->getSlug();

			if ( $data['slug'] !== $oldSlug ) {

				$entry->setSlug( $data['slug'] );

				$newSlug = $entry->getSlug();

				// Copy the entry images from the old entry slug folder to the new folder.
				cnEntry_Action::copyImages( $entry->getImageNameOriginal(), $oldSlug, $newSlug, false );
				cnEntry_Action::copyImages( $entry->getLogoName(), $oldSlug, $newSlug, false );

				// Delete the entry images and their variations.
				cnEntry_Action::deleteImages( $entry->getImageNameOriginal(), $oldSlug );
				cnEntry_Action::deleteImages( $entry->getLogoName(), $oldSlug );

				// Delete the old entry images folder.
				cnFileSystem::xrmdir( CN_IMAGE_PATH . $oldSlug . DIRECTORY_SEPARATOR );
			}
		}

		// Set the entry bio.
		if ( _array::exists( $data, 'bio' ) ) {

			$entry->setBio( $data['bio'] );
		}

		// Set the entry notes.
		if ( _array::exists( $data, 'notes' ) ) {

			$entry->setNotes( $data['notes'] );
		}

		// Set the entry excerpt.
		if ( _array::exists( $data, 'excerpt' ) ) {

			$entry->setExcerpt( $data['excerpt'] );
		}

		// Set entry moderation status.
		if ( _array::exists( $data, 'status' ) ) {

			// Set moderation status per role capability assigned to the current user.
			if ( current_user_can( 'connections_edit_entry' ) ) {

				$entry->setStatus( $data['status'] );

			} elseif ( current_user_can( 'connections_edit_entry_moderated' ) ) {

				// This user capability requires the moderation status to be pending.
				$entry->setStatus( 'pending' );
			}
		}

		// Set entry visibility.
		if ( _array::exists( $data, 'visibility' ) ) {

			$entry->setVisibility( $data['visibility'] );
		}

		return $entry;
	}

	/**
	 * Prepare a single entry output for response.
	 *
	 * @param cnEntry_HTML    $entry   Entry object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response $data
	 */
	public function prepare_item_for_response( $entry, $request ): WP_REST_Response {

		$requestParams     = $request->get_params();
		$excerptParameters = array(
			'length' => _array::get( $requestParams, '_excerpt.length', apply_filters( 'cn_excerpt_length', 55 ) ),
			'more'   => _array::get( $requestParams, '_excerpt.more', '' ),
		);
		$fields            = $this->get_fields_for_response( $request );
		$data              = array();

		if ( is_field_included( 'id', $fields ) ) {

			_array::set( $data, 'id', $entry->getId() );
		}

		if ( is_field_included( 'type', $fields ) ) {

			_array::set( $data, 'type', $entry->getEntryType() );
		}

		if ( is_field_included( 'link', $fields ) ) {

			$data['link'] = $entry->getPermalink();
		}

		if ( is_field_included( 'slug', $fields ) ) {

			_array::set( $data, 'slug', $entry->getSlug() );
		}

		if ( is_field_included( 'fn.rendered', $fields ) ) {

			_array::set( $data, 'fn.rendered', $entry->getName() );
			// _array::set( $data, 'name.rendered', $entry->getName() );
		}

		if ( is_field_included( 'honorific_prefix.rendered', $fields ) ) {

			_array::set( $data, 'honorific_prefix.rendered', $entry->getHonorificPrefix() );
		}

		if ( is_field_included( 'given_name.rendered', $fields ) ) {

			_array::set( $data, 'given_name.rendered', $entry->getFirstName() );
		}

		if ( is_field_included( 'additional_name.rendered', $fields ) ) {

			_array::set( $data, 'additional_name.rendered', $entry->getMiddleName() );
		}

		if ( is_field_included( 'family_name.rendered', $fields ) ) {

			_array::set( $data, 'family_name.rendered', $entry->getLastName() );
		}

		if ( is_field_included( 'honorific_suffix.rendered', $fields ) ) {

			_array::set( $data, 'honorific_suffix.rendered', $entry->getHonorificSuffix() );
		}

		if ( is_field_included( 'job_title.rendered', $fields ) ) {

			_array::set( $data, 'job_title.rendered', $entry->getTitle() );
		}

		if ( is_field_included( 'org.organization_name.rendered', $fields ) ) {

			_array::set( $data, 'org.organization_name.rendered', $entry->getOrganization() );
		}

		if ( is_field_included( 'org.organization_unit.rendered', $fields ) ) {

			_array::set( $data, 'org.organization_unit.rendered', $entry->getDepartment() );
		}

		if ( is_field_included( 'contact.given_name.rendered', $fields ) ) {

			_array::set( $data, 'contact.given_name.rendered', $entry->getContactFirstName() );
		}

		if ( is_field_included( 'contact.given_name.rendered', $fields ) ) {

			_array::set( $data, 'contact.given_name.rendered', $entry->getContactFirstName() );
		}

		if ( is_field_included( 'contact.family_name.rendered', $fields ) ) {

			_array::set( $data, 'contact.family_name.rendered', $entry->getContactLastName() );
		}

		if ( is_field_included( 'adr', $fields ) ) {

			_array::set( $data, 'adr', $this->prepare_address_for_response( $entry, $request ) );
		}

		if ( is_field_included( 'tel', $fields ) ) {

			_array::set( $data, 'tel', $this->prepare_phone_for_response( $entry, $request ) );
		}

		if ( is_field_included( 'email', $fields ) ) {

			_array::set( $data, 'email', $this->prepare_email_for_response( $entry, $request ) );
		}

		if ( is_field_included( 'social', $fields ) ) {

			_array::set( $data, 'social', $this->prepare_social_for_response( $entry, $request ) );
		}

		if ( is_field_included( 'bio.rendered', $fields ) ) {

			_array::set( $data, 'bio.rendered', $entry->getBio() );
		}

		if ( is_field_included( 'notes.rendered', $fields ) ) {

			_array::set( $data, 'notes.rendered', $entry->getNotes() );
		}

		if ( is_field_included( 'excerpt.rendered', $fields ) ) {

			_array::set( $data, 'excerpt.rendered', wpautop( $entry->getExcerpt( $excerptParameters ) ) );
		}

		$context = _array::get( $request, 'context', 'view' );

		if ( $context &&
			 ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
		) {

			if ( is_field_included( 'fn.raw', $fields ) ) {

				_array::set( $data, 'fn.raw', $entry->getName( array(), 'raw' ) );
			}

			if ( is_field_included( 'honorific_prefix.raw', $fields ) ) {

				_array::set( $data, 'honorific_prefix.raw', $entry->getHonorificPrefix( 'raw' ) );
			}

			if ( is_field_included( 'given_name.raw', $fields ) ) {

				_array::set( $data, 'given_name.raw', $entry->getFirstName( 'raw' ) );
			}

			if ( is_field_included( 'additional_name.raw', $fields ) ) {

				_array::set( $data, 'additional_name.raw', $entry->getMiddleName( 'raw' ) );
			}

			if ( is_field_included( 'family_name.raw', $fields ) ) {

				_array::set( $data, 'family_name.raw', $entry->getLastName( 'raw' ) );
			}

			if ( is_field_included( 'honorific_suffix.raw', $fields ) ) {

				_array::set( $data, 'honorific_suffix.raw', $entry->getHonorificSuffix( 'raw' ) );
			}

			if ( is_field_included( 'job_title.raw', $fields ) ) {

				_array::set( $data, 'job_title.raw', $entry->getTitle( 'raw' ) );
			}

			if ( is_field_included( 'org.organization_name.raw', $fields ) ) {

				_array::set( $data, 'org.organization_name.raw', $entry->getOrganization( 'raw' ) );
			}

			if ( is_field_included( 'org.organization_unit.raw', $fields ) ) {

				_array::set( $data, 'org.organization_unit.raw', $entry->getDepartment( 'raw' ) );
			}

			if ( is_field_included( 'contact.given_name.raw', $fields ) ) {

				_array::set( $data, 'contact.given_name.raw', $entry->getContactFirstName( 'raw' ) );
			}

			if ( is_field_included( 'contact.family_name.raw', $fields ) ) {

				_array::set( $data, 'contact.family_name.raw', $entry->getContactLastName( 'raw' ) );
			}

			if ( is_field_included( 'bio.raw', $fields ) ) {

				_array::set( $data, 'bio.raw', $entry->getBio( 'raw' ) );
			}

			if ( is_field_included( 'notes.raw', $fields ) ) {

				_array::set( $data, 'notes.raw', $entry->getNotes( 'raw' ) );
			}

			if ( is_field_included( 'excerpt.raw', $fields ) ) {

				_array::set( $data, 'excerpt.raw', $entry->getExcerpt( $excerptParameters, 'raw' ) );
			}
		}

		if ( is_field_included( 'images', $fields ) ) {

			$data['images'] = $this->prepare_images_for_response( $entry, $request );
		}

		if ( is_field_included( 'visibility', $fields ) ) {

			_array::set( $data, 'visibility', $entry->getVisibility() );
		}

		if ( is_field_included( 'status', $fields ) ) {

			_array::set( $data, 'status', $entry->getStatus() );
		}

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filters the entry data for a response.
		 *
		 * @since 9.6
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param cnEntry          $entry    Entry object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters(
			'Connections_Directory/API/REST/Route/Entry/Prepare_Item/Response',
			$response,
			$entry,
			$request
		);
	}

	/**
	 * Prepare addresses for response.
	 *
	 * @since 9.3.3
	 *
	 * @param cnEntry         $entry   Entry object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array $data
	 */
	private function prepare_address_for_response( cnEntry $entry, WP_REST_Request $request ): array {

		$objects   = array();
		$addresses = $entry->getAddresses( array(), true, false, 'raw' );

		if ( empty( $addresses ) ) {

			return $objects;
		}

		foreach ( $addresses as $address ) {

			$object = array(
				'id'        => $address->id,
				'order'     => $address->order,
				'preferred' => $address->preferred,
				'type'      => $address->type,
			);

			_array::set(
				$object,
				'street_address.rendered',
				cnSanitize::field( 'street', $address->line_1 )
			);

			_array::set(
				$object,
				'extended_address.rendered',
				cnSanitize::field( 'street', $address->line_2 )
			);

			_array::set(
				$object,
				'extended_address_2.rendered',
				cnSanitize::field( 'street', $address->line_3 )
			);

			_array::set(
				$object,
				'extended_address_3.rendered',
				cnSanitize::field( 'street', $address->line_4 )
			);

			_array::set(
				$object,
				'district.rendered',
				cnSanitize::field( 'district', $address->district )
			);

			_array::set(
				$object,
				'county.rendered',
				cnSanitize::field( 'county', $address->county )
			);

			_array::set(
				$object,
				'locality.rendered',
				cnSanitize::field( 'locality', $address->city )
			);

			_array::set(
				$object,
				'region.rendered',
				cnSanitize::field( 'region', $address->state )
			);

			_array::set(
				$object,
				'postal_code.rendered',
				cnSanitize::field( 'postal-code', $address->zipcode )
			);

			_array::set(
				$object,
				'country_name.rendered',
				cnSanitize::field( 'country', $address->country )
			);

			if ( 'edit' === $request['context'] &&
				 ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				_array::set( $object, 'street_address.raw', $address->line_1 );
				_array::set( $object, 'extended_address.raw', $address->line_2 );
				_array::set( $object, 'extended_address_2.raw', $address->line_3 );
				_array::set( $object, 'extended_address_3.raw', $address->line_4 );
				_array::set( $object, 'district.raw', $address->district );
				_array::set( $object, 'county.raw', $address->county );
				_array::set( $object, 'locality.raw', $address->city );
				_array::set( $object, 'region.raw', $address->state );
				_array::set( $object, 'postal_code.raw', $address->zipcode );
				_array::set( $object, 'country_name.raw', $address->country );
			}

			_array::set( $object, 'latitude', $address->latitude );
			_array::set( $object, 'longitude', $address->longitude );
			_array::set( $object, 'visibility', $address->visibility );

			array_push( $objects, $object );
		}

		return $objects;
	}

	/**
	 * Prepare phone numbers for response.
	 *
	 * @since 9.3.3
	 *
	 * @param cnEntry         $entry   Entry object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array $data
	 */
	private function prepare_phone_for_response( cnEntry $entry, WP_REST_Request $request ): array {

		$objects = array();
		$numbers = $entry->getPhoneNumbers( array(), true, false, 'raw' );

		if ( empty( $numbers ) ) {

			return $objects;
		}

		foreach ( $numbers as $phone ) {

			$object = array(
				'id'        => $phone->id,
				'order'     => $phone->order,
				'preferred' => $phone->preferred,
				'type'      => $phone->type,
			);

			_array::set(
				$object,
				'number.rendered',
				cnSanitize::field( 'phone-number', $phone->number )
			);

			if ( 'edit' === $request['context'] &&
				 ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				_array::set( $object, 'number.raw', $phone->number );
			}

			array_push( $objects, $object );
		}

		return $objects;
	}

	/**
	 * Prepare email addresses for response.
	 *
	 * @since 9.3.3
	 *
	 * @param cnEntry         $entry   Entry object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array $data
	 */
	private function prepare_email_for_response( cnEntry $entry, WP_REST_Request $request ): array {

		$objects        = array();
		$emailAddresses = $entry->getEmailAddresses( array(), true, false, 'raw' );

		if ( empty( $emailAddresses ) ) {

			return $objects;
		}

		foreach ( $emailAddresses as $email ) {

			$object = array(
				'id'        => $email->id,
				'order'     => $email->order,
				'preferred' => $email->preferred,
				'type'      => $email->type,
			);

			_array::set(
				$object,
				'address.rendered',
				sanitize_email( $email->address )
			);

			if ( 'edit' === $request['context'] &&
				 ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				_array::set( $object, 'address.raw', $email->address );
			}

			array_push( $objects, $object );
		}

		return $objects;
	}

	/**
	 * Prepare social networks for response.
	 *
	 * @since 9.3.3
	 *
	 * @param cnEntry         $entry   Entry object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array $data
	 */
	private function prepare_social_for_response( cnEntry $entry, WP_REST_Request $request ): array {

		$objects  = array();
		$networks = $entry->getSocialMedia( array(), true, false, 'raw' );

		if ( empty( $networks ) ) {

			return $objects;
		}

		foreach ( $networks as $network ) {

			$socialNetworks = cnOptions::getRegisteredSocialNetworkTypes();

			$object = array(
				'id'        => $network->id,
				'order'     => $network->order,
				'preferred' => $network->preferred,
				'slug'      => $socialNetworks[ $network->type ]['slug'],
				'type'      => $network->type,
			);

			_array::set(
				$object,
				'url',
				cnSanitize::field( 'url', $network->url )
			);

			// if ( 'edit' === $request['context'] &&
			//     ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			// ) {
			//
			// 	_array::set( $object, 'url.raw', $network->address );
			// }

			array_push( $objects, $object );
		}

		return $objects;
	}

	/**
	 * Build image type and size for response.
	 *
	 * @since 9.3.3
	 *
	 * @param cnEntry_HTML    $entry   Entry object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array
	 */
	public function prepare_images_for_response( cnEntry_HTML $entry, WP_REST_Request $request ): array {

		$requestParams = $request->get_params();
		$valid         = array(
			'logo'  => array( 'original', 'scaled' ),
			'photo' => array( 'thumbnail', 'medium', 'large', 'original' ),
		);
		$requested     = array();
		$meta          = array();

		// Parse REST request.
		if ( _array::exists( $requestParams, '_images' ) ) {

			$images = _array::get( $requestParams, '_images', array() );

			// Not an array request likely invalid or not formatted correctly, return empty array.
			if ( ! is_array( $images ) ) {
				return $meta;
			}

			foreach ( $images as $image ) {

				// Not an array request likely invalid or not formatted correctly, continue to next item in image request.
				if ( ! is_array( $image ) ) {
					continue;
				}

				// If type does not exist, continue to next item in image request.
				if ( ! _array::exists( $image, 'type' ) ) {
					continue;
				}

				$type = _array::get( $image, 'type' );

				// Not a valid image type, continue to next item in image request.
				if ( ! in_array( $type, array( 'logo', 'photo' ) ) ) {
					continue;
				}

				// If a size is requested, parse it, if not, return all valid sizes.
				if ( _array::exists( $image, 'size' ) ) {

					$size = _array::get( $image, 'size' );

					// if the requested size is valid, add it to the requested images.
					if ( in_array( $size, $valid[ $type ] ) ) {

						array_push(
							$requested,
							array(
								'type' => $type,
								'size' => $size,
								// 'zc'   => _array::get( $image, 'zc', 1 ),
							)
						);

					} elseif ( 'custom' === $size ) {

						// Get image by custom size.
						array_push(
							$requested,
							array(
								'type'   => $type,
								'size'   => 'custom',
								'width'  => absint( _array::get( $image, 'width' ) ),
								'height' => absint( _array::get( $image, 'height' ) ),
								'zc'     => absint( _array::get( $image, 'zc', 1 ) ),
							)
						);
					}

				} else {

					// So image size specified, return all standard image size for the requested image type.
					foreach ( $valid[ $type ] as $size ) {

						array_push(
							$requested,
							array(
								'type' => $type,
								'size' => $size,
							)
						);
					}
				}

			}

		} else {

			// No images specified, return all standard images sizes.
			foreach ( $valid as $type => $sizes ) {

				foreach ( $sizes as $size ) {

					array_push(
						$requested,
						array(
							'type' => $type,
							'size' => $size,
						)
					);
				}
			}
		}

		// Process REST request.
		foreach ( $requested as $data ) {

			$type   = _array::get( $data, 'type' );
			$size   = _array::get( $data, 'size', 'original' );
			$width  = _array::get( $data, 'width', 0 );
			$height = _array::get( $data, 'height', 0 );
			$crop   = _array::get( $data, 'zc', 1 );

			$image = $entry->getImageMeta(
				array(
					'type'      => $type,
					'size'      => $size,
					'width'     => $width,
					'height'    => $height,
					'crop_mode' => $crop,
				)
			);

			if ( ! is_wp_error( $image ) ) {

				$preset = array(
					'custom'    => null,
					'thumbnail' => 'thumbnail',
					'medium'    => 'entry',
					'large'     => 'profile',
					'original'  => 'original',
				);

				_array::forget( $image, 'log' );
				_array::forget( $image, 'path' );
				_array::forget( $image, 'source' );
				_array::forget( $image, 'type' );

				$image = _array::add(
					$image,
					'rendered',
					$entry->getImage(
						array(
							'image'  => $type,
							'preset' => $preset[ $size ],
							'width'  => $width,
							'height' => $height,
							'zc'     => $crop,
							'return' => true,
						)
					)
				);

				$meta = _array::add( $meta, "{$type}.{$size}", $image );
			}

		}

		return $meta;
	}

	/**
	 * Get the entry's schema, conforming to JSON Schema.
	 *
	 * Schema based on JSON Schema examples and hCard microformat spec which itself is based off the vCard 4 spec.
	 * Uses underscores as spaces to match WP core naming.
	 *  - JSON Schema example @link http://json-schema.org/card
	 *  - hCard Spec @link http://microformats.org/wiki/h-card
	 *
	 * Resource links:
	 *
	 * @link https://timothybjacobs.com/2017/05/17/json-schema-and-the-wp-rest-api/
	 * JSON Schema Validator @link http://www.jsonschemavalidator.net/
	 *
	 * @since 8.5.26
	 *
	 * @return array
	 */
	public function get_item_schema(): array {

		/*
		 * Returned cached copy whenever available.
		 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#caching-schema
		 */
		if ( $this->schema ) {

			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'description' => 'A representation of a person, company, organization, or place',
			'title'       => $this->rest_base,
			'type'        => 'object',
			'properties'  => array(
				'id' => array(
					'description' => __( 'Unique identifier for the entry.', 'connections' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'type' => array(
					'description' => __( 'Type of entry.', 'connections' ),
					'type'        => 'string',
					'enum'        => array( 'family', 'individual', 'organization' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'slug' => array(
					'description' => __( 'An alphanumeric identifier for the object unique to its type.', 'connections' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'link' => array(
					'description' => __( 'URL to the object.', 'connections' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'fn' => array(
					'description' => __( 'The full formatted name of the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Name of the entry, as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML name of the entry, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'honorific_prefix' => array(
					'description' => __( 'An honorific prefix preceding a person\'s name such as Dr/Mrs/Mr.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Honorific prefix as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML honorific prefix, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'given_name' => array(
					'description' => __( 'Given name. In the U.S., the first name of a person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'First name as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML first name, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'additional_name' => array(
					'description' => __( 'An additional name for a person. The middle name of a person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Middle name as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML middle name, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'family_name' => array(
					'description' => __( 'Family name. In the U.S., the last name of an person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Last name as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML last name, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'honorific_suffix' => array(
					'description' => __( 'An honorific suffix preceding a person\'s name such as M.D. or PhD.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Honorific suffix as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML honorific suffix, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'job_title' => array(
					'description' => __( 'The job title of the person (for example, Financial Manager).', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Job title as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML job title, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'org' => array(
					'description' => __( 'The organization object for the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type'       => 'object',
							'properties' => array(
								'organization_name' => array(
									'type' => 'string',
								),
								'organization_unit' => array(
									'type' => 'string',
								),
							),
						),
					),
					'properties'  => array(
						'organization_name' => array(
							'type'       => 'object',
							'properties' => array(
								'raw'      => array(
									'description' => __( 'Organization name as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML organization name, transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
									'readonly'    => true,
								),
							),
						),
						'organization_unit' => array(
							'type'       => 'object',
							'properties' => array(
								'raw'      => array(
									'description' => __( 'Department (organization unit) as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML department (organization unit), transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
				'contact' => array(
					'description' => __( 'The contact name object for the entry of type organization.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type'       => 'object',
							'properties' => array(
								'given_name'  => array(
									'type' => 'string',
								),
								'family_name' => array(
									'type' => 'string',
								),
							),
						),
					),
					'properties'  => array(
						'given_name' => array(
							'type'       => 'object',
							'properties' => array(
								'raw'      => array(
									'description' => __( 'First name as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML first name, transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
									'readonly'    => true,
								),
							),
						),
						'family_name' => array(
							'type'       => 'object',
							'properties' => array(
								'raw'      => array(
									'description' => __( 'Last name as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML last name, transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
				'adr' => array(
					'description' => __( 'The addresses attached to an entry.', 'connections' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'                 => array(
								'description' => __( 'Unique identifier for the address.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'order'              => array(
								'description' => __( 'The display order of the address.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'preferred'          => array(
								'description' => __( 'Whether or not the address is the preferred address.', 'connections' ),
								'type'        => 'bool',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'type'               => array(
								'description' => __( 'Address type.', 'connections' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'enum'        => array_keys( cnOptions::getAddressTypeOptions() ),
							),
							'street_address'     => array(
								'description' => __( 'Address line one.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address line one, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'extended_address'   => array(
								'description' => __( 'Address line two.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address line two, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'extended_address_2' => array(
								'description' => __( 'Address line three.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address line three, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'extended_address_3' => array(
								'description' => __( 'Address line four.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address line four, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'district'           => array(
								'description' => __( 'Address district.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address district, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'county'             => array(
								'description' => __( 'Address county.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address county, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'locality'           => array(
								'description' => __( 'Address locality.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address locality, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'region'             => array(
								'description' => __( 'Address region.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address region, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'postal_code'        => array(
								'description' => __( 'Address post code.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address post code, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'country_name'       => array(
								'description' => __( 'Address country.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'HTML address country, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
									),
								),
							),
							'latitude'           => array(
								'description' => __( 'Address latitude.', 'connections' ),
								'type'        => 'number',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'longitude'          => array(
								'description' => __( 'Address longitude.', 'connections' ),
								'type'        => 'number',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'visibility'         => array(
								'description' => __( 'Visibility of the address.', 'connections' ),
								'type'        => 'string',
								'enum'        => array( 'public', 'private', 'unlisted' ),
								'context'     => array( 'view', 'edit', 'embed' ),
							),
						),
					),
				),
				'tel' => array(
					'description' => __( 'The telephone numbers attached to an entry.', 'connections' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'        => array(
								'description' => __( 'Unique identifier for the phone number.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'order'     => array(
								'description' => __( 'The display order of the phone number.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'preferred' => array(
								'description' => __( 'Whether or not the phone number is the preferred phone number.', 'connections' ),
								'type'        => 'bool',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'type'      => array(
								'description' => __( 'Phone number type.', 'connections' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'enum'        => array_keys( cnOptions::getPhoneTypeOptions() ),
							),
							'number'    => array(
								'description' => __( 'The phone number.', 'connections' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'Phone number, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
										// 'readonly'    => true,
									),
								),
							),
						),
					),
				),
				'email' => array(
					'description' => __( 'The email addresses attached to an entry.', 'connections' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'        => array(
								'description' => __( 'Unique identifier for the email address.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'order'     => array(
								'description' => __( 'The display order of the email address.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'preferred' => array(
								'description' => __( 'Whether or not the email address is the preferred email address.', 'connections' ),
								'type'        => 'bool',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'type'      => array(
								'description' => __( 'Email address type.', 'connections' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'enum'        => array_keys( cnOptions::getEmailTypeOptions() ),
							),
							'address'   => array(
								'description' => __( 'The email address.', 'connections' ),
								'type'        => 'object',
								'format'      => 'email',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'rendered' => array(
										'description' => __( 'Email address, transformed for display.', 'connections' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
										// 'readonly'    => true,
									),
								),
							),
						),
					),
				),
				'social' => array(
					'description' => __( 'The social networks attached to an entry.', 'connections' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit', 'embed' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'        => array(
								'description' => __( 'Unique identifier for the social network.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'order'     => array(
								'description' => __( 'The display order of the social network.', 'connections' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'preferred' => array(
								'description' => __( 'Whether or not the social network is the preferred social network.', 'connections' ),
								'type'        => 'bool',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'type'      => array(
								'description' => __( 'Social network address type.', 'connections' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'enum'        => array_keys( cnOptions::getSocialNetworkTypeOptions() ),
							),
							'slug'      => array(
								'description' => __( 'Social network address slug.', 'connections' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'url'       => array(
								'description' => __( 'Social network URL.', 'connections' ),
								'type'        => 'string',
								'format'      => 'uri',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
						),
					),
				),
				'bio' => array(
					'description' => __( 'The biography for the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Biography for the entry, as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML biography for the entry, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'excerpt' => array(
					'description' => __( 'The excerpt for the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Excerpt for the entry, as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML excerpt for the entry, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'notes' => array(
					'description' => __( 'The notes for the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => array( __CLASS__, '_sanitize' ),
						'validate_callback' => array( __CLASS__, '_validate' ),
						'callback_schema'   => array(
							'type' => 'string',
						),
					),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Notes for the entry, as it exists in the database.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML notes for the entry, transformed for display.', 'connections' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
					),
				),
				'images' => array(
					'description' => __( 'The images attached to an entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'logo'  => array(
							'description' => __( 'The logo image attached to an entry.', 'connections' ),
							'type'        => 'object',
							'context'     => array( 'view', 'edit', 'embed' ),
							'properties'  => $this->get_image_schema_properties( 'logo' ),
						),
						'photo' => array(
							'description' => __( 'The photo image attached to an entry.', 'connections' ),
							'type'        => 'object',
							'context'     => array( 'view', 'edit', 'embed' ),
							'properties'  => $this->get_image_schema_properties( 'photo' ),
						),
					),
				),
				'visibility' => array(
					'description' => __( 'Visibility of the entry.', 'connections' ),
					'type'        => 'string',
					'enum'        => array( 'public', 'private', 'unlisted' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'status' => array(
					'description' => __( 'Moderation status of the entry.', 'connections' ),
					'type'        => 'string',
					'enum'        => array( 'approved', 'pending' ),
					'context'     => array( 'view', 'edit', 'embed' ),
				),
			),
		);

		// Cache generated schema on endpoint instance.
		$this->schema = $schema;

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the image schema.
	 *
	 * @since 9.6
	 *
	 * @param string $type The image type.
	 *                     Accepts: logo|photo
	 *
	 * @return array
	 */
	private function get_image_schema_properties( string $type ): array {

		$images = array(
			'logo'  => array( 'original', 'scaled', 'custom' ),
			'photo' => array( 'thumbnail', 'medium', 'large', 'original', 'custom' ),
		);
		$schema = array();

		foreach ( $images[ $type ] as $size ) {

			$parameters = array(
				'type'       => 'object',
				'properties' => array(
					'name'     => array(
						'description' => __( 'Image filename.', 'connections' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'url'      => array(
						'description' => __( 'Image URL.', 'connections' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'width'    => array(
						'description' => __( 'Image width in pixels.', 'connections' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'height'   => array(
						'description' => __( 'Image height in pixels.', 'connections' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'size'     => array(
						'description' => __( 'Image size attribute.', 'connections' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'mime'     => array(
						'description' => __( 'Image MINE type.', 'connections' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'rendered' => array(
						'description' => __( 'HTML image tag.', 'connections' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
				),
			);

			_array::set( $schema, $size, $parameters );
		}

		return $schema;
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 8.5.26
	 * @since 10.4.44 Add the `exclude`, `include`, `offset`, `categories`, and `categories_exclude` parameters.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {

		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$params['slug'] = array(
			'description'       => __( 'Limit result set to entries with a specific slug.', 'connections' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['exclude'] = array(
			'description' => __( 'Ensure result set excludes specific IDs.', 'connections' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);

		$query_params['include'] = array(
			'description' => __( 'Limit result set to specific IDs.', 'connections' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);

		$query_params['offset'] = array(
			'description' => __( 'Offset the result set by a specific number of items.', 'connections' ),
			'type'        => 'integer',
		);

		$query_params['tax_relation'] = array(
			'description' => __( 'Limit result set based on relationship between multiple taxonomies.', 'connections' ),
			'type'        => 'string',
			'enum'        => array(
				'AND',
				'OR',
			),
		);

		$query_params['categories'] = array(
			'description' => __(
				'Limit result set to items with specific terms assigned in the categories taxonomy.',
				'connections'
			),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);

		$query_params['categories_exclude'] = array(
			'description' => __( 'Limit result set to items except those with specific terms assigned in the categories taxonomy.', 'connections' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
			'default'     => array(),
		);

		return $query_params;
	}

	/**
	 * Callback for `sanitize_callback`.
	 *
	 * @see WP_REST_Request::sanitize_params()
	 *
	 * @internal
	 * @since 10.4.43
	 *
	 * @param mixed           $value   The value to validate.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   The parameter name, used in error messages.
	 *
	 * @return mixed|WP_Error The sanitized value or a WP_Error instance if the value cannot be safely sanitized.
	 */
	public static function _sanitize( $value, WP_REST_Request $request, string $param ) {

		$attributes = $request->get_attributes();
		$schema     = _array::get( $attributes, "args.{$param}.callback_schema", false );

		return rest_sanitize_value_from_schema( $value, $schema, $param );
	}

	/**
	 * Callback for `validate_callback`.
	 *
	 * @see WP_REST_Request::has_valid_params()
	 *
	 * @internal
	 * @since 10.4.43
	 *
	 * @param mixed           $value   The value to validate.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   The parameter name, used in error messages.
	 *
	 * @return true|WP_Error
	 */
	public static function _validate( $value, WP_REST_Request $request, string $param ) {

		$is_valid   = false;
		$attributes = $request->get_attributes();
		$schema     = _array::get( $attributes, "args.{$param}.callback_schema", false );

		if ( is_array( $schema ) ) {

			$result = rest_validate_value_from_schema( $value, $schema, $param );

			if ( true === $result ) {

				$is_valid = true;

			} else {

				return $result;
			}
		}

		return $is_valid;
	}
}
