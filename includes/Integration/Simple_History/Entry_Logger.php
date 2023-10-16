<?php
/**
 * Log entry related events such as create, delete, and modify.
 *
 * @since 10.4.53
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory\Integration\Simple_History
 * @subpackage Connections_Directory\Integration\Simple_History\Entry_Logger
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Integration\Simple_History;

use cnEntry as Entry;
use cnOptions;
use Connections_Directory\Taxonomy;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_nonce;
use Connections_Directory\Utility\_validate;
use Simple_History\Event_Details\Event_Details_Container_Interface;
use Simple_History\Event_Details\Event_Details_Group;
use Simple_History\Helpers;
use Simple_History\Log_Initiators;
use Simple_History\Loggers\Logger;
use Simple_History\Simple_History;
use WP_User;

/**
 * Class Entry_Logger
 *
 * @package Connections_Directory\Integration\Simple_History
 */
final class Entry_Logger extends Logger {

	/**
	 * The log entry data, such as the diff when an entry is updated.
	 *
	 * @since 10.4.53
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Unique slug for the logger.
	 *
	 * The slug will be saved in DB and used to associate each log row with its logger.
	 *
	 * @since 10.4.53
	 *
	 * @var string
	 */
	protected $slug = 'CBD/Entry';

	/**
	 * Logger constructor.
	 *
	 * @since 10.4.53
	 *
	 * @param Simple_History $simple_history Simple History instance.
	 */
	public function __construct( $simple_history = null ) {

		parent::__construct( $simple_history );
	}

	/**
	 * Get array with information about this logger.
	 *
	 * @since 10.4.53
	 *
	 * @return array{
	 *     name: string,
	 *     description: string,
	 *     capability: string,
	 *     name_via: string,
	 *     messages: array{string[]},
	 *     labels: array{
	 *          search: array{
	 *              label: string,
	 *              label_all: string,
	 *              options: string[]
	 *          }
	 *      }
	 * }
	 */
	public function get_info(): array {

		return array(
			'name'        => _x( 'Plugin: Connections Business Directory Entry Logger', 'Logger: Connections Business Directory', 'connections' ),
			'description' => _x( 'Log Connections Business Directory Entry Activity.', 'Logger: Connections Business Directory', 'connections' ),
			// Set the capability required to read the events recorded by this logger.
			'capability'  => 'manage_options',
			'name_via'    => _x( 'Using plugin Connections Business Directory.', 'Logger: Connections Business Directory', 'connections' ),
			'messages'    => array(
				// [key == Action or Filter tag name => value == Log message]
				'Connections_Directory/Entry/Deleted' => _x( 'Deleted Entry: "{entry_name}"', 'Logger: Connections Business Directory', 'connections' ),
				'Connections_Directory/Entry/Saved'   => _x( 'Created Entry: "{entry_name}"', 'Logger: Connections Business Directory', 'connections' ),
				'Connections_Directory/Entry/Updated' => _x( 'Updated Entry: "{entry_name}"', 'Logger: Connections Business Directory', 'connections' ),
			),
			'labels'      => array(
				'search' => array(
					'label'     => _x( 'Connections Business Directory Entry Activity', 'Directory Entry logger: search', 'connections' ),
					'label_all' => _x( 'All Directory Entry Activity', 'Directory Entry logger: search', 'connections' ),
					'options'   => array(
						_x( 'Directory Entries Created', 'Entry logger: search', 'connections' )  => array( 'Connections_Directory/Entry/Saved' ),
						_x( 'Directory Entries Updated', 'Entry logger: search', 'connections' )  => array( 'Connections_Directory/Entry/Updated' ),
						_x( 'Directory Entries Deleted', 'Entry logger: search', 'connections' )  => array( 'Connections_Directory/Entry/Deleted' ),
					),
				),
			),
		);
	}

