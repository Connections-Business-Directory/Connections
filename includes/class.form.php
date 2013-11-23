<?php

/**
 * Class for creating various form HTML elements.
 *
 * @todo This class is an absolute mess, clean and optimize.
 *
 * @package     Connections
 * @subpackage  HTML Form Elements
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create custom HTML forms.
 */
class cnFormObjects {
	private $nonceBase = 'connections';
	private $validate;
	private $visibiltyOptions = array( 'Public'=>'public', 'Private'=>'private', 'Unlisted'=>'unlisted' );

	public function __construct() {
		// Load the validation class.
		$this->validate = new cnValidate();

		/*
		 * Create the visibility option array based on the current user capability.
		 */
		foreach ( $this->visibiltyOptions as $key => $option ) {
			if ( ! $this->validate->userPermitted( $option ) ) unset( $this->visibiltyOptions[$key] );
		}
	}

	/**
	 * The form open tag.
	 *
	 * @todo Finish adding form tag attributes.
	 * @param array
	 * @return string
	 */
	public function open( $attr ) {
		if ( isset( $attr['name'] ) ) $attr['name'] = 'name="' . $attr['name'] . '" ';
		if ( isset( $attr['action'] ) ) $attr['action'] = 'action="' . $attr['action'] . '" ';
		if ( isset( $attr['accept'] ) ) $attr['accept'] = 'accept="' . $attr['accept'] . '" ';
		if ( isset( $attr['accept-charset'] ) ) $attr['accept-charset'] = 'accept-charset="' . $attr['accept-charset'] . '" ';
		if ( isset( $attr['enctype'] ) ) $attr['enctype'] = 'enctype="' . $attr['enctype'] . '" ';
		if ( isset( $attr['method'] ) ) $attr['method'] = 'method="' . $attr['method'] . '" ';

		$out = '<form ';

		foreach ( $attr as $key => $value ) {
			$out .= $value;
		}

		echo $out , '>';
	}

	/**
	 *
	 *
	 * @return string HTML close tag
	 */
	public function close() {
		echo '</form>';
	}

	//Function inspired from:
	//http://www.melbournechapter.net/wordpress/programming-languages/php/cman/2006/06/16/php-form-input-and-cross-site-attacks/
	/**
	 * Creates a random token.
	 *
	 * @param string  $formId The form ID
	 *
	 * @return string
	 */
	public function token( $formId = NULL ) {
		$token = md5( uniqid( rand(), true ) );

		return $token;
	}

