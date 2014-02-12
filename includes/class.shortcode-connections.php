<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * The core [connections] shortcode.
 *
 * @package     Connections
 * @subpackage  Shortcode API
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

class cnShortcode_Connections extends cnShortcode {

	public static function shortcode( $atts, $content = NULL, $tag = 'connections' ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$html     = 'The new shortcode.';
		$convert  = new cnFormatting();

		$template = self::loadTemplate( $atts );

		if ( $template === FALSE ) return self::loadTemplateError( $atts );

		/*
		 * Now that the template has been loaded, Validate the user supplied shortcode atts.
		 */
		$defaults = array(
			'id'                    => NULL,
			'slug'                  => NULL,
			'category'              => NULL,
			'category_in'           => NULL,
			'exclude_category'      => NULL,
			'category_name'         => NULL,
			'category_slug'         => NULL,
			'wp_current_category'   => FALSE,
			'allow_public_override' => FALSE,
			'private_override'      => FALSE,
			'show_alphaindex'       => cnSettingsAPI::get( 'connections', 'display_results', 'index' ),
			'repeat_alphaindex'     => cnSettingsAPI::get( 'connections', 'display_results', 'index_repeat' ),
			'show_alphahead'        => cnSettingsAPI::get( 'connections', 'display_results', 'show_current_character' ),
			'list_type'             => NULL,
			'order_by'              => NULL,
			'limit'                 => NULL,
			'offset'                => NULL,
			'family_name'           => NULL,
			'last_name'             => NULL,
			'title'                 => NULL,
			'organization'          => NULL,
			'department'            => NULL,
			'city'                  => NULL,
			'state'                 => NULL,
			'zip_code'              => NULL,
			'country'               => NULL,
			'content'               => '',
			'near_addr'             => NULL,
			'latitude'              => NULL,
			'longitude'             => NULL,
			'radius'                => 10,
			'unit'                  => 'mi',
			'template'              => NULL, /* @since version 0.7.1.0 */
			'width'                 => NULL,
			'lock'                  => FALSE,
			'force_home'            => FALSE,
			'home_id'               => in_the_loop() && is_page() ? get_the_id() : cnSettingsAPI::get( 'connections', 'home_page', 'page_id' ),
		);

		$atts = apply_filters( 'cn_list_atts_permitted' , $atts );
		$atts = apply_filters( 'cn_list_atts_permitted-' . $template->getSlug() , $atts );

		$atts = shortcode_atts( $defaults, $atts, $tag ) ;

		$atts = apply_filters( 'cn_list_atts' , $atts );
		$atts = apply_filters( 'cn_list_atts-' . $template->getSlug() , $atts );

		/*
		 * Convert some of the $atts values in the array to boolean.
		 */
		$convert->toBoolean( $atts['allow_public_override'] );
		$convert->toBoolean( $atts['private_override'] );
		$convert->toBoolean( $atts['show_alphaindex'] );
		$convert->toBoolean( $atts['repeat_alphaindex'] );
		$convert->toBoolean( $atts['show_alphahead'] );
		$convert->toBoolean( $atts['wp_current_category'] );
		$convert->toBoolean( $atts['lock'] );
		$convert->toBoolean( $atts['force_home'] );
		// var_dump( $atts );

		/*
		 * The post editor entity encodes the post text we have to decode it
		 * so a match can be made when the query is run.
		 */
		$atts['family_name']   = html_entity_decode( $atts['family_name'] );
		$atts['last_name']     = html_entity_decode( $atts['last_name'] );
		$atts['title']         = html_entity_decode( $atts['title'] );
		$atts['organization']  = html_entity_decode( $atts['organization'] );
		$atts['department']    = html_entity_decode( $atts['department'] );
		$atts['city']          = html_entity_decode( $atts['city'] );
		$atts['state']         = html_entity_decode( $atts['state'] );
		$atts['zip_code']      = html_entity_decode( $atts['zip_code'] );
		$atts['country']       = html_entity_decode( $atts['country'] );
		$atts['category_name'] = html_entity_decode( $atts['category_name'] );

		$atts = apply_filters( 'cn_list_retrieve_atts' , $atts );
		$atts = apply_filters( 'cn_list_retrieve_atts-' . $template->getSlug(), $atts );

		$results = $instance->retrieve->entries( $atts );
		// $html .= print_r($connections->lastQuery , TRUE);

		// Apply any registered filters to the results.
		if ( ! empty( $results ) ) {

			$results = apply_filters( 'cn_list_results', $results );
			$results = apply_filters( 'cn_list_results-' . $template->getSlug() , $results );
			self::addFilterRegistry( 'cn_list_results-' . $template->getSlug() );
		}

		ob_start();

			// Prints the template's CSS file.
			// NOTE: This is primarily to support legacy templates which included a CSS
			// file which was not enqueued in the page header.
			do_action( 'cn_action_css-' . $template->getSlug() , $atts );

			// @TODO: This should be rendered via shortcode.
			// The return to top anchor
			// do_action( 'cn_action_return_to_target', $atts );

		$html .= ob_get_clean();

		return self::removeEOL( $html );
	}

}
