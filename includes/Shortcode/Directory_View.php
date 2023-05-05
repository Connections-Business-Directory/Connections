<?php
/**
 * The `[connections]` shortcode.
 *
 * @since      10.4.41
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shortcode
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );
namespace Connections_Directory\Shortcode;

use cnQuery;
use Connections_Directory\Request;
use Connections_Directory\Utility\_array;

/**
 * Class Directory_View
 *
 * @package Connections_Directory\Shortcode
 */
final class Directory_View {

	use Do_Shortcode;
	use Get_HTML;

	/**
	 * The shortcode tag.
	 *
	 * @since 10.4.41
	 */
	const TAG = 'connections';

	/**
	 * The shortcode attributes.
	 *
	 * @since 10.4.41
	 *
	 * @var array
	 */
	private $attributes;

	/**
	 * The content from an enclosing shortcode.
	 *
	 * @since 10.4.41
	 *
	 * @var string
	 */
	private $content;

	/**
	 * The content from an enclosing shortcode.
	 *
	 * @since 10.4.41
	 *
	 * @var string
	 */
	private $tag;

	/**
	 * Register the shortcode.
	 *
	 * @since 10.4.41
	 */
	public static function add() {

		/*
		 * Do not register the shortcode when doing ajax requests.
		 * This is primarily implemented so the shortcodes are not run during Yoast SEO page score admin ajax requests.
		 * The page score can cause the ajax request to fail and/or prevent the page from saving when page score is
		 * being calculated on the output from the shortcode.
		 */
		if ( ! Request::get()->isAjax() ) {

			add_filter( 'pre_do_shortcode_tag', array( __CLASS__, 'maybeDoShortcode' ), 10, 4 );
			add_shortcode( self::TAG, array( __CLASS__, 'instance' ) );
		}
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.41
	 *
	 * @param array  $untrusted The shortcode arguments.
	 * @param string $content   The shortcode content.
	 * @param string $tag       The shortcode tag.
	 */
	public function __construct( array $untrusted, string $content = '', string $tag = '' ) {

		$this->attributes = $untrusted;
		$this->content    = $content;
		$this->tag        = $tag;
		$this->html       = $this->generateHTML();
	}

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @internal
	 * @since 10.4.41
	 *
	 * @param array  $atts    The shortcode arguments.
	 * @param string $content The shortcode content.
	 * @param string $tag     The shortcode tag.
	 *
	 * @return static
	 */
	public static function instance( array $atts, string $content = '', string $tag = self::TAG ): self {

		return new self( $atts, $content, $tag );
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.41
	 *
	 * @return string
	 */
	private function generateHTML(): string {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * Only show this message under the following condition:
		 * - ( The user is not logged in AND the 'Login Required' is checked ) AND ( neither of the shortcode visibility overrides are enabled ).
		 */
		if ( ( ! is_user_logged_in() && ! $instance->options->getAllowPublic() )
			 && ! ( $instance->options->getAllowPublicOverride() || $instance->options->getAllowPrivateOverride() )
		) {

			$message = $instance->settings->get( 'connections', 'connections_login', 'message' );

			// Format and texturize the message.
			$message = wptexturize( wpautop( $message ) );

			// Make any links and such clickable.
			$message = make_clickable( $message );

			// Apply the shortcodes.
			return do_shortcode( $message );
		}

		// Display based on query var `cn-view`.
		$view = cnQuery::getVar( 'cn-view' );

		switch ( $view ) {

			case 'submit':
				if ( has_action( 'cn_submit_entry_form' ) ) {

					ob_start();

					/**
					 * @todo There s/b capability checks just like when editing an entry so users can only submit when they have the permissions.
					 */
					do_action( 'cn_submit_entry_form', $this->attributes, $this->content, $this->tag );

					return ob_get_clean();

				} else {

					return '<p>' . esc_html__( 'Future home of front end submissions.', 'connections' ) . '</p>';
				}

			case 'landing':
				return '<p>' . esc_html__( 'Future home of the landing pages, such a list of categories.', 'connections' ) . '</p>';

			case 'search':
				if ( has_action( 'Connections_Directory/Shortcode/View/Search' ) ) {

					ob_start();

					do_action( 'Connections_Directory/Shortcode/View/Search', $this->attributes, $this->content, $this->tag );

					return ob_get_clean();

				} else {

					return '<p>' . esc_html__( 'Future home of the search page.', 'connections' ) . '</p>';
				}

			case 'results':
				if ( has_action( 'cn_submit_search_results' ) ) {

					ob_start();

					do_action( 'cn_submit_search_results', $this->attributes, $this->content, $this->tag );

					return ob_get_clean();

				} else {

					return '<p>' . esc_html__( 'Future home of the search results landing page.', 'connections' ) . '</p>';
				}

			case 'card':
				// Show the standard result list.
				return Entry_Directory::instance( $this->attributes, $this->content, $this->tag )->getHTML();

			case 'all':
				// Show the "View All" result list using the "Names" template.

				// Disable the output of the repeat character index.
				_array::set( $this->attributes, 'repeat_alphaindex', false );

				// Force the use of the Names template.
				_array::set( $this->attributes, 'template', 'names' );

				return Entry_Directory::instance( $this->attributes, $this->content, $this->tag )->getHTML();

			case 'detail':
				// Show the entry detail using a template based on the entry type.
				switch ( cnQuery::getVar( 'cn-process' ) ) {

					case 'edit':
						if ( has_action( 'cn_edit_entry_form' ) ) {

							// Check to see if the entry has been linked to a user ID.
							$entryID = get_user_meta( get_current_user_id(), 'connections_entry_id', true );

							$results = $instance->retrieve->entries( array( 'status' => 'approved,pending' ) );

							/*
							 * The `cn_edit_entry_form` action should only be executed if the user is
							 * logged in, and they have the `connections_manage` capability and either the
							 * `connections_edit_entry` or `connections_edit_entry_moderated` capability.
							 */

							if ( is_user_logged_in() &&
								( current_user_can( 'connections_manage' ) || ( (int) $entryID == (int) $results[0]->id ) ) &&
								( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) )
							) {

								ob_start();

								if ( ! current_user_can( 'connections_edit_entry' ) && 'pending' === $results[0]->status ) {

									echo '<p>' . esc_html__( 'Your entry submission is currently under review, however, you can continue to make edits to your entry submission while your submission is under review.', 'connections' ) . '</p>';
								}

								do_action( 'cn_edit_entry_form', $this->attributes, $this->content, $this->tag );

								return ob_get_clean();

							} else {

								return esc_html__( 'You are not authorized to edit entries. Please contact the admin if you received this message in error.', 'connections' );
							}

						}

						break;

					default:
						$results = $instance->retrieve->entries( $this->attributes );

						$this->attributes['list_type'] = $instance->settings->get( 'connections', 'connections_display_single', 'template' ) ? $results[0]->entry_type : null;

						return Entry_Directory::instance( $this->attributes, $this->content, $this->tag )->getHTML();
				}

				break;

			default:
				// Show the standard result list.

				if ( has_action( "cn_view_{$view}" ) ) {

					ob_start();

					do_action( "cn_view_{$view}", $this->attributes, $this->content, $this->tag );

					return ob_get_clean();
				}

				break;
		}

		return Entry_Directory::instance( $this->attributes, $this->content, $this->tag )->getHTML();
	}
}
