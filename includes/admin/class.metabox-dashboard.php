<?php

/**
 * Class registering the core metaboxes for the admin Dashboard.
 *
 * @package     Connections
 * @subpackage  Core Metaboxes
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnDashboardMetabox {

	/**
	 * The dashboard metabox options array.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $metaboxes = array();

	/**
	 * Initiate the dashboard metaboxes.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @return void
	 */
	public static function init() {

		// Bail if doing an AJAX request.
		if ( defined('DOING_AJAX') && DOING_AJAX ) return;

		// Build the array that defines the dashboard metaboxes.
		self::register();

		// Register the dashboard metaboxes the Metabox API.
		foreach ( self::$metaboxes as $atts ) {

			cnMetaboxAPI::add( $atts );
		}
	}

	/**
	 * Register the dashboard metaboxes.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @return void
	 */
	private static function register() {

		$pages = 'toplevel_page_connections_dashboard';

		self::$metaboxes[] = array(
			'id'       => 'metabox-news',
			'title'    => __( 'News', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'feed' ),
			'feed'     => 'http://feeds.feedburner.com/connections-pro/news',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-moderate',
			'title'    => __( 'Awaiting Moderation', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'recent' ),
			'order_by' => 'date_added|SORT_ASC',
			'template' => 'dashboard-recent-added',
			'limit'    => 10,
			'status'   => 'pending',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-recent-added',
			'title'    => __( 'Recently Added', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'recent' ),
			'order_by' => 'date_added|SORT_DESC',
			'template' => 'dashboard-recent-added',
			'limit'    => 10,
			'status'   => 'all',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-recent-modified',
			'title'    => __( 'Recently Modified', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'recent' ),
			'order_by' => 'date_modified|SORT_DESC',
			'template' => 'dashboard-recent-modified',
			'limit'    => 10,
			'status'   => 'all',
		);

		self::$metaboxes[] = array(
			'id'        => 'metabox-anniversary-today',
			'title'     => __( 'Today\'s Anniversaries', 'connections' ),
			'pages'     => array( $pages ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Anniversaries Today', 'connections' ),
			'list_type' => 'anniversary',
			'days'      => 0,
			'today'     => TRUE,
		);

		self::$metaboxes[] = array(
			'id'        => 'metabox-birthday-today',
			'title'     => __( 'Today\'s Birthdays', 'connections' ),
			'pages'     => array( $pages ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Birthdays Today', 'connections' ),
			'list_type' => 'birthday',
			'days'      => 0,
			'today'     => TRUE,
		);

		self::$metaboxes[] = array(
			'id'        => 'metabox-anniversary-upcoming',
			'title'     => __( 'Upcoming Anniversaries', 'connections' ),
			'pages'     => array( $pages ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Upcoming Anniversaries.', 'connections' ),
			'list_type' => 'anniversary',
			'days'      => 30,
			'today'     => FALSE,
		);

		self::$metaboxes[] = array(
			'id'        => 'metabox-birthday-upcoming',
			'title'     => __( 'Upcoming Birthdays', 'connections' ),
			'pages'     => array( $pages ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Upcoming Birthdays.', 'connections' ),
			'list_type' => 'birthday',
			'days'      => 30,
			'today'     => FALSE,
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-quick-links',
			'title'    => __( 'Quick Links', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'right',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'links' ),
		);
	}

	/**
	 * Dashboard widget to display a RSS feed.
	 *
	 * @access public
	 * @since  0.8
	 * @param  object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param  array  $metabox The metabox options array from self::register().
	 * @return string          The RSS feed metabox content.
	 */
	public static function feed( $null, $metabox ) {

		?>
		<div class="rss-widget">

		    <?php
			$rss = fetch_feed( $metabox['args']['feed'] );

			if ( is_object( $rss ) ) {

				if ( is_wp_error( $rss ) ) {

					echo '<p>' , sprintf( __( "Newsfeed could not be loaded. Check the <a href='%s'>blog</a> to check for updates.", 'connections' ), $metabox['args']['feed'] ) , '</p>';
					echo '</div>'; //close out the rss-widget before returning.

					return;

				} elseif ( $rss->get_item_quantity() > 0  ) {

					echo '<ul>';

					foreach ( $rss->get_items( 0, 3 ) as $item ) {

						$link = $item->get_link();

						while ( stristr( $link, 'http' ) != $link )
							$link = substr( $link, 1 );

						$link  = esc_url( strip_tags( $link ) );
						$title = esc_attr( strip_tags( $item->get_title() ) );

						if ( empty( $title ) )
							$title = __( 'Untitled', 'connections' );

						$desc = str_replace( array( "\n", "\r" ), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) ) ) ) );
						$desc = wp_html_excerpt( $desc, 360 );

						$desc = esc_html( $desc );

						$date = $item->get_date();
						$diff = '';

						if ( $date )  $diff = human_time_diff( strtotime( $date, time() ) );
					?>
				          <li>
				          	<h4 class="rss-title"><a title="" href='<?php echo $link; ?>'><?php echo $title; ?></a></h4>
						  	<div class="rss-date"><?php echo $date; ?></div>
				          	<div class="rss-summary"><strong><?php echo $diff; ?> <?php _e( 'ago', 'connections' ); ?></strong> - <?php echo $desc; ?></div>
						  </li>
				        <?php
					}

					echo '</ul>';

				} else {

					'<p>' . _e( 'No updates at this time', 'connections' ) . '</p>';
				}
			}
		?>
		</div>
		<?php
	}

	/**
	 * The dashboard widget used to display the recently added/modified entries.
	 *
	 * @access public
	 * @since  0.8
	 * @param  object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param  array  $metabox The metabox options array from self::register().
	 * @return string          The recently added/modifed entries.
	 */
	public static function recent( $null, $metabox ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		add_filter( 'cn_list_results', array( $instance->retrieve, 'removeUnknownDateAdded' ), 9 );

		remove_action( 'cn_list_actions', array( 'cnTemplatePart', 'listActions' ) );

		$atts = array(
			'order_by'        => $metabox['args']['order_by'],
			'template'        => $metabox['args']['template'],
			'show_alphaindex' => FALSE,
			'show_alphahead'  => FALSE,
			'limit'           => $metabox['args']['limit'],
			'status'          => $metabox['args']['status'],
		);

		connectionsEntryList( $atts );

		remove_filter( 'cn_list_results', array( $instance->retrieve, 'removeUnknownDateAdded' ), 9 );
	}

	/**
	 * The dashboard widget used to display the upcoming anniversaries and birthdays.
	 *
	 * @access public
	 * @since  0.8
	 * @param  object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param  array  $metabox The metabox options array from self::register().
	 * @return string          The recently added/modifed entries.
	 */
	public static function celebrate( $null, $metabox ) {

		$message = create_function( '', 'return "' . $metabox['args']['message'] . '";' );

		add_filter( 'cn_upcoming_no_result_message', $message );

		$atts = array(
			'list_type'        => $metabox['args']['list_type'],
			'days'             => $metabox['args']['days'],
			'include_today'    => $metabox['args']['today'],
			'private_override' => FALSE,
			'date_format'      => cnSettingsAPI::get( 'connections', 'connections_display_general', 'date_format' ),
			'show_lastname'    => TRUE,
			'list_title'       => NULL,
			'show_title'       => FALSE,
			'template'         => 'dashboard-upcoming',
		);

		connectionsUpcomingList( $atts );

		remove_filter( 'cn_upcoming_no_result_message', $message );
	}

	/**
	 * The dashboard widget used to display the QuickLink.
	 *
	 * @access public
	 * @since  0.8
	 * @param  object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param  array  $metabox The metabox options array from self::register().
	 * @return string          The QuickLink widget.
	 */
	public static function links() {

		?>
		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-green cn-button-full" href="http://connections-pro.com/documentation/plugin/"><span><?php _e( 'Documentation', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="http://connections-pro.com/faq/"><span><?php _e( 'FAQs', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Have a question, maybe someone else had the same question, please check the FAQs.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="http://connections-pro.com/extensions/"><span><?php _e( 'Extensions', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Extend Connections with the Pro Module Addons.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="http://connections-pro.com/templates/"><span><?php _e( 'Templates', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Check out the template market.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="http://connections-pro.com/support/forum/bug-reports/"><span><?php _e( 'Bug Report', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Did you find a bug, please take the time to report it. Thanks.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="http://connections-pro.com/support/forum/feature-requests/"><span><?php _e( 'Feature Request', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Need a feature, request a feature.', 'connections' ) ?></p>
		</div>
		<div class="clearboth"></div>
		<?php

	}
}

// Init the class.
add_action( 'cn_metabox', array( 'cnDashboardMetabox', 'init' ), 1 );
