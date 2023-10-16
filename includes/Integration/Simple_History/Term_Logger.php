<?php
/**
 * Log term related events such as create, delete, and modify.
 *
 * @since      10.4.53
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory\Integration\Simple_History
 * @subpackage Connections_Directory\Integration\Simple_History\Term_Logger
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Integration\Simple_History;

use cnTerm as Term;
use Connections_Directory\Taxonomy;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_validate;
use Simple_History\Event_Details\Event_Details_Container_Interface;
use Simple_History\Event_Details\Event_Details_Group;
use Simple_History\Helpers;
use Simple_History\Loggers\Logger;
use Simple_History\Simple_History;

/**
 * Class Term_Logger
 *
 * @package Connections_Directory\Integration\Simple_History
 */
final class Term_Logger extends Logger {

	/**
	 * The log term data, such as the diff when a term is updated.
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
	protected $slug = 'CBD/Term';

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
	 *         search: array{
	 *             label: string,
	 *             label_all: string,
	 *             options: string[]
	 *         }
	 *     }
	 * }
	 */
	public function get_info(): array {

		return array(
			'name'        => _x( 'Plugin: Connections Business Directory Term Logger', 'Logger: Connections Business Directory', 'connections' ),
			'description' => _x( 'Log Connections Business Directory Term Activity.', 'Logger: Connections Business Directory', 'connections' ),
			// Set the capability required to read the events recorded by this logger.
			'capability'  => 'manage_options',
			'name_via'    => _x( 'Using plugin Connections Business Directory.', 'Logger: Connections Business Directory', 'connections' ),
			'messages'    => array(
				// [key == Action or Filter tag name => value == Log message]
				'Connections_Directory/Term/Saved'   => _x( 'Created term "{term_name}" in taxonomy "{term_taxonomy}".', 'Logger: Connections Business Directory', 'connections' ),
				'Connections_Directory/Term/Deleted' => _x( 'Deleted term "{term_name}" from taxonomy "{term_taxonomy}".', 'Logger: Connections Business Directory', 'connections' ),
				'Connections_Directory/Term/Updated' => _x( 'Updated term "{term_name}" in taxonomy "{term_taxonomy}".', 'Logger: Connections Business Directory', 'connections' ),
			),
			'labels'      => array(
				'search' => array(
					'label'     => _x( 'Connections Business Directory Term Activity', 'Directory Term logger: search', 'connections' ),
					'label_all' => _x( 'All Directory Term Activity', 'Directory Term logger: search', 'connections' ),
					'options'   => array(
						_x( 'Directory Terms Created', 'Term logger: search', 'connections' )  => array( 'Connections_Directory/Term/Saved' ),
						_x( 'Directory Terms Updated', 'Term logger: search', 'connections' )  => array( 'Connections_Directory/Term/Updated' ),
						_x( 'Directory Terms Deleted', 'Term logger: search', 'connections' )  => array( 'Connections_Directory/Term/Deleted' ),
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

		add_action( 'cn_delete_term', array( $this, 'termDeleted' ), 10, 4 );
		add_action( 'cn_created_term', array( $this, 'termSaved' ), 10, 3 );
		add_action( 'cn_edit_terms', array( $this, 'termUpdated' ), 10, 3 );
	}

	/**
	 * Callback for the `cn_created_term` action hook.
	 *
	 * Fires after a new term is created, and after the term cache has been cleared.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function termSaved( int $term_id, int $tt_id, string $taxonomy ) {

		$term = Term::getBy( 'id', $term_id, $taxonomy );

		if ( ! $term instanceof Taxonomy\Term ) {
			return;
		}

		$term_name     = $term->name;
		$term_taxonomy = $term->taxonomy;
		$term_id       = $term->term_id;

		$this->addData( $term );

		$this->info_message(
			'Connections_Directory/Term/Saved',
			array(
				'term_id'       => $term_id,
				'term_name'     => $term_name,
				'term_taxonomy' => $term_taxonomy,
				'term_data'     => _array::get( $this->data, $term->term_id, array() ),
			)
		);
	}

	/**
	 * Callback for the `cn_edit_terms` action hook.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param int    $term_id  The term ID.
	 * @param string $taxonomy The term Taxonomy.
	 * @param array  $args     The parameters passed to {@see cnTerm::update()}.
	 *
	 * @return void
	 */
	public function termUpdated( int $term_id, string $taxonomy, array $args ) {

		$previous = Term::getBy( 'id', $term_id, $taxonomy );

		if ( ! $previous instanceof Taxonomy\Term ) {

			return;
		}

		$current = new Taxonomy\Term( (object) $args );

		$this->addDiff( $previous, $current );

		$this->info_message(
			'Connections_Directory/Term/Updated',
			array(
				'term_id'       => $term_id,
				'term_name'     => $current->name,
				'term_taxonomy' => $current->taxonomy,
				'term_diff'     => _array::get( $this->data, $current->term_id, array() ),
			)
		);
	}

	/**
	 * Callback for the `cn_delete_term` action hook.
	 *
	 * Fires after a term is deleted from the database and the cache has been cleared.
	 *
	 * @internal
	 * @since 10.4.53
	 *
	 * @param int    $term          Term ID.
	 * @param int    $tt_id         Term taxonomy ID.
	 * @param string $taxonomy      Taxonomy slug.
	 * @param mixed  $deleted_term  Copy of the already-deleted term, in the form specified
	 *                              by the parent function. WP_Error otherwise.
	 */
	public function termDeleted( int $term, int $tt_id, string $taxonomy, $deleted_term ) {

		if ( is_wp_error( $deleted_term ) ) {
			return;
		}

		$term_name     = $deleted_term->name;
		$term_taxonomy = $deleted_term->taxonomy;
		$term_id       = $deleted_term->term_id;

		$this->info_message(
			'Connections_Directory/Term/Deleted',
			array(
				'term_id'       => $term_id,
				'term_name'     => $term_name,
				'term_taxonomy' => $term_taxonomy,
			)
		);
	}

	/**
	 * Create an array with the term data.
	 *
	 * @since 10.4.53
	 *
	 * @param Taxonomy\Term $current The instance of the current Term data.
	 *
	 * @return void
	 */
	protected function addData( Taxonomy\Term $current ) {

		$data = array();

		$data['name']        = $current->name;
		$data['slug']        = $current->slug;
		$data['description'] = $current->description;
		$data['taxonomy']    = $current->taxonomy;
		$data['parent']      = $current->parent;

		_array::set( $this->data, $current->term_id, $data );
	}

	/**
	 * Create an array with the diff data.
	 *
	 * @since 10.4.53
	 *
	 * @param Taxonomy\Term $previous The instance of the previous Term data.
	 * @param Taxonomy\Term $current  The instance of the current Term data.
	 *
	 * @return void
	 */
	protected function addDiff( Taxonomy\Term $previous, Taxonomy\Term $current ) {

		$diff = array();

		if ( $previous->name !== $current->name ) {

			$diff['name'] = array(
				'previous' => $previous->name,
				'current'  => $current->name,
			);
		}

		if ( $previous->slug !== $current->slug ) {

			$diff['slug'] = array(
				'previous' => $previous->slug,
				'current'  => $current->slug,
			);
		}

		if ( $previous->description !== $current->description ) {

			$diff['description'] = array(
				'previous' => $previous->description,
				'current'  => $current->description,
			);
		}

		if ( $previous->taxonomy !== $current->taxonomy ) {

			$diff['taxonomy'] = array(
				'previous' => $previous->taxonomy,
				'current'  => $current->taxonomy,
			);
		}

		if ( $previous->parent !== $current->parent ) {

			$diff['parent'] = array(
				'previous' => $previous->parent,
				'current'  => $current->parent,
			);
		}

		_array::set( $this->data, $current->term_id, $diff );
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
	 * Modify plain output to include link to edit the term.
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

		$taxonomy = Taxonomy\Registry::get()->getTaxonomy( _array::get( $context, 'term_taxonomy', '' ) );
		$term     = Term::getBy( 'id', _array::get( $context, 'term_id', 0 ), _array::get( $context, 'term_taxonomy', '' ) );

		if ( $taxonomy instanceof Taxonomy ) {

			$context['term_taxonomy'] = $taxonomy->getLabels()->name;

			if ( current_user_can( $taxonomy->getCapabilities()->manage_terms ) ) {

				$context['manage_taxonomy'] = add_query_arg(
					array(
						'page' => "connections_manage_{$taxonomy->getSlug()}_terms",
					),
					admin_url( 'admin.php' )
				);
			}

			if ( $term instanceof Taxonomy\Term
				 && current_user_can( $taxonomy->getCapabilities()->edit_terms )
				 && array_key_exists( 'manage_taxonomy', $context )
			) {

				$location = add_query_arg(
					array(
						'cn-action' => "edit_{$taxonomy->getSlug()}",
						'id'        => $term->term_id,
					),
					$context['manage_taxonomy']
				);

				$editURL = wp_nonce_url(
					$location,
					"{$taxonomy->getSlug()}_edit_{$term->term_id}"
				);

				$context['edit_term'] = $editURL;
			}

		}

		switch ( $action ) {

			case 'Connections_Directory/Term/Deleted':
				if ( array_key_exists( 'manage_taxonomy', $context ) ) {

					$message = _x(
						'Deleted term "{term_name}" from taxonomy <a href="{manage_taxonomy}">"{term_taxonomy}"</a>',
						'Logger: Connections Business Directory',
						'connections'
					);
				}
				break;

			case 'Connections_Directory/Term/Saved':
				if ( array_key_exists( 'edit_term', $context ) && array_key_exists( 'manage_taxonomy', $context ) ) {

					$message = _x(
						'Created term <a href="{edit_term}">"{term_name}"</a> in taxonomy <a href="{manage_taxonomy}">"{term_taxonomy}"</a>.',
						'Logger: Connections Business Directory',
						'connections'
					);

				} elseif ( array_key_exists( 'manage_taxonomy', $context ) ) {

					$message = _x(
						'Created term "{term_name}" in taxonomy <a href="{manage_taxonomy}">"{term_taxonomy}"</a>.',
						'Logger: Connections Business Directory',
						'connections'
					);
				}
				break;

			case 'Connections_Directory/Term/Updated':
				if ( array_key_exists( 'edit_term', $context ) && array_key_exists( 'manage_taxonomy', $context ) ) {

					$message = _x(
						'Updated term <a href="{edit_term}">"{term_name}"</a> in taxonomy <a href="{manage_taxonomy}">"{term_taxonomy}"</a>.',
						'Logger: Connections Business Directory',
						'connections'
					);

				} elseif ( array_key_exists( 'manage_taxonomy', $context ) ) {

					$message = _x(
						'Updated term "{term_name}" in taxonomy <a href="{manage_taxonomy}">"{term_taxonomy}"</a>.',
						'Logger: Connections Business Directory',
						'connections'
					);
				}
				break;
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

			case 'Connections_Directory/Term/Saved':
				$data  = $this->getDataFromContext( $context, 'term_data' );
				$table = '';
				$tr    = array();

				foreach ( $data as $key => $entry ) {

					$label = $this->getLabel( (string) $key );

					switch ( $key ) {

						case 'parent':
							if ( 0 === $entry ) {

								$entry = _x( 'None', 'Logger: Connections Business Directory', 'connections' );

							} elseif ( Term::exists( $entry ) ) {

								$entry = Term::getBy( 'id', $entry, _array::get( $context, 'term_taxonomy', '' ) );
							}

							$tr[] = $this->getTableRow(
								$label,
								'',
								$entry instanceof Taxonomy\Term ? $entry->name : (string) $entry
							);

							break;

						case 'taxonomy':
							$taxonomy = Taxonomy\Registry::get()->getTaxonomy( $entry );

							if ( $taxonomy instanceof Taxonomy ) {

								$tr[] = $this->getTableRow( $label, '', $taxonomy->getLabels()->singular_name );

							} else {

								$tr[] = $this->getTableRow( $label, '', $entry );
							}

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

			case 'Connections_Directory/Term/Updated':
				$diff  = $this->getDataFromContext( $context, 'term_diff' );
				$table = '';
				$tr    = array();

				foreach ( $diff as $key => $entry ) {

					$label    = $this->getLabel( (string) $key );
					$current  = _array::get( $entry, 'current', '' );
					$previous = _array::get( $entry, 'previous', '' );

					switch ( $key ) {

						case 'description':
							$textDiff = helpers::text_diff( $previous, $current );

							if ( $textDiff ) {
								$tr[] = sprintf(
									'<tr><td>%1$s</td><td>%2$s</td></tr>',
									$label,
									$textDiff
								);
							}

							break;

						case 'parent':
							if ( 0 === $current ) {

								$current = _x( 'None', 'Logger: Connections Business Directory', 'connections' );

							} elseif ( Term::exists( $current ) ) {

								$current = Term::getBy( 'id', $current, _array::get( $context, 'term_taxonomy', '' ) );
							}

							if ( 0 === $previous ) {

								$previous = _x( 'None', 'Logger: Connections Business Directory', 'connections' );

							} elseif ( Term::exists( $previous ) ) {

								$previous = Term::getBy( 'id', $previous, _array::get( $context, 'term_taxonomy', '' ) );
							}

							$tr[] = $this->getTableRow(
								$label,
								$previous instanceof Taxonomy\Term ? $previous->name : (string) $previous,
								$current instanceof Taxonomy\Term ? $current->name : (string) $current
							);

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
			'name'        => _x( 'Name', 'Logger: Connections Business Directory', 'connections' ),
			'slug'        => _x( 'Slug', 'Logger: Connections Business Directory', 'connections' ),
			'description' => _x( 'Description', 'Logger: Connections Business Directory', 'connections' ),
			'taxonomy'    => _x( 'Taxonomy', 'Logger: Connections Business Directory', 'connections' ),
			'parent'      => _x( 'Parent', 'Logger: Connections Business Directory', 'connections' ),
		);

		return _array::get( $labels, $key, '' );
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
}
