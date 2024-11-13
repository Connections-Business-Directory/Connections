<?php
/**
 * The search form.
 *
 * @since      10.4.64
 *
 * @category   WordPress\Plugin
 * @package    Connections_Directory
 * @subpackage Connections_Directory\Form
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2024, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

declare( strict_types=1 );

namespace Connections_Directory\Form;

use cnSEO;
use cnSettingsAPI;
use cnShortcode;
use Connections_Directory\Form;
use Connections_Directory\Request;
use Connections_Directory\Taxonomy\Registry;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_parse;
use Connections_Directory\Utility\_sanitize;
use Connections_Directory\Utility\_url;

/**
 * Class Search
 *
 * @package Connections_Directory\Form
 */
final class Search extends Form {

	/**
	 * Force the form action to the page set as the directory homepage.
	 *
	 * @since 10.4.64
	 *
	 * @var bool
	 */
	protected $forceHome;

	/**
	 * The directory homepage ID.
	 *
	 * @since 10.4.64
	 *
	 * @var int
	 */
	protected $homepageID;

	/**
	 * Whether the current queried object ID is the post ID set as the directory homepage.
	 *
	 * @since 10.4.64
	 *
	 * @var bool
	 */
	protected $isHomepage;

	/**
	 * Search constructor.
	 *
	 * @param array $parameters The form parameters.
	 */
	public function __construct( array $parameters = array() ) {

		$defaults = array(
			'class'  => array( 'cbd-form__search' ),
			'fields' => $this->fields( $parameters ),
			'submit' => array(
				'class' => array( 'search-submit' ),
				'text'  => esc_attr_x( 'Search', 'submit button', 'connections' ),
			),
		);

		$parameters = _parse::parameters( $parameters, $defaults, false, false );

		parent::__construct( $parameters );

		$this->setMethod( 'GET' );
		$this->setHomepageID( $parameters );
		$this->defineAction( $parameters );
	}

	/**
	 * Set up the form action.
	 *
	 * @since 10.4.64
	 *
	 * @param array $parameters The form object parameters.
	 */
	protected function defineAction( array $parameters ) {

		global $wp_rewrite;

		if ( $wp_rewrite->using_permalinks() ) {

			if ( $this->isHomepage || $this->forceHome ) {

				$permalink = $this->getPermalink( $this->homepageID );

				/**
				 * Filter the form action attribute.
				 *
				 * @since 8.5.15
				 * @since 10.4.39 Changed filter hook name.
				 *
				 * @param string $permalink The form action permalink.
				 * @param array  $atts      The filter parameter arguments.
				 */
				$permalink = apply_filters( 'Connections_Directory/Template/Partial/Search/Form_Action', $permalink, $parameters );
				$permalink = _url::makeRelative( $permalink );

				$this->action = ( $permalink );
			}

			if ( is_front_page() ) {

				$this->addField(
					Field\Hidden::create()
								->setName( 'page_id' )
								->setValue( $this->homepageID )
				);
			}

		} else {

			$fieldName = is_page() ? 'page_id' : 'p';

			$this->addField(
				Field\Hidden::create()
							->setName( $fieldName )
							->setValue( $this->homepageID )
			);
		}
	}

	/**
	 * Generate the post permalink.
	 *
	 * @since 10.4.64
	 *
	 * @param int $id Post ID.
	 *
	 * @return string
	 */
	protected function getPermalink( int $id ): string {

		// The base post permalink is required, do not filter the permalink through cnSEO.
		cnSEO::doFilterPermalink( false );

		$permalink = get_permalink( $id );

		if ( ! is_string( $permalink ) ) {

			$permalink = '';
		}

		// Add the cnSEO permalink filter.
		cnSEO::doFilterPermalink();

		return $permalink;
	}

