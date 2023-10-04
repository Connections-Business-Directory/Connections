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
use Connections_Directory\Utility\_nonce;
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
	 * Callback for the `Connections_Directory/Entry/Update/Before` action.
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
	public function entryUpdated( Entry $entry ) {

		$previous = new Entry();
		$result   = $previous->set( $entry->getId() );

		if ( true === $result ) {}

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
			)
		);
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
		if ( 'Connections_Directory/Entry/Deleted' === $action ) {

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
}
