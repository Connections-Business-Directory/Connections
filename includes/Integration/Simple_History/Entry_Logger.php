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
 * Class Logger
 *
 * @package Connections_Directory\Integration\Simple_History
 */
final class Entry_Logger extends Logger {

	/**
	 * Unique slug for the logger.
	 *
	 * The slug will be saved in DB and used to associate each log row with its logger.
	 *
	 * @since 10.4.53
	 *
	 * @var string
	 */
	protected $slug = 'Connections_Directory';

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
	 * @return array{
	 *     name: string,
	 *     description: string,
	 *     capability: string,
	 *     name_via: string,
	 *     messages: array{string[]}
	 * }
	 */
	public function get_info(): array {

		return array(
			'name'        => _x( 'Plugin: Connections Business Directory Logger', 'Logger: Connections Business Directory', 'connections' ),
			'description' => _x( 'Log Connections Business Directory Activity.', 'Logger: Connections Business Directory', 'connections' ),
			// Set the capability required to read the events recorded by this logger.
			'capability'  => 'manage_options',
			'name_via'    => _x( 'Using plugin Connections Business Directory', 'Logger: Connections Business Directory', 'connections' ),
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
						_x( 'Directory Entries Created', 'Post logger: search', 'connections' )  => array( 'Connections_Directory/Entry/Saved' ),
						_x( 'Directory Entries Updated', 'Post logger: search', 'connections' )  => array( 'Connections_Directory/Entry/Updated' ),
						_x( 'Directory Entries Deleted', 'Post logger: search', 'connections' )  => array( 'Connections_Directory/Entry/Deleted' ),
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
	 * Callback for the `Connections_Directory/Entry/Saved` action.
	 *
	 * Records a log event when an entry is created.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param Entry $entry Instance of cnEntry.
	 *
	 * @return void
	 */
	public function entrySaved( Entry $entry ) {

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
							'label'    => _x( 'Moderation Status', 'Logger: Connections Business Directory', 'connections' ),
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
							'label'    => _x( 'Visibility', 'Logger: Connections Business Directory', 'connections' ),
							'previous' => $entry->getVisibility(),
							'current'  => $visibility,
						),
					),
				)
			);
		}
	}

	/**
	 * Callback for the `Connections_Directory/Entry/Update/Before` action.
	 *
	 * Records a log event when an entry is updated.
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
				'entry_id'    => $current->getId(),
				'entry_name'  => $current->getName(),
				'entry_diff'  => $this->addDiff( $previous, $current ),
			)
		);
	}

	/**
	 * Create an array with the diff data.
	 *
	 * @since 10.4.53
	 *
	 * @param Entry $previous The instance of the previous Entry data.
	 * @param Entry $current  The instance of the current Entry data.
	 *
	 * @return array
	 */
	protected function addDiff( Entry $previous, Entry $current ): array {

		$diff = array();

		if ( $previous->getEntryType() !== $current->getEntryType() ) {

			$diff['type'] = array(
				'label'    => _x( 'Type', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getEntryType(),
				'current'  => $current->getEntryType(),
			);
		}

		if ( $previous->getStatus() !== $current->getStatus() ) {

			$diff['status'] = array(
				'label'    => _x( 'Moderation Status', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getStatus(),
				'current'  => $current->getStatus(),
			);
		}

		if ( $previous->getVisibility() !== $current->getVisibility() ) {

			$diff['visibility'] = array(
				'label'    => _x( 'Visibility', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getVisibility(),
				'current'  => $current->getVisibility(),
			);
		}

		if ( $previous->getName() !== $current->getName() ) {

			$diff['name'] = array(
				'label'    => _x( 'Name', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getName(),
				'current'  => $current->getName(),
			);
		}

		if ( $previous->getOrganization() !== $current->getOrganization() ) {

			$diff['organization'] = array(
				'label'    => _x( 'Organization', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getOrganization(),
				'current'  => $current->getOrganization(),
			);
		}

		if ( $previous->getDepartment() !== $current->getDepartment() ) {

			$diff['department'] = array(
				'label'    => _x( 'Department', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getDepartment(),
				'current'  => $current->getDepartment(),
			);
		}

		if ( $previous->getTitle() !== $current->getTitle() ) {

			$diff['title'] = array(
				'label'    => _x( 'Title', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getTitle(),
				'current'  => $current->getTitle(),
			);
		}

		if ( $previous->getContactName() !== $current->getContactName() ) {

			$diff['contact_name'] = array(
				'label'    => _x( 'Contact Name', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getContactName(),
				'current'  => $current->getContactName(),
			);
		}

		if ( $previous->getBio() !== $current->getBio() ) {

			$diff['bio'] = array(
				'label'    => _x( 'Biographical Info', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getBio(),
				'current'  => $current->getBio(),
			);
		}

		if ( $previous->getNotes() !== $current->getNotes() ) {

			$diff['notes'] = array(
				'label'    => _x( 'Notes', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getNotes(),
				'current'  => $current->getNotes(),
			);
		}

		if ( $previous->getExcerpt( array(), 'edit' ) !== $current->getExcerpt( array(), 'edit' ) ) {

			$diff['excerpt'] = array(
				'label'    => _x( 'Excerpt', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getExcerpt( array(), 'edit' ),
				'current'  => $current->getExcerpt( array(), 'edit' ),
			);
		}

		if ( $previous->getLogoName() !== $current->getLogoName() ) {

			$diff['logo'] = array(
				'label'    => _x( 'Logo', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getLogoName(),
				'current'  => $current->getLogoName(),
				'url'      => $current->getOriginalImageURL( 'logo' ),
			);
		}

		if ( $previous->getImageNameOriginal() !== $current->getImageNameOriginal() ) {

			$diff['photo'] = array(
				'label'    => _x( 'Photo', 'Logger: Connections Business Directory', 'connections' ),
				'previous' => $previous->getImageNameOriginal(),
				'current'  => $current->getImageNameOriginal(),
				'url'      => $current->getOriginalImageURL( 'photo' ),
			);
		}

		return $diff;
	}

	/**
	 * Get the diff from the log event context.
	 *
	 * @since 10.4.53
	 *
	 * @param array $context The log event context.
	 *
	 * @return array
	 */
	protected function getDiffFromContext( array $context ): array {

		$diff = _array::get( $context, 'entry_diff', '[]' );

		$diff = _validate::isJSON( $diff ) ? json_decode( $diff, true ) : array();

		if ( ! is_array( $diff ) ) {
			return array();
		}

		return $diff;
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
						$message = __( 'Edited Entry <a href="{edit_link}">"{entry_name}"</a>', 'connections' );
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
				// @todo Return data about the entry added such as name, status, visibility, and other fields.
				$html = parent::get_log_row_details_output( $row );
				break;

			case 'Connections_Directory/Entry/Updated':
				$diff  = $this->getDiffFromContext( $context );
				$table = '';
				$tr    = array();

				foreach ( $diff as $key => $entry ) {

					$label    = _array::get( $entry, 'label', '' );
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
							$url  = _array::get( $entry, 'url', '' );
							$tr[] = $this->getTableRowImage( $label, $previous, $current, $url );

							break;

						default:
							$tr[] = $this->getTableRow( $label, $previous, $current );
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
	 * Generate the table row for the log event detail.
	 *
	 * @internal
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
	 * @internal
	 * @since 10.4.53
	 *
	 * @param string $label    The row label.
	 * @param string $previous The previous image name.
	 * @param string $current  The current image name.
	 * @param string $url      The current image URL.
	 *
	 * @return string
	 */
	protected function getTableRowImage( string $label, string $previous, string $current, string $url ): string {

		if ( 0 < strlen( $url ) ) {

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
}