	/**
	 * Set up the form object homepage properties.
	 *
	 * @since 10.4.64
	 *
	 * @param array{force_home: bool, home_id: int} $parameters The `force_home` and `home_id` parameters.
	 */
	protected function setHomepageID( array $parameters ) {

		$settingHomepageID = cnSettingsAPI::get( 'connections', 'home_page', 'page_id' );

		$forceHome  = _array::get( $parameters, 'force_home', false );
		$homepageID = _array::get( $parameters, 'home_id', cnShortcode::getHomeID() );

		$homepageID        = _sanitize::integer( $homepageID );
		$settingHomepageID = _sanitize::integer( $settingHomepageID );

		/*
		 * Changed `$isHomepage` to `TRUE` in for action attribute ternary so the search is always off the page root.
		 * See this issue: https://connections-pro.com/support/topic/image-grid-category-dropdown/#post-395856
		 * Doesn't seem to cause any issues, but I can not remember the purpose of defaulting to the current page
		 * for the form action when home_id always should default to the current page unless set otherwise.
		 * @link https://connections-pro.com/support/topic/cross-referencing-search-terms/
		 * @link https://connections-pro.com/support/topic/cross-referencing-using-different-fields/
		 *
		 * Reverted the above change due to
		 * @link https://connections-pro.com/support/topic/image-grid-category-dropdown/#post-395816
		 */
		$this->forceHome  = _format::toBoolean( $forceHome );
		$this->homepageID = $this->forceHome ? $settingHomepageID : $homepageID;
		$this->isHomepage = $settingHomepageID !== $homepageID;
	}

	/**
	 * The search form fields.
	 *
	 * @since 10.4.64
	 *
	 * @param array $parameters Field parameters.
	 *
	 * @return Field[]
	 */
	protected function fields( array $parameters ): array {

		$fields = array();

		foreach ( $this->taxonomyTermFields() as $taxonomyTermField ) {

			$fields[] = $taxonomyTermField;
		}

		$fields[] = $this->countryField();
		$fields[] = $this->regionField();
		$fields[] = $this->keywordField();

		return $fields;
	}

	/**
	 * The keyword search field.
	 *
	 * @since 10.4.65
	 *
	 * @return Field\Search
	 */
	private function keywordField(): Field\Search {

		$label       = '<span class="screen-reader-text">' . _x( 'Search for:', 'label', 'connections' ) . '</span>';
		$placeholder = _x( 'Search&hellip;', 'placeholder', 'connections' );
		$term        = Request\Entry_Search_Term::input()->value();

		return Field\Search::create()
						   ->setName( 'cn-s' )
						   ->setValue( $term )
						   ->addAttribute( 'placeholder', $placeholder )
						   ->addLabel( Field\Label::create()->text( $label ), 'implicit' );
	}

	/**
	 * The taxonomy term select fields.
	 *
	 * @since 10.4.65
	 *
	 * @return Field[]
	 */
	private function taxonomyTermFields(): array {

		$fields     = array();
		$taxonomies = Registry::get()->getTaxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			if ( ! $taxonomy->isPublicQueryable() ) {
				continue;
			}

			if ( 'category' === $taxonomy->getSlug() ) {

				$fields[] = Field\Term_Select::create()
											 ->setName( 'cn-cat' )
											 ->setFieldOptions(
												 array(
													 'hide_if_empty' => false,
													 'show_count'    => true,
													 'value_field'   => 'term_id',
												 )
											 );

			} else {

				$fields[] = Field\Term_Select::create(
					array(
						'taxonomy' => $taxonomy->getSlug(),
					)
				)
											 ->setFieldOptions(
												 array(
													 'hide_if_empty' => false,
													 'show_count'    => true,
												 )
											 );
			}
		}

		return $fields;
	}

	/**
	 * The country dropdown select field.
	 *
	 * @since 10.4.66
	 *
	 * @return Field\Select
	 */
	protected function countryField(): Field\Select {

		$countries = array();
		$results   = \cnRetrieve::countries();

		// $keys      = array_map( 'urlencode', $result );
		// $countries = array_combine( $keys, $result );

		foreach ( $results as $country ) {

			$countries[] = array(
				'label' => $country,
				'value' => urlencode( $country ),
			);
		}

		return Field\Select::create()
						   ->setName( 'cn-country' )
						   ->createOptionsFromArray( $countries );
	}

	/**
	 * The region (state/province) dropdown select field.
	 *
	 * @since 10.66
	 *
	 * @return Field\Select
	 */
	protected function regionField(): Field\Select {

		$regions = array();
		$results = \cnRetrieve::regions();

		foreach ( $results as $region ) {

			$regions[] = array(
				'label' => $region,
				'value' => urlencode( $region ),
			);
		}

		return Field\Select::create()
						   ->setName( 'cn-region' )
						   ->createOptionsFromArray( $regions );
	}

	/**
	 * Override and return an empty string for the form header as it is not necessary.
	 *
	 * @since 10.4.64
	 *
	 * @return string
	 */
	protected function getHeader(): string {

		return '';
	}

	/**
	 * If this is the Customizer preview, return an empty string.
	 *
	 * @since 10.4.64
	 *
	 * @return string
	 */
	public function getHTML(): string {

		return is_customize_preview() ? '' : parent::getHTML();
	}
}