	/**
	 * Add hooks to register callbacks for to log events.
	 *
	 * @since 10.4.53
	 *
	 * @return void
	 */
	public function loaded() {

		add_action( 'Connections_Directory/Entry/Deleted', array( $this, 'entryDeleted' ) );
		add_action( 'Connections_Directory/Entry/Saved', array( $this, 'entrySaved' ) );
		add_action( 'Connections_Directory/Entry/Update/Before', array( $this, 'entryUpdated' ) );
		add_action( 'Connections_Directory/Entry/Action/Set_Status/Before', array( $this, 'entryStatusUpdated' ), 10, 2 );
		add_action( 'Connections_Directory/Entry/Action/Set_Visibility/Before', array( $this, 'entryVisibilityUpdated' ), 10, 2 );

		add_action( 'cn_set_object_terms', array( $this, 'entryTaxonomyTermsUpdated' ), 10, 6 );

		add_action( 'Connections_Directory/Entry/Action/Saved', array( $this, 'logEntrySaved' ), 10, 3 );
		add_action( 'Connections_Directory/Entry/Action/Saved', array( $this, 'logEntryUpdated' ), 10, 3 );
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Deleted` action.
	 *
	 * Records a log event when an entry is deleted.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param Entry $entry Instance of cnEntry.
	 *
	 * @return void
	 */
	public function entryDeleted( Entry $entry ) {

		$user = wp_get_current_user();

		if ( ! $user instanceof WP_User ) {

			return;
		}

		$this->info_message(
			'Connections_Directory/Entry/Deleted',
			array(
				'_initiator'  => Log_Initiators::WP_USER,
				'_user_id'    => $user->ID,
				'_user_login' => $user->user_login,
				'_user_email' => $user->user_email,
				'user_id'     => $user->ID,
				'entry_id'    => $entry->getId(),
				'entry_name'  => $entry->getName(),
			)
		);
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Action/Saved` action.
	 *
	 * Records a log event when an entry is created.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param Entry  $entry  Instance of cnEntry.
	 * @param string $action The current entry action such as `add` and `update`.
	 * @param array  $data   The raw entry data. Consider this unsafe and should be sanitized and validated before use.
	 *
	 * @return void
	 */
	public function logEntrySaved( Entry $entry, string $action, array $data ) {

		if ( 'add' !== $action ) {

			return;
		}

		$user = wp_get_current_user();

		if ( ! $user instanceof WP_User ) {

			return;
		}

		$this->info_message(
			'Connections_Directory/Entry/Saved',
			array(
				'_initiator'  => Log_Initiators::WP_USER,
				'_user_id'    => $user->ID,
				'_user_login' => $user->user_login,
				'_user_email' => $user->user_email,
				'user_id'     => $user->ID,
				'entry_id'    => $entry->getId(),
				'entry_name'  => $entry->getName(),
				'entry_data'  => _array::get( $this->data, $entry->getId(), array() ),
			)
		);
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Action/Saved` action hook.
	 *
	 * Records a log event when an entry is updated.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param Entry  $entry  Instance of cnEntry.
	 * @param string $action The current entry action such as `add` and `update`.
	 * @param array  $data   The raw entry data. Consider this unsafe and should be sanitized and validated before use.
	 *
	 * @return void
	 */
	public function logEntryUpdated( Entry $entry, string $action, array $data ) {

		if ( 'update' !== $action ) {

			return;
		}

		$user = wp_get_current_user();

		if ( ! $user instanceof WP_User ) {

			return;
		}

		$this->info_message(
			'Connections_Directory/Entry/Updated',
			array(
				'_initiator'  => Log_Initiators::WP_USER,
				'_user_id'    => $user->ID,
				'_user_login' => $user->user_login,
				'_user_email' => $user->user_email,
				'user_id'     => $user->ID,
				'entry_id'    => $entry->getId(),
				'entry_name'  => $entry->getName(),
				'entry_diff'  => _array::get( $this->data, $entry->getId(), array() ),
			)
		);
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Action/Set_Status/Before` action.
	 *
	 * Records a log event when an entry is moderation status is updated.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param int[]  $ids    An array of Entry IDs to update the moderation status.
	 * @param string $status The new entry moderation status.
	 *
	 * @return void
	 */
	public function entryStatusUpdated( array $ids, string $status ) {

		$user = wp_get_current_user();

		if ( ! $user instanceof WP_User ) {

			return;
		}

		foreach ( $ids as $id ) {

			$entry  = new Entry();
			$result = $entry->set( $id );

			if ( false === $result ) {

				continue;
			}

			$this->info_message(
				'Connections_Directory/Entry/Updated',
				array(
					'_initiator'  => Log_Initiators::WP_USER,
					'_user_id'    => $user->ID,
					'_user_login' => $user->user_login,
					'_user_email' => $user->user_email,
					'user_id'     => $user->ID,
					'entry_id'    => $entry->getId(),
					'entry_name'  => $entry->getName(),
					'entry_diff'  => array(
						'status' => array(
							'previous' => $entry->getStatus(),
							'current'  => $status,
						),
					),
				)
			);
		}
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Action/Set_Visibility/Before` action.
	 *
	 * Records a log event when an entry is visibility is updated.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param int[]  $ids        An array of Entry IDs to update the visibility.
	 * @param string $visibility The new entry visibility.
	 *
	 * @return void
	 */
	public function entryVisibilityUpdated( array $ids, string $visibility ) {

		$user = wp_get_current_user();

		if ( ! $user instanceof WP_User ) {

			return;
		}

		foreach ( $ids as $id ) {

			$entry  = new Entry();
			$result = $entry->set( $id );

			if ( false === $result ) {

				continue;
			}

			$this->info_message(
				'Connections_Directory/Entry/Updated',
				array(
					'_initiator'  => Log_Initiators::WP_USER,
					'_user_id'    => $user->ID,
					'_user_login' => $user->user_login,
					'_user_email' => $user->user_email,
					'user_id'     => $user->ID,
					'entry_id'    => $entry->getId(),
					'entry_name'  => $entry->getName(),
					'entry_diff'  => array(
						'visibility' => array(
							'previous' => $entry->getVisibility(),
							'current'  => $visibility,
						),
					),
				)
			);
		}
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Saved` action.
	 *
	 * Records the entry diff to the logger data when an entry is updated.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param Entry $current Instance of cnEntry.
	 *
	 * @return void
	 */
	public function entrySaved( Entry $current ) {

		$this->addData( $current );
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Update/Before` action.
	 *
	 * Records the entry diff to the logger data when an entry is updated.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param Entry $current Instance of cnEntry.
	 *
	 * @return void
	 */
	public function entryUpdated( Entry $current ) {

		// Set up an instance of the cnEntry object with the previous data.
		$previous = new Entry();
		$result   = $previous->set( $current->getId() );

		if ( false === $result ) {

			return;
		}

		$this->addDiff( $previous, $current );
	}

	/**
	 * Callback for the `cn_set_object_terms` action hook.
	 *
	 * Records the taxonomy terms diff to the logger data when an entry is updated.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param int    $entryID    The Entry ID.
	 * @param int[]  $terms      An array of object terms.
	 * @param int[]  $tt_ids     An array of term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether to append new terms to the old terms.
	 * @param int[]  $old_tt_ids Old array of term taxonomy IDs.
	 *
	 * @return void
	 */
	public function entryTaxonomyTermsUpdated( int $entryID, array $terms, array $tt_ids, string $taxonomy, bool $append, array $old_tt_ids ) {

		_array::set(
			$this->data,
			"{$entryID}.taxonomies.{$taxonomy}",
			array(
				'current'  => $tt_ids,
				'previous' => $old_tt_ids,
			)
		);
	}

	/**
	 * Create an array with the entry data.
	 *
	 * @since 10.4.53
	 *
	 * @param Entry $current The instance of the current Entry data.
	 *
	 * @return void
	 */
	protected function addData( Entry $current ) {

		$data = array();

		$data['type']         = $current->getEntryType();
		$data['status']       = $current->getStatus();
		$data['visibility']   = $current->getVisibility();
		$data['name']         = $current->getName();
		$data['organization'] = $current->getOrganization();
		$data['department']   = $current->getDepartment();
		$data['title']        = $current->getTitle();
		$data['contact_name'] = $current->getContactName();
		$data['bio']          = $current->getBio();
		$data['notes']        = $current->getNotes();
		$data['excerpt']      = $current->getExcerpt( array(), 'edit' );

		if ( 0 < strlen( $current->getLogoName() ) ) {

			$data['logo'] = array(
				'name' => $current->getLogoName(),
				'path' => $current->getOriginalImagePath( 'logo' ),
				'url'  => $current->getOriginalImageURL( 'logo' ),
			);
		}

		if ( 0 < strlen( $current->getImageNameOriginal() ) ) {

			$data['photo'] = array(
				'name' => $current->getImageNameOriginal(),
				'path' => $current->getOriginalImagePath( 'photo' ),
				'url'  => $current->getOriginalImageURL( 'photo' ),
			);
		}

		_array::set( $this->data, $current->getId(), $data );
	}

	/**
	 * Create an array with the diff data.
	 *
	 * @since 10.4.53
	 *
	 * @param Entry $previous The instance of the previous Entry data.
	 * @param Entry $current  The instance of the current Entry data.
	 *
	 * @return void
	 */
	protected function addDiff( Entry $previous, Entry $current ) {

		$diff = array();

		if ( $previous->getEntryType() !== $current->getEntryType() ) {

			$diff['type'] = array(
				'previous' => $previous->getEntryType(),
				'current'  => $current->getEntryType(),
			);
		}

		if ( $previous->getStatus() !== $current->getStatus() ) {

			$diff['status'] = array(
				'previous' => $previous->getStatus(),
				'current'  => $current->getStatus(),
			);
		}

		if ( $previous->getVisibility() !== $current->getVisibility() ) {

			$diff['visibility'] = array(
				'previous' => $previous->getVisibility(),
				'current'  => $current->getVisibility(),
			);
		}

		if ( $previous->getName() !== $current->getName() ) {

			$diff['name'] = array(
				'previous' => $previous->getName(),
				'current'  => $current->getName(),
			);
		}

		if ( $previous->getOrganization() !== $current->getOrganization() ) {

			$diff['organization'] = array(
				'previous' => $previous->getOrganization(),
				'current'  => $current->getOrganization(),
			);
		}

		if ( $previous->getDepartment() !== $current->getDepartment() ) {

			$diff['department'] = array(
				'previous' => $previous->getDepartment(),
				'current'  => $current->getDepartment(),
			);
		}

		if ( $previous->getTitle() !== $current->getTitle() ) {

			$diff['title'] = array(
				'previous' => $previous->getTitle(),
				'current'  => $current->getTitle(),
			);
		}

		if ( $previous->getContactName() !== $current->getContactName() ) {

			$diff['contact_name'] = array(
				'previous' => $previous->getContactName(),
				'current'  => $current->getContactName(),
			);
		}

		if ( $previous->getBio() !== $current->getBio() ) {

			$diff['bio'] = array(
				'previous' => $previous->getBio(),
				'current'  => $current->getBio(),
			);
		}

		if ( $previous->getNotes() !== $current->getNotes() ) {

			$diff['notes'] = array(
				'previous' => $previous->getNotes(),
				'current'  => $current->getNotes(),
			);
		}

		if ( $previous->getExcerpt( array(), 'edit' ) !== $current->getExcerpt( array(), 'edit' ) ) {

			$diff['excerpt'] = array(
				'previous' => $previous->getExcerpt( array(), 'edit' ),
				'current'  => $current->getExcerpt( array(), 'edit' ),
			);
		}

		if ( $previous->getLogoName() !== $current->getLogoName() ) {

			$diff['logo'] = array(
				'previous' => $previous->getLogoName(),
				'current'  => $current->getLogoName(),
				'path'     => $current->getOriginalImagePath( 'logo' ),
				'url'      => $current->getOriginalImageURL( 'logo' ),
			);
		}

		if ( $previous->getImageNameOriginal() !== $current->getImageNameOriginal() ) {

			$diff['photo'] = array(
				'previous' => $previous->getImageNameOriginal(),
				'current'  => $current->getImageNameOriginal(),
				'path'     => $current->getOriginalImagePath( 'photo' ),
				'url'      => $current->getOriginalImageURL( 'photo' ),
			);
		}

		_array::set( $this->data, $current->getId(), $diff );
	}

	/**
	 * Get the data/diff from the log event context.
	 *
	 * @since 10.4.53
	 *
	 * @param array  $context The log event context.
	 * @param string $key     The array key of the data/diff to get.
	 *
	 * @return array
	 */
	protected function getDataFromContext( array $context, string $key ): array {

		$data = _array::get( $context, $key, '[]' );

		$data = _validate::isJSON( $data ) ? json_decode( $data, true ) : array();

		if ( ! is_array( $data ) ) {
			return array();
		}

		return $data;
	}

	/**
	 * Modify plain output to include link to edit the entry.
	 *
	 * @since 10.4.53
	 *
	 * @param object $row Log meta.
	 *
	 * @return string
	 */
	public function get_log_row_plain_text_output( $row ): string {

		$context = $row->context;

		// Default to original log message.
		$message = $row->message;
		$action  = $context['_message_key'] ?? null;

		// If the current action is deleting the entry, the default log message is sufficient.
		// OR If the Entry ID does not exist.
		if ( 'Connections_Directory/Entry/Deleted' === $action
			  || ! array_key_exists( 'entry_id', $context )
		) {

			return parent::get_log_row_plain_text_output( $row );
		}

		$entry  = new Entry();
		$result = $entry->set( $context['entry_id'] );

		if ( true === $result ) {

			if ( current_user_can( 'connections_edit_entry' )
				 || current_user_can( 'connections_edit_entry_moderated' )
			) {

				$context['edit_link'] = _nonce::url(
					'admin.php?page=connections_manage&cn-action=edit_entry&id=' . $entry->getId(),
					'entry_edit',
					$entry->getId()
				);

				switch ( $action ) {

					case 'Connections_Directory/Entry/Saved':
						$message = __( 'Created Entry <a href="{edit_link}">"{entry_name}"</a>', 'connections' );
						break;

					case 'Connections_Directory/Entry/Updated':
						$message = __( 'Updated Entry <a href="{edit_link}">"{entry_name}"</a>', 'connections' );
						break;
				}

			}
		}

		return helpers::interpolate( $message, $context, $row );
	}

	/**
	 * Modify output to include the entry diff table data.
	 *
	 * @since 10.4.53
	 *
	 * @param object $row Log meta.
	 *
	 * @return string|Event_Details_Container_Interface|Event_Details_Group HTML-formatted output or Event_Details_Container (stringable object).
	 */
	public function get_log_row_details_output( $row ) {

		$context = $row->context;
		$action  = $context['_message_key'];
		$html    = '';

		switch ( $action ) {

			case 'Connections_Directory/Entry/Saved':
				$data  = $this->getDataFromContext( $context, 'entry_data' );
				$table = '';
				$tr    = array();

				foreach ( $data as $key => $entry ) {

					$label = $this->getLabel( (string) $key );

					switch ( $key ) {

						case 'status':
							$status = array(
								'approved' => __( 'Approved', 'connections' ),
								'pending'  => __( 'Pending', 'connections' ),
							);

							$current = _array::get( $status, $entry, '' );

							$tr[] = $this->getTableRow( $label, '', $current );

							break;

						case 'type':
							$type    = cnOptions::getEntryTypes();
							$current = _array::get( $type, $entry, '' );

							$tr[] = $this->getTableRow( $label, '', $current );

							break;

						case 'visibility':
							$visibility = array(
								'public'   => __( 'Public', 'connections' ),
								'private'  => __( 'Private', 'connections' ),
								'unlisted' => __( 'Unlisted', 'connections' ),
							);

							$current = _array::get( $visibility, $entry, '' );

							$tr[] = $this->getTableRow( $label, '', $current );

							break;

						case 'logo':
						case 'photo':
							$name = _array::get( $entry, 'name', '' );
							$path = _array::get( $entry, 'path', '' );
							$url  = _array::get( $entry, 'url', '' );
							$tr[] = $this->getTableRowAddedImage( $label, $name, $path, $url );

							break;

						case 'taxonomies':
							$rows = $this->getTaxonomyTableRows( $entry );
							$tr   = array_merge( $tr, $rows );
							break;

						default:
							if ( ! is_string( $entry ) ) {

								$entry = wp_json_encode( $entry );
							}

							if ( 0 < strlen( $entry ) ) {

								$tr[] = $this->getTableRow( $label, '', $entry );
							}
					}
				}

				if ( 0 < count( $tr ) ) {
					$table = '<table class="SimpleHistoryLogitem__keyValueTable">' . implode( PHP_EOL, $tr ) . '</table>';
				}

				$html = $table;

				break;

			case 'Connections_Directory/Entry/Updated':
				$diff  = $this->getDataFromContext( $context, 'entry_diff' );
				$table = '';
				$tr    = array();

				foreach ( $diff as $key => $entry ) {

					$label    = $this->getLabel( (string) $key );
					$current  = _array::get( $entry, 'current', '' );
					$previous = _array::get( $entry, 'previous', '' );

					switch ( $key ) {

						case 'excerpt':
						case 'bio':
						case 'notes':
							$textDiff = helpers::text_diff( $previous, $current );

							if ( $textDiff ) {
								$tr[] = sprintf(
									'<tr><td>%1$s</td><td>%2$s</td></tr>',
									$label,
									$textDiff
								);
							}

							break;

						case 'status':
							$status = array(
								'approved' => __( 'Approved', 'connections' ),
								'pending'  => __( 'Pending', 'connections' ),
							);

							$current  = _array::get( $status, $current, '' );
							$previous = _array::get( $status, $previous, '' );

							$tr[] = $this->getTableRow( $label, $previous, $current );

							break;

						case 'type':
							$type     = cnOptions::getEntryTypes();
							$current  = _array::get( $type, $current, '' );
							$previous = _array::get( $type, $previous, '' );

							$tr[] = $this->getTableRow( $label, $previous, $current );

							break;

						case 'visibility':
							$visibility = array(
								'public'   => __( 'Public', 'connections' ),
								'private'  => __( 'Private', 'connections' ),
								'unlisted' => __( 'Unlisted', 'connections' ),
							);

							$current  = _array::get( $visibility, $current, '' );
							$previous = _array::get( $visibility, $previous, '' );

							$tr[] = $this->getTableRow( $label, $previous, $current );

							break;

						case 'logo':
						case 'photo':
							$path = _array::get( $entry, 'path', '' );
							$url  = _array::get( $entry, 'url', '' );
							$tr[] = $this->getTableRowUpdatedImage( $label, $previous, $current, $path, $url );

							break;

						case 'taxonomies':
							$rows = $this->getTaxonomyTableRows( $entry );
							$tr   = array_merge( $tr, $rows );
							break;

						default:
							if ( ! is_string( $previous ) ) {

								$previous = wp_json_encode( $previous );
							}

							if ( ! is_string( $current ) ) {

								$current = wp_json_encode( $current );
							}

							if ( 0 < strlen( $previous ) || 0 < strlen( $current ) ) {

								$tr[] = $this->getTableRow( $label, $previous, $current );
							}
					}
				}

				if ( 0 < count( $tr ) ) {
					$table = '<table class="SimpleHistoryLogitem__keyValueTable">' . implode( PHP_EOL, $tr ) . '</table>';
				}

				$html = $table;

				break;
		}

		return $html;
	}

	/**
	 * The log event labels.
	 *
	 * @since 10.4.53
	 *
	 * @param string $key The label key.
	 *
	 * @return string
	 */
	protected function getLabel( string $key ): string {

		$labels = array(
			'type'         => _x( 'Type', 'Logger: Connections Business Directory', 'connections' ),
			'status'       => _x( 'Moderation Status', 'Logger: Connections Business Directory', 'connections' ),
			'visibility'   => _x( 'Visibility', 'Logger: Connections Business Directory', 'connections' ),
			'name'         => _x( 'Name', 'Logger: Connections Business Directory', 'connections' ),
			'organization' => _x( 'Organization', 'Logger: Connections Business Directory', 'connections' ),
			'department'   => _x( 'Department', 'Logger: Connections Business Directory', 'connections' ),
			'title'        => _x( 'Title', 'Logger: Connections Business Directory', 'connections' ),
			'contact_name' => _x( 'Contact Name', 'Logger: Connections Business Directory', 'connections' ),
			'bio'          => _x( 'Biographical Info', 'Logger: Connections Business Directory', 'connections' ),
			'notes'        => _x( 'Notes', 'Logger: Connections Business Directory', 'connections' ),
			'excerpt'      => _x( 'Excerpt', 'Logger: Connections Business Directory', 'connections' ),
			'logo'         => _x( 'Logo', 'Logger: Connections Business Directory', 'connections' ),
			'photo'        => _x( 'Photo', 'Logger: Connections Business Directory', 'connections' ),
		);

		return _array::get( $labels, $key, '' );
	}

	/**
	 * Generate the table row for the log event detail.
	 *
	 * @since 10.5.53
	 *
	 * @param string $label    The row label.
	 * @param string $previous The previous value.
	 * @param string $current  The current value.
	 *
	 * @return string
	 */
	protected function getTableRow( string $label, string $previous, string $current ): string {

		return sprintf(
			'<tr>
				<td>%1$s</td>
				<td>%2$s</td>
			</tr>',
			$label,
			sprintf(
				'<ins class="SimpleHistoryLogitem__keyValueTable__addedThing">%1$s</ins> <del class="SimpleHistoryLogitem__keyValueTable__removedThing">%2$s</del>',
				esc_html( $current ),
				esc_html( $previous )
			)
		);
	}

	/**
	 * Generate the table row for the image log event detail.
	 *
	 * @since 10.4.53
	 *
	 * @param string $label The row label.
	 * @param string $name  The image name.
	 * @param string $path  The image path.
	 * @param string $url   The image URL.
	 *
	 * @return string
	 */
	protected function getTableRowAddedImage( string $label, string $name, string $path, string $url ): string {

		$html = '';

		if ( 0 < strlen( $url ) && file_exists( $path ) ) {

			$image = sprintf(
				'<div>%2$s</div>
				<div class="SimpleHistoryLogitemThumbnail">
					<img src="%1$s">
				</div>',
				esc_url( $url ),
				esc_html( $name )
			);

			$html = sprintf(
				'<tr>
					<td>%1$s</td>
					<td>%2$s</td>
				</tr>',
				$label,
				sprintf(
					'<ins class="SimpleHistoryLogitem__keyValueTable__addedThing">%1$s</ins> <del class="SimpleHistoryLogitem__keyValueTable__removedThing">%2$s</del>',
					$image,
					''
				)
			);
		}

		return $html;
	}

	/**
	 * Generate the table row for the image log event detail.
	 *
	 * @since 10.4.53
	 *
	 * @param string $label    The row label.
	 * @param string $previous The previous image name.
	 * @param string $current  The current image name.
	 * @param string $path     The current image path.
	 * @param string $url      The current image URL.
	 *
	 * @return string
	 */
	protected function getTableRowUpdatedImage( string $label, string $previous, string $current, string $path, string $url ): string {

		if ( 0 < strlen( $url ) && file_exists( $path ) ) {

			$html = sprintf(
				'<div>%2$s</div>
				<div class="SimpleHistoryLogitemThumbnail">
					<img src="%1$s">
				</div>',
				esc_url( $url ),
				esc_html( $current )
			);

		} else {

			$html = sprintf( '<div>%1$s</div>', esc_html( $current ) );
		}

		return sprintf(
			'<tr>
				<td>%1$s</td>
				<td>
					<div class="SimpleHistory__diff__contents SimpleHistory__diff__contents--noContentsCrop" tabindex="0">
						<div class="SimpleHistory__diff__contentsInner">
							<table class="diff SimpleHistory__diff">
								<tr>
									<td class="diff-deletedline">
										%2$s
									</td>
									<td class="diff-addedline">
										%3$s
									</td>
								</tr>
							</table>
						</div>
					</div>
				</td>
			</tr>',
			esc_html( $label ),
			esc_html( $previous ),
			$html
		);
	}

	/**
	 * Generate the table row for taxonomy term log event detail.
	 *
	 * @since 10.4.53
	 *
	 * @param array $entry The log event metadata entry.
	 *
	 * @return string[]
	 */
	protected function getTaxonomyTableRows( array $entry ): array {

		$tr = array();

		$taxonomies = Taxonomy\Registry::get()->getTaxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			if ( array_key_exists( $taxonomy->getSlug(), $entry ) ) {

				$taxonomySlug = $taxonomy->getSlug();

				$label    = $taxonomy->getLabels()->name;
				$current  = _array::get( $entry, "{$taxonomySlug}.current", array() );
				$previous = _array::get( $entry, "{$taxonomySlug}.previous", array() );
				$deleted  = array_diff( $previous, $current );
				$added    = array();
				$removed  = array();
				$items    = array();

				foreach ( $current as $termID ) {

					$term = Taxonomy\Term::get( $termID, $taxonomySlug );

					if ( $term instanceof Taxonomy\Term && ! in_array( $termID, $previous ) ) {

						$added[] = $term;
					}
				}

				foreach ( $deleted as $termID ) {

					$term = Taxonomy\Term::get( $termID, $taxonomySlug );

					if ( $term instanceof Taxonomy\Term ) {

						$removed[] = $term;
					}
				}

				foreach ( $added as $term ) {

					$items[] = sprintf(
						'<li><ins class="SimpleHistoryLogitem__keyValueTable__addedThing">%1$s</ins></li>',
						esc_html( $term->name )
					);
				}

				foreach ( $removed as $term ) {

					$items[] = sprintf(
						'<li><del class="SimpleHistoryLogitem__keyValueTable__removedThing">%1$s</del></li>',
						esc_html( $term->name )
					);
				}

				if ( 0 < count( $items ) ) {

					$tr[] = sprintf(
						'<tr>
							<td>%1$s</td>
							<td>%2$s</td>
						</tr>',
						$label,
						sprintf(
							'<ul>%1$s</ul>',
							implode( '', $items )
						)
					);
				}
			}
		}

		return $tr;
	}
}
