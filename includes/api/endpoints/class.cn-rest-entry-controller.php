<?php
/**
 * REST API Entry Controller
 *
 * @author     Steven A. Zahm
 * @category   API
 * @package    Connections
 * @subpackage REST_API
 * @since      8.5.26
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Entry Controller.
 *
 * @since 8.5.26
 */
class CN_REST_Entry_Controller extends WP_REST_Controller {

	/**
	 * @since 8.5.26
	 */
	const VERSION = '1';

	/**
	 * @since 8.5.26
	 * @var string
	 */
	protected $namespace;

	/**
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
							'default'     => FALSE,
							'description' => __( 'Required to be true, as resource does not support trashing.', 'connections' ),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
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

		return TRUE;
	}

	/**
	 * Get a collection of posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$results = $this->get_entries( $request );

		$entries = array();

		foreach ( $results as $result ) {

			$entry = new cnOutput( $result );

			$data = $this->prepare_item_for_response( $entry, $request );
			$entries[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $entries );

		return $response;
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

		return TRUE;
	}

	/**
	 * Retrieves a single entry.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {

		$atts = array(
			'id' => (int) $request['id'],
		);

		$result = $this->get_entries( $request, $atts );

		if ( empty( $atts['id'] ) || empty( $result ) ) {
			return new WP_Error( 'rest_entry_invalid_id', __( 'Invalid entry ID.', 'connections' ), array( 'status' => 404 ) );
		}

		$entry = new cnOutput( $result[0] );

		$data     = $this->prepare_item_for_response( $entry, $request );
		$response = rest_ensure_response( $data );

		//if ( is_post_type_viewable( get_post_type_object( $post->post_type ) ) ) {
		//	$response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );
		//}

		return $response;
	}

	/**
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @param array           $untrusted
	 *
	 * @return array
	 */
	protected function get_entries( $request, $untrusted = array() ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$categoryIn = cnArray::get( $request, 'category_in', FALSE );
		cnFormatting::toBoolean( $categoryIn );

		$category = $categoryIn ? 'category_in' : 'category';

		$defaults = array(
			'list_type'        => cnArray::get( $request, 'type', NULL ),
			$category          => cnArray::get( $request, 'categories', NULL ),
			'exclude_category' => cnArray::get( $request, 'categories_exclude', NULL ),
			'id'               => cnArray::get( $request, 'id', NULL ),
			'limit'            => cnArray::get( $request, 'per_page', 10 ),
			'offset'           => cnArray::get( $request, 'offset', 0 ),
		);

		$atts = cnSanitize::args( $untrusted, $defaults );

		$results = $instance->retrieve->entries( $atts );

		return $results;
	}

