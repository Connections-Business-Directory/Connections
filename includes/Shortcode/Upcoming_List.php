<?php
/**
 * The `[upcoming_list]` shortcode.
 *
 * @since 10.4.40
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Shortcode\Upcoming_List
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2023, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Shortcode;

use cnEntry_vCard;
use cnShortcode;
use cnTemplate;
use cnTemplateFactory;
use cnTemplatePart;
use Connections_Directory\Request;
use Connections_Directory\Template\Hook_Transient;
use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_parse;

/**
 * Class Upcoming
 *
 * @package Connections_Directory\Shortcode
 */
final class Upcoming_List {

	use Do_Shortcode;
	use Get_HTML;

	/**
	 * The shortcode tag.
	 *
	 * @since 10.4.40
	 */
	const TAG = 'upcoming_list';

	/**
	 * The shortcode attributes.
	 *
	 * @since 10.4.40
	 *
	 * @var array
	 */
	private $attributes = array();

	/**
	 * The content from an enclosing shortcode.
	 *
	 * @since 10.4.40
	 *
	 * @var string
	 */
	private $content;

	/**
	 * An instance of the cnTemplate or false.
	 *
	 * @since 10.4.40
	 *
	 * @var cnTemplate|false
	 */
	private $template;

	/**
	 * Register the shortcode.
	 *
	 * @since 10.4.40
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
	 * @since 10.4.40
	 *
	 * @param array  $untrusted The shortcode arguments.
	 * @param string $content   The shortcode content.
	 * @param string $tag       The shortcode tag.
	 */
	public function __construct( array $untrusted, string $content = '', string $tag = self::TAG ) {

		$this->loadTemplate( $untrusted );

		if ( $this->template instanceof cnTemplate ) {

			$defaults  = $this->getDefaultAttributes();
			$untrusted = shortcode_atts( $defaults, $untrusted, $tag );

			$untrusted = apply_filters(
				"cn_list_atts-{$this->template->getSlug()}",
				apply_filters( 'cn_list_atts', $untrusted )
			);

			$this->attributes = $this->prepareAttributes( $untrusted );
			$this->html       = $this->generateHTML();

		} else {

			$this->html = cnTemplatePart::loadTemplateError( $untrusted );
		}

		$this->content = $content;

		// Clear any filters that have been added.
		// This allows support using the shortcode multiple times on the same page.
		Hook_Transient::instance()->clear();
	}

	/**
	 * Callback for `add_shortcode()`
	 *
	 * @internal
	 * @since 10.4.40
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
	 * Load the template.
	 *
	 * @since 10.4.40
	 *
	 * @param array{list_type: string, template: null|string} $untrusted The shortcode arguments.
	 */
	private function loadTemplate( array $untrusted ) {

		$defaults = array(
			'list_type' => 'birthday',
			'template'  => null,
		);

		$parsed = _parse::parameters( $untrusted, $defaults );

		/*
		 * If a list type was specified in the shortcode, load the template based on that type.
		 * However, if a specific template was specified, that should preempt the template to be loaded based on the list type if it was specified.
		 */
		if ( is_string( $parsed['template'] ) ) {

			$this->template = cnTemplateFactory::getTemplate( $parsed['template'] );

		} else {

			$templateSlug   = Connections_Directory()->options->getActiveTemplate( $parsed['list_type'] );
			$this->template = cnTemplateFactory::getTemplate( $templateSlug );
		}

		if ( $this->template instanceof cnTemplate ) {

			do_action( 'cn_register_legacy_template_parts' );
			do_action( "cn_action_include_once-{$this->template->getSlug()}" );
			do_action( "cn_action_js-{$this->template->getSlug()}" );
		}
	}

	/**
	 * The shortcode attribute defaults.
	 *
	 * @since 10.4.40
	 *
	 * @return array
	 */
	private function getDefaultAttributes(): array {

		$defaults = array(
			'list_type'        => 'birthday',
			'days'             => '30',
			'include_today'    => true,
			'private_override' => false,
			'name_format'      => '',
			'date_format'      => 'F jS',
			'year_type'        => 'upcoming',
			'year_format'      => '%y ' . __( 'Year(s)', 'connections' ),
			'show_lastname'    => false,
			'show_title'       => true,
			'list_title'       => '',
			'no_results'       => apply_filters( 'cn_upcoming_no_result_message', __( 'No results.', 'connections' ) ),
			'template'         => null,
			'content'          => '',
			'force_home'       => true,
			'home_id'          => cnShortcode::getHomeID(),
		);

		return apply_filters(
			"cn_list_atts_permitted-{$this->template->getSlug()}",
			apply_filters( 'cn_list_atts_permitted', $defaults )
		);
	}

