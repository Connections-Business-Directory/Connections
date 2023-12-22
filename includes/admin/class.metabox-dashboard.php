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

use Connections_Directory\Shortcode;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		// Bail if not in admin or doing an AJAX request.
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

			return;
		}

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
			'feed'     => 'https://feeds.feedburner.com/connections-pro/news',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-moderate',
			'title'    => __( 'Awaiting Moderation', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'recent' ),
			'order_by' => 'date_added|SORT_DESC',
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
			'status'   => 'approved',
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
			'status'   => 'approved',
		);

		//self::$metaboxes[] = array(
		//	'id'       => 'metabox-featured-partners',
		//	'title'    => __( 'Featured Partners', 'connections' ),
		//	'pages'    => array( $pages ),
		//	'context'  => 'right',
		//	'priority' => 'core',
		//	'callback' => array( __CLASS__, 'partners' ),
		//);

		self::$metaboxes[] = array(
			'id'       => 'metabox-free-addons',
			'title'    => __( 'Free Addons', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'right',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'extension' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-quick-links',
			'title'    => __( 'Quick Links', 'connections' ),
			'pages'    => array( $pages ),
			'context'  => 'right',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'links' ),
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
			'today'     => true,
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
			'today'     => true,
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
			'today'     => false,
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
			'today'     => false,
		);
	}

	/**
	 * Callback for the `cn_list_retrieve_atts` filter.
	 *
	 * Utilize `suppress_filters` in the "recent" admin dashboard widgets.
	 * required to ensure the Custom Entry Order does not reorder the query results.
	 *
	 * @see cnDashboardMetabox::recent()
	 *
	 * @since 10.4.1
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	public static function suppressFilters( $atts ) {

		$atts['parse_request']    = false;
		$atts['suppress_filters'] = true;

		return $atts;
	}

	/**
	 * Dashboard widget to display a RSS feed.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @param object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param array  $metabox The metabox options array from self::register().
	 */
	public static function feed( $null, $metabox ) {

		?>
		<div class="rss-widget">

			<?php
			$rss = fetch_feed( $metabox['args']['feed'] );

			if ( is_object( $rss ) ) {

				if ( is_wp_error( $rss ) ) {

					/* translators: RSS newsfeed URL. */
					echo '<p>', sprintf( esc_html__( "Newsfeed could not be loaded. Check the <a href='%s'>blog</a> to check for updates.", 'connections' ), esc_url( $metabox['args']['feed'] ) ), '</p>';
					echo '</div>'; // close out the rss-widget before returning.

					return;

				} elseif ( $rss->get_item_quantity() > 0 ) {

					echo '<ul>';

					/** @var SimplePie_Item $item */
					foreach ( $rss->get_items( 0, 3 ) as $item ) {

						$link = $item->get_link();

						while ( stristr( $link, 'http' ) != $link ) {
							$link = substr( $link, 1 );
						}

						$link  = wp_strip_all_tags( $link );
						$title = wp_strip_all_tags( $item->get_title() );

						if ( empty( $title ) ) {
							$title = __( 'Untitled', 'connections' );
						}

						$desc = str_replace(
							array( "\n", "\r" ),
							' ',
							wp_strip_all_tags(
								@html_entity_decode(
									$item->get_description(),
									ENT_QUOTES,
									get_option( 'blog_charset' )
								)
							)
						);

						$desc = wp_html_excerpt( $desc, 360 );

						//$date = $item->get_date();
						//$diff = '';
						//
						//if ( $date ) {
						//	$diff = human_time_diff( strtotime( $date, time() ) );
						//}
						?>
						<li>
							<h3 class="rss-title"><a title="" href='<?php echo esc_url( $link ); ?>'><?php echo esc_html( $title ); ?></a></h3>
							<div class="rss-summary"><?php echo esc_html( $desc ); ?></div>
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
	 * @access private
	 * @since  0.8
	 *
	 * @param object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param array  $metabox The metabox options array from self::register().
	 */
	public static function recent( $null, $metabox ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		add_filter( 'cn_list_results', array( $instance->retrieve, 'removeUnknownDateAdded' ), 9 );
		add_filter( 'cn_list_retrieve_atts', array( __CLASS__, 'suppressFilters' ) );

		remove_action( 'cn_list_actions', array( 'cnTemplatePart', 'listActions' ) );

		$atts = array(
			'order_by'        => $metabox['args']['order_by'],
			'template'        => $metabox['args']['template'],
			'show_alphaindex' => false,
			'show_alphahead'  => false,
			'limit'           => $metabox['args']['limit'],
			'status'          => $metabox['args']['status'],
		);

		Shortcode\Entry_Directory::instance( $atts )->render();

		remove_filter( 'cn_list_results', array( $instance->retrieve, 'removeUnknownDateAdded' ), 9 );
		remove_filter( 'cn_list_retrieve_atts', array( __CLASS__, 'suppressFilters' ) );
	}

	/**
	 * The dashboard widget used to display the upcoming anniversaries and birthdays.
	 *
	 * @access private
	 * @since  0.8
	 *
	 * @param object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param array  $metabox The metabox options array from self::register().
	 */
	public static function celebrate( $null, $metabox ) {

		$atts = array(
			'list_type'        => $metabox['args']['list_type'],
			'days'             => $metabox['args']['days'],
			'include_today'    => $metabox['args']['today'],
			'private_override' => false,
			'date_format'      => cnSettingsAPI::get( 'connections', 'connections_display_general', 'date_format' ),
			'show_lastname'    => true,
			'list_title'       => null,
			'show_title'       => false,
			'template'         => 'dashboard-upcoming',
			'no_results'       => $metabox['args']['message'],
		);

		Shortcode\Upcoming_List::instance( $atts )->render();
	}

	/**
	 * The dashboard widget used to display the QuickLink.
	 *
	 * @access private
	 * @since  0.8
	 */
	public static function links() {

		?>
		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-green cn-button-full" href="https://connections-pro.com/documentation/contents/"><span><?php _e( 'Documentation', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="https://connections-pro.com/faq/"><span><?php _e( 'FAQs', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Have a question, maybe someone else had the same question, please check the FAQs.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="https://connections-pro.com/extensions/"><span><?php _e( 'Extensions', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Extend Connections with the Pro Module Addons.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="https://connections-pro.com/templates/"><span><?php _e( 'Templates', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Check out the template market.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="cn-button cn-button-large cn-button-blue cn-button-full" href="https://wordpress.org/support/plugin/connections/" target="_blank"><span><?php _e( 'Support', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Did you find a bug, please take the time to report it. Thanks.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<?php
	}

	/**
	 * The "Featured Partners" Dashboard admin widget.
	 *
	 * @access private
	 * @since  8.6.8
	 */
	public static function partners() {

		$logo = CN_URL . 'assets/images/tsl-logo-v3.png';
		// $url  = self_admin_url( 'plugin-install.php/?s=Connections+Business+Directory+Mobile+App+Manager+Plugin&tab=search&type=term');
		$url = self_admin_url( 'plugin-install.php?tab=connections' );
		?>
		<div class="two-third">
			<p>Create your very own native mobile app for iOS and Android powered by WordPress and Connections Business Directory using the <a href="https://tinyscreenlabs.com/?tslref=connections">Tiny Screen Labs Mobile App Manager</a>.</p>
		</div>

		<div class="one-third last">
			<p class="center">
				<img src="<?php echo esc_url( $logo ); ?>" style="max-width: 100%">
			</p>
		</div>
		<div class="clearboth"></div>

		<div>
			<p class="center">
				<a class="cn-button cn-button-large cn-button-green cn-button-full" href="<?php echo esc_url( $url ); ?>"><span><?php _e( 'Install Now', 'connections' ); ?></span></a>
			</p>
		</div>

		<?php
	}

	/**
	 * The "Free Addons" Dashboard admin widget.
	 *
	 * @access private
	 * @since  9.3
	 */
	public static function extension() {

		$logo = CN_URL . 'assets/images/icon-256x256.png';
		$url  = is_multisite() ? network_admin_url( 'plugin-install.php?tab=connections' ) : self_admin_url( 'plugin-install.php?tab=connections' );
		?>
		<div class="two-third">
			<p>There are many free addons available for Connections which add additional features. Click the Install Now button to learn more.</p>
		</div>

		<div class="one-third last">
			<p class="center">
				<img src="<?php echo esc_url( $logo ); ?>" style="max-width: 100%">
			</p>
		</div>
		<div class="clearboth"></div>

		<div>
			<p class="center">
				<a class="cn-button cn-button-large cn-button-green cn-button-full" href="<?php echo esc_url( $url ); ?>"><span><?php _e( 'Install Now', 'connections' ); ?></span></a>
			</p>
		</div>

		<?php
	}
}
