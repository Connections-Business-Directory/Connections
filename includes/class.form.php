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
	public function token( $formId ) {
		$token = md5( uniqid( rand(), true ) );

		return $token;
	}

	public function tokenCheck( $tokenID, $token ) {
		global $connections;
		$token = attribute_escape( $token );

		/**
		 *
		 *
		 * @TODO: Check for $tokenID.
		 */

		if ( isset( $_SESSION['cn_session']['formTokens'][$tokenID]['token'] ) ) {
			$sessionToken = esc_attr( $_SESSION['cn_session']['formTokens'][$tokenID]['token'] );
		}
		else {
			$connections->setErrorMessage( 'form_no_session_token' );
			$error = TRUE;
		}

		if ( empty( $token ) ) {
			$connections->setErrorMessage( 'form_no_token' );
			$error = TRUE;
		}

		if ( $sessionToken === $token && !$error ) {
			unset( $_SESSION['cn_session']['formTokens'] );
			return TRUE;
		}
		else {
			$connections->setErrorMessage( 'form_token_mismatch' );
			return FALSE;
		}

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
	 * Builds a form select list
	 *
	 * @return HTML form select
	 * @param string  $name
	 * @param array   $value_options      Associative array where the key is the name visible in the HTML output and the value is the option attribute value
	 * @param string  $selected[optional]
	 */
	public function buildSelect( $name, $value_options, $selected=null, $class='', $id='' ) {

		$select = "\n" . '<select' . ( ( empty( $class ) ? '' : ' class="' . $class . '"' ) ) . ( ( empty( $id ) ? '' : ' id="' . $id . '"' ) ) . ' name="' . $name . '">' . "\n";
		foreach ( $value_options as $key=>$value ) {
			$select .= "<option ";
			if ( $value != null ) {
				$select .= "value='" . $key . "'";
			}
			else {
				$select .= "value=''";
			}
			if ( $selected == $key ) $select .= " SELECTED";

			$select .= ">";
			$select .= $value;
			$select .= "</option> \n";
		}
		$select .= "</select> \n";

		return $select;
	}

	/**
	 * Builds and returns radio groups.
	 *
	 * @param object  $name
	 * @param object  $id
	 * @param object  $value_labels      associative string array label name [key] and value [value]
	 * @param object  $checked[optional] value to be selected by default
	 *
	 * @return string
	 */
	public function buildRadio( $name, $id, $value_labels, $checked=null ) {
		$selected = NULL;
		$radio = NULL;
		$count = 0;

		foreach ( $value_labels as $label => $value ) {
			$idplus = $id . '_' . $count;

			if ( $checked == $value ) $selected = 'CHECKED';

			$radio .= '<label for="' . $idplus . '">';
			$radio .= '<input id="' . $idplus . '" type="radio" name="' . $name . '" value="' . $value . '" ' . $selected . '>';
			$radio .= $label . '</label>';

			$selected = null;
			$idplus = null;
			$count = $count + 1;
		}

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
		add_meta_box( 'submitdiv', __( 'Publish', 'connections' ), array( &$this, 'metaboxPublish' ), $pageHook, 'side', 'core' );
		add_meta_box( 'categorydiv', __( 'Categories', 'connections' ), array( &$this, 'metaboxCategories' ), $pageHook, 'side', 'core' );
		add_meta_box( 'metabox-image', __( 'Image', 'connections' ), array( &$this, 'metaboxImage' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-logo', __( 'Logo', 'connections' ), array( &$this, 'metaboxLogo' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-address', __( 'Addresses', 'connections' ), array( &$this, 'metaboxAddress' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-phone', __( 'Phone Numbers', 'connections' ), array( &$this, 'metaboxPhone' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-email', __( 'Email Addresses', 'connections' ), array( &$this, 'metaboxEmail' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-messenger', __( 'Messenger IDs', 'connections' ), array( &$this, 'metaboxMessenger' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-social-media', __( 'Social Media IDs', 'connections' ), array( &$this, 'metaboxSocialMedia' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-links', __( 'Links', 'connections' ), array( &$this, 'metaboxLinks' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-date', __( 'Dates', 'connections' ), array( &$this, 'metaboxDates' ), $pageHook, 'normal', 'core' );
		//add_meta_box('metabox-birthday', __('Birthday', 'connections'), array(&$this, 'metaboxBirthday'), $pageHook, 'normal', 'core');
		//add_meta_box('metabox-anniversary', __('Anniversary', 'connections'), array(&$this, 'metaboxAnniversary'), $pageHook, 'normal', 'core');
		add_meta_box( 'metabox-bio', __( 'Biographical Info', 'connections' ), array( &$this, 'metaboxBio' ), $pageHook, 'normal', 'core' );
		add_meta_box( 'metabox-note', __( 'Notes', 'connections' ), array( &$this, 'metaboxNotes' ), $pageHook, 'normal', 'core' );
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
			<li><strong>CN_IMAGE_PATH:</strong> <?php echo CN_IMAGE_PATH ?></li>
			<li><strong>CN_IMAGE_BASE_URL:</strong> <?php echo CN_IMAGE_BASE_URL ?></li>
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
			<li><strong>CN_TEMPLATE_PATH:</strong> <?php echo CN_TEMPLATE_PATH ?></li>
			<li><strong>CN_TEMPLATE_URL:</strong> <?php echo CN_TEMPLATE_URL ?></li>
			<li><strong>CN_CUSTOM_TEMPLATE_PATH:</strong> <?php echo CN_CUSTOM_TEMPLATE_PATH ?></li>
			<li><strong>CN_CUSTOM_TEMPLATE_URL:</strong> <?php echo CN_CUSTOM_TEMPLATE_URL ?></li>
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
	 * Outputs the publish meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.6
	 * @param array   $entry
	 */
	public function metaboxPublish( $entry = NULL ) {

		$defaults = array(
				'action'                            => NULL,
				'entry_type'                        => array(
					__( 'Individual', 'connections' )   => 'individual',
					__( 'Organization', 'connections' ) => 'organization',
					__( 'Family', 'connections' )       => 'family'
				)
			);

		$atts = wp_parse_args( apply_filters( 'cn_admin_metabox_publish_atts', $defaults ), $defaults );

		if ( isset( $_GET['cn-action'] ) ) {
			$action = esc_attr( $_GET['cn-action'] );
		} else {
			$action = $atts['action'];
		}

		( $entry->getVisibility() ) ? $visibility = $entry->getVisibility() : $visibility = 'unlisted';
		( $entry->getEntryType() ) ? $type = $entry->getEntryType() : $type = 'individual';


		echo '<div id="minor-publishing">';
		echo '<div id="entry-type">';
		echo $this->buildRadio(
			'entry_type',
			'entry_type',
			$atts['entry_type'],
			$type );
		echo '</div>';

		if ( current_user_can( 'connections_edit_entry' ) ) {
			echo '<div id="visibility">';
			echo '<span class="radio_group">' . $this->buildRadio(
				'visibility',
				'vis',
				array(
					__( 'Public', 'connections' ) => 'public',
					__( 'Private', 'connections' ) => 'private',
					__( 'Unlisted', 'connections' ) => 'unlisted'
				),
				$visibility ) . '</span>';
			echo '<div class="clear"></div>';
			echo '</div>';
		}
		echo '</div>';

		echo '<div id="major-publishing-actions">';

		switch ( TRUE ) {
			case ( $action ==  'edit_entry' || $action == 'edit' ):
				echo '<input type="hidden" name="cn-action" value="update_entry"/>';
				echo '<div id="cancel-button"><a href="admin.php?page=connections_manage" class="button button-warning">' , __( 'Cancel', 'connections' ) , '</a></div>';
				echo '<div id="publishing-action"><input  class="button-primary" type="submit" name="update" value="' , __( 'Update', 'connections' ) , '" /></div>';
				break;

			case ( $action == 'copy_entry' || $action == 'copy' ):
				echo '<input type="hidden" name="cn-action" value="duplicate_entry"/>';
				echo '<div id="cancel-button"><a href="admin.php?page=connections_manage" class="button button-warning">' , __( 'Cancel', 'connections' ) , '</a>';
				echo '</div><div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' , __( 'Add Entry', 'connections' ) , '" /></div>';
				break;

			default:
				echo '<input type="hidden" name="cn-action" value="add_entry"/>';
				echo '<div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' , __( 'Add Entry', 'connections' ) , '" /></div>';
				break;
		}

		echo '<div class="clear"></div>';
		echo '</div>';
	}

	/**
	 * Outputs the category meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxCategories( $entry = NULL ) {
		global $connections;

		$categoryObjects = new cnCategoryObjects();

		echo '<div class="categorydiv" id="taxonomy-category">';
		echo '<div id="category-all" class="tabs-panel">';
		echo '<ul id="categorychecklist">';
		echo $categoryObjects->buildCategoryRow( 'checklist', $connections->retrieve->categories(), NULL, $connections->term->getTermRelationships( $entry->getId() ) );
		echo '</ul>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Outputs the name meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxName( $entry = NULL ) {
		global $connections;

		echo '<div id="family" class="form-field">';

		echo '<label for="family_name">' , __( 'Family Name', 'connections' ) , ':</label>';
		echo '<input type="text" name="family_name" value="' . $entry->getFamilyName() . '" />';
		echo '<div id="relations">';

		// --> Start template for Family <-- \\
		echo '<textarea id="relation-template" style="display: none">';
		echo $this->getEntrySelect( 'family_member[::FIELD::][entry_id]' , NULL , 'family-member-name'  );
		echo $this->buildSelect( 'family_member[::FIELD::][relation]', $connections->options->getDefaultFamilyRelationValues() , NULL , 'family-member-relation' );
		echo '</textarea>';
		// --> End template for Family <-- \\

		if ( $entry->getFamilyMembers() ) {
			foreach ( $entry->getFamilyMembers() as $key => $value ) {
				$relation = new cnEntry();
				$relation->set( $key );
				$token = $this->token( $relation->getId() );

				echo '<div id="relation-row-' . $token . '" class="relation">';
				echo $this->getEntrySelect( 'family_member[' . $token . '][entry_id]', $key , 'family-member-name' );
				echo $this->buildSelect( 'family_member[' . $token . '][relation]', $connections->options->getDefaultFamilyRelationValues(), $value  , 'family-member-relation' );
				echo '<a href="#" class="cn-remove cn-button button button-warning" data-type="relation" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a>';
				echo '</div>';

				unset( $relation );
			}
		}

		echo '</div>';
		echo '<p class="add"><a id="add-relation" class="button">' , __( 'Add Relation', 'connections' ) , '</a></p>';

		echo '
			</div>

			<div class="form-field namefield">
					<div class="">';

		echo '
						<div style="float: left; width: 8%">
							<label for="honorific_prefix">' , __( 'Prefix', 'connections' ) , ':</label>
							<input type="text" name="honorific_prefix" value="' . $entry->getHonorificPrefix() . '" />
						</div>';

		echo '
						<div style="float: left; width: 30%">
							<label for="first_name">' , __( 'First Name', 'connections' ) , ':</label>
							<input type="text" name="first_name" value="' . $entry->getFirstName() . '" />
						</div>

						<div style="float: left; width: 24%">
							<label for="middle_name">' , __( 'Middle Name', 'connections' ) , ':</label>
							<input type="text" name="middle_name" value="' . $entry->getMiddleName() . '" />
						</div>

						<div style="float: left; width: 30%">
							<label for="last_name">' , __( 'Last Name', 'connections' ) , ':</label>
							<input type="text" name="last_name" value="' . $entry->getLastName() . '" />
						</div>';

		echo '
						<div style="float: left; width: 8%">
							<label for="honorific_suffix">' , __( 'Suffix', 'connections' ) , ':</label>
							<input type="text" name="honorific_suffix" value="' . $entry->getHonorificSuffix() . '" />
						</div>';

		echo '
						<label for="title">' , __( 'Title', 'connections' ) , ':</label>
						<input type="text" name="title" value="' . $entry->getTitle() . '" />
					</div>
				</div>

				<div class="form-field">
					<div class="organization">
						<label for="organization">' , __( 'Organization', 'connections' ) , ':</label>
						<input type="text" name="organization" value="' . $entry->getOrganization() . '" />

						<label for="department">' , __( 'Department', 'connections' ) , ':</label>
						<input type="text" name="department" value="' . $entry->getDepartment() . '" />';

		echo '
						<div id="contact_name">
							<div class="input inputhalfwidth">
								<label for="contact_first_name">' , __( 'Contact First Name', 'connections' ) , ':</label>
								<input type="text" name="contact_first_name" value="' . $entry->getContactFirstName() . '" />
							</div>
							<div class="input inputhalfwidth">
								<label for="contact_last_name">' , __( 'Contact Last Name', 'connections' ) , ':</label>
								<input type="text" name="contact_last_name" value="' . $entry->getContactLastName() . '" />
							</div>

							<div class="clear"></div>
						</div>';
		echo '
					</div>
			</div>';
	}

	/**
	 * Outputs the image meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxImage( $entry = NULL ) {
		echo '<div class="form-field">';

		if ( $entry->getImageLinked() ) {
			( $entry->getImageDisplay() ) ? $selected = 'show' : $selected = 'hidden';

			$options = $this->buildRadio(
				'imgOptions',
				'imgOptionID_',
				array(
					__( 'Display', 'connections' ) => 'show',
					__( 'Not Displayed', 'connections' ) => 'hidden',
					__( 'Remove', 'connections' ) =>'remove'
				),
				$selected
			);

			echo '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getImageNameProfile() . '" /> <br /> <span class="radio_group">' . $options . '</span></div> <br />';
		}

		echo '<div class="clear"></div>';
		echo '<label for="original_image">' , __( 'Select Image', 'connections' ) , ':';
		echo '<input type="file" value="" name="original_image" size="25" /></label>';

		echo '</div>';
	}

	/**
	 * Outputs the logo meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxLogo( $entry = NULL ) {
		echo '<div class="form-field">';

		if ( $entry->getLogoLinked() ) {
			( $entry->getLogoDisplay() ) ? $selected = 'show' : $selected = 'hidden';

			$options = $this->buildRadio(
				'logoOptions',
				'logoOptionID_',
				array(
					__( 'Display', 'connections' ) => 'show',
					__( 'Not Displayed', 'connections' ) => 'hidden',
					__( 'Remove', 'connections' ) =>'remove'
				),
				$selected
			);

			echo '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getLogoName() . '" /> <br /> <span class="radio_group">' . $options . '</span></div> <br />';
		}

		echo '<div class="clear"></div>';
		echo '<label for="original_logo">' , __( 'Select Logo', 'connections' ) , ':';
		echo '<input type="file" value="" name="original_logo" size="25" /></label>';

		echo '</div>';
	}

	/**
	 * Outputs the address meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxAddress( $entry = NULL ) {
		global $connections;

		echo  '<div class="widgets-sortables ui-sortable form-field" id="addresses">' , "\n";

		// --> Start template <-- \\
		echo  '<textarea id="address-template" style="display: none;">' , "\n";

		echo '<div class="widget-top">' , "\n";
		echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

		echo '<div class="widget-title"><h4>' , "\n";
		echo __( 'Address Type', 'connections' ) , ': ' , $this->buildSelect( 'address[::FIELD::][type]', $connections->options->getDefaultAddressValues() ) , "\n";
		echo '<label><input type="radio" name="address[preferred]" value="::FIELD::"> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
		echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'address[::FIELD::][visibility]', 'address_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
		echo '</h4></div>'  , "\n";

		echo '</div>' , "\n";

		echo '<div class="widget-inside">';

		echo '<div class="address-local">';
		echo '<div class="address-line">';
		echo  '<label for="address">' , __( 'Address Line 1', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][line_1]" value="">';
		echo  '</div>';

		echo '<div class="address-line">';
		echo  '<label for="address">' , __( 'Address Line 2', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][line_2]" value="">';
		echo  '</div>';

		echo '<div class="address-line">';
		echo  '<label for="address">' , __( 'Address Line 3', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][line_3]" value="">';
		echo  '</div>';

		echo  '</div>';

		echo '<div class="address-region">';
		echo  '<div class="input address-city">';
		echo  '<label for="address">' , __( 'City', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][city]" value="">';
		echo  '</div>';
		echo  '<div class="input address-state">';
		echo  '<label for="address">' , __( 'State', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][state]" value="">';
		echo  '</div>';
		echo  '<div class="input address-zipcode">';
		echo  '<label for="address">' , __( 'Zipcode', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][zipcode]" value="">';
		echo  '</div>';
		echo  '</div>';

		echo '<div class="address-country">';
		echo  '<label for="address">' , __( 'Country', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][country]" value="">';
		echo  '</div>';

		echo '<div class="address-geo">';
		echo  '<div class="input address-latitude">';
		echo  '<label for="latitude">' , __( 'Latitude', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][latitude]" value="">';
		echo  '</div>';
		echo  '<div class="input address-longitude">';
		echo  '<label for="longitude">' , __( 'Longitude', 'connections' ) , '</label>';
		echo  '<input type="text" name="address[::FIELD::][longitude]" value="">';
		echo  '</div>';

		echo '<a class="geocode button" data-uid="::FIELD::" href="#">' , __( 'Geocode', 'connections' ) , '</a>';

		echo  '</div>';

		echo  '<div class="clear"></div>';

		echo '<div class="map" id="map-::FIELD::" data-map-id="::FIELD::" style="display: none; height: 400px;">' , __( 'Geocoding Address.', 'connections' ) , '</div>';

		echo  '<br>';
		echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="address" data-token="::FIELD::">' , __( 'Remove', 'connections' ) , '</a></p>';

		echo  '</div>' , "\n";

		echo  '</textarea>' , "\n";
		// --> End template <-- \\


		$addresses = $entry->getAddresses( array(), FALSE );
		//print_r($addresses);

		if ( ! empty( $addresses ) ) {
			foreach ( $addresses as $address ) {
				$token = $this->token( $entry->getId() );
				$selectName = 'address['  . $token . '][type]';
				( $address->preferred ) ? $preferredAddress = 'CHECKED' : $preferredAddress = '';

				echo '<div class="widget address" id="address-row-'  . $token . '">' , "\n";
				echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

				echo '<div class="widget-title"><h4>' , "\n";
				echo __( 'Address Type', 'connections' ) , ': ' , $this->buildSelect( $selectName, $connections->options->getDefaultAddressValues(), $address->type ) , "\n";
				echo '<label><input type="radio" name="address[preferred]" value="' , $token , '" ' , $preferredAddress , '> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
				echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'address[' . $token . '][visibility]', 'address_visibility_'  . $token , $this->visibiltyOptions, $address->visibility ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";

				echo '</div>' , "\n";

				echo '<div class="widget-inside">' , "\n";

				echo '<div class="address-local">' , "\n";
				echo '<div class="address-line">' , "\n";
				echo  '<label for="address">' , __( 'Address Line 1', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][line_1]" value="' , $address->line_1 , '">' , "\n";
				echo '</div>' , "\n";

				echo '<div class="address-line">' , "\n";
				echo  '<label for="address">' , __( 'Address Line 2', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][line_2]" value="' , $address->line_2 , '">' , "\n";
				echo '</div>' , "\n";

				echo '<div class="address-line">' , "\n";
				echo  '<label for="address">' , __( 'Address Line 3', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][line_3]" value="' , $address->line_3 , '">' , "\n";
				echo '</div>' , "\n";
				echo '</div>' , "\n";

				echo '<div class="address-region">' , "\n";
				echo  '<div class="input address-city">' , "\n";
				echo  '<label for="address">' , __( 'City', 'connections' ) , '</label>';
				echo  '<input type="text" name="address[' , $token . '][city]" value="' , $address->city , '">' , "\n";
				echo  '</div>' , "\n";
				echo  '<div class="input address-state">' , "\n";
				echo  '<label for="address">' , __( 'State', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][state]" value="' , $address->state , '">' , "\n";
				echo  '</div>' , "\n";
				echo  '<div class="input address-zipcode">' , "\n";
				echo  '<label for="address">' , __( 'Zipcode', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][zipcode]" value="' , $address->zipcode , '">' , "\n";
				echo  '</div>' , "\n";
				echo  '</div>' , "\n";

				echo '<div class="address-country">' , "\n";
				echo  '<label for="address">' , __( 'Country', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][country]" value="' , $address->country , '">' , "\n";
				echo  '</div>' , "\n";

				echo '<div class="address-geo">' , "\n";
				echo  '<div class="input address-latitude">' , "\n";
				echo  '<label for="latitude">' , __( 'Latitude', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][latitude]" value="' , $address->latitude , '">' , "\n";
				echo  '</div>' , "\n";
				echo  '<div class="input address-longitude">' , "\n";
				echo  '<label for="longitude">' , __( 'Longitude', 'connections' ) , '</label>' , "\n";
				echo  '<input type="text" name="address[' , $token , '][longitude]" value="' , $address->longitude , '">' , "\n";
				echo  '</div>' , "\n";

				echo '<a class="geocode button" data-uid="' , $token , '" href="#">' , __( 'Geocode', 'connections' ) , '</a>';

				echo  '</div>' , "\n";

				echo  '<input type="hidden" name="address[' , $token , '][id]" value="' , $address->id , '">' , "\n";

				echo  '<div class="clear"></div>' , "\n";

				echo '<div class="map" id="map-' , $token , '" data-map-id="' , $token , '" style="display: none; height: 400px;">' , __( 'Geocoding Address.', 'connections' ) , '</div>';

				echo  '<br>';
				echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="address" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a></p>' , "\n";

				echo  '</div>' , "\n";
				echo  '</div>' , "\n";

			}
		}

		echo  '</div>' , "\n";
		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="address" data-container="addresses">' , __( 'Add Address', 'connections' ) , '</a></p>' , "\n";
	}

	/**
	 * Outputs the phone meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxPhone( $entry = NULL ) {
		global $connections;

		echo  '<div class="widgets-sortables ui-sortable form-field" id="phone-numbers">';

		// --> Start template <-- \\
		echo  '<textarea id="phone-template" style="display: none">';

		echo '<div class="widget-top">' , "\n";
		echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

		echo '<div class="widget-title"><h4>' , "\n";
		echo __( 'Phone Type', 'connections' ) , ': ' , $this->buildSelect( 'phone[::FIELD::][type]', $connections->options->getDefaultPhoneNumberValues() ) , "\n";
		echo '<label><input type="radio" name="phone[preferred]" value="::FIELD::"> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
		echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'phone[::FIELD::][visibility]', 'phone_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
		echo '</h4></div>'  , "\n";

		echo '</div>' , "\n";

		echo '<div class="widget-inside">' , "\n";

		echo  '<label>' , __( 'Phone Number', 'connections' ) , '</label><input type="text" name="phone[::FIELD::][number]" value="" style="width: 30%"/>' , "\n";
		echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="phone" data-token="::FIELD::">' , __( 'Remove', 'connections' ) , '</a></p>' , "\n";

		echo '</div>' , "\n";

		echo  '</textarea>';
		// --> End template <-- \\

		$phoneNumbers = $entry->getPhoneNumbers( array(), FALSE );

		if ( ! empty( $phoneNumbers ) ) {

			foreach ( $phoneNumbers as $phone ) {
				$token = $this->token( $entry->getId() );
				$selectName = 'phone['  . $token . '][type]';
				( $phone->preferred ) ? $preferredPhone = 'CHECKED' : $preferredPhone = '';

				echo '<div class="widget phone" id="phone-row-'  . $token . '">' , "\n";
				echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

				echo '<div class="widget-title"><h4>' , "\n";
				echo __( 'Phone Type', 'connections' ) , ': ' , $this->buildSelect( $selectName, $connections->options->getDefaultPhoneNumberValues(), $phone->type ) , "\n";
				echo '<label><input type="radio" name="phone[preferred]" value="' , $token , '" ' , $preferredPhone , '> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
				echo '<span class="visibility">Visibility: ' , $this->buildRadio( 'phone[' . $token . '][visibility]', 'phone_visibility_'  . $token , $this->visibiltyOptions, $phone->visibility ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";

				echo '</div>' , "\n";

				echo '<div class="widget-inside">' , "\n";

				echo  '<label>' , __( 'Phone Number', 'connections' ) , '</label><input type="text" name="phone[' , $token , '][number]" value="' , $phone->number , '" style="width: 30%"/>';
				echo  '<input type="hidden" name="phone[' , $token , '][id]" value="' , $phone->id , '">' , "\n";
				echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="phone" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a></p>';

				echo '</div>' , "\n";
				echo '</div>' , "\n";
			}

		}

		echo  '</div>';
		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="phone" data-container="phone-numbers">' , __( 'Add Phone Number', 'connections' ) , '</a></p>';
	}

	/**
	 * Outputs the email meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxEmail( $entry = NULL ) {
		global $connections;

		echo  '<div class="widgets-sortables ui-sortable form-field" id="email-addresses">';

		// --> Start template <-- \\
		echo  '<textarea id="email-template" style="display: none">';

		echo '<div class="widget-top">' , "\n";
		echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

		echo '<div class="widget-title"><h4>' , "\n";
		echo __( 'Email Type', 'connections' ) , ': ' , $this->buildSelect( 'email[::FIELD::][type]', $connections->options->getDefaultEmailValues() ) , "\n";
		echo '<label><input type="radio" name="email[preferred]" value="::FIELD::"> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
		echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'email[::FIELD::][visibility]', 'email_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
		echo '</h4></div>'  , "\n";

		echo '</div>' , "\n";

		echo '<div class="widget-inside">' , "\n";

		echo  '<label>' , __( 'Email Address', 'connections' ) , '</label><input type="text" name="email[::FIELD::][address]" value="" style="width: 30%"/>' , "\n";
		echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="email" data-token="::FIELD::">' , __( 'Remove', 'connections' ) , '</a></p>' , "\n";

		echo '</div>' , "\n";

		echo  '</textarea>';
		// --> End template <-- \\

		$emailAddresses = $entry->getEmailAddresses( array(), FALSE );

		if ( ! empty( $emailAddresses ) ) {

			foreach ( $emailAddresses as $email ) {
				$token = $this->token( $entry->getId() );
				$selectName = 'email['  . $token . '][type]';
				( $email->preferred ) ? $preferredEmail = 'CHECKED' : $preferredEmail = '';

				echo '<div class="widget email" id="email-row-'  . $token . '">' , "\n";
				echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

				echo '<div class="widget-title"><h4>' , "\n";
				echo __( 'Email Type', 'connections' ) , ': ' , $this->buildSelect( $selectName, $connections->options->getDefaultEmailValues(), $email->type ) , "\n";
				echo '<label><input type="radio" name="email[preferred]" value="' , $token , '" ' , $preferredEmail , '> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
				echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'email[' . $token . '][visibility]', 'email_visibility_'  . $token , $this->visibiltyOptions, $email->visibility ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";

				echo '</div>' , "\n";

				echo '<div class="widget-inside">' , "\n";

				echo  '<label>' , __( 'Email Address', 'connections' ) , '</label><input type="text" name="email[' , $token , '][address]" value="' , $email->address , '" style="width: 30%"/>';
				echo  '<input type="hidden" name="email[' , $token , '][id]" value="' , $email->id , '">' , "\n";
				echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="email" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a></p>';

				echo '</div>' , "\n";
				echo '</div>' , "\n";
			}

		}

		echo  '</div>';
		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="email" data-container="email-addresses">' , __( 'Add Email Address', 'connections' ) , '</a></p>';
	}

	/**
	 * Outputs the messenger meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxMessenger( $entry = NULL ) {
		global $connections;

		echo  '<div class="widgets-sortables ui-sortable form-field" id="im-ids">';

		// --> Start template.  <-- \\
		echo  '<textarea id="im-template" style="display: none">';

		echo '<div class="widget-top">' , "\n";
		echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

		echo '<div class="widget-title"><h4>' , "\n";
		echo __( 'IM Type', 'connections' ) , ': ' , $this->buildSelect( 'im[::FIELD::][type]', $connections->options->getDefaultIMValues() ) , "\n";
		echo '<label><input type="radio" name="im[preferred]" value="::FIELD::"> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
		echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'im[::FIELD::][visibility]', 'im_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
		echo '</h4></div>'  , "\n";

		echo '</div>' , "\n";

		echo '<div class="widget-inside">' , "\n";

		echo  '<label>' , __( 'IM Network ID', 'connections' ) , '</label><input type="text" name="im[::FIELD::][id]" value="" style="width: 30%"/>' , "\n";
		echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="im" data-token="::FIELD::">' , __( 'Remove', 'connections' ) , '</a></p>' , "\n";

		echo '</div>' , "\n";

		echo  '</textarea>';
		// --> End template. <-- \\

		$imIDs = $entry->getIm( array(), FALSE );

		if ( ! empty( $imIDs ) ) {
			foreach ( $imIDs as $network ) {
				$token = $this->token( $entry->getId() );
				$selectName = 'im['  . $token . '][type]';
				( $network->preferred ) ? $preferredIM = 'CHECKED' : $preferredIM = '';

				echo '<div class="widget im" id="im-row-'  . $token . '">' , "\n";
				echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

				echo '<div class="widget-title"><h4>' , "\n";
				echo __( 'IM Type', 'connections' ) , ': ' , $this->buildSelect( $selectName, $connections->options->getDefaultIMValues(), $network->type ) , "\n";
				echo '<label><input type="radio" name="im[preferred]" value="' , $token , '" ' , $preferredIM , '> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
				echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'im[' . $token . '][visibility]', 'im_visibility_'  . $token , $this->visibiltyOptions, $network->visibility ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";

				echo '</div>' , "\n";

				echo '<div class="widget-inside">' , "\n";

				echo  '<label>' , __( 'IM Network ID', 'connections' ) , '</label><input type="text" name="im[' , $token , '][id]" value="' , $network->id , '" style="width: 30%"/>';
				echo  '<input type="hidden" name="im[' , $token , '][uid]" value="' , $network->uid , '">' , "\n";
				echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="im" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a></p>';

				echo '</div>' , "\n";
				echo '</div>' , "\n";
			}

		}

		echo  '</div>';
		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="im" data-container="im-ids">' , __( 'Add Messenger ID', 'connections' ) , '</a></p>';
	}

	/**
	 * Outputs the social media meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxSocialMedia( $entry = NULL ) {
		global $connections;

		echo  '<div class="widgets-sortables ui-sortable form-field" id="social-media">';

		// --> Start template <-- \\
		echo  '<textarea id="social-template" style="display: none">';

		echo '<div class="widget-top">' , "\n";
		echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

		echo '<div class="widget-title"><h4>' , "\n";
		echo __( 'Social Network', 'connections' ) , ': ' , $this->buildSelect( 'social[::FIELD::][type]', $connections->options->getDefaultSocialMediaValues() ) , "\n";
		echo '<label><input type="radio" name="social[preferred]" value="::FIELD::"> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
		echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'social[::FIELD::][visibility]', 'social_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
		echo '</h4></div>'  , "\n";

		echo '</div>' , "\n";

		echo '<div class="widget-inside">' , "\n";

		echo  '<label>' , __( 'URL', 'connections' ) , '</label><input type="text" name="social[::FIELD::][url]" value="http://" style="width: 30%"/>' , "\n";
		echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="social" data-token="::FIELD::">' , __( 'Remove', 'connections' ) , '</a></p>' , "\n";

		echo '</div>' , "\n";

		echo  '</textarea>';
		// --> End template <-- \\

		$socialNetworks = $entry->getSocialMedia( array(), FALSE );

		if ( ! empty( $socialNetworks ) ) {

			foreach ( $socialNetworks as $network ) {
				$token = $this->token( $entry->getId() );
				$selectName = 'social['  . $token . '][type]';
				( $network->preferred ) ? $preferredNetwork = 'CHECKED' : $preferredNetwork = '';

				echo '<div class="widget social" id="social-row-'  . $token . '">' , "\n";
				echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

				echo '<div class="widget-title"><h4>' , "\n";
				echo __( 'Social Network', 'connections' ) , ': ' , $this->buildSelect( $selectName, $connections->options->getDefaultSocialMediaValues(), $network->type ) , "\n";
				echo '<label><input type="radio" name="social[preferred]" value="' , $token , '" ' , $preferredNetwork , '> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
				echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'social[' . $token . '][visibility]', 'social_visibility_'  . $token , $this->visibiltyOptions, $network->visibility ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";

				echo '</div>' , "\n";

				echo '<div class="widget-inside">' , "\n";

				echo  '<label>' , __( 'URL', 'connections' ) , '</label><input type="text" name="social[' , $token , '][url]" value="' , $network->url , '" style="width: 30%"/>';
				echo  '<input type="hidden" name="social[' , $token , '][id]" value="' , $network->id , '">' , "\n";
				echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="social" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a></p>';

				echo '</div>' , "\n";
				echo '</div>' , "\n";
			}

		}

		echo  '</div>';
		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="social" data-container="social-media">' , __( 'Add Social Media ID', 'connections' ) , '</a></p>';
	}

	/**
	 * Outputs the links meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxLinks( $entry = NULL ) {
		global $connections;

		echo  '<div class="widgets-sortables ui-sortable form-field" id="links">';

		// --> Start template <-- \\
		echo  '<textarea id="link-template" style="display: none">';

		echo '<div class="widget-top">' , "\n";
		echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

		echo '<div class="widget-title"><h4>' , "\n";
		echo __( 'Type', 'connections' ) , ': ' , $this->buildSelect( 'link[::FIELD::][type]', $connections->options->getDefaultLinkValues() ) , "\n";
		echo '<label><input type="radio" name="link[preferred]" value="::FIELD::"> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
		echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'link[::FIELD::][visibility]', 'website_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
		echo '</h4></div>'  , "\n";

		echo '</div>' , "\n";

		echo '<div class="widget-inside">' , "\n";

		echo '<div>' , "\n";
		echo  '<label>' , __( 'Title', 'connections' ) , '</label><input type="text" name="link[::FIELD::][title]" value="" style="width: 30%"/>' , "\n";
		echo  '<label>' , __( 'URL', 'connections' ) , '</label><input type="text" name="link[::FIELD::][url]" value="http://" style="width: 30%"/>' , "\n";
		echo '</div>' , "\n";

		echo '<div>' , "\n";
		echo '<span class="target">' , __( 'Target', 'connections' ) , ': ' , $this->buildSelect( 'link[::FIELD::][target]', array( 'new' => __( 'New Window', 'connections' ), 'same' => __( 'Same Window', 'connections' ) ), 'same' ) , '</span>' , "\n";
		echo '<span class="follow">' , $this->buildSelect( 'link[::FIELD::][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), 'nofollow' ) , '</span>' , "\n";
		echo '</div>' , "\n";

		echo '<div>' , "\n";
		echo '<label><input type="radio" name="link[image]" value="::FIELD::"> ' , __( 'Assign link to the image.', 'connections' ) , '</label>' , "\n";
		echo '<label><input type="radio" name="link[logo]" value="::FIELD::"> ' , __( 'Assign link to the logo.', 'connections' ) , '</label>' , "\n";
		// echo '<label><input type="checkbox" name="link[none]" value="::FIELD::"> ' , __( 'None', 'connections' ) , '</label>' , "\n";
		echo '</div>' , "\n";

		echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="link" data-token="::FIELD::">' , __( 'Remove', 'connections' ) , '</a></p>' , "\n";

		echo '</div>' , "\n";

		echo  '</textarea>';
		// --> End template <-- \\

		$links = $entry->getLinks( array(), FALSE );

		if ( ! empty( $links ) ) {

			foreach ( $links as $link ) {
				$token         = $this->token( $entry->getId() );
				$selectName    = 'link['  . $token . '][type]';
				$preferredLink = ( $link->preferred ) ? 'CHECKED' : '';
				$imageLink     = ( $link->image ) ? 'CHECKED' : '';
				$logoLink      = ( $link->logo ) ? 'CHECKED' : '';
				// $noneLink      = ( empty( $imageLink ) && empty( $logoLink ) ) ? 'CHECKED' : '';
				//var_dump($link);

				echo '<div class="widget link" id="link-row-'  . $token . '">' , "\n";
				echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

				echo '<div class="widget-title"><h4>' , "\n";
				echo __( 'Type', 'connections' ) , ': ' , $this->buildSelect( $selectName, $connections->options->getDefaultLinkValues(), $link->type ) , "\n";
				echo '<label><input type="radio" name="link[preferred]" value="' , $token , '" ' , $preferredLink , '> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
				echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'link[' . $token . '][visibility]', 'link_visibility_'  . $token , $this->visibiltyOptions, $link->visibility ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";

				echo '</div>' , "\n";

				echo '<div class="widget-inside">' , "\n";

				echo '<div>' , "\n";
				echo  '<label>' , __( 'Title', 'connections' ) , '</label><input type="text" name="link[' , $token , '][title]" value="' , $link->title , '" style="width: 30%"/>' , "\n";
				echo  '<label>' , __( 'URL', 'connections' ) , '</label><input type="text" name="link[' , $token , '][url]" value="' , $link->url , '" style="width: 30%"/>';
				echo '</div>' , "\n";

				echo '<div>' , "\n";
				echo '<span class="target">' , __( 'Target', 'connections' ) , ': ' , $this->buildSelect( 'link[' . $token . '][target]', array( '_blank' => __( 'New Window', 'connections' ), '_self' => __( 'Same Window', 'connections' ) ), $link->target ) , '</span>' , "\n";
				echo '<span class="follow">' , $this->buildSelect( 'link[' . $token . '][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), $link->followString ) , '</span>' , "\n";
				echo '</div>' , "\n";

				echo '<div>' , "\n";
				echo '<label><input type="radio" name="link[image]" value="' , $token , '" ' , $imageLink , '> ' , __( 'Assign link to the image.', 'connections' ) , '</label>' , "\n";
				echo '<label><input type="radio" name="link[logo]" value="' , $token , '" ' , $logoLink , '> ' , __( 'Assign link to the logo.', 'connections' ) , '</label>' , "\n";
				// echo '<label><input type="checkbox" name="link[none]" value="' , $token , '" ' , $noneLink , '> ' , __( 'None', 'connections' ) , '</label>' , "\n";
				echo '</div>' , "\n";

				echo  '<input type="hidden" name="link[' , $token , '][id]" value="' , $link->id , '">' , "\n";
				echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="link" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a></p>';

				echo '</div>' , "\n";
				echo '</div>' , "\n";
			}

		}

		echo  '</div>';
		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="link" data-container="links">' , __( 'Add Link', 'connections' ) , '</a></p>';
	}

	/**
	 * Outputs the dates box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.2.7
	 * @param array   $entry
	 */
	public function metaboxDates( $entry = NULL ) {
		global $connections;

		echo  '<div class="widgets-sortables ui-sortable form-field" id="dates">';

		// --> Start template <-- \\
		echo  '<textarea id="date-template" style="display: none">';

		echo '<div class="widget-top">' , "\n";
		echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

		echo '<div class="widget-title"><h4>' , "\n";
		echo __( 'Type', 'connections' ) , ': ' , $this->buildSelect( 'date[::FIELD::][type]', $connections->options->getDateOptions() ) , "\n";
		echo '<label><input type="radio" name="date[preferred]" value="::FIELD::"> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
		echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'date[::FIELD::][visibility]', 'date_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
		echo '</h4></div>'  , "\n";

		echo '</div>' , "\n";

		echo '<div class="widget-inside">' , "\n";

		echo '<div>' , "\n";
		echo  '<label>' , __( 'Date', 'connections' ) , '</label><input type="text" class="datepicker" name="date[::FIELD::][date]" value="" style="padding: 2px; width: 17.5em;"/>' , "\n";
		echo '</div>' , "\n";

		echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="date" data-token="::FIELD::">' , __( 'Remove', 'connections' ) , '</a></p>' , "\n";

		echo '</div>' , "\n";

		echo  '</textarea>';
		// --> End template <-- \\

		$dates = $entry->getDates( array(), FALSE );

		if ( ! empty( $dates ) ) {

			foreach ( $dates as $date ) {
				$token = $this->token( $entry->getId() );
				$selectName = 'date['  . $token . '][type]';
				( $date->preferred ) ? $preferredDate = 'CHECKED' : $preferredDate = '';

				echo '<div class="widget date" id="date-row-'  . $token . '">' , "\n";
				echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";

				echo '<div class="widget-title"><h4>' , "\n";
				echo __( 'Type', 'connections' ) , ': ' , $this->buildSelect( $selectName, $connections->options->getDateOptions(), $date->type ) , "\n";
				echo '<label><input type="radio" name="date[preferred]" value="' , $token , '" ' , $preferredDate , '> ' , __( 'Preferred', 'connections' ) , '</label>' , "\n";
				echo '<span class="visibility">' , __( 'Visibility', 'connections' ) , ': ' , $this->buildRadio( 'date[' . $token . '][visibility]', 'date_visibility_'  . $token , $this->visibiltyOptions, $date->visibility ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";

				echo '</div>' , "\n";

				echo '<div class="widget-inside">' , "\n";

				echo '<div>' , "\n";
				echo  '<label>' , __( 'Date', 'connections' ) , '</label><input type="text" name="date[' , $token , '][date]" class="datepicker" value="' , date( 'm/d/Y', strtotime( $date->date ) ) , '" style="padding: 2px; width: 17.5em;"/>' , "\n";
				echo '</div>' , "\n";

				echo  '<input type="hidden" name="date[' , $token , '][id]" value="' , $date->id , '">' , "\n";
				echo  '<p class="remove-button"><a href="#" class="cn-remove cn-button button button-warning" data-type="date" data-token="' . $token . '">' , __( 'Remove', 'connections' ) , '</a></p>';

				echo '</div>' , "\n";
				echo '</div>' , "\n";
			}

		}

		echo  '</div>';
		echo  '<p class="add"><a href="#" class="cn-add cn-button button" data-type="date" data-container="dates">' , __( 'Add Date', 'connections' ) , '</a></p>';
	}

	/**
	 * Outputs the birthday meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @deprecated since 0.7.3
	 * @param array   $entry
	 */
	public function metaboxBirthday( $entry = NULL ) {
		$date = new cnDate();

		echo '<div class="form-field celebrate">
				<span class="selectbox">' , __( 'Birthday', 'connections' ) , ': ' . $this->buildSelect( 'birthday_month', $date->months, $date->getMonth( $entry->getBirthday() ) ) . '</span>
				<span class="selectbox">' . $this->buildSelect( 'birthday_day', $date->days, $date->getDay( $entry->getBirthday() ) ) . '</span>
			</div>';
		echo '<div class="form-field celebrate-disabled"><p>' , __( 'Field not available for this entry type.', 'connections' ) , '</p></div>';
	}

	/**
	 * Outputs the anniversary meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @deprecated since 0.7.3
	 * @param array   $entry
	 */
	public function metaboxAnniversary( $entry = NULL ) {
		$date = new cnDate();

		echo '<div class="form-field celebrate">
				<span class="selectbox">' , __( 'Anniversary', 'connections' ) , ': ' . $this->buildSelect( 'anniversary_month', $date->months, $date->getMonth( $entry->getAnniversary() ) ) . '</span>
				<span class="selectbox">' . $this->buildSelect( 'anniversary_day', $date->days, $date->getDay( $entry->getAnniversary() ) ) . '</span>
			</div>';
		echo '<div class="form-field celebrate-disabled"><p>' , __( 'Field not available for this entry type.', 'connections' ) , '</p></div>';
	}

	/**
	 * Outputs the bio meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxBio( $entry = NULL ) {
		if ( version_compare( $GLOBALS['wp_version'], '3.2.999', '<' ) ) {
			echo "<div class='form-field'>

					<a class='button alignright' id='toggleBioEditor'>' , __('Toggle Editor', 'connections') , '</a>

					<textarea class='tinymce' id='bio' name='bio' rows='15'>" . $entry->getBio() . "</textarea>

			</div>";
		}
		else {
			wp_editor( $entry->getBio(),
				'bio',
				array
				(
					'media_buttons' => FALSE,
					'tinymce' => array
					(
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
		}

	}

	/**
	 * Outputs the notes meta box.
	 *
	 * @author Steven A. Zahm
	 * @since 0.7.1.5
	 * @param array   $entry
	 */
	public function metaboxNotes( $entry = NULL ) {
		if ( version_compare( $GLOBALS['wp_version'], '3.2.999', '<' ) ) {
			echo "<div class='form-field'>

					<a class='button alignright' id='toggleNoteEditor'>' , __('Toggle Editor', 'connections') , '</a>

					<textarea class='tinymce' id='note' name='notes' rows='15'>" . $entry->getNotes() . "</textarea>

			</div>";
		}
		else {
			wp_editor( $entry->getNotes(),
				'notes',
				array
				(
					'media_buttons' => FALSE,
					'tinymce' => array
					(
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
		}
	}

	private function getEntrySelect( $name, $selected = NULL, $class = NULL , $id = NULL ) {
		global $wpdb, $connections;

		$atts['list_type'] = 'individual';
		$atts['category'] = NULL;
		$atts['visibility'] = NULL;

		$results = $connections->retrieve->entries( $atts );

		$out = '<select' . ( ( empty( $class ) ? '' : ' class="' . $class . '"' ) ) . ( ( empty( $id ) ? '' : ' id="' . $id . '"' ) ) . ' name="' . $name . '">';
		$out .= '<option value="">' . __( 'Select Entry', 'connections' ) . '</option>';
		foreach ( $results as $row ) {
			$entry = new cnEntry( $row );
			$out .= '<option value="' . $entry->getId() . '"';
			if ( $selected == $entry->getId() ) $out .= ' SELECTED';
			$out .= '>' . $entry->getFullLastFirstName() . '</option>';
		}
		$out .= '</select>';

		return $out;
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