	/**
	 * Prepare a single entry output for response.
	 *
	 * @param cnOutput        $entry   Post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response $data
	 */
	public function prepare_item_for_response( $entry, $request ) {

		$requestParams = $request->get_params();
		$data = array();

		cnArray::set( $data, 'id', $entry->getId() );
		cnArray::set( $data, 'type', $entry->getEntryType() );
		cnArray::set( $data, 'slug', $entry->getSlug() );

		cnArray::set( $data, 'name.rendered', $entry->getName() );

		cnArray::set( $data, 'honorific_prefix.rendered', $entry->getHonorificPrefix() );
		cnArray::set( $data, 'given_name.rendered', $entry->getFirstName() );
		cnArray::set( $data, 'additional_name.rendered', $entry->getMiddleName() );
		cnArray::set( $data, 'family_name.rendered', $entry->getLastName() );
		cnArray::set( $data, 'honorific_suffix.rendered', $entry->getHonorificSuffix() );

		cnArray::set( $data, 'job_title.rendered', $entry->getTitle() );
		cnArray::set( $data, 'org.organization_name.rendered', $entry->getOrganization() );
		cnArray::set( $data, 'org.organization_unit.rendered', $entry->getDepartment() );

		cnArray::set( $data, 'contact.given_name.rendered', $entry->getContactFirstName() );
		cnArray::set( $data, 'contact.family_name.rendered', $entry->getContactLastName() );

		$data = $this->prepare_address_for_response( $entry, $request, $data );
		$data = $this->prepare_phone_for_response( $entry, $request, $data );
		$data = $this->prepare_email_for_response( $entry, $request, $data );
		$data = $this->prepare_social_for_response( $entry, $request, $data );

		cnArray::set( $data, 'bio.rendered', $entry->getBio() );
		cnArray::set( $data, 'notes.rendered', $entry->getNotes() );

		$excerptParameters = array(
			'length' => cnArray::get( $requestParams, '_excerpt.length', apply_filters( 'cn_excerpt_length', 55 ) ),
			'more'   => cnArray::get( $requestParams, '_excerpt.more', '' ),
		);

		cnArray::set( $data, 'excerpt.rendered', wpautop( $entry->getExcerpt( $excerptParameters ) ) );

		if ( 'edit' === $request['context'] &&
		     ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
		) {

			cnArray::set( $data, 'name.raw', $entry->getName( array(), 'raw' ) );
			cnArray::set( $data, 'honorific_prefix.raw', $entry->getHonorificPrefix( 'raw' ) );
			cnArray::set( $data, 'given_name.raw', $entry->getFirstName( 'raw' ) );
			cnArray::set( $data, 'additional_name.raw', $entry->getMiddleName( 'raw' ) );
			cnArray::set( $data, 'family_name.raw', $entry->getLastName( 'raw' ) );
			cnArray::set( $data, 'honorific_suffix.raw', $entry->getHonorificSuffix( 'raw' ) );

			cnArray::set( $data, 'job_title.raw', $entry->getTitle( 'raw' ) );
			cnArray::set( $data, 'org.organization_name.raw', $entry->getOrganization( 'raw' ) );
			cnArray::set( $data, 'org.organization_unit.raw', $entry->getDepartment( 'raw' ) );

			cnArray::set( $data, 'contact.given_name.raw', $entry->getContactFirstName( 'raw' ) );
			cnArray::set( $data, 'contact.family_name.raw', $entry->getContactLastName( 'raw' ) );

			cnArray::set( $data, 'bio.raw', $entry->getBio( 'raw' ) );
			cnArray::set( $data, 'notes.raw', $entry->getNotes( 'raw' ) );

			cnArray::set( $data, 'excerpt.raw', $entry->getExcerpt( $excerptParameters, 'raw' ) );
		}

		$data['images'] = $this->prepare_images_for_response( $entry, $request );

		cnArray::set( $data, 'visibility', $entry->getVisibility() );
		cnArray::set( $data, 'status', $entry->getStatus() );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Prepare phone numbers for response.
	 *
	 * @param cnEntry         $entry   Post object.
	 * @param WP_REST_Request $request Request object.
	 * @param array           $data
	 *
	 * @return array $data
	 */
	private function prepare_phone_for_response( $entry, $request, $data ) {

		$numbers = $entry->getPhoneNumbers( array(), TRUE, FALSE, 'raw' );

		if ( empty( $numbers ) ) return $data;

		$objects = array();

		foreach ( $numbers as $phone ) {

			$object = array(
				'id'               => $phone->id,
				'order'            => $phone->order,
				'preferred'        => $phone->preferred,
				'type'             => $phone->type,
			);

			cnArray::set(
				$object,
				'number.rendered',
				cnSanitize::field( 'phone-number', $phone->number, 'display' )
			);

			if ( 'edit' === $request['context'] &&
			     ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				cnArray::set( $object, 'number.raw', $phone->number );
			}

			array_push( $objects, $object );
		}

		cnArray::set( $data, 'tel', $objects );

		return $data;
	}

	/**
	 * Prepare email addresses for response.
	 *
	 * @param cnEntry         $entry   Post object.
	 * @param WP_REST_Request $request Request object.
	 * @param array           $data
	 *
	 * @return array $data
	 */
	private function prepare_email_for_response( $entry, $request, $data ) {

		$emailAddresses = $entry->getEmailAddresses( array(), TRUE, FALSE, 'raw' );

		if ( empty( $emailAddresses ) ) return $data;

		$objects = array();

		foreach ( $emailAddresses as $email ) {

			$object = array(
				'id'               => $email->id,
				'order'            => $email->order,
				'preferred'        => $email->preferred,
				'type'             => $email->type,
			);

			cnArray::set(
				$object,
				'address.rendered',
				sanitize_email(  $email->address )
			);

			if ( 'edit' === $request['context'] &&
			     ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				cnArray::set( $object, 'address.raw', $email->address );
			}

			array_push( $objects, $object );
		}

		cnArray::set( $data, 'email', $objects );

		return $data;
	}

	/**
	 * Prepare social networks for response.
	 *
	 * @param cnEntry         $entry   Post object.
	 * @param WP_REST_Request $request Request object.
	 * @param array           $data
	 *
	 * @return array $data
	 */
	private function prepare_social_for_response( $entry, $request, $data ) {

		$networks = $entry->getSocialMedia( array(), TRUE, FALSE, 'raw' );

		if ( empty( $networks ) ) return $data;

		$objects = array();

		foreach ( $networks as $network ) {

			$socialNetworks = cnOptions::getRegisteredSocialNetworkTypes();

			$object = array(
				'id'        => $network->id,
				'order'     => $network->order,
				'preferred' => $network->preferred,
				'slug'      => $socialNetworks[ $network->type ]['slug'],
				'type'      => $network->type,
			);

			cnArray::set(
				$object,
				'url',
				cnSanitize::field( 'url', $network->url, 'display' )
			);

			//if ( 'edit' === $request['context'] &&
			//     ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			//) {
			//
			//	cnArray::set( $object, 'url.raw', $network->address );
			//}

			array_push( $objects, $object );
		}

		cnArray::set( $data, 'social', $objects );

		return $data;
	}

	/**
	 * Build image type and size for response.
	 *
	 * @since 9.3.3
	 *
	 * @param cnOutput        $entry    Entry object.
	 * @param WP_REST_Request $request  Request object.
	 *
	 * @return array
	 */
	public function prepare_images_for_response( $entry, $request ) {

		$requestParams = $request->get_params();
		$valid         = array(
			'logo'  => array( 'original', 'scaled' ),
			'photo' => array( 'thumbnail', 'medium', 'large', 'original' ),
		);
		$requested     = array();
		$meta          = array();

		// Parse REST request.
		if ( cnArray::exists( $requestParams, '_images' ) ) {

			$images = cnArray::get( $requestParams, '_images', array() );

			// Not an array request likely invalid or not formatted correctly, return empty array.
			if ( ! is_array( $images ) ) return $meta;

			foreach ( $images as $image ) {

				// Not an array request likely invalid or not formatted correctly, continue to next item in image request.
				if ( ! is_array( $image ) ) continue;

				// If type does not exist, continue to next item in image request.
				if ( ! cnArray::exists( $image, 'type' ) ) continue;

				$type = cnArray::get( $image, 'type' );

				// Not a valid image type, continue to next item in image request.
				if ( ! in_array( $type, array( 'logo', 'photo' ) ) ) continue;

				// If a size is requested, parse it, if not, return all valid sizes.
				if ( cnArray::exists( $image, 'size' ) ) {

					$size = cnArray::get( $image, 'size' );

					// if the requested size is valid, add it to the requested images.
					if ( in_array( $size, $valid[ $type ] ) ) {

						array_push(
							$requested,
							array(
								'type' => $type,
								'size' => $size,
								//'zc'   => cnArray::get( $image, 'zc', 1 ),
							)
						);

					} elseif ( 'custom' === $size ) {

						// Get image by custom size.
						array_push(
							$requested,
							array(
								'type'   => $type,
								'size'   => 'custom',
								'width'  => absint( cnArray::get( $image, 'width' ) ),
								'height' => absint( cnArray::get( $image, 'height' ) ),
								'zc'     => absint( cnArray::get( $image, 'zc', 1 ) ),
							)
						);
					}

				} else {

					// So image size specified, return all standard image size for the requested image type.
					foreach ( $valid[ $type ] as $size ) {

						array_push( $requested, array( 'type' => $type, 'size' => $size ) );
					}
				}

			}

		} else {

			// No images specified, return all standard images sizes.
			foreach ( $valid as $type => $sizes ) {

				foreach ( $sizes as $size ) {

					array_push( $requested, array( 'type' => $type, 'size' => $size ) );
				}
			}
		}

		// Process REST request.
		foreach ( $requested as $data ) {

			$type   = cnArray::get( $data, 'type' );
			$size   = cnArray::get( $data, 'size', 'original' );
			$width  = cnArray::get( $data, 'width', 0 );
			$height = cnArray::get( $data, 'height', 0 );
			$crop   = cnArray::get( $data, 'zc', 1 );

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
					'thumbnail' => 'thumbnail',
					'medium'    => 'entry',
					'large'     => 'profile',
					'original'  => 'original',
				);

				cnArray::forget( $image, 'log' );
				cnArray::forget( $image, 'path' );
				cnArray::forget( $image, 'source' );
				cnArray::forget( $image, 'type' );

				$image = cnArray::add(
					$image,
					'rendered',
					$entry->getImage(
						array(
							'image'  => $type,
							'preset' => $preset[ $size ],
							'width'  => $width,
							'height' => $height,
							'zc'     => $crop,
							'return' => TRUE,
						)
					)
				);

				$meta = cnArray::add( $meta, "{$type}.{$size}", $image );
			}

		}