	/**
	 * Parse and prepare the shortcode attributes.
	 *
	 * @since 10.4.40
	 *
	 * @param array $attributes The shortcode arguments.
	 *
	 * @return array
	 */
	private function prepareAttributes( array $attributes ): array {

		_format::toBoolean( $attributes['include_today'] );
		_format::toBoolean( $attributes['private_override'] );
		_format::toBoolean( $attributes['show_lastname'] );
		_format::toBoolean( $attributes['repeat_alphaindex'] );
		_format::toBoolean( $attributes['show_title'] );
		_format::toBoolean( $attributes['force_home'] );

		$attributes['home_id'] = filter_var(
			$attributes['home_id'],
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => cnShortcode::getHomeID(),
					'min_range' => 1,
				),
			)
		);

		if ( 0 === strlen( $attributes['name_format'] ) ) {

			$attributes['name_format'] = $attributes['show_lastname'] ? '%first% %last%' : '%first%';
		}

		return $attributes;
	}

	/**
	 * Generate the shortcode HTML.
	 *
	 * @since 10.4.40
	 *
	 * @return string
	 */
	private function generateHTML(): string {

		$connections = Connections_Directory();

		$html      = '';
		$alternate = '';

		/*
		 * This filter adds the current template paths to cnLocate so when template
		 * part file overrides are being searched for, it'll also search in template
		 * specific paths. This filter is then removed at the end of the shortcode.
		 */
		add_filter( 'cn_locate_file_paths', array( $this->template, 'templatePaths' ) );
		cnShortcode::addFilterRegistry( 'cn_locate_file_paths' );

		/**
		 * @todo Move to to {@see cnTemplateFactory::loadTemplate()}???
		 *       Note: These same actions are also in the [connections] and [cn-entry] shortcodes.
		 */
		do_action( "cn_template_include_once-{$this->template->getSlug()}" );
		do_action( "cn_template_enqueue_js-{$this->template->getSlug()}" );

		$results = Connections_Directory()->retrieve->upcoming(
			array(
				'type'             => $this->attributes['list_type'],
				'days'             => $this->attributes['days'],
				'today'            => $this->attributes['include_today'],
				'visibility'       => array(),
				'private_override' => $this->attributes['private_override'],
				'return'           => 'data', // Valid options are `data` which are the results returned from self::entries() or `id` which are the entry ID/s.
			)
		);

		// If there are no results no need to proceed and output message.
		if ( empty( $results ) ) {

			if ( 0 < strlen( $this->attributes['no_results'] ) ) {

				$html .= '<p class="cn-upcoming-no-results">' . _escape::html( $this->attributes['no_results'] ) . '</p>';

			} else {

				$html .= '&nbsp;'; // Need to return something for Gutenberg support. Otherwise, the loading spinner never stops.
			}

		} else {

			if ( empty( $this->attributes['list_title'] ) ) {

				switch ( $this->attributes['list_type'] ) {

					case 'birthday':
						if ( $this->attributes['days'] >= 1 ) {
							$list_title = 'Upcoming Birthdays the next ' . $this->attributes['days'] . ' days.';
						} else {
							$list_title = 'Today\'s Birthdays';
						}
						break;

					case 'anniversary':
						if ( $this->attributes['days'] >= 1 ) {
							$list_title = 'Upcoming Anniversaries the next ' . $this->attributes['days'] . ' days.';
						} else {
							$list_title = 'Today\'s Anniversaries';
						}
						break;

					default:
						if ( $this->attributes['days'] >= 1 ) {
							$list_title = "Upcoming {$this->attributes['list_type']} the next {$this->attributes['days']} days.";
						} else {
							$list_title = "Today's {$this->attributes['list_type']}";
						}
				}

			} else {

				$list_title = $this->attributes['list_title'];
			}

			ob_start();

				// Prints the template's CSS file.
				do_action( "cn_template_inline_css-{$this->template->getSlug()}", $this->attributes );

				$html .= ob_get_contents();
			ob_end_clean();

			$html .= '<div class="connections-list cn-upcoming ' . _escape::classNames( "cn-{$this->attributes['list_type']}" ) . '" id="cn-list" data-connections-version="' . esc_attr( "{$connections->options->getVersion()}-{$connections->options->getDBVersion()}" ) . '">' . PHP_EOL;

			$html .= '<div class="cn-template ' . _escape::classNames( "cn-{$this->template->getSlug()}" ) . '" id="' . _escape::id( "cn-{$this->template->getSlug()}" ) . '">' . PHP_EOL;

			$html .= '<div class="cn-clear" id="cn-list-head">' . PHP_EOL;

			if ( $this->attributes['show_title'] ) {

				$html .= '<div class="cn-upcoming-title">' . _escape::html( $list_title ) . '</div>' . PHP_EOL;
			}

			$html .= '</div> <!-- end #cn-list-head -->' . PHP_EOL;

			$html .= '<div class="cn-clear" id="cn-list-body">' . PHP_EOL;

			foreach ( $results as $row ) {

				$entry = new cnEntry_vCard( $row );
				$vCard =& $entry;

				// Configure the page where the entry link to.
				$entry->directoryHome(
					array(
						'page_id'    => $this->attributes['home_id'],
						'force_home' => $this->attributes['force_home'],
					)
				);

				if ( ! $this->attributes['show_lastname'] ) {

					$entry->setLastName( '' );
				}

				'' === $alternate ? $alternate = '-alternate' : $alternate = '';

				$html .= "<div class=\"cn-upcoming-row{$alternate} vcard\">" . PHP_EOL;
				ob_start();
				do_action( 'cn_template-' . $this->template->getSlug(), $entry, $this->template, $this->attributes );
				$html .= ob_get_clean();
				$html .= PHP_EOL . "</div> <!-- end .cn-upcoming-row{$alternate} -->" . PHP_EOL;
			}

			$html .= '</div> <!-- end #cn-list-body -->' . PHP_EOL;

			$html .= '<div class="cn-list-foot">' . PHP_EOL;
			$html .= '</div> <!-- end .cn-list-foot -->' . PHP_EOL;

			$html .= '</div> <!-- end .cn-template -->' . PHP_EOL;

			$html .= '</div> <!-- end .connections-list -->' . PHP_EOL;
		}

		// @todo This should be run via a filter.
		return cnShortcode::removeEOL( $html );
	}
}