	/**
	 * Retrieves or displays the nonce field for forms using wp_nonce_field.
	 *
	 * @param string  $action  Action name.
	 * @param string  $item    [optional] Item name. Use when protecting multiple items on the same page.
	 * @param string  $name    [optional] Nonce name.
	 * @param bool    $referer [optional] Whether to set and display the refer field for validation.
	 * @param bool    $echo    [optional] Whether to display or return the hidden form field.
	 * @return string
	 */
	public function tokenField( $action, $item = FALSE, $name = '_cn_wpnonce', $referer = TRUE, $echo = TRUE ) {
		$name = esc_attr( $name );

		if ( $item == FALSE ) {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action, $name, $referer, FALSE );

		} else {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action . '_' . $item, $name, $referer, FALSE );
		}

		if ( $echo ) echo $token;

		// if ( $referer ) wp_referer_field( $echo, 'previous' );

		return $token;
	}

	/**
	 * Retrieves URL with nonce added to the query string.
	 *
	 * @param string  $actionURL URL to add the nonce to.
	 * @param string  $item      Nonce action name.
	 * @return string
	 */
	public function tokenURL( $actionURL, $item ) {

		return wp_nonce_url( $actionURL, $item );
	}

	/**
	 * Generate the complete nonce string, from the nonce base, the action and an item.
	 *
	 * @param string  $action Action name.
	 * @param string  $item   [optional] Item name. Use when protecting multiple items on the same page.
	 * @return string
	 */
	public function getNonce( $action, $item = FALSE ) {

		if ( $item == FALSE ) {

			$nonce = $this->nonceBase . '_' . $action;

		} else {

			$nonce = $this->nonceBase . '_' . $action . '_' . $item;
		}

		return $nonce;
	}

	/**
	 * Renders a select drop down.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param string  $name    The input option id/name value.
	 * @param array   $options An associative array. Key is the option value and the value is the option name.
	 * @param string  $value   [optional] The selected option.
	 * @param string  $class   The class applied to the select.
	 * @param string  $id      UNUSED
	 *
	 * @return string
	 */
	public function buildSelect( $name, $options, $value = '', $class='', $id='' ) {

		$select = cnHTML::field(
			array(
				'type'     => 'select',
				'class'    => $class,
				'id'       => $name,
				'options'  => $options,
				'required' => FALSE,
				'label'    => '',
				'return'   => TRUE,
			),
			$value
		);

		return $select;
	}

	/**
	 * Renders a radio group.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param string  $name    The input option id/name value.
	 * @param string  $id      UNUSED
	 * @param array   $options An associative array. Key is the option name and the value is the option value.
	 * @param string  $value   [optional] The selected option.
	 *
	 * @return string
	 */
	public function buildRadio( $name, $id, $options, $value = '' ) {

		$radio = cnHTML::field(
			array(
				'type'     => 'radio',
				'format'   => 'block',
				'class'    => '',
				'id'       => $name,
				'options'  => array_flip( $options ), // The options array is flipped to preserve backward compatibility.
				'required' => FALSE,
				'return'   => TRUE,
			),
			$value
		);

		return $radio;
	}

	/**
	 * Registers the entry edit form meta boxes
	 *
	 * @access private
	 * @since unknown
	 * @param string  $pageHook The page hook to add the entry edit metaboxes to.
	 * @return void
	 */
	public function registerEditMetaboxes( $pageHook ) {
		/*
		 * Interestingly if either 'submitdiv' or 'linksubmitdiv' is used as
		 * the $id in the add_meta_box function it will show up as a meta box
		 * that can not be hidden when the Screen Options tab is output via the
		 * meta_box_prefs function
		 */
		add_meta_box( 'submitdiv', __( 'Publish', 'connections' ), array( $this, 'metaboxPublish' ), $pageHook, 'side', 'core' );
		add_meta_box( 'categorydiv', __( 'Categories', 'connections' ), array( $this, 'metaboxCategories' ), $pageHook, 'side', 'core' );
		add_meta_box( 'metabox-image', __( 'Image', 'connections' ), array( $this, 'metaboxImage' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-logo', __( 'Logo', 'connections' ), array( $this, 'metaboxLogo' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-address', __( 'Addresses', 'connections' ), array( $this, 'metaboxAddress' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-phone', __( 'Phone Numbers', 'connections' ), array( $this, 'metaboxPhone' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-email', __( 'Email Addresses', 'connections' ), array( $this, 'metaboxEmail' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-messenger', __( 'Messenger IDs', 'connections' ), array( $this, 'metaboxMessenger' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-social-media', __( 'Social Media IDs', 'connections' ), array( $this, 'metaboxSocialMedia' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-links', __( 'Links', 'connections' ), array( $this, 'metaboxLinks' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-date', __( 'Dates', 'connections' ), array( $this, 'metaboxDates' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-bio', __( 'Biographical Info', 'connections' ), array( $this, 'metaboxBio' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-note', __( 'Notes', 'connections' ), array( $this, 'metaboxNotes' ), $pageHook, 'normal', 'core' );
	}

	/**
	 * Registers the Dashboard meta boxes
	 *
	 * @access private
	 * @since 0.7.1.6
	 * @return void
	 */
	public function registerDashboardMetaboxes() {
		global $connections;

		add_meta_box( 'metabox-news', __( 'News', 'connections' ), array( &$this, 'metaboxNews' ), $connections->pageHook->dashboard, 'left', 'core', array( 'feed' => 'http://feeds.feedburner.com/connections-pro/news' ) );
		add_meta_box( 'metabox-upgrade-modules', __( 'Pro Modules Update Notices', 'connections' ), array( &$this, 'metaboxNews' ), $connections->pageHook->dashboard, 'left', 'core', array( 'feed' => 'http://feeds.feedburner.com/connections-pro-modules' ) );
		add_meta_box( 'metabox-upgrade-templates', __( 'Template Update Notices', 'connections' ), array( &$this, 'metaboxNews' ), $connections->pageHook->dashboard, 'left', 'core', array( 'feed' => 'http://feeds.feedburner.com/connections-templates' ) );

		add_meta_box( 'metabox-moderate', __( 'Awaiting Moderation', 'connections' ), array( &$this, 'metaboxModerate' ), $connections->pageHook->dashboard, 'left', 'core' );
		add_meta_box( 'metabox-recent-added', __( 'Recently Added', 'connections' ), array( &$this, 'metaboxRecentAdded' ), $connections->pageHook->dashboard, 'left', 'core' );
		add_meta_box( 'metabox-recent-modified', __( 'Recently Modified', 'connections' ), array( &$this, 'metaboxRecentModified' ), $connections->pageHook->dashboard, 'left', 'core' );

		add_meta_box( 'metabox-quick-links', __( 'Quick Links', 'connections' ), array( &$this, 'metaBoxButtons' ), $connections->pageHook->dashboard, 'right', 'core' );
		add_meta_box( 'metabox-anniversary-today', __( 'Today\'s Anniversaries', 'connections' ), array( &$this, 'metaboxAnniversaryToday' ), $connections->pageHook->dashboard, 'right', 'core' );
		add_meta_box( 'metabox-birthday-today', __( 'Today\'s Birthdays', 'connections' ), array( &$this, 'metaboxBirthdayToday' ), $connections->pageHook->dashboard, 'right', 'core' );
		add_meta_box( 'metabox-anniversary-upcoming', __( 'Upcoming Anniversaries', 'connections' ), array( &$this, 'metaboxAnniversaryUpcoming' ), $connections->pageHook->dashboard, 'right', 'core' );
		add_meta_box( 'metabox-birthday-upcoming', __( 'Upcoming Birthdays', 'connections' ), array( &$this, 'metaboxBirthdayUpcoming' ), $connections->pageHook->dashboard, 'right', 'core' );

		add_meta_box( 'metabox-system', __( 'System', 'connections' ), array( &$this, 'metaboxSystem' ), $connections->pageHook->dashboard, 'right', 'core' );
	}

	public function metaBoxButtons() {
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
				<a class="button large blue full" href="http://connections-pro.com/extensions/"><span><?php _e( 'Pro Modules', 'connections' ); ?></span></a>
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
	 * Outputs Connections Blog/News Feed.
	 *
	 * @author Alex Rabe (http://alexrabe.de/)
	 * @since 0.7.1.6
	 * @param unknown $post
	 * @param unknown $metabox array
	 * @return string
	 */
	public function metaboxNews( $post, $metabox ) {
?>
		<div class="rss-widget">
		    <?php
		$rss = @fetch_feed( $metabox['args']['feed'] );

		if ( is_object( $rss ) ) {

			if ( is_wp_error( $rss ) ) {
				echo '<p>' , sprintf( __( 'Newsfeed could not be loaded.  Check the <a href="%s">blog</a> to check for updates.', 'connections' ), $metabox['args']['feed'] ) , '</p>';
				echo '</div>'; //close out the rss-widget before returning.
				return;
			}
			elseif ( $rss->get_item_quantity() > 0  ) {
				echo '<ul>';
				foreach ( $rss->get_items( 0, 3 ) as $item ) {
					$link = $item->get_link();
					while ( stristr( $link, 'http' ) != $link )
						$link = substr( $link, 1 );
					$link = esc_url( strip_tags( $link ) );
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
			}
			else {
				'<p>' . _e( 'No updates at this time', 'connections' ) . '</p>';
			}
		}
?>
		</div>
		<?php
	}

	/**
	 * Outputs the Dashboard Today's Birthday Widget.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $data
	 * @return void
	 */
	public function metaboxBirthdayToday( $data = NULL ) {
		$message = create_function( '', 'return "' . __( 'No Birthdays Today', 'connections' ) . '";' );
		add_filter( 'cn_upcoming_no_result_message', $message );

		$atts = array(
			'list_type'        => 'birthday',
			'days'             => '0',
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
	 * Outputs the Dashboard Upcoming Birthdays Widget.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $data
	 * @return string
	 */
	public function metaboxBirthdayUpcoming( $data = NULL ) {
		$message = create_function( '', 'return "' . __( 'No Upcoming Birthdays.', 'connections' ) . '";' );
		add_filter( 'cn_upcoming_no_result_message', $message );

		$atts = array(
			'list_type'        => 'birthday',
			'days'             => '30',
			'include_today'    => FALSE,
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
	 * Outputs the Dashboard Today's Anniversary Widget.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $data
	 */
	public function metaboxAnniversaryToday( $data = NULL ) {
		$message = create_function( '', 'return "' . __( 'No Anniversaries Today', 'connections' ) . '";' );
		add_filter( 'cn_upcoming_no_result_message', $message );

		$atts = array(
			'list_type'        => 'anniversary',
			'days'             => '0',
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
	 * Outputs the Dashboard Upcoming Anniversary Widget.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $data
	 */
	public function metaboxAnniversaryUpcoming( $data = NULL ) {
		$message = create_function( '', 'return "' . __( 'No Upcoming Anniversaries.', 'connections' ) . '";' );
		add_filter( 'cn_upcoming_no_result_message', $message );

		$atts = array(
			'list_type'        => 'anniversary',
			'days'             => '30',
			'include_today'    => FALSE,
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
	 * Outputs the Dashboard Awaiting Moderation Widget.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $data
	 */
	public function metaboxModerate( $data = NULL ) {
		global $connections;

		function cnMetaboxModerateAtts( $atts ) {
			$atts['status'] = 'pending';

			return $atts;
		}

		add_filter( 'cn_list_atts_permitted', 'cnMetaboxModerateAtts', 9 );
		add_filter( 'cn_list_results', array( $connections->retrieve, 'removeUnknownDateAdded' ), 9 );

		remove_action( 'cn_action_list_actions', array( 'cnTemplatePart', 'listActions' ) );

		$atts = array(
			'order_by'        => 'date_added|SORT_ASC',
			'template'        => 'dashboard-recent-added',
			'show_alphaindex' => FALSE,
			'show_alphahead'  => FALSE,
		);

		connectionsEntryList( $atts );

		remove_filter( 'cn_list_atts_permitted', 'cnMetaboxModerateAtts', 9 );
		remove_filter( 'cn_list_results', array( $connections->retrieve, 'removeUnknownDateAdded' ), 9 );
	}

	/**
	 * Outputs the Dashboard Recently Added Widget.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $data
	 */
	public function metaboxRecentAdded( $data = NULL ) {
		global $connections;

		add_filter( 'cn_list_results', array( $connections->retrieve, 'removeUnknownDateAdded' ), 9 );

		remove_action( 'cn_action_list_actions', array( 'cnTemplatePart', 'listActions' ) );

		$atts = array(
			'order_by'        => 'date_added|SORT_DESC',
			'template'        => 'dashboard-recent-added',
			'show_alphaindex' => FALSE,
			'show_alphahead'  => FALSE,
			'limit'           => 10
		);

		connectionsEntryList( $atts );

		remove_filter( 'cn_list_results', array( $connections->retrieve, 'removeUnknownDateAdded' ), 9 );
	}

	/**
	 * Outputs the Dashboard Recently Modified Widget.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $data
	 */
	public function metaboxRecentModified( $data = NULL ) {
		global $connections;

		remove_action( 'cn_action_list_actions', array( 'cnTemplatePart', 'listActions' ) );

		$atts = array(
			'order_by'        => 'date_modified|SORT_DESC',
			'template'        => 'dashboard-recent-modified',
			'show_alphaindex' => FALSE,
			'show_alphahead'  => FALSE,
			'limit'           => 10
		);

		connectionsEntryList( $atts );
	}

	/**
	 * Outputs the Server information.
	 *
	 * @author GamerZ (http://www.lesterchan.net) && Alex Rabe (http://alexrabe.de/)
	 * @since 0.7.1.6
	 */
	public function metaboxSystem() {
		global $wpdb, $wp_version, $connections;

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
			<li>Connections: <?php echo $connections->options->getVersion() ?></li>
			<li>Database: <?php echo $connections->options->getDBVersion() ?></li>
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

		<h4><strong><?php _e( 'Server Configuration','connections' );?></strong></h4>

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

	/**
	 * Renders the publish meta box.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instance of the cnEntry object.
	 */
	public function metaboxPublish( $entry ) {

		cnEntryMetabox::publish( $entry, $metabox = array() );
	}

	/**
	 * Renders the category meta box.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instance of the cnEntry object.
	 */
	public function metaboxCategories( $entry = NULL ) {

		cnEntryMetabox::category( $entry, $metabox = array() );
	}

	/**
	 * Renders the name metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxName( $entry ) {

		cnEntryMetabox::name( $entry, $metabox = array() );
	}

	/**
	 * Renders the image metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxImage( $entry ) {

		cnEntryMetabox::image( $entry, $metabox = array() );
	}

	/**
	 * Renders the logo metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxLogo( $entry ) {

		cnEntryMetabox::logo( $entry, $metabox = array() );
	}

	/**
	 * Renders the address metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxAddress( $entry ) {

		cnEntryMetabox::address( $entry, $metabox = array() );
	}

	/**
	 * Renders the phone metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxPhone( $entry = NULL ) {

		cnEntryMetabox::phone( $entry, $metabox = array() );
	}

	/**
	 * Renders the email metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxEmail( $entry = NULL ) {

		cnEntryMetabox::email( $entry, $metabox = array() );
	}

	/**
	 * Renders the messenger metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxMessenger( $entry = NULL ) {

		cnEntryMetabox::messenger( $entry, $metabox = array() );
	}

	/**
	 * Renders the social media metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxSocialMedia( $entry = NULL ) {

		cnEntryMetabox::social( $entry, $metabox = array() );
	}

	/**
	 * Renders the links metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxLinks( $entry = NULL ) {

		cnEntryMetabox::links( $entry, $metabox = array() );
	}

	/**
	 * Renders the dates metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxDates( $entry = NULL ) {

		cnEntryMetabox::date( $entry, $metabox = array() );
	}

	/**
	 * Renders the bio metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxBio( $entry ) {

		$metabox = new cnMetabox_Render();

		$field = array(
			'args' => array(
				'id'       => 'metabox-bio',
				'title'    => __( 'Biographical Info', 'connections' ),
				'context'  => 'normal',
				'priority' => 'core',
				'fields' => array(
					array(
						'id'         => 'bio',
						'type'       => 'rte',
						'value'      => 'getBio',
					),
				),
			),
		);

		$metabox->render( $entry, $field );
	}

	/**
	 * Renders the notes metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxNotes( $entry ) {

		$metabox = new cnMetabox_Render();

		$field = array(
			'args' => array(
				'id'       => 'metabox-note',
				'title'    => __( 'Notes', 'connections' ),
				'context'  => 'normal',
				'priority' => 'core',
				'fields' => array(
					array(
						'id'         => 'notes',
						'type'       => 'rte',
						'value'      => 'getNotes',
					),
				),
			),
		);

		$metabox->render( $entry, $field );
	}
}


class cnCategoryObjects {
	private $rowClass = '';

	public function buildCategoryRow( $type, $parents, $level = 0, $selected = NULL ) {
		$out = NULL;

		foreach ( $parents as $child ) {
			$category = new cnCategory( $child );

			if ( $type === 'table' ) $out .= $this->buildTableRowHTML( $child, $level );
			if ( $type === 'option' ) $out .= $this->buildOptionRowHTML( $child, $level, $selected );
			if ( $type === 'checklist' ) $out .= $this->buildCheckListHTML( $child, $level, $selected );

			if ( is_array( $category->getChildren() ) ) {
				++$level;
				if ( $type === 'table' ) $out .= $this->buildCategoryRow( 'table', $category->getChildren(), $level );
				if ( $type === 'option' ) $out .= $this->buildCategoryRow( 'option', $category->getChildren(), $level, $selected );
				if ( $type === 'checklist' ) $out .= $this->buildCategoryRow( 'checklist', $category->getChildren(), $level, $selected );
				--$level;
			}

		}

		$level = 0;
		return $out;
	}

	private function buildTableRowHTML( $term, $level ) {
		global $connections;
		$form = new cnFormObjects();
		$category = new cnCategory( $term );
		$pad = str_repeat( '&#8212; ', max( 0, $level ) );
		$this->rowClass = 'alternate' == $this->rowClass ? '' : 'alternate';

		/*
		 * Genreate the edit & delete tokens.
		 */
		$editToken = $form->tokenURL( 'admin.php?page=connections_categories&cn-action=edit_category&id=' . $category->getId(), 'category_edit_' . $category->getId() );
		$deleteToken = $form->tokenURL( 'admin.php?cn-action=delete_category&id=' . $category->getId(), 'category_delete_' . $category->getId() );

		$out = '<tr id="cat-' . $category->getId() . '" class="' . $this->rowClass . '">';
		$out .= '<th class="check-column">';
		$out .= '<input type="checkbox" name="category[]" value="' . $category->getId() . '"/>';
		$out .= '</th>';
		$out .= '<td class="name column-name"><a class="row-title" href="' . $editToken . '">' . $pad . $category->getName() . '</a><br />';

		if ( $category->getSlug() !== 'uncategorized' || $category->getName() !== 'Uncategorized' ) {
			$out .= '<div class="row-actions">';
			$out .= '<span class="edit"><a href="' . $editToken . '">' . __( 'Edit', 'connections' ) . '</a> | </span>';
			$out .= '<span class="delete"><a onclick="return confirm(\'You are about to delete this category. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . $deleteToken . '">' . __( 'Delete', 'connections' ) . '</a></span>';
			$out .= '</div>';
		}

		$out .= '</td>';
		$out .= '<td class="description column-description">' . $category->getDescription() . '</td>';
		$out .= '<td class="slug column-slug">' . $category->getSlug() . '</td>';
		$out .= '<td>';
		/*
				 * Genreate the category link token URL.
				 */
		// $categoryFilterURL = $form->tokenURL( 'admin.php?connections_process=true&process=manage&action=filter&category_id=' . $category->getId(), 'filter' );
		$categoryFilterURL = $form->tokenURL( 'admin.php?cn-action=filter&category=' . $category->getId(), 'filter' );

		if ( (integer) $category->getCount() > 0 ) {
			$out .= '<strong>' . __( 'Count', 'connections' ) . ':</strong> ' . '<a href="' . $categoryFilterURL . '">' . $category->getCount() . '</a><br />';
		}
		else {
			$out .= '<strong>' . __( 'Count', 'connections' ) . ':</strong> ' . $category->getCount() . '<br />';
		}

		$out .= '<strong>' . __( 'ID', 'connections' ) . ':</strong> ' . $category->getId();
		$out .= '</td>';
		$out .= '</tr>';

		return $out;
	}

	private function buildOptionRowHTML( $term, $level, $selected ) {
		global $rowClass;
		$selectString = NULL;

		$category = new cnCategory( $term );
		$pad = str_repeat( '&nbsp;&nbsp;&nbsp;', max( 0, $level ) );
		if ( $selected == $category->getId() ) $selectString = ' SELECTED ';

		$out = '<option value="' . $category->getId() . '"' . $selectString . '>' . $pad . $category->getName() . '</option>';

		return $out;
	}

	private function buildCheckListHTML( $term, $level, $checked ) {
		global $rowClass;

		$category = new cnCategory( $term );
		$pad = str_repeat( '&nbsp;&nbsp;&nbsp;', max( 0, $level ) );

		if ( !empty( $checked ) ) {
			if ( in_array( $category->getId(), $checked ) ) {
				$checkString = ' CHECKED ';
			}
			else {
				$checkString = NULL;
			}
		}
		else {
			$checkString = NULL;
		}

		$out = '<li id="category-' . $category->getId() . '" class="category"><label class="selectit">' . $pad . '<input id="check-category-' . $category->getId() . '" type="checkbox" name="entry_category[]" value="' . $category->getId() . '" ' . $checkString . '> ' . $category->getName() . '</input></label></li>';

		return $out;
	}

	public function showForm( $data = NULL ) {
		global $connections;
		$form = new cnFormObjects();
		$category = new cnCategory( $data );
		$parent = new cnCategory( $connections->retrieve->category( $category->getParent() ) );
		$level = NULL;

		$out = '<div class="form-field form-required connectionsform">';
		$out .= '<label for="cat_name">' . __( 'Name', 'connections' ) . '</label>';
		$out .= '<input type="text" aria-required="true" size="40" value="' . $category->getName() . '" id="category_name" name="category_name"/>';
		$out .= '<input type="hidden" value="' . $category->getID() . '" id="category_id" name="category_id"/>';
		$out .= '<p>' . __( 'The name is how it appears on your site.', 'connections' ) . '</p>';
		$out .= '</div>';

		$out .= '<div class="form-field connectionsform">';
		$out .= '<label for="category_nicename">' . __( 'Slug', 'connections' ) . '</label>';
		$out .= '<input type="text" size="40" value="' . $category->getSlug() . '" id="category_slug" name="category_slug"/>';
		$out .= '<p>' . __( 'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'connections' ) . '</p>';
		$out .= '</div>';

		$out .= '<div class="form-field connectionsform">';
		$out .= '<label for="category_parent">' . __( 'Parent', 'connections' ) . '</label>';
		$out .= '<select class="postform" id="category_parent" name="category_parent">';
		$out .= '<option value="0">' . __( 'None', 'connections' ) . '</option>';
		$out .= $this->buildCategoryRow( 'option', $connections->retrieve->categories(), $level, $parent->getID() );
		$out .= '</select>';
		$out .= '<p>' . __( 'Categories can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.', 'connections' ) . '</p>';
		$out .= '</div>';

		// $out .= '<div class="form-field connectionsform">';
		// $out .= '<label for="category_description">' . __( 'Description', 'connections' ) . '</label>';
		// $out .= '<textarea cols="40" rows="5" id="category_description" name="category_description">' . $category->getDescription() . '</textarea>';
		// $out .= '<p>' . __( 'The description is not displayed by default; however, templates may be created or altered to show it.', 'connections' ) . '</p>';
		// $out .= '</div>';

		ob_start();

		wp_editor( $category->getDescription(),
			'category_description',
			array(
				'media_buttons' => FALSE,
				'tinymce' => array(
					'editor_selector' => 'tinymce',
					'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
					'theme_advanced_buttons2' => '',
					'inline_styles' => TRUE,
					'relative_urls' => FALSE,
					'remove_linebreaks' => FALSE,
					'plugins' => 'inlinepopups,spellchecker,tabfocus,paste,wordpress,wpdialogs'
				)
			)
		);

		$out .= ob_get_clean();

		echo $out;
	}
}