		return $meta;
	}

	/**
	 * Prepare addresses for response.
	 *
	 * @param cnEntry         $entry   Post object.
	 * @param WP_REST_Request $request Request object.
	 * @param array           $data
	 *
	 * @return array $data
	 */
	private function prepare_address_for_response( $entry, $request, $data ) {

		$addresses = $entry->getAddresses( array(), TRUE, FALSE, 'raw' );

		if ( empty( $addresses ) ) return $data;

		$objects = array();

		foreach ( $addresses as $address ) {

			$object = array(
				'id'               => $address->id,
				'order'            => $address->order,
				'preferred'        => $address->preferred,
				'type'             => $address->type,
			);

			cnArray::set(
				$object,
				'street_address.rendered',
				cnSanitize::field( 'street', $address->line_1, 'display' )
			);

			cnArray::set(
				$object,
				'extended_address.rendered',
				cnSanitize::field( 'street', $address->line_2, 'display' )
			);

			cnArray::set(
				$object,
				'extended_address_2.rendered',
				cnSanitize::field( 'street', $address->line_3, 'display' )
			);

			cnArray::set(
				$object,
				'extended_address_3.rendered',
				cnSanitize::field( 'street', $address->line_4, 'display' )
			);

			cnArray::set(
				$object,
				'district.rendered',
				cnSanitize::field( 'district', $address->district, 'display' )
			);

			cnArray::set(
				$object,
				'county.rendered',
				cnSanitize::field( 'county', $address->county, 'display' )
			);

			cnArray::set(
				$object,
				'locality.rendered',
				cnSanitize::field( 'locality', $address->city, 'display' )
			);

			cnArray::set(
				$object,
				'region.rendered',
				cnSanitize::field( 'region', $address->state, 'display' )
			);

			cnArray::set(
				$object,
				'postal_code.rendered',
				cnSanitize::field( 'postal-code', $address->zipcode, 'display' )
			);

			cnArray::set(
				$object,
				'country_name.rendered',
				cnSanitize::field( 'country', $address->country, 'display' )
			);

			if ( 'edit' === $request['context'] &&
			     ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
			) {

				cnArray::set( $object, 'street_address.raw', $address->line_1 );
				cnArray::set( $object, 'extended_address.raw', $address->line_2 );
				cnArray::set( $object, 'extended_address_2.raw', $address->line_3 );
				cnArray::set( $object, 'extended_address_3.raw', $address->line_4 );
				cnArray::set( $object, 'district.raw', $address->district );
				cnArray::set( $object, 'county.raw', $address->county );
				cnArray::set( $object, 'locality.raw', $address->city );
				cnArray::set( $object, 'region.raw', $address->state );
				cnArray::set( $object, 'postal_code.raw', $address->zipcode );
				cnArray::set( $object, 'country_name.raw', $address->country );
			}

			cnArray::set( $object, 'latitude', $address->latitude );
			cnArray::set( $object, 'longitude', $address->longitude );
			cnArray::set( $object, 'visibility', $address->visibility );

			array_push( $objects, $object );
		}

		cnArray::set( $data, 'adr', $objects );

		return $data;
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
	 * JSON Schema Validator @link http://www.jsonschemavalidator.net/
	 *
	 * @since 8.5.26
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'description' => 'A representation of a person, company, organization, or place',
			'title'       => $this->rest_base,
			'type'        => 'object',
			'properties'  => array(
				'id'   => array(
					'description' => __( 'Unique identifier for the entry.', 'connections' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => TRUE,
				),
				'type' => array(
					'description' => __( 'Type of entry.', 'connections' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => TRUE,
				),
				'slug' => array(
					'description' => __( 'An alphanumeric identifier for the object unique to its type.', 'connections' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
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
						),
					),
				),
				'honorific_prefix' => array(
					'description' => __( 'An honorific prefix preceding a person\'s name such as Dr/Mrs/Mr.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
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
						),
					),
				),
				'given_name' => array(
					'description' => __( 'Given name. In the U.S., the first name of a person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
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
						),
					),
				),
				'additional_name' => array(
					'description' => __( 'An additional name for a person. The middle name of a person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
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
						),
					),
				),
				'family_name' => array(
					'description' => __( 'Family name. In the U.S., the last name of an person.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
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
						),
					),
				),
				'honorific_suffix' => array(
					'description' => __( 'An honorific suffix preceding a person\'s name such as M.D. /PhD/MSCSW.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
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
						),
					),
				),
				'job_title' => array(
					'description' => __( 'The job title of the person (for example, Financial Manager).', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
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
						),
					),
				),
				'org' => array(
					'description' => __( 'The organization object for the entry.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'organization_name' => array(
							'type'        => 'object',
							'properties'  => array(
								'raw'      => array(
									'description' => __( 'Organization name as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML organization name, transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
								),
							),
						),
						'organization_unit' => array(
							'type'        => 'object',
							'properties'  => array(
								'raw'      => array(
									'description' => __( 'Department (organization unit) as it exists in the database.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML department (organization unit), transformed for display.', 'connections' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
								),
							),
						),
					),
				),
				'contact' => array(
					'description' => __( 'The contact name object for the entry of type organization.', 'connections' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'given_name' => array(
							'type'        => 'object',
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
								),
							),
						),
						'family_name' => array(
							'type'        => 'object',
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
								),
							),
						),
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 8.5.26
	 *
	 * @return array
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$params['slug'] = array(
			'description'       => __( 'Limit result set to entries with a specific slug.', 'connections' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}
}
