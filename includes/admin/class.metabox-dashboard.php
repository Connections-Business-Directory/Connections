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
	 * @since 0.8
	 * @param  object $metabox An Instance of the cnMetaboxAPI.
	 *
	 * @return void
	 */
	public static function init( $metabox ) {

		// Build the array that defines the dashboard metaboxes.
		self::register();

		// Register the dashboard metaboxes the Metabox API.
		foreach ( self::$metaboxes as $atts ) {

			$metabox::add( $atts );
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

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		self::$metaboxes[] = array(
			'id'       => 'metabox-news',
			'title'    => __( 'News', 'connections' ),
			'pages'    => array( $instance->pageHook->dashboard ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'feed' ),
			'feed'     => 'http://feeds.feedburner.com/connections-pro/news',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-upgrade-modules',
			'title'    => __( 'Extension Update Notices', 'connections' ),
			'pages'    => array( $instance->pageHook->dashboard ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'feed' ),
			'feed'     => 'http://feeds.feedburner.com/connections-pro-modules',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-upgrade-templates',
			'title'    => __( 'Template Update Notices', 'connections' ),
			'pages'    => array( $instance->pageHook->dashboard ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'feed' ),
			'feed'     => 'http://feeds.feedburner.com/connections-templates',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-moderate',
			'title'    => __( 'Awaiting Moderation', 'connections' ),
			'pages'    => array( $instance->pageHook->dashboard ),
			'context'  => 'left',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'recent' ),
			'order_by' => 'date_added|SORT_ASC',
			'template' => 'dashboard-recent-added',
			'limit'    => 0,
			'status'   => 'pending',
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-recent-added',
			'title'    => __( 'Recently Added', 'connections' ),
			'pages'    => array( $instance->pageHook->dashboard ),
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
			'pages'    => array( $instance->pageHook->dashboard ),
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
			'pages'     => array( $instance->pageHook->dashboard ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Anniversaries Today', 'connections' ),
			'list_type' => 'anniversary',
			'days'      => 0,
		);

		self::$metaboxes[] = array(
			'id'        => 'metabox-birthday-today',
			'title'     => __( 'Today\'s Birthdays', 'connections' ),
			'pages'     => array( $instance->pageHook->dashboard ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Birthdays Today', 'connections' ),
			'list_type' => 'birthday',
			'days'      => 0,
		);

		self::$metaboxes[] = array(
			'id'        => 'metabox-anniversary-upcoming',
			'title'     => __( 'Upcoming Anniversaries', 'connections' ),
			'pages'     => array( $instance->pageHook->dashboard ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Upcoming Anniversaries.', 'connections' ),
			'list_type' => 'anniversary',
			'days'      => 30,
		);

		self::$metaboxes[] = array(
			'id'        => 'metabox-birthday-upcoming',
			'title'     => __( 'Upcoming Birthdays', 'connections' ),
			'pages'     => array( $instance->pageHook->dashboard ),
			'context'   => 'right',
			'priority'  => 'core',
			'callback'  => array( __CLASS__, 'celebrate' ),
			'message'   => __( 'No Upcoming Birthdays.', 'connections' ),
			'list_type' => 'birthday',
			'days'      => 30,
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-quick-links',
			'title'    => __( 'Quick Links', 'connections' ),
			'pages'    => array( $instance->pageHook->dashboard ),
			'context'  => 'right',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'links' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-system',
			'title'    => __( 'System', 'connections' ),
			'pages'    => array( $instance->pageHook->dashboard ),
			'context'  => 'right',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'system' ),
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
			$rss = @fetch_feed( $metabox['args']['feed'] );

			if ( is_object( $rss ) ) {

				if ( is_wp_error( $rss ) ) {

					echo '<p>' , sprintf( __( 'Newsfeed could not be loaded.  Check the <a href="%s">blog</a> to check for updates.', 'connections' ), $metabox['args']['feed'] ) , '</p>';
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

		remove_action( 'cn_action_list_actions', array( 'cnTemplatePart', 'listActions' ) );

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
			'private_override' => FALSE,
			'date_format'      => cnSettingsAPI::get( 'connections', 'connections_display_general', 'date_format' ),
			'show_lastname'    => TRUE,
			'list_title'       => NULL,
			'show_title'       => FALSE,
			'template'         => 'dashboard-upcoming'
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
				<a class="button large green full" href="http://connections-pro.com/documentation/plugin/"><span><?php _e( 'Documentation', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/faq/"><span><?php _e( 'FAQs', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Have a question, maybe someone else had the same question, please check the FAQs.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/extensions/"><span><?php _e( 'Extensions', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Extend Connections with the Pro Module Addons.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/templates/"><span><?php _e( 'Templates', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Check out the template market.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/support/forum/bug-reports/"><span><?php _e( 'Bug Report', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Did you find a bug, please take the time to report it. Thanks.', 'connections' ); ?></p>
		</div>
		<div class="clearboth"></div>

		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/support/forum/feature-requests/"><span><?php _e( 'Feature Request', 'connections' ); ?></span></a>
			</p>
		</div>

		<div class="two-third last">
			<p><?php _e( 'Need a feature, request a feature.', 'connections' ) ?></p>
		</div>
		<div class="clearboth"></div>
		<?php

	}

	/**
	 * The dashboard widget used to display the systems info.
	 *
	 * @access public
	 * @since  0.8
	 * @param  object $null    Generally a $post or $entry object. Not used in Connections core.
	 * @param  array  $metabox The metabox options array from self::register().
	 * @return string          The system info.
	 */
	public static function system() {
		global $wpdb, $wp_version;

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$convert = new cnFormatting();

		// Get MYSQL Version
		$sqlversion = $wpdb->get_var( "SELECT VERSION() AS version" );
		// GET SQL Mode
		$mysqlinfo = $wpdb->get_results( "SHOW VARIABLES LIKE 'sql_mode'" );
		if ( is_array( $mysqlinfo ) ) $sql_mode = $mysqlinfo[0]->Value;
		if ( empty( $sql_mode ) ) $sql_mode = __( 'Not set', 'connections' );
		// Get PHP Safe Mode
		if ( ini_get( 'safe_mode' ) ) $safe_mode = __( 'On', 'connections' );
		else $safe_mode = __( 'Off', 'connections' );
		// Get PHP allow_url_fopen
		if ( ini_get( 'allow_url_fopen' ) ) $allow_url_fopen = __( 'On', 'connections' );
		else $allow_url_fopen = __( 'Off', 'connections' );
		// Get PHP Max Upload Size
		if ( ini_get( 'upload_max_filesize' ) ) $upload_max = ini_get( 'upload_max_filesize' );
		else $upload_max = __( 'N/A', 'connections' );
		// Get PHP Output buffer Size
		if ( ini_get( 'pcre.backtrack_limit' ) ) $backtrack_limit = ini_get( 'pcre.backtrack_limit' );
		else $backtrack_limit = __( 'N/A', 'connections' );
		// Get PHP Max Post Size
		if ( ini_get( 'post_max_size' ) ) $post_max = ini_get( 'post_max_size' );
		else $post_max = __( 'N/A', 'connections' );
		// Get PHP Max execution time
		if ( ini_get( 'max_execution_time' ) ) $max_execute = ini_get( 'max_execution_time' );
		else $max_execute = __( 'N/A', 'connections' );
		// Get PHP Memory Limit
		if ( ini_get( 'memory_limit' ) ) $memory_limit = ini_get( 'memory_limit' );
		else $memory_limit = __( 'N/A', 'connections' );
		// Get actual memory_get_usage
		if ( function_exists( 'memory_get_usage' ) ) $memory_usage = round( memory_get_usage() / 1024 / 1024, 2 ) . ' ' . __( 'MByte', 'connections' );
		else $memory_usage = __( 'N/A', 'connections' );
		// required for EXIF read
		if ( is_callable( 'exif_read_data' ) ) $exif = __( 'Yes', 'connections' ). " ( V" . substr( phpversion( 'exif' ), 0, 4 ) . ")" ;
		else $exif = __( 'No', 'connections' );
		// required for meta data
		if ( is_callable( 'iptcparse' ) ) $iptc = __( 'Yes', 'connections' );
		else $iptc = __( 'No', 'connections' );
		// required for meta data
		if ( is_callable( 'xml_parser_create' ) ) $xml = __( 'Yes', 'connections' );
		else $xml = __( 'No', 'connections' );

		?>
		<h4><strong><?php _e( 'Version Information','connections' );?></strong></h4>

		<ul class="settings">
			<li>WordPress: <?php echo $wp_version; ?></li>
			<li>Multisite: <?php echo $convert->toYesNo( is_multisite() ); ?></li>
			<li>Connections: <?php echo $instance->options->getVersion() ?></li>
			<li>Database: <?php echo $instance->options->getDBVersion() ?></li>
		</ul>

		<h4><strong><?php _e( 'Constants','connections' );?></strong></h4>

		<ul class="settings">
			<li><strong>CN_MULTISITE_ENABLED:</strong> <?php echo CN_MULTISITE_ENABLED ? __( 'TRUE', 'connections') : __( 'FALSE', 'connections' ); ?></li>
			<li><strong>CN_ENTRY_TABLE:</strong> <?php echo CN_ENTRY_TABLE ?></li>
			<li><strong>CN_ENTRY_ADDRESS_TABLE:</strong> <?php echo CN_ENTRY_ADDRESS_TABLE ?></li>
			<li><strong>CN_ENTRY_PHONE_TABLE:</strong> <?php echo CN_ENTRY_PHONE_TABLE ?></li>
			<li><strong>CN_ENTRY_EMAIL_TABLE:</strong> <?php echo CN_ENTRY_EMAIL_TABLE ?></li>
			<li><strong>CN_ENTRY_MESSENGER_TABLE:</strong> <?php echo CN_ENTRY_MESSENGER_TABLE ?></li>
			<li><strong>CN_ENTRY_SOCIAL_TABLE:</strong> <?php echo CN_ENTRY_SOCIAL_TABLE ?></li>
			<li><strong>CN_ENTRY_LINK_TABLE:</strong> <?php echo CN_ENTRY_LINK_TABLE ?></li>
			<li><strong>CN_ENTRY_DATE_TABLE:</strong> <?php echo CN_ENTRY_DATE_TABLE ?></li>
			<li><strong>CN_ENTRY_TABLE_META:</strong> <?php echo CN_ENTRY_TABLE_META ?></li>
			<li><strong>CN_TERMS_TABLE:</strong> <?php echo CN_TERMS_TABLE ?></li>
			<li><strong>CN_TERM_TAXONOMY_TABLE:</strong> <?php echo CN_TERM_TAXONOMY_TABLE ?></li>
			<li><strong>CN_TERM_RELATIONSHIP_TABLE:</strong> <?php echo CN_TERM_RELATIONSHIP_TABLE ?></li>
			<li><strong>CN_DIR_NAME:</strong> <?php echo CN_DIR_NAME ?></li>
			<li><strong>CN_BASE_NAME:</strong> <?php echo CN_BASE_NAME ?></li>
			<li><strong>CN_PATH:</strong> <?php echo CN_PATH ?></li>
			<li><strong>CN_URL:</strong> <?php echo CN_URL ?></li>
			<li><strong>CN_RELATIVE_URL:</strong> <?php echo CN_RELATIVE_URL ?></li>
			<li><strong>CN_IMAGE_PATH:</strong> <?php echo CN_IMAGE_PATH ?></li>
			<li><strong>CN_IMAGE_BASE_URL:</strong> <?php echo CN_IMAGE_BASE_URL ?></li>
			<li><strong>CN_IMAGE_RELATIVE_URL:</strong> <?php echo CN_IMAGE_RELATIVE_URL ?></li>
			<li><strong>CN_TEMPLATE_PATH:</strong> <?php echo CN_TEMPLATE_PATH ?></li>
			<li><strong>CN_TEMPLATE_URL:</strong> <?php echo CN_TEMPLATE_URL ?></li>
			<li><strong>CN_TEMPLATE_RELATIVE_URL:</strong> <?php echo CN_TEMPLATE_RELATIVE_URL ?></li>
			<li><strong>CN_CUSTOM_TEMPLATE_PATH:</strong> <?php echo CN_CUSTOM_TEMPLATE_PATH ?></li>
			<li><strong>CN_CUSTOM_TEMPLATE_URL:</strong> <?php echo CN_CUSTOM_TEMPLATE_URL ?></li>
			<li><strong>CN_CUSTOM_TEMPLATE_RELATIVE_URL:</strong> <?php echo CN_CUSTOM_TEMPLATE_RELATIVE_URL ?></li>
			<li><strong>CN_CACHE_PATH:</strong> <?php echo CN_CACHE_PATH ?></li>
		</ul>

		<h4><strong><?php _e( 'Server Configuration', 'connections' );?></strong></h4>

		<ul class="settings">
			<li><?php _e( 'Operating System', 'connections' ); ?> : <span><?php echo PHP_OS; ?>&nbsp;(<?php echo PHP_INT_SIZE * 8?>&nbsp;Bit)</span></li>
			<li><?php _e( 'Server', 'connections' ); ?> : <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
			<li><?php _e( 'Memory usage', 'connections' ); ?> : <span><?php echo $memory_usage; ?></span></li>
			<li><?php _e( 'MYSQL Version', 'connections' ); ?> : <span><?php echo $sqlversion; ?></span></li>
			<li><?php _e( 'SQL Mode', 'connections' ); ?> : <span><?php echo $sql_mode; ?></span></li>
			<li><?php _e( 'PHP Version', 'connections' ); ?> : <span><?php echo PHP_VERSION; ?></span></li>
			<li><?php _e( 'PHP Safe Mode', 'connections' ); ?> : <span><?php echo $safe_mode; ?></span></li>
			<li><?php _e( 'PHP Allow URL fopen', 'connections' ); ?> : <span><?php echo $allow_url_fopen; ?></span></li>
			<li><?php _e( 'PHP Memory Limit', 'connections' ); ?> : <span><?php echo $memory_limit; ?></span></li>
			<li><?php _e( 'PHP Max Upload Size', 'connections' ); ?> : <span><?php echo $upload_max; ?></span></li>
			<li><?php _e( 'PHP Max Post Size', 'connections' ); ?> : <span><?php echo $post_max; ?></span></li>
			<li><?php _e( 'PCRE Backtracking Limit', 'connections' ); ?> : <span><?php echo $backtrack_limit; ?></span></li>
			<li><?php _e( 'PHP Max Script Execute Time', 'connections' ); ?> : <span><?php echo $max_execute; ?>s</span></li>
			<li><?php _e( 'PHP Exif support', 'connections' ); ?> : <span><?php echo $exif; ?></span></li>
			<li><?php _e( 'PHP IPTC support', 'connections' ); ?> : <span><?php echo $iptc; ?></span></li>
			<li><?php _e( 'PHP XML support', 'connections' ); ?> : <span><?php echo $xml; ?></span></li>
		</ul>

		<h4><strong><?php _e( 'Graphic Library','connections' );?></strong></h4>

		<?php

		if ( function_exists( "gd_info" ) ) {
			$info = gd_info();
			$keys = array_keys( $info );

			echo '<ul class="settings">';

			for ( $i = 0; $i < count( $keys ); $i++ ) {
				if ( is_bool( $info[ $keys[ $i ] ] ) )
					echo "<li> " . $keys[ $i ] ." : <span>" . $convert->toYesNo( $info[ $keys[ $i ] ] ) . "</span></li>\n";
				else
					echo "<li> " . $keys[ $i ] ." : <span>" . $info[ $keys[ $i ] ] . "</span></li>\n";
			}

			echo '</ul>';

		} else {
			echo '<h4>' . __( 'No GD support', 'connections' ) . '!</h4>';
		}

		?>

		<h4><strong><?php _e( 'Folder Permissions','connections' );?></strong></h4>

		<?php

		echo '<ul class="settings">';
			echo '<li>' , __( 'Image Path Exists:', 'connections' ) , ' ',  $convert->toYesNo( is_dir( CN_IMAGE_PATH ) ) , '</li>';
			if ( is_dir( CN_IMAGE_PATH ) ) echo '<li>' , __( 'Image Path Writeable:', 'connections' ) , ' ', $convert->toYesNo( is_writeable( CN_IMAGE_PATH ) ) , '</li>';

			echo '<li>' , __( 'Template Path Exists:', 'connections' ) , ' ', $convert->toYesNo( is_dir( CN_CUSTOM_TEMPLATE_PATH ) ) , '</li>';
			if ( is_dir( CN_CUSTOM_TEMPLATE_PATH ) ) echo '<li>' , __( 'Template Path Writeable:', 'connections' ) , ' ' , $convert->toYesNo( is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) , '</li>';

			echo '<li>' , __( 'Cache Path Exists:', 'connections' ) , ' ', $convert->toYesNo( is_dir( CN_CACHE_PATH ) ) , '</li>';
			if ( is_dir( CN_CACHE_PATH ) ) echo '<li>' , __( 'Cache Path Writeable:', 'connections' ) , ' ', $convert->toYesNo( is_writeable( CN_CACHE_PATH ) ) , '</li>';
		echo '</ul>';

	}

}
